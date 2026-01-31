#!/usr/bin/env python3
"""
Audit script to find and close already-implemented diagnostic issues.
Scans all diagnostic files, matches against GitHub issues, and closes implemented ones.
"""

import os
import re
import subprocess
import requests
import json
from pathlib import Path

# GitHub API setup
GITHUB_TOKEN = os.environ.get('GITHUB_TOKEN', '')
GITHUB_API = 'https://api.github.com/repos/thisismyurl/wpshadow'
HEADERS = {'Authorization': f'token {GITHUB_TOKEN}', 'Accept': 'application/vnd.github.v3+json'}

def extract_diagnostic_slug(file_path):
    """Extract diagnostic slug from PHP file."""
    try:
        with open(file_path, 'r') as f:
            content = f.read()
            match = re.search(r"protected\s+static\s+\$slug\s*=\s*['\"]([^'\"]+)['\"]", content)
            if match:
                return match.group(1)
    except Exception as e:
        print(f"Error reading {file_path}: {e}")
    return None

def get_all_diagnostic_slugs():
    """Get all diagnostic slugs from implemented files."""
    diagnostics = {}
    base_path = Path('/workspaces/wpshadow/includes/diagnostics/tests')
    
    if not base_path.exists():
        print(f"Path not found: {base_path}")
        return diagnostics
    
    for php_file in base_path.rglob('*.php'):
        slug = extract_diagnostic_slug(php_file)
        if slug:
            diagnostics[slug] = str(php_file)
    
    print(f"✓ Found {len(diagnostics)} implemented diagnostics")
    return diagnostics

def get_open_diagnostic_issues(page=1, per_page=100):
    """Get open diagnostic issues from GitHub."""
    try:
        url = f"{GITHUB_API}/issues?labels=diagnostic&state=open&per_page={per_page}&page={page}"
        response = requests.get(url, headers=HEADERS, timeout=10)
        response.raise_for_status()
        return response.json()
    except Exception as e:
        print(f"Error fetching issues: {e}")
        return []

def extract_issue_slug_from_title(title):
    """Try to extract diagnostic slug from issue title."""
    # Remove common prefixes
    title = re.sub(r'^\[Diagnostic\]\s*', '', title, flags=re.IGNORECASE)
    title = re.sub(r'^Diagnostic:\s*', '', title, flags=re.IGNORECASE)
    
    # Convert to slug format
    slug = title.lower()
    slug = re.sub(r'[^\w\s-]', '', slug)  # Remove special chars
    slug = re.sub(r'\s+', '-', slug)      # Replace spaces with hyphens
    slug = re.sub(r'-+', '-', slug)       # Collapse multiple hyphens
    slug = slug.strip('-')                 # Remove leading/trailing hyphens
    
    return slug

def find_matching_issue(slug, open_issues):
    """Find GitHub issue matching a diagnostic slug."""
    for issue in open_issues:
        issue_slug = extract_issue_slug_from_title(issue['title'])
        
        # Exact match or fuzzy match on slug
        if issue_slug == slug or issue['title'].lower().find(slug.replace('-', ' ')) >= 0:
            return issue
        
        # Try reverse: does the issue title contain the slug?
        if slug.replace('-', ' ').lower() in issue['title'].lower():
            return issue
    
    return None

def close_issue(issue_number, slug):
    """Close a GitHub issue."""
    try:
        url = f"{GITHUB_API}/issues/{issue_number}"
        data = {
            'state': 'closed',
            'labels': ['diagnostic', 'implemented'],
            'body': f'Diagnostic already implemented: {slug}'
        }
        response = requests.patch(url, headers=HEADERS, json=data, timeout=10)
        response.raise_for_status()
        return True
    except Exception as e:
        print(f"Error closing issue #{issue_number}: {e}")
        return False

def main():
    """Main audit function."""
    print("=" * 60)
    print("DIAGNOSTIC AUDIT AND CLOSURE")
    print("=" * 60)
    
    # Get implemented diagnostics
    print("\n[1/4] Scanning implemented diagnostics...")
    implemented = get_all_diagnostic_slugs()
    
    # Get open issues
    print("\n[2/4] Fetching open diagnostic issues from GitHub...")
    all_issues = []
    page = 1
    while True:
        issues = get_open_diagnostic_issues(page=page)
        if not issues:
            break
        all_issues.extend(issues)
        page += 1
        if len(all_issues) >= 300:  # Limit to first 300 for now
            break
    
    print(f"✓ Found {len(all_issues)} open diagnostic issues")
    
    # Match and close
    print("\n[3/4] Matching implemented diagnostics to open issues...")
    closed_count = 0
    matched_count = 0
    unmatched_slugs = []
    
    for slug, file_path in sorted(implemented.items()):
        issue = find_matching_issue(slug, all_issues)
        if issue:
            matched_count += 1
            print(f"\n  Matched: {slug}")
            print(f"    Issue #{issue['number']}: {issue['title']}")
            
            # Close the issue
            if close_issue(issue['number'], slug):
                print(f"    ✓ Closed")
                closed_count += 1
            else:
                print(f"    ✗ Failed to close")
        else:
            unmatched_slugs.append(slug)
    
    # Summary
    print("\n[4/4] SUMMARY")
    print("=" * 60)
    print(f"Implemented diagnostics: {len(implemented)}")
    print(f"Open issues: {len(all_issues)}")
    print(f"Matched: {matched_count}")
    print(f"Closed: {closed_count}")
    print(f"Unmatched implementations: {len(unmatched_slugs)}")
    
    if unmatched_slugs:
        print(f"\nUnmatched slugs (no GitHub issue found):")
        for slug in sorted(unmatched_slugs):
            print(f"  - {slug}")
    
    print("\n" + "=" * 60)
    print(f"✓ Audit complete! {closed_count} issues closed.")
    print("=" * 60)

if __name__ == '__main__':
    main()
