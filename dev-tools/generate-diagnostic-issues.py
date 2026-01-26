#!/usr/bin/env python3
"""
Generate GitHub issues for diagnostic stub files.
Usage: ./generate-diagnostic-issues.py [OPTIONS]

Options:
    --batch N       Create only N issues at a time (default: all)
    --start N       Start from issue number N (default: 1)
    --filter TEXT   Only create issues matching pattern
    --dry-run       Show what would be created without creating issues
"""

import os
import sys
import re
import json
import argparse
from pathlib import Path
import subprocess

def is_stub_file(file_path):
    """Check if a PHP file is a stub (contains 'stub' comment or minimal implementation)."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
            # Check for stub indicators
            if '@stub' in content.lower() or 'stub implementation' in content.lower():
                return True
            # Check if check() method is essentially empty or placeholder
            if 'public static function check()' in content:
                # Extract the check method
                match = re.search(r'public static function check\(\).*?\{(.*?)\n\s*\}', content, re.DOTALL)
                if match:
                    method_body = match.group(1).strip()
                    # If method only returns null or a placeholder, it's a stub
                    if method_body == 'return null;' or 'TODO' in method_body or len(method_body) < 50:
                        return True
            return False
    except Exception as e:
        print(f"Error reading {file_path}: {e}", file=sys.stderr)
        return False

def extract_diagnostic_info(file_path):
    """Extract information from a diagnostic PHP file."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Extract class name
        class_match = re.search(r'class (Diagnostic_\w+)', content)
        class_name = class_match.group(1) if class_match else 'Unknown'
        
        # Extract namespace
        namespace_match = re.search(r'namespace ([^;]+);', content)
        namespace = namespace_match.group(1) if namespace_match else 'Unknown'
        
        # Extract description from docblock
        docblock_match = re.search(r'/\*\*(.*?)\*/', content, re.DOTALL)
        description = ''
        if docblock_match:
            doc_lines = []
            for line in docblock_match.group(1).split('\n'):
                line = line.strip().lstrip('*').strip()
                if line and not line.startswith('@'):
                    doc_lines.append(line)
            description = ' '.join(doc_lines).strip()
        
        if not description:
            # Create a default description from filename
            basename = os.path.basename(file_path)
            name = basename.replace('class-diagnostic-', '').replace('.php', '').replace('-', ' ').title()
            description = f"Tests the {name} diagnostic functionality"
        
        # Check if auto-fixable
        auto_fixable = 'auto_fixable.*=>.*true' in content
        
        return {
            'class_name': class_name,
            'namespace': namespace,
            'description': description,
            'auto_fixable': auto_fixable,
            'filename': os.path.basename(file_path)
        }
    except Exception as e:
        print(f"Error extracting info from {file_path}: {e}", file=sys.stderr)
        return None

def create_issue_body(info, diagnostic_name):
    """Generate the issue body content."""
    return f"""## Diagnostic Information

**File:** `{info['filename']}`  
**Class:** `{info['class_name']}`  
**Namespace:** `{info['namespace']}`  
**Auto-fixable:** {'Yes' if info['auto_fixable'] else 'Unknown'}  
**Status:** 🚧 Stub Implementation

## Description

{info['description']}

## Implementation Requirements

This diagnostic is currently a stub and needs to be fully implemented.

### Core Functionality
- [ ] Implement the `check()` method with proper detection logic
- [ ] Define appropriate threat level assessment
- [ ] Return proper finding data structure when issues detected
- [ ] Return `null` when no issues found
- [ ] Handle edge cases and error conditions gracefully

### Data Structure
The `check()` method should return:
```php
return array(
    'id'          => self::$slug,
    'title'       => self::$title,
    'description' => __( 'Issue description', 'wpshadow' ),
    'severity'    => 'low|medium|high|critical',
    'threat_level' => 0-100,
    'auto_fixable' => true|false,
    'kb_link'     => 'https://wpshadow.com/kb/article-slug',
);
```

### Testing Requirements
- [ ] Create test scenarios for positive detection
- [ ] Create test scenarios for negative detection (no false positives)
- [ ] Test edge cases and boundary conditions
- [ ] Verify integration with WPShadow dashboard
- [ ] Test WP-CLI command: `wp wpshadow diagnostic run {diagnostic_name}`

### Documentation
- [ ] Complete PHPDoc blocks for class and methods
- [ ] Add inline comments for complex logic
- [ ] Ensure WPCS compliance
- [ ] Update feature matrix documentation

### Security & Standards
- [ ] All database queries use `$wpdb->prepare()`
- [ ] All output properly escaped with `esc_html()`, `esc_attr()`, etc.
- [ ] All input sanitized with appropriate functions
- [ ] Follow WordPress Coding Standards (Yoda conditions, tabs, snake_case)
- [ ] No security vulnerabilities introduced

## Implementation Steps

1. **Research**: Understand what this diagnostic should detect
2. **Design**: Plan the detection logic and threat level calculation
3. **Implement**: Write the `check()` method
4. **Test**: Verify all scenarios work correctly
5. **Document**: Complete all documentation
6. **Review**: Ensure code quality and standards compliance

## Related Files

- **Diagnostic:** `includes/diagnostics/{info['filename']}`
- **Treatment:** `includes/treatments/class-treatment-{diagnostic_name}.php` (if auto-fixable)
- **Tests:** `tests/diagnostics/test-diagnostic-{diagnostic_name}.php` (to be created)

## Success Criteria

- [ ] Diagnostic correctly identifies the intended condition
- [ ] No false positives in testing
- [ ] Proper error handling implemented
- [ ] All code follows WPShadow coding standards
- [ ] Documentation is complete and accurate
- [ ] Integration tests pass
- [ ] No PHPCS violations

---

**Priority:** Medium  
**Type:** Implementation  
**Component:** Diagnostics  
**Effort:** Medium
"""

def create_github_issue(repo, title, body, labels, dry_run=False):
    """Create a GitHub issue using the GitHub API."""
    if dry_run:
        print(f"  [DRY RUN] Would create: {title}")
        return True
    
    # Get GitHub token from environment
    token = os.environ.get('GITHUB_TOKEN') or os.environ.get('GH_TOKEN')
    if not token:
        print(f"  ⚠️  Skipping (no GITHUB_TOKEN): {title}")
        return False
    
    try:
        # Use curl to create the issue via GitHub API
        api_url = f"https://api.github.com/repos/{repo}/issues"
        
        payload = {
            'title': title,
            'body': body,
            'labels': labels
        }
        
        cmd = [
            'curl', '-X', 'POST',
            '-H', f'Authorization: token {token}',
            '-H', 'Accept: application/vnd.github.v3+json',
            '-H', 'Content-Type: application/json',
            '--silent',
            '--show-error',
            api_url,
            '-d', json.dumps(payload)
        ]
        
        result = subprocess.run(cmd, capture_output=True, text=True)
        
        if result.returncode == 0:
            try:
                response = json.loads(result.stdout)
                if 'html_url' in response:
                    print(f"  ✅ Created: {title}")
                    print(f"     {response['html_url']}")
                    return True
                else:
                    print(f"  ❌ Failed: {title}")
                    print(f"     Response: {result.stdout[:200]}")
                    return False
            except json.JSONDecodeError:
                print(f"  ❌ Failed to parse response: {title}")
                print(f"     Output: {result.stdout[:200]}")
                return False
        else:
            print(f"  ❌ Failed: {title}")
            print(f"     Error: {result.stderr}")
            return False
    except Exception as e:
        print(f"  ❌ Error creating issue: {e}")
        return False

def main():
    parser = argparse.ArgumentParser(description='Generate GitHub issues for diagnostic stubs')
    parser.add_argument('--batch', type=int, default=0, help='Create only N issues')
    parser.add_argument('--start', type=int, default=1, help='Start from issue number N')
    parser.add_argument('--filter', type=str, default='', help='Filter pattern')
    parser.add_argument('--dry-run', action='store_true', help='Show what would be created')
    parser.add_argument('--repo', type=str, default='thisismyurl/wpshadow', help='GitHub repo')
    
    args = parser.parse_args()
    
    # Find diagnostics directory
    script_dir = Path(__file__).parent
    repo_root = script_dir.parent.parent
    diagnostics_dir = repo_root / 'includes' / 'diagnostics'
    
    if not diagnostics_dir.exists():
        print(f"Error: Diagnostics directory not found: {diagnostics_dir}")
        sys.exit(1)
    
    # Find all diagnostic files
    all_files = sorted(diagnostics_dir.rglob('class-diagnostic-*.php'))
    
    # Filter for stubs only
    stub_files = [f for f in all_files if is_stub_file(f)]
    
    # Apply text filter if provided
    if args.filter:
        stub_files = [f for f in stub_files if args.filter.lower() in str(f).lower()]
    
    total = len(stub_files)
    print(f"Found {total} stub diagnostic files")
    
    if args.dry_run:
        print("🔍 DRY RUN MODE - No issues will be created\n")
    
    if args.batch > 0:
        end_at = args.start + args.batch - 1
        print(f"Creating batch: {args.start} to {end_at} (max {args.batch} issues)\n")
    else:
        print(f"Creating all issues starting from #{args.start}\n")
    
    count = 0
    created = 0
    
    for file_path in stub_files:
        count += 1
        
        # Skip if before start position
        if count < args.start:
            continue
        
        # Stop if batch limit reached
        if args.batch > 0 and created >= args.batch:
            break
        
        # Extract diagnostic name from filename
        filename = file_path.name
        diagnostic_name = filename.replace('class-diagnostic-', '').replace('.php', '')
        
        # Convert to title case
        title = diagnostic_name.replace('-', ' ').title()
        
        # Extract file info
        info = extract_diagnostic_info(file_path)
        if not info:
            print(f"[{count}/{total}] ⚠️  Skipping {title} (could not extract info)")
            continue
        
        print(f"[{count}/{total}] Processing: {title}")
        
        # Create issue body
        issue_body = create_issue_body(info, diagnostic_name)
        issue_title = f"Implement Diagnostic: {title}"
        labels = ['diagnostics', 'stub-implementation', 'needs-implementation']
        
        # Create the issue
        success = create_github_issue(args.repo, issue_title, issue_body, labels, args.dry_run)
        
        if success:
            created += 1
        
        # Small delay to avoid rate limiting (not in dry-run)
        if not args.dry_run:
            import time
            time.sleep(1)
    
    print(f"\n{'Would create' if args.dry_run else 'Created'} {created} issues (processed {count} files)")

if __name__ == '__main__':
    main()
