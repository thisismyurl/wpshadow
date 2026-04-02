#!/bin/bash
# Verification script to check devcontainer setup
# Run this to verify everything is configured correctly

set -e

echo "🔍 WPShadow DevContainer Verification"
echo "======================================"
echo ""

# Check files exist
echo "📋 Checking configuration files..."
FILES_TO_CHECK=(
    ".devcontainer/devcontainer.json"
    ".devcontainer/post-create.sh"
    ".devcontainer/post-start-enhanced.sh"
    "docker-compose.yml"
)

for file in "${FILES_TO_CHECK[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file"
    else
        echo "  ✗ MISSING: $file"
    fi
done

echo ""
echo "🔧 Checking tools..."

# Check for required commands
COMMANDS=("docker" "docker-compose" "curl" "mysql")

for cmd in "${COMMANDS[@]}"; do
    if command -v "$cmd" &> /dev/null; then
        VERSION=$("$cmd" --version 2>&1 | head -1)
        echo "  ✓ $cmd - $VERSION"
    else
        echo "  ? $cmd - not found (will be available in container)"
    fi
done

echo ""
echo "📁 Checking plugin structure..."
if [ -f "wpshadow.php" ]; then
    echo "  ✓ Plugin file found"
    PLUGIN_VERSION=$(grep "Version:" wpshadow.php | head -1 | sed 's/.*Version: //' | tr -d '\r' | awk '{print $1}')
    echo "    Version: $PLUGIN_VERSION"
else
    echo "  ✗ Plugin file not found at root"
fi

echo ""
echo "✅ Verification complete!"
echo ""
echo "Next steps:"
echo "  1. Open in Dev Container (VS Code or GitHub Codespaces)"
echo "  2. Wait for automatic setup (3-5 minutes)"
echo "  3. Access WordPress at http://localhost:8080"
echo ""
