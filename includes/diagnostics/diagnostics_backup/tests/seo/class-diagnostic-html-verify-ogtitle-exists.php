<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_Ogtitle_Exists extends Diagnostic_Base {
	protected static $slug = 'html-verify-ogtitle-exists';
	protected static $title = 'Missing OG:Title';
	protected static $description = 'Verifies og:title meta tag exists';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$has_og_title = preg_match( '/<meta[^>]*property="og:title"/', $post->post_content );
		if ( ! $has_og_title ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No og:title meta tag found. This is the title shown in social previews. Add an og:title (50-65 characters recommended).', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/html-verify-ogtitle-exists',
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
