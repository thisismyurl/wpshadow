#!/bin/bash

# WPShadow Docker Testing Environment Reset Script
# WARNING: This will delete all WordPress data and start fresh

echo "⚠️  WARNING: This will delete ALL WordPress data and start fresh!"
echo ""
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Reset cancelled."
    exit 0
fi

echo ""
echo "🗑️  Stopping containers and removing volumes..."
docker compose down -v

echo "🧹 Cleaning up..."
docker volume prune -f

echo "🚀 Starting fresh WordPress installation..."
./docker-start.sh

echo ""
echo "✅ Environment reset complete!"
echo "   Visit your WordPress URL to complete the fresh installation."
