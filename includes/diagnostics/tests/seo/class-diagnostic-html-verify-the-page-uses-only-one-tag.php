<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_The_Page_Uses_Only_One_Tag extends Diagnostic_Base {
	protected static $slug = 'html-verify-the-page-uses-only-one-tag';
	protected static $title = 'Multiple H1 Tags Found';
	protected static $description = 'Verifies page uses only one H1 tag';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$h1_count = preg_match_all( '/<h1/', $post->post_content );
		if ( $h1_count > 1 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Found %d H1 tags. Use only one H1 per page for proper SEO and accessibility structure. Use H2-H6 for subheadings.', 'wpshadow' ), $h1_count ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-verify-the-page-uses-only-one-tag',
				'meta' => array( 'count' => $h1_count ),
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
