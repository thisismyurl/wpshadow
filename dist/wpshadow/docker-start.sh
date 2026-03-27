#!/bin/bash

# WPShadow Docker Testing Environment Startup Script
# Configured for GitHub Codespaces

echo "🚀 Starting WPShadow Docker Testing Environment..."
echo ""

# Start Docker Compose
docker compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to start (this may take a minute)..."
sleep 10

# Check if we're in Codespaces
if [ -n "$CODESPACE_NAME" ]; then
    # Codespace environment
    WORDPRESS_URL="https://${CODESPACE_NAME}-8080.app.github.dev"
    PHPMYADMIN_URL="https://${CODESPACE_NAME}-8081.app.github.dev"
    
    echo ""
    echo "✅ Services started successfully in GitHub Codespace!"
    echo ""
    echo "📌 Access your WordPress site:"
    echo "   🌐 WordPress: $WORDPRESS_URL"
    echo "   🔧 Admin: $WORDPRESS_URL/wp-admin"
    echo "   📊 phpMyAdmin: $PHPMYADMIN_URL"
    echo ""
    echo "🔑 Database Credentials:"
    echo "   Host: db:3306"
    echo "   Database: wordpress"
    echo "   Username: wordpress"
    echo "   Password: wordpress"
    echo ""
    echo "🔐 WordPress Initial Setup:"
    echo "   When you first visit WordPress, create an admin account."
    echo "   The WPShadow plugin is already mounted at:"
    echo "   /var/www/html/wp-content/plugins/wpshadow"
    echo ""
    echo "📝 Quick Commands:"
    echo "   Stop: docker-compose down"
    echo "   Restart: docker-compose restart"
    echo "   Logs: docker-compose logs -f wordpress"
    echo "   Shell: docker-compose exec wordpress bash"
    echo ""
else
    # Local environment
    WORDPRESS_URL="http://localhost:8080"
    PHPMYADMIN_URL="http://localhost:8081"
    
    echo ""
    echo "✅ Services started successfully!"
    echo ""
    echo "📌 Access your WordPress site:"
    echo "   🌐 WordPress: $WORDPRESS_URL"
    echo "   🔧 Admin: $WORDPRESS_URL/wp-admin"
    echo "   📊 phpMyAdmin: $PHPMYADMIN_URL"
    echo ""
fi

# Check container status
echo "📦 Container Status:"
docker compose ps

echo ""
echo "✨ Ready to test WPShadow!"
