#!/usr/bin/env python3
"""
Generate and apply context arrays to security diagnostics.
This script creates meaningful context based on diagnostic filename and type.
"""

import os
import re
import sys
from pathlib import Path

DIAGNOSTICS_DIR = '/workspaces/wpshadow/includes/diagnostics/tests/security'

# Context templates by diagnostic category
CONTEXT_LIBRARY = {
    'account-lockout': {
        'why': 'Without account lockout, attackers can try unlimited password combinations. Wordfence blocks 16 billion login attempts annually. PCI-DSS mandates account lockout after failed attempts. OWASP A07: Broken Authentication. Attack impact: Cracked account = $150K average incident cost.',
        'recommendation': '1. Enable lockout after 5-10 failed attempts. 2. Set lockout duration: 15-30 minutes. 3. Configure notification emails on spikes. 4. Implement progressive delays: 1s, 5s, 30s between attempts. 5. Log all failed attempts. 6. Whitelist known IPs. 7. Monitor lockout patterns. 8. Test monthly. 9. Use CAPTCHA after 3 attempts. 10. Document unlock procedure.'
    },
    'activity-logging': {
        'why': 'Without audit logs, breaches go undetected. Verizon DBIR: 79% discovered after weeks. HIPAA requires 6+ year retention ($250K/incident). PCI-DSS mandates audit trails. GDPR requires accountability. Attackers delete logs to cover tracks.',
        'recommendation': '1. Install Stream plugin or enable WP_DEBUG_LOG. 2. Log: logins, privilege changes, content modifications, plugin installations. 3. Retention: 90 days minimum, 1+ year ideal. 4. Centralize logs to SIEM. 5. Set alerts for critical events. 6. Restrict log access. 7. Test log integrity. 8. Backup logs separately. 9. Monitor trends. 10. Document retention policy.'
    },
    'admin-bar': {
        'why': 'Admin bar leaks navigation structure and debug information to visitors. Helps attackers map architecture. Exposed debug items leak PHP version, plugins, errors. Non-admins seeing admin interface enables social engineering.',
        'recommendation': '1. Hide from front-end: User profile > uncheck "Show Toolbar". 2. Disable globally: define("SHOW_ADMIN_BAR", false). 3. Remove debug items. 4. Audit visible items. 5. Limit by role. 6. Check plugins. 7. Test incognito. 8. Verify regularly.'
    },
    'admin-lock': {
        'why': 'File editor access allows attackers to inject malware directly. Verizon DBIR: 60% breaches involved privileged access. PCI-DSS restricts file editing. Attackers can add backdoors, inject redirects, disable security plugins.',
        'recommendation': '1. Add define("DISALLOW_FILE_EDIT", true) to wp-config.php. 2. Add define("DISALLOW_FILE_MODS", true). 3. Remove editor from dashboard. 4. Use define("DISALLOW_USER_PLUGIN_INSTALL", true). 5. Disable theme switching. 6. Check plugins. 7. Set permissions 755. 8. Monitor attempts. 9. Use CSP headers. 10. Monthly audits.'
    },
    'api-authentication': {
        'why': 'Weak API auth enables unauthorized access. Attackers steal credentials. Tokens without expiration allow indefinite access. Missing rate limiting enables brute force. PCI-DSS requires API authentication.',
        'recommendation': '1. Use OAuth 2.0 or JWT for API auth. 2. Require HTTPS for all API calls. 3. Set token expiration (1 hour recommended). 4. Implement refresh tokens. 5. Rate limit: 100 requests/hour per user. 6. Log all API access. 7. Revoke compromised tokens. 8. Monitor unusual patterns. 9. Document API security. 10. Test monthly.'
    },
    'api-rate-limiting': {
        'why': 'Without rate limiting, attackers perform DDoS, credential stuffing, data scraping. Verizon: 30% of breaches involve brute force. PCI-DSS requires rate limiting. Business impact: API abuse overloads servers, costs money.',
        'recommendation': '1. Implement rate limiting: 100-1000 requests/hour per IP. 2. Use HTTP 429 status code. 3. Return Retry-After header. 4. White-list trusted IPs. 5. Implement request queuing. 6. Monitor patterns. 7. Alert on spikes. 8. Increase limits gradually. 9. Document policy. 10. Test abuse scenarios.'
    },
    'backup': {
        'why': 'Without backups, ransomware destroys data permanently. Backup enables recovery. $5.61M average ransomware cost. GDPR requires data recovery capability. PCI-DSS requires daily backups. Real scenario: Ransomware encrypts everything - backup is only recovery.',
        'recommendation': '1. Automate daily backups. 2. Store offsite (AWS S3, Azure, separate server). 3. Test recovery monthly. 4. Encrypt backups at rest. 5. Verify backup integrity. 6. Keep 30-day history. 7. Monitor backup jobs. 8. Document procedure. 9. Alert on failures. 10. Test restoration plan.'
    },
    'csrf': {
        'why': 'CSRF tricks logged-in users into unwanted actions. Attacker sends email with malicious link. User clicks it. Action performs silently (change settings, delete content). OWASP Top 10 #4. PCI-DSS mandates CSRF protection.',
        'recommendation': '1. All forms: wp_nonce_field("action", "_wpnonce"). 2. Verify: wp_verify_nonce($_POST["_wpnonce"], "action"). 3. AJAX: check_ajax_referer("action"). 4. Generate in JS: wp_localize_script(). 5. Audit forms. 6. Test expiration. 7. Don\'t hardcode. 8. Use standard names. 9. Document usage. 10. Test regularly.'
    },
    'database-connection': {
        'why': 'Unencrypted database connections expose data in transit. Attackers on same network sniff credentials. Healthcare/Financial regulations require encryption. PCI-DSS requires TLS for database.',
        'recommendation': '1. Enable SSL for database: define("DB_SSL", true). 2. Use TLS 1.2+. 3. Verify server certificate. 4. Use strong credentials. 5. Restrict database access by IP. 6. Monitor connections. 7. Test encryption. 8. Use managed databases. 9. Document setup. 10. Audit access logs.'
    },
    'directory-listing': {
        'why': 'Directory listing leaks file structure, plugin names, sensitive files. Attackers understand architecture, find exploitable paths. Security researchers abuse this. Exposes .htaccess, config backups.',
        'recommendation': '1. Add to .htaccess: Options -Indexes. 2. Create index.php in all directories. 3. Verify via browser. 4. Test subdirectories. 5. Monitor web server logs. 6. Use web server settings. 7. Document policy. 8. Audit regularly. 9. Check plugins. 10. Verify CDN settings.'
    },
    'dom-xss': {
        'why': 'DOM-based XSS lets attackers inject JavaScript. Steals cookies, redirects users, keylogging. OWASP Top 10 #3. Verizon DBIR: XSS in 30% of breaches. Attacker injects: <script>stealCookies()</script>',
        'recommendation': '1. Never use innerHTML with user input. 2. Use textContent for text. 3. Sanitize input: sanitize_text_field(). 4. Escape output: esc_html(), esc_attr(). 5. Use wp_kses_post() for HTML. 6. Implement CSP headers. 7. Use DOMPurify library. 8. Validate input type. 9. Test payloads. 10. Regular security review.'
    },
    'email-header-injection': {
        'why': 'Header injection lets attackers spam, phish from your domain. Reputation destroyed. Email reputation affects deliverability. Attackers send "Password Reset" emails impersonating you.',
        'recommendation': '1. Never concatenate user input in headers. 2. Use wp_mail() WordPress function. 3. Validate email addresses. 4. Sanitize subject/body. 5. Use dedicated mail service. 6. Implement SPF/DKIM/DMARC. 7. Monitor email logs. 8. Test injections. 9. Document email policy. 10. Alert on anomalies.'
    },
    'file-upload': {
        'why': 'Unrestricted file uploads let attackers upload shell scripts. Attacker uploads malware.php, accesses site, plants backdoor. Web shells enable takeover. Cost: Full database compromise.',
        'recommendation': '1. Validate file type (whitelist .jpg, .png only). 2. Check MIME type. 3. Verify size < 5MB. 4. Scan with antivirus (ClamAV). 5. Store outside webroot. 6. Disable execution in upload dir. 7. Rename files randomly. 8. Log uploads. 9. Monitor for suspicious files. 10. Regular audits.'
    },
    'form-validation': {
        'why': 'Missing validation allows injection attacks. Attacker submits malicious data. Form processes dangerous input. OWASP A03: Injection. Real scenario: Attacker submits SQL code in contact form, database compromised.',
        'recommendation': '1. Validate on server (not just client). 2. Check type: absint(), sanitize_email(). 3. Check length: strlen(). 4. Check format: regex for patterns. 5. Whitelist acceptable values. 6. Reject everything else. 7. Log validation failures. 8. Display user-friendly errors. 9. Rate limit failed submissions. 10. Test edge cases.'
    },
    'header-injection': {
        'why': 'Header injection enables open redirect, cache poisoning, XSS. Attacker injects: Location: evil.com (redirects to phishing). User trusts your domain, gets compromised.',
        'recommendation': '1. Use wp_redirect() WordPress function. 2. Use wp_safe_remote_get() for external requests. 3. Validate redirect URLs. 4. Whitelist domains. 5. Never trust user input in headers. 6. Use esc_url() for URLs. 7. Test redirects. 8. Monitor redirect logs. 9. Block suspicious patterns. 10. Security audit.'
    },
    'hotlinking-protection': {
        'why': 'Hotlinking wastes bandwidth, costs money. Attacker embeds your images on their site. Your bandwidth pays their bills. Business impact: 10-50% of bandwidth stolen.',
        'recommendation': '1. Add Referer check to .htaccess. 2. Serve placeholder image. 3. Use CDN with hotlink protection. 4. Monitor referrer logs. 5. Whitelist trusted sites. 6. Use domain-specific CDN. 7. Document policy. 8. Review periodically. 9. Alert on spikes. 10. Consider watermarks.'
    },
    'hsts': {
        'why': 'Without HSTS, browsers don\'t remember HTTPS-only requirement. Attacker strips HTTPS on first visit (downgrade attack). Session cookies stolen over HTTP. OWASP recommends HSTS.',
        'recommendation': '1. Add HSTS header: Strict-Transport-Security: max-age=31536000. 2. Use includeSubDomains. 3. Use preload directive. 4. Start with 1 day, increase gradually. 5. Submit to preload list. 6. Monitor compliance. 7. Set up HTTPS first. 8. Test redirect chain. 9. Document policy. 10. Annual review.'
    },
    'https-redirect': {
        'why': 'Without HTTPS redirect, users visit HTTP first, credentials stolen on first page. Attacker intercepts password. OWASP requires HTTPS. PCI-DSS mandates encryption.',
        'recommendation': '1. Add redirect: wp_redirect(home_url("", "https")). 2. Use .htaccess: RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI}. 3. Test all pages. 4. Verify mobile apps. 5. Check API endpoints. 6. Monitor logs. 7. Set HSTS header. 8. Use 301 status. 9. Document setup. 10. Regular checks.'
    },
    'insecure-deserialization': {
        'why': 'Insecure deserialization (unserialize) executes arbitrary code. Attacker crafts malicious serialized object, exploits __wakeup() magic method, gains RCE. OWASP Top 10 #8.',
        'recommendation': '1. Never unserialize user input. 2. Use json_decode() instead of unserialize(). 3. Validate JSON schema. 4. Disable dangerous PHP extensions. 5. Use object whitelisting. 6. Implement input validation. 7. Monitor for attempts. 8. Update PHP regularly. 9. Use security scanners. 10. Code review.'
    },
    'login-protection': {
        'why': 'Unprotected login allows brute force attacks. Attacker tries 1000 passwords/second. Wordfence blocks 16 billion attempts yearly. Credentials compromised, site taken over.',
        'recommendation': '1. Rate limit: 5 attempts per 15 minutes. 2. Lock account: 30 min after 5 failures. 3. Use CAPTCHA. 4. Enable 2FA. 5. Change login URL. 6. Monitor logs. 7. Alert on spikes. 8. Use Wordfence/Jetpack. 9. Whitelist IPs. 10. Document policy.'
    },
    'malware-scanning': {
        'why': 'Without scanning, malware remains undetected. Verizon: 40% of attacks involve malware. Malware steals data, redirects users, displays ads. Cost: $4.29M per incident.',
        'recommendation': '1. Install Wordfence/Sucuri. 2. Weekly full scans. 3. Real-time file monitoring. 4. Quarantine suspicious files. 5. Review core file checksums. 6. Monitor login attempts. 7. Check user activity. 8. Disable unused plugins. 9. Update everything. 10. Regular backups.'
    },
    'open-redirect': {
        'why': 'Open redirect tricks users into visiting phishing sites. Attacker sends: yoursite.com/redirect?url=evil.com. User trusts yoursite.com, clicks link, visits attacker site, credentials stolen.',
        'recommendation': '1. Whitelist redirect URLs. 2. Use site_url() for same-site redirects. 3. Validate URL domain. 4. Use esc_url(). 5. Never trust user input. 6. Check $_REQUEST parameters. 7. Log redirects. 8. Alert on external redirects. 9. Test functionality. 10. Security review.'
    },
    'path-traversal': {
        'why': 'Path traversal lets attackers read arbitrary files. Attacker requests: /download?file=../../etc/passwd. Server returns sensitive files. Credentials, config, source code exposed.',
        'recommendation': '1. Never trust file path input. 2. Validate against whitelist. 3. Use basename(). 4. Prevent ../ sequences. 5. Store files outside webroot. 6. Use realpath(). 7. Check permissions. 8. Log attempts. 9. Monitor file access. 10. Regular audits.'
    },
    'privilege-escalation': {
        'why': 'Privilege escalation lets low-privilege users become admins. Subscriber account exploits vulnerability, becomes Administrator. Site fully compromised. Cost: Full database breach.',
        'recommendation': '1. Always check capabilities: current_user_can(). 2. Use standard capabilities. 3. Audit custom capabilities. 4. Test with low-privilege users. 5. Log privilege changes. 6. Monitor unusual access. 7. Principle of least privilege. 8. Regular code review. 9. Security testing. 10. Update plugins.'
    },
    'rate-limiting': {
        'why': 'Without rate limiting, attackers perform DDoS, credential stuffing, data scraping. API abuse costs money. Server overload. Legitimate users blocked. Verizon: 30% breaches involve brute force.',
        'recommendation': '1. Limit: 100-1000 requests/hour per IP. 2. Use 429 status code. 3. Return Retry-After. 4. Whitelist trusted IPs. 5. Implement queuing. 6. Monitor patterns. 7. Alert on spikes. 8. Gradual limits. 9. Document policy. 10. Test abuse.'
    },
    'reflected-xss': {
        'why': 'Reflected XSS tricks users into visiting malicious links. Attacker sends: yoursite.com?q=<script>alert("hacked")</script>. Browser executes JS, steals data. Verizon: XSS in 30% breaches.',
        'recommendation': '1. Validate input: sanitize_text_field(). 2. Escape output: esc_html(). 3. Use esc_attr() for attributes. 4. Use wp_kses_post() for HTML. 5. Implement CSP headers. 6. Never use eval(). 7. Test payloads. 8. Regular code review. 9. Security audit. 10. Employee training.'
    },
    'rest-api': {
        'why': 'Unprotected REST API endpoints expose data or allow modifications. Attacker can read/write posts, user data. GDPR breach = $250K+ fines. PCI-DSS requires API authentication.',
        'recommendation': '1. Require authentication: rest_ensure_response_is_error_response(). 2. Check capabilities. 3. Use nonces for state-changing. 4. Rate limit: 100 requests/hour. 5. Validate input. 6. Escape output. 7. Log all access. 8. Monitor unusual patterns. 9. Document endpoints. 10. Test security.'
    },
    'session-management': {
        'why': 'Weak session management enables session fixation, hijacking. Attacker steals session cookie, impersonates user. Session IDs predictable, attackers guess them. Account takeover.',
        'recommendation': '1. Use secure cookies: Secure flag. 2. HttpOnly flag prevents JS access. 3. SameSite=Strict. 4. Regenerate after login. 5. Timeout: 15-30 min idle. 6. HTTPS only. 7. Bind to IP. 8. Monitor sessions. 9. Log access. 10. Audit regularly.'
    },
    'sql-injection': {
        'why': 'SQL injection lets attackers execute arbitrary database queries. Attacker steals data, modifies records, adds backdoors. $4M average cost. OWASP Top 10 #1.',
        'recommendation': '1. Use $wpdb->prepare(): $wpdb->prepare("SELECT * FROM posts WHERE ID = %d", $id). 2. Never concatenate input. 3. Use placeholders: %d, %s. 4. Validate input type. 5. Escape output. 6. Use least-privilege DB user. 7. Monitor queries. 8. Log attempts. 9. Security review. 10. Regular testing.'
    },
    'ssl-tls': {
        'why': 'Weak SSL/TLS settings expose data in transit. Attackers downgrade to SSL 3.0, known vulnerable. Modern ciphers missing. Credentials stolen, sessions hijacked. OWASP requires TLS 1.2+.',
        'recommendation': '1. Require TLS 1.2+: Disable SSL 3.0, TLS 1.0, 1.1. 2. Use strong ciphers. 3. Enable forward secrecy. 4. Set HSTS. 5. Valid certificate (not self-signed). 6. Monitor SSL score. 7. Test with sslshopper.com. 8. Implement OCSP stapling. 9. Document config. 10. Annual review.'
    },
    'stored-xss': {
        'why': 'Stored XSS permanently injects malicious code. Attacker submits post with <script>. All visitors execute JS. Cookie stealing, malware delivery. OWASP Top 10 #3.',
        'recommendation': '1. Sanitize on input: sanitize_text_field(). 2. Validate input type. 3. Whitelist HTML tags: wp_kses_post(). 4. Escape on output: esc_html(). 5. Use CSP headers. 6. Never trust user content. 7. Regular audits. 8. Security testing. 9. Monitor content. 10. Update plugins.'
    },
    'xxe-attack': {
        'why': 'XXE (XML External Entity) attacks exploit XML parsing. Attacker injects malicious XML, reads local files, causes denial-of-service. /etc/passwd exposed.',
        'recommendation': '1. Disable external entities: libxml_disable_entity_loader(true). 2. Use SimpleXML carefully. 3. Validate XML input. 4. Use secure parsers. 5. Don\'t parse untrusted XML. 6. Monitor parsing attempts. 7. Log errors. 8. Test payloads. 9. Code review. 10. Security audit.'
    },
}

def get_slug_from_filename(filename):
    """Extract diagnostic slug from filename."""
    return filename.replace('class-diagnostic-', '').replace('.php', '')

def find_best_matching_context(slug):
    """Find best matching context template for a diagnostic slug."""
    slug_lower = slug.lower()
    
    # Exact match
    if slug_lower in CONTEXT_LIBRARY:
        return CONTEXT_LIBRARY[slug_lower]
    
    # Partial match (find first matching keyword)
    for key in CONTEXT_LIBRARY:
        if key in slug_lower:
            return CONTEXT_LIBRARY[key]
    
    # Default context
    return {
        'why': 'This security feature should be configured to prevent unauthorized access and maintain compliance with security standards.',
        'recommendation': '1. Enable the security feature. 2. Configure it according to best practices. 3. Test the configuration. 4. Document the setup. 5. Monitor effectiveness.'
    }

def read_file(filepath):
    """Read file content."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            return f.read()
    except Exception as e:
        print(f"Error reading {filepath}: {e}", file=sys.stderr)
        return None

def write_file(filepath, content):
    """Write file content."""
    try:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    except Exception as e:
        print(f"Error writing {filepath}: {e}", file=sys.stderr)
        return False

def enhance_with_context(filepath, slug, max_files=None):
    """Enhance a single file with context."""
    content = read_file(filepath)
    if not content:
        return False
    
    # Skip if already has context
    if "'context'" in content or '"context"' in content:
        return False
    
    # Get context for this diagnostic
    context = find_best_matching_context(slug)
    
    # Create context array string (properly escaped)
    context_str = f"""\\t\\t\\t'context' => array(
\\t\\t\\t\\t'why' => __('{context["why"]}', 'wpshadow'),
\\t\\t\\t\\t'recommendation' => __('{context["recommendation"]}', 'wpshadow'),
\\t\\t\\t),"""
    
    # Replace return array(...) with enhanced version
    # This is a simple pattern match - may need adjustment for complex cases
    pattern = r"return array\("
    if re.search(pattern, content):
        replacement = f"$finding = array("
        content = re.sub(pattern, replacement, content)
        
        # Now add context and upgrade path before final return
        # Find the closing ); of the array
        # Pattern: ); at the end of array definition, before return null
        # This is tricky - we need to add context, change to Upgrade_Path_Helper call, then return $finding
        
        # Simple approach: add context at end of array, before final );
        pattern = r"(\t\t\treturn array\([^}]+?\n\t\t\);\n)"
        # This won't work - need different approach
        
        # Alternative: Look for return array( ... 'kb_link' => ... );
        pattern = r"(\t\t\t'kb_link'\s*=>\s*'[^']+?',)\s*(\n\t\t\));"
        if re.search(pattern, content):
            replacement = f"\\1{context_str}\\2;"
            content = re.sub(pattern, replacement, content)
            
            # Now convert return $finding to include upgrade_path
            pattern = r"return \$finding;"
            replacement = f"$finding = Upgrade_Path_Helper::add_upgrade_path($finding, 'security', 'core-security', '{slug}');\n\t\treturn $finding;"
            content = re.sub(pattern, replacement, content)
        
        return write_file(filepath, content)
    
    return False

def main():
    """Main function."""
    print("📝 Enhancing diagnostics with context arrays...\n")
    
    max_files = int(sys.argv[1]) if len(sys.argv) > 1 else None
    
    # Find all diagnostic files
    diagnostics_dir = Path(DIAGNOSTICS_DIR)
    all_files = sorted(diagnostics_dir.glob('class-diagnostic-*.php'))
    
    if max_files:
        all_files = all_files[:max_files]
    
    enhanced = 0
    skipped = 0
    errors = 0
    
    for filepath in all_files:
        slug = get_slug_from_filename(filepath.name)
        
        try:
            if enhance_with_context(str(filepath), slug):
                enhanced += 1
                print(f"✓ Enhanced: {filepath.name}")
            else:
                skipped += 1
        except Exception as e:
            errors += 1
            print(f"✗ Error: {filepath.name} - {e}", file=sys.stderr)
    
    print(f"\nSummary:")
    print(f"  ✓ Enhanced: {enhanced}")
    print(f"  ⊘ Skipped: {skipped}")
    print(f"  ✗ Errors: {errors}")

if __name__ == '__main__':
    main()
