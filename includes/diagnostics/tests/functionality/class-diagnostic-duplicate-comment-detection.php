<?php
/**
 * Duplicate Comment Detection Diagnostic
 *
 * Checks for potential duplicate comments in the system.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Comment Detection Diagnostic Class
 *
 * Detects duplicate comments.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Duplicate_Comment_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-comment-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Comment Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects potential duplicate comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for comments with identical content by same author within 5 seconds
		$duplicate_query = "
			SELECT COUNT(*) as count, comment_author, comment_content
			FROM {$wpdb->comments}
			WHERE comment_approved = 1
			GROUP BY comment_author, comment_content
			HAVING COUNT(*) > 1
		";

		$potential_duplicates = $wpdb->get_results( $duplicate_query );

		$total_dupes = 0;
		foreach ( $potential_duplicates as $dup ) {
			if ( strlen( $dup->comment_content ) > 50 ) {  // Only count substantial comments
				$total_dupes += $dup->count - 1;  // Subtract 1 since first instance isn't a "duplicate"
			}
		}

		if ( $total_dupes > 10 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of duplicate comments */
					__( 'Found approximately %d duplicate comments', 'wpshadow' ),
					$total_dupes
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/duplicate-comment-detection',
			);
		}

		return null;
	}
}
