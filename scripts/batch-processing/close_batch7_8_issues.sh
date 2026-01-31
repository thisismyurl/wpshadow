#!/bin/bash

# Close batch 7 (8 issues)
for i in 1854 1855 1856 1857 1858 1859 1860 1861; do
  curl -X PATCH -H "Authorization: token $GITHUB_TOKEN" -H "Accept: application/vnd.github.v3+json" \
    https://api.github.com/repos/thisismyurl/wpshadow/issues/$i -d '{"state":"closed"}' -s > /dev/null
done

# Close batch 8 (9 issues)
for i in 1862 1863 1864 1865 1866 1867 1868 1869 1870; do
  curl -X PATCH -H "Authorization: token $GITHUB_TOKEN" -H "Accept: application/vnd.github.v3+json" \
    https://api.github.com/repos/thisismyurl/wpshadow/issues/$i -d '{"state":"closed"}' -s > /dev/null
done

echo "✅ Closed batch 7 & 8 (17 issues total)"
