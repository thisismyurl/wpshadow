#!/usr/bin/env python3
"""
Analyze diagnostic duplicates - compare content and class names.

This script:
1. Finds all duplicate diagnostic files
2. Compares their content and class names
3. Identifies which are true duplicates vs. variations
4. Suggests consolidation strategy
"""

import os
import hashlib
from pathlib import Path
from collections import defaultdict

BASE_PATH = Path('includes/diagnostics/tests')

def get_class_name(file_path):
    """Extract class name from PHP file."""
    with open(file_path, 'r') as f:
        for line in f:
            if 'class ' in line and 'extends' in line:
                # Extract class name from: class Diagnostic_Something extends
                parts = line.split('class ')
                if len(parts) > 1:
                    class_part = parts[1].split(' extends')[0].strip()
                    return class_part
    return None

def get_file_hash(file_path):
    """Get hash of file content."""
    with open(file_path, 'rb') as f:
        return hashlib.md5(f.read()).hexdigest()

def get_slug(file_path):
    """Extract diagnostic slug from file."""
    with open(file_path, 'r') as f:
        for line in f:
            if "protected static \$slug = '" in line or 'protected static $slug = "' in line:
                # Extract slug value
                slug = line.split("'")[1] if "'" in line else line.split('"')[1]
                return slug
    return None

def main():
    # Find all diagnostic files
    diagnostics = defaultdict(list)
    for file_path in BASE_PATH.rglob('class-diagnostic-*.php'):
        name = file_path.name.replace('class-diagnostic-', '').replace('.php', '')
        diagnostics[name].append(file_path)

    # Filter to duplicates only
    duplicates = {k: v for k, v in diagnostics.items() if len(v) > 1}

    # Analyze each duplicate set
    report = []
    report.append("# Diagnostic Duplicates - Detailed Analysis\n")
    report.append(f"Found {len(duplicates)} duplicate diagnostic names ({sum(len(v) for v in duplicates.values())} total files)\n")
    report.append("=" * 100 + "\n\n")

    for dup_name in sorted(duplicates.keys()):
        files = sorted(duplicates[dup_name])
        report.append(f"## {dup_name}\n")
        report.append(f"**Locations:** {len(files)}\n\n")

        # Analyze each file
        file_info = []
        for file_path in files:
            class_name = get_class_name(file_path)
            slug = get_slug(file_path)
            content_hash = get_file_hash(file_path)
            
            file_info.append({
                'path': file_path,
                'rel_path': str(file_path.relative_to(BASE_PATH)),
                'class': class_name,
                'slug': slug,
                'hash': content_hash,
            })

        # Check if files are identical
        hashes = set(info['hash'] for info in file_info)
        classes = set(info['class'] for info in file_info)
        slugs = set(info['slug'] for info in file_info)

        if len(hashes) == 1:
            report.append("⚠️  **STATUS: IDENTICAL CONTENT**\n")
        else:
            report.append("⚠️  **STATUS: DIFFERENT CONTENT** (potential variations)\n")

        report.append(f"- Classes: {len(classes)} unique\n")
        report.append(f"- Slugs: {len(slugs)} unique\n")
        report.append(f"- Content hashes: {len(hashes)} unique\n\n")

        # List files
        report.append("**Files:**\n")
        for info in file_info:
            report.append(f"  - {info['rel_path']}\n")
            report.append(f"    - Class: `{info['class']}`\n")
            report.append(f"    - Slug: `{info['slug']}`\n")
            report.append(f"    - Hash: `{info['hash'][:8]}`\n")

        report.append("\n")

    # Write report
    output_path = Path('dev-tools/diagnostic-duplicates-detailed.txt')
    with open(output_path, 'w') as f:
        f.write('\n'.join(report))

    print(f"✓ Detailed analysis saved to {output_path}")

if __name__ == '__main__':
    main()
