#!/usr/bin/env python3
"""
Remove verbose comment blocks from diagnostic files.

Patterns to remove:
- DIAGNOSTIC GOAL CLARIFICATION
- TEST IMPLEMENTATION NEEDED
- HTML ASSESSMENT TEST
- NEEDS CLARIFICATION
- STUB implementation comments (specific format)
"""

import os
import re
from pathlib import Path

def remove_verbose_comments(filepath):
    """Remove verbose comment blocks from a diagnostic file."""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original_content = content

    # Pattern 1: DIAGNOSTIC GOAL CLARIFICATION blocks (multiline comment blocks)
    # Matches: /** ... DIAGNOSTIC GOAL CLARIFICATION ... */
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*DIAGNOSTIC GOAL CLARIFICATION\s*\n.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 2: TEST IMPLEMENTATION NEEDED/STRATEGY blocks
    # Matches: /** ... TEST IMPLEMENTATION ... */
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*TEST IMPLEMENTATION.*?\*/',
        '',
        content,
        flags=re.DOTALL | re.IGNORECASE
    )

    # Pattern 3: HTML ASSESSMENT TEST blocks
    # Matches: /** ... HTML ASSESSMENT TEST ... */
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*HTML ASSESSMENT TEST.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 4: NEEDS CLARIFICATION blocks
    # Matches: /** ... NEEDS CLARIFICATION ... */
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*NEEDS CLARIFICATION:.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 5: DETECTION STRATEGY blocks
    # Matches: /** ... DETECTION STRATEGY ... */
    content = re.sub(
        r'/\*\*\s*\n\s*\*\s*DIAGNOSTIC ANALYSIS.*?\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Pattern 6: Remove extra blank lines created by removing blocks
    # Multiple consecutive blank lines become double newline
    content = re.sub(r'\n\s*\n\s*\n\s*\n+', '\n\n', content)

    # Pattern 7: Remove STUB comments with specific pattern (// STUB: ... with multi-line notes)
    # This one is trickier because it's single-line comments followed by multi-line
    content = re.sub(
        r'\s*// STUB:.*?\n.*?training gaps\.\n',
        '\n',
        content,
        flags=re.DOTALL
    )

    # Pattern 8: Remove standalone stub comments at the end
    content = re.sub(
        r'(\s*// STUB: Implement.*?\n.*?(?:KB article|disaster prevented|revenue impact).*?\n)',
        '\n',
        content,
        flags=re.DOTALL
    )

    # Write back only if changes were made
    if content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    return False

def main():
    """Process all diagnostic files."""
    diagnostics_dir = Path('/workspaces/wpshadow/includes/diagnostics/tests')

    diagnostic_files = sorted(diagnostics_dir.glob('class-diagnostic-*.php'))

    removed_count = 0
    for filepath in diagnostic_files:
        if remove_verbose_comments(filepath):
            removed_count += 1

    print(f"✅ Cleaned {removed_count} files out of {len(diagnostic_files)}")

if __name__ == '__main__':
    main()
