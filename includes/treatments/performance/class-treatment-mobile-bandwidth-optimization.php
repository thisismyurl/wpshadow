<?php
/**
 * Mobile Bandwidth Optimization Treatment
 *
 * Detects mobile images served at desktop sizes, wasting bandwidth and increasing load times.
 *
 * **What This Check Does:**
 * 1. Analyzes responsive image srcset attributes on mobile screens
 * 2. Detects images without srcset (always serves full desktop size on mobile)
 * 3. Checks for lazy loading implementation (defer offscreen image loading)
 * 4. Measures actual bandwidth consumed on 3G/4G connections
 * 5. Validates viewport meta tag and responsive CSS
 * 6. Identifies images that should use picture element for art direction
 *
 * **Why This Matters:**
 * A single hero image at 2560x1440 (2.8MB) served on mobile wastes 2.5MB of bandwidth.
 * With responsive images + srcset, that same image is 400KB on mobile (87% reduction).
 * Mobile data costs users real money ($0.05-$0.50 per MB in many countries). Serving
 * 10MB of unnecessary image data adds up to $0.50-$5 per visitor. A site with 100,000
 * mobile visitors losing 50% of potential conversions due to slow load times is leaving
 * $500-$5,000 per day on the table.
 *
 * **Real-World Scenario:**
 * Mobile app review site with 80% mobile traffic and terrible mobile conversion. Investigation
 * showed app screenshots served at 4K resolution (12MB per page) to mobile users on 4G.
 * Adding responsive images with srcset optimized for mobile devices (320px, 600px, 1200px)
 * reduced average page image size from 18MB to 2.2MB on mobile. Page load dropped from 14s
 * to 2.1s on 4G. Mobile conversion increased 67% that month. Cost: 3 hours. Value: $85,000
 * in additional reviews/subscriptions.
 *
 * **Business Impact:**
 * - Users on metered data abandon site (60-80% bounce rate increase)
 * - Mobile conversion rates cut in half
 * - 3G users experience 20+ second load times
 * - Site becomes "that slow one" reputation
 * - Analytics show 50% of mobile users leave before first interaction
 * - Revenue impact: $1,000-$10,000+ per day for e-commerce/subscriptions
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents invisible user frustration on mobile
 * - #9 Show Value: Delivers 85%+ bandwidth savings for image-heavy sites
 * - #10 Talk-About-Worthy: Users feel the improvement immediately
 *
 * **Related Checks:**
 * - Lazy Load Images Not Implemented (defer offscreen loading)
 * - Image Optimization Plugin Not Active (compression)
 * - Responsive Images Not Configured (viewport-based sizing)
 * - First Contentful Paint Not Optimized (speed metric)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/mobile-bandwidth-optimization
 * - Video: https://wpshadow.com/training/responsive-images-101 (5 min)
 * - Advanced: https://wpshadow.com/training/srcset-strategy (8 min)
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
 * Mobile Bandwidth Optimization Treatment Class
 *
 * Validates responsive image serving and mobile-specific bandwidth efficiency.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Bandwidth_Optimization extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-bandwidth-optimization';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Bandwidth Optimization';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests adaptive image loading on mobile networks';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests if site implements bandwidth-saving features for mobile users
	 * like lazy loading and responsive images.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Bandwidth_Optimization' );
	}
}
