#!/bin/bash
set -e

echo "🎭 Generating Test Data for WPShadow Development..."

# Wait for WordPress to be ready
until wp core is-installed --allow-root 2>/dev/null; do
    echo "⏳ Waiting for WordPress..."
    sleep 2
done

# Create test users
echo "👥 Creating test users..."
wp user create editor editor@example.com --role=editor --user_pass=editor --allow-root || echo "Editor already exists"
wp user create author author@example.com --role=author --user_pass=author --allow-root || echo "Author already exists"
wp user create contributor contributor@example.com --role=contributor --user_pass=contributor --allow-root || echo "Contributor already exists"
wp user create subscriber subscriber@example.com --role=subscriber --user_pass=subscriber --allow-root || echo "Subscriber already exists"

# Generate test posts
echo "📝 Generating test posts..."
for i in {1..10}; do
    wp post generate --count=1 --post_type=post --post_status=publish --allow-root || true
done

# Generate test pages
echo "📄 Generating test pages..."
for i in {1..5}; do
    wp post generate --count=1 --post_type=page --post_status=publish --allow-root || true
done

# Generate comments
echo "💬 Generating test comments..."
wp comment generate --count=25 --allow-root || true

# Install and activate Twenty Twenty-Four theme (if not already)
echo "🎨 Installing test theme..."
wp theme install twentytwentyfour --activate --allow-root || echo "Theme already installed"

# Install additional test theme
wp theme install twentytwentythree --allow-root || echo "Theme already installed"

echo "✅ Test data generation complete!"
echo ""
echo "📊 Summary:"
wp user list --allow-root
echo ""
echo "Posts: $(wp post list --post_type=post --format=count --allow-root || echo '0')"
echo "Pages: $(wp post list --post_type=page --format=count --allow-root || echo '0')"
echo "Comments: $(wp comment list --format=count --allow-root || echo '0')"
