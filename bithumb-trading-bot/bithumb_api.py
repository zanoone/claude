"""
Bithumb API 2.0 Client (JWT 인증 방식)
"""
import time
import jwt
import requests
import hashlib
import urllib.parse
import uuid
from typing import Dict, Optional, Any
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class BithumbAPI:
    """Bithumb API 2.0 JWT 인증 클라이언트"""

    BASE_URL = "https://api.bithumb.com"

    def __init__(self, api_key: str, secret_key: str):
        self.api_key = api_key
        self.secret_key = secret_key

    def _generate_jwt_token(self, endpoint: str, params: Dict = None) -> str:
        """JWT 토큰 생성 (Bithumb API 2.0 표준)"""
        # 페이로드 기본 필드
        payload = {
            'access_key': self.api_key,
            'nonce': str(uuid.uuid4()),
            'timestamp': round(time.time() * 1000)
        }

        # 쿼리 파라미터가 있는 경우 query_hash 생성
        if params:
            query_string = urllib.parse.urlencode(params)
            query_hash = hashlib.sha512(query_string.encode('utf-8')).hexdigest()
            payload['query_hash'] = query_hash
            payload['query_hash_alg'] = 'SHA512'
            logger.debug(f"Query String: {query_string}")
            logger.debug(f"Query Hash: {query_hash}")

        logger.debug(f"JWT Payload: {payload}")

        # JWT 토큰 생성
        token = jwt.encode(payload, self.secret_key, algorithm='HS256')
        logger.debug(f"Generated Token: {token[:50]}...")
        return f"Bearer {token}"

    def _request(self, method: str, endpoint: str, params: Dict = None) -> Dict:
        """API 요청 실행"""
        url = f"{self.BASE_URL}{endpoint}"

        headers = {
            'Authorization': self._generate_jwt_token(endpoint, params)
        }

        try:
            if method == 'GET':
                response = requests.get(url, params=params, headers=headers, timeout=10)
            elif method == 'POST':
                # Bithumb API는 POST 요청 시 form data 형식 사용
                response = requests.post(url, data=params, headers=headers, timeout=10)
            else:
                raise ValueError(f"Unsupported method: {method}")

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
