<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Nofollow_Meta_Tag_Applied_Accidentally extends Diagnostic_Base {
	protected static $slug = 'html-detect-nofollow-meta-tag-applied-accidentally';
	protected static $title = 'Accidental Nofollow Meta';
	protected static $description = 'Detects accidentally applied nofollow meta tag';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<meta[^>]*name="robots"[^>]*content="[^"]*nofollow/', $post->post_content ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Found robots meta="nofollow" on this page. Link authority is not passed. Unless this is a non-important page, remove nofollow.', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-detect-nofollow-meta-tag-applied-accidentally',
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
