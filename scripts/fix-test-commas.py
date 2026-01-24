#!/usr/bin/env python3
"""Fix missing commas in array literals in test files."""

import os
import re
import subprocess

def fix_missing_commas(filepath):
    """Fix missing commas before quoted strings in arrays."""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            lines = f.readlines()
    except:
        return False
    
    fixed = False
    for i, line in enumerate(lines):
        # Match pattern: '],' or ')',  followed by new line starting with spaces and single quote
        # Then closing bracket/paren
        if i < len(lines) - 1:
            # Check if current line ends with ] or ) without comma
            if re.search(r"['\"][\s]*$", line) and lines[i+1].lstrip().startswith("'"):
                # Add comma at end of this line
                lines[i] = lines[i].rstrip() + ',\n'
                fixed = True
    
    if fixed:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.writelines(lines)
        
        # Verify
        result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
        if 'No syntax errors' not in result.stdout:
            return False
    
    return fixed

def main():
    diagnostic_dir = '/workspaces/wpshadow/includes/diagnostics/tests'
    fixed_count = 0
    
    for filename in sorted(os.listdir(diagnostic_dir)):
        if filename.startswith('class-test-') and filename.endswith('.php'):
            filepath = os.path.join(diagnostic_dir, filename)
            if fix_missing_commas(filepath):
                fixed_count += 1
    
    print(f"✅ Fixed {fixed_count} test files with missing commas")

if __name__ == '__main__':
    main()
