<?php
/**
 * Amelia Employee Permissions Diagnostic
 *
 * Amelia employee roles misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.469.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Employee Permissions Diagnostic Class
 *
 * @since 1.469.0000
 */
class Diagnostic_AmeliaEmployeePermissions extends Diagnostic_Base {

	protected static $slug = 'amelia-employee-permissions';
	protected static $title = 'Amelia Employee Permissions';
	protected static $description = 'Amelia employee roles misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify employee role mapping
		$role_map = get_option( 'amelia_user_roles', array() );
		if ( empty( $role_map ) || ! is_array( $role_map ) ) {
			$issues[] = 'Employee role mapping not configured';
		}
		
		// Check 2: Check for admin access restrictions
		$admin_access = get_option( 'amelia_allow_admin_access', 0 );
		if ( $admin_access ) {
			$issues[] = 'Admin access allowed for employees (risk of privilege escalation)';
		}
		
		// Check 3: Verify booking management capability
		$manage_bookings = get_option( 'amelia_employee_manage_bookings', 0 );
		if ( ! $manage_bookings ) {
			$issues[] = 'Employee booking management capability not configured';
		}
		
		// Check 4: Check for customer data access limits
		$data_access = get_option( 'amelia_employee_customer_data_access', 'full' );
		if ( $data_access === 'full' ) {
			$issues[] = 'Employees have full customer data access';
		}
		
		// Check 5: Verify service assignment enforcement
		$service_restrictions = get_option( 'amelia_employee_service_restrictions', 0 );
		if ( ! $service_restrictions ) {
			$issues[] = 'Employee service restrictions not enforced';
		}
		
		// Check 6: Check for notification permission boundaries
		$notification_access = get_option( 'amelia_employee_notification_access', 0 );
		if ( $notification_access ) {
			$issues[] = 'Employees can edit system notifications';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Amelia employee permissions issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/amelia-employee-permissions',
			);
		}
		
		return null;
	}
}
