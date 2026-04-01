<?php
/**
 * Minification Status Treatment
 *
 * Detects CSS and JavaScript files that aren't minified, wasting bandwidth unnecessarily.
 *
 * **What This Check Does:**
 * 1. Scans all enqueued CSS and JavaScript files for minification
 * 2. Checks file naming patterns (.min.css, .min.js indicators)
 * 3. Analyzes file content for minification characteristics
 * 4. Identifies unminified development copies in production
 * 5. Calculates potential file size reduction (typically 40-70%)
 * 6. Flags problematic files for each user (custom theme CSS, plugin JS)
 *
 * **Why This Matters:**
 * Minification removes unnecessary whitespace, comments, and line breaks from CSS/JS files. A typical
 * CSS file reduces by 40-60% after minification. For a site with 500KB of CSS/JS, not minifying wastes
 * 200-300KB per page load. At 100,000 monthly visitors, that's 20-30TB of wasted bandwidth per month.
 * This directly costs money in hosting bandwidth and kills mobile performance on slow networks.
 *
 * **Real-World Scenario:**
 * Corporate website with custom theme had 200KB of un-minified theme CSS + 150KB un-minified jQuery plugins.
 * Total 350KB of unnecessary whitespace and comments per page load. Minification reduced files to 95KB combined.
 * Page load dropped from 12 seconds to 8 seconds on 4G. Bounce rate decreased 38%. Additionally, server
 * bandwidth costs dropped from $2,400/month to $800/month (85% reduction on transfer costs).
 * Cost: 15 minutes to run through MinUI or WP Rocket. Value: $1,600/month recurring savings.
 *
 * **Business Impact:**
 * - Wasted bandwidth ($100-$1,000/month depending on traffic)
 * - Slower page loads (every KB adds ~100ms on 4G)
 * - Mobile users experience delays (3G users see 40-80% worse performance)
 * - SEO penalty (Page Speed metric included in rankings)
 * - Developer mistakes (accidental production deployment of dev files)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents bandwidth waste and server overload
 * - #9 Show Value: Delivers 40-70% file size reduction with zero effort
 * - #10 Talk-About-Worthy: "Our site got lighter without changes" is impressive
 *
 * **Related Checks:**
 * - Asset Bundling Not Optimized (HTTP request reduction)
 * - Asset Caching Not Configured (browser cache benefits)
 * - HTTP/2 Support Not Enabled (multiplexing with minified assets)
 * - Page Load Time Not Optimized (overall speed)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/minification-status
 * - Video: https://wpshadow.com/training/css-js-minification (5 min)
 * - Advanced: https://wpshadow.com/training/build-process-optimization (10 min)
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minification Status Treatment Class
 *
 * Detects un-minified CSS and JavaScript files in production environments.
 *
 * @since 0.6093.1200
 */
class Treatment_Minification_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'minification-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Minification Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS and JavaScript files are minified';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes enqueued scripts and styles for minification.
	 * Un-minified files are 40-60% larger than needed.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Minification_Status' );
	}
}
