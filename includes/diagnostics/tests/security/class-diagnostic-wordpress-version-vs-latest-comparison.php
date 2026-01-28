<?php
/**
 * WordPress Version vs Latest Comparison Diagnostic
 *
 * Checks WordPress version against latest stable release and security update timing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Version vs Latest Comparison Class
 *
 * Tests whether WordPress is up to date.
 *
 * @since 1.26028.1905
 */
class Diagnostic_WordPress_Version_Vs_Latest_Comparison extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-version-vs-latest-comparison';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Version vs Latest Comparison';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks WordPress version against latest stable release and security update timing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		$version_info = self::get_version_comparison();
		
		if ( ! $version_info['is_latest'] ) {
			$severity = 'medium';
			if ( $version_info['versions_behind'] > 5 ) {
				$severity = 'critical';
			} elseif ( $version_info['versions_behind'] > 2 ) {
				$severity = 'high';
			}

			$issues = array();
			
			$issues[] = sprintf(
				/* translators: 1: current version, 2: latest version */
				__( 'WordPress %1$s installed, %2$s is latest', 'wpshadow' ),
				$wp_version,
				$version_info['latest_version']
			);

			if ( $version_info['versions_behind'] > 1 ) {
				$issues[] = sprintf(
					/* translators: %d: number of versions behind */
					__( '(%d versions behind)', 'wpshadow' ),
					$version_info['versions_behind']
				);
			}

			if ( ! $version_info['auto_updates_enabled'] ) {
				$issues[] = __( 'Auto-updates disabled (security risk)', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => $severity,
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-version-vs-latest-comparison',
				'meta'         => array(
					'current_version'       => $wp_version,
					'latest_version'        => $version_info['latest_version'],
					'versions_behind'       => $version_info['versions_behind'],
					'auto_updates_enabled'  => $version_info['auto_updates_enabled'],
				),
			);
		}

		return null;
	}

	/**
	 * Get version comparison information.
	 *
	 * @since  1.26028.1905
	 * @return array Version information.
	 */
	private static function get_version_comparison() {
		global $wp_version;

		$info = array(
			'is_latest'            => true,
			'latest_version'       => $wp_version,
			'versions_behind'      => 0,
			'auto_updates_enabled' => false,
		);

		// Check for core updates.
		wp_version_check();
		$updates = get_core_updates();

		if ( ! empty( $updates ) && 'latest' !== $updates[0]->response ) {
			$info['is_latest'] = false;
			$info['latest_version'] = $updates[0]->version;
			
			// Calculate versions behind.
			$current_parts = explode( '.', $wp_version );
			$latest_parts = explode( '.', $info['latest_version'] );

			if ( isset( $current_parts[1] ) && isset( $latest_parts[1] ) ) {
				$info['versions_behind'] = (int) $latest_parts[1] - (int) $current_parts[1];
			}
		}

		// Check if auto-updates are enabled.
		$auto_updates_enabled = get_site_option( 'auto_update_core_major', false );
		if ( ! $auto_updates_enabled ) {
			// Check for minor/security updates.
			$auto_updates_enabled = defined( 'WP_AUTO_UPDATE_CORE' ) && WP_AUTO_UPDATE_CORE;
		}

		$info['auto_updates_enabled'] = (bool) $auto_updates_enabled;

		return $info;
	}
}
