#!/usr/bin/env python3
"""
Fix orphaned comment closers (closing */ without opening /**)
These were created when we removed verbose comment blocks.
"""

import re
from pathlib import Path

def fix_orphaned_comments(filepath):
    """Remove orphaned */ that don't have matching /**"""
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    fixed_lines = []
    skip_next_close = False

    for i, line in enumerate(lines):
        # Check if this is an orphaned closing comment
        # Pattern: line with just whitespace and */
        if re.match(r'^\s*\*/\s*$', line):
            # Check if there's a matching opening /** before it
            # Look backwards for the most recent opening
            has_opening = False
            for j in range(i - 1, max(0, i - 50), -1):
                if '/**' in lines[j]:
                    has_opening = True
                    break
                # If we hit a closing */ before finding opening, skip this one
                if '*/' in lines[j] and '/**' not in lines[j]:
                    break

            # If no opening found in recent context, this is orphaned - skip it
            if not has_opening:
                continue

        fixed_lines.append(line)

    # Write back if changed
    if len(fixed_lines) != len(lines):
        with open(filepath, 'w', encoding='utf-8') as f:
            f.writelines(fixed_lines)
        return True
    return False

def main():
    """Process all diagnostic files"""
    base_path = Path('/workspaces/wpshadow/includes/diagnostics')

    fixed = 0
    checked = 0

    for filepath in base_path.rglob('class-diagnostic-*.php'):
        checked += 1
        if fix_orphaned_comments(filepath):
            fixed += 1

    for filepath in base_path.rglob('class-test-*.php'):
        checked += 1
        if fix_orphaned_comments(filepath):
            fixed += 1

    print(f"✅ Fixed {fixed} files out of {checked} checked")

if __name__ == '__main__':
    main()
