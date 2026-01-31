<?php
/**
 * NextGEN Gallery Image Sizes Diagnostic
 *
 * NextGEN Gallery creating too many sizes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.493.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NextGEN Gallery Image Sizes Diagnostic Class
 *
 * @since 1.493.0000
 */
class Diagnostic_NextgenGalleryImageSizes extends Diagnostic_Base {

	protected static $slug = 'nextgen-gallery-image-sizes';
	protected static $title = 'NextGEN Gallery Image Sizes';
	protected static $description = 'NextGEN Gallery creating too many sizes';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'C_NextGEN_Bootstrap' ) ) {
			return null;
		}
		
		// Check if NextGEN Gallery is active
		if ( ! class_exists( 'C_NextGEN_Bootstrap' ) && ! defined( 'NGG_PLUGIN' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check galleries
		$galleries = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ngg_gallery" );
		
		if ( $galleries === 0 ) {
			return null;
		}

		// Check image sizes configured
		$ngg_options = get_option( 'ngg_options', array() );
		$thumbwidth = isset( $ngg_options['thumbwidth'] ) ? $ngg_options['thumbwidth'] : 0;
		$thumbheight = isset( $ngg_options['thumbheight'] ) ? $ngg_options['thumbheight'] : 0;
		
		// Check custom image sizes
		$image_sizes = $wpdb->get_results(
			"SELECT COUNT(*) as count FROM {$wpdb->prefix}ngg_imagick_sizes"
		);
		if ( isset( $image_sizes[0]->count ) && $image_sizes[0]->count > 10 ) {
			$issues[] = 'excessive_image_size_variants';
			$threat_level += 30;
		}

		// Check backup images
		$backup_enabled = isset( $ngg_options['imgBackup'] ) ? $ngg_options['imgBackup'] : 0;
		if ( $backup_enabled ) {
			$issues[] = 'backup_images_enabled';
			$threat_level += 20;
		}

		// Check automatic resize
		$auto_resize = isset( $ngg_options['imgAutoResize'] ) ? $ngg_options['imgAutoResize'] : 0;
		if ( ! $auto_resize ) {
			$issues[] = 'automatic_resize_disabled';
			$threat_level += 15;
		}

		// Check thumbnail quality
		$thumb_quality = isset( $ngg_options['thumbquality'] ) ? $ngg_options['thumbquality'] : 100;
		if ( $thumb_quality > 85 ) {
			$issues[] = 'thumbnail_quality_too_high';
			$threat_level += 15;
		}

		// Check watermark on all sizes
		$watermark_all = isset( $ngg_options['wmType'] ) ? $ngg_options['wmType'] : 'text';
		if ( $watermark_all !== 'none' ) {
			$issues[] = 'watermark_increases_processing';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of image size issues */
				__( 'NextGEN Gallery creates too many image sizes: %s. This wastes disk space and slows image processing.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/nextgen-gallery-image-sizes',
			);
		}
		
		return null;
	}
}
