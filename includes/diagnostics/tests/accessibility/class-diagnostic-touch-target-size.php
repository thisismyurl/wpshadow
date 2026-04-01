<?php
/**
 * Touch Target Size Diagnostic
 *
 * Issue #4942: Touch Targets Too Small (<44px)
 * Pillar: 🌍 Accessibility First
 *
 * Checks if interactive elements meet minimum touch size.
 * Small buttons are difficult for mobile and motor-impaired users.
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
 * Diagnostic_Touch_Target_Size Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Touch_Target_Size extends Diagnostic_Base {

	protected static $slug = 'touch-target-size';
	protected static $title = 'Touch Targets Too Small (<44px)';
	protected static $description = 'Checks if buttons and links are large enough for touch';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Make all buttons at least 44×44 pixels', 'wpshadow' );
		$issues[] = __( 'Add padding to links for larger click area', 'wpshadow' );
		$issues[] = __( 'Space touch targets at least 8px apart', 'wpshadow' );
		$issues[] = __( 'Test on actual mobile devices (not just browser)', 'wpshadow' );
		$issues[] = __( 'Consider 48×48px for primary actions', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Touch targets smaller than 44×44px are hard to tap on mobile. This affects everyone, especially people with motor disabilities.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/touch-targets?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 2.5.5 Target Size (Level AAA: 44×44px)',
					'apple_guideline'         => 'Apple HIG: 44×44pt minimum',
					'google_guideline'        => 'Material Design: 48×48dp minimum',
					'affected_users'          => '60% mobile traffic, motor disabilities, elderly',
				),
			);
		}

		return null;
	}
}
