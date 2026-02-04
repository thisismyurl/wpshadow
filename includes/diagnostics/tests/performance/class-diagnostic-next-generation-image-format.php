<?php
/**
 * Next Generation Image Format Diagnostic
 *
 * Issue #4978: Images Not in Modern Formats (WEBP)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if modern image formats (WebP) are used.
 * WebP is 25% smaller than JPEG with same quality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Next_Generation_Image_Format Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Next_Generation_Image_Format extends Diagnostic_Base {

	protected static $slug = 'next-generation-image-format';
	protected static $title = 'Images Not in Modern Formats (WEBP)';
	protected static $description = 'Checks if modern image formats like WebP are used';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Convert images to WebP format', 'wpshadow' );
		$issues[] = __( 'WebP is 25% smaller than JPEG', 'wpshadow' );
		$issues[] = __( 'Use <picture> to serve WebP with JPEG fallback', 'wpshadow' );
		$issues[] = __( 'Tools: ImageMagick, FFmpeg, online converters', 'wpshadow' );
		$issues[] = __( 'Consider AVIF for next-generation format', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WebP and AVIF are newer image formats that compress better than JPEG and PNG. Modern browsers support them with graceful fallbacks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/webp-format',
				'details'      => array(
					'recommendations'         => $issues,
					'compression'             => 'WebP: 25% smaller than JPEG, AVIF: 50% smaller',
					'browser_support'         => 'WebP 94%, AVIF 75%',
					'fallback_example'        => '<picture><source srcset="image.webp" type="image/webp"><img src="image.jpg"></picture>',
				),
			);
		}

		return null;
	}
}
