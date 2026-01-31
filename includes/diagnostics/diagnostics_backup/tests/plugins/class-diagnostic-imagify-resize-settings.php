<?php
/**
 * Imagify Resize Settings Diagnostic
 *
 * Imagify Resize Settings detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.743.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imagify Resize Settings Diagnostic Class
 *
 * @since 1.743.0000
 */
class Diagnostic_ImagifyResizeSettings extends Diagnostic_Base {

	protected static $slug = 'imagify-resize-settings';
	protected static $title = 'Imagify Resize Settings';
	protected static $description = 'Imagify Resize Settings detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'IMAGIFY_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Resize enabled
		$resize_enabled = get_option( 'imagify_resize_enabled', 'no' );
		if ( 'no' === $resize_enabled ) {
			return null; // Feature not in use
		}
		
		// Check 2: Max width/height
		$max_width = get_option( 'imagify_resize_max_width', 0 );
		$max_height = get_option( 'imagify_resize_max_height', 0 );
		
		if ( $max_width === 0 && $max_height === 0 ) {
			$issues[] = __( 'No resize limits (images not resized)', 'wpshadow' );
		}
		
		// Check 3: Preserve EXIF
		$preserve_exif = get_option( 'imagify_preserve_exif', 'no' );
		if ( 'yes' === $preserve_exif ) {
			$issues[] = __( 'EXIF preserved (larger files, privacy risk)', 'wpshadow' );
		}
		
		// Check 4: Resize original
		$resize_original = get_option( 'imagify_resize_original', 'no' );
		if ( 'no' === $resize_original ) {
			$issues[] = __( 'Originals not resized (disk space waste)', 'wpshadow' );
		}
		
		// Check 5: Auto-optimize new images
		$auto_optimize = get_option( 'imagify_auto_optimize', 'no' );
		if ( 'no' === $auto_optimize ) {
			$issues[] = __( 'Manual optimization (unoptimized images)', 'wpshadow' );
		}
		
		// Check 6: Bulk processing status
		global $wpdb;
		$unoptimized = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_key = '_imagify_status' AND meta_value = 'unoptimized'"
		);
		
		if ( $unoptimized > 100 ) {
			$issues[] = sprintf( __( '%d unoptimized images remain', 'wpshadow' ), $unoptimized );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Imagify resize configuration issues */
				__( 'Imagify resize has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/imagify-resize-settings',
		);
	}
}
