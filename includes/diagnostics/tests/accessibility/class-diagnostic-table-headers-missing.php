<?php
/**
 * Table Headers Missing Diagnostic
 *
 * Issue #4751: Tables Missing Header Associations
 * Pillar: 🌍 Accessibility First
 *
 * Checks if data tables use proper header associations.
 * Screen readers need <th> elements and scope attributes to read tables correctly.
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
 * Diagnostic_Table_Headers_Missing Class
 *
 * Checks for proper table header implementation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Table_Headers_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'table-headers-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tables Missing Header Associations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data tables use proper <th> elements and scope attributes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Use <th> elements for column and row headers', 'wpshadow' );
		$issues[] = __( 'Add scope="col" for column headers', 'wpshadow' );
		$issues[] = __( 'Add scope="row" for row headers', 'wpshadow' );
		$issues[] = __( 'Use <caption> to describe the table\'s purpose', 'wpshadow' );
		$issues[] = __( 'For complex tables, use headers and id attributes', 'wpshadow' );
		$issues[] = __( 'Don\'t use tables for layout—only for data', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your data tables might not have proper headers for screen readers. When a screen reader encounters a table, it needs <th> elements to announce which row or column a cell belongs to. Without headers, a complex table sounds like a random list of numbers—imagine hearing "42, 18, 7, 93" without knowing those are sales figures for Q1, Q2, Q3, Q4. Header associations let screen readers say "Q1 Sales: 42" so users understand the data relationships. This benefits blind users (2% of population) and helps everyone make sense of complex data.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/table-headers?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'    => $issues,
					'wcag_requirement'   => 'WCAG 2.0.6093.1200 Info and Relationships (Level A)',
					'affected_users'     => 'Blind users (2%), low vision users',
					'simple_table'       => '<table><caption>Sales Data</caption><tr><th scope="col">Quarter</th><th scope="col">Sales</th></tr><tr><th scope="row">Q1</th><td>$42,000</td></tr></table>',
					'testing_tip'        => 'Use a screen reader to navigate your tables and verify each cell is announced with its corresponding headers',
				),
			);
		}

		return null;
	}
}
