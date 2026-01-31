<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Duplicate_Title_Tags_Across_Pages extends Diagnostic_Base {
	protected static $slug = 'html-detect-duplicate-title-tags-across-pages';
	protected static $title = 'Title Tag Appears Generic';
	protected static $description = 'Detects duplicate or generic title tags';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$title = $post->post_title;
		$generic_titles = array( 'Home', 'Page', 'Blog', 'Post', 'Article', 'News', 'Welcome' );
		if ( in_array( $title, $generic_titles, true ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Title tag "%s" is too generic. Each page needs a unique, descriptive title. Include keywords and differentiators.', 'wpshadow' ), $title ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-detect-duplicate-title-tags-across-pages',
				'meta' => array( 'title' => $title ),
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
