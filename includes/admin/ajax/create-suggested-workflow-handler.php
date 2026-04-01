<?php
/**
 * AJAX Handler: Create Suggested Workflow
 *
 * Creates workflows from Smart Suggestions shown on the automations dashboard.
 * Handles nonce validation, payload normalization, trigger/action mapping,
 * workflow persistence, and response payload for inline UI updates.
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 * @since 0.6093.1200
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
 * Create_Suggested_Workflow_Handler class.
 *
 * @since 0.6093.1200
 */
class Create_Suggested_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register the AJAX action for creating a suggested workflow.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_create_suggested_workflow', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Create a workflow from a Smart Suggestion payload.
	 *
	 * Validates nonce/capability, converts suggestion fields into workflow
	 * blocks, stores the workflow, logs activity, and returns response data
	 * for both redirect and inline dashboard rendering.
	 *
	 * @since 0.6093.1200
	 * @return void Sends JSON response and exits.
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

		$trigger_label = self::get_trigger_summary( $trigger, $mapped_trigger );
		$action_label  = self::get_action_summary( $blocks );
		$card_html     = self::render_workflow_card( $workflow_id, $title, $trigger_label, $action_label );
		$section_html  = self::render_workflow_section( $card_html );

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
				'message'      => sprintf(
					/* translators: %s: workflow title */
					__( 'Workflow "%s" created successfully!', 'wpshadow' ),
					$title
				),
				'workflow_id'  => $workflow_id,
				'redirect'     => admin_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow_id ),
				'card_html'    => $card_html,
				'section_html' => $section_html,
			)
		);
	}

	/**
	 * Get a friendly trigger summary.
	 *
	 * @since 0.6093.1200
	 * @param  string $trigger_slug  Trigger identifier.
	 * @param  array  $trigger_block Trigger block config.
	 * @return string Human-readable trigger label for dashboard cards.
	 */
	private static function get_trigger_summary( string $trigger_slug, array $trigger_block ): string {
		switch ( $trigger_slug ) {
			case 'time_daily':
				return __( 'Daily at 2:00 AM', 'wpshadow' );
			case 'time_weekly':
				return __( 'Weekly on Monday at 3:00 AM', 'wpshadow' );
			case 'hourly_check':
				return __( 'Every hour', 'wpshadow' );
			case 'pre_publish_review':
				return __( 'Before publishing', 'wpshadow' );
			case 'comment_posted':
				return __( 'When a comment is posted', 'wpshadow' );
			case 'plugin_state_changed':
				return __( 'When a plugin is activated', 'wpshadow' );
			case 'post_status_changed':
				return __( 'When a post status changes', 'wpshadow' );
			case 'user_registered':
				return __( 'When a user registers', 'wpshadow' );
			case 'theme_changed':
				return __( 'When the theme changes', 'wpshadow' );
			default:
				break;
		}

		if ( isset( $trigger_block['id'] ) && 'time_trigger' === $trigger_block['id'] ) {
			return __( 'On schedule', 'wpshadow' );
		}

		if ( isset( $trigger_block['id'] ) && 'event_trigger' === $trigger_block['id'] ) {
			return __( 'On event', 'wpshadow' );
		}

		return __( 'On schedule', 'wpshadow' );
	}

	/**
	 * Get a friendly action summary from workflow blocks.
	 *
	 * @since 0.6093.1200
	 * @param  array $blocks Workflow blocks.
	 * @return string Human-readable action label for dashboard cards.
	 */
	private static function get_action_summary( array $blocks ): string {
		$actions = Block_Registry::get_actions();

		foreach ( $blocks as $block ) {
			if ( ! isset( $block['type'], $block['id'] ) ) {
				continue;
			}
			if ( 'action' !== $block['type'] || 'kanban_note' === $block['id'] ) {
				continue;
			}
			if ( isset( $actions[ $block['id'] ]['label'] ) ) {
				return $actions[ $block['id'] ]['label'];
			}
			return __( 'Action', 'wpshadow' );
		}

		return __( 'Action', 'wpshadow' );
	}

	/**
	 * Render an automation card for the dashboard list.
	 *
	 * @since 0.6093.1200
	 * @param  string $workflow_id   Workflow ID.
	 * @param  string $title         Workflow title.
	 * @param  string $trigger_label Trigger summary label.
	 * @param  string $action_label  Action summary label.
	 * @return string Rendered HTML card markup.
	 */
	private static function render_workflow_card( string $workflow_id, string $title, string $trigger_label, string $action_label ): string {
		$card_class = 'enabled';

		ob_start();
		?>
		<div class="wps-card wpshadow-automation-card <?php echo esc_attr( $card_class ); ?>" data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>">
			<div class="wps-card-body">
				<div class="wpshadow-automation-header">
					<div class="wpshadow-automation-toggle">
						<label class="workflow-toggle">
							<input type="checkbox" class="workflow-enable-toggle" <?php echo checked( true, true, false ); ?>>
							<span class="toggle-slider"></span>
						</label>
					</div>
					<div class="wpshadow-automation-info">
						<h3><?php echo esc_html( $title ); ?></h3>
						<p class="wpshadow-automation-summary">
							<span class="wpshadow-automation-trigger">
								<span class="dashicons dashicons-clock"></span>
								<?php echo esc_html( $trigger_label ); ?>
							</span>
							<span class="wpshadow-automation-actions">
								<span class="dashicons dashicons-admin-tools"></span>
								<?php echo esc_html( $action_label ); ?>
							</span>
						</p>
					</div>
				</div>
				<div class="wpshadow-automation-actions-buttons">
					<button
						type="button"
						class="wps-btn wps-btn-secondary wps-btn-sm wpshadow-automation-detail-btn"
						data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>"
						data-workflow-name="<?php echo esc_attr( $title ); ?>"
						data-trigger="<?php echo esc_attr( $trigger_label ); ?>"
						data-action="<?php echo esc_attr( $action_label ); ?>"
					>
						<?php esc_html_e( 'View Details', 'wpshadow' ); ?>
					</button>
					<button
						type="button"
						class="wps-btn wps-btn-success wps-btn-sm workflow-run-btn"
						data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>"
					>
						<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
					</button>
					<button
						type="button"
						class="wps-btn wps-btn-danger wps-btn-sm workflow-delete-btn"
						data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>"
					>
						<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render the automations section wrapper with a single card.
	 *
	 * @since 0.6093.1200
	 * @param  string $card_html Rendered card HTML.
	 * @return string Rendered section HTML markup.
	 */
	private static function render_workflow_section( string $card_html ): string {
		ob_start();
		?>
		<div class="wpshadow-automations-section">
			<h2><?php esc_html_e( 'Your Automations', 'wpshadow' ); ?></h2>
			<p class="wpshadow-automations-intro">
				<?php esc_html_e( 'Manage and monitor your active automations.', 'wpshadow' ); ?>
			</p>
			<div class="wpshadow-automations-list">
				<?php echo $card_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Map suggestion trigger slug into workflow trigger block
	 *
	 * @since 0.6093.1200
	 * @param  string $trigger_slug Trigger identifier from suggestion.
	 * @return array|null Trigger block structure.
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
	 * @since 0.6093.1200
	 * @param  array  $actions Action slugs.
	 * @param  string $title   Workflow title for messaging.
	 * @return array Workflow action blocks, including a Kanban note action.
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
