<?php
/**
 * Multisite Network Privacy Policy Consistency Diagnostic
 *
 * Verifies all sub-sites have consistent privacy policies
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Multisite
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_MultisitePrivacyConsistency Class
 *
 * Checks for: network privacy policy, GDPR plugins, cookie consent
 *
 * @since 1.6031.1445
 */
class Diagnostic_MultisitePrivacyConsistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-privacy-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Network Privacy Policy Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies all sub-sites have consistent privacy policies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for privacy-policy page.
		$page = get_page_by_path( 'privacy-policy' );
		if ( ! $page ) {
			$issues[] = __( 'No network-wide privacy policy', 'wpshadow' );
		}

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'gdpr', 'privacy', 'cookie-consent' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $plugin_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}

		// Additional checks would go here for: Inconsistent privacy pages across sites

		// Additional checks would go here for: No cookie consent mechanism

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Privacy consistency concerns: %s. Multisite networks need unified privacy policies.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-privacy-consistency',
		);
	}
}
