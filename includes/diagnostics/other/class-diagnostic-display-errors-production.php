<?php
declare(strict_types=1);
/**
 * Display Errors in Production Diagnostic
 *
 * Philosophy: Information disclosure - hide errors in production
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if PHP errors are displayed to users.
 */
class Diagnostic_Display_Errors_Production extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$display_errors = ini_get( 'display_errors' );
		$error_reporting = error_reporting();
		
		$issues = array();
		
		// Check if display_errors is on
		if ( $display_errors && $display_errors !== '0' && $display_errors !== 'off' ) {
			$issues[] = 'display_errors is ON (exposes file paths and logic)';
		}
		
		// Check if error_reporting includes warnings/notices in production
		if ( $error_reporting & E_WARNING || $error_reporting & E_NOTICE ) {
			if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
				$issues[] = 'error_reporting includes warnings/notices in production';
			}
		}
		
		// Check if WP_DEBUG_DISPLAY is enabled
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$issues[] = 'WP_DEBUG_DISPLAY is TRUE (shows WordPress errors to public)';
		}
		
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => 'display-errors-production',
				'title'       => 'Errors Displayed in Production',
				'description' => sprintf(
					'Production error configuration issues: %s. Error messages reveal file paths, database structure, and application logic to attackers.',
					implode( '; ', $issues )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/disable-error-display/',
				'training_link' => 'https://wpshadow.com/training/error-handling/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}
}
