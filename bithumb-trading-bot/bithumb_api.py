"""
Bithumb API Client (HMAC-SHA512 인증 방식)
"""
import time
import requests
import hashlib
import urllib.parse
import base64
import hmac
from typing import Dict, Optional, Any
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class BithumbAPI:
    """Bithumb API HMAC-SHA512 인증 클라이언트"""

    BASE_URL = "https://api.bithumb.com"

    def __init__(self, api_key: str, secret_key: str):
        self.api_key = api_key.encode('utf-8')
        # Secret Key가 Base64로 인코딩되어 있으므로 디코딩
        self.secret_key = base64.b64decode(secret_key)

    def _generate_signature(self, endpoint: str, params: Dict = None, nonce: str = None) -> tuple:
        """HMAC-SHA512 시그니처 생성"""
        if nonce is None:
            nonce = str(int(time.time() * 1000))

        # 파라미터에 endpoint 추가
        if params is None:
            params = {}
        params['endpoint'] = endpoint

        # Query string 생성 (sorted)
        query_string = urllib.parse.urlencode(sorted(params.items()))

        # Signature 생성: endpoint + \0 + query_string + \0 + nonce
        payload = endpoint + chr(0) + query_string + chr(0) + nonce

        # HMAC-SHA512 해싱
        signature = hmac.new(
            self.secret_key,
            payload.encode('utf-8'),
            hashlib.sha512
        )

        # Base64 인코딩 (바이너리 digest 사용)
        signature_b64 = base64.b64encode(signature.digest())

        logger.debug(f"Endpoint: {endpoint}")
        logger.debug(f"Query String: {query_string}")
        logger.debug(f"Nonce: {nonce}")
        logger.debug(f"Signature: {signature_b64.decode('utf-8')[:50]}...")

        return signature_b64.decode('utf-8'), nonce

    def _request(self, method: str, endpoint: str, params: Dict = None) -> Dict:
        """API 요청 실행"""
        url = f"{self.BASE_URL}{endpoint}"

        if method == 'POST':
            # Private API - HMAC-SHA512 인증 필요
            signature, nonce = self._generate_signature(endpoint, params)

            headers = {
                'Api-Key': self.api_key.decode('utf-8'),
                'Api-Sign': signature,
                'Api-Nonce': nonce,
                'Content-Type': 'application/x-www-form-urlencoded'
            }

            # params에 endpoint 포함 (이미 _generate_signature에서 추가됨)
            if params is None:
                params = {}
            params['endpoint'] = endpoint

            logger.debug(f"Request Headers: {headers}")

            response = requests.post(url, data=params, headers=headers, timeout=10)
        else:
            # Public API - 인증 불필요
            response = requests.get(url, params=params, timeout=10)

        try:
            logger.debug(f"Request URL: {url}")
            logger.debug(f"Request Method: {method}")
            logger.debug(f"Response Status: {response.status_code}")

            response.raise_for_status()
            data = response.json()

            # Bithumb API 응답 형식 처리
            if data.get('status') == '0000':
                return data.get('data', {})
            else:
                error_msg = data.get('message', 'Unknown error')
                logger.error(f"API Error: {error_msg}")
                logger.error(f"Full Response: {data}")
                raise Exception(f"Bithumb API Error: {error_msg}")

        except requests.exceptions.RequestException as e:
            logger.error(f"Request failed: {e}")
            raise

    # ========== 계좌 정보 ==========

    def get_balance(self, currency: str = "ALL") -> Dict:
        """잔고 조회"""
        endpoint = "/info/balance"
        params = {"currency": currency}
        return self._request('POST', endpoint, params)

    def get_wallet_address(self, currency: str) -> Dict:
        """입금 주소 조회"""
        endpoint = "/info/wallet_address"
        params = {"currency": currency}
        return self._request('POST', endpoint, params)

    # ========== 시세 정보 (Public API) ==========

    def get_ticker(self, order_currency: str = "ALL", payment_currency: str = "KRW") -> Dict:
        """현재가 조회"""
        endpoint = f"/public/ticker/{order_currency}_{payment_currency}"
        response = requests.get(f"{self.BASE_URL}{endpoint}", timeout=10)
        data = response.json()

        if data.get('status') == '0000':
            return data.get('data', {})
        else:
            raise Exception(f"Ticker API Error: {data.get('message')}")

    def get_orderbook(self, order_currency: str, payment_currency: str = "KRW") -> Dict:
        """호가 정보 조회"""
        endpoint = f"/public/orderbook/{order_currency}_{payment_currency}"
        response = requests.get(f"{self.BASE_URL}{endpoint}", timeout=10)
        data = response.json()

        if data.get('status') == '0000':
            return data.get('data', {})
        else:
            raise Exception(f"Orderbook API Error: {data.get('message')}")

    def get_transaction_history(self, order_currency: str, payment_currency: str = "KRW") -> Dict:
        """최근 체결 내역"""
        endpoint = f"/public/transaction_history/{order_currency}_{payment_currency}"
        response = requests.get(f"{self.BASE_URL}{endpoint}", timeout=10)
        data = response.json()

        if data.get('status') == '0000':
            return data.get('data', {})
        else:
            raise Exception(f"Transaction History API Error: {data.get('message')}")

    # ========== 주문 ==========

    def place_market_buy(self, order_currency: str, units: Optional[float] = None,
                         price: Optional[float] = None) -> Dict:
        """시장가 매수 (units 또는 price 중 하나 필수)"""
        endpoint = "/trade/market_buy"
        params = {
            "order_currency": order_currency,
            "payment_currency": "KRW"
        }

        if units:
            params["units"] = str(units)
        elif price:
            params["price"] = str(int(price))  # KRW는 정수
        else:
            raise ValueError("units 또는 price 중 하나는 필수입니다")

        logger.info(f"[매수 주문] {order_currency} - {params}")
        return self._request('POST', endpoint, params)

    def place_market_sell(self, order_currency: str, units: float) -> Dict:
        """시장가 매도"""
        endpoint = "/trade/market_sell"
        params = {
            "order_currency": order_currency,
            "payment_currency": "KRW",
            "units": str(units)
        }

        logger.info(f"[매도 주문] {order_currency} - {units} units")
        return self._request('POST', endpoint, params)

    def place_limit_buy(self, order_currency: str, price: float, units: float) -> Dict:
        """지정가 매수"""
        endpoint = "/trade/place"
        params = {
            "order_currency": order_currency,
            "payment_currency": "KRW",
            "type": "bid",
            "price": str(int(price)),
            "units": str(units)
        }

        logger.info(f"[지정가 매수] {order_currency} - {price} KRW x {units} units")
        return self._request('POST', endpoint, params)

    def place_limit_sell(self, order_currency: str, price: float, units: float) -> Dict:
        """지정가 매도"""
        endpoint = "/trade/place"
        params = {
            "order_currency": order_currency,
            "payment_currency": "KRW",
            "type": "ask",
            "price": str(int(price)),
            "units": str(units)
        }

        logger.info(f"[지정가 매도] {order_currency} - {price} KRW x {units} units")
        return self._request('POST', endpoint, params)

    def cancel_order(self, order_id: str, order_currency: str, type: str) -> Dict:
        """주문 취소"""
        endpoint = "/trade/cancel"
        params = {
            "order_id": order_id,
            "order_currency": order_currency,
            "payment_currency": "KRW",
            "type": type  # "bid" or "ask"
        }

        logger.info(f"[주문 취소] Order ID: {order_id}")
        return self._request('POST', endpoint, params)

    def get_orders(self, order_currency: str, order_id: Optional[str] = None) -> Dict:
        """주문 내역 조회"""
        endpoint = "/info/orders"
        params = {
            "order_currency": order_currency,
            "payment_currency": "KRW"
        }

        if order_id:
            params["order_id"] = order_id

        return self._request('POST', endpoint, params)

    def get_user_transactions(self, order_currency: str, offset: int = 0, count: int = 20) -> Dict:
        """거래 완료 내역"""
        endpoint = "/info/user_transactions"
        params = {
            "order_currency": order_currency,
            "payment_currency": "KRW",
            "offset": offset,
            "count": count
        }

        return self._request('POST', endpoint, params)


# 테스트 함수
if __name__ == "__main__":
    from dotenv import load_dotenv
    import os

    load_dotenv()

    api = BithumbAPI(
        api_key=os.getenv('BITHUMB_API_KEY'),
        secret_key=os.getenv('BITHUMB_SECRET_KEY')
    )

    # 잔고 조회 테스트
    try:
        balance = api.get_balance()
        print("잔고:", balance)

        # 현재가 조회 테스트
        ticker = api.get_ticker("BTC")
        print("BTC 현재가:", ticker)

    except Exception as e:
        print(f"에러: {e}")
