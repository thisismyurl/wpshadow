<?php
/**
 * Lazy Loading Implementation Treatment
 *
 * Detects images and iframes not using lazy loading, causing unnecessary bandwidth waste and slow load.
 *
 * **What This Check Does:**
 * 1. Scans images for loading="lazy" attribute implementation
 * 2. Identifies above-the-fold vs offscreen images
 * 3. Checks for third-party lazy loading plugin installation
 * 4. Validates iframe lazy loading configuration
 * 5. Detects background images without lazy loading
 * 6. Measures potential bandwidth savings from implementation
 *
 * **Why This Matters:**
 * Without lazy loading, browsers load every image on page load, even images users never scroll to see.
 * A single article with 20 images might load 15MB on page load, but only 2-3 images appear above the fold.
 * Those other 12-17 images waste 12-15MB of bandwidth. At 100,000 monthly visits, that's1.0-1.5 petabytes
 * of wasted bandwidth per month. Mobile users on data plans literally pay for bandwidth they never see.\n *
 * **Real-World Scenario:**
 * Gallery website with 500+ high-res photo albums. Each album had 50 images averaging 500KB each (25MB per album).
 * Users rarely scrolled beyond first 10 images. Without lazy loading, every album load consumed full 25MB.\n * After implementing WordPress native lazy loading (loading="lazy" on every <img>), initial page load dropped from
 * 45MB to 2.5MB. Page load time improved from 28 seconds to 3.2 seconds on 4G. Bounce rate decreased 52%.\n * Cost: 2 hours. Value: $45,000 in recovered site traffic and improved user retention.\n *
 * **Business Impact:**
 * - Wasted bandwidth ($500-$5,000/month for media-heavy sites)\n * - Slow page load kills conversions (3 second → 8 second = 40-60% conversion loss)\n * - Mobile users abandon site (99% bounce on slow mobile loads)\n * - SEO penalty (Google penalizes slow sites in rankings)\n * - Server overload from unnecessary image requests\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents bandwidth waste and server overload\n * - #9 Show Value: Delivers 80%+ bandwidth savings for image-heavy sites\n * - #10 Talk-About-Worthy: "Our site loads instantly now" is huge for galleries\n *
 * **Related Checks:**\n * - Image Optimization Plugin Not Active (compression + lazy loading combo)\n * - Responsive Images Not Configured (srcset + lazy loading combo)\n * - Mobile Bandwidth Optimization (overall mobile efficiency)\n * - First Contentful Paint Not Optimized (FCP improvement via lazy loading)\n *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/lazy-loading-implementation\n * - Video: https://wpshadow.com/training/lazy-loading-guide (4 min)\n * - Advanced: https://wpshadow.com/training/intersection-observer-api (9 min)\n *
 * @since 1.6093.1200\n * @package WPShadow\\Treatments\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Treatments;\n\nuse WPShadow\\Core\\Treatment_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Lazy Loading Implementation Treatment Class\n *\n * Verifies offscreen images use lazy loading to defer non-critical image loading.
 *
 * @since 1.6093.1200
 */
class Treatment_Lazy_Loading_Implementation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-implementation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Implementation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies lazy loading is enabled for deferred image loading';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Lazy_Loading_Implementation' );
	}
}
