#!/usr/bin/env python3
"""Remove malformed comment blocks from PHP files."""

import os
import re
import subprocess

def fix_file(filepath):
    """Remove malformed comment blocks."""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
    except:
        return False
    
    original_content = content
    lines = content.split('\n')
    fixed_lines = []
    i = 0
    
    while i < len(lines):
        line = lines[i]
        
        # Check if this line starts with /* (possible malformed block)
        if re.match(r'^\s*/\*\s*$', line):
            # Look ahead to find where this comment should end
            j = i + 1
            found_close = False
            last_star_line = -1
            
            # Scan for */ or end of block
            while j < len(lines) and j < i + 20:  # Max 20 lines for a comment
                if '*/' in lines[j]:
                    found_close = True
                    i = j
                    break
                # Track lines with just * (likely part of malformed comment)
                if re.match(r'^\s*\*\s*$', lines[j]):
                    last_star_line = j
                # If we hit real code, comment wasn't properly closed
                if lines[j].strip() and not lines[j].strip().startswith('*') and not lines[j].strip().startswith('//'):
                    # This is real code - skip the malformed comment
                    i = j - 1
                    break
                j += 1
            
            if found_close:
                # Normal comment block - keep it
                fixed_lines.append(line)
            else:
                # Malformed block - skip this line (don't add it)
                pass
        else:
            fixed_lines.append(line)
        
        i += 1
    
    fixed_content = '\n'.join(fixed_lines)
    
    if fixed_content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(fixed_content)
        
        # Verify the fix with PHP linter
        result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
        if 'No syntax errors' in result.stdout:
            return True
        else:
            # Revert if still broken
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(original_content)
            return False
    
    return False

def main():
    diagnostic_dir = '/workspaces/wpshadow/includes/diagnostics/tests'
    fixed_count = 0
    total_checked = 0
    failed_files = []
    
    for filename in sorted(os.listdir(diagnostic_dir)):
        if filename.startswith('class-diagnostic-') and filename.endswith('.php'):
            filepath = os.path.join(diagnostic_dir, filename)
            total_checked += 1
            
            if fix_file(filepath):
                fixed_count += 1
            else:
                # Check if still broken
                result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
                if 'Parse error' in result.stderr or 'Parse error' in result.stdout:
                    failed_files.append(filename)
    
    print(f"✅ Fixed {fixed_count} files out of {total_checked} checked")
    if failed_files:
        print(f"⚠️  Still has errors ({len(failed_files)})")

if __name__ == '__main__':
    main()
