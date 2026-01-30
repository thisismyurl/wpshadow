<?php
/**
 * Kadence Theme Blocks Performance Diagnostic
 *
 * Kadence Theme Blocks Performance needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1300.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kadence Theme Blocks Performance Diagnostic Class
 *
 * @since 1.1300.0000
 */
class Diagnostic_KadenceThemeBlocksPerformance extends Diagnostic_Base {

	protected static $slug = 'kadence-theme-blocks-performance';
	protected static $title = 'Kadence Theme Blocks Performance';
	protected static $description = 'Kadence Theme Blocks Performance needs optimization';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Kadence\\Theme' ) && ! function_exists( 'kadence_blocks_loader' ) ) {
			return null;
		}

		$issues = array();

		// Check for block library loading
		$block_loading = get_option( 'kadence_blocks_preload_all', '0' );
		if ( '1' === $block_loading ) {
			$issues[] = 'preloading all blocks (loads unused assets)';
		}

		// Check for Google Fonts loading
		$google_fonts = get_option( 'kadence_blocks_google_fonts', '1' );
		$local_fonts = get_option( 'kadence_blocks_local_google_fonts', '0' );
		if ( '1' === $google_fonts && '0' === $local_fonts ) {
			$issues[] = 'Google Fonts loaded from CDN (not locally cached)';
		}

		// Check for CSS inline optimization
		$inline_css = get_option( 'kadence_blocks_inline_css', '0' );
		if ( '0' === $inline_css ) {
			$issues[] = 'CSS not inlined (additional HTTP requests)';
		}

		// Check for dynamic content caching
		global $wpdb;
		$dynamic_blocks = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%wp:kadence/query%'"
		);

		if ( $dynamic_blocks > 0 ) {
			$cache_enabled = get_option( 'kadence_blocks_query_cache', '0' );
			if ( '0' === $cache_enabled ) {
				$issues[] = 'dynamic query blocks not cached (database queries on every load)';
			}
		}

		// Check for icon library loading
		$icon_library = get_option( 'kadence_blocks_icon_library', 'all' );
		if ( 'all' === $icon_library ) {
			$issues[] = 'loading full icon library (increases page weight)';
		}

		// Check for animation library inclusion
		$animations = get_option( 'kadence_blocks_animations_disabled', '0' );
		$has_animations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_content LIKE '%kadenceAnimation%'
			 OR post_content LIKE '%data-aos%'"
		);

		if ( '0' === $animations && 0 === (int) $has_animations ) {
			$issues[] = 'animation library loaded but no animations used';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Kadence theme block performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/kadence-theme-blocks-performance',
			);
		}

		return null;
	}
}
