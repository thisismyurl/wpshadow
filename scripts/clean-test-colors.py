#!/usr/bin/env python3
"""
Clean color references from all test files.
"""

import re
import glob

def remove_colors(content):
    """Remove color and bg_color entries."""
    # Handle various spacing and quote combinations
    content = re.sub(r",?\s*'color'\s*=>\s*'#[0-9a-fA-F]+',?", "", content)
    content = re.sub(r",?\s*'bg_color'\s*=>\s*'#[0-9a-fA-F]+',?", "", content)
    content = re.sub(r',?\s*"color"\s*=>\s*"#[0-9a-fA-F]+",?', "", content)
    content = re.sub(r',?\s*"bg_color"\s*=>\s*"#[0-9a-fA-F]+",?', "", content)

    # Clean up double commas
    content = re.sub(r",\s*,", ",", content)

    return content

def process_file(filepath):
    """Process a single file."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        original = content
        content = remove_colors(content)

        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {filepath} - {e}")
        return False

# Process all test files
pattern = "/workspaces/wpshadow/includes/diagnostics/tests/class-test-*.php"
files = glob.glob(pattern)

cleaned = 0
for filepath in sorted(files):
    if process_file(filepath):
        cleaned += 1

print(f"✅ Cleaned {cleaned} test files")
