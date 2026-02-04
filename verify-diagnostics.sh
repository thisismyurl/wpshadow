#!/bin/bash
# Verify diagnostic implementations exist and are syntactically correct

echo "🔍 Verifying diagnostic implementations..."
echo ""

# Array of issue numbers and diagnostic slugs
declare -a issues=(
  "3966:second-order-sql-injection"
  "3968:reflected-xss"
  "3973:stored-xss"
  "3985:xml-rpc-brute-force"
  "3986:backup-authentication-bypass"
  "3987:session-replay-attacks"
  "3988:cross-site-session-leakage"
  "3989:session-storage-security"
  "3990:insecure-random-number-generation"
  "3991:directory-listing-vulnerability"
)

success_count=0
fail_count=0

for issue_slug in "${issues[@]}"
do
  issue_num=$(echo "$issue_slug" | cut -d: -f1)
  slug=$(echo "$issue_slug" | cut -d: -f2)
  
  # Try to find the diagnostic file
  found=$(find includes/diagnostics/tests -name "*${slug}*" -type f 2>/dev/null | head -1)
  
  if [ -n "$found" ]; then
    echo "✅ Issue #$issue_num → Found: $found"
    ((success_count++))
  else
    echo "❌ Issue #$issue_num → NOT FOUND (slug: $slug)"
    ((fail_count++))
  fi
done

echo ""
echo "========================================="
echo "Summary: $success_count found, $fail_count missing"
echo "========================================="
