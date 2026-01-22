<?php
declare(strict_types=1);
/**
 * Site Health Monitoring Status Diagnostic
 *
 * Philosophy: Educate about proactive monitoring - essential for peace of mind
 * Guides to Pro Guardian features for automated monitoring
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check monitoring and alerting configuration.
 */
class Diagnostic_Monitoring_Status extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();
		$recommendations = array();
		
		// Check if WordPress Site Health checks are enabled
		$health_checks_enabled = ! get_option( 'health_checks_disabled' );
		if ( ! $health_checks_enabled ) {
			$issues[] = 'WordPress Site Health monitoring is disabled';
		}
		
		// Check if error logging is enabled
		$debug_enabled = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		if ( ! $debug_enabled ) {
			$recommendations[] = 'Enable debug logging for better troubleshooting (does not affect visitors)';
		}
		
		// Check if admin email matches user email (for notifications)
		$admin_email = get_option( 'admin_email' );
		$current_user = wp_get_current_user();
		if ( $admin_email !== $current_user->user_email ) {
			$recommendations[] = 'Admin email (' . $admin_email . ') is different from your account - verify you can receive alerts';
		}
		
		// Check if backups are being created (if Vault is installed)
		if ( class_exists( 'WPShadow_Vault' ) || defined( 'WPSHADOW_VAULT_VERSION' ) ) {
			$last_backup = get_option( 'wpshadow_vault_last_backup' );
			$backup_age_days = ( time() - intval( $last_backup ) ) / ( 60 * 60 * 24 );
			
			if ( ! $last_backup || $backup_age_days > 7 ) {
				$recommendations[] = 'No recent backups detected - consider scheduling daily backups';
			}
		} else {
			$recommendations[] = 'No backup solution installed - consider WPShadow Vault for automated backups';
		}
		
		// Check if Guardian is monitoring (if installed)
		if ( ! class_exists( 'WPShadow_Guardian' ) && ! defined( 'WPSHADOW_GUARDIAN_VERSION' ) ) {
			$recommendations[] = 'Not using cloud monitoring - Guardian could detect issues 24/7';
		}
		
		if ( ! empty( $issues ) || ! empty( $recommendations ) ) {
			$description = '';
			if ( ! empty( $issues ) ) {
				$description .= '⚠️ ' . implode( '. ', $issues ) . '. ';
			}
			if ( ! empty( $recommendations ) ) {
				$description .= '💡 Recommendations: ' . implode( '. ', $recommendations ) . '.';
			}
			
			return array(
				'title'       => 'Monitoring & Alerting Setup',
				'description' => $description,
				'severity'    => 'medium',
				'category'    => 'monitoring',
				'kb_link'     => 'https://wpshadow.com/kb/monitoring-your-wordpress-site/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=monitoring-status',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
