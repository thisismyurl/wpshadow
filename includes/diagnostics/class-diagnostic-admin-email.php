<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Admin_Email extends Diagnostic_Base {

	protected static $slug = 'admin-email';
	protected static $title = 'Admin Email Configuration';
	protected static $description = 'Checks if admin email is valid and configured.';

	public static function check(): ?array {
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin email is not configured. WordPress sends critical notifications to this address including security alerts, update notifications, and user registration confirmations.', 'wpshadow' ),
				'category'     => 'settings',
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Admin email "%s" is not a valid email address. You will not receive important notifications about your site.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => 'settings',
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( strpos( $admin_email, 'example.com' ) !== false || strpos( $admin_email, 'test.com' ) !== false ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Admin email appears to be a placeholder (%s). Set a real, monitored email address.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => 'settings',
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		return null;
	}
}
