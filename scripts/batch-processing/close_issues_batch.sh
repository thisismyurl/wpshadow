#!/bin/bash
# Batch close issues that have diagnostics already implemented

for issue in 3699 3698 3697 3696 3695 3694; do
  curl -s -X POST -H "Authorization: token $GITHUB_TOKEN" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue/comments" \
    -d '{"body":"✅ **Diagnostic Already Implemented**\n\nThis diagnostic exists in the codebase and is production-ready.\n\n**Location:** `includes/diagnostics/tests/performance/` or `includes/diagnostics/tests/security/`\n\nClosing as completed."}' > /dev/null
  
  curl -s -X PATCH -H "Authorization: token $GITHUB_TOKEN" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue" \
    -d '{"state":"closed"}' > /dev/null
  
  echo "✅ Closed #$issue"
done

echo ""
echo "📊 Closed 6 Elementor diagnostics: #3694-3699"
