<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Duplicate_Schema_Blocks extends Diagnostic_Base {
	protected static $slug = 'html-detect-duplicate-schema-blocks';
	protected static $title = 'Duplicate Schema Blocks';
	protected static $description = 'Detects duplicate JSON-LD schema blocks';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match_all( '/<script[^>]*type="application\/ld\+json"/', $post->post_content, $matches ) ) {
			$count = count( $matches[0] );
			if ( $count < 2 ) { return null; }
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Found %d JSON-LD schema blocks on same page. Duplicate schemas confuse search engines. Use one primary schema per page type.', 'wpshadow' ), $count ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-detect-duplicate-schema-blocks',
				'meta' => array( 'count' => $count ),
			);
		}
		return null;
	}
}
