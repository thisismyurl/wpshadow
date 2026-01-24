#!/usr/bin/env python3
"""
Test WordPress REST API connection (no external dependencies)
"""
import os
import sys
import urllib.request
import urllib.error
import base64
import json

# Parse .env file manually
env_file = '.env'
env_vars = {}

if os.path.exists(env_file):
    with open(env_file) as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith('#'):
                key, value = line.split('=', 1)
                env_vars[key] = value

WP_SITE_URL = env_vars.get('WP_SITE_URL', '').strip()
WP_USERNAME = env_vars.get('WP_USERNAME', '').strip()
WP_APP_PASSWORD = env_vars.get('WP_APP_PASSWORD', '').strip()

if not all([WP_SITE_URL, WP_USERNAME, WP_APP_PASSWORD]):
    print("❌ Error: Missing environment variables in .env")
    print(f"   WP_SITE_URL: {WP_SITE_URL}")
    print(f"   WP_USERNAME: {WP_USERNAME}")
    print(f"   WP_APP_PASSWORD: {'***' if WP_APP_PASSWORD else 'NOT SET'}")
    sys.exit(1)

print("🔍 Testing WordPress REST API Connection...")
print(f"   Site: {WP_SITE_URL}")
print(f"   User: {WP_USERNAME}")
print()

try:
    # Test 1: Check if site is reachable
    print("1️⃣  Checking if site is reachable...")
    req = urllib.request.Request(WP_SITE_URL)
    try:
        response = urllib.request.urlopen(req, timeout=5)
        print(f"   ✅ Site reachable (HTTP {response.status})")
    except urllib.error.HTTPError as e:
        if e.code in [301, 302, 303, 307]:
            print(f"   ✅ Site reachable (HTTP {e.code} - redirect)")
        else:
            print(f"   ⚠️  Got HTTP {e.code}")
    print()
    
    # Test 2: Check REST API availability
    print("2️⃣  Checking REST API availability...")
    api_url = f"{WP_SITE_URL}/wp-json"
    req = urllib.request.Request(api_url)
    try:
        response = urllib.request.urlopen(req, timeout=5)
        print(f"   ✅ REST API available (HTTP {response.status})")
    except urllib.error.HTTPError as e:
        print(f"   ⚠️  REST API returned {e.code}")
    print()
    
    # Test 3: Test authentication
    print("3️⃣  Testing authentication with Application Password...")
    
    # Create Basic Auth header
    credentials = f"{WP_USERNAME}:{WP_APP_PASSWORD}"
    encoded = base64.b64encode(credentials.encode()).decode('ascii')
    
    auth_url = f"{WP_SITE_URL}/wp-json/wp/v2/users/me"
    req = urllib.request.Request(auth_url)
    req.add_header('Authorization', f'Basic {encoded}')
    
    try:
        response = urllib.request.urlopen(req, timeout=5)
        data = json.loads(response.read().decode('utf-8'))
        
        print(f"   ✅ Authentication successful!")
        print(f"   User: {data.get('name')}")
        print(f"   Email: {data.get('email')}")
        print(f"   ID: {data.get('id')}")
        print()
        
        # Test 4: Check if user can read posts
        print("4️⃣  Checking permissions...")
        posts_url = f"{WP_SITE_URL}/wp-json/wp/v2/posts?per_page=1"
        req = urllib.request.Request(posts_url)
        req.add_header('Authorization', f'Basic {encoded}')
        
        response = urllib.request.urlopen(req, timeout=5)
        print(f"   ✅ Can read posts")
        print()
        
        print("=" * 50)
        print("✅ ALL TESTS PASSED!")
        print("=" * 50)
        print()
        print("Your credentials are working correctly.")
        print("Ready to publish KB articles! 🚀")
        
    except urllib.error.HTTPError as e:
        if e.code == 401:
            print(f"   ❌ Authentication failed (HTTP {e.code})")
            print(f"   Invalid username or application password")
            print()
            print("💡 Double-check:")
            print("   1. Username is correct")
            print("   2. Application Password is correct (entire string with spaces)")
            print("   3. .env file format: KEY=VALUE (no quotes)")
            sys.exit(1)
        else:
            print(f"   ❌ Unexpected response: HTTP {e.code}")
            print(f"   Error: {e.reason}")
            sys.exit(1)

except urllib.error.URLError as e:
    print(f"   ❌ Connection error: {e.reason}")
    print()
    print("💡 This means:")
    print("   1. Site is not reachable")
    print("   2. URL might be incorrect")
    print("   3. DNS or network issue")
    sys.exit(1)
    
except Exception as e:
    print(f"   ❌ Error: {e}")
    sys.exit(1)
