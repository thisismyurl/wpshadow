#!/bin/bash

# Close duplicate issues
declare -A duplicates=(
  [3650]="Duplicate of earlier WooCommerce issue - diagnostic already implemented"
  [3649]="Duplicate of earlier WooCommerce issue - diagnostic already implemented"
  [3648]="Duplicate of earlier membership issue - diagnostic already implemented"
  [3647]="Duplicate of earlier membership issue - diagnostic already implemented"
)

for issue in 3650 3649 3648 3647; do
  comment="${duplicates[$issue]}"

  echo "Closing duplicate #$issue..."

  # Add comment
  curl -s -X POST \
    -H "Authorization: token $GITHUB_TOKEN" \
    -H "Accept: application/vnd.github.v3+json" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue/comments" \
    -d "{\"body\":\"$comment\"}" > /dev/null

  # Close issue
  curl -s -X PATCH \
    -H "Authorization: token $GITHUB_TOKEN" \
    -H "Accept: application/vnd.github.v3+json" \
    "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue" \
    -d '{"state":"closed"}' > /dev/null

  echo "✓ Closed duplicate #$issue"
  sleep 1
done

echo "Completed: 4 duplicates closed"
