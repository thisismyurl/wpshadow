<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Duplicate_Meta_Descriptions_Across_Pages extends Diagnostic_Base {
	protected static $slug = 'html-detect-duplicate-meta-descriptions-across-pages';
	protected static $title = 'Duplicate Meta Descriptions';
	protected static $description = 'Detects duplicate meta descriptions';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<meta[^>]*name="description"[^>]*content="([^"]+)"/', $post->post_content, $matches ) ) {
			$desc = $matches[1];
			if ( strlen( $desc ) < 10 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Meta description is too short or may be generic. Search results show 155-160 characters. Write unique, descriptive text for each page.', 'wpshadow' ),
					'severity' => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-detect-duplicate-meta-descriptions-across-pages',
					'meta' => array( 'description' => substr( $desc, 0, 50 ) ),
				);
			}
		}
		return null;
	}
}
