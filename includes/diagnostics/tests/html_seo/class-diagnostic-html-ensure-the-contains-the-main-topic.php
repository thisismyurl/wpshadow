<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Ensure_The_Contains_The_Main_Topic extends Diagnostic_Base {
	protected static $slug = 'html-ensure-the-contains-the-main-topic';
	protected static $title = 'H1 Doesn\'t Match Page Topic';
	protected static $description = 'Ensures H1 contains the main topic';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<h1[^>]*>([^<]+)<\/h1>/', $post->post_content, $matches ) ) {
			$h1_text = strtolower( trim( $matches[1] ) );
			$title_words = array_slice( explode( ' ', strtolower( $post->post_title ) ), 0, 3 );
			$matching_words = 0;
			foreach ( $title_words as $word ) {
				if ( strlen( $word ) > 3 && strpos( $h1_text, $word ) !== false ) {
					$matching_words++;
				}
			}
			if ( $matching_words === 0 && strlen( $post->post_title ) > 5 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'H1 ("%s") doesn\'t match page title ("%s"). H1 should reflect the main topic of the page.', 'wpshadow' ), substr( $h1_text, 0, 50 ), $post->post_title ),
					'severity' => 'medium',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-ensure-the-contains-the-main-topic',
					'meta' => array( 'h1' => $h1_text, 'title' => $post->post_title ),
				);
			}
		}
		return null;
	}
}
