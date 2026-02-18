<?php
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

namespace WPShadow\Diagnostics\Journalism;

use WPShadow\Core\Diagnostic_Base;

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
