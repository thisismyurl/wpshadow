#!/bin/bash

# Check issues 3646 to 3636
echo "Checking issues #3646 to #3636..."
echo ""

# Issues to check (reverse order for display)
for issue in 3646 3645 3644 3643 3642 3641 3640 3639 3638 3637 3636; do
  title=$(curl -s -H "Authorization: token $GITHUB_TOKEN" "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue" | grep -o '"title":"[^"]*"' | cut -d'"' -f4 | head -1)
  
  # Extract plugin/feature name and search for diagnostic file
  # Common patterns to check based on typical issue titles
  slug=$(echo "$title" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g' | sed 's/--*/-/g' | sed 's/^-//;s/-$//')
  
  # Search for potential diagnostic files
  found=0
  if find /workspaces/wpshadow/includes/diagnostics/tests -type f -name "*${slug}*.php" 2>/dev/null | grep -q .; then
    found=1
    file=$(find /workspaces/wpshadow/includes/diagnostics/tests -type f -name "*${slug}*.php" 2>/dev/null | head -1)
    echo "✅ #$issue EXISTS: $title"
    echo "   File: ${file#/workspaces/wpshadow/}"
  else
    echo "❌ #$issue MISSING: $title"
  fi
  echo ""
done
