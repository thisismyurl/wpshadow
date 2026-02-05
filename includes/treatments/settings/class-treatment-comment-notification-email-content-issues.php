<?php
/**
 * Comment Notification Email Content Issues Treatment
 *
 * Checks for potential problems in comment notification email content.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1331
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Notification Email Content Issues Treatment Class
 *
 * Detects missing site identity fields used in notification emails.
 *
 * @since 1.5049.1331
 */
class Treatment_Comment_Notification_Email_Content_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-email-content-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Email Content Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for missing content in comment notification emails';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1331
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'comments_notify' ) ) {
			return null;
		}

		$blogname = get_option( 'blogname' );
		$admin_email = get_option( 'admin_email' );

		$issues = array();

		if ( empty( $blogname ) ) {
			$issues[] = __( 'Site name is missing, which can make notification emails unclear.', 'wpshadow' );
		}

		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email address is missing or invalid.', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment notification emails may be missing essential site details.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'details'      => array(
					'issues' => $issues,
				),
				'kb_link'      => 'https://wpshadow.com/kb/comment-notification-email-content-issues',
			);
		}

		return null;
	}
}
