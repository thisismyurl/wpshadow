<?php
/**
 * Workflow List View (Workflow Builder dashboard)
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$workflows = \WPShadow\Workflow\Workflow_Manager::get_workflows();

// Filter out temporary Kanban workflows
$hidden_workflow_ids = \WPShadow\Workflow\Kanban_Workflow_Helper::get_hidden_workflow_ids();
$workflows           = array_filter(
	$workflows,
	function ( $workflow ) use ( $hidden_workflow_ids ) {
		return ! in_array( $workflow['id'], $hidden_workflow_ids, true );
	}
);

$suggestions = \WPShadow\Workflow\Workflow_Suggestions::get_suggestions();
$suggestions = array_slice( $suggestions, 0, 6 );
?>

<div class="wps-page-container wpshadow-workflow-list">
	<!-- Page Header -->
	<?php wpshadow_render_page_header(
		__( 'Workflow Manager', 'wpshadow' ),
		sprintf(
			/* translators: %s: link to knowledge base article */
			__( 'Automate your WordPress management with smart workflows. %s', 'wpshadow' ),
			'<a href="https://wpshadow.com/kb/workflow-manager" target="_blank">' . __( 'Learn about workflows →', 'wpshadow' ) . '</a>'
		),
		'dashicons-update'
	); ?>

	<?php if ( empty( $workflows ) ) : ?>
		<!-- Create Workflow Button Section -->
		<div class="workflow-cta-section">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>" class="wps-btn wps-btn-primary wps-btn-lg">
				<?php esc_html_e( 'Create a Workflow', 'wpshadow' ); ?>
			</a>
		</div>

		<!-- Empty State -->
		<div class="wpshadow-empty-state">
			<div class="empty-state-icon">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<h2><?php esc_html_e( 'No Workflows Yet', 'wpshadow' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: link to build workflow */
					esc_html__( 'Start with a smart suggestion tailored to your site, or %s.', 'wpshadow' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ) . '">' . esc_html__( 'build your own workflow', 'wpshadow' ) . '</a>'
				);
				?>
			</p>

			<?php if ( ! empty( $suggestions ) ) : ?>
				<div class="suggested-workflows">
					<h3><?php esc_html_e( 'Suggested Workflows', 'wpshadow' ); ?></h3>
					<p class="suggested-intro"><?php esc_html_e( 'Ready-to-run automations based on your site signals. One click to create.', 'wpshadow' ); ?></p>
					<div class="suggested-grid suggested-workflows-grid">
						<?php foreach ( $suggestions as $suggestion ) : ?>
							<div class="suggested-card" data-trigger="<?php echo esc_attr( $suggestion['trigger'] ); ?>" data-actions='<?php echo esc_attr( wp_json_encode( $suggestion['actions'] ) ); ?>'>
								<div class="suggested-card-header">
									<span class="suggested-icon" data-color="<?php echo esc_attr( $suggestion['color'] ); ?>">
										<span class="dashicons <?php echo esc_attr( $suggestion['icon'] ); ?>"></span>
									</span>
									<div class="suggested-meta">
										<h4><?php echo esc_html( $suggestion['title'] ); ?></h4>
										<span class="suggested-reason"><?php echo esc_html( $suggestion['reason'] ); ?></span>
									</div>
								</div>
								<p class="suggested-description"><?php echo esc_html( $suggestion['description'] ); ?></p>
								<button type="button" class="wps-btn wps-btn-primary create-suggested-workflow" data-title="<?php echo esc_attr( $suggestion['title'] ); ?>" data-label="<?php esc_attr_e( 'Create from suggestion', 'wpshadow' ); ?>">
									<?php esc_html_e( 'Create from suggestion', 'wpshadow' ); ?>
								</button>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<!-- Workflow List -->
		<?php if ( ! empty( $suggestions ) ) : ?>
			<div class="suggested-workflows compact">
				<div class="suggested-header">
					<div>
						<h3><?php esc_html_e( 'Suggested Workflows', 'wpshadow' ); ?></h3>
						<p class="suggested-intro"><?php esc_html_e( 'High-impact automations tuned to your site. Add any with one click.', 'wpshadow' ); ?></p>
					</div>
					<a class="wps-btn wps-btn-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>"><?php esc_html_e( 'Build your own', 'wpshadow' ); ?></a>
				</div>
				<div class="suggested-grid suggested-workflows-grid">
					<?php foreach ( $suggestions as $suggestion ) : ?>
						<div class="suggested-card" data-trigger="<?php echo esc_attr( $suggestion['trigger'] ); ?>" data-actions='<?php echo esc_attr( wp_json_encode( $suggestion['actions'] ) ); ?>'>
							<div class="suggested-card-header">
								<span class="suggested-icon" data-color="<?php echo esc_attr( $suggestion['color'] ); ?>">
									<span class="dashicons <?php echo esc_attr( $suggestion['icon'] ); ?>"></span>
								</span>
								<div class="suggested-meta">
									<h4><?php echo esc_html( $suggestion['title'] ); ?></h4>
									<span class="suggested-reason"><?php echo esc_html( $suggestion['reason'] ); ?></span>
								</div>
							</div>
							<p class="suggested-description"><?php echo esc_html( $suggestion['description'] ); ?></p>
							<button type="button" class="wps-btn wps-btn-secondary wps-btn-sm create-suggested-workflow" data-title="<?php echo esc_attr( $suggestion['title'] ); ?>" data-label="<?php esc_attr_e( 'Add suggestion', 'wpshadow' ); ?>">
								<?php esc_html_e( 'Add suggestion', 'wpshadow' ); ?>
							</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="wpshadow-automations">
			<?php foreach ( $workflows as $workflow ) : ?>
				<?php
				$trigger_label = wpshadow_workflow_get_trigger_summary( $workflow );
				$action_label  = wpshadow_workflow_get_action_summary( $workflow );
				$is_enabled    = ! isset( $workflow['enabled'] ) || $workflow['enabled'];
				$card_class    = $is_enabled ? 'enabled' : 'disabled';
				?>
				<div class="workflow-card <?php echo esc_attr( $card_class ); ?>" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
					<div class="workflow-header">
						<div class="workflow-status">
							<label class="workflow-toggle">
								<input type="checkbox" class="workflow-enable-toggle" <?php checked( $is_enabled ); ?>>
								<span class="toggle-slider"></span>
							</label>
						</div>
						<div class="workflow-info">
							<h3 class="workflow-name"><?php echo esc_html( $workflow['name'] ); ?></h3>
							<p class="workflow-summary">
								<span class="workflow-trigger">
									<span class="dashicons dashicons-clock"></span>
									<?php echo esc_html( $trigger_label ); ?>
								</span>
								<span class="workflow-actions">
									<span class="dashicons dashicons-admin-tools"></span>
									<?php echo esc_html( $action_label ); ?>
								</span>
							</p>
						</div>
					</div>

				<div class="workflow-buttons">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow['id'] ) ); ?>" class="wps-btn wps-btn-primary wps-btn-sm">
							<?php esc_html_e( 'Edit', 'wpshadow' ); ?>
						</a>
						<button type="button" class="wps-btn wps-btn-secondary wps-btn-sm workflow-test-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Test', 'wpshadow' ); ?>
						</button>
						<button type="button" class="wps-btn wps-btn-success wps-btn-sm workflow-run-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
						</button>
						<button type="button" class="wps-btn wps-btn-danger wps-btn-sm workflow-delete-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>
</div>

<?php
/**
 * Get human-readable trigger summary with trigger name or schedule
 *
 * @param array $workflow Workflow data
 * @return string Trigger summary
 */
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

/**
 * Get human-readable action summary with first (and only) action name
 * Note: This version of the plugin supports only one action per trigger
 *
 * @param array $workflow Workflow data
 * @return string Action summary
 */
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