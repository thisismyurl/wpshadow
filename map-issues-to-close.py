#!/usr/bin/env python3
"""
Map production diagnostic slugs to GitHub issue numbers
and generate bulk close script
"""

import json
import re
from pathlib import Path

# Load production slugs
with open('/tmp/production_slugs.txt', 'r') as f:
    production_slugs = set(line.replace('PRODUCTION: ', '').strip() for line in f if line.strip())

print(f"Loaded {len(production_slugs)} production slugs")

# Create title variations for each slug
def slug_to_title_variations(slug):
    """Generate possible title variations from slug"""
    # Convert slug to title case
    title = slug.replace('-', ' ').title()
    
    # Generate variations
    variations = [
        title,
        title.lower(),
        slug,
        slug.replace('-', ' '),
        slug.replace('-', '_'),
    ]
    
    return variations

# Map slugs to potential issue titles
slug_to_titles = {}
for slug in production_slugs:
    slug_to_titles[slug] = slug_to_title_variations(slug)

# For now, let's create a script that will check each issue
# We'll need the GitHub CLI or API to actually match and close

print("\n=== Production Slugs Ready for Closing ===")
print(f"Total: {len(production_slugs)}")
print("\nSample slugs:")
for slug in sorted(list(production_slugs))[:20]:
    print(f"  - {slug}")

print(f"\n\n=== GitHub CLI Commands ===")
print("# First, let's fetch all 650 issues and map them")
print('gh issue list --repo thisismyurl/wpshadow --label diagnostic --state open --limit 1000 --json number,title > /tmp/github_issues.json')
