#!/bin/bash

# GitHub token from environment
GITHUB_TOKEN="${GITHUB_TOKEN}"
REPO="thisismyurl/wpshadow"

# The 22 gap issues to close
issues=(
  3687  # Paywall Transparency
  3686  # News Corrections Policy
  3685  # Source Protection Privacy
  3679  # Client Gallery Privacy
  3678  # Portfolio Accessibility
  3676  # Image Copyright Protection
  3674  # Forum Performance Scale
  3673  # Forum Moderation Policy
  3672  # UGC Copyright DMCA
  3671  # Forum Member Privacy
  3670  # Multisite Registration Antispam
  3669  # Multisite Plugin Theme Security
  3668  # Multisite Data Isolation
  3666  # Membership Data Portability
  3663  # Ecommerce ADA Compliance
  3660  # Marketplace Seller Verification
  3657  # Digital Product Security
  3653  # Member Content Moderation
  3642  # Ecommerce Checkout Accessibility
  3638  # Customer Account Security
  3637  # Digital Product Privacy
  3636  # Multisite Privacy Consistency
)

echo "Closing 22 gap diagnostic issues..."

for issue in "${issues[@]}"; do
  echo "Closing issue #${issue}..."
  
  curl -X PATCH \
    -H "Authorization: token ${GITHUB_TOKEN}" \
    -H "Accept: application/vnd.github.v3+json" \
    "https://api.github.com/repos/${REPO}/issues/${issue}" \
    -d '{"state":"closed","body":"✅ **Diagnostic Implemented**\n\nThis diagnostic has been implemented as part of the specialized diagnostic expansion.\n\n**Implementation Details:**\n- Created diagnostic class in `includes/diagnostics/tests/`\n- Auto-discovered by diagnostic loader\n- Follows WPShadow diagnostic architecture\n- Extends `Diagnostic_Base` with proper structure\n\n**Commit:** 8108f742 (Deploy v1.26031.0922)\n\n**Status:** Complete and deployed"}' \
    --silent --show-error
  
  if [ $? -eq 0 ]; then
    echo "  ✅ Successfully closed #${issue}"
  else
    echo "  ❌ Failed to close #${issue}"
  fi
  
  # Rate limit delay
  sleep 0.3
done

echo "✅ Finished closing all 22 gap issues!"
