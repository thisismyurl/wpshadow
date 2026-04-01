<?php
/**
 * Plugin Documentation Quality Diagnostic
 *
 * Checks for missing documentation hints such as readme files.
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
 * Plugin Documentation Quality Diagnostic Class
 *
 * Detects active plugins lacking basic documentation markers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Documentation_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-documentation-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Documentation Quality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins include basic documentation files';

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
		$missing_docs = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			$readme = $plugin_dir . '/readme.txt';
			$has_readme = file_exists( $readme );

			$description = $all_plugins[ $plugin_file ]['Description'] ?? '';
			if ( ! $has_readme && strlen( trim( $description ) ) < 20 ) {
				$missing_docs[] = $all_plugins[ $plugin_file ]['Name'];
			}
		}

		if ( ! empty( $missing_docs ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some active plugins appear to lack basic documentation such as readme files.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'plugins' => array_slice( $missing_docs, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-documentation-quality?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
