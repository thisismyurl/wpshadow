<?php
/**
 * Plugin Support & Maintenance Status Diagnostic
 *
 * Checks for plugins that appear outdated or unmaintained.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Support & Maintenance Status Diagnostic Class
 *
 * Flags active plugins whose main file hasn't changed in a long time.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Support_Maintenance_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-support-maintenance-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Support & Maintenance Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if active plugins appear unmaintained';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$all_plugins = get_plugins();
		$stale_plugins = array();

		$stale_threshold = time() - ( DAY_IN_SECONDS * 730 ); // 2 years.

		foreach ( $active_plugins as $plugin_file ) {
			$path = WP_PLUGIN_DIR . '/' . $plugin_file;
			if ( ! file_exists( $path ) ) {
				continue;
			}

			$modified = filemtime( $path );
			if ( $modified && $modified < $stale_threshold ) {
				$stale_plugins[] = $all_plugins[ $plugin_file ]['Name'] ?? $plugin_file;
			}
		}

		if ( ! empty( $stale_plugins ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some active plugins have not been updated in over two years. Consider verifying support status or switching to actively maintained alternatives.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'stale_plugins' => array_slice( $stale_plugins, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-support-maintenance-status?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
