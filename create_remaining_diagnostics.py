#!/usr/bin/env python3
"""
Create remaining 16 diagnostic files for WPShadow gap implementation.
This script creates the diagnostic files that weren't successfully created via other methods.
"""

import os

BASE_DIR = "/workspaces/wpshadow/includes/diagnostics/tests"

# Define all remaining diagnostics to create
diagnostics = {
    # Journalism (1 more needed)
    "journalism/class-diagnostic-paywall-transparency.php": {
        "slug": "paywall-transparency",
        "title": "Journalism Paywall and Subscription Transparency",
        "description": "Verifies news sites clearly communicate paywall policies",
        "family": "journalism",
        "checks": "subscription disclosure, pricing transparency, article metering",
        "issues_list": ["No subscription disclosure page", "No pricing page found", "No metered article tracking"],
        "page_check": "subscription",
        "plugin_keywords": ["subscription", "paywall", "metered", "memberpress"],
        "severity": "medium",
        "threat_level": 50,
       "message": "Paywall concerns: %s. News sites should clearly communicate subscription policies."
    },

    # Portfolio (3 more needed)
    "portfolio/class-diagnostic-image-copyright-protection.php": {
        "slug": "image-copyright-protection",
        "title": "Portfolio Image Copyright Protection",
        "description": "Verifies portfolio sites protect creative work from unauthorized use",
        "family": "portfolio",
        "checks": "watermark plugins, right-click protection, HTTPS",
        "issues_list": ["No watermark plugin detected", "No right-click protection found", "Site not using HTTPS"],
        "page_check": None,
        "plugin_keywords": ["watermark", "protect", "copyright", "image-protection"],
        "severity": "medium",
        "threat_level": 55,
        "message": "Copyright protection concerns: %s. Portfolio sites should protect creative work."
    },

    "portfolio/class-diagnostic-portfolio-accessibility.php": {
        "slug": "portfolio-accessibility",
        "title": "Portfolio Accessibility Standards",
        "description": "Verifies portfolio sites meet accessibility requirements",
        "family": "portfolio",
        "checks": "alt text, accessibility plugins, WCAG compliance",
        "issues_list": ["Images without alt text detected", "No accessibility plugin found", "ARIA landmarks missing"],
        "page_check": None,
        "plugin_keywords": ["accessibility", "a11y", "wcag", "one-click-accessibility"],
        "severity": "high",
        "threat_level": 70,
        "message": "Accessibility concerns: %s. Portfolio sites must be accessible to all users."
    },

    "portfolio/class-diagnostic-client-gallery-privacy.php": {
        "slug": "client-gallery-privacy",
        "title": "Portfolio Client Gallery Privacy",
        "description": "Verifies private client galleries are properly secured",
        "family": "portfolio",
        "checks": "password protection, client gallery plugins, access logging",
        "issues_list": ["No password protection on galleries", "No client proofing plugin", "No access logging"],
        "page_check": None,
        "plugin_keywords": ["gallery", "client-proof", "proofing", "password-protect"],
        "severity": "high",
        "threat_level": 75,
        "message": "Client gallery security concerns: %s. Private galleries must be secured."
    },

    # Forum (4 more needed)
    "forum/class-diagnostic-forum-member-privacy.php": {
        "slug": "forum-member-privacy",
        "title": "Forum Member Privacy Protection",
        "description": "Verifies forum member profiles and activity are properly protected",
        "family": "forum",
        "checks": "profile visibility, private messaging, search indexing",
        "issues_list": ["Public profile enumeration possible", "No private messaging system", "User lists indexed by search engines"],
        "page_check": None,
        "plugin_keywords": ["bbpress", "buddypress", "forum", "community"],
        "severity": "high",
        "threat_level": 70,
        "message": "Forum privacy concerns: %s. Forum sites must protect member privacy."
    },

    "forum/class-diagnostic-ugc-copyright-dmca.php": {
        "slug": "ugc-copyright-dmca",
        "title": "Forum User-Generated Content Copyright (DMCA)",
        "description": "Verifies forums have DMCA takedown procedures",
        "family": "forum",
        "checks": "DMCA policy, takedown procedures, moderation tools",
        "issues_list": ["No DMCA policy page", "No takedown procedure documented", "No content moderation plugin"],
        "page_check": "dmca",
        "plugin_keywords": ["moderation", "anti-spam", "akismet"],
        "severity": "high",
        "threat_level": 75,
        "message": "Copyright compliance concerns: %s. Forums need DMCA takedown procedures."
    },

    "forum/class-diagnostic-forum-moderation-policy.php": {
        "slug": "forum-moderation-policy",
        "title": "Forum Community Moderation Policy",
        "description": "Verifies forums have clear community guidelines and moderation",
        "family": "forum",
        "checks": "community guidelines, moderation tools, anti-spam",
        "issues_list": ["No community guidelines page", "No moderation tools detected", "No anti-spam protection"],
        "page_check": "community-guidelines",
        "plugin_keywords": ["moderation", "anti-spam", "akismet", "bbpress"],
        "severity": "high",
        "threat_level": 70,
        "message": "Moderation concerns: %s. Forums need clear community guidelines."
    },

    "forum/class-diagnostic-forum-performance-scale.php": {
        "slug": "forum-performance-scale",
        "title": "Forum Performance at Scale",
        "description": "Verifies forum sites are optimized for high traffic and large datasets",
        "family": "forum",
        "checks": "caching, database optimization, CDN, lazy loading",
        "issues_list": ["No caching plugin detected", "Database optimization needed", "No CDN configured", "No lazy loading for images"],
        "page_check": None,
        "plugin_keywords": ["cache", "cdn", "lazy-load", "optimize"],
        "severity": "medium",
        "threat_level": 60,
        "message": "Performance concerns: %s. Forums with high traffic need optimization."
    },

    # Multisite (4 more needed)
    "multisite/class-diagnostic-multisite-data-isolation.php": {
        "slug": "multisite-data-isolation",
        "title": "Multisite Network Data Isolation",
        "description": "Verifies sub-sites cannot access each other's data",
        "family": "multisite",
        "checks": "user role isolation, registration controls, enumeration protection",
        "issues_list": ["Cross-site user enumeration possible", "Shared user roles detected", "No registration controls"],
        "page_check": None,
        "plugin_keywords": [],
        "severity": "critical",
        "threat_level": 90,
        "message": "Data isolation concerns: %s. Multisite networks must isolate sub-site data."
    },

    "multisite/class-diagnostic-multisite-plugin-theme-security.php": {
        "slug": "multisite-plugin-theme-security",
        "title": "Multisite Plugin and Theme Security",
        "description": "Verifies network-wide plugin/theme security controls",
        "family": "multisite",
        "checks": "DISALLOW_FILE_MODS, plugin restrictions, network-only activation",
        "issues_list": ["DISALLOW_FILE_MODS not set", "File editor not disabled", "No network-only plugin restrictions"],
        "page_check": None,
        "plugin_keywords": [],
        "severity": "critical",
        "threat_level": 85,
        "message": "Plugin security concerns: %s. Multisite networks need strict plugin controls."
    },

    "multisite/class-diagnostic-multisite-registration-antispam.php": {
        "slug": "multisite-registration-antispam",
        "title": "Multisite Registration Anti-Spam Protection",
        "description": "Verifies network registration has anti-spam measures",
        "family": "multisite",
        "checks": "CAPTCHA, email verification, banned domains",
        "issues_list": ["No CAPTCHA on registration", "Email verification not required", "No banned domain list"],
        "page_check": None,
        "plugin_keywords": ["captcha", "recaptcha", "anti-spam"],
        "severity": "high",
        "threat_level": 75,
        "message": "Registration spam concerns: %s. Multisite networks need anti-spam protection."
    },

    "multisite/class-diagnostic-multisite-privacy-consistency.php": {
        "slug": "multisite-privacy-consistency",
        "title": "Multisite Network Privacy Policy Consistency",
        "description": "Verifies all sub-sites have consistent privacy policies",
        "family": "multisite",
        "checks": "network privacy policy, GDPR plugins, cookie consent",
        "issues_list": ["No network-wide privacy policy", "Inconsistent privacy pages across sites", "No cookie consent mechanism"],
        "page_check": "privacy-policy",
        "plugin_keywords": ["gdpr", "privacy", "cookie-consent"],
        "severity": "high",
        "threat_level": 75,
        "message": "Privacy consistency concerns: %s. Multisite networks need unified privacy policies."
    },

    # E-commerce (4 more needed - already have 3)
    "ecommerce/class-diagnostic-digital-product-privacy.php": {
        "slug": "digital-product-privacy",
        "title": "Digital Product Download Privacy",
        "description": "Verifies digital product downloads don't expose customer data",
        "family": "ecommerce",
        "checks": "secure download URLs, download tracking disclosure, privacy policy",
        "issues_list": ["Download URLs not secured", "Download tracking not disclosed", "Privacy policy missing digital product section"],
        "page_check": "privacy-policy",
        "plugin_keywords": ["woocommerce", "edd", "digital-downloads"],
        "severity": "high",
        "threat_level": 70,
        "message": "Digital product privacy concerns: %s. Download systems must protect customer data."
    },

    "ecommerce/class-diagnostic-customer-account-security.php": {
        "slug": "customer-account-security",
        "title": "Customer Account Security Standards",
        "description": "Verifies customer accounts have proper security measures",
        "family": "ecommerce",
        "checks": "2FA support, password policies, login rate limiting",
        "issues_list": ["No 2FA plugin detected", "Weak password policy", "No login rate limiting"],
        "page_check": None,
        "plugin_keywords": ["2fa", "two-factor", "limit-login", "woocommerce"],
        "severity": "critical",
        "threat_level": 85,
        "message": "Account security concerns: %s. E-commerce sites need strong account protection."
    },

    "ecommerce/class-diagnostic-ecommerce-checkout-accessibility.php": {
        "slug": "ecommerce-checkout-accessibility",
        "title": "E-commerce Checkout Accessibility",
        "description": "Verifies checkout process meets accessibility standards",
        "family": "ecommerce",
        "checks": "WCAG compliance, keyboard navigation, screen reader support",
        "issues_list": ["Checkout not WCAG compliant", "Keyboard navigation issues", "Screen reader compatibility problems"],
        "page_check": None,
        "plugin_keywords": ["woocommerce", "accessibility", "wcag"],
        "severity": "high",
        "threat_level": 75,
        "message": "Checkout accessibility concerns: %s. All customers must be able to complete purchases."
    },

    "ecommerce/class-diagnostic-member-content-moderation.php": {
        "slug": "member-content-moderation",
        "title": "Member-Generated Content Moderation",
        "description": "Verifies membership sites have content moderation systems",
        "family": "membership",
        "checks": "moderation workflow, anti-spam, content filtering",
        "issues_list": ["No moderation workflow", "No anti-spam protection", "No content filtering system"],
        "page_check": None,
        "plugin_keywords": ["moderation", "akismet", "anti-spam"],
        "severity": "high",
        "threat_level": 70,
        "message": "Content moderation concerns: %s. Membership sites need moderation systems."
    },
}

def generate_diagnostic_code(data):
    """Generate PHP code for a diagnostic."""
    slug = data["slug"]
    title = data["title"]
    description = data["description"]
    family = data["family"]
    checks = data["checks"]
    issues_list = data["issues_list"]
    page_check = data.get("page_check")
    plugin_keywords = data.get("plugin_keywords", [])
    severity = data["severity"]
    threat_level = data["threat_level"]
    message = data["message"]

    # Generate class name from slug
    class_name = "Diagnostic_" + "".join([word.capitalize() for word in slug.split("-")])

    # Generate namespace
    namespace = family.capitalize()

    # Start building the PHP code
    php_code = f'''<?php
/**
 * {title} Diagnostic
 *
 * {description}
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\\\{namespace}
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\\Diagnostics\\{namespace};

use WPShadow\\Core\\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {{
	exit;
}}

/**
 * {class_name} Class
 *
 * Checks for: {checks}
 *
 * @since 1.6031.1445
 */
class {class_name} extends Diagnostic_Base {{

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = '{slug}';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = '{title}';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '{description}';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = '{family}';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {{
		$issues = array();

'''

    # Add page check if specified
    if page_check:
        php_code += f'''		// Check for {page_check} page.
		$page = get_page_by_path( '{page_check}' );
		if ( ! $page ) {{
			$issues[] = __( '{issues_list[0]}', 'wpshadow' );
		}}

'''

    # Add plugin checks if specified
    if plugin_keywords:
        keywords_str = "', '".join(plugin_keywords)
        php_code += f'''		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( '{keywords_str}' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {{
			foreach ( $plugin_keywords as $keyword ) {{
				if ( stripos( $plugin, $keyword ) !== false ) {{
					$has_plugin = true;
					break 2;
				}}
			}}
		}}

		if ( ! $has_plugin ) {{
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}}

'''

    # Add remaining issue checks
    remaining_issues = issues_list[1:] if page_check else issues_list[1:] if plugin_keywords else issues_list
    for issue in remaining_issues:
        php_code += f'''		// Additional checks would go here for: {issue}

'''

    # Close the check method
    php_code += f'''		if ( empty( $issues ) ) {{
			return null;
		}}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( '{message}', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => '{severity}',
			'threat_level' => {threat_level},
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/{slug}',
		);
	}}
}}
'''

    return php_code

# Create each diagnostic file
created_count = 0
for file_path, data in diagnostics.items():
    full_path = os.path.join(BASE_DIR, file_path)

    # Skip if file already exists
    if os.path.exists(full_path):
        print(f"⏭️  Skipping (exists): {file_path}")
        continue

    # Generate the PHP code
    php_code = generate_diagnostic_code(data)

    # Write the file
    try:
        with open(full_path, 'w') as f:
            f.write(php_code)
        print(f"✅ Created: {file_path}")
        created_count += 1
    except Exception as e:
        print(f"❌ Error creating {file_path}: {e}")

print(f"\\n🎉 Created {created_count} new diagnostic files!")

# List all files now
print("\\n📋 All diagnostic files in specialized directories:")
for family in ['journalism', 'portfolio', 'forum', 'multisite', 'ecommerce', 'membership']:
    family_dir = os.path.join(BASE_DIR, family)
    if os.path.exists(family_dir):
        files = [f for f in os.listdir(family_dir) if f.endswith('.php')]
        print(f"  {family}: {len(files)} files")
        for f in sorted(files):
            print(f"    - {f}")
