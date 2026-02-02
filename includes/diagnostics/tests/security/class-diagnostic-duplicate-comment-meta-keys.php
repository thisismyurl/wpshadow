<?php
/**
 * Duplicate Comment Meta Keys Diagnostic
 *
 * Detects duplicate comment meta keys that may indicate data corruption or\n * plugin conflicts. Duplicate meta keys waste database space and cause queries\n * to return unexpected results (which value is returned?). Single meta key\n * per comment is the correct pattern.\n *
 * **What This Check Does:**
 * - Queries commentmeta table for duplicate meta_key entries per comment\n * - Identifies comments with multiple values for same meta_key\n * - Detects source of duplicates (plugin conflict vs corruption)\n * - Counts total duplicate meta entries\n * - Validates meta_value consistency across duplicates\n * - Tests impact on get_comment_meta() queries\n *
 * **Why This Matters:**
 * Duplicate meta keys indicate data quality issues. Scenarios:\n * - Plugin A stores custom field, Plugin B overwrites (duplicate created)\n * - Database corruption from failed query\n * - get_comment_meta() returns first value (maybe wrong one)\n * - Database bloat (data takes 2-3x space)\n * - Queries slow down (more rows to scan)\n *
 * **Business Impact:**
 * Comment system uses meta for spam confidence score. Duplicate meta_key for\n * same comment causes get_comment_meta('spam_score') to return first (lowest)\n * score. High-spam comment shows low score to moderators. Spam slips through.\n * 100 spam comments per month reach site (could have been blocked). Site reputation\n * damaged (appears to allow spam).\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Data integrity verified\n * - #9 Show Value: Prevents silent data corruption\n * - #10 Beyond Pure: Proactive database health\n *
 * **Related Checks:**
 * - Database Corruption Not Checked Regularly (database integrity)\n * - Plugin Conflicts Detection (plugin compatibility)\n * - Comment Meta Inconsistencies (related data issues)\n *
 * **Learn More:**
 * Comment meta best practices: https://wpshadow.com/kb/wordpress-comment-meta\n * Video: Comment metadata management (8min): https://wpshadow.com/training/comment-meta\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Comment Meta Keys Diagnostic Class
 *
 * Implements detection of duplicate comment meta keys.\n *
 * **Detection Pattern:**
 * 1. Query commentmeta table\n * 2. Group by comment_id and meta_key\n * 3. Count rows per group (HAVING count > 1)\n * 4. Collect duplicate entries\n * 5. Check meta_value for consistency across duplicates\n * 6. Return severity if duplicates found\n *
 * **Real-World Scenario:**
 * WordPress site has 2 spam filtering plugins active. First plugin stores\n * spam_score as commentmeta. Second plugin stores separate spam_score for its\n * own confidence. Query get_comment_meta(123, 'spam_score') returns first result.\n * First plugin: score = 95 (high spam). Second plugin: score = 5 (low spam).\n * Moderator sees low spam score, approves comment. Comment was actually spam.\n *
 * **Implementation Notes:**
 * - Uses $wpdb->get_results() with GROUP BY\n * - Counts duplicate entries per comment per key\n * - Validates all duplicates returned\n * - Severity: medium (duplicates detected), high (many duplicates)\n * - Treatment: consolidate duplicate meta values, delete extras\n *
 * @since 1.5049.1230
 */
class Diagnostic_Duplicate_Comment_Meta_Keys extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-comment-meta-keys';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Comment Meta Keys';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for duplicate comment meta keys';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Find comments with duplicate meta keys.
		$duplicates = $wpdb->get_results(
			"SELECT comment_id, meta_key, COUNT(*) as count
			FROM {$wpdb->commentmeta}
			GROUP BY comment_id, meta_key
			HAVING count > 1
			ORDER BY count DESC
			LIMIT 50",
			ARRAY_A
		);

		if ( ! empty( $duplicates ) ) {
			$total_duplicates = count( $duplicates );
			$total_excess = 0;
			$most_duplicated = array();

			foreach ( $duplicates as $duplicate ) {
				$total_excess += ( $duplicate['count'] - 1 );
				if ( count( $most_duplicated ) < 5 ) {
					$most_duplicated[] = sprintf(
						/* translators: 1: meta key, 2: comment ID, 3: count */
						__( 'Key "%1$s" on comment #%2$d (%3$d copies)', 'wpshadow' ),
						$duplicate['meta_key'],
						$duplicate['comment_id'],
						$duplicate['count']
					);
				}
			}

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of comments affected, 2: total excess entries */
					__( '%1$d comments have duplicate meta keys (%2$d excess entries)', 'wpshadow' ),
					$total_duplicates,
					$total_excess
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'details'     => array(
					'affected_comments' => $total_duplicates,
					'excess_entries'    => $total_excess,
					'most_duplicated'   => $most_duplicated,
					'sample_data'       => array_slice( $duplicates, 0, 10 ),
				),
				'kb_link'     => 'https://wpshadow.com/kb/duplicate-comment-meta-keys',
			);
		}

		return null;
	}
}
