#!/bin/bash
# Setup script for MLCampbell podcast generator
# This script helps configure the ElevenLabs API key for local development

set -e

echo "=================================================="
echo "MLCampbell - Setup Script"
echo "=================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env file exists
if [ -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file already exists${NC}"
    read -p "Do you want to reconfigure? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Skipping configuration..."
        exit 0
    fi
fi

echo "To use MLCampbell with audio generation, you need an ElevenLabs API key."
echo ""
echo "Steps:"
echo "  1. Go to: https://elevenlabs.io/app/settings/api-keys"
echo "  2. Sign up for a free account if needed"
echo "  3. Copy your API key"
echo ""
read -p "Paste your ElevenLabs API key: " api_key

if [ -z "$api_key" ]; then
    echo -e "${RED}✗ API key cannot be empty${NC}"
    exit 1
fi

# Create .env file
cat > .env <<EOF
# ElevenLabs Configuration
ELEVENLABS_API_KEY=$api_key
DEBUG=false
EOF

echo ""
echo -e "${GREEN}✓ .env file created${NC}"
echo ""

# Test the key
echo "Testing API key..."
php -r "
require 'ElevenLabsIntegration.php';
\$api = new ElevenLabsIntegration('$api_key');
if (\$api->testConnection()) {
    echo \"✓ Connection successful!\\n\";
    \$info = \$api->getAccountInfo();
    if (\$info) {
        echo \"  Account: \" . (\$info['subscription']['tier'] ?? 'Free') . \"\\n\";
        echo \"  Characters used: \" . (\$info['character_count'] ?? '0') . \"\\n\";
    }
} else {
    echo \"✗ Connection failed. Check your API key.\\n\";
    exit(1);
}
"

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}Setup complete!${NC}"
    echo ""
    echo "Next steps:"
    echo "  1. Generate a script:"
    echo "     php PodcastScriptGenerator.php"
    echo ""
    echo "  2. Convert to audio:"
    echo "     php ElevenLabsIntegration.php"
    echo ""
    echo "  3. Find your audio files in:"
    echo "     audio_segments/"
else
    echo ""
    echo -e "${RED}Setup failed. Check your API key.${NC}"
    exit 1
fi
