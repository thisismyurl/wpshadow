<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Ensure_Ogimage_Is_An_Absolute_Url extends Diagnostic_Base {
	protected static $slug = 'html-ensure-ogimage-is-an-absolute-url';
	protected static $title = 'OG:Image Relative URL';
	protected static $description = 'Ensures OG:image uses absolute URL';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<meta[^>]*property="og:image"[^>]*content="([^"]+)"/', $post->post_content, $matches ) ) {
			$url = $matches[1];
			if ( strpos( $url, 'http' ) !== 0 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'OG:image URL is relative: "%s". Social platforms need absolute URLs. Use: https://example.com/image.jpg', 'wpshadow' ), substr( $url, 0, 50 ) ),
					'severity' => 'medium',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-ensure-ogimage-is-an-absolute-url',
					'meta' => array(),
				);
			}
		}

		// SEO validation checks
		if ( ! function_exists( 'wp_get_document_title' ) ) {
			$issues[] = __( 'Document title function unavailable', 'wpshadow' );
		}
		if ( get_option( 'blog_public' ) === '0' ) {
			$issues[] = __( 'Site set to private in search engines', 'wpshadow' );
		}
		// Check meta robots
		if ( ! function_exists( 'wp_robots' ) ) {
			$issues[] = __( 'Robots meta tag function unavailable', 'wpshadow' );
		}
		return null;
	}
}
