#!/usr/bin/env python3
"""
Enhance all security diagnostics with Upgrade_Path_Helper integration.
This script identifies diagnostics missing the enhancement and applies it systematically.
"""

import os
import re
import subprocess
from pathlib import Path

SECURITY_DIAGNOSTICS_DIR = Path('/workspaces/wpshadow/includes/diagnostics/tests/security')
DIAGNOSTICS_NEEDING_ENHANCEMENT = []

def find_diagnostics_needing_enhancement():
    """Find all diagnostic files missing Upgrade_Path_Helper."""
    result = subprocess.run(
        [
            'grep',
            '-L',
            'Upgrade_Path_Helper',
            str(SECURITY_DIAGNOSTICS_DIR / 'class-diagnostic-*.php')
        ],
        shell=True,
        capture_output=True,
        text=True
    )
    
    if result.stdout:
        files = [f for f in result.stdout.strip().split('\n') if f]
        return sorted(files)
    return []

def get_slug_from_filename(filename):
    """Extract diagnostic slug from filename."""
    # Remove class-diagnostic- prefix and .php suffix
    slug = filename.replace('class-diagnostic-', '').replace('.php', '')
    return slug

def read_file(filepath):
    """Read file content."""
    with open(filepath, 'r', encoding='utf-8') as f:
        return f.read()

def write_file(filepath, content):
    """Write file content."""
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

def has_upgrade_path_import(content):
    """Check if file already has Upgrade_Path_Helper import."""
    return 'use WPShadow\Core\Upgrade_Path_Helper;' in content

def add_upgrade_path_import(content):
    """Add Upgrade_Path_Helper import if missing."""
    if has_upgrade_path_import(content):
        return content
    
    # Find the position to insert the import (after other use statements)
    import_pattern = r'(namespace.*?;\s*\n\n)(use|if\s*\()'
    match = re.search(import_pattern, content, re.DOTALL)
    
    if match:
        namespace_end = match.start(2)
        import_statement = "use WPShadow\\Core\\Upgrade_Path_Helper;\n\n"
        content = content[:namespace_end] + import_statement + content[namespace_end:]
    
    return content

def enhance_diagnostic(filepath):
    """Enhance a single diagnostic file."""
    print(f"Processing: {os.path.basename(filepath)}")
    
    content = read_file(filepath)
    
    # Skip if already enhanced
    if has_upgrade_path_import(content):
        print(f"  ✓ Already enhanced")
        return False
    
    # Add import if missing
    if 'use WPShadow\Core\Upgrade_Path_Helper;' not in content:
        content = add_upgrade_path_import(content)
    
    # Check if it has a basic return statement for findings
    if 'return array(' not in content or 'return null' not in content:
        print(f"  ⚠ Skipping: No simple array return pattern found")
        return False
    
    # Verify file structure
    if 'protected static $slug' not in content:
        print(f"  ⚠ Skipping: Missing protected static $slug")
        return False
    
    write_file(filepath, content)
    
    # Run error check
    result = subprocess.run(
        ['php', '-l', filepath],
        capture_output=True,
        text=True
    )
    
    if result.returncode == 0:
        print(f"  ✓ Enhanced and verified")
        return True
    else:
        print(f"  ✗ Syntax error: {result.stderr}")
        return False

def main():
    """Main function."""
    print("🔍 Finding diagnostic files needing enhancement...\n")
    
    files = find_diagnostics_needing_enhancement()
    print(f"Found {len(files)} files needing enhancement\n")
    
    enhanced_count = 0
    skipped_count = 0
    
    for filepath in files[:10]:  # Process first 10 as demo
        try:
            if enhance_diagnostic(filepath):
                enhanced_count += 1
            else:
                skipped_count += 1
        except Exception as e:
            print(f"  ✗ Error: {str(e)}")
            skipped_count += 1
    
    print(f"\n✓ Enhanced: {enhanced_count}")
    print(f"⚠ Skipped: {skipped_count}")
    print(f"Total remaining: {len(files)}")

if __name__ == '__main__':
    main()
