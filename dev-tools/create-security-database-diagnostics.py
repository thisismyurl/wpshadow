#!/usr/bin/env python3
"""
Create GitHub Issues for Free Security Database Diagnostics

Generates issues for integrating free security databases into WPShadow Guardian
following CORE_PHILOSOPHY.md principles:
- Free as Possible (Commandment #2)
- Beyond Pure - Privacy First (Commandment #10)
- Register, Don't Pay (Commandment #3)
- Helpful Neighbor Experience (Commandment #1)

All integrations are OPT-IN and respect user privacy.
"""

import json
import os
import subprocess
from typing import Dict, List

# GitHub API configuration
GITHUB_TOKEN = os.environ.get('GH_TOKEN')
REPO = "thisismyurl/wpshadow"

def create_github_issue(title: str, body: str, labels: List[str]) -> bool:
    """Create a GitHub issue using gh CLI."""
    try:
        cmd = [
            'gh', 'issue', 'create',
            '--repo', REPO,
            '--title', title,
            '--body', body,
            '--label', ','.join(labels)
        ]
        result = subprocess.run(cmd, capture_output=True, text=True, check=True)
        print(f"✅ Created: {title}")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to create issue: {title}")
        print(f"   Error: {e.stderr}")
        return False

# ============================================================================
# SECURITY DATABASE DIAGNOSTIC ISSUES
# ============================================================================

DIAGNOSTICS = [
    # ========================================================================
    # GROUP 1: WordPress Plugin & Theme Security (HIGHEST PRIORITY)
    # ========================================================================
    {
        "title": "Diagnostic: Vulnerable Plugins Detection (WPScan API)",
        "family": "security",
        "priority": "critical",
        "api_name": "WPScan Vulnerability Database",
        "api_url": "https://wpscan.com/",
        "requires_signup": True,
        "requires_payment": False,
        "free_tier": "25 API requests/day",
        "rate_limit": "25/day",
        "commercial_use": True,
        "opt_in": True,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `vulnerable-plugins-wpscan`  
**API:** WPScan Vulnerability Database (FREE with registration)  
**Philosophy Alignment:** Commandment #2 (Free as Possible), #10 (Privacy First)

### What This Checks

Scans all installed WordPress plugins against WPScan's database of 30,000+ known vulnerabilities to detect security risks.

**Real-World Analogy:** "Like checking if your smoke detectors have been recalled—we'll tell you if any of your plugins have known security problems that hackers could exploit."

### Why It Matters

- **Security Risk:** Vulnerable plugins are the #1 attack vector for WordPress sites
- **Business Impact:** Prevents data breaches, site defacement, malware infections
- **User Education:** Helps users understand plugin security importance

### API Details

**Service:** WPScan Vulnerability Database  
**Website:** https://wpscan.com/  
**Documentation:** https://wpscan.com/api

**Free Tier:**
- ✅ **FREE forever** with registration
- ✅ 25 API requests per day
- ✅ Access to full vulnerability database
- ✅ Commercial use allowed
- ✅ No credit card required

**Sign-Up Process:**
1. Register free account at https://wpscan.com/register
2. Get API token from dashboard
3. Paste token into WPShadow settings

**Rate Limits:**
- 25 requests/day (sufficient for checking 25 plugins daily)
- Responses are cacheable for 24 hours
- No overage charges (requests just fail gracefully)

### Implementation Requirements

**Base Class:** `Diagnostic_Base`  
**Namespace:** `WPShadow\\Diagnostics\\Security`  
**File:** `includes/diagnostics/security/class-diagnostic-vulnerable-plugins-wpscan.php`

**Dependencies:**
- WPScan API key (user-provided, opt-in)
- WordPress HTTP API (`wp_remote_get`)
- Transient caching (24-hour cache)

**Privacy Considerations (Commandment #10):**
- ✅ **OPT-IN ONLY** - Disabled by default
- ✅ Only sends plugin slugs (publicly available info)
- ✅ No personal data transmitted
- ✅ No site URL sent to WPScan
- ✅ Cached results to minimize external calls

**Failure Modes (Murphy's Law Pillar):**
- API key missing → Show setup instructions
- Rate limit exceeded → Use cached data from previous scan
- Network timeout → Graceful degradation, try again later
- Invalid API response → Log error, continue with other diagnostics

### Check Logic

```php
/**
 * Check for vulnerable plugins using WPScan API
 *
 * @return array|null Finding if vulnerabilities detected, null if safe
 */
public static function check() {
    // Verify API key exists (opt-in check)
    $api_key = get_option( 'wpshadow_wpscan_api_key', '' );
    
    if ( empty( $api_key ) ) {
        return array(
            'id'          => 'wpscan-api-not-configured',
            'title'       => __( 'Plugin Security Scanner Not Set Up Yet', 'wpshadow' ),
            'description' => __( 'Get a free WPScan API key to automatically check if your plugins have known security issues. It\'s like having a security expert review your plugins daily. Takes 2 minutes to set up.', 'wpshadow' ),
            'severity'    => 'info',
            'kb_link'     => 'https://wpshadow.com/kb/wpscan-setup',
        );
    }
    
    // Get all installed plugins
    $plugins = get_plugins();
    $vulnerable = array();
    
    foreach ( $plugins as $plugin_path => $plugin_data ) {
        $slug = dirname( $plugin_path );
        if ( '.' === $slug ) {
            $slug = basename( $plugin_path, '.php' );
        }
        
        // Check cache first
        $cache_key = 'wpshadow_wpscan_' . $slug;
        $cached = get_transient( $cache_key );
        
        if ( false !== $cached ) {
            if ( ! empty( $cached ) ) {
                $vulnerable[] = $cached;
            }
            continue;
        }
        
        // Make API request
        $response = wp_remote_get(
            "https://wpscan.com/api/v3/plugins/{$slug}",
            array(
                'headers' => array( 'Authorization' => 'Token ' . $api_key ),
                'timeout' => 10,
            )
        );
        
        if ( is_wp_error( $response ) ) {
            // Network error - skip this plugin
            continue;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( ! empty( $data[ $slug ]['vulnerabilities'] ) ) {
            $vulns = $data[ $slug ]['vulnerabilities'];
            
            $vulnerable[] = array(
                'plugin'          => $plugin_data['Name'],
                'slug'            => $slug,
                'version'         => $plugin_data['Version'],
                'vulnerabilities' => $vulns,
            );
            
            // Cache result for 24 hours
            set_transient( $cache_key, end( $vulnerable ), DAY_IN_SECONDS );
        } else {
            // Plugin is safe - cache negative result
            set_transient( $cache_key, null, DAY_IN_SECONDS );
        }
        
        // Respect rate limits - sleep between requests
        usleep( 100000 ); // 0.1 second delay
    }
    
    if ( empty( $vulnerable ) ) {
        return null; // All plugins safe!
    }
    
    // Create finding for vulnerable plugins
    $count = count( $vulnerable );
    
    return array(
        'id'          => 'vulnerable-plugins-detected',
        'title'       => sprintf(
            _n(
                '%d Plugin Has Known Security Issues',
                '%d Plugins Have Known Security Issues',
                $count,
                'wpshadow'
            ),
            $count
        ),
        'description' => sprintf(
            __( 'We found %d plugins with known security problems that hackers could exploit (like leaving windows unlocked). These should be updated or removed soon. Click for details and update instructions.', 'wpshadow' ),
            $count
        ),
        'severity'    => 'high',
        'threat_level' => 80,
        'auto_fixable' => false,
        'vulnerable_plugins' => $vulnerable,
        'kb_link'     => 'https://wpshadow.com/kb/vulnerable-plugins',
    );
}
```

### User Experience (Grandma Test)

**Finding Title:**  
"3 Plugins Have Known Security Issues"

**Finding Description:**  
"We found 3 plugins with known security problems that hackers could exploit (like leaving windows unlocked). These should be updated or removed soon. Click for details and update instructions."

**Severity Badge:**  
"🟠 High Priority"

**Action Buttons:**
- "View Vulnerable Plugins"
- "How to Fix This"
- "Learn About Plugin Security"

### Settings Integration

**Location:** `Settings > WPShadow > Security > API Integrations`

**UI Mockup:**
```
┌─────────────────────────────────────────────────────────────┐
│ 🛡️ WPScan Plugin Security Scanner (FREE)                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Automatically check your plugins for known security        │
│ issues. Like having a security expert review your          │
│ plugins every day.                                          │
│                                                             │
│ ✅ 100% Free (25 checks/day)                               │
│ ✅ No credit card needed                                   │
│ ✅ 30,000+ vulnerabilities tracked                         │
│                                                             │
│ [Toggle: OFF] Enable WPScan Security Scanning              │
│                                                             │
│ WPScan API Key: [_______________________________]           │
│                 [Get Free API Key →]                        │
│                                                             │
│ 📚 What data is sent?                                       │
│ Only plugin names (publicly available info). No personal   │
│ data, no site URL, no user information.                    │
│                                                             │
│ [Save Settings]                                             │
└─────────────────────────────────────────────────────────────┘
```

### Acceptance Criteria

- [ ] Diagnostic extends `Diagnostic_Base`
- [ ] OPT-IN only (disabled by default)
- [ ] API key stored securely in options table
- [ ] Results cached for 24 hours (respects rate limits)
- [ ] Graceful failure when API unavailable
- [ ] Shows setup instructions when API key missing
- [ ] All text passes Grandma Test (novice-comprehensible)
- [ ] Privacy-first (only sends public plugin slugs)
- [ ] KB article created explaining setup
- [ ] Settings page integration with clear API documentation

### Testing Checklist

- [ ] Test with no API key (shows setup instructions)
- [ ] Test with invalid API key (shows error message)
- [ ] Test with valid API key and vulnerable plugin
- [ ] Test with valid API key and all safe plugins
- [ ] Test cache behavior (doesn't re-check within 24 hours)
- [ ] Test rate limit handling (graceful degradation)
- [ ] Test network timeout handling
- [ ] Verify no personal data transmitted
- [ ] Verify PHPCS compliance
- [ ] Verify accessibility (WCAG AA)

### Related Issues

- #XXX: Utilities Page for API Key Management
- #XXX: KB Article: Setting Up WPScan API
- #XXX: Treatment: Update Vulnerable Plugins (if auto-updatable)

---

**Labels:** `diagnostic`, `security`  
**Milestone:** Security Database Integrations  
**Priority:** High
""",
        "labels": ["diagnostic", "security"]
    },
    
    # ========================================================================
    {
        "title": "Diagnostic: Abandoned Plugins Detection (WordPress.org API)",
        "family": "security",
        "priority": "high",
        "api_name": "WordPress.org Plugins API",
        "api_url": "https://api.wordpress.org/plugins/",
        "requires_signup": False,
        "requires_payment": False,
        "free_tier": "Unlimited (no rate limits)",
        "rate_limit": "None",
        "commercial_use": True,
        "opt_in": False,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `abandoned-plugins`  
**API:** WordPress.org Plugins API (100% FREE, no sign-up)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Identifies plugins that haven't been updated in 2+ years or are marked as closed on WordPress.org—signs they may be abandoned and potentially insecure.

**Real-World Analogy:** "Like checking if your smoke detector batteries are expired—plugins that haven't been updated in years might have unpatched security holes."

### Why It Matters

- **Security Risk:** Abandoned plugins often have unpatched vulnerabilities
- **Compatibility:** May break with future WordPress updates
- **No Support:** No one to fix bugs or security issues

### API Details

**Service:** WordPress.org Plugins API  
**Website:** https://api.wordpress.org/plugins/  
**Documentation:** https://codex.wordpress.org/WordPress.org_API

**Free Tier:**
- ✅ **100% FREE forever**
- ✅ No sign-up required
- ✅ No rate limits
- ✅ Commercial use allowed
- ✅ No API key needed

**Endpoint:**
```
GET https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&slug={slug}
```

**Response Data:**
- `last_updated`: Timestamp of last update
- `active_installs`: Number of active installations
- `tested`: Last WordPress version tested
- `download_link`: Official download URL
- `banners`: Plugin artwork

### Implementation Requirements

**Base Class:** `Diagnostic_Base`  
**Namespace:** `WPShadow\\Diagnostics\\Security`  
**File:** `includes/diagnostics/security/class-diagnostic-abandoned-plugins.php`

**Dependencies:**
- WordPress HTTP API (`wp_remote_get`)
- No API key required
- No user opt-in needed (public data only)

**Privacy Considerations:**
- ✅ No personal data sent
- ✅ Only queries public WordPress.org API
- ✅ Plugin slugs are publicly available information

### Check Logic

```php
public static function check() {
    $plugins = get_plugins();
    $abandoned = array();
    $threshold = 2; // 2 years = abandoned
    
    foreach ( $plugins as $plugin_path => $plugin_data ) {
        $slug = dirname( $plugin_path );
        if ( '.' === $slug ) {
            $slug = basename( $plugin_path, '.php' );
        }
        
        // Skip if not from WordPress.org
        $update_data = get_site_transient( 'update_plugins' );
        if ( ! isset( $update_data->no_update[ $plugin_path ] ) && 
             ! isset( $update_data->response[ $plugin_path ] ) ) {
            continue; // Premium/custom plugin
        }
        
        // Query WordPress.org API
        $response = wp_remote_get(
            "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&slug={$slug}",
            array( 'timeout' => 10 )
        );
        
        if ( is_wp_error( $response ) ) {
            continue;
        }
        
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( empty( $data['last_updated'] ) ) {
            continue;
        }
        
        $last_updated = strtotime( $data['last_updated'] );
        $years_old = ( time() - $last_updated ) / YEAR_IN_SECONDS;
        
        if ( $years_old >= $threshold ) {
            $abandoned[] = array(
                'plugin'       => $plugin_data['Name'],
                'slug'         => $slug,
                'last_updated' => $data['last_updated'],
                'years_old'    => round( $years_old, 1 ),
            );
        }
    }
    
    if ( empty( $abandoned ) ) {
        return null;
    }
    
    return array(
        'id'          => 'abandoned-plugins-detected',
        'title'       => sprintf(
            _n(
                '%d Plugin Hasn\'t Been Updated in Over 2 Years',
                '%d Plugins Haven\'t Been Updated in Over 2 Years',
                count( $abandoned ),
                'wpshadow'
            ),
            count( $abandoned )
        ),
        'description' => __( 'These plugins might be abandoned by their developers (like a house with no maintenance for years). This could mean security problems aren\'t getting fixed. Consider finding actively-maintained alternatives.', 'wpshadow' ),
        'severity'    => 'medium',
        'threat_level' => 60,
        'abandoned_plugins' => $abandoned,
        'kb_link'     => 'https://wpshadow.com/kb/abandoned-plugins',
    );
}
```

### User Experience

**Finding Title:**  
"3 Plugins Haven't Been Updated in Over 2 Years"

**Finding Description:**  
"These plugins might be abandoned by their developers (like a house with no maintenance for years). This could mean security problems aren't getting fixed. Consider finding actively-maintained alternatives."

**Action Buttons:**
- "View Abandoned Plugins"
- "Find Replacement Plugins"
- "Learn About Plugin Maintenance"

### Acceptance Criteria

- [ ] Diagnostic extends `Diagnostic_Base`
- [ ] No API key required (enabled by default)
- [ ] Checks all WordPress.org plugins
- [ ] Identifies plugins 2+ years without updates
- [ ] Graceful handling of API timeouts
- [ ] Results cached for 24 hours
- [ ] All text passes Grandma Test
- [ ] KB article with plugin alternatives

---

**Labels:** `diagnostic`, `security`, `no-api-key-required`, `enabled-by-default`
""",
        "labels": ["diagnostic", "security"]
    },
    
    # ========================================================================
    # GROUP 2: Email & Account Security
    # ========================================================================
    {
        "title": "Diagnostic: Breached Admin Email Detection (Have I Been Pwned)",
        "family": "security",
        "priority": "critical",
        "api_name": "Have I Been Pwned API",
        "api_url": "https://haveibeenpwned.com/",
        "requires_signup": False,
        "requires_payment": False,
        "free_tier": "Rate limited (1 request per 1.5 seconds)",
        "rate_limit": "1 request / 1.5 seconds",
        "commercial_use": True,
        "opt_in": True,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `breached-admin-emails`  
**API:** Have I Been Pwned (FREE, no sign-up)  
**Philosophy Alignment:** Commandment #10 (Privacy First), #1 (Helpful Neighbor)

### What This Checks

Checks if admin user email addresses have been exposed in known data breaches (12+ billion compromised accounts tracked).

**Real-World Analogy:** "Like checking if your house keys were copied when the hardware store got robbed—we'll tell you if your login email was leaked in a data breach."

### Why It Matters

- **Account Takeover Risk:** Breached emails are prime targets for hackers
- **Password Reuse:** Many people reuse passwords across sites
- **Credential Stuffing:** Automated attacks using leaked credentials

### API Details

**Service:** Have I Been Pwned (HIBP)  
**Website:** https://haveibeenpwned.com/  
**Documentation:** https://haveibeenpwned.com/API/v3

**Free Tier:**
- ✅ **100% FREE forever**
- ✅ No API key required for basic checks
- ✅ 12+ billion breached accounts
- ✅ Commercial use allowed
- ⚠️ Rate limited: 1 request per 1.5 seconds

**Endpoint:**
```
GET https://haveibeenpwned.com/api/v3/breachedaccount/{email}
```

**Privacy Considerations (CRITICAL):**
- ⚠️ Sends email addresses to external service
- ✅ **OPT-IN ONLY** - Disabled by default
- ✅ User must explicitly enable this check
- ✅ Clear consent explanation required

### Implementation Requirements

**Base Class:** `Diagnostic_Base`  
**Namespace:** `WPShadow\\Diagnostics\\Security`  
**File:** `includes/diagnostics/security/class-diagnostic-breached-admin-emails.php`

**Privacy Requirements (NON-NEGOTIABLE):**
```php
// Check if user has opted in
$opt_in = get_option( 'wpshadow_hibp_opt_in', false );

if ( ! $opt_in ) {
    return array(
        'id'          => 'hibp-not-enabled',
        'title'       => __( 'Breach Detection Not Enabled Yet', 'wpshadow' ),
        'description' => __( 'We can check if your admin email addresses have been exposed in data breaches (like finding out if your keys were copied). This requires sending email addresses to Have I Been Pwned\'s service. Enable this in Settings if you\'d like this protection.', 'wpshadow' ),
        'severity'    => 'info',
        'kb_link'     => 'https://wpshadow.com/kb/breach-detection',
    );
}
```

### Check Logic

```php
public static function check() {
    // Verify opt-in
    if ( ! get_option( 'wpshadow_hibp_opt_in', false ) ) {
        return self::get_opt_in_prompt();
    }
    
    $admins = get_users( array( 'role__in' => array( 'administrator' ) ) );
    $breached = array();
    
    foreach ( $admins as $admin ) {
        // Check cache first (24-hour cache)
        $cache_key = 'wpshadow_hibp_' . md5( $admin->user_email );
        $cached = get_transient( $cache_key );
        
        if ( false !== $cached ) {
            if ( ! empty( $cached ) ) {
                $breached[] = $cached;
            }
            continue;
        }
        
        // Rate limiting: 1 request per 1.5 seconds
        sleep( 2 );
        
        // Query HIBP API
        $response = wp_remote_get(
            'https://haveibeenpwned.com/api/v3/breachedaccount/' . urlencode( $admin->user_email ),
            array(
                'headers' => array( 'User-Agent' => 'WPShadow-Security-Scanner' ),
                'timeout' => 10,
            )
        );
        
        if ( is_wp_error( $response ) ) {
            continue;
        }
        
        $code = wp_remote_retrieve_response_code( $response );
        
        if ( 200 === $code ) {
            $breaches = json_decode( wp_remote_retrieve_body( $response ), true );
            
            if ( ! empty( $breaches ) ) {
                $breach_data = array(
                    'email'    => $admin->user_email,
                    'username' => $admin->user_login,
                    'breaches' => $breaches,
                );
                
                $breached[] = $breach_data;
                set_transient( $cache_key, $breach_data, DAY_IN_SECONDS );
            } else {
                set_transient( $cache_key, null, DAY_IN_SECONDS );
            }
        }
    }
    
    if ( empty( $breached ) ) {
        return null;
    }
    
    return array(
        'id'          => 'admin-emails-breached',
        'title'       => sprintf(
            _n(
                '%d Admin Email Found in Data Breach',
                '%d Admin Emails Found in Data Breaches',
                count( $breached ),
                'wpshadow'
            ),
            count( $breached )
        ),
        'description' => __( 'One or more admin email addresses have been exposed in known data breaches (like your keys being copied). These accounts are at higher risk of being hacked. We strongly recommend changing passwords immediately.', 'wpshadow' ),
        'severity'    => 'critical',
        'threat_level' => 90,
        'breached_accounts' => $breached,
        'kb_link'     => 'https://wpshadow.com/kb/breached-credentials',
    );
}
```

### User Consent UI (REQUIRED)

**Settings Location:** `Settings > WPShadow > Security > Breach Detection`

```
┌─────────────────────────────────────────────────────────────┐
│ 🔐 Breach Detection (Have I Been Pwned)                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Check if admin email addresses have been exposed in data   │
│ breaches. Like finding out if your house keys were copied. │
│                                                             │
│ ⚠️  Privacy Notice:                                         │
│ This feature sends admin email addresses to                │
│ HaveIBeenPwned.com's service to check their database.     │
│ Only emails are sent—no passwords, no personal data.       │
│                                                             │
│ ✅ 12+ billion breached accounts tracked                   │
│ ✅ 100% free service (no sign-up needed)                   │
│ ✅ Trusted by security professionals worldwide             │
│                                                             │
│ [Toggle: OFF] Enable Breach Detection                      │
│                                                             │
│ By enabling this, you consent to WPShadow sending your     │
│ admin email addresses to HaveIBeenPwned.com to check for   │
│ data breaches. Learn more about their privacy policy:      │
│ [View HIBP Privacy Policy →]                                │
│                                                             │
│ [Save Settings]                                             │
└─────────────────────────────────────────────────────────────┘
```

### Acceptance Criteria

- [ ] **OPT-IN ONLY** - Disabled by default
- [ ] Clear consent UI with privacy explanation
- [ ] Link to HIBP privacy policy
- [ ] Rate limiting (1 request / 1.5 seconds)
- [ ] Results cached for 24 hours
- [ ] Graceful API failure handling
- [ ] No passwords ever transmitted
- [ ] All text passes Grandma Test
- [ ] KB article explaining breach detection

---

**Labels:** `diagnostic`, `security`, `opt-in`, `privacy-sensitive`, `api-integration`
""",
        "labels": ["diagnostic", "security"]
    },
    
    # ========================================================================
    {
        "title": "Diagnostic: Spam Registrations Detection (StopForumSpam)",
        "family": "security",
        "priority": "medium",
        "api_name": "StopForumSpam API",
        "api_url": "https://www.stopforumspam.com/",
        "requires_signup": False,
        "requires_payment": False,
        "free_tier": "Unlimited (no rate limits)",
        "rate_limit": "None",
        "commercial_use": True,
        "opt_in": True,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `spam-registrations-stopforumspam`  
**API:** StopForumSpam (100% FREE, no limits)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Checks new user registrations against StopForumSpam's database of 15M+ known spammer IPs and emails.

**Real-World Analogy:** "Like having a bouncer check IDs at the door—we'll block known spammers from creating fake accounts on your site."

### Why It Matters

- **Spam Prevention:** Stop fake registrations before they happen
- **Resource Savings:** Prevent spam content creation
- **User Experience:** Keep your community spam-free

### API Details

**Service:** StopForumSpam  
**Website:** https://www.stopforumspam.com/  
**Documentation:** https://www.stopforumspam.com/usage

**Free Tier:**
- ✅ **100% FREE forever**
- ✅ No sign-up required
- ✅ No rate limits
- ✅ 15M+ known spammers
- ✅ Commercial use allowed

**Endpoint:**
```
GET https://api.stopforumspam.org/api?ip={ip}&email={email}&json
```

**Privacy Considerations:**
- ⚠️ Sends IP addresses and emails to external service
- ✅ **OPT-IN ONLY** for historical user checks
- ✅ Real-time registration blocking (separate feature)

---

**Labels:** `diagnostic`, `security`, `spam-prevention`, `opt-in`
""",
        "labels": ["diagnostic", "security"]
    },
    
    # ========================================================================
    # GROUP 3: IP Reputation & DDoS Protection
    # ========================================================================
    {
        "title": "Diagnostic: IP Reputation Check (AbuseIPDB)",
        "family": "security",
        "priority": "high",
        "api_name": "AbuseIPDB",
        "api_url": "https://www.abuseipdb.com/",
        "requires_signup": True,
        "requires_payment": False,
        "free_tier": "1,000 checks/day",
        "rate_limit": "1,000/day",
        "commercial_use": True,
        "opt_in": True,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `ip-reputation-abuseipdb`  
**API:** AbuseIPDB (FREE with registration)  
**Philosophy Alignment:** Commandment #3 (Register, Don't Pay)

### What This Checks

Checks your site's IP address against AbuseIPDB's database of known malicious IPs used for hacking, DDoS attacks, and spam.

**Real-World Analogy:** "Like checking if your house address is on a 'bad neighborhood' list—we'll tell you if your server's IP has been reported for suspicious activity."

### Why It Matters

- **Email Deliverability:** Blacklisted IPs can't send emails
- **SEO Impact:** Search engines may penalize bad IP neighborhoods
- **Security Risk:** Your IP might be targeted if previously compromised

### API Details

**Service:** AbuseIPDB  
**Website:** https://www.abuseipdb.com/  
**Documentation:** https://docs.abuseipdb.com/

**Free Tier:**
- ✅ **FREE forever** with registration
- ✅ 1,000 checks per day
- ✅ Commercial use allowed
- ✅ No credit card required

**Sign-Up Process:**
1. Register free account at https://www.abuseipdb.com/register
2. Get API key from dashboard
3. Paste key into WPShadow settings

**Rate Limits:**
- 1,000 requests/day
- 1 request per second
- Responses cacheable for 24 hours

---

**Labels:** `diagnostic`, `security`, `ip-reputation`, `api-integration`, `opt-in`
""",
        "labels": ["diagnostic", "security"]
    },
    
    {
        "title": "Diagnostic: Email Blacklist Check (Spamhaus)",
        "family": "security",
        "priority": "high",
        "api_name": "Spamhaus DNS Blocklists",
        "api_url": "https://www.spamhaus.org/",
        "requires_signup": False,
        "requires_payment": False,
        "free_tier": "DNS queries (reasonable use)",
        "rate_limit": "Reasonable use",
        "commercial_use": False,
        "opt_in": False,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `email-blacklist-spamhaus`  
**API:** Spamhaus DNS Blocklists (FREE for non-commercial)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Checks if your site's IP address is listed on Spamhaus blacklists, which could prevent your emails from being delivered.

**Real-World Analogy:** "Like checking if your return address is on the post office's spam list—we'll tell you if your emails might not be getting through."

### Why It Matters

- **Email Deliverability:** Blacklisted IPs can't send emails
- **Business Impact:** Customer communications might fail
- **Quick Detection:** Catch issues before they impact users

### API Details

**Service:** Spamhaus DNS Blocklists  
**Website:** https://www.spamhaus.org/  
**Documentation:** https://www.spamhaus.org/faq/section/DNSBL%20Usage

**Free Tier:**
- ✅ FREE for non-commercial use
- ✅ DNS-based queries (no API key)
- ✅ Multiple blocklists (ZEN, DBL, XBL)
- ⚠️ Commercial use requires data feed license

**Usage:**
```php
// DNS query to check IP
$reversed_ip = implode( '.', array_reverse( explode( '.', $ip ) ) );
$query = "{$reversed_ip}.zen.spamhaus.org";
$result = gethostbyname( $query );
```

---

**Labels:** `diagnostic`, `security`, `email-deliverability`, `no-api-key-required`
""",
        "labels": ["diagnostic", "security"]
    },
    
    # ========================================================================
    # GROUP 4: Malware & Phishing Protection
    # ========================================================================
    {
        "title": "Diagnostic: External Link Safety Check (Google Safe Browsing)",
        "family": "security",
        "priority": "medium",
        "api_name": "Google Safe Browsing API",
        "api_url": "https://safebrowsing.google.com/",
        "requires_signup": True,
        "requires_payment": False,
        "free_tier": "10,000 queries/day",
        "rate_limit": "10,000/day",
        "commercial_use": True,
        "opt_in": False,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `external-links-safe-browsing`  
**API:** Google Safe Browsing (FREE with API key)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Scans all external links in your content against Google's database of malicious websites (phishing, malware, unwanted software).

**Real-World Analogy:** "Like checking if directions lead to a safe neighborhood—we'll warn you if any links on your site point to dangerous websites."

### Why It Matters

- **User Safety:** Protect visitors from malicious links
- **SEO Impact:** Google penalizes sites linking to malware
- **Legal Liability:** Reduce risk of directing users to scams

### API Details

**Service:** Google Safe Browsing API v4  
**Website:** https://safebrowsing.google.com/  
**Documentation:** https://developers.google.com/safe-browsing/v4

**Free Tier:**
- ✅ **FREE forever** with API key
- ✅ 10,000 queries per day
- ✅ Commercial use allowed
- ✅ No credit card required

**Sign-Up Process:**
1. Create Google Cloud project (free)
2. Enable Safe Browsing API
3. Get API key
4. Paste key into WPShadow settings

---

**Labels:** `diagnostic`, `security`, `link-safety`, `api-integration`
""",
        "labels": ["diagnostic", "security"]
    },
    
    {
        "title": "Diagnostic: Phishing URL Detection (PhishTank)",
        "family": "security",
        "priority": "medium",
        "api_name": "PhishTank API",
        "api_url": "https://phishtank.org/",
        "requires_signup": True,
        "requires_payment": False,
        "free_tier": "Unlimited (with API key)",
        "rate_limit": "None specified",
        "commercial_use": True,
        "opt_in": False,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `phishing-urls-phishtank`  
**API:** PhishTank (FREE with registration)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Checks URLs in comments, posts, and pages against PhishTank's community-verified phishing database.

**Real-World Analogy:** "Like having a neighborhood watch for scam links—we'll catch fake websites trying to steal passwords or credit cards."

### Why It Matters

- **User Protection:** Stop phishing attacks before they harm visitors
- **Brand Safety:** Prevent your site from hosting phishing links
- **Community Trust:** Show users your site is safe

### API Details

**Service:** PhishTank  
**Website:** https://phishtank.org/  
**Documentation:** https://phishtank.org/developer_info.php

**Free Tier:**
- ✅ **FREE forever** with API key
- ✅ No rate limits specified
- ✅ Community-verified phishing URLs
- ✅ Commercial use allowed

---

**Labels:** `diagnostic`, `security`, `phishing-protection`, `api-integration`
""",
        "labels": ["diagnostic", "security"]
    },
    
    {
        "title": "Diagnostic: Malware URL Detection (URLhaus)",
        "family": "security",
        "priority": "medium",
        "api_name": "URLhaus (abuse.ch)",
        "api_url": "https://urlhaus.abuse.ch/",
        "requires_signup": False,
        "requires_payment": False,
        "free_tier": "Unlimited (no rate limits)",
        "rate_limit": "None",
        "commercial_use": True,
        "opt_in": False,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `malware-urls-urlhaus`  
**API:** URLhaus by abuse.ch (100% FREE)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Scans for URLs distributing malware (viruses, ransomware, trojans) in your content and comments.

**Real-World Analogy:** "Like checking if gift boxes contain bombs—we'll detect links that download viruses to visitors' computers."

### Why It Matters

- **Visitor Safety:** Prevent malware downloads
- **Site Reputation:** Google blacklists malware-hosting sites
- **Legal Protection:** Reduce liability for malware distribution

### API Details

**Service:** URLhaus (abuse.ch project)  
**Website:** https://urlhaus.abuse.ch/  
**Documentation:** https://urlhaus-api.abuse.ch/

**Free Tier:**
- ✅ **100% FREE forever**
- ✅ No API key required
- ✅ No rate limits
- ✅ Commercial use allowed
- ✅ Real-time malware URL feed

---

**Labels:** `diagnostic`, `security`, `malware-protection`, `no-api-key-required`
""",
        "labels": ["diagnostic", "security"]
    },
    
    # ========================================================================
    # GROUP 5: SSL & Certificate Monitoring
    # ========================================================================
    {
        "title": "Diagnostic: SSL Configuration Audit (SSL Labs API)",
        "family": "security",
        "priority": "medium",
        "api_name": "Qualys SSL Labs API",
        "api_url": "https://www.ssllabs.com/",
        "requires_signup": False,
        "requires_payment": False,
        "free_tier": "Rate limited (1 assessment every 2 minutes)",
        "rate_limit": "1 assessment / 2 minutes",
        "commercial_use": True,
        "opt_in": False,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `ssl-configuration-ssllabs`  
**API:** Qualys SSL Labs (FREE, no sign-up)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Performs a comprehensive SSL/TLS configuration audit and assigns a security grade (A+ to F).

**Real-World Analogy:** "Like a safety inspection for your site's lock—we'll grade how secure your HTTPS connection is and suggest improvements."

### Why It Matters

- **Data Protection:** Weak SSL can be intercepted by hackers
- **Browser Warnings:** Poor configuration triggers browser alerts
- **SEO Impact:** Google prefers properly configured HTTPS

### API Details

**Service:** Qualys SSL Labs  
**Website:** https://www.ssllabs.com/  
**Documentation:** https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs-v3.md

**Free Tier:**
- ✅ **100% FREE forever**
- ✅ No API key required
- ✅ Commercial use allowed
- ⚠️ Rate limited: 1 assessment every 2 minutes
- ⚠️ Assessments take 2-3 minutes to complete

---

**Labels:** `diagnostic`, `security`, `ssl-audit`, `no-api-key-required`
""",
        "labels": ["diagnostic", "security"]
    },
    
    {
        "title": "Diagnostic: Certificate Transparency Monitoring (crt.sh)",
        "family": "security",
        "priority": "low",
        "api_name": "crt.sh Certificate Search",
        "api_url": "https://crt.sh/",
        "requires_signup": False,
        "requires_payment": False,
        "free_tier": "Unlimited",
        "rate_limit": "None",
        "commercial_use": True,
        "opt_in": False,
        "body": """## 🎯 Diagnostic Overview

**Family:** Security  
**Slug:** `certificate-transparency-crtsh`  
**API:** crt.sh (100% FREE)  
**Philosophy Alignment:** Commandment #2 (Free as Possible)

### What This Checks

Monitors Certificate Transparency logs to detect unauthorized SSL certificates issued for your domain.

**Real-World Analogy:** "Like checking if someone made copies of your house keys—we'll alert you if SSL certificates are issued for your domain without your knowledge."

### Why It Matters

- **Security Alert:** Detect phishing sites using your domain
- **Domain Hijacking:** Early warning of domain takeover attempts
- **Compliance:** Some regulations require CT monitoring

### API Details

**Service:** crt.sh (Comodo Certificate Search)  
**Website:** https://crt.sh/  
**API:** https://crt.sh/?q=example.com&output=json

**Free Tier:**
- ✅ **100% FREE forever**
- ✅ No API key required
- ✅ No rate limits
- ✅ Commercial use allowed
- ✅ JSON output available

---

**Labels:** `diagnostic`, `security`, `ssl-monitoring`, `no-api-key-required`
""",
        "labels": ["diagnostic", "security"]
    },
]

# ============================================================================
# UTILITIES PAGE ISSUE
# ============================================================================

UTILITIES_ISSUE = {
    "title": "Feature: API Key Management Page (Security Database Integrations)",
    "body": """## 🎯 Feature Overview

**Page:** Settings > WPShadow > Security > API Integrations  
**Purpose:** Central management for all third-party security API keys  
**Philosophy Alignment:** Commandment #10 (Privacy First), #8 (Inspire Confidence)

### What This Provides

A single, secure location for users to configure API keys for free security database integrations.

**Real-World Analogy:** "Like a keychain for all your security service keys—one place to manage all your API connections."

### Why It Matters

- **User Experience:** One place for all API configurations
- **Security:** Encrypted storage of API keys
- **Trust:** Clear explanation of what data each service receives
- **Opt-In:** Explicit consent for each integration

---

## 📋 API Services to Include

### 1. WPScan Vulnerability Database
- **Status:** FREE with registration
- **Rate Limit:** 25 requests/day
- **Sign-Up:** https://wpscan.com/register
- **Privacy:** Only sends plugin slugs (public info)
- **Opt-In:** Required

### 2. Have I Been Pwned
- **Status:** FREE (no sign-up)
- **Rate Limit:** 1 request / 1.5 seconds
- **Sign-Up:** Not required
- **Privacy:** ⚠️ Sends admin email addresses
- **Opt-In:** Required (privacy-sensitive)

### 3. AbuseIPDB
- **Status:** FREE with registration
- **Rate Limit:** 1,000 requests/day
- **Sign-Up:** https://www.abuseipdb.com/register
- **Privacy:** Only sends IP addresses
- **Opt-In:** Required

### 4. Google Safe Browsing
- **Status:** FREE with API key
- **Rate Limit:** 10,000 requests/day
- **Sign-Up:** Google Cloud Console
- **Privacy:** Sends URLs from content
- **Opt-In:** Recommended

### 5. PhishTank
- **Status:** FREE with API key
- **Rate Limit:** Unlimited
- **Sign-Up:** https://phishtank.org/register.php
- **Privacy:** Sends URLs for validation
- **Opt-In:** Recommended

---

## 🎨 UI Design

### Settings Page Structure

```
┌─────────────────────────────────────────────────────────────┐
│ 🔐 Security API Integrations                                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Connect to free security databases to protect your site    │
│ from vulnerabilities, malware, and data breaches.          │
│                                                             │
│ All integrations are optional and can be disabled anytime. │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 1️⃣ PLUGIN VULNERABILITY SCANNING (Recommended)            │
│                                                             │
│ WPScan Vulnerability Database                               │
│ Check plugins for known security issues daily              │
│                                                             │
│ [Toggle: OFF] Enable WPScan Scanning                        │
│                                                             │
│ API Key: [_______________________________]                  │
│          [Get Free API Key →]                               │
│                                                             │
│ 💰 Cost: FREE forever (25 checks/day)                      │
│ 📊 What's sent: Plugin names only (public info)            │
│ 🔒 Privacy: No personal data transmitted                   │
│                                                             │
│ [Test Connection]  [View Documentation]                     │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 2️⃣ EMAIL BREACH DETECTION (Privacy-Sensitive)             │
│                                                             │
│ Have I Been Pwned                                           │
│ Check if admin emails were exposed in data breaches        │
│                                                             │
│ [Toggle: OFF] Enable Breach Detection                      │
│                                                             │
│ ⚠️  Privacy Notice:                                         │
│ This sends admin email addresses to HaveIBeenPwned.com     │
│ to check their breach database. No passwords are sent.     │
│                                                             │
│ 💰 Cost: FREE forever (rate limited)                       │
│ 📊 What's sent: Admin email addresses                      │
│ 🔒 Privacy: [View HIBP Privacy Policy →]                   │
│                                                             │
│ [Understand Privacy Impact]  [View Documentation]           │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 3️⃣ IP REPUTATION MONITORING                                │
│                                                             │
│ AbuseIPDB                                                   │
│ Check if your server IP is on blacklists                   │
│                                                             │
│ [Toggle: OFF] Enable IP Reputation Checks                  │
│                                                             │
│ API Key: [_______________________________]                  │
│          [Get Free API Key →]                               │
│                                                             │
│ 💰 Cost: FREE forever (1,000 checks/day)                   │
│ 📊 What's sent: Server IP address only                     │
│ 🔒 Privacy: No personal data transmitted                   │
│                                                             │
│ [Test Connection]  [View Documentation]                     │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 4️⃣ EXTERNAL LINK SAFETY                                    │
│                                                             │
│ Google Safe Browsing                                        │
│ Check external links for phishing/malware                  │
│                                                             │
│ [Toggle: OFF] Enable Link Safety Checks                    │
│                                                             │
│ API Key: [_______________________________]                  │
│          [Get Free API Key →]                               │
│                                                             │
│ 💰 Cost: FREE forever (10,000 checks/day)                  │
│ 📊 What's sent: URLs from your content                     │
│ 🔒 Privacy: Google's privacy policy applies                │
│                                                             │
│ [Test Connection]  [View Documentation]                     │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 5️⃣ PHISHING DETECTION                                      │
│                                                             │
│ PhishTank                                                   │
│ Community-verified phishing URL detection                  │
│                                                             │
│ [Toggle: OFF] Enable PhishTank Checks                      │
│                                                             │
│ API Key: [_______________________________]                  │
│          [Get Free API Key →]                               │
│                                                             │
│ 💰 Cost: FREE forever (unlimited)                          │
│ 📊 What's sent: URLs for validation                        │
│ 🔒 Privacy: No personal data transmitted                   │
│                                                             │
│ [Test Connection]  [View Documentation]                     │
│                                                             │
└─────────────────────────────────────────────────────────────┘

[Save All Settings]  [Reset to Defaults]
```

---

## 🔐 Security Requirements

### API Key Storage
```php
// Encrypt API keys in database
$encrypted_key = openssl_encrypt(
    $api_key,
    'AES-256-CBC',
    wp_salt( 'auth' ),
    0,
    substr( wp_salt( 'secure_auth' ), 0, 16 )
);

update_option( 'wpshadow_wpscan_api_key_encrypted', $encrypted_key, false );
```

### Connection Testing
```php
/**
 * Test API connection
 *
 * @param string $service Service name (wpscan, hibp, etc)
 * @return array {
 *     @type bool   $success Connection successful
 *     @type string $message Result message
 *     @type array  $details API response details
 * }
 */
public static function test_api_connection( $service ) {
    // Implementation varies by service
}
```

---

## 📚 Documentation Requirements

### KB Articles Needed

1. **Setting Up WPScan API**
   - Step-by-step registration
   - API key retrieval
   - Configuration in WPShadow
   - Troubleshooting

2. **Understanding Breach Detection Privacy**
   - What data is sent
   - Why email addresses are needed
   - HIBP privacy policy
   - Opt-out instructions

3. **Managing API Integrations**
   - Overview of all services
   - When to enable each
   - Rate limit management
   - Cost breakdown (all free)

4. **Security Database Comparison**
   - Which services to enable
   - Privacy considerations
   - Performance impact
   - Recommended configurations

---

## ✅ Acceptance Criteria

- [ ] Settings page created at `Settings > WPShadow > Security`
- [ ] All API keys stored encrypted
- [ ] Connection test button for each service
- [ ] Clear privacy explanations for each service
- [ ] Opt-in toggles for privacy-sensitive services
- [ ] "Get Free API Key" links for each service
- [ ] Documentation links for setup guides
- [ ] All text passes Grandma Test
- [ ] WCAG AA accessibility compliance
- [ ] Mobile-responsive design
- [ ] PHPCS compliance

---

## 🧪 Testing Checklist

- [ ] Test with no API keys (shows setup instructions)
- [ ] Test with valid API keys (successful connection)
- [ ] Test with invalid API keys (shows error)
- [ ] Test connection test buttons
- [ ] Verify API keys stored encrypted
- [ ] Test opt-in toggle behavior
- [ ] Verify privacy-sensitive services disabled by default
- [ ] Test mobile responsiveness
- [ ] Verify screen reader accessibility
- [ ] Test with JavaScript disabled (graceful degradation)

---

**Labels:** `feature`, `settings-page`, `api-integration`, `security`, `privacy-first`  
**Milestone:** Security Database Integrations  
**Priority:** Critical (blocks diagnostic implementations)
""",
    "labels": ["diagnostic", "security"]
}

# ============================================================================
# MAIN EXECUTION
# ============================================================================

def main():
    """Generate all GitHub issues."""
    print("\n" + "="*70)
    print("  CREATING SECURITY DATABASE DIAGNOSTIC ISSUES")
    print("="*70 + "\n")
    
    # Create utilities page issue first (blocking)
    print("📋 Creating Utilities Page Issue (BLOCKER)...\n")
    create_github_issue(
        UTILITIES_ISSUE["title"],
        UTILITIES_ISSUE["body"],
        UTILITIES_ISSUE["labels"]
    )
    
    print("\n" + "-"*70 + "\n")
    
    # Create diagnostic issues
    print("📋 Creating Diagnostic Issues...\n")
    
    created = 0
    failed = 0
    
    for diagnostic in DIAGNOSTICS:
        success = create_github_issue(
            diagnostic["title"],
            diagnostic["body"],
            diagnostic["labels"]
        )
        
        if success:
            created += 1
        else:
            failed += 1
    
    # Summary
    print("\n" + "="*70)
    print("  SUMMARY")
    print("="*70)
    print(f"✅ Created: {created + 1} issues (1 utilities + {created} diagnostics)")
    print(f"❌ Failed:  {failed} issues")
    print("="*70 + "\n")
    
    # API Overview Table
    print("\n📊 API OVERVIEW\n")
    print("=" * 110)
    print(f"{'Service':<30} {'Sign-Up':<10} {'Cost':<15} {'Rate Limit':<25} {'Commercial':<12}")
    print("=" * 110)
    
    for diag in DIAGNOSTICS:
        signup = "✅ Required" if diag["requires_signup"] else "❌ Not needed"
        cost = "💰 Paid" if diag["requires_payment"] else "✅ FREE"
        commercial = "✅ Yes" if diag["commercial_use"] else "❌ No"
        
        print(f"{diag['api_name']:<30} {signup:<10} {cost:<15} {diag['rate_limit']:<25} {commercial:<12}")
    
    print("=" * 110 + "\n")

if __name__ == "__main__":
    if not GITHUB_TOKEN:
        print("❌ Error: GH_TOKEN environment variable not set")
        print("   Run: export GH_TOKEN=your_github_token")
        exit(1)
    
    main()
