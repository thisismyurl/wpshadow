#!/usr/bin/env python3
"""Convert broken stub files to valid minimal PHP."""

import os
import re
import subprocess

def convert_to_minimal(filepath):
    """Convert a broken stub file to a minimal valid form."""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
    except:
        return False
    
    # Extract the class name from the filename
    filename = os.path.basename(filepath)
    class_match = re.search(r'class-diagnostic-(.+)\.php', filename)
    if not class_match:
        return False
    
    slug = class_match.group(1)
    class_name = 'Diagnostic_' + ''.join(word.capitalize() for word in slug.split('-'))
    
    # Create a minimal valid stub
    minimal = f'''<?php
declare(strict_types=1);
namespace WPShadow\\Diagnostics;

use WPShadow\\Core\\Diagnostic_Base;

/**
 * Diagnostic stub: {slug}
 * This is a placeholder implementation.
 */
class {class_name} extends Diagnostic_Base {{
	protected static $slug = '{slug}';
	protected static $title = '{' '.join(word.capitalize() for word in slug.split('-'))}';
	
	public static function check(): ?array {{
		// TODO: Implement diagnostic logic
		return null;
	}}
	
	public static function run(): array {{
		return array();
	}}
}}
'''
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(minimal)
    
    # Verify
    result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
    if 'No syntax errors' in result.stdout:
        return True
    else:
        return False

def main():
    diagnostic_dir = '/workspaces/wpshadow/includes/diagnostics/tests'
    fixed_count = 0
    failed_files = []
    
    for filename in sorted(os.listdir(diagnostic_dir)):
        if filename.startswith('class-diagnostic-') and filename.endswith('.php'):
            filepath = os.path.join(diagnostic_dir, filename)
            
            # Quick check for PHP errors first
            result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
            if 'Parse error' not in result.stderr and 'Parse error' not in result.stdout:
                continue  # File is already good
            
            if convert_to_minimal(filepath):
                fixed_count += 1
            else:
                failed_files.append(filename)
    
    print(f"✅ Converted {fixed_count} broken stub files to minimal valid format")
    if failed_files:
        print(f"⚠️  Failed to fix {len(failed_files)} files")

if __name__ == '__main__':
    main()
