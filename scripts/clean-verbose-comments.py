#!/usr/bin/env python3
"""
Properly remove verbose comment blocks from diagnostic files.
"""

import re
from pathlib import Path

def clean_file(filepath):
    """Remove verbose comment blocks while preserving file structure."""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original_len = len(content)

    # Pattern 1: Remove DIAGNOSTIC GOAL CLARIFICATION /** ... */ blocks
    # This handles multi-line comment blocks that contain this pattern
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*DIAGNOSTIC GOAL CLARIFICATION.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 2: Remove TEST IMPLEMENTATION ... /** ... */ blocks
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*TEST IMPLEMENTATION.*?\*/',
        '',
        content,
        flags=re.DOTALL | re.IGNORECASE
    )

    # Pattern 3: Remove HTML ASSESSMENT TEST /** ... */ blocks
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*HTML ASSESSMENT TEST.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 4: Remove NEEDS CLARIFICATION /** ... */ blocks
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*NEEDS CLARIFICATION:?.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 5: Remove DIAGNOSTIC ANALYSIS /** ... */ blocks
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*DIAGNOSTIC ANALYSIS.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 6: Remove orphaned comment markers (/** on its own line)
    content = re.sub(r'^\s*/\*\*\s*$', '', content, flags=re.MULTILINE)

    # Pattern 7: Clean up multiple consecutive blank lines (more than 2)
    content = re.sub(r'\n\n\n+', '\n\n', content)

    # Only write if changed
    if len(content) != original_len:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    return False

def main():
    """Process all diagnostic test files."""
    base_path = Path('/workspaces/wpshadow/includes/diagnostics/tests')
    files = sorted(base_path.glob('class-diagnostic-*.php'))

    cleaned = 0
    for filepath in files:
        if clean_file(filepath):
            cleaned += 1

    print(f"✅ Cleaned {cleaned} out of {len(files)} diagnostic files")

if __name__ == '__main__':
    main()
