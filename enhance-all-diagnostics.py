#!/usr/bin/env python3
"""
Bulk enhance security diagnostics with Upgrade_Path_Helper and comprehensive context.
Processes all diagnostic files missing the enhancement.
"""

import os
import re
import sys
from pathlib import Path

SECURITY_DIAGNOSTICS_DIR = '/workspaces/wpshadow/includes/diagnostics/tests/security'

# Context templates for different diagnostic categories
CONTEXT_TEMPLATES = {
    'account-lockout': {
        'why': 'Without account lockout, attackers can try unlimited password combinations (brute force). Wordfence blocks 16 billion login attempts annually. PCI-DSS mandates account lockout. OWASP A07: Broken Authentication lists unlimited login attempts as critical. Attack costs: Cracked account = $150K average incident cost. Healthcare/Financial: HIPAA/GLBA require failed login tracking.',
        'recommendation': '1. Enable account lockout after 5 failed attempts\n2. Set lockout duration: 15-30 minutes (balance security/usability)\n3. Configure notification emails on failed login spikes\n4. Implement progressive delays: 1s, 5s, 30s between attempts\n5. Log all failed attempts with IP/user-agent\n6. Whitelist known IPs (office, VPN) to reduce false positives\n7. Monitor lockout patterns: Sudden spikes indicate attacks\n8. Test lockout mechanism monthly\n9. Use CAPTCHA after 3 failed attempts to prevent bot attacks\n10. Document emergency unlock procedure for locked admins'
    },
    'activity-logging': {
        'why': 'Without activity logs, breaches go undetected for months. Verizon DBIR: 79% discovered after weeks. HIPAA: 6+ year retention required ($250K per incident). PCI-DSS: Audit trails mandatory for all system access. GDPR: Accountability principle requires demonstrating compliance. Attackers delete logs to cover tracks - if you weren\'t logging, you can\'t defend yourself.',
        'recommendation': '1. Install Stream plugin or enable WP_DEBUG_LOG\n2. Log: User logins, privilege changes, content modifications, plugin installations\n3. Retention: Minimum 90 days, ideally 1+ year\n4. Centralize logs: Send to external SIEM for analysis\n5. Set alerts: Critical events (failed login spikes, suspicious modifications)\n6. Restrict access: Only admins view logs; consider read-only storage\n7. Log integrity: Test that logs can\'t be deleted by compromised admins\n8. Backup logs separately from main database\n9. Monitor trends: Look for patterns indicating compromise\n10. Document log retention policy in security plan'
    },
    'admin-bar': {
        'why': 'Admin bar leaks information: navigation structure, admin URLs, debug info. Helps attackers map site architecture. Debug items expose PHP version, active plugins, errors. Non-admins seeing admin interface elements enables social engineering.',
        'recommendation': '1. Hide from front-end: User profile > uncheck "Show Toolbar"\n2. Disable globally: define("SHOW_ADMIN_BAR", false); in wp-config.php\n3. Filter debug items: Use wp_admin_bar_render hook\n4. Audit items: Inspect what\'s visible in browser DevTools\n5. Role-based: Only show to administrators\n6. Check plugins: Verify custom plugins don\'t add sensitive items\n7. Test incognito: Confirm non-logged-in users see nothing\n8. Monitor on production: Regularly verify settings remain secure'
    },
    'admin-capability': {
        'why': 'Inconsistent capability mapping causes privilege escalation. If a custom capability isn\'t properly checked, subscribers could access admin functions. Attackers exploit loose capability checks to escalate from Contributor to Admin.',
        'recommendation': '1. Audit all custom capabilities: Find all add_cap() calls\n2. Document capability hierarchy: Who should have what access\n3. Check all functions: Verify current_user_can() before sensitive operations\n4. Map by role: Explicitly define capabilities for each role\n5. Test permissions: Use different user roles to verify access control\n6. Use standard capabilities: Prefer manage_options over custom caps\n7. Avoid direct role checks: Use capabilities not roles\n8. Review plugins: Check plugin capabilities don\'t grant too much access\n9. Principle of least privilege: Only grant needed capabilities\n10. Document in README: Explain custom capability structure'
    },
}

def get_slug_from_filename(filename):
    """Extract diagnostic slug from filename."""
    return filename.replace('class-diagnostic-', '').replace('.php', '')

def get_class_name_from_filename(filename):
    """Convert filename to class name."""
    slug = get_slug_from_filename(filename)
    # Convert kebab-case to PascalCase
    parts = slug.split('-')
    return 'Diagnostic_' + '_'.join(word.capitalize() for word in parts)

def read_file(filepath):
    """Read file content."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            return f.read()
    except Exception as e:
        print(f"Error reading {filepath}: {e}")
        return None

def find_return_statement(content):
    """Find the return statement in check() method."""
    # Pattern for return array(...)
    pattern = r'return array\((.*?)\);'
    match = re.search(pattern, content, re.DOTALL)
    return match

def has_upgrade_path_helper(content):
    """Check if file already has Upgrade_Path_Helper."""
    return 'Upgrade_Path_Helper' in content

def get_context_for_diagnostic(slug):
    """Get context template for diagnostic type."""
    # Match slug against context templates
    for key, template in CONTEXT_TEMPLATES.items():
        if key in slug.lower():
            return template
    
    # Default context for unknown types
    return {
        'why': 'This security feature should be enabled to prevent unauthorized access and maintain compliance with industry standards.',
        'recommendation': '1. Enable the security feature\n2. Test it works as expected\n3. Document your configuration\n4. Review regularly'
    }

def create_enhanced_version(content, slug, filename):
    """Create enhanced version with context and upgrade path."""
    if has_upgrade_path_helper(content):
        return None  # Already enhanced
    
    # Add Upgrade_Path_Helper import
    if 'use WPShadow\\Core\\Upgrade_Path_Helper;' not in content:
        # Find position after other use statements
        use_pattern = r'(use WPShadow\\Core\\Diagnostic_Base;)'
        replacement = r'use WPShadow\\Core\\Diagnostic_Base;\nuse WPShadow\\Core\\Upgrade_Path_Helper;'
        content = re.sub(use_pattern, replacement, content)
    
    # Get context for this diagnostic
    context = get_context_for_diagnostic(slug)
    
    # Convert return array statements to use $finding variable
    # This is complex - we need to preserve the structure while adding context
    
    # Pattern 1: return array(...);
    pattern = r'return array\((.*?)\);'
    
    def replace_return(match):
        finding_content = match.group(1)
        # Add context array if not present
        if "'context'" not in finding_content and '"context"' not in finding_content:
            # Insert context before final );
            context_array = f",\n\t\t\t'context' => array(\n\t\t\t\t'why' => __('{context['why']}', 'wpshadow'),\n\t\t\t\t'recommendation' => __('{context['recommendation']}', 'wpshadow'),\n\t\t\t),"
            finding_content += context_array
        
        return f"$finding = array({finding_content});\n\t\t$finding = Upgrade_Path_Helper::add_upgrade_path($finding, 'security', 'core-security', '{slug}');\n\t\treturn $finding;"
    
    content = re.sub(pattern, replace_return, content, flags=re.DOTALL)
    
    return content

def process_files(max_files=None):
    """Process diagnostic files."""
    files_to_process = []
    
    # Find all diagnostic files without Upgrade_Path_Helper
    for filename in sorted(os.listdir(SECURITY_DIAGNOSTICS_DIR)):
        if not filename.startswith('class-diagnostic-') or not filename.endswith('.php'):
            continue
        
        filepath = os.path.join(SECURITY_DIAGNOSTICS_DIR, filename)
        content = read_file(filepath)
        
        if content and not has_upgrade_path_helper(content):
            files_to_process.append(filepath)
    
    if max_files:
        files_to_process = files_to_process[:max_files]
    
    print(f"Found {len(files_to_process)} files to enhance")
    
    enhanced = 0
    skipped = 0
    errors = 0
    
    for filepath in files_to_process:
        filename = os.path.basename(filepath)
        slug = get_slug_from_filename(filename)
        
        try:
            content = read_file(filepath)
            if not content:
                errors += 1
                continue
            
            enhanced_content = create_enhanced_version(content, slug, filename)
            if enhanced_content:
                # Write enhanced version
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(enhanced_content)
                print(f"✓ Enhanced: {filename}")
                enhanced += 1
            else:
                print(f"⊘ Already enhanced: {filename}")
                skipped += 1
        except Exception as e:
            print(f"✗ Error processing {filename}: {e}")
            errors += 1
    
    print(f"\n Summary:")
    print(f"  Enhanced: {enhanced}")
    print(f"  Skipped: {skipped}")
    print(f"  Errors: {errors}")

if __name__ == '__main__':
    # Process first 50 files as demonstration
    max_files = int(sys.argv[1]) if len(sys.argv) > 1 else 50
    process_files(max_files)
