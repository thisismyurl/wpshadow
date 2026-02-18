<?php
/**
 * Color-Only Information Diagnostic
 *
 * Issue #4929: Information Conveyed by Color Only
 * Pillar: 🌍 Accessibility First
 *
 * Checks if information uses more than just color.
 * Colorblind users (8% males) can't distinguish red/green.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Color_Only_Information Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Color_Only_Information extends Diagnostic_Base {

	protected static $slug = 'color-only-information';
	protected static $title = 'Information Conveyed by Color Only';
	protected static $description = 'Checks if information uses color plus additional indicators';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Never use color alone to convey information', 'wpshadow' );
		$issues[] = __( 'Add icons: ✓ success (green), ✗ error (red), ⚠ warning (yellow)', 'wpshadow' );
		$issues[] = __( 'Add text labels: "Success", "Error", "Warning"', 'wpshadow' );
		$issues[] = __( 'Use patterns in charts: dots, stripes, hatching', 'wpshadow' );
		$issues[] = __( 'Test with grayscale to verify usability', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Colorblind users (8% of males) cannot distinguish red/green. Always combine color with icons, text, or patterns.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/color-only-information',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 1.4.1 Use of Color (Level A)',
					'colorblind_types'        => 'Deuteranopia (red-green), Protanopia, Tritanopia',
					'affected_users'          => '8% males (1 in 12), 0.5% females',
				),
			);
		}

		return null;
	}
}
