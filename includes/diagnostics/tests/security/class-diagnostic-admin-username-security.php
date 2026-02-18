<?php
/**
 * Admin Username Security Diagnostic
 *
 * Issue #4910: Username "admin" Still Exists
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if default "admin" username exists.
 * Attackers always try "admin" first in brute force.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Username_Security Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Admin_Username_Security extends Diagnostic_Base {

	protected static $slug = 'admin-username-security';
	protected static $title = 'Username "admin" Still Exists';
	protected static $description = 'Checks if default admin username is still in use';
	protected static $family = 'security';

	public static function check() {
		// Check if "admin" user exists
		$admin_user = get_user_by( 'login', 'admin' );

		if ( $admin_user && user_can( $admin_user, 'manage_options' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The username "admin" is the first target of brute force attacks. Change it to something unique and unpredictable.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/admin-username',
				'details'      => array(
					'user_id'                 => $admin_user->ID,
					'attack_frequency'        => '99% of brute force attacks try "admin" first',
					'recommendation'          => 'Create new admin, transfer content, delete "admin"',
					'username_requirements'   => 'Use unique, non-dictionary username (not your name)',
				),
			);
		}

		return null;
	}
}
