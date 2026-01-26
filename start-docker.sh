#!/bin/bash
# Quick script to start WordPress on port 9000 after Codespace rebuild

echo "🐳 Checking Docker availability..."
if ! command -v docker &> /dev/null; then
    echo "❌ Docker not found. Please rebuild the Codespace first."
    echo "   Press Ctrl+Shift+P → 'Codespaces: Rebuild Container'"
    exit 1
fi

echo "✅ Docker found: $(docker --version)"
echo ""
echo "🚀 Starting WordPress on port 9000..."
cd /workspaces/wpshadow/dev-tools
docker compose up -d

echo ""
echo "✅ WordPress starting..."
echo "📍 URL: Check the Ports tab for the forwarded URL (port 9000)"
echo ""
echo "⏳ Wait 30 seconds, then access WordPress to complete setup"
echo ""
echo "📊 View logs: docker compose logs -f"
echo "🛑 Stop: docker compose down"
