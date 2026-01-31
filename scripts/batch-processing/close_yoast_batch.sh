#!/bin/bash
# Close Yoast SEO diagnostic issues #3693-3690

ISSUES=(3693 3692 3691 3690)
REPO="thisismyurl/wpshadow"
COMMENT="This diagnostic has been implemented and is available in the codebase. Closing as complete."

for ISSUE in "${ISSUES[@]}"; do
    echo "Closing issue #${ISSUE}..."
    
    # Post comment
    curl -s -X POST \
        -H "Authorization: token ${GITHUB_TOKEN}" \
        -H "Accept: application/vnd.github.v3+json" \
        https://api.github.com/repos/${REPO}/issues/${ISSUE}/comments \
        -d "{\"body\":\"${COMMENT}\"}" > /dev/null
    
    sleep 0.3
    
    # Close issue
    curl -s -X PATCH \
        -H "Authorization: token ${GITHUB_TOKEN}" \
        -H "Accept: application/vnd.github.v3+json" \
        https://api.github.com/repos/${REPO}/issues/${ISSUE} \
        -d '{"state":"closed"}' > /dev/null
    
    echo "✅ Closed #${ISSUE}"
    sleep 0.5
done

echo ""
echo "📊 Closed 4 Yoast SEO diagnostics: #3693-3690"
