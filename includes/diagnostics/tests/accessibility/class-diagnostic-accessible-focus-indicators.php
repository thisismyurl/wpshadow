<?php
/**
 * Accessible Focus Indicators Diagnostic
 *
 * Issue #4889: Focus Indicators Removed or Invisible
 * Pillar: 🌍 Accessibility First
 *
 * Checks if keyboard focus is always visible.
 * Keyboard users need to see where they are on the page.
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
 * Diagnostic_Accessible_Focus_Indicators Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Accessible_Focus_Indicators extends Diagnostic_Base {

	protected static $slug = 'accessible-focus-indicators';
	protected static $title = 'Focus Indicators Removed or Invisible';
	protected static $description = 'Checks if keyboard focus is always visible for navigation';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'NEVER remove focus outlines with outline: none', 'wpshadow' );
		$issues[] = __( 'Use visible focus styles: 2px solid outline', 'wpshadow' );
		$issues[] = __( 'Ensure focus color has 3:1 contrast with background', 'wpshadow' );
		$issues[] = __( 'Focus indicators must be visible on all interactive elements', 'wpshadow' );
		$issues[] = __( 'Custom focus styles should be MORE visible, not less', 'wpshadow' );
		$issues[] = __( 'Test with Tab key: can you see where focus is?', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Keyboard users navigate by pressing Tab. Without visible focus indicators, they are lost and cannot use the interface.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/focus-indicators?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 2.4.7 Focus Visible (Level AA)',
					'affected_users'          => 'Keyboard-only, motor disabilities, power users',
					'css_pattern'             => ':focus { outline: 2px solid #0073aa; outline-offset: 2px; }',
				),
			);
		}

		return null;
	}
}
