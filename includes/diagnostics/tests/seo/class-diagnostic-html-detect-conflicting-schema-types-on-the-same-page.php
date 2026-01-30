<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Conflicting_Schema_Types_On_The_Same_Page extends Diagnostic_Base {
	protected static $slug = 'html-detect-conflicting-schema-types-on-the-same-page';
	protected static $title = 'Conflicting Schema Types';
	protected static $description = 'Detects conflicting schema.org types on page';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$types = array();
		if ( preg_match_all( '/"@type"\s*:\s*"([^"]+)"/', $post->post_content, $matches ) ) {
			$types = array_unique( $matches[1] );
		}
		if ( count( $types ) < 2 ) { return null; }
		$conflicting = array( 'Article', 'NewsArticle', 'BlogPosting', 'CreativeWork' );
		$found = array_intersect( $types, $conflicting );
		if ( count( $found ) < 2 ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d conflicting schema types: %s. Use only one primary schema type per page. Multiple conflicting types confuse search engines.', 'wpshadow' ), count( $found ), implode( ', ', array_slice( $found, 0, 3 ) ) ),
			'severity' => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-conflicting-schema-types-on-the-same-page',
			'meta' => array( 'types' => $types ),
		);
	}
}
