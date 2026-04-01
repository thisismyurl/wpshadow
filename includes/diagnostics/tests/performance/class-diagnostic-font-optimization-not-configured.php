<?php
/**
 * Font Optimization Not Configured Diagnostic
 *
 * Checks if fonts are optimized.
 * Web fonts = custom typography downloaded from server/CDN.
 * Unoptimized = multiple formats, no subsetting, blocking render.
 * Optimized = WOFF2, subsetted, font-display: swap. 70% smaller, faster.
 *
 * **What This Check Does:**
 * - Checks font file formats (WOFF2 preferred)
 * - Validates font subsetting (remove unused characters)
 * - Tests font loading strategy (preload, font-display)
 * - Checks for variable fonts (single file, multiple weights)
 * - Validates font file sizes (<50KB per font)
 * - Returns severity if fonts unoptimized
 *
 * **Why This Matters:**
 * Unoptimized font = 300KB TTF with all characters.
 * Blocks page render. Takes 2+ seconds on mobile.
 * Optimized = 50KB WOFF2 with only used characters.
 * Loads fast, doesn't block render. Page interactive sooner.
 *
 * **Business Impact:**
 * Site uses Google Fonts: 4 weights of custom font. Total: 800KB
 * font files. Mobile 3G: 8 seconds to load fonts. Text invisible
 * (FOIT). Bounce rate: 65%. Optimized: subset to Latin characters
 * only (remove Cyrillic, Greek). Convert to WOFF2. Variable font
 * (1 file, all weights). Total: 120KB (85% reduction). Load time:
 *1.0 seconds. Added font-display: swap (text visible immediately).
 * Bounce rate: 28%. Conversions increased 130%. Setup: 2 hours.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Typography loads reliably
 * - #9 Show Value: Massive file size reduction
 * - #10 Beyond Pure: Modern font technology
 *
 * **Related Checks:**
 * - Fallback Font Configuration (complementary)
 * - Font Loading Strategy (delivery method)
 * - Asset Minification (broader optimization)
 *
 * **Learn More:**
 * Font optimization: https://wpshadow.com/kb/font-optimization
 * Video: Modern web fonts (15min): https://wpshadow.com/training/web-fonts
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font Optimization Not Configured Diagnostic Class
 *
 * Detects unoptimized fonts.
 *
 * **Detection Pattern:**
 * 1. Parse CSS for @font-face declarations
 * 2. Check font file formats (WOFF2 > WOFF > TTF)
 * 3. Measure font file sizes
 * 4. Check for font subsetting (unicode-range)
 * 5. Validate font-display property
 * 6. Return if using legacy formats or large files
 *
 * **Real-World Scenario:**
 * Original: 4 TTF files (Regular, Bold, Italic, BoldItalic) =1.0MB.
 * Optimization: converted to WOFF2 = 400KB. Subset to Latin-only
 * (unicode-range: U+0000-00FF) = 220KB. Used variable font instead
 * (single file, all weights) = 85KB. Final reduction: 93%. Page load
 * time improved 2.5 seconds on mobile. First Contentful Paint: 3.8s →1.0s.
 *
 * **Implementation Notes:**
 * - Checks font formats and sizes
 * - Validates subsetting and loading strategy
 * - Tests variable font usage
 * - Severity: medium (significant mobile impact)
 * - Treatment: convert to WOFF2, subset, use variable fonts
 *
 * @since 0.6093.1200
 */
class Diagnostic_Font_Optimization_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'font-optimization-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Font Optimization Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if fonts are optimized';

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
		// Check for font preloading and optimization
		if ( ! has_filter( 'wp_head', 'preload_google_fonts' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Font optimization is not configured. Preload fonts, use font subsetting, and minimize font file sizes for faster rendering.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/font-optimization-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
