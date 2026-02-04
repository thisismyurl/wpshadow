#!/usr/bin/env python3
"""
Remove identical duplicate diagnostics.

This script removes the 4 identical duplicate files, keeping only one version.
Run this AFTER reviewing and approving the changes in DIAGNOSTIC_DUPLICATES_SUMMARY.md

Usage: python3 remove-duplicate-diagnostics.py
"""

import os
from pathlib import Path

# Files to delete (keeping one, deleting the other)
TO_DELETE = [
    'includes/diagnostics/tests/content/class-diagnostic-feed-caching-performance.php',
    'includes/diagnostics/tests/settings/class-diagnostic-feed-content-encoding.php',
    'includes/diagnostics/tests/settings/class-diagnostic-feed-custom-endpoints.php',
    'includes/diagnostics/tests/settings/class-diagnostic-feed-summary-vs-full.php',
]

def main():
    print("🧹 Removing identical duplicate diagnostics...\n")
    
    deleted_count = 0
    for file_path in TO_DELETE:
        full_path = Path(file_path)
        if full_path.exists():
            full_path.unlink()
            print(f"✓ Deleted: {file_path}")
            deleted_count += 1
        else:
            print(f"✗ Not found: {file_path}")
    
    print(f"\n✅ Cleanup complete!")
    print(f"Deleted {deleted_count} files")
    print(f"Estimated space saved: ~4 KB")
    
    # Summary
    print("\n📋 Files retained (canonical versions):")
    print("  - performance/class-diagnostic-feed-caching-performance.php")
    print("  - performance/class-diagnostic-feed-content-encoding.php")
    print("  - performance/class-diagnostic-feed-custom-endpoints.php")
    print("  - performance/class-diagnostic-feed-summary-vs-full.php")

if __name__ == '__main__':
    main()
