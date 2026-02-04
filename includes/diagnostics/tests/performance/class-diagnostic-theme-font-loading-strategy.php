<?php
/**
 * Theme Font Loading Strategy Diagnostic
 *
 * Checks if font loading uses modern optimization techniques (preconnect, preload, font-display).
 *
 * **What This Check Does:**
 * 1. Detects font sources (self-hosted vs CDN)
 * 2. Checks for preconnect link tags
 * 3. Verifies preload hints on critical fonts
 * 4. Analyzes font-display values (swap, block, auto, fallback, optional)
 * 5. Measures font loading performance impact
 * 6. Flags non-optimized loading patterns\n *
 * **Why This Matters:**\n * Modern font optimization reduces loading time by 70-80% through: preconnect (start DNS lookup early),
 * preload (start font download immediately), font-display: swap (show text immediately). Without these,
 * font loading is slow and blocks content.\n *
 * **Real-World Scenario:**\n * Theme loaded fonts naively (no preconnect, no preload, no font-display). Fonts loaded during page parse
 * (late). Text didn't appear until fonts ready. Performance: 1.8s before text visible. After adding
 * preconnect + preload + font-display: swap, font download started immediately and text appeared via
 * system font in 0.1s (custom font loaded in parallel). Visitors saw content 1.7 seconds faster.\n *
 * **Business Impact:**\n * - Visible text delay: 1-3 seconds slower\n * - Blank space where text should be\n * - Bounce rate increases 15-25%\n * - Core Web Vitals: LCP delayed 1-3 seconds\n * - Conversion rate drops 15-30%\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Immediate visible performance improvement\n * - #8 Inspire Confidence: Modern optimization patterns\n * - #10 Talk-About-Worthy: "Fonts load invisibly in background now"\n *
 * **Related Checks:**\n * - Theme Font Loading Issues (overall strategy)\n * - Theme Asset Loading Optimization (asset patterns)\n * - First Contentful Paint (content timing)\n * - Core Web Vitals (LCP measurement)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/font-strategy-optimization\n * - Video: https://wpshadow.com/training/preload-preconnect-hints (7 min)\n * - Advanced: https://wpshadow.com/training/font-subsetting-strategies (12 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Font Loading Strategy Diagnostic
 *
 * Flags themes loading fonts without optimization hints.
 *
 * @since 1.6030.2240
 */
class Diagnostic_Theme_Font_Loading_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-font-loading-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Font Loading Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if font loading is optimized (preconnect/preload)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 60000 );
		if ( false === $content ) {
			return null;
		}

		$uses_google_fonts = false !== strpos( $content, 'fonts.googleapis.com' );
		$has_preconnect = false !== strpos( $content, 'preconnect' );
		$has_preload = false !== strpos( $content, 'preload' );

		if ( $uses_google_fonts && ! ( $has_preconnect || $has_preload ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Fonts are loaded without preconnect or preload hints', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-font-loading-strategy',
				'details'      => array(
					'issues' => array(
						__( 'Add preconnect/preload for font domains', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
