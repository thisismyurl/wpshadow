<?php
/**
 * Inactive Plugin Cleanup Diagnostic
 *
 * Identifies inactive plugins that should be removed or cleaned up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inactive Plugin Cleanup Diagnostic
 *
 * Highlights inactive plugins that add maintenance and security risk.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Inactive_Plugin_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inactive-plugin-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inactive Plugin Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies inactive plugins that should be removed or cleaned up';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins   = get_option( 'active_plugins', array() );
		$all_plugins      = get_plugins();
		$inactive_plugins = array();
		$stale_plugins    = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				continue;
			}

			$inactive_plugins[] = $plugin_data['Name'] ?? $plugin_file;

			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
			if ( file_exists( $plugin_path ) ) {
				$modified = filemtime( $plugin_path );
				if ( $modified && ( time() - $modified ) > YEAR_IN_SECONDS ) {
					$stale_plugins[] = $plugin_data['Name'] ?? $plugin_file;
				}
			}
		}

		if ( empty( $inactive_plugins ) ) {
			return null;
		}

		$issues = array();
		if ( count( $inactive_plugins ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive plugins */
				__( '%d inactive plugins installed', 'wpshadow' ),
				count( $inactive_plugins )
			);
		}

		if ( ! empty( $stale_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of stale plugins */
				__( '%d inactive plugins have not been updated in over a year', 'wpshadow' ),
				count( $stale_plugins )
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Inactive plugins add maintenance and security risk', 'wpshadow' ),
			'severity'     => count( $inactive_plugins ) > 20 ? 'high' : 'medium',
			'threat_level' => min( 40 + ( count( $inactive_plugins ) * 2 ), 80 ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/inactive-plugin-cleanup',
			'details'      => array(
				'inactive_plugins' => $inactive_plugins,
				'stale_plugins'    => $stale_plugins,
				'issues'           => $issues,
			),
		);
	}
}
