<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Measure_Meta_Description_Length extends Diagnostic_Base {
	protected static $slug = 'html-measure-meta-description-length';
	protected static $title = 'Meta Description Length Issue';
	protected static $description = 'Validates meta description length';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match( '/<meta[^>]*name="description"[^>]*content="([^"]+)"/', $post->post_content, $matches ) ) {
			$desc = $matches[1];
			$length = strlen( $desc );
			if ( $length < 120 || $length > 160 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'Meta description is %d characters. Optimal: 120-160 characters. Too short: not enough info; too long: truncated in search results.', 'wpshadow' ), $length ),
					'severity' => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-measure-meta-description-length',
					'meta' => array( 'length' => $length, 'optimal_min' => 120, 'optimal_max' => 160 ),
				);
			}
		}
		return null;
	}
}
