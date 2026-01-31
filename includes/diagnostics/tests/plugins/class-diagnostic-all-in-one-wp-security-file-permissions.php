<?php
/**
 * All In One Wp Security File Permissions Diagnostic
 *
 * All In One Wp Security File Permissions misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.865.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security File Permissions Diagnostic Class
 *
 * @since 1.865.0000
 */
class Diagnostic_AllInOneWpSecurityFilePermissions extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-file-permissions';
	protected static $title = 'All In One Wp Security File Permissions';
	protected static $description = 'All In One Wp Security File Permissions misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AIOWPSEC_VERSION' ) && ! function_exists( 'aiowps_is_option_enabled' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify file permissions hardening
		$file_permissions = get_option( 'aiowps_file_permissions', 0 );
		if ( ! $file_permissions ) {
			$issues[] = 'File permissions hardening not enabled';
		}

		// Check 2: Check for wp-config protection
		$wpconfig = get_option( 'aiowps_wpconfig_protection', 0 );
		if ( ! $wpconfig ) {
			$issues[] = 'wp-config.php protection not enabled';
		}

		// Check 3: Verify .htaccess protection
		$htaccess = get_option( 'aiowps_htaccess_protection', 0 );
		if ( ! $htaccess ) {
			$issues[] = '.htaccess protection not enabled';
		}

		// Check 4: Check for file editor disable
		$disable_editor = get_option( 'aiowps_disable_file_editor', 0 );
		if ( ! $disable_editor ) {
			$issues[] = 'File editor not disabled';
		}

		// Check 5: Verify uploads protection
		$upload_protection = get_option( 'aiowps_uploads_protection', 0 );
		if ( ! $upload_protection ) {
			$issues[] = 'Uploads directory protection not enabled';
		}

		// Check 6: Check for file change detection
		$file_change = get_option( 'aiowps_file_change_detection', 0 );
		if ( ! $file_change ) {
			$issues[] = 'File change detection not enabled';
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
					'Found %d All In One WP Security file permissions issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-security-file-permissions',
			);
		}

		return null;
	}
}
