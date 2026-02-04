<?php
/**
 * Comment Backtrace Generation Diagnostic
 *
 * Checks if comment backtrace data is stored safely without exposing system paths.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Backtrace Diagnostic Class
 *
 * @since 1.6031.1300
 */
class Diagnostic_Comment_Backtrace_Generation extends Diagnostic_Base {

	protected static $slug = 'comment-backtrace-generation';
	protected static $title = 'Comment Backtrace Generation';
	protected static $description = 'Checks if comment backtrace data is stored safely';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1300
	 * @return array|null
	 */
	public static function check() {
		// Check if WP_DEBUG is enabled in production.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WP_DEBUG is enabled which may expose system paths in comment error messages', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-backtrace-generation',
			);
		}

		// Check recent comments for any that might contain debug output.
		global $wpdb;
		$debug_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments}
				WHERE comment_content LIKE %s
				OR comment_content LIKE %s
				OR comment_content LIKE %s
				LIMIT 10",
				'%' . $wpdb->esc_like( '/home/' ) . '%',
				'%' . $wpdb->esc_like( '/var/www/' ) . '%',
				'%' . $wpdb->esc_like( 'wp-includes' ) . '%'
			)
		);

		if ( $debug_comments > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of comments */
					__( 'Found %d comments containing system path information - possible debug output leak', 'wpshadow' ),
					$debug_comments
				),
				'severity'     => 'high',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-backtrace-generation',
			);
		}

		return null;
	}
}
