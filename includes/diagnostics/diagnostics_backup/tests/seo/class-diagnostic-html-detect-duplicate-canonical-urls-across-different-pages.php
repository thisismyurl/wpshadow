<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Duplicate_Canonical_Urls_Across_Different_Pages extends Diagnostic_Base {
	protected static $slug = 'html-detect-duplicate-canonical-urls-across-different-pages';
	protected static $title = 'Duplicate Canonical URLs';
	protected static $description = 'Detects duplicate canonical across pages';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<link[^>]*rel="canonical"[^>]*href="([^"]+)"/', $post->post_content, $matches ) ) {
			$canonical = $matches[1];
			if ( $canonical !== get_permalink( $post ) && ! empty( $canonical ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'Canonical URL (%s) doesn\'t match this page\'s URL. Duplicate canonicals across pages confuse search engines. Use self-referencing canonicals.', 'wpshadow' ), substr( $canonical, 0, 60 ) ),
					'severity' => 'medium',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-detect-duplicate-canonical-urls-across-different-pages',
					'meta' => array( 'canonical' => $canonical ),
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
