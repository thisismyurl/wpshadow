<?php
/**
 * Runcloud Git Deployment Diagnostic
 *
 * Runcloud Git Deployment needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1025.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runcloud Git Deployment Diagnostic Class
 *
 * @since 1.1025.0000
 */
class Diagnostic_RuncloudGitDeployment extends Diagnostic_Base {

	protected static $slug = 'runcloud-git-deployment';
	protected static $title = 'Runcloud Git Deployment';
	protected static $description = 'Runcloud Git Deployment needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for RunCloud hosting environment
		if ( ! defined( 'RUNCLOUD_HUB_VERSION' ) && ! isset( $_SERVER['RUNCLOUD_SERVER_NAME'] ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Git initialized
		$git_dir = ABSPATH . '.git';
		if ( ! is_dir( $git_dir ) ) {
			return null;
		}
		
		// Check 2: Deployment webhook configured
		$webhook_url = get_option( 'runcloud_git_webhook', '' );
		if ( empty( $webhook_url ) ) {
			$issues[] = __( 'Git deployment webhook not configured', 'wpshadow' );
		}
		
		// Check 3: Deployment history logging
		$log_deployments = get_option( 'runcloud_log_deployments', false );
		if ( ! $log_deployments ) {
			$issues[] = __( 'Deployment history not logged (audit trail missing)', 'wpshadow' );
		}
		
		// Check 4: Automatic deployment safety
		$auto_deploy = get_option( 'runcloud_auto_deploy', false );
		$require_approval = get_option( 'runcloud_require_approval', true );
		
		if ( $auto_deploy && ! $require_approval ) {
			$issues[] = __( 'Automatic deployment without approval (dangerous for production)', 'wpshadow' );
		}
		
		// Check 5: Rollback configuration
		$max_backups = get_option( 'runcloud_max_deployment_backups', 0 );
		if ( $max_backups === 0 ) {
			$issues[] = __( 'No deployment backups configured (cannot rollback)', 'wpshadow' );
		}
		
		// Check 6: File permissions after deployment
		$fix_permissions = get_option( 'runcloud_fix_permissions_after_deploy', true );
		if ( ! $fix_permissions ) {
			$issues[] = __( 'File permissions not corrected after deployment', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of deployment issues */
				__( 'RunCloud Git deployment has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/runcloud-git-deployment',
		);
	}
}
