#!/usr/bin/env python3
"""
Verify all diagnostic files have required settings in return arrays.
"""

import re
import glob

REQUIRED_KEYS = [
    'id',
    'title',
    'description',
    'severity',
    'category',
    'threat_level'
]

def check_file(filepath):
    """Check if a diagnostic file has required settings."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        # Find return array
        matches = re.finditer(r"return\s+(?:array\s*\(|[\[\{])(.*?)(?:\);|[\]\}];?)", content, re.DOTALL)

        missing_keys = set()
        for match in matches:
            array_content = match.group(1)
            for key in REQUIRED_KEYS:
                if f"'{key}'" not in array_content and f'"{key}"' not in array_content:
                    missing_keys.add(key)

        return list(missing_keys) if missing_keys else None
    except Exception as e:
        return [str(e)]

# Process all diagnostics
pattern = "/workspaces/wpshadow/includes/diagnostics/tests/class-diagnostic-*.php"
files = glob.glob(pattern)

missing_count = 0
issues = {}

for filepath in sorted(files):
    missing = check_file(filepath)
    if missing:
        missing_count += 1
        filename = filepath.split('/')[-1]
        issues[filename] = missing

print(f"Checked {len(files)} diagnostic files")
print(f"Files with missing required keys: {missing_count}\n")

if issues:
    print("Sample missing keys:")
    for filename, keys in list(issues.items())[:5]:
        print(f"  {filename}: {', '.join(keys)}")
else:
    print("✅ All diagnostic files have required settings!")
