"""
Bithumb WebSocket Client - 실시간 시세 및 체결 데이터 수신
"""
import json
import time
import threading
import websocket
from typing import Callable, List, Dict
import logging
from collections import defaultdict, deque

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class BithumbWebSocket:
    """Bithumb WebSocket 클라이언트"""

    WS_URL = "wss://pubwss.bithumb.com/pub/ws"

    def __init__(self, symbols: List[str] = None, on_reconnect_callback=None):
        """
        Args:
            symbols: 구독할 심볼 리스트 (예: ['BTC', 'ETH'])
            on_reconnect_callback: 재연결 시 호출할 콜백 함수
        """
        self.symbols = symbols or []
        self.ws = None
        self.thread = None
        self.running = False
        self.reconnect_attempts = 0
        self.max_reconnect_attempts = 10
        self.reconnect_delay = 5  # 초
        self.on_reconnect_callback = on_reconnect_callback
        self.last_message_time = time.time()

        # 데이터 저장소
        self.ticker_data = {}  # 현재가 데이터
        self.orderbook_data = {}  # 호가 데이터
        self.transaction_data = defaultdict(deque)  # 체결 데이터 (최근 100개)

        # 거래량 분석용
        self.volume_tracker = defaultdict(lambda: {
            'total_volume': 0,
            'volume_history': deque(maxlen=60),  # 최근 60초
            'last_update': time.time()
        })

        # 콜백 함수
        self.on_ticker_callback = None
        self.on_transaction_callback = None
        self.on_orderbook_callback = None

    def on_message(self, ws, message):
        """WebSocket 메시지 수신"""
        try:
            self.last_message_time = time.time()  # 마지막 메시지 시간 업데이트
            data = json.loads(message)
            msg_type = data.get('type')

            if msg_type == 'ticker':
                self._handle_ticker(data)
            elif msg_type == 'transaction':
                self._handle_transaction(data)
            elif msg_type == 'orderbookdepth':
                self._handle_orderbook(data)

        except Exception as e:
            logger.error(f"메시지 처리 오류: {e}")

    def _handle_ticker(self, data):
        """Ticker 데이터 처리"""
        content = data.get('content', {})
        symbol = content.get('symbol', '').replace('_KRW', '')

        if symbol:
            self.ticker_data[symbol] = {
                'closing_price': float(content.get('closePrice', 0)),
                'opening_price': float(content.get('openPrice', 0)),
                'high_price': float(content.get('highPrice', 0)),
                'low_price': float(content.get('lowPrice', 0)),
                'volume': float(content.get('volume', 0)),
                'value': float(content.get('value', 0)),  # 거래대금
                'change_rate': float(content.get('chgRate', 0)),
                'timestamp': time.time()
            }

            # 거래량 추적
            self._track_volume(symbol, float(content.get('value', 0)))

            # 콜백 실행
            if self.on_ticker_callback:
                self.on_ticker_callback(symbol, self.ticker_data[symbol])

    def _handle_transaction(self, data):
        """체결 데이터 처리"""
        content = data.get('content', {})
        if not content:
            return

        symbol = content.get('symbol', '').replace('_KRW', '')
        trans_list = content.get('list', [])

        for trans in trans_list:
            transaction = {
                'symbol': symbol,
                'type': trans.get('buySellGb'),  # 1: 매도, 2: 매수
                'price': float(trans.get('contPrice', 0)),
                'quantity': float(trans.get('contQty', 0)),
                'amount': float(trans.get('contAmt', 0)),
                'timestamp': int(trans.get('contDtm', 0))
            }

            # 최근 체결 내역 저장 (최대 100개)
            self.transaction_data[symbol].append(transaction)
            if len(self.transaction_data[symbol]) > 100:
                self.transaction_data[symbol].popleft()

            # 콜백 실행
            if self.on_transaction_callback:
                self.on_transaction_callback(symbol, transaction)

    def _handle_orderbook(self, data):
        """호가 데이터 처리"""
        content = data.get('content', {})
        if not content:
            return

        symbol = content.get('symbol', '').replace('_KRW', '')
        orderbook = {
            'symbol': symbol,
            'bids': [],  # 매수 호가
            'asks': [],  # 매도 호가
            'timestamp': time.time()
        }

        # 매수 호가
        for bid in content.get('list', []):
            if bid.get('orderType') == 'bid':
                orderbook['bids'].append({
                    'price': float(bid.get('price', 0)),
                    'quantity': float(bid.get('quantity', 0))
                })

        # 매도 호가
        for ask in content.get('list', []):
            if ask.get('orderType') == 'ask':
                orderbook['asks'].append({
                    'price': float(ask.get('price', 0)),
                    'quantity': float(ask.get('quantity', 0))
                })

        self.orderbook_data[symbol] = orderbook

        # 콜백 실행
        if self.on_orderbook_callback:
            self.on_orderbook_callback(symbol, orderbook)

    def _track_volume(self, symbol: str, value: float):
        """거래량 추적"""
        tracker = self.volume_tracker[symbol]
        current_time = time.time()

        # 1분 단위로 거래량 기록
        if current_time - tracker['last_update'] >= 1:
            tracker['volume_history'].append({
                'value': value,
                'timestamp': current_time
            })
            tracker['last_update'] = current_time

        tracker['total_volume'] = value

    def get_volume_spike(self, symbol: str) -> float:
        """거래량 급등 비율 계산"""
        tracker = self.volume_tracker.get(symbol)
        if not tracker or len(tracker['volume_history']) < 10:
            return 0.0

        # 최근 거래량과 평균 비교
        recent_volumes = [v['value'] for v in tracker['volume_history']]
        avg_volume = sum(recent_volumes) / len(recent_volumes)

        if avg_volume == 0:
            return 0.0

        current_volume = tracker['total_volume']
        spike_ratio = current_volume / avg_volume

        return spike_ratio

    def on_error(self, ws, error):
        """WebSocket 에러"""
        logger.error(f"WebSocket 에러: {error}")
        # 재연결 시도
        if self.running:
            self._attempt_reconnect()

    def on_close(self, ws, close_status_code, close_msg):
        """WebSocket 연결 종료"""
        logger.warning(f"WebSocket 연결 종료: {close_status_code} - {close_msg}")
        # 의도적 종료가 아니면 재연결
        if self.running:
            self._attempt_reconnect()

    def _attempt_reconnect(self):
        """재연결 시도"""
        if self.reconnect_attempts >= self.max_reconnect_attempts:
            logger.error(f"최대 재연결 시도 횟수 초과 ({self.max_reconnect_attempts})")
            self.running = False
            return

        self.reconnect_attempts += 1
        wait_time = self.reconnect_delay * self.reconnect_attempts

        logger.warning(f"WebSocket 재연결 시도 {self.reconnect_attempts}/{self.max_reconnect_attempts} - {wait_time}초 대기")

        # 재연결 콜백 실행
        if self.on_reconnect_callback:
            self.on_reconnect_callback("WebSocket")

        time.sleep(wait_time)

        # 재연결
        try:
            self.start()
            logger.info("WebSocket 재연결 성공")
            self.reconnect_attempts = 0  # 성공 시 카운터 리셋
        except Exception as e:
            logger.error(f"WebSocket 재연결 실패: {e}")
            self._attempt_reconnect()  # 재귀적으로 재시도

    def on_open(self, ws):
        """WebSocket 연결 성공"""
        logger.info("WebSocket 연결 성공")

        # Ticker 구독
        if self.symbols:
            subscribe_msg = {
                "type": "ticker",
                "symbols": [f"{s}_KRW" for s in self.symbols],
                "tickTypes": ["1H"]  # 1시간 단위
            }
            ws.send(json.dumps(subscribe_msg))
            logger.info(f"Ticker 구독: {self.symbols}")

            # 체결 데이터 구독
            transaction_msg = {
                "type": "transaction",
                "symbols": [f"{s}_KRW" for s in self.symbols]
            }
            ws.send(json.dumps(transaction_msg))
            logger.info(f"Transaction 구독: {self.symbols}")

            # 호가 데이터 구독
            orderbook_msg = {
                "type": "orderbookdepth",
                "symbols": [f"{s}_KRW" for s in self.symbols]
            }
            ws.send(json.dumps(orderbook_msg))
            logger.info(f"Orderbook 구독: {self.symbols}")

    def start(self):
        """WebSocket 연결 시작"""
        self.running = True

        def run_ws():
            self.ws = websocket.WebSocketApp(
                self.WS_URL,
                on_open=self.on_open,
                on_message=self.on_message,
                on_error=self.on_error,
                on_close=self.on_close
            )
            self.ws.run_forever()

        self.thread = threading.Thread(target=run_ws, daemon=True)
        self.thread.start()
        logger.info("WebSocket 시작")

    def stop(self):
        """WebSocket 연결 중지"""
        self.running = False
        if self.ws:
            self.ws.close()
        logger.info("WebSocket 중지")

    def subscribe(self, symbols: List[str]):
        """추가 심볼 구독"""
        if not self.ws:
            return

        subscribe_msg = {
            "type": "ticker",
            "symbols": [f"{s}_KRW" for s in symbols],
            "tickTypes": ["1H"]
        }
        self.ws.send(json.dumps(subscribe_msg))

        transaction_msg = {
            "type": "transaction",
            "symbols": [f"{s}_KRW" for s in symbols]
        }
        self.ws.send(json.dumps(transaction_msg))

        logger.info(f"추가 구독: {symbols}")

    def get_ticker(self, symbol: str) -> Dict:
        """현재가 데이터 조회"""
        return self.ticker_data.get(symbol, {})

    def get_orderbook(self, symbol: str) -> Dict:
        """호가 데이터 조회"""
        return self.orderbook_data.get(symbol, {})

    def get_recent_transactions(self, symbol: str, count: int = 10) -> List[Dict]:
        """최근 체결 내역 조회"""
        transactions = list(self.transaction_data.get(symbol, []))
        return transactions[-count:]

    def is_healthy(self, timeout: int = 30) -> bool:
        """WebSocket 연결 상태 확인"""
        if not self.running or not self.ws:
            return False

        # 마지막 메시지 수신 시간 체크
        if time.time() - self.last_message_time > timeout:
            logger.warning(f"WebSocket 메시지 수신 시간 초과 ({timeout}초)")
            return False

        return True


# 테스트
if __name__ == "__main__":
    ws_client = BithumbWebSocket(symbols=['BTC', 'ETH', 'XRP'])

    def on_ticker(symbol, data):
        print(f"[Ticker] {symbol}: {data['closing_price']:,.0f} KRW, 거래량: {data['volume']:,.2f}")

    ws_client.on_ticker_callback = on_ticker
    ws_client.start()

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        ws_client.stop()
