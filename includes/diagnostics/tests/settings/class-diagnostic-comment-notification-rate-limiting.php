<?php
/**
 * Comment Notification Rate Limiting Diagnostic
 *
 * Checks whether comment notification email volume is excessive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1331
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Notification Rate Limiting Diagnostic Class
 *
 * Detects excessive comment notification volume.
 *
 * @since 1.5049.1331
 */
class Diagnostic_Comment_Notification_Rate_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-rate-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive comment notification email volume';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1331
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'comments_notify' ) ) {
			return null;
		}

		global $wpdb;

		$recent_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_date_gmt >= %s",
				gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS )
			)
		);

		if ( $recent_count > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'High comment volume detected in the last hour. Consider adding rate limiting or batching for notification emails.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'comments_last_hour' => $recent_count,
				),
				'kb_link'      => 'https://wpshadow.com/kb/comment-notification-rate-limiting',
			);
		}

		return null;
	}
}
