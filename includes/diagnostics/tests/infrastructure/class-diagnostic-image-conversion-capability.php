<?php
/**
 * Diagnostic: Image Conversion Capability
 *
 * Checks if WordPress can convert images between formats (JPEG, PNG, WebP, etc.).
 * Image conversion is required for thumbnails, resizing, and optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Image_Conversion_Capability
 *
 * Tests image format conversion capabilities.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Image_Conversion_Capability extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-conversion-capability';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Conversion Capability';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress can convert images between formats';

	/**
	 * Check image conversion capability.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if image editor is available.
		if ( ! function_exists( 'wp_get_image_editor' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WordPress image editor functions are not available.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_conversion_capability',
				'meta'        => array(
					'editor_available' => false,
				),
			);
		}

		// Check for GD and Imagick support.
		$has_gd      = extension_loaded( 'gd' );
		$has_imagick = extension_loaded( 'imagick' );

		if ( ! $has_gd && ! $has_imagick ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Neither GD nor Imagick extension is available. WordPress cannot convert or resize images.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_conversion_capability',
				'meta'        => array(
					'has_gd'      => false,
					'has_imagick' => false,
				),
			);
		}

		// Check supported image formats.
		$supported_formats = array();

		if ( $has_gd ) {
			$gd_info = gd_info();
			$supported_formats['gd'] = array(
				'jpeg' => ! empty( $gd_info['JPEG Support'] ),
				'png'  => ! empty( $gd_info['PNG Support'] ),
				'gif'  => ! empty( $gd_info['GIF Create Support'] ),
				'webp' => ! empty( $gd_info['WebP Support'] ),
			);
		}

		if ( $has_imagick ) {
			$imagick = new \Imagick();
			$formats = $imagick->queryFormats();
			$supported_formats['imagick'] = array(
				'jpeg' => in_array( 'JPEG', $formats, true ),
				'png'  => in_array( 'PNG', $formats, true ),
				'gif'  => in_array( 'GIF', $formats, true ),
				'webp' => in_array( 'WEBP', $formats, true ),
			);
		}

		// Check if common formats are supported.
		$missing_formats = array();

		foreach ( array( 'jpeg', 'png' ) as $format ) {
			$supported = false;
			if ( isset( $supported_formats['gd'][ $format ] ) && $supported_formats['gd'][ $format ] ) {
				$supported = true;
			}
			if ( isset( $supported_formats['imagick'][ $format ] ) && $supported_formats['imagick'][ $format ] ) {
				$supported = true;
			}
			if ( ! $supported ) {
				$missing_formats[] = strtoupper( $format );
			}
		}

		if ( ! empty( $missing_formats ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Comma-separated list of missing formats */
					__( 'WordPress cannot convert %s images. Consider installing GD or Imagick with full format support.', 'wpshadow' ),
					implode( ', ', $missing_formats )
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_conversion_capability',
				'meta'        => array(
					'has_gd'            => $has_gd,
					'has_imagick'       => $has_imagick,
					'missing_formats'   => $missing_formats,
					'supported_formats' => $supported_formats,
				),
			);
		}

		// Image conversion is fully functional.
		return null;
	}
}
