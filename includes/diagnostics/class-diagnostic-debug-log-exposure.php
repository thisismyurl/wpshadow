<?php declare(strict_types=1);
/**
 * Debug Log Exposure Diagnostic
 *
 * Philosophy: Information disclosure - protect error logs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if debug.log is publicly accessible.
 */
class Diagnostic_Debug_Log_Exposure {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Test if debug.log is accessible
		$log_url = content_url( 'debug.log' );
		$response = wp_remote_head( $log_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$status = wp_remote_retrieve_response_code( $response );
		
		if ( $status === 200 ) {
			return array(
				'id'          => 'debug-log-exposure',
				'title'       => 'Debug Log Publicly Accessible',
				'description' => 'Your debug.log file is publicly accessible, exposing sensitive paths, plugin information, and errors to attackers. Block access via .htaccess or move the log.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/protect-debug-log/',
				'training_link' => 'https://wpshadow.com/training/debug-log-security/',
				'auto_fixable' => true,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
