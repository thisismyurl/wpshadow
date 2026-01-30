<?php
/**
 * Ewww Image Optimizer Bulk Processing Diagnostic
 *
 * Ewww Image Optimizer Bulk Processing detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.751.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ewww Image Optimizer Bulk Processing Diagnostic Class
 *
 * @since 1.751.0000
 */
class Diagnostic_EwwwImageOptimizerBulkProcessing extends Diagnostic_Base {

	protected static $slug = 'ewww-image-optimizer-bulk-processing';
	protected static $title = 'Ewww Image Optimizer Bulk Processing';
	protected static $description = 'Ewww Image Optimizer Bulk Processing detected';
	protected static $family = 'functionality';

	public static function check() {
		$has_ewww = defined( 'EWWW_IMAGE_OPTIMIZER_VERSION' ) ||
		            class_exists( 'EWWW_Image_Optimizer' ) ||
		            function_exists( 'ewww_image_optimizer' );

		if ( ! $has_ewww ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Unoptimized images
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
		);

		$optimized = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ewww_image_optimizer'"
		);

		if ( $total_images > 0 && $optimized < ( $total_images * 0.5 ) ) {
			$unoptimized = $total_images - $optimized;
			$issues[] = sprintf( __( '%d unoptimized images (slow site)', 'wpshadow' ), $unoptimized );
		}

		// Check 2: Bulk processing status
		$bulk_resume = get_option( 'ewww_image_optimizer_bulk_resume', '' );
		if ( ! empty( $bulk_resume ) ) {
			$issues[] = __( 'Bulk optimization paused (incomplete)', 'wpshadow' );
		}

		// Check 3: Backup originals
		$backup = get_option( 'ewww_image_optimizer_backup_files', 0 );
		if ( ! $backup ) {
			$issues[] = __( 'Originals not backed up (irreversible)', 'wpshadow' );
		}

		// Check 4: Resize settings
		$max_width = get_option( 'ewww_image_optimizer_maxmediawidth', 0 );
		if ( $max_width === 0 ) {
			$issues[] = __( 'No max width set (oversized images)', 'wpshadow' );
		}

		// Check 5: WebP generation
		$webp = get_option( 'ewww_image_optimizer_webp', 0 );
		if ( ! $webp ) {
			$issues[] = __( 'WebP not enabled (missing modern format)', 'wpshadow' );
		}

		// Check 6: Lazy load
		$lazy_load = get_option( 'ewww_image_optimizer_lazy_load', 0 );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Lazy load disabled (slower page loads)', 'wpshadow' );
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
				__( 'EWWW Image Optimizer has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/ewww-image-optimizer-bulk-processing',
		);
	}
}
