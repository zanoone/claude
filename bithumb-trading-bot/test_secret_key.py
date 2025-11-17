"""
Secret Key 처리 방식 테스트
두 가지 방법을 모두 시도:
1. Secret Key를 그대로 사용 (문자열 → bytes)
2. Secret Key를 Base64 디코드해서 사용
"""
import os
import time
import hmac
import hashlib
import base64
import urllib.parse
import requests
from dotenv import load_dotenv

load_dotenv()

API_KEY = os.getenv('BITHUMB_API_KEY')
SECRET_KEY = os.getenv('BITHUMB_SECRET_KEY')

def test_method_1():
    """방법 1: Secret Key를 그대로 사용 (현재 방식)"""
    print("\n" + "="*70)
    print("방법 1: Secret Key를 문자열로 그대로 사용")
    print("="*70)

    endpoint = "/info/balance"
    params = {"currency": "ALL"}
    nonce = str(int(time.time() * 1000))

    query_string = urllib.parse.urlencode(params)
    sign_data = endpoint + chr(0) + query_string + chr(0) + nonce

    # Secret Key를 그대로 bytes로 변환
    secret_key_bytes = SECRET_KEY.encode('utf-8')

    h = hmac.new(secret_key_bytes, sign_data.encode('utf-8'), hashlib.sha512)
    signature = base64.b64encode(h.hexdigest().encode('utf-8')).decode('utf-8')

    print(f"Secret Key (원본): {SECRET_KEY[:20]}...")
    print(f"Secret Key 길이: {len(SECRET_KEY)}")
    print(f"Secret Key (bytes 길이): {len(secret_key_bytes)}")
    print(f"Signature: {signature[:50]}...")

    # API 요청
    headers = {
        'Api-Key': API_KEY,
        'Api-Sign': signature,
        'Api-Nonce': nonce,
        'Content-Type': 'application/x-www-form-urlencoded'
    }

    response = requests.post('https://api.bithumb.com/info/balance', data=params, headers=headers)
    print(f"\nResponse Status: {response.status_code}")
    print(f"Response: {response.text[:200]}")

    return response.status_code == 200

def test_method_2():
    """방법 2: Secret Key를 Base64 디코드해서 사용"""
    print("\n" + "="*70)
    print("방법 2: Secret Key를 Base64 디코드해서 사용")
    print("="*70)

    endpoint = "/info/balance"
    params = {"currency": "ALL"}
    nonce = str(int(time.time() * 1000))

    query_string = urllib.parse.urlencode(params)
    sign_data = endpoint + chr(0) + query_string + chr(0) + nonce

    # Secret Key를 Base64 디코드
    secret_key_bytes = base64.b64decode(SECRET_KEY)

    h = hmac.new(secret_key_bytes, sign_data.encode('utf-8'), hashlib.sha512)
    signature = base64.b64encode(h.hexdigest().encode('utf-8')).decode('utf-8')

    print(f"Secret Key (원본): {SECRET_KEY[:20]}...")
    print(f"Secret Key 길이: {len(SECRET_KEY)}")
    print(f"Secret Key (디코드 후 hex): {secret_key_bytes.hex()[:40]}...")
    print(f"Secret Key (bytes 길이): {len(secret_key_bytes)}")
    print(f"Signature: {signature[:50]}...")

    # API 요청
    headers = {
        'Api-Key': API_KEY,
        'Api-Sign': signature,
        'Api-Nonce': nonce,
        'Content-Type': 'application/x-www-form-urlencoded'
    }

    response = requests.post('https://api.bithumb.com/info/balance', data=params, headers=headers)
    print(f"\nResponse Status: {response.status_code}")
    print(f"Response: {response.text[:200]}")

    return response.status_code == 200

if __name__ == "__main__":
    print("Bithumb API Secret Key 처리 방식 테스트")

    result1 = test_method_1()
    result2 = test_method_2()

    print("\n" + "="*70)
    print("테스트 결과")
    print("="*70)
    print(f"방법 1 (그대로 사용): {'✅ 성공' if result1 else '❌ 실패'}")
    print(f"방법 2 (Base64 디코드): {'✅ 성공' if result2 else '❌ 실패'}")

    if result1:
        print("\n✅ 방법 1이 정답입니다: Secret Key를 그대로 사용하세요")
    elif result2:
        print("\n✅ 방법 2가 정답입니다: Secret Key를 Base64 디코드해서 사용하세요")
    else:
        print("\n❌ 두 방법 모두 실패했습니다. API 키를 확인하세요")
