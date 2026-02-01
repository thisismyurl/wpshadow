#!/usr/bin/env python3
"""
Enhanced Transient Migration Script with String Concatenation Support
"""

import re
from pathlib import Path

def migrate_notification_manager():
    """Manually handle notification-manager with string concatenation"""

    file_path = Path('/workspaces/wpshadow/includes/integration/cloud/class-notification-manager.php')
    content = file_path.read_text()

    # Replace get_transient with dynamic key
    content = content.replace(
        'if ( get_transient( $cache_key ) ) {',
        'if ( \\WPShadow\\Core\\Cache_Manager::get( $cache_key, \'wpshadow_cloud\' ) ) {'
    )

    # Replace set_transient with dynamic key
    content = content.replace(
        'set_transient( $cache_key, true, HOUR_IN_SECONDS );',
        '\\WPShadow\\Core\\Cache_Manager::set( $cache_key, true, \'wpshadow_cloud\', HOUR_IN_SECONDS );'
    )

    # Also check the earlier usage
    old_pattern1 = 'if ( get_transient( $cache_key ) ) {\n\t\t\treturn false; // Already sent recently'
    new_pattern1 = 'if ( \\WPShadow\\Core\\Cache_Manager::get( $cache_key, \'wpshadow_cloud\' ) ) {\n\t\t\treturn false; // Already sent recently'
    content = content.replace(old_pattern1, new_pattern1)

    old_pattern2 = 'set_transient( $cache_key, true, HOUR_IN_SECONDS );'
    new_pattern2 = '\\WPShadow\\Core\\Cache_Manager::set( $cache_key, true, \'wpshadow_cloud\', HOUR_IN_SECONDS );'
    content = content.replace(old_pattern2, new_pattern2)

    file_path.write_text(content)
    print(f"✅ Migrated: class-notification-manager.php (2 dynamic key calls)")
    return True

if __name__ == '__main__':
    migrate_notification_manager()
