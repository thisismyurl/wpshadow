<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_Robots_Meta_Tag_Is_Present_When_Needed extends Diagnostic_Base {
	protected static $slug = 'html-verify-robots-meta-tag-is-present-when-needed';
	protected static $title = 'Missing Robots Meta Tag';
	protected static $description = 'Verifies robots meta tag when needed';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( in_array( $post->post_status, array( 'draft', 'pending', 'private' ), true ) ) {
			$has_robots = preg_match( '/<meta[^>]*name="robots"/', $post->post_content );
			if ( ! $has_robots ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Non-published content should have robots meta="noindex" to prevent indexing. Add this meta tag to protect unpublished or private pages.', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 30,
					'auto_fixable' => true,
					'kb_link' => 'https://wpshadow.com/kb/html-verify-robots-meta-tag-is-present-when-needed',
					'meta' => array(),
				);
			}
		}
		return null;
	}
}
