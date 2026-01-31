#!/bin/bash

for i in 1846 1847 1848 1849 1850 1851 1852 1853; do
  curl -X PATCH \
    -H "Authorization: token $GITHUB_TOKEN" \
    -H "Accept: application/vnd.github.v3+json" \
    https://api.github.com/repos/thisismyurl/wpshadow/issues/$i \
    -d '{"state":"closed"}' \
    -s > /dev/null && echo "✓ Closed #$i"
done

echo "✅ Closed batch 6 issues (#1846-#1853)"
