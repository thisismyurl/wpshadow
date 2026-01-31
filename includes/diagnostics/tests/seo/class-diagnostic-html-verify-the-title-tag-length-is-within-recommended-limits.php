<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_The_Title_Tag_Length_Is_Within_Recommended_Limits extends Diagnostic_Base {
	protected static $slug = 'html-verify-the-title-tag-length-is-within-recommended-limits';
	protected static $title = 'Title Tag Length Issue';
	protected static $description = 'Verifies title tag length is optimal';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$title = $post->post_title;
		$length = strlen( $title );
		if ( $length < 30 || $length > 60 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Title tag is %d characters. Optimal: 30-60 characters. Too short: lacks keywords; too long: truncated in search results.', 'wpshadow' ), $length ),
				'severity' => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-verify-the-title-tag-length-is-within-recommended-limits',
				'meta' => array( 'length' => $length, 'optimal_min' => 30, 'optimal_max' => 60 ),
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
