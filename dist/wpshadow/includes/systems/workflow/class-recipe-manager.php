<?php
/**
 * Recipe Manager - Multi-Step Workflow Automation
 *
 * Pre-built workflow recipes that connect WPShadow features.
 * Implements #1 Helpful Neighbor - proactive guidance.
 *
 * @package    WPShadow
 * @subpackage Workflow
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Workflow;

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recipe Manager Class
 *
 * Manages and executes multi-step workflow recipes.
 *
 * @since 1.6093.1200
 */
class Recipe_Manager {

	/**
	 * Initialize the recipe manager.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_wpshadow_get_recipes', array( __CLASS__, 'ajax_get_recipes' ) );
		add_action( 'wp_ajax_wpshadow_execute_recipe', array( __CLASS__, 'ajax_execute_recipe' ) );
		add_action( 'wp_ajax_wpshadow_recipe_step_complete', array( __CLASS__, 'ajax_step_complete' ) );
	}

	/**
	 * Get available workflow recipes.
	 *
	 * @since 1.6093.1200
	 * @return array Available recipes.
	 */
	public static function get_recipes() {
		return array(
			'safe-plugin-update'       => array(
				'title'       => __( 'Safe Plugin Update', 'wpshadow' ),
				'description' => __( 'Update plugins safely with automatic backup and testing', 'wpshadow' ),
				'icon'        => '🔄',
				'time_saved'  => 45,
				'difficulty'  => 'easy',
				'steps'       => array(
					array(
						'id'          => 'clone-site',
						'title'       => __( 'Clone to Staging', 'wpshadow' ),
						'description' => __( 'Create a staging copy of your site to test updates', 'wpshadow' ),
						'action'      => 'site_cloner',
						'automated'   => true,
					),
					array(
						'id'          => 'update-plugins',
						'title'       => __( 'Update Plugins on Staging', 'wpshadow' ),
						'description' => __( 'Update all plugins on the staging site', 'wpshadow' ),
						'action'      => 'manual',
						'automated'   => false,
					),
					array(
						'id'          => 'run-diagnostics',
						'title'       => __( 'Run Health Check', 'wpshadow' ),
						'description' => __( 'Scan for issues after updates', 'wpshadow' ),
						'action'      => 'run_diagnostics',
						'automated'   => true,
					),
					array(
						'id'          => 'conflict-test',
						'title'       => __( 'Test for Conflicts', 'wpshadow' ),
						'description' => __( 'Detect any plugin conflicts', 'wpshadow' ),
						'action'      => 'plugin_conflict',
						'automated'   => true,
					),
					array(
						'id'          => 'apply-to-production',
						'title'       => __( 'Apply to Production', 'wpshadow' ),
						'description' => __( 'If all tests pass, apply updates to live site', 'wpshadow' ),
						'action'      => 'manual',
						'automated'   => false,
					),
				),
			),

			'website-migration'        => array(
				'title'       => __( 'Website Migration', 'wpshadow' ),
				'description' => __( 'Move your site to a new domain or hosting', 'wpshadow' ),
				'icon'        => '🚚',
				'time_saved'  => 90,
				'difficulty'  => 'medium',
				'steps'       => array(
					array(
						'id'          => 'backup-database',
						'title'       => __( 'Backup Database', 'wpshadow' ),
						'description' => __( 'Create a complete database backup', 'wpshadow' ),
						'action'      => 'database_backup',
						'automated'   => true,
					),
					array(
						'id'          => 'find-replace-domain',
						'title'       => __( 'Update Domain References', 'wpshadow' ),
						'description' => __( 'Replace old domain with new domain', 'wpshadow' ),
						'action'      => 'bulk_find_replace',
						'automated'   => true,
					),
					array(
						'id'          => 'update-ssl',
						'title'       => __( 'Update to HTTPS', 'wpshadow' ),
						'description' => __( 'Replace http:// with https:// if needed', 'wpshadow' ),
						'action'      => 'bulk_find_replace',
						'automated'   => true,
					),
					array(
						'id'          => 'regenerate-images',
						'title'       => __( 'Regenerate Thumbnails', 'wpshadow' ),
						'description' => __( 'Rebuild image thumbnails for new CDN/hosting', 'wpshadow' ),
						'action'      => 'regenerate_thumbnails',
						'automated'   => true,
					),
					array(
						'id'          => 'verify-migration',
						'title'       => __( 'Verify Migration', 'wpshadow' ),
						'description' => __( 'Run diagnostics to ensure everything works', 'wpshadow' ),
						'action'      => 'run_diagnostics',
						'automated'   => true,
					),
				),
			),

			'new-theme-setup'          => array(
				'title'       => __( 'New Theme Setup', 'wpshadow' ),
				'description' => __( 'Safely test and deploy a new WordPress theme', 'wpshadow' ),
				'icon'        => '🎨',
				'time_saved'  => 30,
				'difficulty'  => 'easy',
				'steps'       => array(
					array(
						'id'          => 'clone-for-testing',
						'title'       => __( 'Clone Site for Testing', 'wpshadow' ),
						'description' => __( 'Create staging environment to test new theme', 'wpshadow' ),
						'action'      => 'site_cloner',
						'automated'   => true,
					),
					array(
						'id'          => 'activate-theme',
						'title'       => __( 'Activate New Theme', 'wpshadow' ),
						'description' => __( 'Activate the new theme on staging', 'wpshadow' ),
						'action'      => 'manual',
						'automated'   => false,
					),
					array(
						'id'          => 'regenerate-thumbnails',
						'title'       => __( 'Regenerate All Images', 'wpshadow' ),
						'description' => __( 'Rebuild thumbnails for new theme image sizes', 'wpshadow' ),
						'action'      => 'regenerate_thumbnails',
						'automated'   => true,
					),
					array(
						'id'          => 'test-conflicts',
						'title'       => __( 'Test Plugin Compatibility', 'wpshadow' ),
						'description' => __( 'Check for theme/plugin conflicts', 'wpshadow' ),
						'action'      => 'plugin_conflict',
						'automated'   => true,
					),
					array(
						'id'          => 'deploy-theme',
						'title'       => __( 'Deploy to Production', 'wpshadow' ),
						'description' => __( 'Activate theme on live site', 'wpshadow' ),
						'action'      => 'manual',
						'automated'   => false,
					),
				),
			),

			'performance-optimization' => array(
				'title'       => __( 'Performance Optimization', 'wpshadow' ),
				'description' => __( 'Complete site speed optimization workflow', 'wpshadow' ),
				'icon'        => '⚡',
				'time_saved'  => 60,
				'difficulty'  => 'medium',
				'steps'       => array(
					array(
						'id'          => 'backup-before',
						'title'       => __( 'Create Backup', 'wpshadow' ),
						'description' => __( 'Backup database before optimization', 'wpshadow' ),
						'action'      => 'database_backup',
						'automated'   => true,
					),
					array(
						'id'          => 'optimize-database',
						'title'       => __( 'Optimize Database', 'wpshadow' ),
						'description' => __( 'Clean and optimize database tables', 'wpshadow' ),
						'action'      => 'database_optimization',
						'automated'   => true,
					),
					array(
						'id'          => 'regenerate-images',
						'title'       => __( 'Optimize Images', 'wpshadow' ),
						'description' => __( 'Regenerate thumbnails with optimal sizes', 'wpshadow' ),
						'action'      => 'regenerate_thumbnails',
						'automated'   => true,
					),
					array(
						'id'          => 'run-diagnostics',
						'title'       => __( 'Performance Scan', 'wpshadow' ),
						'description' => __( 'Scan for performance bottlenecks', 'wpshadow' ),
						'action'      => 'run_diagnostics',
						'automated'   => true,
					),
					array(
						'id'          => 'apply-fixes',
						'title'       => __( 'Apply Recommended Fixes', 'wpshadow' ),
						'description' => __( 'Auto-fix performance issues', 'wpshadow' ),
						'action'      => 'apply_treatments',
						'automated'   => true,
					),
				),
			),

			'security-hardening'       => array(
				'title'       => __( 'Security Hardening', 'wpshadow' ),
				'description' => __( 'Complete security audit and fixes', 'wpshadow' ),
				'icon'        => '🔒',
				'time_saved'  => 45,
				'difficulty'  => 'easy',
				'steps'       => array(
					array(
						'id'          => 'security-scan',
						'title'       => __( 'Security Audit', 'wpshadow' ),
						'description' => __( 'Run comprehensive security diagnostics', 'wpshadow' ),
						'action'      => 'run_diagnostics',
						'automated'   => true,
					),
					array(
						'id'          => 'backup-config',
						'title'       => __( 'Backup Configuration', 'wpshadow' ),
						'description' => __( 'Backup wp-config.php and .htaccess', 'wpshadow' ),
						'action'      => 'backup_config',
						'automated'   => true,
					),
					array(
						'id'          => 'apply-security-fixes',
						'title'       => __( 'Apply Security Fixes', 'wpshadow' ),
						'description' => __( 'Auto-fix security vulnerabilities', 'wpshadow' ),
						'action'      => 'apply_treatments',
						'automated'   => true,
					),
					array(
						'id'          => 'verify-security',
						'title'       => __( 'Verify Security', 'wpshadow' ),
						'description' => __( 'Re-scan to confirm fixes applied', 'wpshadow' ),
						'action'      => 'run_diagnostics',
						'automated'   => true,
					),
				),
			),
		);
	}

	/**
	 * Execute a recipe.
	 *
	 * @since 1.6093.1200
	 * @param  string $recipe_id Recipe identifier.
	 * @return array {
	 *     Execution result.
	 *
	 *     @type bool   $success Whether execution started.
	 *     @type string $message Result message.
	 *     @type array  $data    Additional data.
	 * }
	 */
	public static function execute_recipe( $recipe_id ) {
		$recipes = self::get_recipes();

		if ( ! isset( $recipes[ $recipe_id ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Recipe not found', 'wpshadow' ),
			);
		}

		$recipe = $recipes[ $recipe_id ];

		// Initialize recipe execution state
		$state = array(
			'recipe_id'       => $recipe_id,
			'current_step'    => 0,
			'completed_steps' => array(),
			'started_at'      => current_time( 'timestamp' ),
			'status'          => 'in_progress',
		);

		update_option( 'wpshadow_recipe_execution_' . $recipe_id, $state );

		// Log recipe start
		Activity_Logger::log(
			'workflow_recipe_started',
			array(
				'recipe_id' => $recipe_id,
				'title'     => $recipe['title'],
			)
		);

		return array(
			'success' => true,
			'message' => __( 'Recipe execution started', 'wpshadow' ),
			'data'    => array(
				'recipe' => $recipe,
				'state'  => $state,
			),
		);
	}

	/**
	 * Mark recipe step as complete.
	 *
	 * @since 1.6093.1200
	 * @param  string $recipe_id Recipe identifier.
	 * @param  string $step_id   Step identifier.
	 * @return array {
	 *     Completion result.
	 *
	 *     @type bool   $success   Whether step was marked complete.
	 *     @type string $message   Result message.
	 *     @type bool   $completed Whether entire recipe is complete.
	 * }
	 */
	public static function complete_step( $recipe_id, $step_id ) {
		$state = get_option( 'wpshadow_recipe_execution_' . $recipe_id );

		if ( ! $state ) {
			return array(
				'success' => false,
				'message' => __( 'Recipe execution not found', 'wpshadow' ),
			);
		}

		// Mark step as completed
		$state['completed_steps'][] = $step_id;
		$state['current_step']++;

		// Check if recipe is complete
		$recipes = self::get_recipes();
		$recipe  = $recipes[ $recipe_id ];
		$is_complete = count( $state['completed_steps'] ) >= count( $recipe['steps'] );

		if ( $is_complete ) {
			$state['status']      = 'completed';
			$state['completed_at'] = current_time( 'timestamp' );

			// Log recipe completion
			Activity_Logger::log(
				'workflow_recipe_completed',
				array(
					'recipe_id'  => $recipe_id,
					'title'      => $recipe['title'],
					'time_saved' => $recipe['time_saved'] ?? 0,
				)
			);
		}

		update_option( 'wpshadow_recipe_execution_' . $recipe_id, $state );

		return array(
			'success'   => true,
			'message'   => __( 'Step completed', 'wpshadow' ),
			'completed' => $is_complete,
		);
	}

	/**
	 * AJAX: Get available recipes.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function ajax_get_recipes() {
		// Use Security_Validator for consistent security checks
		\WPShadow\Core\Security_Validator::verify_request( 'wpshadow_recipes', 'manage_options', 'nonce' );

		$recipes = self::get_recipes();

		wp_send_json_success(
			array(
				'recipes' => $recipes,
			)
		);
	}

	/**
	 * AJAX: Execute a recipe.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function ajax_execute_recipe() {
		// Use Security_Validator for consistent security checks
		\WPShadow\Core\Security_Validator::verify_request( 'wpshadow_recipes', 'manage_options', 'nonce' );

		$recipe_id = isset( $_POST['recipe_id'] ) ? sanitize_key( wp_unslash( $_POST['recipe_id'] ) ) : '';

		if ( empty( $recipe_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid recipe ID', 'wpshadow' ) ) );
		}

		$result = self::execute_recipe( $recipe_id );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * AJAX: Mark step as complete.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function ajax_step_complete() {
		check_ajax_referer( 'wpshadow_recipes', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$recipe_id = isset( $_POST['recipe_id'] ) ? sanitize_key( wp_unslash( $_POST['recipe_id'] ) ) : '';
		$step_id   = isset( $_POST['step_id'] ) ? sanitize_key( wp_unslash( $_POST['step_id'] ) ) : '';

		if ( empty( $recipe_id ) || empty( $step_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'wpshadow' ) ) );
		}

		$result = self::complete_step( $recipe_id, $step_id );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}
}
