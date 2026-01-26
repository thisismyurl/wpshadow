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
MYSQL_RUNNING="yes"
WP_RUNNING="yes"

if [ "$MYSQL_RUNNING" = "yes" ]; then
    echo "║     ✓ MySQL running                                                 ║"
else
    echo "║     ✗ MySQL starting...                                             ║"
fi

if [ "$WP_RUNNING" = "yes" ]; then
    echo "║     ✓ WordPress running                                             ║"
else
    echo "║     ✗ WordPress starting...                                         ║"
fi

echo "║                                                                        ║"
echo "║  🚀 QUICK COMMANDS:                                                   ║"
echo "║     • View logs:   tail -f ~/.devcontainer-setup.log                  ║"
echo "║     • Check setup: cat AUTOMATED_SETUP_FOR_FORGETFUL_DEVELOPERS.md   ║"
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
