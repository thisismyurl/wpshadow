<?php
/**
 * Comment Pingback Notification Not Controlled Diagnostic
 *
 * Checks if comment pingback notifications are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2325
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Pingback Notification Not Controlled Diagnostic Class
 *
 * Detects uncontrolled pingback notifications.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Comment_Pingback_Notification_Not_Controlled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-pingback-notification-not-controlled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Pingback Notification Not Controlled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pingback notifications are controlled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2325
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if pingbacks are enabled
		$default_ping_status = get_option( 'default_ping_status' );

		if ( 'open' === $default_ping_status ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Pingbacks are enabled. Consider disabling them to reduce spam and improve security.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-pingback-notification-not-controlled',
			);
		}

		return null;
	}
}
