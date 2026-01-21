<?php
/**
 * User Notification Email Default State Diagnostic
 *
 * Checks if new user notification emails should be unchecked by default for CASL compliance.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_User_Notification_Email extends Diagnostic_Base {

	protected static $slug = 'user-notification-email';
	protected static $title = 'User Notification Email Compliance';
	protected static $description = 'Checks if new user notification emails follow privacy law opt-in requirements.';

	public static function check(): ?array {
		// Check if we're overriding the default to be unchecked (compliant)
		$email_unchecked_by_default = get_option( 'wpshadow_user_email_unchecked_by_default', false );

		if ( $email_unchecked_by_default ) {
			// Compliant setting is enabled
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'For CASL (Canada), GDPR (EU), and CCPA (US) compliance, new user notification emails should be unchecked by default to ensure explicit opt-in. Currently, the checkbox on user-new.php appears checked by default. Use the Email Test & Configuration tool to enable "Uncheck email notification by default" for strict privacy law compliance.', 'wpshadow' )
			),
			'category'     => 'settings',
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
