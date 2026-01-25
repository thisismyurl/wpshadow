# WPShadow Development Tools

This directory contains scripts, tools, and resources used for development and automation of the WPShadow plugin. These files are **not** included in the production release package.

## Contents

### GitHub Automation Scripts
- **create-final-labels.sh** - Script to create GitHub labels for issue tracking
- **create-labels-fixed.sh** - Fixed version of label creation script

### Python Development Scripts
- **generate-kb-articles.py** - Generates knowledge base articles
- **test-wp-connection.py** - Tests WordPress connection for development

### Knowledge Base Files
- **kb-articles/** - Source markdown files for knowledge base articles
- **kb-articles-content-output.json** - Generated KB content
- **.kb-index.json** - KB article index

## Usage

These tools are for developers and contributors only. End users installing the plugin from WordPress.org will not see these files.

### Running Scripts

#### GitHub Label Creation
```bash
./create-final-labels.sh
```

#### KB Article Generation
```bash
python3 generate-kb-articles.py
```

#### WordPress Connection Test
```bash
python3 test-wp-connection.py
```

## Development Setup

For development setup instructions, see:
- Main README: `/README.md`
- Development Guide: `/docs/COMPLETE_SETUP_GUIDE.md`
- Docker Setup: `.devcontainer/README.md`

## Exclusion from Release

All files in this directory are excluded from the production release via:
1. `.distignore` file
2. `build-release.sh` script

This ensures only plugin-related files are distributed to end users.
