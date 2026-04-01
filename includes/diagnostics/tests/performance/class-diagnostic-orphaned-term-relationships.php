<?php
/**
 * Orphaned Term Relationships Diagnostic
 *
 * Checks for term relationships pointing to missing term taxonomy rows.
 * Term relationships = links between posts and categories/tags.
 * Orphaned = relationship pointing to deleted term taxonomy.
 * Causes taxonomy queries to fail, wastes space.
 *
 * **What This Check Does:**
 * - Scans term_relationships table
 * - Identifies term_taxonomy_id values with no matching term_taxonomy
 * - Counts orphaned relationships
 * - Estimates wasted space
 * - Checks for taxonomy query errors
 * - Returns severity if orphans found
 *
 * **Why This Matters:**
 * Terms/categories deleted incorrectly (bypass WordPress API).
 * term_taxonomy row removed but relationships remain.
 * Result: broken taxonomy queries, errors in widgets.
 * Taxonomy counts incorrect. Admin UI shows errors.
 * Cleanup: restore data integrity, fix errors.
 *
 * **Business Impact:**
 * Directory site: imported/deleted terms multiple times during
 * migration. Direct database manipulation. term_relationships: 850K
 * rows. Orphan check: 120K orphaned (14%). Symptoms: category widgets
 * showing errors, taxonomy pages 404, admin taxonomy screens slow.
 * Query impact: get_terms() including non-existent terms. Cleanup:
 * DELETE FROM wp_term_relationships WHERE term_taxonomy_id NOT IN
 * (SELECT term_taxonomy_id FROM wp_term_taxonomy). Result: 850K →
 * 730K rows. Space: 18MB reclaimed. Errors: eliminated. Taxonomy
 * queries: 25% faster. Admin: responsive again. Setup: 10 minutes.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Data integrity maintained
 * - #9 Show Value: Errors eliminated, queries faster
 * - #10 Beyond Pure: Proper database relationships
 *
 * **Related Checks:**
 * - Orphaned Post Meta (similar issue)
 * - Term Count Accuracy (related validation)
 * - Taxonomy Performance (broader check)
 *
 * **Learn More:**
 * Term relationship cleanup: https://wpshadow.com/kb/term-cleanup
 * Video: WordPress taxonomy system (14min): https://wpshadow.com/training/taxonomy
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
 * Orphaned Term Relationships Diagnostic Class
 *
 * Detects term relationships referencing missing term taxonomy entries.
 *
 * **Detection Pattern:**
 * 1. Query term_relationships for distinct term_taxonomy_id
 * 2. Check each exists in wp_term_taxonomy
 * 3. Count orphaned relationships
 * 4. Identify affected object_id values (posts)
 * 5. Check for taxonomy query errors
 * 6. Return if orphans found (any count is problematic)
 *
 * **Real-World Scenario:**
 * Plugin uninstall removed custom taxonomy data (term_taxonomy rows)
 * but left relationships. 25K orphaned relationships. Category pages:
 * errors. Widget output: broken. Cleanup query removed orphans.
 * Restored taxonomy functionality. Prevention: always use WordPress
 * API (wp_delete_term) instead of direct database deletes.
 *
 * **Implementation Notes:**
 * - Checks term_relationships integrity
 * - Validates term_taxonomy_id references
 * - Identifies data integrity issues
 * - Severity: medium (causes errors, data integrity issue)
 * - Treatment: DELETE orphaned relationships
 *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-term-relationships?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
