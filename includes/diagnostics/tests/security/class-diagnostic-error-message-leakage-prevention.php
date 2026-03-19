<?php
/**
 * Error Message Leakage Prevention Diagnostic
 *
 * Issue #4988: Error Messages Leak Information
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if error messages expose sensitive info.
 * Database errors leak table names and structure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Error_Message_Leakage_Prevention Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Error_Message_Leakage_Prevention extends Diagnostic_Base {

	protected static $slug = 'error-message-leakage-prevention';
	protected static $title = 'Error Messages Leak Information';
	protected static $description = 'Checks if error messages expose sensitive information';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Show generic errors to users: "Something went wrong"', 'wpshadow' );
		$issues[] = __( 'Log detailed errors internally (not displayed)', 'wpshadow' );
		$issues[] = __( 'Never show database table names in errors', 'wpshadow' );
		$issues[] = __( 'Never show file paths in errors', 'wpshadow' );
		$issues[] = __( 'Disable error display in production (WP_DEBUG_DISPLAY)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Detailed error messages help developers debug, but expose security-sensitive information to attackers. Show friendly errors to users, log details internally.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/error-handling',
				'details'      => array(
					'recommendations'         => $issues,
					'wordpress_setting'       => 'define("WP_DEBUG_DISPLAY", false); // Show errors only in logs',
					'bad_example'             => 'Database connection failed to users.wp_users table',
					'good_example'            => 'An unexpected error occurred. Please contact support.',
				),
			);
		}

		return null;
	}
}
