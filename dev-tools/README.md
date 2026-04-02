# WPShadow Development Tools

This directory contains resources for the WPShadow plugin. These files are **not** included in the production release package.

## Contents

### Knowledge Base Files
- **kb-articles/** - Source markdown files for knowledge base articles (79 articles)
- **kb-articles-content-output.json** - Generated KB content
- **.kb-index.json** - KB article index

### WordPress Content
- **wp-content/** - WordPress content directory for development and KB publishing

## KB Article Structure

KB articles are organized by category:
- accessibility/
- design/
- developer/
- enterprise/
- marketing/
- performance/
- privacy/
- security/
- seo/
- user-experience/

See [kb-articles/README.md](kb-articles/README.md) for KB article documentation.

## Development Setup

For development setup instructions, see:
- Main README: `/README.md`
- Development Guide: `/docs/COMPLETE_SETUP_GUIDE.md`
- Development Environment: `.devcontainer/README.md`

## Exclusion from Release

All files in this directory are excluded from the production release via:
1. `.distignore` file
2. `build-release.sh` script

This ensures only plugin-related files are distributed to end users.
