<?php
/**
 * Mobile Responsiveness Not Optimized Treatment
 *
 * Checks if site is mobile responsive.
 * Mobile responsive = layout adapts to different screen sizes.
 * Without = desktop layout on mobile (tiny text, horizontal scrolling).
 * With responsive = readable, usable on all devices.
 *
 * **What This Check Does:**
 * - Checks viewport meta tag (<meta name="viewport">)
 * - Validates responsive CSS (media queries)
 * - Tests layout on mobile viewport (320px, 375px, 414px)
 * - Checks font sizes (minimum 16px to prevent zoom)
 * - Validates touch target sizes (minimum 48x48px)
 * - Returns severity if not mobile optimized
 *
 * **Why This Matters:**
 * 60%+ traffic = mobile. Non-responsive site = tiny text.
 * Users pinch/zoom to read. Horizontal scroll to see content.
 * Frustrating. High bounce rate. Google penalizes in mobile search.
 * Responsive site = great experience everywhere.
 * Critical for SEO and user experience.
 *
 * **Business Impact:**
 * Site: desktop-only design. Mobile users: 68% of traffic. Bounce
 * rate mobile: 78% (desktop: 35%). Mobile conversion: 0.8% (desktop:
 * 3.2%). Redesigned responsive: viewport tag, flexible grid, media
 * queries, touch-friendly buttons. Mobile bounce: 78% → 32%. Mobile
 * conversion: 0.8% → 2.9% (3.6x improvement). Mobile revenue: +$45K/month.
 * Google mobile rankings: improved 15 positions average. Setup: theme
 * redesign (80 hours) or responsive theme ($60-$200). ROI: massive.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Works great on all devices
 * - #9 Show Value: Mobile traffic converts
 * - #10 Beyond Pure: Inclusive device support
 *
 * **Related Checks:**
 * - Mobile Bandwidth Optimization (mobile performance)
 * - Touch Target Size (mobile usability)
 * - Viewport Configuration (responsive foundation)
 *
 * **Learn More:**
 * Responsive design: https://wpshadow.com/kb/responsive-design
 * Video: Mobile-first WordPress (22min): https://wpshadow.com/training/mobile-first
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Responsiveness Not Optimized Treatment Class
 *
 * Detects non-responsive site.
 *
 * **Detection Pattern:**
 * 1. Check viewport meta tag presence and configuration
 * 2. Scan CSS for responsive media queries
 * 3. Test layout at mobile viewports (320px, 375px, 414px)
 * 4. Validate font sizes (min 16px body text)
 * 5. Check touch target sizes (buttons, links min 48x48px)
 * 6. Return if responsive design missing
 *
 * **Real-World Scenario:**
 * Added viewport tag: <meta name="viewport" content="width=device-width,
 * initial-scale=1">. Implemented responsive CSS: @media (max-width: 768px)
 * { font-size: 16px; flexible grid; stacked layout; }. Touch targets:
 * buttons min 44px height. Result: mobile Google test "Mobile-Friendly".
 * Bounce rate improved 55%. Mobile search rankings improved significantly.
 *
 * **Implementation Notes:**
 * - Checks viewport meta tag
 * - Validates responsive CSS
 * - Tests mobile layouts
 * - Severity: critical (60%+ traffic is mobile)
 * - Treatment: implement responsive design or use responsive theme
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Responsiveness_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsiveness-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsiveness Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site is mobile responsive';

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
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Responsiveness_Not_Optimized' );
	}
}
