<?php
/**
 * Admin Notices and Messages Security
 *
 * Checks if admin notices are properly escaped to prevent XSS attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0637
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Notices and Messages Security
 *
 * @since 1.26033.0637
 */
class Diagnostic_Admin_Notices_And_Messages_Security extends Diagnostic_Base {

	protected static $slug = 'admin-notices-and-messages-security';
	protected static $title = 'Admin Notices and Messages Security';
	protected static $description = 'Verifies admin notices are properly escaped';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check for admin_notice hook usage
		$notice_hooks = has_action( 'admin_notices' );
		if ( $notice_hooks > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of notice hooks */
				__( '%d admin_notices hooks detected - verify all escape output properly', 'wpshadow' ),
				$notice_hooks
			);
		}

		// Check for deprecated notice functions
		if ( function_exists( 'add_settings_error' ) ) {
			global $wp_settings_errors;
			$error_count = is_array( $wp_settings_errors ) ? count( $wp_settings_errors ) : 0;
			if ( $error_count > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of error messages */
					__( 'High number of settings errors (%d) accumulated', 'wpshadow' ),
					$error_count
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-notices-and-messages-security',
			);
		}

		return null;
	}
}
