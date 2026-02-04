#!/bin/bash
# Batch 6: Performance diagnostics

echo "=== BATCH 6: PERFORMANCE DIAGNOSTICS ==="
echo ""

patterns=(
  "page-speed"
  "load-time"
  "ttfb"
  "first-contentful-paint"
  "largest-contentful-paint"
  "cumulative-layout-shift"
  "first-input-delay"
  "time-to-interactive"
  "core-web-vitals"
  "lighthouse"
  "pagespeed-insights"
  "gtmetrix"
  "webpagetest"
  "caching"
  "cache-control"
  "expires-header"
  "etag"
  "gzip"
  "brotli"
  "compression"
  "minification"
  "concatenation"
  "lazy-load"
  "defer"
  "async"
  "critical-css"
  "above-fold"
  "image-optimization"
  "webp"
  "avif"
  "responsive-images"
  "srcset"
  "cdn"
  "edge-caching"
  "database-query"
  "slow-query"
  "n+1"
  "query-cache"
  "object-cache"
  "transient"
  "opcache"
  "apcu"
  "redis"
  "memcached"
  "varnish"
  "reverse-proxy"
  "http2"
  "http3"
  "quic"
)

found=0
for pattern in "${patterns[@]}"; do
  if find includes/diagnostics/tests/performance/ -name "*.php" | xargs grep -iq "$pattern" 2>/dev/null; then
    echo "✅ $pattern"
    ((found++))
  else
    echo "⚠️  $pattern"
  fi
done

echo ""
echo "Found: $found/50"
