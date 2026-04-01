#!/usr/bin/env bash
set -euo pipefail

# Sync source plugin files into dist/wpshadow for release packaging.
# This is intentionally opinionated to avoid shipping dev-only artifacts.

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DIST_DIR="$ROOT_DIR/dist/wpshadow"

mkdir -p "$DIST_DIR"

rsync -a --delete \
  --exclude='.git/' \
  --exclude='dist/' \
  --exclude='vendor/' \
  --exclude='node_modules/' \
  --exclude='tests/' \
  --exclude='dev-tools/' \
  --exclude='.devcontainer/' \
  --exclude='.github/' \
  --exclude='docker-compose.yml' \
  --exclude='docker-*.sh' \
  --exclude='watch-and-deploy.sh' \
  --exclude='deploy-ftp.sh' \
  --exclude='Makefile.devcontainer' \
  --exclude='phpunit.xml' \
  --exclude='playwright.config.js' \
  --exclude='package*.json' \
  --exclude='composer.lock' \
  --exclude='backup.sql' \
  "$ROOT_DIR/" "$DIST_DIR/"

echo "Synced source files to $DIST_DIR"
