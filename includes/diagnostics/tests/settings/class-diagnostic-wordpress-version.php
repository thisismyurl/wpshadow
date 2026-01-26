<?php
/**
 * Diagnostic: WordPress Core Version
 *
 * Checks if WordPress core is up to date.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WordPress_Version Class
 *
 * Detects if WordPress core is outdated. WordPress releases regular updates
 * for security, performance, and bug fixes. Keeping WordPress current is
 * essential for:
 *
 * - Security: Critical patches for discovered vulnerabilities
 * - Performance: Optimization improvements in each release
 * - Compatibility: New plugins/themes require minimum versions
 * - Stability: Bug fixes and reliability improvements
 *
 * Returns different threat levels based on how far behind the current version.
 *
 * @since 1.2601.2200
 */
class Diagnostic_WordPress_Version extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-version';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Version';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress core is up to date with latest security and performance patches';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Gets current WordPress version and compares against thresholds.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if WordPress is outdated, null if current.
	 */
	public static function check() {
		global $wp_version;

		$current_version = $wp_version;

		// For this diagnostic, we can't easily get "latest" without remote calls
		// So we use a threshold: if more than 1 major version behind, flag it
		// Parse current version
		$current_parts = explode( '.', $current_version );
		$current_major = isset( $current_parts[0] ) ? (int) $current_parts[0] : 0;
		$current_minor = isset( $current_parts[1] ) ? (int) $current_parts[1] : 0;

		// As of January 2026, latest is WordPress 6.4+
		// If current is 6.3 or lower, suggest update
		$latest_major = 6;
		$latest_minor = 4;

		// If current version is same or newer, all good
		if ( $current_major > $latest_major || 
			( $current_major === $latest_major && $current_minor >= $latest_minor ) ) {
			return null;
		}

		// If major version is significantly behind (e.g., 5.x when 6.x is available)
		if ( $current_major < $latest_major ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current version, 2: latest version */
					esc_html__( 'Your WordPress version is %1$s. The latest version is %2$s and includes important security updates and performance improvements. Update WordPress now through the WordPress admin panel.', 'wpshadow' ),
					$current_version,
					'6.4'
				),
				'severity'           => 'high',
				'threat_level'       => 65,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-wordpress-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version' => $current_version,
					'latest_version'  => '6.4',
					'recommendation'  => 'Update WordPress via WordPress admin > Updates',
				),
			);
		}

		// If minor version is behind (e.g., 6.3 when 6.4 is available)
		if ( $current_major === $latest_major && $current_minor < $latest_minor ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current version, 2: latest version */
					esc_html__( 'Your WordPress version is %1$s. Version %2$s is available with performance improvements and security patches. Update WordPress now.', 'wpshadow' ),
					$current_version,
					'6.4'
				),
				'severity'           => 'medium',
				'threat_level'       => 35,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-wordpress-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version' => $current_version,
					'latest_version'  => '6.4',
					'recommendation'  => 'Update WordPress via WordPress admin > Updates',
				),
			);
		}

		return null;
	}
}
