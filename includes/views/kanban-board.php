<?php

/**
 * Kanban Board UI for organizing findings by status.
 * Displays findings in 4 columns: Detected, Manual, Automated, Fixed
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get status manager
$status_manager = new \WPShadow\Core\Finding_Status_Manager();

// Get all findings from diagnostic registry
$diagnostic_registry = \WPShadow\Diagnostics\Diagnostic_Registry::class;
$all_findings        = method_exists( $diagnostic_registry, 'run_enabled_scan' ) ? $diagnostic_registry::run_enabled_scan() : array();

// Apply category filter if present (Issue #564)
$kanban_category = isset( $_GET['kanban_category'] ) ? sanitize_key( $_GET['kanban_category'] ) : '';
if ( ! empty( $kanban_category ) ) {
	$all_findings = array_filter(
		$all_findings,
		function ( $f ) use ( $kanban_category ) {
			return isset( $f['category'] ) && $f['category'] === $kanban_category;
		}
	);
}

// Get all workflows for the Workflows column
if ( class_exists( '\WPShadow\Workflow\Workflow_Manager' ) ) {
	$workflows = \WPShadow\Workflow\Workflow_Manager::get_workflows();
} else {
	$workflows = array();
}

// Organize findings by status
$findings_by_status = array(
	'detected'  => array(),
	'manual'    => array(),
	'automated' => array(),
	'fixed'     => $workflows, // Workflows column shows workflows instead of findings
);

foreach ( $all_findings as $finding ) {
	$finding_id = isset( $finding['id'] ) ? $finding['id'] : '';
	if ( empty( $finding_id ) ) {
		// Generate a stable fallback ID from title/description to avoid undefined index notices
		$seed          = isset( $finding['title'] ) ? $finding['title'] : ( isset( $finding['description'] ) ? $finding['description'] : '' );
		$finding_id    = 'finding-' . md5( $seed );
		$finding['id'] = $finding_id;
	}

	$finding_status = $status_manager->get_finding_status( $finding_id );
	if ( ! $finding_status ) {
		// New findings default to 'detected'
		$finding_status = 'detected';
	}
	$finding['status'] = $finding_status;

	// Add to appropriate column (skip if workflow status - those are handled separately)
	if ( isset( $findings_by_status[ $finding_status ] ) && 'fixed' !== $finding_status ) {
		$findings_by_status[ $finding_status ][] = $finding;
	}
}

// Sort DETECTED column by priority (threat_level DESC) and limit to 10 visible
if ( ! empty( $findings_by_status['detected'] ) ) {
	usort(
		$findings_by_status['detected'],
		function ( $a, $b ) {
			$threat_a = isset( $a['threat_level'] ) ? (int) $a['threat_level'] : 50;
			$threat_b = isset( $b['threat_level'] ) ? (int) $b['threat_level'] : 50;
			return $threat_b - $threat_a; // Descending order (highest priority first)
		}
	);
}

$status_labels = array(
	'detected'  => 'Detected',
	'manual'    => 'User to Fix',
	'automated' => 'Fix Now',
	'fixed'     => 'Workflows',
);

$status_colors = array(
	'detected'  => '#2196f3', // Blue
	'manual'    => '#ff9800', // Orange
	'automated' => '#4caf50', // Green
	'fixed'     => '#8bc34a', // Light green
);

$category_meta = array(
	'seo'      => array(
		'label' => 'SEO',
		'icon'  => 'dashicons-search',
		'color' => '#2563eb',
		'bg'    => '#e7f1ff',
	),
	'design'   => array(
		'label' => 'Design',
		'icon'  => 'dashicons-admin-appearance',
		'color' => '#8e44ad',
		'bg'    => '#f2e9fb',
	),
	'settings' => array(
		'label' => 'Settings',
		'icon'  => 'dashicons-admin-generic',
		'color' => '#4b5563',
		'bg'    => '#eef2f7',
	),
	'mobile'   => array(
		'label' => 'Mobile',
		'icon'  => 'dashicons-smartphone',
		'color' => '#009688',
		'bg'    => '#e0f2f1',
	),
);

$severity_legend = array(
	'critical' => array(
		'label' => 'Critical (75+)',
		'color' => '#f44336',
		'bg'    => '#ffebee',
	),
	'high'     => array(
		'label' => 'High (50-74)',
		'color' => '#ff9800',
		'bg'    => '#fff3e0',
	),
	'medium'   => array(
		'label' => 'Moderate (<50)',
		'color' => '#2196f3',
		'bg'    => '#e3f2fd',
	),
);
?>

<div class="wpshadow-kanban-container wps-m-30" id="wpshadow-kanban-board">
	<div class="wps-alert wps-alert--info wps-mb-6">
		<div class="wps-flex wps-items-start wps-gap-4">
			<span class="dashicons dashicons-info wps-text-primary" class="wps-icon-lg wps-icon-info" aria-hidden="true"></span>
			<div class="wps-flex-1">
				<h3 class="wps-m-0 wps-mb-2 wps-text-lg wps-font-bold">
					<?php esc_html_e( 'Organize Your Findings', 'wpshadow' ); ?>
					<small class="wps-text-gray-500 wps-font-normal">(v<?php echo esc_html( WPSHADOW_VERSION ); ?>)</small>
				</h3>
				<p class="wps-m-0 wps-leading-relaxed">
					<?php esc_html_e( 'Drag findings between columns to decide how to handle them. Use your keyboard (Enter/Space) for accessibility.', 'wpshadow' ); ?>
					<a href="https://wpshadow.com/kb/kanban-workflow/?utm_source=wpshadow" target="_blank" class="wps-text-primary wps-font-semibold wps-no-underline wps-ml-1" aria-label="<?php esc_attr_e( 'Learn about the Kanban workflow (opens in new tab)', 'wpshadow' ); ?>">
						<?php esc_html_e( 'Learn about the workflow', 'wpshadow' ); ?> →
					</a>
				</p>
			</div>
		</div>
	</div>
	<div id="wpshadow-kanban-status" class="wps-none wps-m-0 wps-p-3 wps-rounded-md" role="status" aria-live="polite" aria-atomic="true"></div>
	<?php wp_nonce_field( 'wpshadow_kanban', 'wpshadow_kanban_nonce' ); ?>

	<!-- Workflow Creation Modal -->
	<div id="wpshadow-autofix-modal" class="wps-none">
		<div class="wps-m-3 wps-p-8 wps-rounded-lg">
			<button class="wpshadow-autofix-modal-close wps-absolute wps-top-4 wps-right-4 wps-bg-transparent wps-border-none wps-text-4xl wps-cursor-pointer wps-text-gray-400 wps-leading-none">×</button>
			<h2 class="wps-mt-0 wps-text-success wps-flex wps-items-center wps-gap-2">
				<span class="dashicons dashicons-update" class="wps-icon-lg"></span>
				Create Workflow
			</h2>
			<p class="wps-m-15">
				Create a workflow to automatically handle this issue. Choose how you want it to work:
			</p>
			<div class="wps-m-4 wps-p-4 wps-rounded">
				<p class="wps-m-0">
					<strong>Always Auto-fix:</strong>
				</p>
				<p class="wps-m-0">
					Creates a persistent workflow that will automatically fix this issue whenever it's detected. Visible in the Workflow Manager.
				</p>
			</div>
			<div class="wps-m-4 wps-p-4 wps-rounded">
				<p class="wps-m-0">
					<strong>Just Once:</strong>
				</p>
				<p class="wps-m-0">
					Fixes this specific issue now. A temporary workflow will run in the background (won't appear in your workflow list).
				</p>
			</div>
			<div class="wps-flex wps-gap-3 wps-justify-end">
				<button type="button" class="wps-btn wps-btn--secondary wps-p-3" id="wpshadow-autofix-once">Just Once</button>
				<button type="button" class="wps-btn wps-btn--primary wps-p-3" id="wpshadow-autofix-always">Create Workflow</button>
			</div>
		</div>
	</div>

	<!-- Workflow Creation Modal -->
	<div id="wpshadow-workflow-creation-modal" class="wps-none">
		<div class="wps-m-3 wps-p-8 wps-rounded-lg">
			<button class="wpshadow-workflow-modal-close wps-absolute wps-top-4 wps-right-4 wps-bg-transparent wps-border-none wps-text-4xl wps-cursor-pointer wps-text-gray-400 wps-leading-none">×</button>
			<h2 class="wps-mt-0 wps-text-primary wps-flex wps-items-center wps-gap-2">
				<span class="dashicons dashicons-update" class="wps-icon-lg"></span>
				Create Workflow
			</h2>

			<!-- Finding Details -->
			<div class="wps-m-4 wps-p-4 wps-rounded">
				<p class="wps-m-0">
					<span class="dashicons dashicons-yes-alt" class="wps-icon-md"></span>
					Finding:
				</p>
				<p class="wps-m-0 workflow-finding-title"></p>
				<p class="wps-m-0 workflow-finding-desc"></p>
			</div>

			<!-- Workflow Name -->
			<div class="wps-m-5">
				<label class="wps-block">
					<?php esc_html_e( 'Workflow Name', 'wpshadow' ); ?>
				</label>
				<input type="text" id="wpshadow-workflow-name" placeholder="e.g., Clear cache daily" class="wps-p-3 wps-rounded">
			</div>

			<!-- Workflow Type Selection -->
			<div class="wps-m-5">
				<p class="wps-m-0"><?php esc_html_e( 'How should this workflow work?', 'wpshadow' ); ?></p>

				<!-- Option 1: Always Auto-fix -->
				<div class="wps-p-4 wps-rounded-md">
					<label class="wps-flex wps-gap-3 wps-items-center wps-m-0">
						<input type="radio" name="workflow_type" value="auto_fix" checked class="wps-cursor-pointer">
						<div>
							<strong class="wps-text-sm wps-text-success"><?php esc_html_e( '✓ Always Auto-fix', 'wpshadow' ); ?></strong>
							<p class="wps-m-1">
								<?php esc_html_e( 'Creates an ongoing workflow that will automatically fix this issue whenever Guardian detects it.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<!-- Option 2: Reactive (Alert + Manual Fix) -->
				<div class="wps-p-4 wps-rounded-md">
					<label class="wps-flex wps-gap-3 wps-items-center wps-m-0">
						<input type="radio" name="workflow_type" value="reactive" class="wps-cursor-pointer">
						<div>
							<strong class="wps-text-sm wps-text-warning"><?php esc_html_e( '🔔 Alert & Track', 'wpshadow' ); ?></strong>
							<p class="wps-m-1">
								<?php esc_html_e( 'Send an alert when Guardian detects this issue, but don\'t auto-fix. You\'ll fix it yourself.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<!-- Option 3: Scheduled -->
				<div class="wps-p-4 wps-rounded-md">
					<label class="wps-flex wps-gap-3 wps-items-center wps-m-0">
						<input type="radio" name="workflow_type" value="scheduled" class="wps-cursor-pointer">
						<div>
							<strong class="wps-text-sm wps-text-primary"><?php esc_html_e( '⏰ On Schedule', 'wpshadow' ); ?></strong>
							<p class="wps-m-1">
								<?php esc_html_e( 'Run this workflow on a regular schedule (e.g., daily maintenance task).', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>
			</div>

			<!-- Info Box -->
			<div class="wps-m-5 wps-p-3 wps-rounded-md">
				<strong><?php esc_html_e( '💡 Tip:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'After creating the workflow, you\'ll be able to customize triggers, actions, and schedule from the Workflow Manager.', 'wpshadow' ); ?>
			</div>

			<!-- Action Buttons -->
			<div class="wps-flex wps-gap-3 wps-justify-end">
				<button type="button" id="wpshadow-workflow-modal-cancel" class="wps-btn wps-btn--secondary wps-p-3">
					<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
				</button>
				<button type="button" id="wpshadow-workflow-modal-create" class="wps-btn wps-btn--primary wps-btn-icon-left wps-p-3">
					<span class="dashicons dashicons-update" class="wps-icon-md"></span>
					<?php esc_html_e( 'Create & Configure', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Family-Aware Fix Modal (Philosophy #9: Show Value) -->
	<div id="wpshadow-family-fix-modal" class="wps-none">
		<div class="wps-m-3 wps-p-8 wps-rounded-lg">
			<button class="wpshadow-family-fix-modal-close wps-absolute wps-top-4 wps-right-4 wps-bg-transparent wps-border-none wps-text-4xl wps-cursor-pointer wps-text-gray-400 wps-leading-none">×</button>
			<h2 class="wps-mt-0 wps-text-primary wps-flex wps-items-center wps-gap-2">
				<span class="dashicons dashicons-groups" class="wps-icon-lg"></span>
				Fix Related Issues
			</h2>
			<p class="wps-m-4">
				WPShadow found <strong><span class="family-count">2</span> related issues</strong> in the <strong><span class="family-title">Same Family</span></strong>. You can fix them all at once to save time!
			</p>
			<div class="wps-m-4 wps-p-4 wps-rounded">
				<p class="wps-m-0">
					Related issues in this family:
				</p>
				<ul class="family-list wps-m-0">
					<!-- Populated by JavaScript -->
				</ul>
			</div>
			<div class="wps-m-4 wps-p-4 wps-rounded">
				<p class="wps-m-0">
					<strong>💡 Time-Saving Tip:</strong><br>
					Fixing all related issues at once can save you significant time. <a href="https://wpshadow.com/kb/family-grouped-fixes/?utm_source=wpshadow" target="_blank" class="wps-text-primary wps-no-underline">Learn more →</a>
				</p>
			</div>
			<div class="wps-flex wps-gap-3 wps-justify-end">
				<button type="button" id="wpshadow-family-fix-this-only" class="wps-btn wps-btn--secondary wps-p-3">Fix This Only</button>
				<button type="button" id="wpshadow-family-fix-all" class="wps-btn wps-btn--primary wps-p-3">Fix All Related Issues</button>
			</div>
		</div>
	</div>



	<div class="wpshadow-kanban-board wps-grid wps-p-5 wps-rounded-lg">
		<?php foreach ( array( 'detected', 'manual', 'automated', 'fixed' ) as $column_status ) : ?>
			<div class="kanban-column" data-status="<?php echo esc_attr( $column_status ); ?>" role="region" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: Column status label */ __( '%s findings column', 'wpshadow' ), $status_labels[ $column_status ] ) ); ?>">
				<div class="wps-kanban-column-header">
					<h3 class="wps-kanban-column-title">
						<?php echo esc_html( $status_labels[ $column_status ] ); ?>
					</h3>
					<span class="wps-kanban-count-badge column-count" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: Number of items in the column */ __( '%d items', 'wpshadow' ), count( $findings_by_status[ $column_status ] ) ) ); ?>">
						<?php echo count( $findings_by_status[ $column_status ] ); ?>
					</span>
				</div>

				<div class="kanban-column-content">
					<?php
					if ( 'fixed' === $column_status ) : // Workflows column - show workflows
						?>
						<?php
						foreach ( $findings_by_status[ $column_status ] as $workflow_id => $workflow ) :
							$workflow_name     = isset( $workflow['name'] ) ? $workflow['name'] : 'Unnamed Workflow';
							$workflow_enabled  = isset( $workflow['enabled'] ) ? $workflow['enabled'] : false;
							$workflow_triggers = isset( $workflow['triggers'] ) ? count( $workflow['triggers'] ) : 0;
							$workflow_actions  = isset( $workflow['actions'] ) ? count( $workflow['actions'] ) : 0;
							?>
							<div class="workflow-card"
								data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>"
								role="article"
								aria-label="<?php echo esc_attr( sprintf( __( 'Workflow: %s', 'wpshadow' ), $workflow_name ) ); ?>">
								<!-- Workflow Title -->
						<div class="wps-flex wps-gap-2 wps-items-center wps-m-0">
							<span class="dashicons dashicons-update" class="wps-icon-md" aria-hidden="true"></span>
							<strong><?php echo esc_html( $workflow_name ); ?></strong>
							<?php if ( ! $workflow_enabled ) : ?>
								<span class="wps-p-1 wps-rounded-sm"><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></span>
									<?php endif; ?>
								</div>

								<!-- Workflow Stats -->
								<div class="wps-activity-meta">
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: number of triggers, 2: number of actions */
											__( '%1$d trigger%2$s • %3$d action%4$s', 'wpshadow' ),
											(int) $workflow_triggers,
											$workflow_triggers !== 1 ? 's' : '',
											(int) $workflow_actions,
											$workflow_actions !== 1 ? 's' : ''
										)
									);
									?>
								<div class="wps-activity-meta">
									<?php echo (int) $workflow_triggers; ?> trigger<?php echo 1 !== $workflow_triggers ? 's' : ''; ?> •
									<?php echo (int) $workflow_actions; ?> action<?php echo 1 !== $workflow_actions ? 's' : ''; ?>
								</div>

								<!-- Edit Link -->
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflow-builder&workflow_id=' . $workflow_id ) ); ?>"
									style="font-size: 12px; color: var(--wps-info); text-decoration: none;">
									<?php esc_html_e( 'Edit Workflow', 'wpshadow' ); ?> →
								</a>
							</div>
						<?php endforeach; ?>
						<?php
					else : // Regular findings columns
						?>
						<?php
						$findings       = $findings_by_status[ $column_status ];
						$total_findings = count( $findings );
						foreach ( $findings as $idx => $finding ) :
							// For DETECTED column, only show first 10 items
							$is_hidden    = ( 'detected' === $column_status && $idx >= 10 );
							$hidden_class = $is_hidden ? ' wpshadow-hidden-finding' : '';

							$threat_level = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
							$threat_label = wpshadow_get_threat_label( $threat_level );
							$threat_color = wpshadow_get_threat_gauge_color( $threat_level );

							// Determine card color based on threat level
							if ( $threat_level >= 75 ) {
								$card_border   = '#f44336';
								$card_bg       = '#ffebee';
								$card_severity = 'critical';
							} elseif ( $threat_level >= 50 ) {
								$card_border   = '#ff9800';
								$card_bg       = '#fff3e0';
								$card_severity = 'high';
							} else {
								$card_border   = '#2196f3';
								$card_bg       = '#e3f2fd';
								$card_severity = 'medium';
							}

							$note         = $status_manager->get_finding_note( $finding['id'] );
							$category_key = isset( $finding['category'] ) ? $finding['category'] : 'settings';
							$category     = isset( $category_meta[ $category_key ] ) ? $category_meta[ $category_key ] : $category_meta['settings'];

							// Smart Action Status (Issue #567)
							$smart_status = '';
							$smart_icon   = '';
							$smart_color  = '';

							if ( 'manual' === $column_status ) {
								$manual_fixes = get_option( 'wpshadow_manual_fixes', array() );
								if ( isset( $manual_fixes[ $finding['id'] ] ) ) {
									$smart_status = __( 'Manual fix assigned', 'wpshadow' );
									$smart_icon   = '👤';
									$smart_color  = '#ff9800';
								}
							} elseif ( 'automated' === $column_status ) {
								$automated = get_option( 'wpshadow_scheduled_automated_fixes', array() );
								if ( isset( $automated[ $finding['id'] ] ) ) {
									$auto_status = $automated[ $finding['id'] ]['status'];
									if ( 'pending' === $auto_status ) {
										$smart_status = __( 'Fix scheduled', 'wpshadow' );
										$smart_icon   = '⏱️';
										$smart_color  = '#2196f3';
									} elseif ( 'completed' === $auto_status ) {
										$smart_status = __( 'Fix completed', 'wpshadow' );
										$smart_icon   = '✅';
										$smart_color  = '#4caf50';
									} elseif ( 'failed' === $auto_status ) {
										$smart_status = __( 'Fix failed', 'wpshadow' );
										$smart_icon   = '⚠️';
										$smart_color  = '#f44336';
									}
								}
							}
							?>
						<div class="finding-card <?php echo esc_attr( $card_severity . $hidden_class ); ?>"
							data-finding-id="<?php echo esc_attr( $finding['id'] ); ?>"
							data-category="<?php echo esc_attr( $category_key ); ?>"
							draggable="true"
							tabindex="0"
							role="article"
							aria-label="<?php echo esc_attr( sprintf( /* translators: 1: Finding title, 2: Threat level label */ __( '%1$s - Threat level: %2$s', 'wpshadow' ), $finding['title'], $threat_label ) ); ?>"
							<?php
							if ( $is_hidden ) {
								echo 'style="display: none;"';
							}
							?>
						>
							<!-- Card Header -->
							<div class="finding-card-header">
								<h4 class="finding-card-title finding-title">
									<?php echo esc_html( $finding['title'] ); ?>
								</h4>
								<span class="finding-card-severity finding-threat <?php echo esc_attr( $card_severity ); ?>" data-level="<?php echo esc_attr( $card_severity ); ?>">
									<?php echo esc_html( ucfirst( $card_severity ) ); ?>
								</span>
							</div>

							<!-- Card Body / Description -->
							<p class="finding-card-description finding-description">
								<?php
								echo esc_html( substr( $finding['description'], 0, 150 ) );
								if ( strlen( $finding['description'] ) > 150 ) {
									echo '...';
								}
								?>
							</p>

							<!-- Smart Action Status Badge (Issue #567) -->
							<?php if ( ! empty( $smart_status ) ) : ?>
								<div style="margin: 8px 0;">
									<span style="display: inline-block; font-size: 0.75rem; padding: 4px 8px; border-radius: 12px; background: <?php echo esc_attr( $smart_color ); ?>20; color: <?php echo esc_attr( $smart_color ); ?>; font-weight: 500;" title="<?php echo esc_attr( $smart_status ); ?>">
										<?php echo esc_html( $smart_icon . ' ' . $smart_status ); ?>
									</span>
								</div>
							<?php endif; ?>

							<!-- Card Footer with Actions -->
							<div class="finding-card-footer">
								<div class="finding-card-actions finding-actions">
									<?php if ( ! empty( $finding['kb_link'] ) ) : ?>
										<a href="<?php echo esc_url( $finding['kb_link'] ); ?>" 
											target="_blank" 
											class="button button-small"
											aria-label="<?php esc_attr_e( 'Learn more in knowledge base', 'wpshadow' ); ?>">
											<?php esc_html_e( 'Learn more', 'wpshadow' ); ?>
										</a>
									<?php endif; ?>
									<?php if ( ! empty( $finding['training_link'] ) ) : ?>
										<a href="<?php echo esc_url( $finding['training_link'] ); ?>" 
											target="_blank" 
											class="button button-small"
											aria-label="<?php esc_attr_e( 'Watch training video', 'wpshadow' ); ?>">
											<?php esc_html_e( 'Watch training', 'wpshadow' ); ?>
										</a>
									<?php endif; ?>
									<?php if ( ! empty( $finding['auto_fixable'] ) && $finding['auto_fixable'] ) : ?>
										<button class="wps-btn-sm primary finding-autofix" data-finding-id="<?php echo esc_attr( $finding['id'] ); ?>">
											<?php esc_html_e( 'Auto-Fix', 'wpshadow' ); ?>
										</button>
									<?php endif; ?>
								</div>
							</div>

							<!-- Status note (if exists) -->
							<?php if ( $note ) : ?>
								<div class="finding-note">
									<strong><?php esc_html_e( 'Note:', 'wpshadow' ); ?></strong> <?php echo esc_html( $note ); ?>
								</div>
							<?php endif; ?>

							<!-- Keyboard Navigation Hint (appears on focus) -->
							<span class="wps-keyboard-hint" aria-hidden="true">
								<?php esc_html_e( 'Press Enter to move', 'wpshadow' ); ?>
							</span>
						</div>
							<?php endforeach; ?>
						<?php
					endif; // End if fixed (workflows) vs regular findings
					?>

				<?php if ( empty( $findings_by_status[ $column_status ] ) ) : ?>
						<div class="kanban-column-empty kanban-empty-message">
							<?php if ( $column_status === 'fixed' ) : ?>
								<?php esc_html_e( 'No workflows yet. Drag findings here to create workflows.', 'wpshadow' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'No findings yet', 'wpshadow' ); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="wpshadow-kanban-legend" class="wps-flex-gap-12-items-center-m-10-p-10-round">
			<p class="wps-kanban-legend-label">Color legend</p>
			<?php foreach ( $severity_legend as $legend ) : ?>
				<p class="wps-inline-flex">
					<span style="
					display: inline-block;
					width: 12px;
					height: 12px;
					border-radius: 3px;
					background: <?php echo esc_attr( $legend['bg'] ); ?>;
					border: 1px solid <?php echo esc_attr( $legend['color'] ); ?>;
				"></span>
					<span><?php echo esc_html( $legend['label'] ); ?></span>
				</p>
			<?php endforeach; ?>
			<span class="wps-kanban-status-text" style="margin-left: auto;">Column dots match status colors</span>
		</div>
	</div>

</div>

<script>
	jQuery(document).ready(function($) {
		// Add CSS for hidden findings
		$('<style>')
			.text('.wpshadow-hidden-finding { display: none !important; }')
			.appendTo('head');

		// Initialize column counts on page load
		updateColumnCounts();

		// Drag and drop functionality
		let draggedElement = null;

		// Drag start
		$(document).on('dragstart', '.finding-card', function(e) {
			draggedElement = this;
			$(this).css('opacity', '0.5');
			e.originalEvent.dataTransfer.effectAllowed = 'move';
			e.originalEvent.dataTransfer.setData('text/html', this.innerHTML);
		});

		// Drag over column
		$(document).on('dragover', '.kanban-column-content', function(e) {
			e.preventDefault();
			e.originalEvent.dataTransfer.dropEffect = 'move';
			$(this).closest('.kanban-column').css('background-color', '#f9f9f9');
		});

		// Drag leave
		$(document).on('dragleave', '.kanban-column-content', function(e) {
			$(this).closest('.kanban-column').css('background-color', '#fff');
		});

		// Drop
		$(document).on('drop', '.kanban-column-content', function(e) {
			e.preventDefault();
			if (draggedElement) {
				const $column = $(this).closest('.kanban-column');
				const newStatus = $column.data('status');
				const $card = $(draggedElement);
				const findingId = $card.data('finding-id');

				// If dropping to workflow column, show workflow creation modal
				if (newStatus === 'workflow') {
					$card.css('opacity', '1');
					const findingTitle = $card.find('.finding-title').text();
					const findingDesc = $card.find('.finding-description').text();
					const category = $card.data('category');

					// Show workflow creation modal
					const $modal = $('#wpshadow-workflow-creation-modal');
					$modal.data('finding-id', findingId);
					$modal.data('finding-title', findingTitle);
					$modal.data('finding-category', category);
					$modal.find('.workflow-finding-title').text(findingTitle);
					$modal.find('.workflow-finding-desc').text(findingDesc);
					$modal.find('#wpshadow-workflow-name').val(findingTitle);
					$modal.fadeIn(300);
					return; // Don't save status until user confirms
				}

				// Save status change via AJAX for non-workflow drops
				$.post(ajaxurl, {
					action: 'wpshadow_change_finding_status',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_kanban' ) ); ?>',
					finding_id: findingId,
					new_status: newStatus
				}, function(response) {
					if (response.success) {
						// Move card to new column
						const $oldColumn = $(draggedElement).closest('.kanban-column');
						const oldStatus = $oldColumn.data('status');

						$(draggedElement).css('opacity', '1').detach();
						$column.find('.kanban-column-content').append(draggedElement);

						// Auto-fill DETECTED column: show next hidden item if item was moved out
						if (oldStatus === 'detected') {
							const $detectedColumn = $('.kanban-column[data-status="detected"]');
							const $hiddenItems = $detectedColumn.find('.wpshadow-hidden-finding');
							if ($hiddenItems.length > 0) {
								$hiddenItems.first().removeClass('wpshadow-hidden-finding').fadeIn(300);
							}
						}

						updateColumnCounts();
					} else {
						WPShadowModal.alert({
							title: '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>',
							message: 'Error: ' + (response.data.message || 'Could not update status'),
							type: 'error'
						});
						$(draggedElement).css('opacity', '1');
					}
				});
			}
		});

		// Drag end
		$(document).on('dragend', '.finding-card', function(e) {
			$(this).css('opacity', '1');
			$('.kanban-column').css('background-color', '#fff');
		});

		// Auto-fix from card
		$(document).on('click', '.finding-autofix', function(e) {
			e.preventDefault();
			const findingId = $(this).data('finding-id');
			const $btn = $(this);

			$btn.prop('disabled', true).text('Fixing...');

			$.post(ajaxurl, {
				action: 'wpshadow_autofix_finding',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_autofix' ) ); ?>',
				finding_id: findingId
			}, function(response) {
				if (response.success) {
					const $card = $btn.closest('.finding-card');
					$card.html(
					'<div class="wps-p-4 wps-rounded">' +
					'<strong style="color: #2e7d32;">✓ Fixed!</strong>' +
					'<p class="wps-m-2">' + response.data.message + '</p>' +
						'</div>'
					);
					setTimeout(function() {
						$card.closest('.kanban-column').find('.kanban-column-content').append(
							$card.detach().attr('data-finding-id', findingId).fadeIn()
						);
						updateColumnCounts();
					}, 1500);
			} else {
				WPShadowModal.alert({
					title: '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>',
					message: 'Error: ' + (response.data.message || 'Could not auto-fix'),
					type: 'error'
				});
				$btn.prop('disabled', false).text('Fix Now');
			}
		});
	});
		// Workflow Creation Modal
		$(document).on('click', '.wpshadow-create-workflow-btn', function(e) {
			e.preventDefault();
			const findingId = $(this).data('finding-id');
			const $card = $(this).closest('.finding-card');
			const findingTitle = $card.find('.finding-title').text();
			const findingDesc = $card.find('.finding-description').text();
			const category = $card.data('category');

			// Show workflow creation modal
			const $modal = $('#wpshadow-workflow-creation-modal');
			$modal.data('finding-id', findingId);
			$modal.data('finding-title', findingTitle);
			$modal.data('finding-category', category);
			$modal.find('.workflow-finding-title').text(findingTitle);
			$modal.find('.workflow-finding-desc').text(findingDesc);
			$modal.fadeIn(300);
		});

		// Close workflow modal
		$(document).on('click', '.wpshadow-workflow-modal-close, #wpshadow-workflow-modal-cancel', function(e) {
			e.preventDefault();
			$('#wpshadow-workflow-creation-modal').fadeOut(200);
		});

		// Create workflow from finding
		$(document).on('click', '#wpshadow-workflow-modal-create', function(e) {
			e.preventDefault();
			const $modal = $('#wpshadow-workflow-creation-modal');
			const findingId = $modal.data('finding-id');
			const findingTitle = $modal.data('finding-title');
			const category = $modal.data('finding-category');
			const workflowName = $modal.find('#wpshadow-workflow-name').val() || findingTitle;
			const workflowType = $modal.find('input[name="workflow_type"]:checked').val();

			const $btn = $(this);
			$btn.prop('disabled', true).text('Creating...');

			$.post(ajaxurl, {
				action: 'wpshadow_create_workflow_from_finding',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_create_workflow' ) ); ?>',
				finding_id: findingId,
				workflow_name: workflowName,
				workflow_type: workflowType,
				category: category
			}, function(response) {
				if (response.success) {
					const workflowId = response.data.workflow_id;

					// Redirect to workflow builder with pre-filled data
					window.location.href = ajaxurl.replace('admin-ajax.php', '') + 'admin.php?page=wpshadow-workflows&workflow_id=' + workflowId + '&new=1';
				} else {
				WPShadowModal.alert({
					title: '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>',
					message: 'Error: ' + (response.data.message || 'Could not create workflow'),
					type: 'error'
				});
				$btn.prop('disabled', false).text('Create & Configure');
			}
		});
	});
		// Just close modal button
		$(document).on('click', '#wpshadow-workflow-modal-cancel', function(e) {
			e.preventDefault();
			$('#wpshadow-workflow-creation-modal').fadeOut(200);
		});

		// Details modal (placeholder)
		$(document).on('click', '.finding-details', function(e) {
			e.preventDefault();
			const findingId = $(this).data('finding-id');
			WPShadowModal.alert({
				title: '<?php echo esc_js( __( 'Finding Details', 'wpshadow' ) ); ?>',
				message: 'Finding details modal for: ' + findingId,
				type: 'info'
			}); // TODO: Implement details modal
		});

		// Update column counts (only count visible items)
		function updateColumnCounts() {
			$('.kanban-column').each(function() {
				const $column = $(this);
				const status = $column.data('status');
				let count;

				if (status === 'detected') {
					// For detected column, show visible count / total count
					// Only count finding-card elements directly in this column's kanban-column-content
					const $content = $column.find('.kanban-column-content');
					const visibleCount = $content.find('> .finding-card:not(.wpshadow-hidden-finding)').length;
					const totalCount = $content.find('> .finding-card').length;
					count = visibleCount;
					if (totalCount > visibleCount) {
						count = visibleCount + ' / ' + totalCount;
					}
				} else {
					// Other columns show total count
					// Only count finding-card elements directly in this column's kanban-column-content
					const $content = $column.find('.kanban-column-content');
					count = $content.find('> .finding-card').length;
				}

				$column.find('h3').find('span:last').text(count);
			});
		}
	});
</script>