<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Confirm_Canonical_Tag_Exists extends Diagnostic_Base {
	protected static $slug = 'html-confirm-canonical-tag-exists';
	protected static $title = 'Missing Canonical Tag';
	protected static $description = 'Confirms canonical tag exists on page';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$has_canonical = preg_match( '/<link[^>]*rel="canonical"/', $post->post_content );
		if ( ! $has_canonical ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'No canonical tag found. Add: <link rel="canonical" href="%s">', 'wpshadow' ), get_permalink( $post ) ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/html-confirm-canonical-tag-exists',
				'meta' => array( 'url' => get_permalink( $post ) ),
			);
		}
		return null;
	}
}
