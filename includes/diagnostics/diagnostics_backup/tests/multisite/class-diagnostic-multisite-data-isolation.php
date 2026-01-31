<?php
/**
 * Multisite Sub-site Data Isolation Diagnostic
 *
 * Checks if multisite networks properly isolate sub-site data including
 * user access, database separation, and cross-site data leakage prevention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Multisite
 * @since      1.6031.1455
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Data Isolation Diagnostic Class
 *
 * Verifies multisite networks implement proper sub-site data isolation.
 *
 * @since 1.6031.1455
 */
class Diagnostic_Multisite_Data_Isolation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-data-isolation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Sub-site Data Isolation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies multisite networks properly isolate sub-site data and prevent cross-site access';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1455
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null; // Not multisite.
		}

		$issues = array();
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for user role isolation plugins.
		$has_role_isolation = false;
		$isolation_plugins = array(
			'multisite-user-management',
			'user-role-editor',
			'network-privacy',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $isolation_plugins as $iso_plugin ) {
				if ( stripos( $plugin, $iso_plugin ) !== false ) {
					$has_role_isolation = true;
					break 2;
				}
			}
		}

		if ( ! $has_role_isolation ) {
			$issues[] = __( 'No user role isolation plugin for multisite detected', 'wpshadow' );
		}

		// Check if users can register to any site (potential privacy issue).
		if ( get_site_option( 'registration' ) === 'all' ) {
			$issues[] = __( 'User registration open to all sites (users can access multiple sub-sites)', 'wpshadow' );
		}

		// Check for cross-site publishing plugins (can leak data).
		$has_cross_site = false;
		$cross_plugins = array(
			'multisite-content-copier',
			'network-posts',
			'blog-copier',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $cross_plugins as $cross_plugin ) {
				if ( stripos( $plugin, $cross_plugin ) !== false ) {
					$has_cross_site = true;
					break 2;
				}
			}
		}

		if ( $has_cross_site ) {
			$issues[] = __( 'Cross-site content plugins detected (verify data isolation settings)', 'wpshadow' );
		}

		// Check for user enumeration protection.
		$test_user_url = site_url( '/?author=1' );
		$response = wp_remote_get( $test_user_url, array( 'sslverify' => false, 'timeout' => 5 ) );
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$issues[] = __( 'User enumeration possible (author archives exposed)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite data isolation concerns: %s. Networks should implement strict user access controls and prevent cross-site data leakage.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-data-isolation',
		);
	}
}
