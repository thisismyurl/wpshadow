<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Oversized_Image_Dimensions extends Diagnostic_Base {
	protected static $slug = 'html-detect-oversized-image-dimensions';
	protected static $title = 'Oversized Image Dimensions';
	protected static $description = 'Detects images with excessively large dimensions';
	protected static $family = 'performance';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$content = $post->post_content;
		$oversized = array();
		if ( preg_match_all( '/<img[^>]*(?:width=["\']?(\d+)["\']?)?[^>]*(?:height=["\']?(\d+)["\']?)?[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\']?/i', $content, $matches ) ) {
			foreach ( $matches[3] as $idx => $src ) {
				$width = isset( $matches[1][ $idx ] ) ? intval( $matches[1][ $idx ] ) : 0;
				$height = isset( $matches[2][ $idx ] ) ? intval( $matches[2][ $idx ] ) : 0;
				if ( $width > 1920 || $height > 1440 ) {
					$oversized[] = array(
						'src' => basename( $src ),
						'dimensions' => $width . 'x' . $height,
					);
				}
			}
		}
		if ( count( $oversized ) < 2 ) { return null; }
		$items = '';
		foreach ( array_slice( $oversized, 0, 3 ) as $img ) {
			$items .= sprintf( "\n- %s (%s)", esc_html( $img['src'] ), esc_html( $img['dimensions'] ) );
		}
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d image(s) with oversized dimensions. Images larger than 1920x1440 are typically larger than needed and slow down pages. Resize images to fit layout and use responsive images with srcset.%s', 'wpshadow' ), count( $oversized ), $items ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-oversized-image-dimensions',
			'meta' => array( 'count' => count( $oversized ) ),
		);
	}
}
