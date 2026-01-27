<?php
/**
 * Diagnostic: Style Enqueue Order
 *
 * Checks if CSS dependencies are properly ordered when enqueued.
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
 * Class Diagnostic_Wp_Enqueue_Style_Order
 *
 * Tests style enqueue ordering and dependency resolution.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Enqueue_Style_Order extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-enqueue-style-order';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Style Enqueue Order';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS dependencies are properly ordered';

	/**
	 * Check style enqueue order.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		if ( ! isset( $wp_styles ) || ! is_object( $wp_styles ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Style queue is not initialized. Cannot validate style dependencies.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_enqueue_style_order',
				'meta'        => array(
					'queue_initialized' => false,
				),
			);
		}

		// Check for unresolved dependencies.
		$issues = array();

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! empty( $style->deps ) ) {
				foreach ( $style->deps as $dep ) {
					if ( ! isset( $wp_styles->registered[ $dep ] ) ) {
						$issues[] = sprintf( '%s depends on missing %s', $handle, $dep );
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Stylesheets have unresolved dependencies. Some CSS may not load correctly. Check plugin/theme enqueue functions.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_enqueue_style_order',
				'meta'        => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
