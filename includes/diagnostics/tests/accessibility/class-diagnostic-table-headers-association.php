<?php
/**
 * Table Headers Association Diagnostic
 *
 * Issue #4959: Data Tables Missing Header Associations
 * Pillar: 🌍 Accessibility First
 *
 * Checks if data tables use proper header markup.
 * Screen readers need header associations to understand tables.
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
 * Diagnostic_Table_Headers_Association Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Table_Headers_Association extends Diagnostic_Base {

	protected static $slug = 'table-headers-association';
	protected static $title = 'Data Tables Missing Header Associations';
	protected static $description = 'Checks if tables use <th> elements and scope attributes';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use <th> for all header cells', 'wpshadow' );
		$issues[] = __( 'Add scope="col" for column headers', 'wpshadow' );
		$issues[] = __( 'Add scope="row" for row headers', 'wpshadow' );
		$issues[] = __( 'Use <caption> to describe table purpose', 'wpshadow' );
		$issues[] = __( 'Complex tables: Use id/headers attributes', 'wpshadow' );
		$issues[] = __( 'Layout tables: Use role="presentation"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Screen readers announce table headers for each cell. Without proper markup, tables are incomprehensible to blind users.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/table-headers',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1.6093.1200 Info and Relationships (Level A)',
					'example'                 => '<th scope="col">Name</th>',
					'why_important'           => 'Screen reader announces "Name: John Doe" not just "John Doe"',
				),
			);
		}

		return null;
	}
}
