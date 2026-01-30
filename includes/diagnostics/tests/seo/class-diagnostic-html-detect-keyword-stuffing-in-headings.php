<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Keyword_Stuffing_In_Headings extends Diagnostic_Base {
	protected static $slug = 'html-detect-keyword-stuffing-in-headings';
	protected static $title = 'Keyword Stuffing in Headings';
	protected static $description = 'Detects excessive keywords in headings';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$stuffed = 0;
		if ( preg_match_all( '/<h[1-6][^>]*>([^<]+)<\/h[1-6]>/', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $heading ) {
				$word_count = str_word_count( $heading );
				if ( $word_count > 10 && preg_match_all( '/\b\w+\b/', $heading, $words ) ) {
					$unique = count( array_unique( array_map( 'strtolower', $words[0] ) ) );
					if ( $unique < $word_count * 0.5 ) {
						$stuffed++;
					}
				}
			}
		}
		if ( $stuffed < 1 ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d heading(s) with keyword stuffing. Headings should be clear and natural, not keyword-focused. Write for users first, SEO second.', 'wpshadow' ), $stuffed ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-keyword-stuffing-in-headings',
			'meta' => array( 'count' => $stuffed ),
		);
	}
}
