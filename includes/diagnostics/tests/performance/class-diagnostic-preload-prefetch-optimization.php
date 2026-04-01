<?php
/**
 * Preload/Prefetch Optimization Diagnostic
 *
 * Detects resource preload and prefetch implementation optimization.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preload/Prefetch Optimization Diagnostic
 *
 * Analyzes resource hint implementation for optimization opportunities.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Preload_Prefetch_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'preload-prefetch-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Preload/Prefetch Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates resource preload and prefetch implementation strategy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		// Count enqueued resources
		$script_count  = 0;
		$style_count   = 0;
		$font_requests = 0;

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( $wp_scripts->query( $handle ) ) {
					$script_count++;
				}
			}
		}

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( $wp_styles->query( $handle ) ) {
					$style_count++;
				}
				if ( isset( $style->src ) && is_string( $style->src ) && strpos( $style->src, 'fonts' ) !== false ) {
					$font_requests++;
				}
			}
		}

		// Check for preload/prefetch headers
		$preload_hints   = false;
		$prefetch_hints  = false;

		// Check in wp_head hook
		$temp_output = ob_start();
		wp_head();
		$head_content = ob_get_clean();

		if ( strpos( $head_content, 'rel="preload"' ) !== false || strpos( $head_content, "rel='preload'" ) !== false ) {
			$preload_hints = true;
		}
		if ( strpos( $head_content, 'rel="prefetch"' ) !== false || strpos( $head_content, "rel='prefetch'" ) !== false ) {
			$prefetch_hints = true;
		}

		// Count preload/prefetch directives
		preg_match_all( '/rel=["\']preload["\']/', $head_content, $preload_matches );
		preg_match_all( '/rel=["\']prefetch["\']/', $head_content, $prefetch_matches );

		$preload_count  = count( $preload_matches[0] );
		$prefetch_count = count( $prefetch_matches[0] );

		// Generate findings if optimization opportunities exist
		if ( $script_count > 5 && $preload_count < 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of scripts */
					__( '%d scripts enqueued with minimal preload hints. Consider preloading critical scripts.', 'wpshadow' ),
					$script_count
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/preload-prefetch-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'script_count'       => $script_count,
					'preload_count'      => $preload_count,
					'prefetch_count'     => $prefetch_count,
					'font_requests'      => $font_requests,
					'recommendation'     => 'Add preload hints for fonts and critical scripts',
					'impact_estimate'    => '50-150ms faster resource delivery',
					'best_practice'      => 'Preload: fonts, hero image, critical JS. Prefetch: next-page resources',
				),
			);
		}

		if ( $font_requests > 0 && $preload_count === 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Web fonts detected without preload hints. Preload fonts to reduce FCP and FOUT.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/preload-prefetch-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'font_requests'    => $font_requests,
					'preload_count'    => $preload_count,
					'recommendation'   => 'Add <link rel="preload" as="font" href="..." crossorigin>',
					'impact_estimate'  => '100-300ms font loading improvement',
					'browser_support'  => '97% (with fallback)',
				),
			);
		}

		return null;
	}
}
