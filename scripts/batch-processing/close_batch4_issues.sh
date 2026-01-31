#!/bin/bash

# Close issues #1826-#1835 (batch 4 comment notification diagnostics)

for i in 1826 1827 1828 1829 1830 1831 1832 1833 1834 1835; do
  curl -X PATCH \
    -H "Authorization: token $GITHUB_TOKEN" \
    -H "Accept: application/vnd.github.v3+json" \
    https://api.github.com/repos/thisismyurl/wpshadow/issues/$i \
    -d '{"state":"closed"}' \
    -s > /dev/null && echo "✓ Closed #$i"
done

echo ""
echo "✅ Closed 10 batch 4 issues (#1826-#1835)"
