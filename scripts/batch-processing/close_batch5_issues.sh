#!/bin/bash

for i in 1836 1837 1838 1839 1840 1841 1842 1843 1844 1845; do
  curl -X PATCH \
    -H "Authorization: token $GITHUB_TOKEN" \
    -H "Accept: application/vnd.github.v3+json" \
    https://api.github.com/repos/thisismyurl/wpshadow/issues/$i \
    -d '{"state":"closed"}' \
    -s > /dev/null && echo "✓ Closed #$i"
done

echo "✅ Closed batch 5 issues (#1836-#1845)"
