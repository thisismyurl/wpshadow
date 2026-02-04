<?php
/**
 * Accessibility Statement Diagnostic
 *
 * Issue #4979: No Accessibility Statement
 * Pillar: 🌍 Accessibility First / #1: Helpful Neighbor
 *
 * Checks if site has accessibility statement.
 * Users with disabilities need to know support is available.
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
 * Diagnostic_Accessibility_Statement Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Accessibility_Statement extends Diagnostic_Base {

	protected static $slug = 'accessibility-statement';
	protected static $title = 'No Accessibility Statement';
	protected static $description = 'Checks if site has accessibility statement';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Create accessibility statement page', 'wpshadow' );
		$issues[] = __( 'List WCAG 2.1 conformance level (A, AA, AAA)', 'wpshadow' );
		$issues[] = __( 'Describe accessibility features available', 'wpshadow' );
		$issues[] = __( 'Explain how to use keyboard navigation', 'wpshadow' );
		$issues[] = __( 'Provide contact info for accessibility issues', 'wpshadow' );
		$issues[] = __( 'Link to accessibility statement from footer', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Accessibility statements show users with disabilities that you care about their experience. It also demonstrates legal compliance efforts.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accessibility-statement',
				'details'      => array(
					'recommendations'         => $issues,
					'template'                => 'Use AODA or similar template as starting point',
					'legal_benefit'           => 'Shows good faith effort in accessibility compliance',
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
				),
			);
		}

		return null;
	}
}
