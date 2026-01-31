#!/usr/bin/env python3
"""
Guardian Analyzer Transient Migration Script
Migrates guardian analyzer files to Cache_Manager
"""

import re
import sys
from pathlib import Path

# Reuse the Transient Migrator class
class TransientMigrator:
    """Migrates transient calls to Cache_Manager"""
    
    def __init__(self, file_path, cache_group):
        self.file_path = Path(file_path)
        self.cache_group = cache_group
        self.changes = []
        
    def migrate_file(self):
        """Main migration method"""
        if not self.file_path.exists():
            print(f"❌ File not found: {self.file_path}")
            return False
            
        content = self.file_path.read_text()
        original_content = content
        
        # Migrate all transient patterns
        content = self._migrate_all_patterns(content)
        
        if content != original_content:
            self.file_path.write_text(content)
            print(f"✅ Migrated: {self.file_path.name}")
            print(f"   Changes: {len(self.changes)}")
            return True
        else:
            print(f"ℹ️  No changes needed: {self.file_path.name}")
            return False
    
    def _migrate_all_patterns(self, content):
        """Migrate all transient patterns"""
        
        # Pattern 1: get_transient( 'key' )
        pattern1 = r'get_transient\(\s*[\'"]([^\'"]+)[\'"]\s*\)'
        def replacer1(match):
            key = match.group(1).replace('wpshadow_', '')
            self.changes.append(f"get_transient → Cache_Manager::get")
            return f"\\WPShadow\\Core\\Cache_Manager::get( '{key}', '{self.cache_group}' )"
        content = re.sub(pattern1, replacer1, content)
        
        # Pattern 2: set_transient( 'key', $value, expire )
        pattern2 = r'set_transient\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\)'
        def replacer2(match):
            key = match.group(1).replace('wpshadow_', '')
            value = match.group(2)
            expire = match.group(3)
            self.changes.append(f"set_transient → Cache_Manager::set")
            return f"\\WPShadow\\Core\\Cache_Manager::set( '{key}', {value}, '{self.cache_group}', {expire} )"
        content = re.sub(pattern2, replacer2, content)
        
        # Pattern 3: delete_transient( 'key' )
        pattern3 = r'delete_transient\(\s*[\'"]([^\'"]+)[\'"]\s*\)'
        def replacer3(match):
            key = match.group(1).replace('wpshadow_', '')
            self.changes.append(f"delete_transient → Cache_Manager::delete")
            return f"\\WPShadow\\Core\\Cache_Manager::delete( '{key}', '{self.cache_group}' )"
        content = re.sub(pattern3, replacer3, content)
        
        return content


def migrate_guardian_files():
    """Migrate all guardian analyzer files"""
    
    files = [
        ('includes/guardian/class-css-analyzer.php', 'wpshadow_guardian'),
        ('includes/guardian/class-ssl-expiration-analyzer.php', 'wpshadow_guardian'),
        ('includes/guardian/class-ab-test-overhead-analyzer.php', 'wpshadow_guardian'),
        ('includes/guardian/class-icon-analyzer.php', 'wpshadow_guardian'),
        ('includes/guardian/class-anomaly-detector.php', 'wpshadow_guardian'),
    ]
    
    base_path = Path('/workspaces/wpshadow')
    migrated = 0
    
    print("🛡️  Starting Guardian Analyzer Transient Migration")
    print("=" * 60)
    
    for file_rel_path, cache_group in files:
        file_path = base_path / file_rel_path
        migrator = TransientMigrator(file_path, cache_group)
        if migrator.migrate_file():
            migrated += 1
        print()
    
    print("=" * 60)
    print(f"✅ Migration complete: {migrated}/{len(files)} files updated")
    
    return migrated


if __name__ == '__main__':
    result = migrate_guardian_files()
    sys.exit(0 if result > 0 else 1)
