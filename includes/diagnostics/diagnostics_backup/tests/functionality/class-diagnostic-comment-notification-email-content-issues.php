<?php
/**
 * Comment Notification Email Content Issues Diagnostic
 *
 * Checks if comment notification emails contain proper content.
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
 * Comment Notification Email Content Issues Diagnostic Class
 *
 * Detects problems with comment notification email content.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Notification_Email_Content_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-email-content-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Email Content Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks notification email content integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check blog info for proper setup
		$blog_name = get_option( 'blogname', '' );
		$site_url  = get_option( 'siteurl', '' );

		if ( empty( $blog_name ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Blog name is not configured. Comment notification emails may lack context.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-notification-email-content-issues',
			);
		}

		if ( empty( $site_url ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Site URL is not configured. Comment notification emails may not include proper links.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-notification-email-content-issues',
			);
		}

		return null;
	}
}
