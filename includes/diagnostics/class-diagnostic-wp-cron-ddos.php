<?php declare(strict_types=1);
/**
 * WP-Cron DDOS Amplification Diagnostic
 *
 * Philosophy: Performance security - prevent cron flooding
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if wp-cron.php is publicly accessible.
 */
class Diagnostic_WP_Cron_DDOS {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check if WP_CRON is disabled (recommended)
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return null; // Properly configured
		}
		
		// Test if wp-cron.php is accessible
		$cron_url = site_url( 'wp-cron.php' );
		$response = wp_remote_post( $cron_url, array(
			'timeout' => 5,
			'blocking' => true,
			'sslverify' => false,
		) );
		
		if ( is_wp_error( $response ) ) {
			return null; // Can't access (maybe already blocked)
		}
		
		$status = wp_remote_retrieve_response_code( $response );
		
		if ( $status === 200 ) {
			return array(
				'id'          => 'wp-cron-ddos',
				'title'       => 'WP-Cron DDOS Amplification Risk',
				'description' => 'wp-cron.php is publicly accessible and processes on every request. Attackers can flood this endpoint to amplify DDOS attacks and overload your server. Define DISABLE_WP_CRON and use system cron.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-wp-cron/',
				'training_link' => 'https://wpshadow.com/training/cron-security/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}
}
