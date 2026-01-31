#!/bin/bash

# WPShadow KB Article Creator Script
# Creates all KB articles referenced in the plugin as draft posts
# Uses WordPress REST API with Basic Auth

# Configuration
SITE_URL="https://wpshadow.com"
WP_USER="github"
WP_PASSWORD="github"
CATEGORY_ID="3"  # Adjust if needed for KB category
STATUS="draft"

# Create temporary file for KB links
KB_FILE="/tmp/kb_links.txt"

# Extract all KB links from plugin
echo "📝 Extracting KB links from plugin..."
grep -r "wpshadow.com/kb" /workspaces/wpshadow/includes --include="*.php" | \
  grep -o "wpshadow.com/kb/[a-z0-9-]*" | sort | uniq > "$KB_FILE"

TOTAL=$(wc -l < "$KB_FILE")
echo "✅ Found $TOTAL KB articles to create"
echo ""

# Counter
CREATED=0
FAILED=0
SKIPPED=0

# Process each KB link
while IFS= read -r kb_link; do
  # Extract slug from KB link
  SLUG=$(echo "$kb_link" | sed 's|.*wpshadow.com/kb/||')
  
  # Generate title from slug (convert hyphens to spaces, capitalize)
  TITLE=$(echo "$SLUG" | sed 's/-/ /g' | sed 's/\b\(.\)/\U\1/g')
  
  # Generate basic content
  CONTENT="<h2>$TITLE</h2>
<p>This knowledge base article covers: $SLUG</p>
<p><!-- Add comprehensive content here --></p>
<h3>Key Points:</h3>
<ul>
<li>Implementation details</li>
<li>Best practices</li>
<li>Troubleshooting tips</li>
</ul>
<h3>Resources:</h3>
<ul>
<li><a href=\"https://wpshadow.com/kb/$SLUG\">Knowledge Base Article</a></li>
<li>Related articles</li>
</ul>"
  
  # Create JSON payload
  PAYLOAD=$(cat <<EOF
{
  "title": "$TITLE",
  "content": "$CONTENT",
  "status": "$STATUS",
  "slug": "$SLUG",
  "categories": [$CATEGORY_ID]
}
EOF
)
  
  # URL encode password for Basic Auth
  ENCODED_PASS=$(python3 -c "import urllib.parse; print(urllib.parse.quote('$WP_PASSWORD'))")
  
  # Make API request
  RESPONSE=$(curl -s -w "\n%{http_code}" \
    -X POST "$SITE_URL/wp-json/wp/v2/posts" \
    -u "$WP_USER:$WP_PASSWORD" \
    -H "Content-Type: application/json" \
    -d "$PAYLOAD")
  
  # Extract status code
  HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
  BODY=$(echo "$RESPONSE" | head -n-1)
  
  if [ "$HTTP_CODE" = "201" ]; then
    POST_ID=$(echo "$BODY" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    echo "✅ [$((++CREATED))/$TOTAL] Created: $SLUG (Post ID: $POST_ID)"
  elif [ "$HTTP_CODE" = "400" ] && echo "$BODY" | grep -q "already exists"; then
    echo "⏭️  [$((++SKIPPED))/$TOTAL] Skipped: $SLUG (already exists)"
  else
    echo "❌ [$((++FAILED))/$TOTAL] Failed: $SLUG (HTTP $HTTP_CODE)"
    echo "   Error: $(echo "$BODY" | grep -o '"message":"[^"]*' | head -1)"
  fi
  
done < "$KB_FILE"

echo ""
echo "================================"
echo "📊 Summary:"
echo "✅ Created: $CREATED"
echo "⏭️  Skipped: $SKIPPED"
echo "❌ Failed: $FAILED"
echo "📈 Total: $TOTAL"
echo "================================"

# Cleanup
rm "$KB_FILE"
