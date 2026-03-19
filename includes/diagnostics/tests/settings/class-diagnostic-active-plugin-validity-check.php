<?php
/**
 * Active Plugin Validity Check Diagnostic
 *
 * Ensures active plugin files exist and are readable.
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
 * Active Plugin Validity Check Diagnostic
 *
 * Detects missing or invalid plugin files in the active list.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Active_Plugin_Validity_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'active-plugin-validity-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Active Plugin Validity Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures active plugin files exist and are readable';

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
		$active_plugins = get_option( 'active_plugins', array() );
		$all_plugins    = get_plugins();
		$missing        = array();
		$unreadable     = array();

		foreach ( $active_plugins as $plugin_file ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

			if ( ! file_exists( $plugin_path ) ) {
				$missing[] = $plugin_file;
				continue;
			}

			if ( ! is_readable( $plugin_path ) ) {
				$unreadable[] = $plugin_file;
			}

			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				$missing[] = $plugin_file;
			}
		}

		if ( empty( $missing ) && empty( $unreadable ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Active plugin list contains invalid entries', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/active-plugin-validity-check',
			'details'      => array(
				'missing'    => $missing,
				'unreadable' => $unreadable,
			),
		);
	}
}
