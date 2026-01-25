<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: WordPress Version Status
 *
 * Monitors WordPress core version and identifies when updates are available.
 * Running outdated WordPress increases security vulnerability risk significantly.
 *
 * @since 1.2.0
 */
class Test_Wordpress_Version_Status extends Diagnostic_Base {


	/**
	 * Check WordPress version status
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$version_status = self::check_wordpress_version();

		if ( $version_status['threat_level'] === 0 ) {
			return null;
		}

		return array(
			'threat_level'  => $version_status['threat_level'],
			'threat_color'  => $version_status['threat_color'],
			'passed'        => false,
			'issue'         => $version_status['issue'],
			'metadata'      => $version_status,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-core-updates/',
			'training_link' => 'https://wpshadow.com/training/wordpress-version-management/',
		);
	}

	/**
	 * Guardian Sub-Test: Current version check
	 *
	 * @return array Test result
	 */
	public static function test_current_version(): array {
		global $wp_version;

		return array(
			'test_name'   => 'Current Version',
			'version'     => $wp_version,
			'passed'      => true,
			'description' => sprintf( 'Running WordPress %s', $wp_version ),
		);
	}

	/**
	 * Guardian Sub-Test: Version update availability
	 *
	 * @return array Test result
	 */
	public static function test_update_available(): array {
		$updates = get_transient( 'site_transient_update_core' );

		$has_update     = false;
		$latest_version = null;

		if ( $updates && isset( $updates->updates ) ) {
			foreach ( $updates->updates as $update ) {
				if ( $update->response === 'upgrade' ) {
					$has_update     = true;
					$latest_version = $update->new_version;
					break;
				}
			}
		}

		global $wp_version;

		return array(
			'test_name'       => 'Update Availability',
			'current_version' => $wp_version,
			'has_update'      => $has_update,
			'latest_version'  => $latest_version,
			'passed'          => ! $has_update,
			'description'     => $has_update ? sprintf( 'Update available: %s', $latest_version ) : 'Running latest version',
		);
	}

	/**
	 * Guardian Sub-Test: Security release check
	 *
	 * @return array Test result
	 */
	public static function test_security_release(): array {
		// Get version parts
		global $wp_version;
		$version_parts = explode( '.', $wp_version );
		$major_version = intval( $version_parts[0] );
		$minor_version = intval( $version_parts[1] ?? 0 );

		// Check if version is very old (3+ major versions behind)
		$is_very_old = false;
		if ( $major_version < 6 ) {
			$is_very_old = true;
		}

		return array(
			'test_name'     => 'Security Release Status',
			'major_version' => $major_version,
			'minor_version' => $minor_version,
			'is_very_old'   => $is_very_old,
			'passed'        => ! $is_very_old,
			'description'   => $is_very_old ? sprintf( 'Version %s is quite old - recommend updating', $wp_version ) : sprintf( 'Version %s is current', $wp_version ),
		);
	}

	/**
	 * Guardian Sub-Test: Auto-update configuration
	 *
	 * @return array Test result
	 */
	public static function test_auto_update_config(): array {
		$core_auto_update   = get_option( 'auto_core_update_triggered' );
		$core_update_option = get_option( 'auto_update_core_dev' );

		// Check for major version auto-updates setting
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) ) {
			$auto_update_enabled = WP_AUTO_UPDATE_CORE === true || WP_AUTO_UPDATE_CORE === 'minor' || WP_AUTO_UPDATE_CORE === 'major';
		} else {
			$auto_update_enabled = true; // Default
		}

		return array(
			'test_name'       => 'Auto-Update Configuration',
			'auto_updates_on' => $auto_update_enabled,
			'passed'          => $auto_update_enabled,
			'description'     => $auto_update_enabled ? 'Core auto-updates enabled' : 'Core auto-updates disabled',
		);
	}

	/**
	 * Check WordPress version status
	 *
	 * @return array Version check results
	 */
	private static function check_wordpress_version(): array {
		global $wp_version;

		$threat_level = 0;
		$issue        = 'WordPress version is up to date';
		$threat_color = 'green';

		// Check if update available
		$updates = get_transient( 'site_transient_update_core' );

		if ( $updates && isset( $updates->updates ) ) {
			foreach ( $updates->updates as $update ) {
				if ( $update->response === 'upgrade' ) {
					$threat_level = 50;
					$threat_color = 'yellow';
					$issue        = sprintf( 'Update available: WordPress %s', $update->new_version );

					// Check if it's a security update
					if ( isset( $update->security ) && $update->security ) {
						$threat_level = 85;
						$threat_color = 'red';
						$issue        = sprintf( 'Security update available: WordPress %s', $update->new_version );
					}
					break;
				}
			}
		}

		// Check if version is very old
		$version_parts = explode( '.', $wp_version );
		$major_version = intval( $version_parts[0] );

		if ( $major_version < 6 ) {
			$threat_level = max( $threat_level, 70 );
			$threat_color = 'red';
			$issue        = sprintf( 'WordPress %s is outdated - version 6.0+ recommended', $wp_version );
		}

		return array(
			'threat_level'    => $threat_level,
			'threat_color'    => $threat_color,
			'issue'           => $issue,
			'current_version' => $wp_version,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'WordPress Version Status';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Monitors WordPress core version and identifies when updates are available';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Updates';
	}
}
