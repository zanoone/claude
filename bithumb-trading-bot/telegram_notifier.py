"""
텔레그램 알림 모듈
"""
import requests
import logging
from typing import Optional
from datetime import datetime

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class TelegramNotifier:
    """텔레그램 알림 전송"""

    def __init__(self, bot_token: str, chat_id: str, enabled: bool = True):
        self.bot_token = bot_token
        self.chat_id = chat_id
        self.enabled = enabled
        self.api_url = f"https://api.telegram.org/bot{bot_token}/sendMessage"

        if self.enabled:
            # 봇 시작 알림
            self.send_message("🤖 Bithumb 트레이딩 봇이 시작되었습니다!")

    def send_message(self, message: str, parse_mode: str = "HTML") -> bool:
        """텔레그램 메시지 전송"""
        if not self.enabled:
            return False

        try:
            payload = {
                'chat_id': self.chat_id,
                'text': message,
                'parse_mode': parse_mode
            }

            response = requests.post(self.api_url, json=payload, timeout=10)

            if response.status_code == 200:
                return True
            else:
                logger.error(f"텔레그램 전송 실패: {response.status_code} - {response.text}")
                return False

        except Exception as e:
            logger.error(f"텔레그램 전송 에러: {e}")
            return False

    def notify_buy(self, symbol: str, quantity: float, price: float, amount: float):
        """매수 알림"""
        message = f"""
🟢 <b>매수 체결</b>

💰 코인: <b>{symbol}</b>
📊 수량: <code>{quantity:.8f}</code>
💵 가격: <code>{price:,.0f}</code> KRW
💸 총액: <code>{amount:,.0f}</code> KRW

⏰ 시간: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
"""
        self.send_message(message.strip())
        logger.info(f"[텔레그램] 매수 알림 전송: {symbol}")

    def notify_sell(self, symbol: str, quantity: float, buy_price: float,
                    sell_price: float, profit_rate: float, profit_krw: float, reason: str):
        """매도 알림"""
        emoji = "✅" if profit_rate > 0 else "❌"
        color = "🟢" if profit_rate > 0 else "🔴"

        message = f"""
{emoji} <b>매도 체결 - {reason}</b>

💰 코인: <b>{symbol}</b>
📊 수량: <code>{quantity:.8f}</code>

📈 매수가: <code>{buy_price:,.0f}</code> KRW
📉 매도가: <code>{sell_price:,.0f}</code> KRW

{color} 수익률: <b>{profit_rate*100:+.2f}%</b>
💵 손익: <b>{profit_krw:+,.0f}</b> KRW

⏰ 시간: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
"""
        self.send_message(message.strip())
        logger.info(f"[텔레그램] 매도 알림 전송: {symbol} ({reason})")

    def notify_error(self, error_message: str):
        """에러 알림"""
        message = f"""
⚠️ <b>에러 발생</b>

❗ {error_message}

⏰ 시간: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
"""
        self.send_message(message.strip())
        logger.info(f"[텔레그램] 에러 알림 전송")

    def notify_daily_stats(self, stats: dict, current_balance: float):
        """일일 통계 알림"""
        win_rate = (stats['wins'] / stats['trades'] * 100) if stats['trades'] > 0 else 0
        total_return = 0

        if stats['start_balance'] > 0:
            total_return = (current_balance - stats['start_balance']) / stats['start_balance'] * 100

        message = f"""
📊 <b>일일 거래 통계</b>

거래 횟수: <code>{stats['trades']}</code>회
성공: <code>{stats['wins']}</code>회 | 실패: <code>{stats['losses']}</code>회
승률: <b>{win_rate:.1f}%</b>

💰 총 손익: <b>{stats['total_profit']:+,.0f}</b> KRW
📈 수익률: <b>{total_return:+.2f}%</b>

💵 현재 잔고: <code>{current_balance:,.0f}</code> KRW

⏰ {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
"""
        self.send_message(message.strip())
        logger.info(f"[텔레그램] 통계 알림 전송")

    def notify_warning(self, warning_message: str):
        """경고 알림"""
        message = f"""
⚠️ <b>경고</b>

{warning_message}

⏰ 시간: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
"""
        self.send_message(message.strip())
        logger.info(f"[텔레그램] 경고 알림 전송")

    def notify_reconnect(self, service: str):
        """재연결 알림"""
        message = f"""
🔄 <b>재연결 시도</b>

서비스: <b>{service}</b>

⏰ 시간: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
"""
        self.send_message(message.strip())
        logger.info(f"[텔레그램] 재연결 알림 전송: {service}")

    def notify_shutdown(self):
        """종료 알림"""
        message = f"""
🛑 <b>봇 종료</b>

트레이딩 봇이 안전하게 종료되었습니다.

⏰ 시간: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
"""
        self.send_message(message.strip())
        logger.info(f"[텔레그램] 종료 알림 전송")


# 테스트
if __name__ == "__main__":
    import os
    from dotenv import load_dotenv

    load_dotenv()

    notifier = TelegramNotifier(
        bot_token=os.getenv('TELEGRAM_BOT_TOKEN'),
        chat_id=os.getenv('TELEGRAM_CHAT_ID'),
        enabled=os.getenv('ENABLE_TELEGRAM', 'true').lower() == 'true'
    )

    # 테스트 메시지
    notifier.notify_buy("BTC", 0.001, 50000000, 50000)
    notifier.notify_sell("BTC", 0.001, 50000000, 51000000, 0.015, 750, "익절")
