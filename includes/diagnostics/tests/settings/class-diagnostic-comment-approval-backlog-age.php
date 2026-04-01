<?php
/**
 * Comment Approval Backlog Age Diagnostic
 *
 * Checks how long comments have been waiting for approval.
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
 * Comment Approval Backlog Age Diagnostic Class
 *
 * Detects stale pending comments.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Approval_Backlog_Age extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-approval-backlog-age';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Approval Backlog Age';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks how long pending comments have waited for approval';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$pending = get_comments(
			array(
				'status'  => 'hold',
				'number'  => 1,
				'orderby' => 'comment_date_gmt',
				'order'   => 'ASC',
			)
		);

		if ( empty( $pending ) ) {
			return null;
		}

		$oldest = $pending[0];
		$age_seconds = time() - strtotime( $oldest->comment_date_gmt . ' GMT' );
		$age_days = (int) floor( $age_seconds / DAY_IN_SECONDS );

		if ( $age_days >= 7 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some comments have been awaiting approval for more than 7 days.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'details'      => array(
					'oldest_pending_days' => $age_days,
				),
				'kb_link'      => 'https://wpshadow.com/kb/comment-approval-backlog-age?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
