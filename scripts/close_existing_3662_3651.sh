#!/bin/bash

# Close issues with existing implementations
issues=(3662 3659 3656 3654 3651)
comments=(
  "✅ Already implemented: \`includes/diagnostics/tests/plugins/class-diagnostic-woocommerce-subscriptions-payment.php\`"
  "✅ Already implemented: \`includes/diagnostics/tests/plugins/class-diagnostic-woocommerce-abandoned-cart-recovery.php\`"
  "✅ Already implemented: \`includes/diagnostics/tests/plugins/class-diagnostic-memberpress-content-protection.php\`"
  "✅ Already implemented: \`includes/diagnostics/tests/plugins/class-diagnostic-memberpress-payment-gateway-security.php\`"
  "✅ Already implemented: \`includes/diagnostics/tests/plugins/class-diagnostic-restrict-content-pro-content-restrictions.php\`"
)

for i in "${!issues[@]}"; do
  issue="${issues[$i]}"
  comment="${comments[$i]}"

  echo "Closing #$issue..."

  # Add comment
  curl -s -X POST \
    -H "Authorization: token $GITHUB_TOKEN" \
    -H "Accept: application/vnd.github.v3+json" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue/comments" \
    -d "{\"body\":\"$comment\"}" > /dev/null

  # Close issue
  curl -s -X PATCH \
    -H "Authorization: token $GITHUB_TOKEN" \
    -H "Accept: application/vnd.github.v3+json" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue" \
    -d '{"state":"closed"}' > /dev/null

  echo "✓ Closed #$issue"
  sleep 1
done

echo "Completed: 5 issues closed"
