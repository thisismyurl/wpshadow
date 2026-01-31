<?php
/**
 * Duplicate Comment Meta Keys Diagnostic
 *
 * Detects duplicate comment meta keys that may indicate data corruption.
 *
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
 * Checks for unnecessary duplicate meta keys in comment metadata.
 *
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
