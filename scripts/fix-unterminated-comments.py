#!/usr/bin/env python3
"""Fix unterminated comments in PHP files."""

import os
import re
import subprocess

def fix_file(filepath):
    """Fix unterminated comment issues in a PHP file."""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
    except:
        return False

    original_content = content

    # Fix pattern: /* ... multiline comment without closing */ followed by code
    # Look for /* without matching */
    lines = content.split('\n')
    fixed_lines = []
    in_block_comment = False
    comment_start_line = -1

    for i, line in enumerate(lines):
        # Check if this line starts a block comment
        if '/*' in line and '*/' not in line:
            # This might be a multiline comment
            in_block_comment = True
            comment_start_line = i
            fixed_lines.append(line)
        elif '*/' in line and in_block_comment:
            # End of block comment
            in_block_comment = False
            fixed_lines.append(line)
        elif in_block_comment:
            # Inside block comment
            fixed_lines.append(line)
        else:
            # Normal code line
            # If we suddenly find code while supposedly in a comment, close it
            if in_block_comment and line.strip() and not line.strip().startswith('*'):
                # This looks like code, close the comment first
                fixed_lines.insert(len(fixed_lines), '\t\t */')
                in_block_comment = False
            fixed_lines.append(line)

    # If comment still open at end, close it
    if in_block_comment:
        fixed_lines.append('\t\t */')

    fixed_content = '\n'.join(fixed_lines)

    if fixed_content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(fixed_content)

        # Verify the fix with PHP linter
        result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
        if 'No syntax errors' in result.stdout:
            return True
        else:
            # Revert if still broken
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(original_content)
            return False

    return False

def main():
    diagnostic_dir = '/workspaces/wpshadow/includes/diagnostics/tests'
    fixed_count = 0
    total_checked = 0
    failed_files = []

    for filename in sorted(os.listdir(diagnostic_dir)):
        if filename.startswith('class-diagnostic-') and filename.endswith('.php'):
            filepath = os.path.join(diagnostic_dir, filename)
            total_checked += 1

            if fix_file(filepath):
                fixed_count += 1
            else:
                # Check if still broken
                result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
                if 'Parse error' in result.stderr or 'Parse error' in result.stdout:
                    failed_files.append(filename)

    print(f"✅ Fixed {fixed_count} files out of {total_checked} checked")
    if failed_files:
        print(f"⚠️  Still has errors ({len(failed_files)}):")
        for f in failed_files[:10]:
            print(f"    - {f}")
        if len(failed_files) > 10:
            print(f"    ... and {len(failed_files) - 10} more")

if __name__ == '__main__':
    main()
