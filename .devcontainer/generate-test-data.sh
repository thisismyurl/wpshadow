#!/bin/bash

echo "🎲 Generating test data for WPShadow..."

# Create test users with different roles
wp user create editor editor@example.com --role=editor --user_pass=editor --allow-root
wp user create author author@example.com --role=author --user_pass=author --allow-root
wp user create subscriber sub@example.com --role=subscriber --user_pass=subscriber --allow-root

# Generate posts
wp post generate --count=50 --allow-root

# Generate comments
wp comment generate --count=100 --allow-root

# Create pages
wp post create --post_type=page --post_title='About' --post_status=publish --allow-root
wp post create --post_type=page --post_title='Contact' --post_status=publish --allow-root

# Install and activate test theme
wp theme install twentytwentyfour --activate --allow-root

echo "✅ Test data generated!"
echo "Test users:"
echo "  - admin/admin (administrator)"
echo "  - editor/editor (editor)"
echo "  - author/author (author)"
echo "  - sub/subscriber (subscriber)"
