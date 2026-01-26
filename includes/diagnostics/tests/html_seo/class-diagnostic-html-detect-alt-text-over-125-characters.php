<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Alt_Text_Over_125_Characters extends Diagnostic_Base {
	protected static $slug = 'html-detect-alt-text-over-125-characters';
	protected static $title = 'Alt Text Too Long';
	protected static $description = 'Detects alt text exceeding 125 characters';
	protected static $family = 'accessibility';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$long_alt = array();
		if ( preg_match_all( '/<img[^>]*alt=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
			foreach ( $matches[1] as $alt ) {
				if ( strlen( $alt ) > 125 ) {
					$long_alt[] = substr( $alt, 0, 70 ) . '...';
				}
			}
		}
		if ( count( $long_alt ) < 2 ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d image(s) with alt text over 125 characters. Screen readers may truncate long alt text. Keep it concise: describe the image, not the entire page.', 'wpshadow' ), count( $long_alt ) ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-alt-text-over-125-characters',
			'meta' => array( 'count' => count( $long_alt ) ),
		);
	}
}
