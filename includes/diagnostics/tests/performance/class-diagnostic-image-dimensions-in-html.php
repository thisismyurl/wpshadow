<?php
/**
 * Image Dimensions in HTML Diagnostic
 *
 * Issue #4976: Images Missing Width/Height Attributes
 * Pillar: ⚙️ Murphy's Law / 🎓 Learning Inclusive
 *
 * Checks if images have width/height attributes.
 * Missing dimensions cause layout shift during load.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Dimensions_In_HTML Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Image_Dimensions_In_HTML extends Diagnostic_Base {

	protected static $slug = 'image-dimensions-in-html';
	protected static $title = 'Images Missing Width/Height Attributes';
	protected static $description = 'Checks if images have width and height attributes';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add width and height attributes to all <img> tags', 'wpshadow' );
		$issues[] = __( 'Prevents Cumulative Layout Shift (CLS)', 'wpshadow' );
		$issues[] = __( 'Browser reserves space before image loads', 'wpshadow' );
		$issues[] = __( 'Use aspect-ratio CSS for responsive images', 'wpshadow' );
		$issues[] = __( 'WordPress: wp_get_attachment_image() includes attributes', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Images without dimensions cause the page to shift as they load. This is jarring for users and tanks Core Web Vitals scores.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/image-dimensions',
				'details'      => array(
					'recommendations'         => $issues,
					'core_web_vital'          => 'Cumulative Layout Shift (CLS)',
					'example'                 => '<img src="image.jpg" width="400" height="300" alt="...">',
					'responsive_css'          => 'img { aspect-ratio: 4/3; width: 100%; height: auto; }',
				),
			);
		}

		return null;
	}
}
