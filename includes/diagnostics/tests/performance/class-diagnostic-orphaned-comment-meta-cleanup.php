<?php
/**
 * Orphaned Comment Meta Cleanup Diagnostic
 *
 * Checks for comment meta entries referencing deleted comments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned Comment Meta Cleanup Diagnostic Class
 *
 * Detects comment meta entries orphaned by deleted comments.
 *
 * @since 1.5049.1401
 */
class Diagnostic_Orphaned_Comment_Meta_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-comment-meta-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Comment Meta Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for comment metadata from deleted comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->commentmeta} cm
			LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
			WHERE c.comment_ID IS NULL"
		);

		if ( $orphaned >= 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned comment metadata from deleted comments was found. Cleaning it up can improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'orphaned_count' => $orphaned,
				),
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-comment-meta-cleanup',
			);
		}

		return null;
	}
}
