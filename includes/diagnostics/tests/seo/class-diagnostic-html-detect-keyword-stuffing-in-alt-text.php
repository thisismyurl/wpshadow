<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Keyword_Stuffing_In_Alt_Text extends Diagnostic_Base {
	protected static $slug = 'html-detect-keyword-stuffing-in-alt-text';
	protected static $title = 'Keyword Stuffing in Alt Text';
	protected static $description = 'Detects excessive keywords in image alt text';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$stuffed = array();
		if ( preg_match_all( '/<img[^>]*alt=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $alt ) {
				$word_count = str_word_count( $alt );
				if ( $word_count > 15 ) {
					$stuffed[] = substr( $alt, 0, 50 );
				}
			}
		}
		if ( count( $stuffed ) < 2 ) 
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
			'description' => sprintf( __( 'Found %d image(s) with excessive alt text (keyword stuffing). Alt text should be 1-2 sentences describing the image, not a keyword list. This hurts both UX and SEO.', 'wpshadow' ), count( $stuffed ) ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-keyword-stuffing-in-alt-text',
			'meta' => array( 'count' => count( $stuffed ) ),
		);
	}
}
