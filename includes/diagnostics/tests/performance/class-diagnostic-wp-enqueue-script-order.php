<?php
/**
 * Diagnostic: Script Enqueue Order
 *
 * Checks if JavaScript dependencies are properly ordered when enqueued.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Enqueue_Script_Order
 *
 * Tests script enqueue ordering and dependency resolution.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Enqueue_Script_Order extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-enqueue-script-order';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Script Enqueue Order';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JavaScript dependencies are properly ordered';

	/**
	 * Check script enqueue order.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! is_object( $wp_scripts ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Script queue is not initialized. Cannot validate script dependencies.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_enqueue_script_order',
				'meta'        => array(
					'queue_initialized' => false,
				),
			);
		}

		// Check for circular dependencies or missing dependency.
		$issues = array();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! empty( $script->deps ) ) {
				foreach ( $script->deps as $dep ) {
					if ( ! isset( $wp_scripts->registered[ $dep ] ) ) {
						$issues[] = sprintf( '%s depends on missing %s', $handle, $dep );
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Scripts have unresolved dependencies. Some JavaScript may not load correctly. Check plugin/theme enqueue functions.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_enqueue_script_order',
				'meta'        => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
