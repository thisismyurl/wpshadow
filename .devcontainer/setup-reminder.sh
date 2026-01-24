#!/bin/bash
# This file is sourced by .bashrc to display setup status on terminal open
# Add this line to .bashrc: source .devcontainer/setup-reminder.sh

echo ""
echo "╔════════════════════════════════════════════════════════════════════════╗"
echo "║                                                                        ║"
echo "║         ✨ WPSHADOW AUTOMATED SETUP - Everything is Running ✨         ║"
echo "║                                                                        ║"
echo "║  🎯 WHAT YOU NEED TO DO: Open WordPress and start testing!            ║"
echo "║                                                                        ║"
echo "║  📍 WordPress URL:                                                    ║"

# Detect environment
if [ -n "$CODESPACE_NAME" ] && [ -n "$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN" ]; then
    echo "║     http://${CODESPACE_NAME}-9000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}     ║"
else
    echo "║     http://localhost:9000                                             ║"
fi

echo "║                                                                        ║"
echo "║  📦 SERVICES STATUS:                                                  ║"

# Check services silently
MYSQL=$(docker ps 2>/dev/null | grep -c "mysql" || echo "0")
WP=$(docker ps 2>/dev/null | grep -c "wordpress" || echo "0")

if [ "$MYSQL" -gt 0 ]; then
    echo "║     ✓ MySQL running                                                 ║"
else
    echo "║     ✗ MySQL starting...                                             ║"
fi

if [ "$WP" -gt 0 ]; then
    echo "║     ✓ WordPress running                                             ║"
else
    echo "║     ✗ WordPress starting...                                         ║"
fi

echo "║                                                                        ║"
echo "║  🚀 QUICK COMMANDS:                                                   ║"
echo "║     • Logs:        docker-compose logs -f wordpress                    ║"
echo "║     • Status:      docker-compose ps                                   ║"
echo "║     • Restart:     docker-compose restart                              ║"
echo "║     • Reset:       docker-compose down -v && docker-compose up -d     ║"
echo "║                                                                        ║"
echo "║  💡 FORGOT SOMETHING? Read:                                           ║"
echo "║     cat AUTOMATED_SETUP_FOR_FORGETFUL_DEVELOPERS.md                   ║"
echo "║                                                                        ║"
echo "║  📊 SETUP LOGS:                                                       ║"
echo "║     • Latest start:  tail -f /tmp/wpshadow-start.log                   ║"
echo "║     • Latest setup:  cat /tmp/wpshadow-setup.log                       ║"
echo "║                                                                        ║"
echo "╚════════════════════════════════════════════════════════════════════════╝"
echo ""
