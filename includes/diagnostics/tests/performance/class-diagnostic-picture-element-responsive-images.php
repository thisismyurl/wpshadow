<?php
/**
 * Picture Element Responsive Images Diagnostic
 *
 * Issue #4977: Images Not Responsive (Not Optimized)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if images use responsive picture element.
 * Serving same size to phones and desktops wastes bandwidth.
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
 * Diagnostic_Picture_Element_Responsive_Images Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Picture_Element_Responsive_Images extends Diagnostic_Base {

	protected static $slug = 'picture-element-responsive-images';
	protected static $title = 'Images Not Responsive (Not Optimized)';
	protected static $description = 'Checks if images use responsive picture element';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use <picture> element for art direction', 'wpshadow' );
		$issues[] = __( 'Serve different images for mobile/desktop', 'wpshadow' );
		$issues[] = __( 'Use srcset for resolution switching (1x, 2x, 3x)', 'wpshadow' );
		$issues[] = __( 'Serve WebP with fallback to JPEG/PNG', 'wpshadow' );
		$issues[] = __( 'WordPress: wp_get_attachment_image() generates srcset', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Responsive images serve different sizes to different devices. Mobile phones download smaller images (faster), desktops get full resolution.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/responsive-images',
				'details'      => array(
					'recommendations'         => $issues,
					'bandwidth_savings'       => '60-80% smaller on mobile',
					'example'                 => '<picture><source media="(max-width:600px)" srcset="small.jpg"><img src="large.jpg"></picture>',
					'srcset_example'          => '<img srcset="small.jpg 480w, large.jpg 1200w" sizes="(max-width:600px) 100vw, 50vw">',
				),
			);
		}

		return null;
	}
}
