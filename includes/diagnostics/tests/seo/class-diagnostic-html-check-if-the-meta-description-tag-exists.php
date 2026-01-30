<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Check_If_The_Meta_Description_Tag_Exists extends Diagnostic_Base {
	protected static $slug = 'html-check-if-the-meta-description-tag-exists';
	protected static $title = 'Missing Meta Description';
	protected static $description = 'Checks if meta description tag exists';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$has_description = preg_match( '/<meta[^>]*name="description"/', $post->post_content );
		if ( ! $has_description ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No meta description found. This text appears in search results. Add 120-160 character description summarizing the page content.', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/html-check-if-the-meta-description-tag-exists',
				'meta' => array(),
			);
		}
		return null;
	}
}
