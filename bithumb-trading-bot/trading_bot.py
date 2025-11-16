#!/usr/bin/env python3
"""
Bithumb 거래량 기반 단타 트레이딩 봇

주의사항:
- 실제 자금이 투입되므로 신중하게 운영하세요
- 반드시 .env 파일에서 설정을 확인하세요
- DRY_RUN=true로 먼저 테스트하세요
"""
import os
import sys
import time
import signal
from dotenv import load_dotenv
import logging

from bithumb_api import BithumbAPI
from websocket_client import BithumbWebSocket
from trading_strategy import TradingStrategy
from telegram_notifier import TelegramNotifier

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)
logger = logging.getLogger(__name__)


class TradingBot:
    """메인 트레이딩 봇"""

    def __init__(self):
        # 환경 변수 로드
        load_dotenv()

        # API 클라이언트 초기화
        self.api = BithumbAPI(
            api_key=os.getenv('BITHUMB_API_KEY'),
            secret_key=os.getenv('BITHUMB_SECRET_KEY')
        )

        # 설정 로드
        self.config = {
            'blacklist': os.getenv('BLACKLIST', '').split(','),
            'fee_rate': float(os.getenv('FEE_RATE', 0.0025)),
            'min_profit_rate': float(os.getenv('MIN_PROFIT_RATE', 0.006)),
            'stop_loss_rate': float(os.getenv('STOP_LOSS_RATE', 0.015)),
            'take_profit_rate': float(os.getenv('TAKE_PROFIT_RATE', 0.02)),
            'max_position_ratio': float(os.getenv('MAX_POSITION_RATIO', 0.95)),
            'volume_check_interval': int(os.getenv('VOLUME_CHECK_INTERVAL', 5)),
            'min_volume_krw': float(os.getenv('MIN_VOLUME_KRW', 50000000)),
            'volume_spike_threshold': float(os.getenv('VOLUME_SPIKE_THRESHOLD', 2.0)),
            'max_daily_loss': float(os.getenv('MAX_DAILY_LOSS', 0.05)),
            'trading_enabled': os.getenv('TRADING_ENABLED', 'true').lower() == 'true',
            'dry_run': os.getenv('DRY_RUN', 'false').lower() == 'true'
        }

        # 텔레그램 알림 초기화
        self.telegram = TelegramNotifier(
            bot_token=os.getenv('TELEGRAM_BOT_TOKEN'),
            chat_id=os.getenv('TELEGRAM_CHAT_ID'),
            enabled=os.getenv('ENABLE_TELEGRAM', 'true').lower() == 'true'
        )

        # WebSocket 클라이언트 초기화 (모든 코인 모니터링)
        self.all_symbols = self._get_all_symbols()
        self.ws = BithumbWebSocket(
            symbols=self.all_symbols,
            on_reconnect_callback=self._on_reconnect
        )

        # 트레이딩 전략 초기화
        self.strategy = TradingStrategy(self.api, self.ws, self.config, self.telegram)

        # 실행 상태
        self.running = False

    def _on_reconnect(self, service: str):
        """재연결 콜백"""
        logger.warning(f"{service} 재연결 중...")
        if self.telegram:
            self.telegram.notify_reconnect(service)

    def _get_all_symbols(self) -> list:
        """거래 가능한 모든 심볼 조회"""
        try:
            ticker = self.api.get_ticker("ALL")
            symbols = [key for key in ticker.keys() if key not in ['date', 'timestamp']]

            # 블랙리스트 제외
            blacklist = set(self.config.get('blacklist', []))
            symbols = [s for s in symbols if s not in blacklist]

            logger.info(f"모니터링 대상: {len(symbols)}개 코인")
            logger.info(f"블랙리스트: {', '.join(blacklist)}")

            return symbols
        except Exception as e:
            logger.error(f"심볼 조회 실패: {e}")
            if self.telegram:
                self.telegram.notify_error(f"심볼 조회 실패: {e}")
            return []

    def start(self):
        """봇 시작"""
        logger.info("=" * 70)
        logger.info("Bithumb 거래량 기반 단타 트레이딩 봇 시작")
        logger.info("=" * 70)

        if self.config['dry_run']:
            logger.warning("⚠️  DRY RUN 모드 - 실제 거래 없이 시뮬레이션만 실행")
        else:
            logger.warning("⚠️  실제 거래 모드 - 자금이 투입됩니다!")

        # 초기 잔고 확인
        try:
            balance = self.api.get_balance("KRW")
            krw_balance = float(balance.get('available_krw', 0))
            self.strategy.daily_stats['start_balance'] = krw_balance

            logger.info(f"현재 KRW 잔고: {krw_balance:,.0f} KRW")
            logger.info(f"최대 거래 금액: {krw_balance * self.config['max_position_ratio']:,.0f} KRW")
            logger.info("-" * 70)

            if krw_balance < 5000:
                logger.error("잔고가 5,000원 미만입니다. 프로그램을 종료합니다.")
                return

        except Exception as e:
            logger.error(f"잔고 조회 실패: {e}")
            return

        # WebSocket 시작
        self.ws.start()
        time.sleep(3)  # WebSocket 연결 대기

        logger.info("WebSocket 연결 완료 - 실시간 데이터 수신 중...")
        logger.info("=" * 70)

        self.running = True
        self._run_trading_loop()

    def _run_trading_loop(self):
        """메인 트레이딩 루프"""
        last_check_time = 0
        stats_print_interval = 300  # 5분마다 통계 출력
        last_stats_time = time.time()
        last_health_check = time.time()
        health_check_interval = 30  # 30초마다 헬스체크

        while self.running:
            try:
                current_time = time.time()

                # 포지션이 있는 경우 - 청산 조건 체크
                if self.strategy.current_position:
                    # 수동 매도 체크
                    if self.strategy.check_position_manually_sold():
                        continue

                    exit_reason = self.strategy.check_exit_conditions()

                    if exit_reason:
                        logger.info(f"청산 조건 감지: {exit_reason}")
                        self.strategy.execute_sell(reason=exit_reason)
                        time.sleep(2)

                # 포지션이 없는 경우 - 신규 진입 기회 탐색
                elif current_time - last_check_time >= self.config['volume_check_interval']:
                    last_check_time = current_time

                    # 거래 가능 여부 확인
                    if not self.strategy.should_trade():
                        logger.warning("거래 중지 조건 충족 (일일 손실 제한 등)")
                        time.sleep(60)
                        continue

                    # 최적 코인 선택
                    best_coin = self.strategy.find_best_coin()

                    if best_coin:
                        logger.info(f"진입 시그널: {best_coin}")

                        # 매수 실행
                        if self.strategy.execute_buy(best_coin):
                            logger.info(f"✅ {best_coin} 매수 완료")
                        else:
                            logger.warning(f"❌ {best_coin} 매수 실패")

                        time.sleep(2)

                # 헬스체크
                if current_time - last_health_check >= health_check_interval:
                    if not self.ws.is_healthy():
                        logger.warning("WebSocket 연결 불안정 - 재연결 시도")
                        if self.telegram:
                            self.telegram.notify_warning("WebSocket 연결 불안정")
                        self.ws.stop()
                        time.sleep(3)
                        self.ws.start()
                    last_health_check = current_time

                # 통계 출력
                if current_time - last_stats_time >= stats_print_interval:
                    self.strategy.print_stats()

                    # 텔레그램 통계 전송
                    if self.telegram:
                        current_balance = self.strategy._get_krw_balance()
                        self.telegram.notify_daily_stats(self.strategy.daily_stats, current_balance)

                    last_stats_time = current_time

                time.sleep(1)

            except KeyboardInterrupt:
                logger.info("사용자에 의해 중단됨")
                break
            except Exception as e:
                logger.error(f"트레이딩 루프 에러: {e}", exc_info=True)
                time.sleep(5)

    def stop(self):
        """봇 종료"""
        logger.info("봇을 종료합니다...")
        self.running = False

        # 포지션이 남아있으면 청산
        if self.strategy.current_position:
            logger.warning("포지션이 남아있습니다. 강제 청산합니다.")
            self.strategy.execute_sell(reason="봇 종료")

        # WebSocket 종료
        self.ws.stop()

        # 최종 통계 출력
        logger.info("\n최종 거래 통계:")
        self.strategy.print_stats()

        # 텔레그램 종료 알림
        if self.telegram:
            current_balance = self.strategy._get_krw_balance()
            self.telegram.notify_daily_stats(self.strategy.daily_stats, current_balance)
            self.telegram.notify_shutdown()

        logger.info("봇 종료 완료")


def signal_handler(signum, frame):
    """시그널 핸들러"""
    global bot
    logger.info("\n종료 시그널 수신")
    if bot:
        bot.stop()
    sys.exit(0)


# 전역 봇 객체
bot = None


def main():
    global bot

    # 시그널 핸들러 등록
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)

    try:
        bot = TradingBot()
        bot.start()
    except Exception as e:
        logger.error(f"치명적 오류: {e}", exc_info=True)
        if bot:
            bot.stop()


if __name__ == "__main__":
    main()
