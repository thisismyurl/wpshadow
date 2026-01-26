<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Check_If_The_Title_Contains_The_Pages_Primary_Keyword extends Diagnostic_Base {
	protected static $slug = 'html-check-if-the-title-contains-the-pages-primary-keyword';
	protected static $title = 'Title Missing Primary Keyword';
	protected static $description = 'Checks if title contains primary keyword';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$title = strtolower( $post->post_title );
		$content = strtolower( wp_strip_all_tags( $post->post_content ) );
		if ( strlen( $content ) > 500 ) {
			$words = explode( ' ', $content );
			$word_frequency = array_count_values( $words );
			arsort( $word_frequency );
			$top_words = array_slice( array_keys( $word_frequency ), 0, 5 );
			$found = false;
			foreach ( $top_words as $word ) {
				if ( strlen( $word ) > 4 && strpos( $title, $word ) !== false ) {
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Title doesn\'t contain primary keywords from your content. Include key terms in title for better ranking signals.', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-check-if-the-title-contains-the-pages-primary-keyword',
					'meta' => array( 'title' => $post->post_title, 'top_words' => array_slice( $top_words, 0, 3 ) ),
				);
			}
		}
		return null;
	}
}
