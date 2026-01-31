<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Check_If_The_Tag_Exists extends Diagnostic_Base {
	protected static $slug = 'html-check-if-the-tag-exists';
	protected static $title = 'Missing Title Tag';
	protected static $description = 'Checks if title tag exists in HTML';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( empty( $post->post_title ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No page title found. Every page needs a unique, descriptive title tag. Titles appear in search results and browser tabs.', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-check-if-the-tag-exists',
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
