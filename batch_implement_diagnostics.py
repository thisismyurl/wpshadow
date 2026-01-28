#!/usr/bin/env python3
"""
Batch implement diagnostics from GitHub issues in descending order.
Implements diagnostics and closes completed issues automatically.
"""

import os
import sys
import json
import time
import requests
from typing import Optional, Dict, List

# GitHub configuration
GITHUB_TOKEN = os.environ.get('GITHUB_TOKEN')
REPO_OWNER = 'thisismyurl'
REPO_NAME = 'wpshadow'
HEADERS = {
    'Authorization': f'token {GITHUB_TOKEN}',
    'Accept': 'application/vnd.github.v3+json'
}

# Track progress
completed_issues = []
failed_issues = []

def fetch_diagnostic_issues(start_issue: int, count: int = 100) -> List[Dict]:
    """Fetch diagnostic issues in descending order from start_issue."""
    issues = []
    
    print(f"🔍 Fetching {count} issues starting from #{start_issue}...")
    
    for issue_num in range(start_issue, start_issue - count, -1):
        if issue_num < 1:
            break
            
        url = f'https://api.github.com/repos/{REPO_OWNER}/{REPO_NAME}/issues/{issue_num}'
        
        try:
            response = requests.get(url, headers=HEADERS, timeout=10)
            
            if response.status_code == 200:
                issue = response.json()
                
                # Check if it's a diagnostic issue
                labels = [label['name'] for label in issue.get('labels', [])]
                if 'diagnostic' in labels and issue.get('state') == 'open':
                    issues.append({
                        'number': issue['number'],
                        'title': issue['title'],
                        'body': issue.get('body', ''),
                        'labels': labels
                    })
                    print(f"  ✓ #{issue['number']}: {issue['title']}")
            
            # Rate limiting - be nice to GitHub API
            time.sleep(0.3)
            
        except Exception as e:
            print(f"  ⚠️  Error fetching #{issue_num}: {e}")
            continue
    
    print(f"\n📊 Found {len(issues)} open diagnostic issues")
    return issues

def close_issue(issue_number: int, comment: str) -> bool:
    """Close a GitHub issue with a comment."""
    try:
        # Add comment
        comment_url = f'https://api.github.com/repos/{REPO_OWNER}/{REPO_NAME}/issues/{issue_number}/comments'
        comment_response = requests.post(
            comment_url,
            headers=HEADERS,
            json={'body': comment},
            timeout=10
        )
        
        if comment_response.status_code != 201:
            print(f"  ⚠️  Failed to add comment: {comment_response.status_code}")
            return False
        
        # Close issue
        issue_url = f'https://api.github.com/repos/{REPO_OWNER}/{REPO_NAME}/issues/{issue_number}'
        close_response = requests.patch(
            issue_url,
            headers=HEADERS,
            json={'state': 'closed'},
            timeout=10
        )
        
        if close_response.status_code == 200:
            print(f"  ✅ Closed issue #{issue_number}")
            return True
        else:
            print(f"  ⚠️  Failed to close: {close_response.status_code}")
            return False
            
    except Exception as e:
        print(f"  ❌ Error closing #{issue_number}: {e}")
        return False

def main():
    """Main execution function."""
    if not GITHUB_TOKEN:
        print("❌ GITHUB_TOKEN environment variable not set")
        sys.exit(1)
    
    # Start from issue 3421 (just completed) and go down
    start_issue = 3420
    batch_size = 100
    
    print(f"🚀 Starting batch implementation from issue #{start_issue}")
    print(f"📦 Processing {batch_size} issues per batch\n")
    
    # Fetch issues
    issues = fetch_diagnostic_issues(start_issue, batch_size)
    
    if not issues:
        print("✅ No open diagnostic issues found in this range")
        return
    
    # Save issues list for processing
    output_file = '/tmp/diagnostic_batch_issues.json'
    with open(output_file, 'w') as f:
        json.dump(issues, f, indent=2)
    
    print(f"\n💾 Saved {len(issues)} issues to {output_file}")
    print(f"\n📋 Next steps:")
    print(f"   1. Review issues in {output_file}")
    print(f"   2. Implement diagnostics based on issue descriptions")
    print(f"   3. Close completed issues with verification comments")
    
    # Return first issue for immediate implementation
    if issues:
        first = issues[0]
        print(f"\n🎯 Next issue to implement: #{first['number']}")
        print(f"   Title: {first['title']}")
        print(f"   Labels: {', '.join(first['labels'])}")

if __name__ == '__main__':
    main()
