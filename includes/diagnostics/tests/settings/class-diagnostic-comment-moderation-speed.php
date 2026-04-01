<?php
/**
 * Comment Moderation Speed Diagnostic
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

class Diagnostic_Comment_Moderation_Speed extends Diagnostic_Base {
	protected static $slug = 'comment-moderation-speed';
	protected static $title = 'Comment Moderation Speed';
	protected static $description = 'Detects slow moderation workflow';
	protected static $family = 'functionality';

	public static function check() {
		global $wpdb;

		// Check for comments pending moderation.
		$pending_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '0'"
		);

		if ( $pending_count > 100 ) {
			// Check average time to approve.
			$avg_time = $wpdb->get_var(
				"SELECT AVG(TIMESTAMPDIFF(HOUR, c1.comment_date, c2.comment_date))
				FROM {$wpdb->comments} c1
				JOIN {$wpdb->comments} c2 ON c1.comment_post_ID = c2.comment_post_ID
				WHERE c1.comment_approved = '1'
				AND c2.comment_approved = '1'
				AND c1.comment_date < c2.comment_date
				LIMIT 100"
			);

			if ( $avg_time > 48 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						__( '%d comments pending moderation with average approval time of %.0f hours', 'wpshadow' ),
						$pending_count,
						$avg_time
					),
					'severity'     => 'low',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-moderation-speed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				);
			}
		}

		return null;
	}
}
