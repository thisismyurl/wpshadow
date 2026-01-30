<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Confirm_Images_Include_Width_And_Height_Attributes extends Diagnostic_Base {
	protected static $slug = 'html-confirm-images-include-width-and-height-attributes';
	protected static $title = 'Images Missing Width & Height';
	protected static $description = 'Detects images without width/height attributes';
	protected static $family = 'performance';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$missing = 0;
		if ( preg_match_all( '/<img[^>]*src=["\']([^"\']+)["\'][^>]*/', $post->post_content, $matches ) ) {
			foreach ( $matches[0] as $tag ) {
				if ( ! preg_match( '/width\s*=/', $tag ) || ! preg_match( '/height\s*=/', $tag ) ) {
					$missing++;
				}
			}
		}
		if ( $missing < 3 ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d image(s) missing width and height attributes. These prevent layout shift and allow proper sizing. Add: <img width="800" height="600" src="...">%s', 'wpshadow' ), $missing, '' ),
			'severity' => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-confirm-images-include-width-and-height-attributes',
			'meta' => array( 'count' => $missing ),
		);
	}
}
