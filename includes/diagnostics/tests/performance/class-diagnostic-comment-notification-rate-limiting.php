<?php
/**
 * Comment Notification Rate Limiting Diagnostic
 *
 * Checks if comment notification rate limiting is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
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
 * Detects problems with comment notification rate limiting.
 *
 * @since 1.2601.2310
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
	protected static $description = 'Checks if notification rate limiting is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if spam plugin is active for rate limiting
		$spam_check_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam-bee.php',
			'wordfence/wordfence.php',
		);

		$spam_plugin_active = false;
		foreach ( $spam_check_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$spam_plugin_active = true;
				break;
			}
		}

		if ( ! $spam_plugin_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No comment spam/rate limiting plugin is active. Comment notifications could be flooded by spam activity.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-notification-rate-limiting',
			);
		}

		return null;
	}
}
