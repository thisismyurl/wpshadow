<?php
/**
 * Orphaned Term Relationships Diagnostic
 *
 * Checks for term relationships pointing to missing term taxonomy rows.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1354
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned Term Relationships Diagnostic Class
 *
 * Detects term relationships referencing missing term taxonomy entries.
 *
 * @since 1.5049.1354
 */
class Diagnostic_Orphaned_Term_Relationships extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-term-relationships';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Term Relationships';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for term relationships pointing to missing taxonomy entries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1354
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.term_taxonomy_id IS NULL"
		);

		if ( $orphaned >= 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned term relationships were found. Cleaning them up can improve taxonomy queries.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'orphaned_relationships' => $orphaned,
				),
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-term-relationships',
			);
		}

		return null;
	}
}
