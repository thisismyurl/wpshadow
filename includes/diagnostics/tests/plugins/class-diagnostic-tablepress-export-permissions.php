<?php
/**
 * TablePress Export Permissions Diagnostic
 *
 * TablePress export accessible to all users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.417.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Export Permissions Diagnostic Class
 *
 * @since 1.417.0000
 */
class Diagnostic_TablepressExportPermissions extends Diagnostic_Base {

	protected static $slug = 'tablepress-export-permissions';
	protected static $title = 'TablePress Export Permissions';
	protected static $description = 'TablePress export accessible to all users';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'TABLEPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Export permission restrictions
		$export_perm = get_option( 'tablepress_export_permission_required', 0 );
		if ( ! $export_perm ) {
			$issues[] = 'Export permission not restricted';
		}

		// Check 2: CSV export authentication
		$csv_auth = get_option( 'tablepress_csv_export_requires_auth', 0 );
		if ( ! $csv_auth ) {
			$issues[] = 'CSV export not authenticated';
		}

		// Check 3: PDF export protection
		$pdf_protect = get_option( 'tablepress_pdf_export_protected', 0 );
		if ( ! $pdf_protect ) {
			$issues[] = 'PDF export protection not enabled';
		}

		// Check 4: Data sensitivity flagging
		$sensitivity = get_option( 'tablepress_data_sensitivity_flagging_enabled', 0 );
		if ( ! $sensitivity ) {
			$issues[] = 'Data sensitivity flagging not enabled';
		}

		// Check 5: Audit logging
		$logging = get_option( 'tablepress_export_audit_logging_enabled', 0 );
		if ( ! $logging ) {
			$issues[] = 'Export audit logging not enabled';
		}

		// Check 6: Role-based access control
		$rbac = get_option( 'tablepress_export_rbac_enabled', 0 );
		if ( ! $rbac ) {
			$issues[] = 'Role-based export access not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d export permission issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-export-permissions',
			);
		}

		return null;
	}
}
