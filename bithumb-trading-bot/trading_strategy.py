"""
거래량 기반 단타 트레이딩 전략
"""
import time
import logging
from typing import Optional, Dict, List
from datetime import datetime
from collections import defaultdict

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)
logger = logging.getLogger(__name__)


class TradingStrategy:
    """거래량 기반 단타 전략"""

    def __init__(self, api_client, ws_client, config: Dict, telegram_notifier=None):
        self.api = api_client
        self.ws = ws_client
        self.config = config
        self.telegram = telegram_notifier

        # 거래 상태
        self.current_position = None  # {'symbol': 'BTC', 'buy_price': 50000, 'quantity': 0.1, 'buy_time': timestamp}
        self.blacklist = set(config.get('blacklist', []))

        # 거래 통계
        self.daily_stats = {
            'trades': 0,
            'wins': 0,
            'losses': 0,
            'total_profit': 0,
            'start_balance': 0
        }

        # 거래량 분석
        self.volume_analyzer = defaultdict(lambda: {
            'avg_volume': 0,
            'last_spike_time': 0
        })

    def should_trade(self) -> bool:
        """거래 가능 여부 확인"""
        # 일일 손실 제한 체크
        max_daily_loss = self.config.get('max_daily_loss', 0.05)
        if self.daily_stats['start_balance'] > 0:
            current_balance = self._get_krw_balance()
            loss_ratio = (self.daily_stats['start_balance'] - current_balance) / self.daily_stats['start_balance']

            if loss_ratio >= max_daily_loss:
                logger.warning(f"일일 최대 손실 도달: {loss_ratio*100:.2f}% - 거래 중지")
                return False

        return True

    def _get_krw_balance(self) -> float:
        """KRW 잔고 조회"""
        try:
            balance = self.api.get_balance("KRW")
            available = float(balance.get('available_krw', 0))
            return available
        except Exception as e:
            logger.error(f"잔고 조회 실패: {e}")
            return 0

    def _get_coin_balance(self, symbol: str) -> float:
        """코인 잔고 조회"""
        try:
            balance = self.api.get_balance(symbol)
            available = float(balance.get(f'available_{symbol.lower()}', 0))
            return available
        except Exception as e:
            logger.error(f"{symbol} 잔고 조회 실패: {e}")
            return 0

    def check_position_manually_sold(self) -> bool:
        """포지션이 수동으로 매도되었는지 확인"""
        if not self.current_position:
            return False

        symbol = self.current_position['symbol']
        current_balance = self._get_coin_balance(symbol)

        # 보유량이 현저히 줄었으면 수동 매도로 간주
        if current_balance < self.current_position['quantity'] * 0.1:
            logger.warning(f"[수동 매도 감지] {symbol} - 포지션이 수동으로 청산되었습니다.")
            if self.telegram:
                self.telegram.notify_warning(f"{symbol} 포지션이 수동으로 청산되었습니다.")
            self.current_position = None
            return True

        return False

    def find_best_coin(self) -> Optional[str]:
        """거래량 기반 최적 코인 선택"""
        min_volume_krw = self.config.get('min_volume_krw', 50000000)
        spike_threshold = self.config.get('volume_spike_threshold', 2.0)

        candidates = []

        for symbol, ticker in self.ws.ticker_data.items():
            # 블랙리스트 제외 (절대 매수 금지)
            if symbol in self.blacklist:
                continue

            # 최소 거래량 체크
            volume_krw = ticker.get('value', 0)
            if volume_krw < min_volume_krw:
                continue

            # 거래량 급등 확인
            spike_ratio = self.ws.get_volume_spike(symbol)
            if spike_ratio < spike_threshold:
                continue

            # 가격 변동률 확인 (급등하는 코인 선호)
            change_rate = ticker.get('change_rate', 0)

            candidates.append({
                'symbol': symbol,
                'volume': volume_krw,
                'spike_ratio': spike_ratio,
                'change_rate': change_rate,
                'price': ticker.get('closing_price', 0)
            })

        if not candidates:
            return None

        # 거래량 급등 비율이 가장 높은 코인 선택
        best_coin = max(candidates, key=lambda x: x['spike_ratio'])

        logger.info(f"[최적 코인 선택] {best_coin['symbol']} - "
                   f"거래량: {best_coin['volume']:,.0f} KRW, "
                   f"급등 비율: {best_coin['spike_ratio']:.2f}x, "
                   f"변동률: {best_coin['change_rate']:.2f}%")

        return best_coin['symbol']

    def execute_buy(self, symbol: str) -> bool:
        """전액 매수"""
        try:
            # KRW 잔고 확인
            krw_balance = self._get_krw_balance()
            if krw_balance < 5000:  # 최소 5천원
                logger.warning(f"잔고 부족: {krw_balance:,.0f} KRW")
                return False

            # 포지션 비율 적용
            max_ratio = self.config.get('max_position_ratio', 0.95)
            buy_amount = krw_balance * max_ratio

            # 수수료 고려
            fee_rate = self.config.get('fee_rate', 0.0025)
            buy_amount_with_fee = buy_amount / (1 + fee_rate)

            # 시장가 매수
            logger.info(f"[매수 실행] {symbol} - {buy_amount_with_fee:,.0f} KRW")

            if not self.config.get('dry_run', False):
                result = self.api.place_market_buy(symbol, price=int(buy_amount_with_fee))
                order_id = result.get('order_id')

                # 체결 확인
                time.sleep(2)
                filled_quantity = self._get_coin_balance(symbol)
                filled_price = buy_amount / filled_quantity if filled_quantity > 0 else 0

                self.current_position = {
                    'symbol': symbol,
                    'buy_price': filled_price,
                    'quantity': filled_quantity,
                    'buy_time': time.time(),
                    'order_id': order_id
                }

                logger.info(f"[매수 완료] {symbol} - "
                           f"수량: {filled_quantity:.8f}, "
                           f"평균가: {filled_price:,.0f} KRW")

                # 텔레그램 알림
                if self.telegram:
                    self.telegram.notify_buy(symbol, filled_quantity, filled_price, buy_amount)

                return True
            else:
                # 시뮬레이션 모드
                ticker = self.ws.get_ticker(symbol)
                current_price = ticker.get('closing_price', 0)
                quantity = buy_amount_with_fee / current_price

                self.current_position = {
                    'symbol': symbol,
                    'buy_price': current_price,
                    'quantity': quantity,
                    'buy_time': time.time(),
                    'order_id': 'SIMULATION'
                }

                logger.info(f"[시뮬레이션 매수] {symbol} - "
                           f"수량: {quantity:.8f}, "
                           f"가격: {current_price:,.0f} KRW")

                # 텔레그램 알림
                if self.telegram:
                    self.telegram.notify_buy(symbol, quantity, current_price, buy_amount_with_fee)

                return True

        except Exception as e:
            logger.error(f"매수 실패: {e}")
            return False

    def execute_sell(self, reason: str = "일반 매도") -> bool:
        """전액 매도"""
        if not self.current_position:
            return False

        try:
            symbol = self.current_position['symbol']
            quantity = self.current_position['quantity']
            buy_price = self.current_position['buy_price']

            logger.info(f"[매도 실행] {symbol} - {quantity:.8f} units ({reason})")

            if not self.config.get('dry_run', False):
                result = self.api.place_market_sell(symbol, quantity)

                # 체결 확인
                time.sleep(2)
                ticker = self.ws.get_ticker(symbol)
                sell_price = ticker.get('closing_price', 0)

                # 수익률 계산
                profit_rate = (sell_price - buy_price) / buy_price
                fee_rate = self.config.get('fee_rate', 0.0025)
                net_profit_rate = profit_rate - (fee_rate * 2)  # 매수/매도 수수료

                profit_krw = (sell_price - buy_price) * quantity - (sell_price * quantity * fee_rate * 2)

                logger.info(f"[매도 완료] {symbol} - "
                           f"매수가: {buy_price:,.0f}, "
                           f"매도가: {sell_price:,.0f}, "
                           f"수익률: {net_profit_rate*100:.2f}%, "
                           f"수익금: {profit_krw:,.0f} KRW")

                # 통계 업데이트
                self.daily_stats['trades'] += 1
                if net_profit_rate > 0:
                    self.daily_stats['wins'] += 1
                else:
                    self.daily_stats['losses'] += 1
                self.daily_stats['total_profit'] += profit_krw

                # 텔레그램 알림
                if self.telegram:
                    self.telegram.notify_sell(symbol, quantity, buy_price, sell_price,
                                             net_profit_rate, profit_krw, reason)

            else:
                # 시뮬레이션 모드
                ticker = self.ws.get_ticker(symbol)
                sell_price = ticker.get('closing_price', 0)

                profit_rate = (sell_price - buy_price) / buy_price
                fee_rate = self.config.get('fee_rate', 0.0025)
                net_profit_rate = profit_rate - (fee_rate * 2)

                profit_krw = (sell_price - buy_price) * quantity - (sell_price * quantity * fee_rate * 2)

                logger.info(f"[시뮬레이션 매도] {symbol} - "
                           f"매수가: {buy_price:,.0f}, "
                           f"매도가: {sell_price:,.0f}, "
                           f"수익률: {net_profit_rate*100:.2f}%, "
                           f"수익금: {profit_krw:,.0f} KRW")

                # 통계 업데이트
                self.daily_stats['trades'] += 1
                if net_profit_rate > 0:
                    self.daily_stats['wins'] += 1
                else:
                    self.daily_stats['losses'] += 1
                self.daily_stats['total_profit'] += profit_krw

                # 텔레그램 알림
                if self.telegram:
                    self.telegram.notify_sell(symbol, quantity, buy_price, sell_price,
                                             net_profit_rate, profit_krw, reason)

            self.current_position = None
            return True

        except Exception as e:
            logger.error(f"매도 실패: {e}")
            return False

    def check_exit_conditions(self) -> Optional[str]:
        """청산 조건 체크"""
        if not self.current_position:
            return None

        symbol = self.current_position['symbol']
        buy_price = self.current_position['buy_price']
        buy_time = self.current_position['buy_time']

        # 현재가 조회
        ticker = self.ws.get_ticker(symbol)
        current_price = ticker.get('closing_price', 0)

        if current_price == 0:
            return None

        # 수익률 계산
        profit_rate = (current_price - buy_price) / buy_price

        # 익절 조건
        take_profit = self.config.get('take_profit_rate', 0.02)
        if profit_rate >= take_profit:
            return "익절"

        # 손절 조건
        stop_loss = self.config.get('stop_loss_rate', 0.015)
        if profit_rate <= -stop_loss:
            return "손절"

        # 시간 기반 청산 (5분 이상 보유시 작은 이익이라도 실현)
        holding_time = time.time() - buy_time
        min_profit = self.config.get('min_profit_rate', 0.006)

        if holding_time > 300 and profit_rate >= min_profit:  # 5분
            return "시간 기반 익절"

        # 거래량 감소 감지 (급등이 끝나면 청산)
        spike_ratio = self.ws.get_volume_spike(symbol)
        if spike_ratio < 1.0 and profit_rate > 0:
            return "거래량 감소"

        return None

    def print_stats(self):
        """거래 통계 출력"""
        stats = self.daily_stats
        win_rate = (stats['wins'] / stats['trades'] * 100) if stats['trades'] > 0 else 0

        logger.info("=" * 60)
        logger.info(f"일일 거래 통계")
        logger.info(f"총 거래: {stats['trades']}회")
        logger.info(f"성공: {stats['wins']}회, 실패: {stats['losses']}회")
        logger.info(f"승률: {win_rate:.1f}%")
        logger.info(f"총 수익: {stats['total_profit']:,.0f} KRW")

        if stats['start_balance'] > 0:
            current_balance = self._get_krw_balance()
            total_return = (current_balance - stats['start_balance']) / stats['start_balance'] * 100
            logger.info(f"수익률: {total_return:.2f}%")

        logger.info("=" * 60)
