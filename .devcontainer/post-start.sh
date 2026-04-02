#!/bin/bash

# WPShadow Post-Start Script
# ===========================
# Runs every time you start your development container.

# Simply delegate to the enhanced post-start script
bash "$(dirname "$0")/post-start-enhanced.sh"
