#!/usr/bin/env python3
"""
Audit GitHub issues against diagnostic implementations
Maps each GitHub issue to its implementation status (production, stub, or missing)
"""

import re
import json
from pathlib import Path

# Load stub and production slugs
with open('/tmp/stub_slugs.txt', 'r') as f:
    stub_slugs = set(line.replace('STUB: ', '').strip() for line in f if line.strip())

with open('/tmp/production_slugs.txt', 'r') as f:
    production_slugs = set(line.replace('PRODUCTION: ', '').strip() for line in f if line.strip())

# GitHub issues data (650 issues)
# We'll process the title to find matching diagnostic slugs
github_issues = []

# Read all diagnostic files to create title -> slug mapping
title_to_slug = {}
diagnostic_dir = Path('includes/diagnostics/tests')

for php_file in diagnostic_dir.rglob('*.php'):
    try:
        content = php_file.read_text()
        
        # Extract slug
        slug_match = re.search(r'protected static \$slug\s*=\s*[\'"]([^\'"]+)[\'"]', content)
        # Extract title
        title_match = re.search(r'protected static \$title\s*=\s*[\'"]([^\'"]+)[\'"]', content)
        
        if slug_match and title_match:
            slug = slug_match.group(1)
            title = title_match.group(1)
            title_to_slug[title.lower()] = slug
            # Also map without special chars
            clean_title = re.sub(r'[^a-z0-9\s]', '', title.lower())
            title_to_slug[clean_title] = slug
    except Exception as e:
        pass

print("=== Diagnostic Implementation Audit ===\n")
print(f"Total diagnostic files: {len(stub_slugs) + len(production_slugs)}")
print(f"Production (working): {len(production_slugs)}")
print(f"Stubs (TODO): {len(stub_slugs)}")
print(f"Title mappings created: {len(title_to_slug)}")
print()

# Sample check: print some production slugs
print("=== Production Diagnostic Slugs (Sample) ===")
for slug in list(production_slugs)[:10]:
    print(f"✅ {slug}")

print()
print("=== Stub Diagnostic Slugs (Sample) ===")
for slug in list(stub_slugs)[:10]:
    print(f"⚠️ {slug}")
