<?php
/**
 * Plugin Activation Hook Bottleneck Diagnostic
 *
 * Checks for plugins with slow activation hooks.
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
 * Plugin Activation Hook Bottleneck Diagnostic Class
 *
 * Detects plugins with performance issues on activation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Plugin_Activation_Hook_Bottleneck extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-activation-hook-bottleneck';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Activation Hook Bottleneck';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for slow plugin activation hooks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		// Check for activation hooks
		if ( isset( $wp_filter['activate_plugin'] ) ) {
			$count = count( $wp_filter['activate_plugin']->callbacks );

			if ( $count > 10 ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__( 'Site has %d plugin activation hooks. This can slow down plugin activation and updates.', 'wpshadow' ),
						absint( $count )
					),
					'severity'      => 'low',
					'threat_level'  => 25,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/plugin-activation-hook-bottleneck',
				);
			}
		}

		return null;
	}
}
