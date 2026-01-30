<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Broken_Image_Urls extends Diagnostic_Base {
	protected static $slug = 'html-detect-broken-image-urls';
	protected static $title = 'Broken Image URLs';
	protected static $description = 'Detects broken or placeholder image URLs';
	protected static $family = 'performance';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$broken = array();
		if ( preg_match_all( '/<img[^>]*src=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $src ) {
				if ( empty( $src ) || $src === '#' || strpos( $src, 'placeholder' ) !== false ) {
					$broken[] = substr( $src, 0, 60 );
				}
			}
		}
		if ( count( $broken ) < 2 ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d broken image URL(s). Users see broken image icons; content quality appears poor.', 'wpshadow' ), count( $broken ) ),
			'severity' => 'high',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-broken-image-urls',
			'meta' => array( 'count' => count( $broken ) ),
		);
	}
}
