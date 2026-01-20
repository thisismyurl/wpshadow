<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Admin_Username extends Diagnostic_Base {

	protected static $slug = 'admin-username';
	protected static $title = 'Default Admin Username';
	protected static $description = 'Checks if the default "admin" username exists, which is a security vulnerability.';

	public static function check(): ?array {
		$admin_user = get_user_by( 'login', 'admin' );

		if ( ! $admin_user ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Default "admin" username exists. This is a major security vulnerability as it\'s a primary brute-force target. Create a new admin account with a unique username and delete this one.', 'wpshadow' ),
			'category'     => 'security',
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
