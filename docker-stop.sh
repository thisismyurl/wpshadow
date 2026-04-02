#!/bin/bash

# WPShadow Docker Testing Environment Shutdown Script

echo "🛑 Stopping WPShadow Docker Testing Environment..."
echo ""

# Stop and remove containers
docker compose down

echo ""
echo "✅ All containers stopped and removed."
echo ""
echo "💡 To preserve data, volumes are kept."
echo "   To remove volumes too: docker compose down -v"
echo ""
