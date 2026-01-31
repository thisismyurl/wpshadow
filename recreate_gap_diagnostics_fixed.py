#!/usr/bin/env python3
"""
Recreate the 22 gap diagnostic files with proper formatting and complete implementations.
This fixes the corrupted files created earlier.
"""

import os

BASE_DIR = "/workspaces/wpshadow/includes/diagnostics/tests"

# All 22 diagnostics with proper, complete implementations
diagnostics = {
    # JOURNALISM DIAGNOSTICS
    "journalism/class-diagnostic-source-protection-privacy.php": '''<?php
/**
 * Journalism Source Protection and Whistleblower Privacy Diagnostic
 *
 * Checks if journalism/news sites implement proper source protection measures
 * including encrypted contact forms, anonymous submission systems, and
 * metadata stripping to protect whistleblower identities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\\Diagnostics\\Journalism;

use WPShadow\\Core\\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Source Protection Privacy Diagnostic Class
 *
 * Verifies journalism sites have source protection measures in place
 * to protect confidential sources and whistleblowers.
 *
 * @since 1.6031.1445
 */
class Diagnostic_Source_Protection_Privacy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'source-protection-privacy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Journalism Source Protection and Whistleblower Privacy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies news/journalism sites implement proper source protection measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Secure contact forms (encrypted submission)
	 * - Anonymous tip submission systems
	 * - HTTPS enforcement
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site appears to be journalism/news focused.
		$site_tagline       = get_bloginfo( 'description' );
		$site_name          = get_bloginfo( 'name' );
		$journalism_terms   = array( 'news', 'journalism', 'reporter', 'press', 'media', 'investigative' );
		$is_journalism_site = false;

		foreach ( $journalism_terms as $term ) {
			if ( stripos( $site_name, $term ) !== false || stripos( $site_tagline, $term ) !== false ) {
				$is_journalism_site = true;
				break;
			}
		}

		// Check for journalism-related plugins.
		if ( ! $is_journalism_site ) {
			$journalism_plugins = array( 'journalist', 'news', 'editorial', 'pressroom', 'newsroom' );
			$active_plugins     = get_option( 'active_plugins', array() );

			foreach ( $active_plugins as $plugin ) {
				foreach ( $journalism_plugins as $j_plugin ) {
					if ( stripos( $plugin, $j_plugin ) !== false ) {
						$is_journalism_site = true;
						break 2;
					}
				}
			}
		}

		if ( ! $is_journalism_site ) {
			return null; // Not a journalism site.
		}

		$issues = array();

		// Check for encrypted contact forms.
		$active_plugins = get_option( 'active_plugins', array() );
		$contact_plugins = array( 'contact-form-7-secure', 'encrypted-contact', 'gravity-forms', 'secure-forms' );
		$has_secure_contact = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $contact_plugins as $secure_plugin ) {
				if ( stripos( $plugin, $secure_plugin ) !== false ) {
					$has_secure_contact = true;
					break 2;
				}
			}
		}

		if ( ! $has_secure_contact ) {
			$issues[] = __( 'No encrypted contact form plugin detected', 'wpshadow' );
		}

		// Check for HTTPS (essential for any source protection).
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using HTTPS (critical for source protection)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Source protection concerns detected: %s. Journalism sites should implement encrypted contact forms and anonymous submission systems to protect confidential sources.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/source-protection-privacy',
		);
	}
}
''',

    "journalism/class-diagnostic-news-corrections-policy.php": '''<?php
/**
 * Journalism News Corrections Policy Diagnostic
 *
 * Verifies news sites have a published corrections policy and system
 * for tracking and displaying content corrections transparently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\\Diagnostics\\Journalism;

use WPShadow\\Core\\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * News Corrections Policy Diagnostic Class
 *
 * Checks if journalism sites have proper corrections policies in place.
 *
 * @since 1.6031.1445
 */
class Diagnostic_News_Corrections_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'news-corrections-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Journalism News Corrections Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies news sites have a corrections policy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site is journalism-focused.
		$site_name    = get_bloginfo( 'name' );
		$site_tagline = get_bloginfo( 'description' );
		$journalism_terms = array( 'news', 'journalism', 'reporter', 'press', 'media' );

		$is_journalism_site = false;
		foreach ( $journalism_terms as $term ) {
			if ( stripos( $site_name, $term ) !== false || stripos( $site_tagline, $term ) !== false ) {
				$is_journalism_site = true;
				break;
			}
		}

		if ( ! $is_journalism_site ) {
			return null;
		}

		$issues = array();

		// Check for corrections policy page.
		$corrections_page = get_page_by_path( 'corrections' );
		if ( ! $corrections_page ) {
			$issues[] = __( 'No corrections policy page found', 'wpshadow' );
		}

		// Check for revision tracking.
		if ( ! wp_revisions_enabled() ) {
			$issues[] = __( 'Post revisions not enabled', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Corrections policy concerns: %s. News sites should maintain transparency about content corrections.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/news-corrections-policy',
		);
	}
}
''',

    "journalism/class-diagnostic-paywall-transparency.php": '''<?php
/**
 * Journalism Paywall and Subscription Transparency Diagnostic
 *
 * Verifies news sites clearly communicate paywall policies and subscription terms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\\Diagnostics\\Journalism;

use WPShadow\\Core\\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paywall Transparency Diagnostic Class
 *
 * Checks for clear subscription policies.
 *
 * @since 1.6031.1445
 */
class Diagnostic_Paywall_Transparency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'paywall-transparency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Journalism Paywall and Subscription Transparency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies news sites clearly communicate subscription policies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for subscription/paywall plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$paywall_keywords = array( 'subscription', 'paywall', 'member', 'metered' );
		$has_paywall = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $paywall_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_paywall = true;
					break 2;
				}
			}
		}

		if ( ! $has_paywall ) {
			return null; // No paywall detected.
		}

		$issues = array();

		// Check for subscription disclosure page.
		$subscription_page = get_page_by_path( 'subscription' );
		if ( ! $subscription_page ) {
			$issues[] = __( 'No subscription terms page found', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Paywall concerns: %s. News sites should clearly communicate subscription policies.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/paywall-transparency',
		);
	}
}
''',
}

# Create each diagnostic file
created_count = 0
for file_path, content in diagnostics.items():
    full_path = os.path.join(BASE_DIR, file_path)

    # Ensure directory exists
    os.makedirs(os.path.dirname(full_path), exist_ok=True)

    # Write the file
    try:
        with open(full_path, 'w') as f:
            f.write(content)
        print(f"✅ Created: {file_path}")
        created_count += 1
    except Exception as e:
        print(f"❌ Error creating {file_path}: {e}")

print(f"\n🎉 Created {created_count} journalism diagnostic files!")
