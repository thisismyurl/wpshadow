<?php
/**
 * AJAX Handler: Create Suggested Workflow
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 */

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;
use WPShadow\Core\Activity_Logger;
use WPShadow\Workflow\Block_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle creation of workflows from suggestions
 */
class Create_Suggested_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_create_suggested_workflow', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to create workflow from suggestion
	 */
	public static function handle(): void {
		// Security check (accept automations or workflow nonces from different UIs)
		$nonce_value  = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$nonce_action = '';

		if ( $nonce_value && wp_verify_nonce( $nonce_value, 'wpshadow_automations' ) ) {
			$nonce_action = 'wpshadow_automations';
		} elseif ( $nonce_value && wp_verify_nonce( $nonce_value, 'wpshadow_workflow' ) ) {
			$nonce_action = 'wpshadow_workflow';
		}

		if ( '' === $nonce_action ) {
			self::send_error( __( 'Please refresh the page and try again.', 'wpshadow' ) );
			return;
		}

		self::verify_request( $nonce_action, 'manage_options' );

		// Get parameters
		$title   = self::get_post_param( 'title', 'text', '', true );
		$trigger = self::get_post_param( 'trigger', 'text', '', true );
		$actions = self::get_post_param( 'actions', 'json', array(), true );

		// Build workflow blocks
		$blocks = array();

		$mapped_trigger = self::map_trigger( $trigger );
		if ( ! $mapped_trigger ) {
			self::send_error( __( 'Unsupported trigger for suggested workflow.', 'wpshadow' ) );
			return;
		}
		$blocks[] = $mapped_trigger;

		$mapped_actions = self::map_actions( $actions, $title );
		$blocks         = array_merge( $blocks, $mapped_actions );

		// Save workflow
		$workflow_id = 'wf_' . wp_generate_uuid4();
		$workflow    = Workflow_Manager::save_workflow( $title, $blocks, $workflow_id );

		// Log activity (Philosophy #9: Show Value)
		Activity_Logger::log(
			'workflow_created',
			sprintf( 'Suggested workflow created: %s', $title ),
			'',
			array(
				'workflow_id' => $workflow_id,
				'trigger'     => $trigger,
				'actions'     => count( $actions ),
				'source'      => 'suggestion',
			)
		);

		self::send_success(
			array(
				'message'     => sprintf(
					/* translators: %s: workflow title */
					__( 'Workflow "%s" created successfully!', 'wpshadow' ),
					$title
				),
				'workflow_id' => $workflow_id,
				'redirect'    => admin_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow_id ),
			)
		);
	}

	/**
	 * Map suggestion trigger slug into workflow trigger block
	 *
	 * @param string $trigger_slug Trigger identifier from suggestion
	 * @return array|null Trigger block or null on failure
	 */
	private static function map_trigger( string $trigger_slug ): ?array {
		$trigger_slug = sanitize_key( $trigger_slug );

		switch ( $trigger_slug ) {
			case 'time_daily':
				return array(
					'type'   => 'trigger',
					'id'     => 'time_trigger',
					'config' => array(
						'frequency' => 'daily',
						'time'      => '02:00',
						'days'      => Block_Registry::get_default_days(),
					),
				);

			case 'time_weekly':
				return array(
					'type'   => 'trigger',
					'id'     => 'time_trigger',
					'config' => array(
						'frequency' => 'weekly',
						'time'      => '03:00',
						'days'      => array( 'monday' ),
					),
				);

			case 'hourly_check':
				return array(
					'type'   => 'trigger',
					'id'     => 'time_trigger',
					'config' => array(
						'frequency' => 'hourly',
						'days'      => Block_Registry::get_default_days(),
					),
				);

			case 'pre_publish_review':
			case 'comment_posted':
			case 'plugin_state_changed':
			case 'post_status_changed':
			case 'user_registered':
			case 'theme_changed':
				return array(
					'type'   => 'trigger',
					'id'     => 'event_trigger',
					'config' => array(
						'event_type'    => $trigger_slug,
						'plugin_action' => 'activated',
					),
				);

			default:
				// Fallback to manual/external trigger so workflow remains runnable
				return array(
					'type'   => 'trigger',
					'id'     => 'manual_cron_trigger',
					'config' => array(),
				);
		}
	}

	/**
	 * Map suggestion action slugs into workflow action blocks
	 *
	 * @param array  $actions Action slugs
	 * @param string $title   Workflow title for messaging
	 * @return array
	 */
	private static function map_actions( array $actions, string $title ): array {
		$blocks = array();

		foreach ( $actions as $action_slug ) {
			$action_slug = sanitize_key( $action_slug );

			switch ( $action_slug ) {
				case 'disable_debug_mode':
					$blocks[] = array(
						'type'   => 'action',
						'id'     => 'apply_treatment',
						'config' => array(
							'specific_treatment' => 'debug_mode',
							'halt_on_error'      => true,
						),
					);
					break;

				case 'check_ssl_health':
					$blocks[] = array(
						'type'   => 'action',
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_type'     => 'specific',
							'specific_diagnostic' => 'ssl',
						),
					);
					break;

				case 'check_plugin_updates':
					$blocks[] = array(
						'type'   => 'action',
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_type'     => 'specific',
							'specific_diagnostic' => 'outdated_plugins',
						),
					);
					break;

				case 'run_performance_scan':
					$blocks[] = array(
						'type'   => 'action',
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_type' => 'full',
						),
					);
					break;

				case 'backup_submission':
					$blocks[] = array(
						'type'   => 'action',
						'id'     => 'backup',
						'config' => array(
							'backup_type' => 'database',
						),
					);
					break;

				case 'send_admin_email':
				case 'send_admin_notification':
				case 'notify_if_suspicious':
					$blocks[] = array(
						'type'   => 'action',
						'id'     => 'send_email',
						'config' => array(
							'recipient'      => 'admin',
							'subject'        => sanitize_text_field( $title ),
							'message'        => __( 'WPShadow workflow notification', 'wpshadow' ),
							'include_report' => false,
						),
					);
					break;

				default:
					$blocks[] = array(
						'type'   => 'action',
						'id'     => 'send_notification',
						'config' => array(
							'title'   => sanitize_text_field( $title ),
							'message' => sprintf( __( 'Workflow step: %s', 'wpshadow' ), $action_slug ),
							'type'    => 'info',
						),
					);
					break;
			}
		}

		// Always add a Kanban note so users see value (#9 Show Value)
		$blocks[] = array(
			'type'   => 'action',
			'id'     => 'kanban_note',
			'config' => array(
				'title'       => sanitize_text_field( $title ),
				'description' => __( 'Suggested workflow created. Review actions and customize as needed.', 'wpshadow' ),
				'severity'    => 'medium',
				'status'      => 'detected',
				'category'    => 'automation',
			),
		);

		return $blocks;
	}
}

Create_Suggested_Workflow_Handler::register();
