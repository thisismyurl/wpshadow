<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Missing_Tags_For_Content_Structure extends Diagnostic_Base {
	protected static $slug = 'html-detect-missing-tags-for-content-structure';
	protected static $title = 'Missing H2 Tags for Structure';
	protected static $description = 'Detects missing h2 tags for structure';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$h1_count = preg_match_all( '/<h1/', $post->post_content );
		$h2_count = preg_match_all( '/<h2/', $post->post_content );
		$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
		if ( $h1_count > 0 && $h2_count === 0 && $word_count > 500 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Long content (%d words) with H1 but no H2 tags. Add H2 subheadings every 300-500 words for better structure and readability.', 'wpshadow' ), $word_count ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-detect-missing-tags-for-content-structure',
				'meta' => array( 'words' => $word_count, 'h1' => $h1_count, 'h2' => $h2_count ),
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
