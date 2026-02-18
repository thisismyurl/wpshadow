<?php
/**
 * Simplified Automations Dashboard View
 *
 * Displays a user-friendly interface with Add Automation card, suggestions, and automation list.
 *
 * @package WPShadow
 * @subpackage Views
 * @since   1.6030.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper function to get trigger summary
 * Note: This function is also defined in workflow-list.php
 * Using it here to ensure availability in the dashboard view
 *
 * @since  1.6030.2148
 * @param  array $workflow Workflow data.
 * @return string Trigger summary.
 */
if ( ! function_exists( 'wpshadow_workflow_get_trigger_summary' ) ) {
	function wpshadow_workflow_get_trigger_summary( $workflow ) {
		// Try to get from blocks format (new format)
		$trigger_block = null;
		if ( ! empty( $workflow['blocks'] ) && is_array( $workflow['blocks'] ) ) {
			foreach ( $workflow['blocks'] as $block ) {
				if ( 'trigger' === $block['type'] ) {
					$trigger_block = $block;
					break;
				}
			}
		}

		// Fallback to direct trigger key (legacy format)
		if ( ! $trigger_block && ! empty( $workflow['trigger'] ) ) {
			$trigger_block = $workflow['trigger'];
		}

		if ( ! $trigger_block ) {
			return __( 'No trigger configured', 'wpshadow' );
		}

		$trigger_id = isset( $trigger_block['id'] ) ? $trigger_block['id'] : '';
		$config     = isset( $trigger_block['config'] ) ? $trigger_block['config'] : array();

		// For time triggers, show the schedule
		if ( 'time_daily' === $trigger_id || ( isset( $trigger_block['type'] ) && 'time_trigger' === $trigger_block['type'] ) ) {
			$frequency = isset( $config['frequency'] ) ? $config['frequency'] : 'daily';
			$time      = isset( $config['time'] ) ? $config['time'] : '02:00';

			// Convert time format (24-hour to 12-hour with AM/PM)
			$time_parts   = explode( ':', $time );
			$hour         = intval( $time_parts[0] );
			$minute       = isset( $time_parts[1] ) ? $time_parts[1] : '00';
			$ampm         = $hour >= 12 ? 'PM' : 'AM';
			$display_hour = $hour % 12;
			if ( 0 === $display_hour ) {
				$display_hour = 12;
			}

			$time_display = sprintf( '%d:%s %s', $display_hour, $minute, $ampm );

			if ( 'daily' === $frequency ) {
				return sprintf( __( 'Daily at %s', 'wpshadow' ), $time_display );
			} elseif ( 'weekly' === $frequency ) {
				$day = isset( $config['day'] ) ? ucfirst( $config['day'] ) : 'Sunday';
				return sprintf( __( 'Weekly on %1$s at %2$s', 'wpshadow' ), $day, $time_display );
			} elseif ( 'monthly' === $frequency ) {
				$day = isset( $config['day'] ) ? $config['day'] : '1';
				return sprintf( __( 'Monthly on day %1$s at %2$s', 'wpshadow' ), $day, $time_display );
			}
		}

		// For other triggers, get label from registry
		$all_triggers = \WPShadow\Workflow\Block_Registry::get_triggers();

		if ( ! empty( $trigger_id ) && isset( $all_triggers[ $trigger_id ] ) ) {
			$trigger_block_data = $all_triggers[ $trigger_id ];
			return $trigger_block_data['label'];
		}

		// Fallback to type-based summary
		if ( isset( $trigger_block['type'] ) ) {
			$type      = $trigger_block['type'];
			$summaries = array(
				'time_trigger'      => __( 'On schedule', 'wpshadow' ),
				'page_load_trigger' => __( 'On page load', 'wpshadow' ),
				'event_trigger'     => __( 'On event', 'wpshadow' ),
				'condition_trigger' => __( 'When condition met', 'wpshadow' ),
			);
			return isset( $summaries[ $type ] ) ? $summaries[ $type ] : $type;
		}

		return __( 'Unknown trigger', 'wpshadow' );
	}
}

/**
 * Helper function to get action summary
 * Note: This function is also defined in workflow-list.php
 * Using it here to ensure availability in the dashboard view
 *
 * @since  1.6030.2148
 * @param  array $workflow Workflow data.
 * @return string Action summary.
 */
if ( ! function_exists( 'wpshadow_workflow_get_action_summary' ) ) {
	function wpshadow_workflow_get_action_summary( $workflow ) {
		$action_blocks = array();

		// Try to get from blocks format (new format)
		if ( ! empty( $workflow['blocks'] ) && is_array( $workflow['blocks'] ) ) {
			foreach ( $workflow['blocks'] as $block ) {
				if ( 'action' === $block['type'] ) {
					$action_blocks[] = $block;
				}
			}
		}

		// Fallback to direct actions key (legacy format)
		if ( empty( $action_blocks ) && ! empty( $workflow['actions'] ) && is_array( $workflow['actions'] ) ) {
			$action_blocks = $workflow['actions'];
		}

		if ( empty( $action_blocks ) ) {
			return __( 'No actions configured', 'wpshadow' );
		}

		$first_action = $action_blocks[0];
		$action_id    = isset( $first_action['id'] ) ? $first_action['id'] : '';

		// Get action from registry to get the label
		$all_actions = \WPShadow\Workflow\Block_Registry::get_actions();

		if ( ! empty( $action_id ) && isset( $all_actions[ $action_id ] ) ) {
			$action_block_data = $all_actions[ $action_id ];
			return $action_block_data['label'];
		}

		// Fallback - just count (shouldn't happen with one-action-per-trigger rule)
		return __( '1 action', 'wpshadow' );
	}
}

$workflows   = \WPShadow\Workflow\Workflow_Manager::get_workflows();
$suggestions = \WPShadow\Workflow\Workflow_Suggestions::get_suggestions();
$suggestions = array_slice( $suggestions, 0, 4 );

// Filter out temporary Kanban workflows
$hidden_workflow_ids = \WPShadow\Workflow\Kanban_Workflow_Helper::get_hidden_workflow_ids();
$workflows           = array_filter(
	$workflows,
	function ( $workflow ) use ( $hidden_workflow_ids ) {
		return ! in_array( $workflow['id'], $hidden_workflow_ids, true );
	}
);
?>

<div class="wps-page-container wpshadow-automations-dashboard">
	<!-- Page Header -->
	<?php wpshadow_render_page_header(
		__( 'Automations', 'wpshadow' ),
		sprintf(
			/* translators: %s: link to knowledge base article */
			__( 'Automate your WordPress management with smart workflows. %s', 'wpshadow' ),
			'<a href="https://wpshadow.com/kb/workflows" target="_blank">' . __( 'Learn about automations →', 'wpshadow' ) . '</a>'
		),
		'dashicons-update'
	); ?>

	<!-- Add Automation Card -->
	<div class="wps-card wpshadow-add-automation-card">
		<div class="wps-card-body">
			<div class="wpshadow-add-automation-content">
				<div class="wpshadow-add-automation-icon">
					<span class="dashicons dashicons-plus-alt2"></span>
				</div>
				<div class="wpshadow-add-automation-text">
					<h3><?php esc_html_e( 'Create New Automation', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Set up a new workflow to automate your WordPress management.', 'wpshadow' ); ?></p>
				</div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>" class="wps-btn wps-btn-primary wps-btn-lg">
					<?php esc_html_e( 'Add Automation', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Smart Suggestions Section -->
	<?php if ( ! empty( $suggestions ) ) : ?>
		<div class="wpshadow-suggestions-section">
			<div class="wpshadow-suggestions-header">
				<div>
					<h2><?php esc_html_e( 'Smart Suggestions', 'wpshadow' ); ?></h2>
					<p><?php esc_html_e( 'High-impact automations tailored to your site. One click to create.', 'wpshadow' ); ?></p>
				</div>
			</div>

			<div class="wpshadow-suggestions-grid">
				<?php foreach ( $suggestions as $suggestion ) : ?>
					<div class="wps-card wpshadow-suggestion-card">
						<div class="wps-card-body">
							<div class="wpshadow-suggestion-icon" style="background: <?php echo esc_attr( $suggestion['color'] ); ?>;">
								<span class="dashicons <?php echo esc_attr( $suggestion['icon'] ); ?>"></span>
							</div>
							<h3><?php echo esc_html( $suggestion['title'] ); ?></h3>
							<p class="wpshadow-suggestion-reason"><?php echo esc_html( $suggestion['reason'] ); ?></p>
							<p class="wpshadow-suggestion-description"><?php echo esc_html( $suggestion['description'] ); ?></p>
							<button 
								type="button" 
								class="wps-btn wps-btn-secondary wps-btn-block create-suggested-workflow" 
								data-suggestion-id="<?php echo esc_attr( $suggestion['id'] ); ?>"
								data-title="<?php echo esc_attr( $suggestion['title'] ); ?>"
								data-trigger="<?php echo esc_attr( $suggestion['trigger'] ); ?>"
								data-actions='<?php echo esc_attr( wp_json_encode( $suggestion['actions'] ) ); ?>'
							>
								<?php esc_html_e( 'Create Automation', 'wpshadow' ); ?>
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Saved Automations Section -->
	<?php if ( ! empty( $workflows ) ) : ?>
		<div class="wpshadow-automations-section">
			<h2><?php esc_html_e( 'Your Automations', 'wpshadow' ); ?></h2>
			<p class="wpshadow-automations-intro"><?php esc_html_e( 'Manage and monitor your active automations.', 'wpshadow' ); ?></p>

			<div class="wpshadow-automations-list">
				<?php foreach ( $workflows as $workflow ) : ?>
					<?php
					$trigger_label = wpshadow_workflow_get_trigger_summary( $workflow );
					$action_label  = wpshadow_workflow_get_action_summary( $workflow );
					$is_enabled    = ! isset( $workflow['enabled'] ) || $workflow['enabled'];
					$card_class    = $is_enabled ? 'enabled' : 'disabled';
					?>
					<div class="wps-card wpshadow-automation-card <?php echo esc_attr( $card_class ); ?>" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
						<div class="wps-card-body">
							<div class="wpshadow-automation-header">
								<div class="wpshadow-automation-toggle">
									<label class="workflow-toggle">
										<input type="checkbox" class="workflow-enable-toggle" <?php checked( $is_enabled ); ?>>
										<span class="toggle-slider"></span>
									</label>
								</div>
								<div class="wpshadow-automation-info">
									<h3><?php echo esc_html( $workflow['name'] ); ?></h3>
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
									data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>"
									data-workflow-name="<?php echo esc_attr( $workflow['name'] ); ?>"
									data-trigger="<?php echo esc_attr( $trigger_label ); ?>"
									data-action="<?php echo esc_attr( $action_label ); ?>"
								>
									<?php esc_html_e( 'View Details', 'wpshadow' ); ?>
								</button>
								<button 
									type="button" 
									class="wps-btn wps-btn-success wps-btn-sm workflow-run-btn" 
									data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>"
								>
									<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
								</button>
								<button 
									type="button" 
									class="wps-btn wps-btn-danger wps-btn-sm workflow-delete-btn" 
									data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>"
								>
									<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
								</button>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<!-- Empty State -->
		<div class="wpshadow-empty-automations">
			<div class="wpshadow-empty-icon">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<h2><?php esc_html_e( 'No Automations Yet', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Get started by creating a new automation or using one of our smart suggestions.', 'wpshadow' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>" class="wps-btn wps-btn-primary">
				<?php esc_html_e( 'Create Your First Automation', 'wpshadow' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>

<!-- Automation Detail Modal -->
<div id="wpshadow-automation-detail-modal" class="wpshadow-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="wpshadow-modal-automation-name" aria-hidden="true" data-wpshadow-modal="static" data-overlay-close="true" data-esc-close="true">
	<div class="wpshadow-modal wpshadow-modal--wide" role="document">
		<button type="button" class="wpshadow-modal-close" aria-label="<?php esc_attr_e( 'Close', 'wpshadow' ); ?>" data-wpshadow-modal-close="wpshadow-automation-detail-modal">
			<span class="dashicons dashicons-no" aria-hidden="true"></span>
		</button>
		<div class="wpshadow-modal-header">
			<h2 id="wpshadow-modal-automation-name"></h2>
		</div>
		<div class="wpshadow-modal-body">
			<!-- Visual Summary -->
			<div class="wpshadow-modal-summary">
				<div class="wpshadow-summary-section">
					<h3><?php esc_html_e( 'Trigger', 'wpshadow' ); ?></h3>
					<p id="wpshadow-modal-trigger" class="wpshadow-summary-text"></p>
				</div>
				<div class="wpshadow-summary-arrow">
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</div>
				<div class="wpshadow-summary-section">
					<h3><?php esc_html_e( 'Action', 'wpshadow' ); ?></h3>
					<p id="wpshadow-modal-action" class="wpshadow-summary-text"></p>
				</div>
			</div>

			<!-- Action Buttons -->
			<div class="wpshadow-modal-actions">
				<a id="wpshadow-modal-edit-btn" href="#" class="wps-btn wps-btn-primary">
					<?php esc_html_e( 'Edit', 'wpshadow' ); ?>
				</a>
				<button 
					type="button" 
					id="wpshadow-modal-run-btn" 
					class="wps-btn wps-btn-success"
					data-workflow-id=""
				>
					<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
				</button>
				<button 
					type="button" 
					id="wpshadow-modal-delete-btn" 
					class="wps-btn wps-btn-danger"
					data-workflow-id=""
				>
					<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
				</button>
			</div>

			<!-- Activity History -->
			<div class="wpshadow-modal-activity">
				<h3><?php esc_html_e( 'Recent Activity', 'wpshadow' ); ?></h3>
				<div id="wpshadow-modal-activity-list" class="wpshadow-activity-list">
					<p class="wpshadow-activity-loading"><?php esc_html_e( 'Loading activity...', 'wpshadow' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
/* Automations Dashboard Styles */
.wpshadow-automations-dashboard {
	/* Uses default wps-page-container max-width of 1400px */
}

/* Add Automation Card */
.wpshadow-add-automation-card {
	background: linear-gradient(135deg, #2271b1 0%, #1e5a96 100%);
	border: none;
	color: white;
	margin-bottom: 30px;
}

.wpshadow-add-automation-card .wps-card-body {
	padding: 30px;
}

.wpshadow-add-automation-content {
	display: flex;
	align-items: center;
	gap: 20px;
}

.wpshadow-add-automation-icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 60px;
	height: 60px;
	background: rgba(255, 255, 255, 0.2);
	border-radius: 8px;
	flex-shrink: 0;
}

.wpshadow-add-automation-icon .dashicons {
	width: 32px;
	height: 32px;
	font-size: 32px;
	color: white;
}

.wpshadow-add-automation-text {
	flex: 1;
}

.wpshadow-add-automation-text h3 {
	margin: 0 0 5px 0;
	font-size: 18px;
	color: white;
}

.wpshadow-add-automation-text p {
	margin: 0;
	color: rgba(255, 255, 255, 0.9);
	font-size: 14px;
}

/* Smart Suggestions Section */
.wpshadow-suggestions-section {
	margin-bottom: 40px;
}

.wpshadow-suggestions-header {
	margin-bottom: 20px;
}

.wpshadow-suggestions-header h2 {
	margin: 0 0 5px 0;
	font-size: 18px;
}

.wpshadow-suggestions-header p {
	margin: 0;
	color: #666;
	font-size: 14px;
}

.wpshadow-suggestions-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 20px;
}

.wpshadow-suggestion-card {
	display: flex;
	flex-direction: column;
	cursor: pointer;
	transition: all 0.2s ease;
}

.wpshadow-suggestion-card:hover {
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
	transform: translateY(-2px);
}

.wpshadow-suggestion-card .wps-card-body {
	display: flex;
	flex-direction: column;
	gap: 12px;
	flex: 1;
}

.wpshadow-suggestion-icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 48px;
	height: 48px;
	border-radius: 6px;
}

.wpshadow-suggestion-icon .dashicons {
	width: 24px;
	height: 24px;
	font-size: 24px;
	color: white;
}

.wpshadow-suggestion-card h3 {
	margin: 0;
	font-size: 15px;
}

.wpshadow-suggestion-reason {
	margin: 0;
	font-size: 12px;
	color: #2271b1;
	font-weight: 500;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.wpshadow-suggestion-description {
	margin: 0;
	font-size: 13px;
	color: #666;
	flex: 1;
	line-height: 1.4;
}

.wpshadow-suggestion-card .wps-btn {
	width: 100%;
	margin-top: auto;
}

/* Automations Section */
.wpshadow-automations-section {
	margin-bottom: 30px;
}

.wpshadow-automations-section h2 {
	margin: 0 0 5px 0;
	font-size: 18px;
}

.wpshadow-automations-intro {
	margin: 0 0 20px 0;
	color: #666;
	font-size: 14px;
}

.wpshadow-automations-list {
	display: grid;
	gap: 12px;
}

.wpshadow-automation-card {
	transition: all 0.2s ease;
}

.wpshadow-automation-card.disabled {
	opacity: 0.6;
}

.wpshadow-automation-header {
	display: flex;
	align-items: center;
	gap: 15px;
	margin-bottom: 15px;
}

.wpshadow-automation-toggle {
	flex-shrink: 0;
}

.wpshadow-automation-info {
	flex: 1;
}

.wpshadow-automation-info h3 {
	margin: 0 0 5px 0;
	font-size: 16px;
}

.wpshadow-automation-summary {
	margin: 0;
	display: flex;
	align-items: center;
	gap: 15px;
	font-size: 13px;
	color: #666;
}

.wpshadow-automation-trigger,
.wpshadow-automation-actions {
	display: flex;
	align-items: center;
	gap: 5px;
}

.wpshadow-automation-trigger .dashicons,
.wpshadow-automation-actions .dashicons {
	width: 16px;
	height: 16px;
	font-size: 16px;
	color: #999;
}

.wpshadow-automation-actions-buttons {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
}

.wpshadow-automation-actions-buttons .wps-btn {
	white-space: nowrap;
}

/* Empty State */
.wpshadow-empty-automations {
	text-align: center;
	padding: 60px 20px;
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	margin-top: 20px;
}

.wpshadow-empty-icon {
	font-size: 64px;
	color: #ccc;
	margin-bottom: 20px;
}

.wpshadow-empty-icon .dashicons {
	width: 64px;
	height: 64px;
	font-size: 64px;
}

.wpshadow-empty-automations h2 {
	font-size: 24px;
	margin: 0 0 10px 0;
}

.wpshadow-empty-automations > p {
	font-size: 16px;
	color: #666;
	margin: 0 0 30px 0;
}

/* Toggle Switch */
.workflow-toggle {
	position: relative;
	display: inline-block;
	width: 50px;
	height: 28px;
	cursor: pointer;
}

.workflow-toggle input {
	opacity: 0;
	width: 0;
	height: 0;
}

.toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: 0.4s;
	border-radius: 28px;
}

.toggle-slider:before {
	position: absolute;
	content: '';
	height: 22px;
	width: 22px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: 0.4s;
	border-radius: 50%;
}

input:checked + .toggle-slider {
	background-color: #2271b1;
}

input:checked + .toggle-slider:before {
	transform: translateX(22px);
}


/* Summary Section */
.wpshadow-modal-summary {
	display: flex;
	align-items: center;
	gap: 20px;
	padding: 20px;
	background: #f5f5f5;
	border-radius: 8px;
	margin-bottom: 20px;
}

.wpshadow-summary-section {
	flex: 1;
}

.wpshadow-summary-section h3 {
	margin: 0 0 10px 0;
	font-size: 12px;
	text-transform: uppercase;
	color: #666;
	font-weight: 600;
}

.wpshadow-summary-text {
	margin: 0;
	font-size: 14px;
	color: #333;
}

.wpshadow-summary-arrow {
	display: flex;
	align-items: center;
	justify-content: center;
	color: #999;
}

.wpshadow-summary-arrow .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

/* Modal Actions */
.wpshadow-modal-actions {
	display: flex;
	gap: 10px;
	margin-bottom: 30px;
	flex-wrap: wrap;
}

.wpshadow-modal-actions .wps-btn {
	flex: 1;
	min-width: 120px;
}

/* Activity List */
.wpshadow-modal-activity {
	border-top: 1px solid #eee;
	padding-top: 20px;
}

.wpshadow-modal-activity h3 {
	margin: 0 0 15px 0;
	font-size: 14px;
	font-weight: 600;
}

.wpshadow-activity-list {
	max-height: 300px;
	overflow-y: auto;
}

.wpshadow-activity-item {
	padding: 12px;
	border-left: 3px solid #2271b1;
	background: #f9f9f9;
	margin-bottom: 10px;
	border-radius: 4px;
}

.wpshadow-activity-item-time {
	font-size: 12px;
	color: #999;
	margin-bottom: 5px;
}

.wpshadow-activity-item-text {
	font-size: 13px;
	color: #333;
	margin: 0;
}

.wpshadow-activity-loading {
	text-align: center;
	color: #999;
	font-size: 13px;
	padding: 20px;
	margin: 0;
}

.wpshadow-activity-empty {
	text-align: center;
	color: #999;
	font-size: 13px;
	padding: 20px;
	margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
	.wpshadow-add-automation-content {
		flex-direction: column;
		text-align: center;
	}

	.wpshadow-suggestions-grid {
		grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
	}

	.wpshadow-automation-header {
		flex-direction: column;
		align-items: flex-start;
	}

	.wpshadow-modal-summary {
		flex-direction: column;
		gap: 15px;
	}

	.wpshadow-summary-arrow {
		transform: rotate(90deg);
	}

	.wpshadow-modal-actions .wps-btn {
		width: 100%;
	}
}
</style>

<!-- Workflow Activity Log -->
<div style="margin-top: 40px;">
	<?php
	if ( function_exists( 'wpshadow_render_page_activities' ) ) {
		wpshadow_render_page_activities( 'workflows', 10 );
	}
	?>
</div>
</div>