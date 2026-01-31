<?php
/**
 * Automatic Updates Not Configured Diagnostic
 *
 * Checks if automatic updates are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatic Updates Not Configured Diagnostic Class
 *
 * Detects missing automatic update configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Automatic_Updates_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automatic-updates-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automatic Updates Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if automatic updates are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for automatic security updates
		if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) || ! AUTOMATIC_UPDATER_DISABLED ) {
			// WordPress automatic updates are allowed
			if ( defined( 'WP_AUTO_UPDATE_CORE' ) && constant( 'WP_AUTO_UPDATE_CORE' ) ) {
				return null; // Automatic updates are enabled
			}
		}

		// Check WordPress version freshness
		global $wp_version;
		$wp_updates = wp_get_update_core();

		if ( is_object( $wp_updates ) && isset( $wp_updates->response ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automatic WordPress updates are not enabled. WordPress will not update automatically to security patches, leaving your site vulnerable.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/automatic-updates-not-configured',
			);
		}

		return null;
	}
}
