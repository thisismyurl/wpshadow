<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Selfreferential_Canonical_Correctness extends Diagnostic_Base {
	protected static $slug = 'html-detect-selfreferential-canonical-correctness';
	protected static $title = 'Self-Referential Canonical Issue';
	protected static $description = 'Detects self-referential canonical correctness';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$permalink = get_permalink( $post );
		if ( preg_match( '/<link[^>]*rel="canonical"[^>]*href="([^"]+)"/', $post->post_content, $matches ) ) {
			$canonical = $matches[1];
			if ( $canonical !== $permalink ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'Canonical URL doesn\'t match this page. Self-referential canonical should point to itself: %s', 'wpshadow' ), $permalink ),
					'severity' => 'medium',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-detect-selfreferential-canonical-correctness',
					'meta' => array( 'current' => $canonical, 'expected' => $permalink ),
				);
			}
		}
		return null;
	}
}
