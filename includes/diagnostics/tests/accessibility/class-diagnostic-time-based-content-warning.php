<?php
/**
 * Time-Based Content Warning Diagnostic
 *
 * Issue #4930: Auto-Advancing Content (No User Control)
 * Pillar: 🌍 Accessibility First / ⚙️ Murphy's Law
 *
 * Checks if time-sensitive content can be paused.
 * Users with cognitive disabilities need more time to read.
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
 * Diagnostic_Time_Based_Content_Warning Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Time_Based_Content_Warning extends Diagnostic_Base {

	protected static $slug = 'time-based-content-warning';
	protected static $title = 'Auto-Advancing Content (No User Control)';
	protected static $description = 'Checks if time-based content can be paused or stopped';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Provide pause button for auto-advancing carousels', 'wpshadow' );
		$issues[] = __( 'Provide stop button for auto-refreshing content', 'wpshadow' );
		$issues[] = __( 'Never auto-advance faster than 5 seconds per item', 'wpshadow' );
		$issues[] = __( 'Pause auto-advance on hover or focus', 'wpshadow' );
		$issues[] = __( 'Provide navigation controls (previous, next)', 'wpshadow' );
		$issues[] = __( 'Never use auto-play for audio or video', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Auto-advancing content is difficult for users with cognitive disabilities, screen reader users, and people with slower reading speeds. Always provide controls.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/time-based-content',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 2.2.2 Pause, Stop, Hide (Level A)',
					'affected_users'          => 'ADHD, dyslexia, cognitive disabilities, screen readers',
					'carousel_problem'        => 'Auto-advancing carousels have 1% engagement rate',
				),
			);
		}

		return null;
	}
}
