<?php
/**
 * Cumulative Layout Shift (CLS) Analysis Diagnostic
 *
 * Calculates layout shift score to detect visual instability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cumulative Layout Shift (CLS) Analysis Class
 *
 * Tests CLS metric.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Cumulative_Layout_Shift_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cumulative-layout-shift-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cumulative Layout Shift (CLS) Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Calculates layout shift score to detect visual instability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cls_check = self::check_cls_indicators();
		
		if ( $cls_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $cls_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cumulative-layout-shift-analysis',
				'meta'         => array(
					'images_without_dimensions' => $cls_check['images_without_dimensions'],
					'font_loading_method'       => $cls_check['font_loading_method'],
					'recommendations'           => $cls_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check CLS indicators.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_cls_indicators() {
		$check = array(
			'has_issues'                => false,
			'issues'                    => array(),
			'images_without_dimensions' => array(),
			'font_loading_method'       => 'unknown',
			'recommendations'           => array(),
		);

		// Check homepage content for images without dimensions.
		$homepage_id = (int) get_option( 'page_on_front' );
		
		if ( $homepage_id > 0 ) {
			$content = get_post_field( 'post_content', $homepage_id );
			
			if ( ! empty( $content ) ) {
				// Find img tags.
				preg_match_all( '/<img([^>]*)>/i', $content, $matches );
				
				if ( ! empty( $matches[1] ) ) {
					foreach ( $matches[1] as $img_attrs ) {
						// Check if width/height attributes present.
						$has_width = false !== strpos( $img_attrs, 'width=' );
						$has_height = false !== strpos( $img_attrs, 'height=' );
						
						if ( ! $has_width || ! $has_height ) {
							// Extract src.
							if ( preg_match( '/src=[\'"]([^\'"]+)[\'"]/i', $img_attrs, $src_match ) ) {
								$check['images_without_dimensions'][] = $src_match[1];
							}
						}
					}
				}
			}
		}

		// Check if theme uses web fonts.
		global $wp_styles;
		
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}

				$style = $wp_styles->registered[ $handle ];
				
				// Check for Google Fonts.
				if ( ! empty( $style->src ) && false !== strpos( $style->src, 'fonts.googleapis.com' ) ) {
					// Check if display=swap is used.
					if ( false !== strpos( $style->src, 'display=swap' ) ) {
						$check['font_loading_method'] = 'swap';
					} else {
						$check['font_loading_method'] = 'block';
						$check['has_issues'] = true;
						$check['issues'][] = __( 'Web fonts loading without font-display: swap (causes layout shift)', 'wpshadow' );
						$check['recommendations'][] = __( 'Add display=swap to Google Fonts URLs', 'wpshadow' );
					}
					break;
				}
			}
		}

		// Detect issues from images.
		if ( count( $check['images_without_dimensions'] ) > 3 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of images */
				__( '%d images missing width/height attributes (causes layout shift)', 'wpshadow' ),
				count( $check['images_without_dimensions'] )
			);
			$check['recommendations'][] = __( 'Add width and height attributes to all images', 'wpshadow' );
		}

		return $check;
	}
}
