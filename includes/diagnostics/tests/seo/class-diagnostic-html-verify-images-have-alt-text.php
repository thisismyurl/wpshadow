<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_Images_Have_Alt_Text extends Diagnostic_Base {
	protected static $slug = 'html-verify-images-have-alt-text';
	protected static $title = 'Missing Alt Text on Images';
	protected static $description = 'Verifies all images have alt text';
	protected static $family = 'accessibility';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$no_alt = 0;
		if ( preg_match_all( '/<img[^>]*src=["\']([^"\']+)["\'][^>]*/', $post->post_content, $matches ) ) {
			foreach ( $matches[0] as $tag ) {
				if ( ! preg_match( '/alt\s*=/', $tag ) ) {
					$no_alt++;
				}
			}
		}
		if ( $no_alt < 2 ) 
		// Basic WordPress functionality checks
		if ( ! function_exists( 'get_option' ) ) {
			$issues[] = __( 'Options API not available', 'wpshadow' );
		}
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks not available', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
{ return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d image(s) without alt text attributes. All images need descriptive alt text for accessibility and SEO. Add: <img alt="description" src="...">%s', 'wpshadow' ), $no_alt, '' ),
			'severity' => 'high',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-verify-images-have-alt-text',
			'meta' => array( 'count' => $no_alt ),
		);
	}
}
