#!/bin/bash
for issue in 3646 3645 3644 3643 3641 3640 3639; do
  echo "Closing #$issue..."
  curl -s -X PATCH -H "Authorization: token $GITHUB_TOKEN" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue" \
    -d '{"state":"closed"}' > /dev/null
  sleep 1
done
echo "✓ Closed 7 issues"
