#!/bin/bash
# Make all scripts in scripts/ executable
# Run this anytime you add new scripts to the directory

echo "🔧 Making all scripts executable..."

# Make all .sh and .py files executable
find /workspaces/wpshadow/scripts -type f \( -name "*.sh" -o -name "*.py" \) -exec chmod +x {} \;

# Also handle dev-tools if needed
if [ -d "/workspaces/wpshadow/dev-tools" ]; then
    find /workspaces/wpshadow/dev-tools -type f \( -name "*.sh" -o -name "*.py" \) -exec chmod +x {} \;
fi

echo "✅ Done! All scripts are now executable."
