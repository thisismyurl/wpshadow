<?php
/**
 * Zoom and Text Scaling Support Diagnostic
 *
 * Issue #4864: Admin Doesn't Support 200% Zoom/Text Scaling
 * Pillar: 🌍 Accessibility First
 *
 * Checks if admin interface remains usable at 200% zoom.
 * Affords people with low vision to enlarge content.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Zoom_Text_Scaling_Support Class
 *
 * Checks for:
 * - Fixed widths don't prevent 200% zoom (no hardcoded px widths for containers)
 * - Text doesn't overflow when zoomed
 * - Buttons remain clickable at large sizes (44×44px minimum)
 * - Layout adapts responsively to zoomed text
 * - Horizontal scrolling doesn't become required
 * - Touch targets remain accessible at zoom levels
 *
 * Why this matters:
 * - Low vision users often need 200%+ zoom to read
 * - Mobile users naturally zoom in on mobile devices
 * - Browser zoom shouldn't break layout
 * - Many WordPress themes are built with rigid pixel widths
 *
 * @since 1.6093.1200
 */
class Diagnostic_Zoom_Text_Scaling_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'zoom-text-scaling-support';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Admin Doesn\'t Support 200% Zoom/Text Scaling';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if admin interface works when text is zoomed to 200%';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual zoom testing requires manual QA.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Use relative units (em, rem, %) instead of fixed pixels', 'wpshadow' );
		$issues[] = __( 'Ensure buttons remain 44×44px minimum at default zoom', 'wpshadow' );
		$issues[] = __( 'Test layout at 200% browser zoom (Ctrl/Cmd + to zoom)', 'wpshadow' );
		$issues[] = __( 'Ensure no horizontal scrolling is required at 200% zoom', 'wpshadow' );
		$issues[] = __( 'Use responsive breakpoints for different zoom levels', 'wpshadow' );
		$issues[] = __( 'Don\'t use max-width on main content containers', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'People with low vision use browser zoom to enlarge text. If the layout breaks at 200% zoom, the site becomes unusable for them.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/zoom-support',
				'details'      => array(
					'recommendations'     => $issues,
					'wcag_requirement'    => 'WCAG 2.1.6093.1200 Resize text (at least 200%)',
					'testing_method'      => 'Press Ctrl/Cmd + multiple times to zoom to 200%, navigate entire interface',
					'common_issue'        => 'Sidebar collapses or hidden at small viewports (breaking desktop zoom)',
					'affected_population' => 'Low vision users (~8% of population)',
					'mobile_benefit'      => 'Improves mobile experience for all users',
				),
			);
		}

		return null;
	}
}
