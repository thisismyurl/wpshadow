<?php
/**
 * Media Touch Gesture Support Diagnostic
 *
 * Tests touch gesture support for the media picker and
 * validates required scripts for mobile interactions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Touch_Gesture_Support Class
 *
 * Checks for touch-related scripts and media view settings.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Touch_Gesture_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-touch-gesture-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Touch Gesture Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests touch gesture handling in the media picker';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'wp_enqueue_media' ) ) {
			$issues[] = __( 'Media uploader is unavailable; touch gestures cannot be supported', 'wpshadow' );
		}

		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media views script is not registered; touch interactions may fail', 'wpshadow' );
		}

		if ( ! wp_script_is( 'jquery-touch-punch', 'registered' ) && ! wp_script_is( 'jquery-ui-touch-punch', 'registered' ) ) {
			$issues[] = __( 'No touch gesture helper scripts detected; swipe and pinch interactions may be limited', 'wpshadow' );
		}

		if ( ! has_filter( 'media_view_settings' ) ) {
			$issues[] = __( 'No media view settings filter detected; touch-specific UI tweaks may be missing', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-touch-gesture-support?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
