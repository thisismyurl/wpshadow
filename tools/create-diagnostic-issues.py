#!/usr/bin/env python3
"""
Create GitHub Issues for All 26 Diagnostics
Parses the copy-paste ready format and creates issues via GitHub API
"""

import subprocess
import re
import sys
from pathlib import Path


def run_command(cmd):
    """Run shell command and return output"""
    try:
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True, check=True)
        return True, result.stdout.strip()
    except subprocess.CalledProcessError as e:
        return False, e.stderr.strip()


def check_gh_cli():
    """Verify gh CLI is installed and authenticated"""
    success, _ = run_command("gh auth status")
    if not success:
        print("❌ GitHub CLI not authenticated. Run: gh auth login")
        sys.exit(1)
    print("✅ GitHub CLI authenticated\n")


def parse_issues_from_file(file_path):
    """Parse issues from markdown file"""
    content = Path(file_path).read_text()
    
    issues = []
    
    # Split by the numbered copy-paste sections
    sections = re.split(r"^## \d+️⃣", content, flags=re.MULTILINE)
    
    for section in sections[1:]:  # Skip header
        lines = section.strip().split('\n')
        if len(lines) < 3:
            continue
        
        # Extract title from first line
        title_line = lines[0].strip()
        title_match = re.search(r"Title:\s*`?([^`\n]+)`?", title_line)
        if not title_match:
            continue
        title = title_match.group(1).strip()
        
        # Extract labels
        labels_line = lines[1].strip() if len(lines) > 1 else ""
        labels_match = re.search(r"Labels:\s*`?([^`\n]+)`?", labels_line)
        labels = labels_match.group(1).strip() if labels_match else "diagnostic,enhancement"
        
        # Extract body (everything after the markdown code block opening)
        body_start = section.find("```\n") + 4
        body_end = section.find("\n```", body_start)
        
        if body_start > 3 and body_end > body_start:
            body = section[body_start:body_end].strip()
            
            if title and body:
                issues.append({
                    'title': title,
                    'body': body,
                    'labels': labels
                })
    
    return issues


def create_issue(repo, title, body, labels):
    """Create a single GitHub issue"""
    # Escape body for shell
    body_escaped = body.replace('"', '\\"').replace('`', '\\`').replace('$', '\\$')
    
    # Build gh command
    cmd = f'''gh issue create \\
        --repo "{repo}" \\
        --title "{title}" \\
        --body "{body_escaped}" \\
        --label "{labels}"'''
    
    success, output = run_command(cmd)
    
    if success:
        # Extract issue number
        match = re.search(r"#(\d+)", output)
        issue_num = match.group(1) if match else "?"
        return True, issue_num
    else:
        return False, output


def main():
    repo = "thisismyurl/wpshadow"
    issues_file = "docs/GITHUB_ISSUES_COPY_PASTE_READY.md"
    
    print("🔍 Checking GitHub CLI...")
    check_gh_cli()
    
    print(f"📖 Parsing issues from {issues_file}...")
    issues = parse_issues_from_file(issues_file)
    print(f"   Found {len(issues)} issues\n")
    
    if not issues:
        print("❌ No issues found in file")
        sys.exit(1)
    
    print("🚀 Creating GitHub issues...\n")
    
    created = 0
    failed = 0
    
    for i, issue in enumerate(issues, 1):
        title = issue['title'][:70]
        success, result = create_issue(repo, issue['title'], issue['body'], issue['labels'])
        
        if success:
            print(f"  {i:2d}. ✅ {title:70s} #{result}")
            created += 1
        else:
            print(f"  {i:2d}. ❌ {title:70s}")
            print(f"      Error: {result[:100]}")
            failed += 1
    
    print("\n" + "="*80)
    print(f"✅ Successfully created {created} issues")
    if failed > 0:
        print(f"⚠️  Failed to create {failed} issues")
    
    print("\n📊 Next steps:")
    print(f"   1. Review issues at: https://github.com/{repo}/issues")
    print("   2. Create project board to track progress")
    print("   3. Prioritize Phase 1 (Security) issues")
    print("   4. Assign to team members")
    print()


if __name__ == "__main__":
    main()
