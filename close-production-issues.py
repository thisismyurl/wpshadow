#!/usr/bin/env python3
"""
Close GitHub Issues for Verified Production Diagnostics

This script uses the GitHub API to bulk close issues that have
verified production implementations.
"""

import json
import os
from pathlib import Path

# Load production slugs
with open('/tmp/production_slugs.txt', 'r') as f:
    production_slugs = set(line.replace('PRODUCTION: ', '').strip() for line in f if line.strip())

print(f"✅ Loaded {len(production_slugs)} production diagnostic slugs")
print()

# GitHub API configuration
GITHUB_TOKEN = os.getenv('GITHUB_TOKEN', '')
REPO_OWNER = 'thisismyurl'
REPO_NAME = 'wpshadow'

# Create slug variations for matching
def create_slug_variations(slug):
    """Create different variations of a slug for matching"""
    variations = set([slug])
    
    # Add with spaces
    variations.add(slug.replace('-', ' '))
    
    # Add title case
    title_case = slug.replace('-', ' ').title()
    variations.add(title_case)
    
    # Add without dashes/underscores
    variations.add(slug.replace('-', '').replace('_', ''))
    
    return variations

print("=" * 80)
print("PRODUCTION DIAGNOSTIC SLUGS READY TO CLOSE")
print("=" * 80)
print()

# Group by category
categories = {}
for slug in sorted(production_slugs):
    # Try to infer category from slug
    if any(word in slug for word in ['admin', 'dashboard', 'backend']):
        category = 'Admin'
    elif any(word in slug for word in ['security', 'auth', 'password', 'sql', 'xss']):
        category = 'Security'
    elif any(word in slug for word in ['performance', 'cache', 'optimize', 'speed']):
        category = 'Performance'
    elif any(word in slug for word in ['database', 'db', 'query', 'table']):
        category = 'Database'
    elif any(word in slug for word in ['html', 'seo', 'meta', 'sitemap']):
        category = 'HTML/SEO'
    elif any(word in slug for word in ['email', 'smtp', 'mail']):
        category = 'Email'
    elif any(word in slug for word in ['backup', 'restore']):
        category = 'Backup'
    elif any(word in slug for word in ['cron', 'schedule']):
        category = 'Cron'
    else:
        category = 'Other'
    
    if category not in categories:
        categories[category] = []
    categories[category].append(slug)

for category, slugs in sorted(categories.items()):
    print(f"\n📁 {category}: {len(slugs)} diagnostics")
    for slug in slugs[:5]:
        print(f"   - {slug}")
    if len(slugs) > 5:
        print(f"   ... and {len(slugs) - 5} more")

print()
print("=" * 80)
print(f"TOTAL PRODUCTION DIAGNOSTICS: {len(production_slugs)}")
print("=" * 80)
print()

# Generate GitHub CLI commands to close issues
print("### GitHub CLI Commands to Close Production Issues ###")
print()
print("# Note: This is a template. You'll need to manually match slugs to issue numbers")
print("# Or fetch all issues via API and match programmatically")
print()
print("# Example for manual closing:")
for slug in sorted(list(production_slugs))[:10]:
    print(f"# gh issue close <issue_number> --repo {REPO_OWNER}/{REPO_NAME} --comment '✅ Verified: Production implementation complete for {slug}'")

print()
print("# To generate full list, fetch all 650 issues and match by title")
print("# Then use the GitHub API to bulk close")
