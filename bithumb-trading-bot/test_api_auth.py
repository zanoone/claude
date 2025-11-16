#!/usr/bin/env python3
"""
Bithumb API 인증 테스트 스크립트
API 키가 올바른지 확인합니다.
"""
import os
import sys
import logging
from dotenv import load_dotenv
from bithumb_api import BithumbAPI

# 디버그 로깅 활성화
logging.basicConfig(
    level=logging.DEBUG,
    format='%(asctime)s [%(levelname)s] %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)
logger = logging.getLogger(__name__)

def main():
    # 환경 변수 로드
    load_dotenv()

    api_key = os.getenv('BITHUMB_API_KEY')
    secret_key = os.getenv('BITHUMB_SECRET_KEY')

    print("=" * 70)
    print("Bithumb API 인증 테스트")
    print("=" * 70)
    print(f"API Key: {api_key}")
    print(f"API Key 길이: {len(api_key) if api_key else 0}")
    print(f"Secret Key: {secret_key[:30]}...")
    print(f"Secret Key 길이: {len(secret_key) if secret_key else 0}")
    print("=" * 70)

    if not api_key or not secret_key:
        print("❌ API 키가 설정되지 않았습니다!")
        print("   .env 파일을 확인하세요.")
        return

    # API 클라이언트 생성
    api = BithumbAPI(api_key=api_key, secret_key=secret_key)

    print("\n[테스트 1] Public API - 현재가 조회 (인증 불필요)")
    try:
        ticker = api.get_ticker("BTC")
        btc_price = ticker.get('closing_price', 0)
        print(f"✅ 성공: BTC 현재가 = {btc_price:,.0f} KRW")
    except Exception as e:
        print(f"❌ 실패: {e}")

    print("\n[테스트 2] Private API - 잔고 조회 (인증 필요)")
    try:
        balance = api.get_balance("KRW")
        krw_balance = balance.get('available_krw', 0)
        print(f"✅ 성공: KRW 잔고 = {krw_balance:,.0f} KRW")
        print("\n잔고 정보:")
        for key, value in balance.items():
            if 'krw' in key.lower():
                print(f"  {key}: {value}")
    except Exception as e:
        print(f"❌ 실패: {e}")
        print("\n가능한 원인:")
        print("1. API 키가 잘못되었거나 만료됨")
        print("2. Secret Key가 잘못 복사됨 (Base64 인코딩 확인)")
        print("3. API 키에 '잔고 조회' 권한이 없음")
        print("4. Bithumb에서 API 키를 비활성화함")
        print("\n해결 방법:")
        print("- Bithumb 웹사이트 > 마이페이지 > API 관리에서 키 확인")
        print("- 새로운 API 키 발급 (거래 권한 포함)")
        print("- Secret Key를 정확히 복사 (공백 없이)")

    print("\n" + "=" * 70)

if __name__ == "__main__":
    main()
