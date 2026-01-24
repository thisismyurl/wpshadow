#!/usr/bin/env python3
"""Strip problematic stub docblocks from diagnostic files."""

import os
import re
import subprocess

def fix_stub_file(filepath):
    """Remove problematic stub docblock content."""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
    except:
        return False
    
    original_content = content
    
    # Extract just PHP namespace, class, and valid methods
    # Remove everything from the stub comment until the class definition
    lines = content.split('\n')
    fixed_lines = []
    in_class = False
    in_stub_doc = False
    
    for i, line in enumerate(lines):
        # Check if we're in a stub docblock
        if ' * ⚠️ STUB' in line or 'STUB - NEEDS IMPLEMENTATION' in line:
            in_stub_doc = True
        
        # If we've reached class definition, we're safe
        if line.startswith('class '):
            in_stub_doc = False
            in_class = True
            fixed_lines.append(line)
        elif in_stub_doc and line.strip().startswith('*'):
            # Skip stub documentation lines
            continue
        elif line.strip() == '*/':
            if in_stub_doc:
                # This closes the stub block
                in_stub_doc = False
                continue
            else:
                fixed_lines.append(line)
        elif line.startswith('<?php') or line.startswith('declare') or line.startswith('namespace') or line.startswith('use '):
            fixed_lines.append(line)
        elif in_stub_doc:
            # Skip lines while in stub
            continue
        else:
            fixed_lines.append(line)
    
    fixed_content = '\n'.join(fixed_lines)
    
    if fixed_content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(fixed_content)
        
        # Verify with PHP linter
        result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
        if 'No syntax errors' in result.stdout:
            return True
        else:
            # Revert on failure
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(original_content)
            return False
    
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
            
            if fix_stub_file(filepath):
                fixed_count += 1
            else:
                # Check if still broken
                result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
                if 'Parse error' in result.stderr or 'Parse error' in result.stdout:
                    failed_files.append(filename)
    
    print(f"✅ Fixed {fixed_count} stub files")
    print(f"⚠️  Remaining errors: {len(failed_files)}")

if __name__ == '__main__':
    main()
