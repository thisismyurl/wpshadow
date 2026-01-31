<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Check_For_Overly_Long_Headings extends Diagnostic_Base {
	protected static $slug = 'html-check-for-overly-long-headings';
	protected static $title = 'Overly Long Headings';
	protected static $description = 'Detects headings exceeding recommended length';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$long = 0;
		if ( preg_match_all( '/<h[1-6][^>]*>([^<]+)<\/h[1-6]>/', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $heading ) {
				if ( strlen( $heading ) > 70 ) {
					$long++;
				}
			}
		}
		if ( $long < 2 ) 
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
{ return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d heading(s) over 70 characters. Long headings are harder to scan and may be truncated in search results. Keep headings concise.', 'wpshadow' ), $long ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-check-for-overly-long-headings',
			'meta' => array( 'count' => $long ),
		);
	}
}
