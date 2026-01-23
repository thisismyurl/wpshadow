#!/bin/bash
# KB & Training Search Tool
# Quick access to knowledge base and training resources

set -e

if [ $# -lt 1 ]; then
    echo "Usage: kb <keyword>"
    echo "  Examples: kb ssl, kb performance, kb security"
    echo ""
    echo "Or: kb-training <topic>"
    echo ""
    exit 1
fi

KEYWORD="$1"

# Read KB index
KB_FILE="/workspaces/wpshadow/.kb-index.json"

if [ ! -f "$KB_FILE" ]; then
    echo "Error: .kb-index.json not found"
    exit 1
fi

echo "🔍 Searching KB for: $KEYWORD"
echo ""

# Search KB articles
jq -r ".kb_articles[] | select(.topics | map(test(\"$KEYWORD\"; \"i\")) | any) | \"📖 \\(.title)\\n   \\(.url)\\n\"" "$KB_FILE" 2>/dev/null || true

# Search training videos
jq -r ".training_videos[] | select(.title | test(\"$KEYWORD\"; \"i\") or .topics | map(test(\"$KEYWORD\"; \"i\")) | any) | \"🎓 \\(.title) (\\(.duration), \\(.level))\\n   \\(.url)\\n\"" "$KB_FILE" 2>/dev/null || true

echo ""
echo "💡 Tip: Link these in get_description() and treatment apply() for education"
