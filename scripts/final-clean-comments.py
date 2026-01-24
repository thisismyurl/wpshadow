#!/usr/bin/env python3
"""
Clean verbose comment blocks from diagnostic files using exact patterns.
Only removes complete blocks that we can match precisely.
"""

import re
from pathlib import Path

def remove_block(content, marker):
    """
    Remove /** ... */ blocks that contain a specific marker.
    Ensures we match complete blocks and don't break code.
    """
    # Match: /** followed by newline, then any content containing marker, then */
    # Using non-greedy matching to get the nearest closing */
    pattern = rf'/\*\*.*?{re.escape(marker)}.*?\*/'
    return re.sub(pattern, '', content, flags=re.DOTALL)

def clean_file(filepath):
    """Clean a single diagnostic file."""
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    in_block = False
    skip_until_close = False
    block_start = None
    output_lines = []

    i = 0
    while i < len(lines):
        line = lines[i]

        # Check if we're starting a verbose block
        if '/**' in line and i + 1 < len(lines):
            # Look ahead to see if this is a verbose block
            next_lines_text = ''.join(lines[i:min(i+30, len(lines))])

            if any(marker in next_lines_text for marker in [
                'DIAGNOSTIC GOAL CLARIFICATION',
                'TEST IMPLEMENTATION',
                'HTML ASSESSMENT TEST',
                'NEEDS CLARIFICATION',
                'DIAGNOSTIC ANALYSIS'
            ]):
                # Skip this entire block until we find the closing */
                skip_until_close = True
                i += 1
                while i < len(lines) and '*/' not in lines[i]:
                    i += 1
                if i < len(lines):
                    i += 1  # Skip the line with */
                continue

        # Regular line - keep it
        output_lines.append(line)
        i += 1

    # Clean up multiple consecutive blank lines
    final_lines = []
    blank_count = 0
    for line in output_lines:
        if line.strip() == '':
            blank_count += 1
            if blank_count <= 2:  # Allow max 2 consecutive blanks
                final_lines.append(line)
        else:
            blank_count = 0
            final_lines.append(line)

    # Write back
    with open(filepath, 'w', encoding='utf-8') as f:
        f.writelines(final_lines)

    return len(output_lines) < len(lines)

def main():
    """Process all diagnostic files."""
    base_path = Path('/workspaces/wpshadow/includes/diagnostics/tests')
    files = sorted(base_path.glob('class-diagnostic-*.php'))

    cleaned = 0
    for filepath in files:
        if clean_file(filepath):
            cleaned += 1

    print(f"✅ Cleaned {cleaned} out of {len(files)} diagnostic files")

if __name__ == '__main__':
    main()
