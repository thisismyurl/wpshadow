#!/usr/bin/env python3
"""Aggressively fix common PHP corruption patterns in diagnostic files."""

import os
import re
import subprocess

def fix_file(filepath):
    """Fix common PHP corruption patterns."""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
    except:
        return False
    
    original_content = content
    
    # Pattern 1: Remove stray */ at class/namespace level (lines 7-10 usually)
    # This is typically a closing marker with no matching opener
    content = re.sub(r'^(\s+)\*/\n', '', content, flags=re.MULTILINE, count=1)
    
    # Pattern 2: Remove lines like 'slug', 'title' appearing outside functions
    # These are orphaned array parameters
    lines = content.split('\n')
    fixed_lines = []
    in_class = False
    in_function = False
    brace_depth = 0
    
    for i, line in enumerate(lines):
        # Track if we're in a function
        if 'function ' in line and '(' in line:
            in_function = True
        
        # Track brace depth
        brace_depth += line.count('{')
        brace_depth -= line.count('}')
        
        # If function just ended (brace depth 0 or dropping below class level)
        if in_function and brace_depth < 2:
            in_function = False
        
        # Skip orphaned array params (lines starting with ', 'slug', 'title', etc.
        if not in_function and (re.match(r"^\s*'[a-z-]+'\s*,?\s*$", line) or 
                                re.match(r"^\s*'[a-z\s-]+'\s*,?\s*$", line)):
            # This is likely orphaned
            continue
        
        # Skip extra closing braces/parens that appear without context
        if line.strip() == ');' and i > 0:
            # Check if previous few lines are function body
            prev_lines = '\n'.join(lines[max(0, i-3):i])
            if 'return' not in prev_lines and 'echo' not in prev_lines:
                # Likely orphaned closing
                continue
        
        fixed_lines.append(line)
    
    content = '\n'.join(fixed_lines)
    
    if content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
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
            
            # Check if still broken
            result = subprocess.run(['php', '-l', filepath], capture_output=True, text=True)
            if 'Parse error' in result.stderr or 'Parse error' in result.stdout:
                failed_files.append(filename)
    
    print(f"✅ Fixed {fixed_count} files")
    print(f"⚠️  Remaining errors: {len(failed_files)} files")
    if failed_files:
        for f in failed_files[:5]:
            print(f"    - {f}")
        if len(failed_files) > 5:
            print(f"    ... and {len(failed_files) - 5} more")

if __name__ == '__main__':
    main()
