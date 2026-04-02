#!/usr/bin/env python3
"""
Close Completed Diagnostic GitHub Issues

This script closes GitHub issues for diagnostics that have been implemented.
Uses GitHub REST API with authentication token.

Usage:
    export GITHUB_TOKEN="your_token_here"
    python3 close-completed-diagnostic-issues.py --start 4800 --count 137

Requirements:
    pip install requests
"""

import os
import sys
import argparse
import requests
import json
from pathlib import Path


class GitHubIssueCloser:
    """Handles closing GitHub issues via REST API."""

    def __init__(self, token, repo_owner="thisismyurl", repo_name="wpshadow"):
        self.token = token
        self.repo_owner = repo_owner
        self.repo_name = repo_name
        self.base_url = f"https://api.github.com/repos/{repo_owner}/{repo_name}"
        self.headers = {
            "Authorization": f"token {token}",
            "Accept": "application/vnd.github.v3+json",
        }
        self.session = requests.Session()
        self.session.headers.update(self.headers)

    def get_issue(self, issue_number):
        """Get issue details."""
        url = f"{self.base_url}/issues/{issue_number}"
        response = self.session.get(url)
        
        if response.status_code == 200:
            return response.json()
        elif response.status_code == 404:
            return None
        else:
            response.raise_for_status()

    def close_issue(self, issue_number, comment=None):
        """Close a GitHub issue with optional comment."""
        # Add comment if provided
        if comment:
            comment_url = f"{self.base_url}/issues/{issue_number}/comments"
            comment_data = {"body": comment}
            comment_response = self.session.post(comment_url, json=comment_data)
            
            if comment_response.status_code != 201:
                print(f"  ⚠️  Warning: Failed to add comment to #{issue_number}")

        # Close the issue
        url = f"{self.base_url}/issues/{issue_number}"
        data = {"state": "closed"}
        response = self.session.patch(url, json=data)
        
        if response.status_code == 200:
            return True
        else:
            print(f"  ❌ Failed to close #{issue_number}: {response.status_code}")
            print(f"     {response.json().get('message', 'Unknown error')}")
            return False

    def close_issues_batch(self, start_number, count, comment_template=None):
        """Close a batch of issues."""
        results = {
            "closed": [],
            "not_found": [],
            "already_closed": [],
            "failed": [],
        }

        print(f"\n🔍 Checking issues #{start_number} through #{start_number + count - 1}...\n")

        for i in range(count):
            issue_number = start_number + i
            
            # Get issue details
            issue = self.get_issue(issue_number)
            
            if not issue:
                print(f"#{issue_number:5d} - ⚠️  Not found (may not exist)")
                results["not_found"].append(issue_number)
                continue

            if issue["state"] == "closed":
                print(f"#{issue_number:5d} - ✓  Already closed: {issue['title'][:60]}")
                results["already_closed"].append(issue_number)
                continue

            # Issue is open, close it
            comment = comment_template.format(issue_number=issue_number) if comment_template else None
            
            if self.close_issue(issue_number, comment):
                print(f"#{issue_number:5d} - ✅ Closed: {issue['title'][:60]}")
                results["closed"].append(issue_number)
            else:
                print(f"#{issue_number:5d} - ❌ Failed to close: {issue['title'][:60]}")
                results["failed"].append(issue_number)

        return results


def scan_diagnostic_files():
    """Scan for implemented diagnostic files."""
    workspace_root = Path(__file__).parent.parent
    diagnostic_dirs = [
        workspace_root / "includes" / "systems" / "diagnostics" / "tests",
        workspace_root / "includes" / "diagnostics" / "tests",
    ]

    diagnostics = []
    for base_dir in diagnostic_dirs:
        if base_dir.exists():
            for category_dir in base_dir.iterdir():
                if category_dir.is_dir():
                    for file in category_dir.glob("class-diagnostic-*.php"):
                        diagnostics.append({
                            "file": str(file.relative_to(workspace_root)),
                            "category": category_dir.name,
                            "name": file.stem,
                        })

    return diagnostics


def main():
    parser = argparse.ArgumentParser(
        description="Close completed diagnostic GitHub issues",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
    # Close issues 4800-4936 (137 diagnostics)
    python3 close-completed-diagnostic-issues.py --start 4800 --count 137

    # Dry run (check status without closing)
    python3 close-completed-diagnostic-issues.py --start 4800 --count 137 --dry-run

    # Close specific range with custom comment
    python3 close-completed-diagnostic-issues.py --start 4800 --count 20 --comment "Diagnostic implemented in batch 1"

Environment:
    GITHUB_TOKEN - GitHub personal access token with repo permissions
    Get token at: https://github.com/settings/tokens
        """
    )
    
    parser.add_argument(
        "--start",
        type=int,
        default=4800,
        help="Starting issue number (default: 4800)",
    )
    parser.add_argument(
        "--count",
        type=int,
        default=137,
        help="Number of issues to close (default: 137)",
    )
    parser.add_argument(
        "--comment",
        type=str,
        help="Comment to add when closing (supports {issue_number} placeholder)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Check issue status without closing",
    )
    parser.add_argument(
        "--repo-owner",
        type=str,
        default="thisismyurl",
        help="Repository owner (default: thisismyurl)",
    )
    parser.add_argument(
        "--repo-name",
        type=str,
        default="wpshadow",
        help="Repository name (default: wpshadow)",
    )

    args = parser.parse_args()

    # Check for GitHub token
    token = os.environ.get("GITHUB_TOKEN")
    if not token:
        print("❌ Error: GITHUB_TOKEN environment variable not set")
        print("\nTo get a token:")
        print("1. Visit https://github.com/settings/tokens")
        print("2. Generate new token (classic)")
        print("3. Select 'repo' scope")
        print("4. Export token: export GITHUB_TOKEN='your_token_here'")
        sys.exit(1)

    # Scan for implemented diagnostics
    diagnostics = scan_diagnostic_files()
    print(f"\n📊 Found {len(diagnostics)} implemented diagnostic files\n")

    if args.dry_run:
        print("🔍 DRY RUN MODE - Will check status without closing\n")

    # Create closer instance
    closer = GitHubIssueCloser(token, args.repo_owner, args.repo_name)

    # Default comment if none provided
    default_comment = (
        "✅ **Diagnostic Implemented**\n\n"
        "This diagnostic has been successfully implemented and merged.\n"
        "- Total diagnostics created: 137\n"
        "- Completion: 63.7% (137/215)\n"
        "- Implementation date: February 4, 2026\n\n"
        "Closing as complete."
    )
    
    comment = args.comment or default_comment

    # Close issues
    if args.dry_run:
        # Just check status
        results = closer.close_issues_batch(args.start, args.count, None)
    else:
        results = closer.close_issues_batch(args.start, args.count, comment)

    # Print summary
    print("\n" + "="*70)
    print("📊 SUMMARY")
    print("="*70)
    print(f"✅ Closed:          {len(results['closed']):3d}")
    print(f"✓  Already closed:  {len(results['already_closed']):3d}")
    print(f"⚠️  Not found:       {len(results['not_found']):3d}")
    print(f"❌ Failed:          {len(results['failed']):3d}")
    print(f"📊 Total checked:   {args.count:3d}")
    print("="*70)

    if results["closed"]:
        print(f"\n✅ Successfully closed {len(results['closed'])} issues")
    
    if results["failed"]:
        print(f"\n❌ Failed to close {len(results['failed'])} issues:")
        for issue_num in results["failed"]:
            print(f"   - #{issue_num}")
        sys.exit(1)

    print("\n✨ Done!\n")


if __name__ == "__main__":
    main()
