<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Confirm_The_Page_Isnt_Blocking_Indexing_With_Robots_Meta extends Diagnostic_Base {
	protected static $slug = 'html-confirm-the-page-isnt-blocking-indexing-with-robots-meta';
	protected static $title = 'Page Indexing Not Blocked';
	protected static $description = 'Confirms page indexing isn\'t blocked by robots meta';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<meta[^>]*name="robots"[^>]*content="[^"]*noindex/', $post->post_content ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Found robots meta="noindex" on this page. Indexing is blocked. Search engines cannot index this page. Remove noindex unless this is intentional (e.g., duplicate pages).', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-confirm-the-page-isnt-blocking-indexing-with-robots-meta',
				'meta' => array(),
			);
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
