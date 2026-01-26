#!/usr/bin/env python3
"""Show progress of diagnostic issue creation."""

import re
from pathlib import Path

def is_stub_file(file_path):
    """Check if a PHP file is a stub."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
            if '@stub' in content.lower() or 'stub implementation' in content.lower():
                return True
            if 'public static function check()' in content:
                match = re.search(r'public static function check\(\).*?\{(.*?)\n\s*\}', content, re.DOTALL)
                if match:
                    method_body = match.group(1).strip()
                    if method_body == 'return null;' or 'TODO' in method_body or len(method_body) < 50:
                        return True
            return False
    except Exception:
        return False

def main():
    # Find diagnostics directory
    script_dir = Path(__file__).parent
    repo_root = script_dir.parent.parent
    diagnostics_dir = repo_root / 'includes' / 'diagnostics'
    
    if not diagnostics_dir.exists():
        print(f"Error: Diagnostics directory not found: {diagnostics_dir}")
        sys.exit(1)
    
    # Find all diagnostic files
    all_files = sorted(diagnostics_dir.rglob('class-diagnostic-*.php'))
    stub_files = [f for f in all_files if is_stub_file(f)]
    
    total_diagnostics = len(all_files)
    total_stubs = len(stub_files)
    implemented = total_diagnostics - total_stubs
    
    print("📊 WPShadow Diagnostic Implementation Progress")
    print("=" * 50)
    print(f"Total Diagnostics:     {total_diagnostics:4d}")
    print(f"Implemented:           {implemented:4d} ({implemented/total_diagnostics*100:.1f}%)")
    print(f"Stubs Remaining:       {total_stubs:4d} ({total_stubs/total_diagnostics*100:.1f}%)")
    print("=" * 50)
    print()
    print(f"📝 Issues can be created for {total_stubs} stub diagnostics")
    print()
    print("To create issues in batches:")
    print(f"  ./.github/scripts/create-issues-batch.sh 1 10")
    print()

if __name__ == '__main__':
    main()
