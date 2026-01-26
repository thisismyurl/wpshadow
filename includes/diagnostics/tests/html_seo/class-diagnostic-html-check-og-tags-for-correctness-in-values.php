<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Check_Og_Tags_For_Correctness_In_Values extends Diagnostic_Base {
	protected static $slug = 'html-check-og-tags-for-correctness-in-values';
	protected static $title = 'OG Tags with Incorrect Values';
	protected static $description = 'Validates OG tag values for correctness';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$issues = 0;
		if ( preg_match( '/<meta[^>]*property="og:title"[^>]*content="([^"]*)"/', $post->post_content, $m ) ) {
			if ( empty( $m[1] ) || strlen( $m[1] ) > 65 ) { $issues++; }
		}
		if ( preg_match( '/<meta[^>]*property="og:description"[^>]*content="([^"]*)"/', $post->post_content, $m ) ) {
			if ( empty( $m[1] ) || strlen( $m[1] ) > 160 ) { $issues++; }
		}
		if ( preg_match( '/<meta[^>]*property="og:image"[^>]*content="([^"]*)"/', $post->post_content, $m ) ) {
			if ( empty( $m[1] ) ) { $issues++; }
		}
		if ( $issues > 0 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Found %d OG tag(s) with incorrect values. Ensure og:title (max 65 chars), og:description (max 160 chars), og:image (must exist).', 'wpshadow' ), $issues ),
				'severity' => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-check-og-tags-for-correctness-in-values',
				'meta' => array( 'issues' => $issues ),
			);
		}
		return null;
	}
}
