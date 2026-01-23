<?php
declare(strict_types=1);
/**
 * SSL/TLS Certificate Renewal Monitoring Diagnostic
 *
 * Philosophy: Uptime assurance - prevent expired certificates
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if SSL certificate renewal is monitored.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SSL_Certificate_Renewal_Monitoring extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$ssl_monitoring = get_option( 'wpshadow_ssl_monitoring_enabled' );
		
		if ( empty( $ssl_monitoring ) ) {
			return array(
				'id'          => 'ssl-certificate-renewal-monitoring',
				'title'       => 'No SSL Certificate Renewal Monitoring',
				'description' => 'SSL certificate expiration is not monitored. Expired certificates cause site downtime and security warnings. Enable automatic renewal monitoring and alerts.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/monitor-ssl-expiration/',
				'training_link' => 'https://wpshadow.com/training/ssl-management/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}

}