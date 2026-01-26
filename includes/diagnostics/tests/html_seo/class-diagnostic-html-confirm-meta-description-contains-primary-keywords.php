<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Confirm_Meta_Description_Contains_Primary_Keywords extends Diagnostic_Base {
	protected static $slug = 'html-confirm-meta-description-contains-primary-keywords';
	protected static $title = 'Meta Description Missing Keywords';
	protected static $description = 'Confirms meta description contains keywords';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<meta[^>]*name="description"[^>]*content="([^"]+)"/', $post->post_content, $matches ) ) {
			$desc = strtolower( $matches[1] );
			$title_words = array_slice( explode( ' ', strtolower( $post->post_title ) ), 0, 2 );
			$found_keywords = 0;
			foreach ( $title_words as $word ) {
				if ( strlen( $word ) > 3 && strpos( $desc, $word ) !== false ) {
					$found_keywords++;
				}
			}
			if ( $found_keywords === 0 && strlen( $post->post_title ) > 5 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'Meta description doesn\'t contain primary keywords from title. Include key words for better CTR and relevance signals.', 'wpshadow' ) ),
					'severity' => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-confirm-meta-description-contains-primary-keywords',
					'meta' => array( 'title' => $post->post_title ),
				);
			}
		}
		return null;
	}
}
