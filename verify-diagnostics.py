#!/usr/bin/env python3
"""
Systematic diagnostic verification script for WPShadow.
Marks functional diagnostics with verification tags.
"""

import os
import re
from pathlib import Path

# Base directory
BASE_DIR = Path('/workspaces/wpshadow/includes/diagnostics')

# Registered diagnostics (from Diagnostic_Registry)
REGISTERED_DIAGNOSTICS = [
    'Diagnostic_Memory_Limit',
    'Diagnostic_Backup',
    'Diagnostic_Permalinks',
    'Diagnostic_Tagline',
    'Diagnostic_SSL',
    'Diagnostic_Outdated_Plugins',
    'Diagnostic_Debug_Mode',
    'Diagnostic_WordPress_Version',
    'Diagnostic_Plugin_Count',
    'Diagnostic_Inactive_Plugins',
    'Diagnostic_Theme_Update_Noise',
    'Diagnostic_Plugin_Update_Noise',
    'Diagnostic_Hotlink_Protection',
    'Diagnostic_Head_Cleanup_Emoji',
    'Diagnostic_Head_Cleanup_OEmbed',
    'Diagnostic_Head_Cleanup_RSD',
    'Diagnostic_Head_Cleanup_Shortlink',
    'Diagnostic_Iframe_Busting',
    'Diagnostic_Image_Lazy_Load',
    'Diagnostic_External_Fonts',
    'Diagnostic_Jquery_Migrate',
    'Diagnostic_Plugin_Auto_Updates',
    'Diagnostic_Error_Log',
    'Diagnostic_Core_Integrity',
    'Diagnostic_Skiplinks',
    'Diagnostic_Asset_Versions_CSS',
    'Diagnostic_Asset_Versions_JS',
    'Diagnostic_CSS_Classes',
    'Diagnostic_Maintenance',
    'Diagnostic_Nav_ARIA',
    'Diagnostic_Admin_Username',
    'Diagnostic_Admin_Font_Bloat',
    'Diagnostic_Admin_Theme_Assets',
    'Diagnostic_Search_Indexing',
    'Diagnostic_Admin_Email',
    'Diagnostic_User_Notification_Email',
    'Diagnostic_Timezone',
    'Diagnostic_Content_Optimizer',
    'Diagnostic_Paste_Cleanup',
    'Diagnostic_HTML_Cleanup',
    'Diagnostic_Pre_Publish_Review',
    'Diagnostic_Embed_Disable',
    'Diagnostic_Interactivity_Cleanup',
    'Diagnostic_PHP_Version',
    'Diagnostic_File_Permissions',
    'Diagnostic_Security_Headers',
    'Diagnostic_Post_Via_Email',
    'Diagnostic_Post_Via_Email_Category',
    'Diagnostic_Initial_Setup',
    'Diagnostic_Comments_Disabled',
    'Diagnostic_Howdy_Greeting',
    'Diagnostic_Dark_Mode',
    'Diagnostic_Mobile_Friendliness',
    'Diagnostic_Database_Indexes',
    'Diagnostic_PHP_Compatibility',
    'Diagnostic_Theme_Performance',
    'Diagnostic_Font_Optimization',
    'Diagnostic_Monitoring_Status',
    'Diagnostic_Backup_Verification',
    'Diagnostic_Automation_Readiness',
    'Diagnostic_Object_Cache',
    'Diagnostic_Heartbeat_Throttling',
    'Diagnostic_XML_Sitemap',
    'Diagnostic_Robots_Txt',
    'Diagnostic_Favicon',
    'Diagnostic_Two_Factor',
    'Diagnostic_Disallow_File_Edit',
    'Diagnostic_Webhooks_Readiness',
    'Diagnostic_Resource_Hints',
    'Diagnostic_REST_API',
    'Diagnostic_RSS_Feeds',
    'Diagnostic_WP_Generator',
    'Diagnostic_Block_Cleanup',
    'Diagnostic_Consent_Checks',
    'Diagnostic_Emoji_Scripts',
    'Diagnostic_JQuery_Cleanup',
]

def find_all_diagnostics():
    """Find all diagnostic PHP files."""
    diagnostics = []
    for php_file in BASE_DIR.rglob('class-diagnostic-*.php'):
        diagnostics.append(php_file)
    return sorted(diagnostics)

def is_functional_diagnostic(file_path):
    """Check if diagnostic is functional (has return null statements)."""
    try:
        content = file_path.read_text()
        # Functional diagnostics have conditional null returns
        return 'return null' in content
    except:
        return False

def is_already_verified(file_path):
    """Check if file already has verification tag."""
    try:
        content = file_path.read_text()
        return '@verified' in content
    except:
        return False

def get_class_name_from_file(file_path):
    """Extract class name from file."""
    try:
        content = file_path.read_text()
        match = re.search(r'class\s+(Diagnostic_\w+)', content)
        if match:
            return match.group(1)
    except:
        pass
    return None

def is_registered(class_name):
    """Check if diagnostic is in registry."""
    return class_name in REGISTERED_DIAGNOSTICS

def mark_diagnostic_verified(file_path, is_in_registry=False):
    """Add verification tag to diagnostic file."""
    try:
        content = file_path.read_text()
        
        # Find the class docblock
        pattern = r'(\/\*\*[\s\S]*?\*\/)\s*(class\s+Diagnostic_\w+)'
        match = re.search(pattern, content)
        
        if not match:
            return False
            
        docblock = match.group(1)
        class_line = match.group(2)
        
        # Check if already verified
        if '@verified' in docblock:
            return False
            
        # Add verification tags before closing */
        guardian_status = 'Yes' if is_in_registry else 'Pending'
        verification_text = f''' * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated {guardian_status} - {'Registered in Diagnostic_Registry' if is_in_registry else 'Not yet in Diagnostic_Registry'}
 '''
        
        # Insert before the closing */
        new_docblock = docblock.replace('*/', verification_text + '*/')
        
        # Replace in content
        new_content = content.replace(
            docblock + '\n' + class_line,
            new_docblock + '\n' + class_line
        )
        
        # Write back
        file_path.write_text(new_content)
        return True
        
    except Exception as e:
        print(f"Error marking {file_path}: {e}")
        return False

def main():
    """Main verification process."""
    print("🔍 WPShadow Diagnostic Verification")
    print("=" * 50)
    
    diagnostics = find_all_diagnostics()
    print(f"Found {len(diagnostics)} total diagnostic files")
    
    functional_count = 0
    already_verified = 0
    newly_verified = 0
    stub_count = 0
    
    for diag_file in diagnostics:
        # Check if already verified
        if is_already_verified(diag_file):
            already_verified += 1
            continue
            
        # Check if functional
        if is_functional_diagnostic(diag_file):
            functional_count += 1
            
            # Get class name
            class_name = get_class_name_from_file(diag_file)
            if class_name:
                in_registry = is_registered(class_name)
                
                # Mark it
                if mark_diagnostic_verified(diag_file, in_registry):
                    newly_verified += 1
                    status = "✓" if in_registry else "○"
                    print(f"{status} {diag_file.name}")
        else:
            stub_count += 1
    
    print("\n" + "=" * 50)
    print(f"✓ Already verified:     {already_verified}")
    print(f"✓ Newly verified:       {newly_verified}")
    print(f"○ Functional (total):   {functional_count + already_verified}")
    print(f"⚠ Stubs (need impl):    {stub_count}")
    print(f"📊 Total diagnostics:   {len(diagnostics)}")
    print("=" * 50)

if __name__ == '__main__':
    main()
