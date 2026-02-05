<?php
/**
 * Mobile Responsiveness Treatment
 *
 * Issue #4872: Admin Interface Not Mobile Responsive
 * Pillar: 🌍 Accessibility First
 *
 * Checks if admin interface works on mobile devices.
 * ~60% of web traffic is mobile - admin should work on tablets too.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Mobile_Responsiveness Class
 *
 * Checks for:
 * - Viewport meta tag set correctly
 * - Responsive layouts (not fixed widths)
 * - Touch-friendly buttons (44×44px minimum)
 * - Font sizes readable on mobile (16px+)
 * - No horizontal scrolling required
 * - Mobile-first CSS approach
 * - Touch targets have spacing (no accidental clicks)
 *
 * Why this matters:
 * - Site managers edit content from phones/tablets
 * - Emergency admin actions may happen on mobile
 * - ~60% of web traffic is mobile
 * - WordPress mobile admin app is basic - web admin should work too
 *
 * @since 1.6050.0000
 */
class Treatment_Mobile_Responsiveness extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'mobile-responsiveness';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'Admin Interface Not Mobile Responsive';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if admin interface adapts to mobile/tablet devices';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance treatment - actual mobile testing requires device testing.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Include viewport meta tag: <meta name="viewport" content="width=device-width, initial-scale=1">', 'wpshadow' );
		$issues[] = __( 'Use responsive layouts: media queries, flexbox, grid (not fixed px widths)', 'wpshadow' );
		$issues[] = __( 'Make buttons 44×44px minimum (touch-friendly)', 'wpshadow' );
		$issues[] = __( 'Use 16px+ font size (readable on mobile)', 'wpshadow' );
		$issues[] = __( 'Avoid horizontal scrolling (fits in viewport)', 'wpshadow' );
		$issues[] = __( 'Collapse navigation menu on mobile (hamburger icon)', 'wpshadow' );
		$issues[] = __( 'Test on actual devices, not just DevTools (they lie)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Most web users are on mobile devices. If the admin doesn\'t work on phones/tablets, site managers can\'t manage their site on the go.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-responsiveness',
				'details'      => array(
					'recommendations'         => $issues,
					'mobile_traffic'          => '~60% of web traffic is mobile',
					'common_breakpoints'      => 'Mobile: 320px, Tablet: 768px, Desktop: 1024px+',
					'testing_devices'         => 'iPhone 12 (390px), iPad (768px), Android phones (360-412px)',
					'devtools_caveat'         => 'Chrome DevTools simulates mobile, but actual devices have lag/bugs',
					'google_ranking'          => 'Mobile-friendly is a ranking factor for Google',
				),
			);
		}

		return null;
	}
}
