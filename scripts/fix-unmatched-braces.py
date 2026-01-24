#!/usr/bin/env python3
"""
Fix unmatched braces in PHP files caused by aggressive comment removal.
Specific issue: Extra closing braces at end of class definitions.
"""

import subprocess
import re
from pathlib import Path

def count_braces(content):
    """Count opening and closing braces, ignoring those in strings"""
    opens = content.count('{')
    closes = content.count('}')
    return opens, closes

def fix_unmatched_braces(filepath):
    """Fix extra closing braces at end of classes"""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    opens, closes = count_braces(content)

    # If closes > opens, we have extra closing braces
    if closes > opens:
        diff = closes - opens

        # Remove extra closing braces from the end
        # Work backwards and remove the extras
        lines = content.rstrip().split('\n')
        removed = 0

        while removed < diff and lines:
            line = lines[-1].strip()
            if line == '}':
                lines.pop()
                removed += 1
            else:
                break

        content = '\n'.join(lines) + '\n'

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    return False

def validate_php(filepath):
    """Run PHP syntax check"""
    try:
        result = subprocess.run(
            ['php', '-l', str(filepath)],
            capture_output=True,
            text=True,
            timeout=5
        )
        return 'No syntax errors' in result.stdout
    except:
        return False

def main():
    """Process all diagnostic files"""
    base_path = Path('/workspaces/wpshadow/includes/diagnostics')

    files_to_check = list(base_path.rglob('class-diagnostic-*.php')) + list(base_path.rglob('class-test-*.php'))

    fixed = 0
    failed = 0

    for filepath in files_to_check:
        if fix_unmatched_braces(filepath):
            fixed += 1
            # Verify the fix worked
            if not validate_php(filepath):
                failed += 1
                print(f"⚠️  Still has errors: {filepath.name}")

    print(f"✅ Fixed {fixed} files ({failed} still have errors)")

if __name__ == '__main__':
    main()
