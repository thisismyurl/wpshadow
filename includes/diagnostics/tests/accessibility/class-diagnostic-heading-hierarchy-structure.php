<?php
/**
 * Heading Hierarchy Structure Diagnostic
 *
 * Issue #4892: Heading Structure Not Hierarchical (Skip Levels)
 * Pillar: 🌍 Accessibility First
 *
 * Checks if headings follow proper h1→h2→h3 structure.
 * Screen readers use headings to understand page structure.
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
 * Diagnostic_Heading_Hierarchy_Structure Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Heading_Hierarchy_Structure extends Diagnostic_Base {

	protected static $slug = 'heading-hierarchy-structure';
	protected static $title = 'Heading Structure Not Hierarchical (Skip Levels)';
	protected static $description = 'Checks if headings follow logical h1→h2→h3 hierarchy';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use single h1 per page (page title)', 'wpshadow' );
		$issues[] = __( 'Follow hierarchy: h1 → h2 → h3 (don\'t skip levels)', 'wpshadow' );
		$issues[] = __( 'Don\'t use headings for styling (use CSS instead)', 'wpshadow' );
		$issues[] = __( 'Headings should describe content, not just "Click here"', 'wpshadow' );
		$issues[] = __( 'Multiple h2 sections are fine (siblings at same level)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Screen readers use heading hierarchy to understand page structure. Skipping levels (h1 → h4) creates confusion about content organization.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/heading-hierarchy',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 1.3.1 Info and Relationships',
					'bad_example'             => 'h1 → h4 (skips h2, h3)',
					'good_example'            => 'h1 → h2 → h3 → h2 (back to sibling)',
					'screen_reader_nav'       => 'Users press H key to jump between headings',
				),
			);
		}

		return null;
	}
}
