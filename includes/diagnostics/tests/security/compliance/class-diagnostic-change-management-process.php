<?php
/**
 * Change Management Process Diagnostic
 *
 * Checks if formal change control process is active for production changes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6035.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Change Management Process Diagnostic Class
 *
 * Detects if proper change management controls are in place
 * for production deployments and configuration changes.
 *
 * @since 1.6035.1445
 */
class Diagnostic_Change_Management_Process extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'change-management-process';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Change Management Process';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if formal change control process is active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'governance';

	/**
	 * Primary persona
	 *
	 * @var string
	 */
	protected static $persona = 'enterprise-corp';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if change management is enabled.
		$change_mgmt_enabled = get_option( 'wpshadow_change_management_enabled', false );
		$change_approval_required = get_option( 'wpshadow_change_approval_required', false );
		
		// Check for version control.
		$has_git = file_exists( ABSPATH . '.git' );
		$git_branch = '';
		
		if ( $has_git ) {
			$head_file = ABSPATH . '.git/HEAD';
			if ( file_exists( $head_file ) ) {
				$head_content = file_get_contents( $head_file );
				if ( preg_match( '#ref: refs/heads/(.+)#', $head_content, $matches ) ) {
					$git_branch = trim( $matches[1] );
				}
			}
		}

		$is_production = ( 
			! defined( 'WP_ENVIRONMENT_TYPE' ) || 
			WP_ENVIRONMENT_TYPE === 'production' 
		) && $git_branch !== 'development' && $git_branch !== 'dev' && $git_branch !== 'staging';

		// Check for deployment plugins.
		$deployment_plugins = array(
			'revisr/revisr.php'                      => 'Revisr',
			'wp-pusher/wp-pusher.php'                => 'WP Pusher',
			'git-it-write/git-it-write.php'          => 'Git It Write',
			'versionpress/versionpress.php'          => 'VersionPress',
			'wp-deployment/wp-deployment.php'        => 'WP Deployment',
		);

		$has_deployment_plugin = false;
		$active_deployment_tool = '';

		foreach ( $deployment_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_deployment_plugin = true;
				$active_deployment_tool = $plugin_name;
				break;
			}
		}

		// Check for CI/CD integration.
		$has_cicd = defined( 'WP_CICD_ENABLED' ) || 
		            get_option( 'wpshadow_cicd_configured', false ) ||
		            file_exists( ABSPATH . '.github/workflows' ) ||
		            file_exists( ABSPATH . '.gitlab-ci.yml' ) ||
		            file_exists( ABSPATH . 'Jenkinsfile' ) ||
		            file_exists( ABSPATH . '.circleci/config.yml' );

		// Check change log.
		$changelog_exists = file_exists( ABSPATH . 'CHANGELOG.md' ) ||
		                    file_exists( ABSPATH . 'CHANGES.md' ) ||
		                    get_option( 'wpshadow_changelog_url', '' ) !== '';

		// Check for maintenance mode capability.
		$has_maintenance_mode = is_plugin_active( 'wp-maintenance-mode/wp-maintenance-mode.php' ) ||
		                        is_plugin_active( 'maintenance/maintenance.php' ) ||
		                        get_option( 'wpshadow_maintenance_mode_available', false );

		// Check rollback capability.
		$has_rollback = ( $has_git && $changelog_exists ) ||
		                is_plugin_active( 'wp-rollback/wp-rollback.php' ) ||
		                get_option( 'wpshadow_rollback_enabled', false );

		// Check approval workflow.
		$approval_workflow_configured = get_option( 'wpshadow_approval_workflow_configured', false );
		$approvers_defined = get_option( 'wpshadow_change_approvers', array() );
		$has_approvers = is_array( $approvers_defined ) && count( $approvers_defined ) > 0;

		// Check change request documentation.
		$last_change_request = get_option( 'wpshadow_last_change_request_id', '' );
		$has_change_tracking = ! empty( $last_change_request );

		// Check recent changes without approval.
		global $wpdb;
		$recent_plugin_changes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name = '_site_transient_update_plugins' 
				AND option_value LIKE %s",
				'%updated_at%'
			)
		);

		// Evaluate issues.
		if ( $is_production && ! $change_mgmt_enabled ) {
			$issues[] = __( 'Production environment without change management controls', 'wpshadow' );
		}

		if ( $is_production && ! $has_git ) {
			$issues[] = __( 'No version control (Git) detected on production site', 'wpshadow' );
		}

		if ( $change_mgmt_enabled && ! $approval_workflow_configured ) {
			$issues[] = __( 'Change management enabled but approval workflow not configured', 'wpshadow' );
		}

		if ( $approval_workflow_configured && ! $has_approvers ) {
			$issues[] = __( 'Approval workflow configured but no approvers defined', 'wpshadow' );
		}

		if ( ! $changelog_exists ) {
			$issues[] = __( 'No changelog file found for tracking changes', 'wpshadow' );
		}

		if ( $is_production && ! $has_cicd ) {
			$issues[] = __( 'No CI/CD pipeline detected for automated deployment validation', 'wpshadow' );
		}

		if ( ! $has_maintenance_mode ) {
			$issues[] = __( 'No maintenance mode capability for planned changes', 'wpshadow' );
		}

		if ( ! $has_rollback ) {
			$issues[] = __( 'No rollback capability configured for failed changes', 'wpshadow' );
		}

		if ( $change_mgmt_enabled && ! $has_change_tracking ) {
			$issues[] = __( 'Change management enabled but no change requests tracked', 'wpshadow' );
		}

		// Check for testing environment.
		if ( $is_production && ! defined( 'WP_STAGING_SITE' ) && ! get_option( 'wpshadow_staging_url', '' ) ) {
			$issues[] = __( 'No staging environment for pre-production change validation', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$context_info = array();
		if ( $has_git ) {
			$context_info[] = sprintf( __( 'Git branch: %s', 'wpshadow' ), $git_branch ?: 'unknown' );
		}
		if ( $active_deployment_tool ) {
			$context_info[] = sprintf( __( 'Using %s', 'wpshadow' ), $active_deployment_tool );
		}
		if ( $has_cicd ) {
			$context_info[] = __( 'CI/CD configured', 'wpshadow' );
		}

		$description = sprintf(
			/* translators: %s: additional context information */
			__( 'Change management process not fully configured. %s', 'wpshadow' ),
			! empty( $context_info ) 
				? implode( '. ', $context_info ) . '.'
				: __( 'No change control tools detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/change-management-process',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'change_mgmt_enabled'        => $change_mgmt_enabled,
				'has_git'                    => $has_git,
				'git_branch'                 => $git_branch,
				'is_production'              => $is_production,
				'has_deployment_plugin'      => $has_deployment_plugin,
				'active_deployment_tool'     => $active_deployment_tool,
				'has_cicd'                   => $has_cicd,
				'changelog_exists'           => $changelog_exists,
				'has_maintenance_mode'       => $has_maintenance_mode,
				'has_rollback'               => $has_rollback,
				'approval_workflow'          => $approval_workflow_configured,
				'has_approvers'              => $has_approvers,
				'has_change_tracking'        => $has_change_tracking,
			),
		);
	}
}
