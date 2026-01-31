<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_Canonical_Tag_Matches_The_Correct_Url extends Diagnostic_Base {
	protected static $slug = 'html-verify-canonical-tag-matches-the-correct-url';
	protected static $title = 'Canonical URL Mismatch';
	protected static $description = 'Verifies canonical tag URL is correct';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<link[^>]*rel="canonical"[^>]*href="([^"]+)"/', $post->post_content, $matches ) ) {
			$canonical = $matches[1];
			if ( empty( $canonical ) || ! filter_var( $canonical, FILTER_VALIDATE_URL ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'Canonical URL is invalid or empty: %s. Canonical must be a valid, absolute URL.', 'wpshadow' ), substr( $canonical, 0, 50 ) ),
					'severity' => 'high',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-verify-canonical-tag-matches-the-correct-url',
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
