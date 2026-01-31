#!/usr/bin/env python3
"""
WPShadow Transient to Cache_Manager Migration Script
Automates the conversion of transient calls to Cache_Manager calls
"""

import re
import sys
from pathlib import Path

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
        
        # Pattern 1: get_transient() calls
        content = self._migrate_get_transient(content)
        
        # Pattern 2: set_transient() calls
        content = self._migrate_set_transient(content)
        
        # Pattern 3: delete_transient() calls
        content = self._migrate_delete_transient(content)
        
        if content != original_content:
            self.file_path.write_text(content)
            print(f"✅ Migrated: {self.file_path.name}")
            print(f"   Changes: {len(self.changes)}")
            for change in self.changes:
                print(f"   - {change}")
            return True
        else:
            print(f"ℹ️  No changes needed: {self.file_path.name}")
            return False
    
    def _migrate_get_transient(self, content):
        """Migrate get_transient() to Cache_Manager::get()"""
        
        # Pattern: get_transient( 'wpshadow_key_name' )
        # Replace with: \WPShadow\Core\Cache_Manager::get( 'key_name', 'group' )
        
        def replacer(match):
            full_match = match.group(0)
            key = match.group(1)
            
            # Remove 'wpshadow_' prefix if present
            clean_key = key.replace('wpshadow_', '')
            
            replacement = f"\\WPShadow\\Core\\Cache_Manager::get( '{clean_key}', '{self.cache_group}' )"
            self.changes.append(f"get_transient('{key}') → Cache_Manager::get('{clean_key}')")
            return replacement
        
        # Match: get_transient( 'key' ) or get_transient('key')
        pattern = r'get_transient\(\s*[\'"]([^\'"]+)[\'"]\s*\)'
        content = re.sub(pattern, replacer, content)
        
        return content
    
    def _migrate_set_transient(self, content):
        """Migrate set_transient() to Cache_Manager::set()"""
        
        def replacer(match):
            key = match.group(1)
            value = match.group(2)
            expire = match.group(3) if match.group(3) else '0'
            
            # Remove 'wpshadow_' prefix if present
            clean_key = key.replace('wpshadow_', '')
            
            replacement = f"\\WPShadow\\Core\\Cache_Manager::set( '{clean_key}', {value}, '{self.cache_group}', {expire} )"
            self.changes.append(f"set_transient('{key}') → Cache_Manager::set('{clean_key}')")
            return replacement
        
        # Match: set_transient( 'key', $value, expire )
        pattern = r'set_transient\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([^,]+)\s*(?:,\s*([^)]+))?\s*\)'
        content = re.sub(pattern, replacer, content)
        
        return content
    
    def _migrate_delete_transient(self, content):
        """Migrate delete_transient() to Cache_Manager::delete()"""
        
        def replacer(match):
            key = match.group(1)
            
            # Remove 'wpshadow_' prefix if present
            clean_key = key.replace('wpshadow_', '')
            
            replacement = f"\\WPShadow\\Core\\Cache_Manager::delete( '{clean_key}', '{self.cache_group}' )"
            self.changes.append(f"delete_transient('{key}') → Cache_Manager::delete('{clean_key}')")
            return replacement
        
        # Match: delete_transient( 'key' )
        pattern = r'delete_transient\(\s*[\'"]([^\'"]+)[\'"]\s*\)'
        content = re.sub(pattern, replacer, content)
        
        return content


def migrate_cloud_files():
    """Migrate all cloud integration files"""
    
    files = [
        ('includes/integration/cloud/class-multisite-dashboard.php', 'wpshadow_cloud'),
        ('includes/integration/cloud/class-usage-tracker.php', 'wpshadow_cloud'),
        ('includes/integration/cloud/class-notification-manager.php', 'wpshadow_cloud'),
        ('includes/integration/cloud/class-registration-manager.php', 'wpshadow_cloud'),
        ('includes/integration/cloud/class-deep-scanner.php', 'wpshadow_cloud'),
    ]
    
    base_path = Path('/workspaces/wpshadow')
    migrated = 0
    
    print("🚀 Starting Cloud Integration Transient Migration")
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
    result = migrate_cloud_files()
    sys.exit(0 if result > 0 else 1)
