#!/bin/bash
# Quick syntax checker for deployed PHP files
# Verifies files on test server don't have parse errors

TEST_SERVER="https://wpshadow.com"

echo "🔍 Checking test server health..."
echo ""

# Test 1: Check main admin page
echo "Test 1: Admin dashboard"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$TEST_SERVER/wp-admin/")
if [ "$STATUS" = "302" ] || [ "$STATUS" = "200" ]; then
    echo "✅ Admin page responds (HTTP $STATUS)"
else
    echo "❌ Admin page error (HTTP $STATUS)"
fi

# Test 2: Check plugin page
echo ""
echo "Test 2: WPShadow admin page"
RESPONSE=$(curl -s "$TEST_SERVER/wp-admin/admin.php?page=wpshadow" 2>&1)
if echo "$RESPONSE" | grep -qi "parse error\|fatal error\|syntax error"; then
    echo "❌ Parse error detected on page"
    echo "$RESPONSE" | grep -i "error" | head -3
else
    echo "✅ No obvious errors detected"
fi

# Test 3: Check REST API
echo ""
echo "Test 3: REST API"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$TEST_SERVER/wp-json/wp/v2/")
if [ "$STATUS" = "200" ] || [ "$STATUS" = "401" ]; then
    echo "✅ REST API responds (HTTP $STATUS)"
else
    echo "❌ REST API error (HTTP $STATUS)"
fi

echo ""
echo "✅ Health check complete!"
