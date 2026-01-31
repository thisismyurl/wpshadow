#!/bin/bash

# Batch processor for GitHub issues
# Checks if diagnostics exist and closes issues

START=${1:-3635}
END=${2:-3625}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Processing issues #$START → #$END"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

closed_count=0
needs_impl_count=0
declare -a needs_impl

for issue in $(seq $START -1 $END); do
  title=$(curl -s -H "Authorization: token $GITHUB_TOKEN" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue" | jq -r '.title')
  
  if [ "$title" == "null" ] || [ -z "$title" ]; then
    echo "⏭️  #$issue - Not found/closed"
    continue
  fi
  
  # Extract keywords from title for search
  keywords=$(echo "$title" | tr '[:upper:]' '[:lower:]' | sed 's/diagnostic #[0-9]*: //g')
  
  # Quick search for diagnostic files
  found=0
  
  # Search common patterns
  if echo "$keywords" | grep -qi "woocommerce\|e-commerce\|ecommerce"; then
    if find /workspaces/wpshadow/includes/diagnostics/tests -type f -name "*woocommerce*" -o -name "*ecommerce*" | head -1 | grep -q "."; then
      found=1
    fi
  fi
  
  if echo "$keywords" | grep -qi "multisite\|network"; then
    if find /workspaces/wpshadow/includes/diagnostics/tests/multisite -type f -name "*.php" | head -1 | grep -q "."; then
      found=1
    fi
  fi
  
  if echo "$keywords" | grep -qi "subscription"; then
    if find /workspaces/wpshadow/includes/diagnostics/tests -type f -name "*subscription*" | head -1 | grep -q "."; then
      found=1
    fi
  fi
  
  if echo "$keywords" | grep -qi "shipping\|coupon\|discount"; then
    if find /workspaces/wpshadow/includes/diagnostics/tests -type f -name "*shipping*" -o -name "*coupon*" -o -name "*discount*" | head -1 | grep -q "."; then
      found=1
    fi
  fi
  
  if [ $found -eq 1 ]; then
    echo "✅ #$issue: $title"
    
    # Close the issue
    curl -s -X PATCH -H "Authorization: token $GITHUB_TOKEN" \
      "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue" \
      -d '{"state":"closed"}' > /dev/null
    
    closed_count=$((closed_count + 1))
    sleep 0.5
  else
    echo "❌ #$issue: $title"
    needs_impl+=("#$issue - $title")
    needs_impl_count=$((needs_impl_count + 1))
  fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 BATCH RESULTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Closed: $closed_count"
echo "❌ Need Implementation: $needs_impl_count"
echo ""

if [ $needs_impl_count -gt 0 ]; then
  echo "Issues needing implementation:"
  for item in "${needs_impl[@]}"; do
    echo "  $item"
  done
fi
