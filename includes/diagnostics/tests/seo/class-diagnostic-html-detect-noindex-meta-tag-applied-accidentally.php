<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Noindex_Meta_Tag_Applied_Accidentally extends Diagnostic_Base {
	protected static $slug = 'html-detect-noindex-meta-tag-applied-accidentally';
	protected static $title = 'Accidental Noindex Meta';
	protected static $description = 'Detects accidentally applied noindex meta tag';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<meta[^>]*name="robots"[^>]*content="[^"]*noindex/', $post->post_content ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Found robots meta="noindex" on this page. This page is hidden from search engines. Check if this was intentional or accidentally applied. Important pages should NOT be noindex.', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-detect-noindex-meta-tag-applied-accidentally',
				'meta' => array(),
			);
		}
		return null;
	}
}
