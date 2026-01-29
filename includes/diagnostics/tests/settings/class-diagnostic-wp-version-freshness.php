<?php
/**
 * WordPress Version Freshness Diagnostic
 *
 * Checks if WordPress core is updated to the latest version
 * to prevent security vulnerabilities from outdated software.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Version Freshness Class
 *
 * Validates WordPress is running the latest version.
 * 90% of hacks exploit known vulnerabilities in old versions.
 *
 * @since 1.5029.1045
 */
class Diagnostic_WP_Version_Freshness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-version-freshness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Version Freshness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WordPress is updated to latest version';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Compares current WordPress version to latest available version.
	 * Flags if 2+ versions behind or major version outdated.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if outdated, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_wp_version_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get current version using WordPress global (NO $wpdb).
		global $wp_version;
		$current_version = $wp_version;

		// Get latest version from WordPress API.
		require_once ABSPATH . 'wp-admin/includes/update.php';
		wp_version_check();

		$update_data = get_site_transient( 'update_core' );

		if ( ! $update_data || empty( $update_data->updates ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		// Find the latest stable version.
		$latest_version = null;
		foreach ( $update_data->updates as $update ) {
			if ( 'latest' === $update->response && ! empty( $update->version ) ) {
				$latest_version = $update->version;
				break;
			}
		}

		if ( ! $latest_version ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		// Compare versions.
		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			// Calculate version lag.
			$current_parts = explode( '.', $current_version );
			$latest_parts  = explode( '.', $latest_version );

			$major_behind = (int) $latest_parts[0] - (int) $current_parts[0];
			$minor_behind = (int) $latest_parts[1] - (int) $current_parts[1];

			$is_major_outdated = $major_behind > 0;
			$is_multiple_versions_behind = $minor_behind >= 2;

			// Determine threat level.
			$threat_level = 40;
			if ( $is_major_outdated ) {
				$threat_level = 75;
			} elseif ( $is_multiple_versions_behind ) {
				$threat_level = 60;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current version, 2: latest version */
					__( 'WordPress is outdated. Current: %1$s, Latest: %2$s. Update immediately to patch security vulnerabilities.', 'wpshadow' ),
					$current_version,
					$latest_version
				),
				'severity'     => $threat_level > 60 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/settings-wp-version-freshness',
				'data'         => array(
					'current_version'   => $current_version,
					'latest_version'    => $latest_version,
					'major_behind'      => $major_behind,
					'minor_behind'      => $minor_behind,
					'is_major_outdated' => $is_major_outdated,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
