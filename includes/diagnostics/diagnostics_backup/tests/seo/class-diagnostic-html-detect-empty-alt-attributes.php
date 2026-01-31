<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Empty_Alt_Attributes extends Diagnostic_Base {
	protected static $slug = 'html-detect-empty-alt-attributes';
	protected static $title = 'Empty Alt Attributes';
	protected static $description = 'Detects images with empty alt attributes';
	protected static $family = 'accessibility';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$empty_alt = 0;
		if ( preg_match_all( '/<img[^>]*alt=["\']([^"\']*)["\']/', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $alt ) {
				if ( empty( $alt ) ) {
					$empty_alt++;
				}
			}
		}
		if ( $empty_alt < 2 ) 
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
			'description' => sprintf( __( 'Found %d image(s) with empty alt attributes (alt=""). Screen readers announce "image" with no description. Add descriptive alt text to improve accessibility and SEO.', 'wpshadow' ), $empty_alt ),
			'severity' => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-empty-alt-attributes',
			'meta' => array( 'count' => $empty_alt ),
		);
	}
}
