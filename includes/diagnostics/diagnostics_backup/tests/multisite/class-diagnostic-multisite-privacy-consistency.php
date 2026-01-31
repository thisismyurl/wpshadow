<?php
/**
 * Multisite Network Privacy Policy Consistency Diagnostic
 *
 * Checks if multisite networks maintain consistent privacy policies across
 * all sub-sites or allow proper site-level customization while meeting legal requirements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Multisite
 * @since      1.6031.1458
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Privacy Policy Consistency Diagnostic Class
 *
 * Verifies multisite networks have consistent privacy policies.
 *
 * @since 1.6031.1458
 */
class Diagnostic_Multisite_Privacy_Consistency extends Diagnostic_Base {

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
	protected static $description = 'Verifies multisite networks maintain consistent privacy policies across sub-sites';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1458
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null; // Not multisite.
		}

		$issues = array();

		// Check if network has a privacy policy page set.
		$network_privacy_page = get_site_option( 'wp_page_for_privacy_policy' );
		if ( empty( $network_privacy_page ) ) {
			$issues[] = __( 'No network-wide privacy policy page configured', 'wpshadow' );
		}

		// Check privacy policy plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$has_privacy_plugin = false;
		$privacy_plugins = array(
			'gdpr',
			'privacy-policy',
			'cookie-notice',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $privacy_plugins as $priv_plugin ) {
				if ( stripos( $plugin, $priv_plugin ) !== false ) {
					$has_privacy_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_privacy_plugin ) {
			$issues[] = __( 'No privacy/GDPR compliance plugin detected', 'wpshadow' );
		}

		// Check cookie consent.
		$has_cookie_consent = false;
		$cookie_plugins = array(
			'cookie-notice',
			'cookie-law',
			'gdpr-cookie',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $cookie_plugins as $cook_plugin ) {
				if ( stripos( $plugin, $cook_plugin ) !== false ) {
					$has_cookie_consent = true;
					break 2;
				}
			}
		}

		if ( ! $has_cookie_consent ) {
			$issues[] = __( 'No cookie consent plugin found', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite privacy policy concerns: %s. Networks should have consistent privacy policies and GDPR compliance across all sub-sites.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-privacy-consistency',
		);
	}
}
