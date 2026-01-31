#!/bin/bash
# Close diagnostics #3680-3684, 3688-3689 (7 issues)

ISSUES=(3689 3688 3684 3683 3682 3681 3680)
REPO="thisismyurl/wpshadow"
COMMENT="This diagnostic has been implemented and is available in the codebase. Closing as complete."

for ISSUE in "${ISSUES[@]}"; do
    echo "Closing #${ISSUE}..."
    
    curl -s -X POST \
        -H "Authorization: token ${GITHUB_TOKEN}" \
        -H "Accept: application/vnd.github.v3+json" \
        https://api.github.com/repos/${REPO}/issues/${ISSUE}/comments \
        -d "{\"body\":\"${COMMENT}\"}" > /dev/null
    
    sleep 0.3
    
    curl -s -X PATCH \
        -H "Authorization: token ${GITHUB_TOKEN}" \
        -H "Accept: application/vnd.github.v3+json" \
        https://api.github.com/repos/${REPO}/issues/${ISSUE} \
        -d '{"state":"closed"}' > /dev/null
    
    echo "✅ #${ISSUE}"
    sleep 0.4
done

echo ""
echo "📊 Closed 7 diagnostics: #3689, #3688, #3684-#3680"
echo "⚠️  Skipped 3 (need implementation): #3687 (Paywall), #3686 (Corrections), #3685 (Whistleblower)"
