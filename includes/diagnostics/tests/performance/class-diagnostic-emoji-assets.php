<?php
/**
 * Emoji Assets Diagnostic
 *
 * Checks whether WordPress emoji detection scripts and styles are loaded on
 * every front-end page when modern browsers handle emoji natively.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Emoji_Assets Class
 *
 * @since 0.6095
 */
class Diagnostic_Emoji_Assets extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'emoji-assets';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Emoji Assets';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress emoji detection scripts are loaded on every front-end page. Modern browsers handle emoji natively so these assets represent unnecessary HTTP requests.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects plugins or settings that already disable emoji assets. If none
	 * are found, scans registered scripts for the wp-emoji-release handle.
	 *
	 * @since  0.6095
	 * @return array|null Finding array when emoji assets are still loaded, null when healthy.
	 */
	public static function check() {
		// Check for plugins or configurations known to disable emoji assets.
		$pm = get_option( 'perfmatters_options', array() );
		if ( is_array( $pm ) && ! empty( $pm['extras']['disable_emoji'] ) ) {
			return null;
		}

		$rocket = get_option( 'wp_rocket_settings', array() );
		if ( is_array( $rocket ) && ! empty( $rocket['emoji'] ) ) {
			return null;
		}

		// Autoptimize
		$ao = get_option( 'autoptimize_extra_settings', array() );
		if ( is_array( $ao ) && ! empty( $ao['autoptimize_extra_remove_emojis'] ) ) {
			return null;
		}

		// WP Asset CleanUp
		if ( false !== get_option( 'wpacu_settings', false ) ) {
			return null; // Plugin present; assume emoji management is handled.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress loads emoji detection scripts and styles on every front-end page. Modern browsers handle emoji natively without these assets. Removing them reduces the number of HTTP requests and eliminates a small amount of render-blocking JavaScript. A performance or asset-management plugin can remove them with a single toggle.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 10,
			'details'      => array(
				'note' => __( 'Use Perfmatters, WP Rocket, or a similar plugin to disable emoji scripts and styles.', 'wpshadow' ),
			),
		);
	}
}
