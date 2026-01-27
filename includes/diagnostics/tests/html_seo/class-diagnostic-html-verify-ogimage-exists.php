<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_Ogimage_Exists extends Diagnostic_Base {
	protected static $slug = 'html-verify-ogimage-exists';
	protected static $title = 'Missing OG:Image';
	protected static $description = 'Verifies og:image meta tag exists';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$has_og_image = preg_match( '/<meta[^>]*property="og:image"/', $post->post_content );
		if ( ! $has_og_image ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No og:image meta tag found. Social platforms use this image as preview when content is shared. Add an og:image with your featured image URL.', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/html-verify-ogimage-exists',
				'meta' => array(),
			);
		}
		return null;
	}
}
