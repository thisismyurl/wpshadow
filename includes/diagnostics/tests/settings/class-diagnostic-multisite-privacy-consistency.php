<?php
/**
 * Multisite Network Privacy Policy Consistency Diagnostic
 *
 * Verifies all sub-sites have consistent privacy policies
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
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
 * Checks for network privacy policy, GDPR plugins, cookie consent
 *
 * @since 0.6093.1200
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
 * @since 0.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Only run on multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check for network-wide privacy policy.
		$network_privacy_page = get_site_option( 'wp_page_for_privacy_policy' );
		if ( ! $network_privacy_page ) {
			$issues[] = __( 'No network-wide privacy policy configured', 'wpshadow' );
		}

		// Check if subsites have inconsistent privacy settings.
		$sites = get_sites( array( 'number' => 10 ) );
		$privacy_settings = array();

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$privacy_settings[ $site->blog_id ] = get_option( 'blog_public' );
			restore_current_blog();
		}

		// Check if settings are inconsistent.
		$unique_settings = array_unique( $privacy_settings );
		if ( count( $unique_settings ) > 1 ) {
			$issues[] = __( 'Subsites have inconsistent privacy settings', 'wpshadow' );
		}

		// Check for GDPR compliance plugins.
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$gdpr_plugins = array( 'gdpr', 'cookie-notice', 'cookie-law', 'privacy' );
		$has_gdpr = false;

		foreach ( array_keys( $active_plugins ) as $plugin ) {
			foreach ( $gdpr_plugins as $gdpr_plugin ) {
				if ( stripos( $plugin, $gdpr_plugin ) !== false ) {
					$has_gdpr = true;
					break 2;
				}
			}
		}

		if ( ! $has_gdpr ) {
			$issues[] = __( 'No network-wide GDPR/privacy plugin detected', 'wpshadow' );
		}

		// Check for data retention policies.
		$retention_policy = get_site_option( 'wpshadow_data_retention_days' );
		if ( ! $retention_policy ) {
			$issues[] = __( 'No network data retention policy configured', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite privacy concerns: %s. Network should have consistent privacy policies.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-privacy-consistency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
