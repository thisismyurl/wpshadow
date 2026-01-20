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

// Get all findings
$all_findings = wpshadow_get_site_findings();

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
		$seed = isset( $finding['title'] ) ? $finding['title'] : ( isset( $finding['description'] ) ? $finding['description'] : '' );
		$finding_id = 'finding-' . md5( $seed );
		$finding['id'] = $finding_id;
	}

	$status = $status_manager->get_finding_status( $finding_id );
	if ( ! $status ) {
		// New findings default to 'detected'
		$status = 'detected';
	}
	$finding['status'] = $status;
	$findings_by_status[ $status ][] = $finding;
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

<div class="wpshadow-kanban-container" id="wpshadow-kanban-board" style="margin: 30px 0;">
	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; margin: 0 0 20px 0;">
		<?php $wpshadow_hide_quick = get_user_meta( get_current_user_id(), 'wpshadow_hide_quick_scan', true ); if ( empty( $wpshadow_hide_quick ) ) : ?>
		<div style="background: #f7fbff; border: 1px solid #d6e9ff; border-radius: 6px; padding: 12px 14px; position: relative;">
			<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
				<div style="flex: 1;">
					<p style="margin: 0 0 8px 0; font-weight: 700; color: #0b5cad;">Quick Scan (low impact)</p>
					<p style="margin: 0; color: #315070; font-size: 13px;">Run the safe checks now: cache-light diagnostics and a single page render. This keeps the board aligned with the latest findings without slowing editors.</p>
				</div>
				<?php endif; ?>
				<button type="button" class="wpshadow-dismiss-scan" data-scan="quick" style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 0; color: #999; flex-shrink: 0; line-height: 1;" title="Dismiss">✕</button>
			</div>
			<div style="display: flex; gap: 10px; align-items: center; margin-top: 10px; flex-wrap: wrap;">
				<button type="button" id="wpshadow-kanban-run-quick" class="button button-primary">Run Quick Scan</button>
				<span style="font-size: 12px; color: #315070;">~15 seconds, safe to run live</span>
				<?php 
			$last_quick = get_option( 'wpshadow_last_quick_checks' );
			if ( !empty( $last_quick ) && (int) $last_quick > 0 ) : ?>
				<span style="font-size: 12px; color: #1e3a8a; font-weight: 600;">Last scanned: <?php echo wp_kses_post( wpshadow_format_time_with_tooltip( (int) $last_quick ) ); ?></span>
				<?php else : ?>
					<span style="font-size: 12px; color: #6b7280;">Last scanned: not yet</span>
				<?php endif; ?>
			</div>
		</div>
		<?php $wpshadow_hide_deep = get_user_meta( get_current_user_id(), 'wpshadow_hide_deep_scan', true ); if ( empty( $wpshadow_hide_deep ) ) : ?>
		<div style="background: #fff8f3; border: 1px solid #ffd7b5; border-radius: 6px; padding: 12px 14px; position: relative;">
			<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
				<div style="flex: 1;">
					<p style="margin: 0 0 8px 0; font-weight: 700; color: #c05c00;">Deep Scan (potential impact)</p>
					<p style="margin: 0; color: #704620; font-size: 13px;">After you reshuffle items, schedule the higher-impact scans (full crawls, performance sweeps) during a quiet window so editors are not interrupted.</p>
				</div>
				<?php endif; ?>
				<button type="button" class="wpshadow-dismiss-scan" data-scan="deep" style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 0; color: #999; flex-shrink: 0; line-height: 1;" title="Dismiss">✕</button>
			</div>
			<div style="display: flex; gap: 10px; align-items: center; margin-top: 10px; flex-wrap: wrap;">
				<button type="button" id="wpshadow-kanban-schedule-deep" class="button button-primary">Schedule Deep Scan (recommended)</button>
				<button type="button" id="wpshadow-kanban-run-deep" class="button">Run Deep Scan</button>
				<?php 
			$last_heavy = get_option( 'wpshadow_last_heavy_tests' );
			if ( !empty( $last_heavy ) && (int) $last_heavy > 0 ) : ?>
				<span style="font-size: 12px; color: #92400e; font-weight: 600;">Last run: <?php echo wp_kses_post( wpshadow_format_time_with_tooltip( (int) $last_heavy ) ); ?></span>
				<?php else : ?>
					<span style="font-size: 12px; color: #6b7280;">Last run: not yet</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div style="margin: 20px 0; padding: 15px 20px; background: #f9fafb; border-left: 4px solid #3b82f6; border-radius: 4px;">
		<h3 style="margin: 0 0 8px 0; color: #1e40af; font-size: 15px; font-weight: 600;">Organize Your Findings</h3>
		<p style="color: #4b5563; margin: 0; font-size: 14px; line-height: 1.5;">
			Drag findings between columns to decide how to handle them. 
			<a href="https://wpshadow.com/kb/kanban-workflow/?utm_source=wpshadow" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 500;">Learn about the workflow →</a>
		</p>
	</div>
	<div id="wpshadow-kanban-status" style="display: none; margin: 0 0 16px 0; padding: 10px 12px; border: 1px solid #dbeafe; background: #eff6ff; color: #0b5cad; border-radius: 6px; font-size: 13px;"></div>
	<?php wp_nonce_field( 'wpshadow_kanban', 'wpshadow_kanban_nonce' ); ?>

	<!-- Workflow Creation Modal -->
	<div id="wpshadow-autofix-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);">
		<div style="background: #fff; margin: 10% auto; padding: 30px; border-radius: 8px; max-width: 500px; position: relative; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
			<button class="wpshadow-autofix-modal-close" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; font-size: 28px; cursor: pointer; color: #999; line-height: 1;">×</button>
			<h2 style="margin-top: 0; color: #4caf50;">
				<span class="dashicons dashicons-update" style="font-size: 28px; width: 28px; height: 28px;"></span>
				Create Workflow
			</h2>
			<p style="color: #555; line-height: 1.6; margin: 15px 0;">
				Create a workflow to automatically handle this issue. Choose how you want it to work:
			</p>
			<div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 15px 0; border-radius: 4px;">
				<p style="margin: 0 0 10px 0; font-weight: 600; color: #1e40af;">
					<strong>Always Auto-fix:</strong>
				</p>
				<p style="margin: 0; font-size: 13px; color: #1e3a8a;">
					Creates a persistent workflow that will automatically fix this issue whenever it's detected. Visible in the Workflow Manager.
				</p>
			</div>
			<div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 4px;">
				<p style="margin: 0 0 10px 0; font-weight: 600; color: #92400e;">
					<strong>Just Once:</strong>
				</p>
				<p style="margin: 0; font-size: 13px; color: #78350f;">
					Fixes this specific issue now. A temporary workflow will run in the background (won't appear in your workflow list).
				</p>
			</div>
			<div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
				<button id="wpshadow-autofix-once" class="button" style="padding: 10px 20px;">Just Once</button>
				<button id="wpshadow-autofix-always" class="button button-primary" style="padding: 10px 20px;">Create Workflow</button>
			</div>
		</div>
	</div>

	

	<div class="wpshadow-kanban-board" style="
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 15px;
		padding: 20px;
		background: #f5f5f5;
		border-radius: 8px;
		min-height: 500px;
	">
		<?php foreach ( array( 'detected', 'manual', 'automated', 'fixed' ) as $status ) : ?>
			<div class="kanban-column" data-status="<?php echo esc_attr( $status ); ?>" style="
				background: white;
				border-radius: 6px;
				padding: 15px;
				min-height: 500px;
				border: 1px solid #e0e0e0;
				overflow-y: auto;
			">
				<h3 style="
					margin-top: 0;
					margin-bottom: 15px;
					color: #333;
					font-size: 13px;
					font-weight: 600;
					text-transform: uppercase;
					letter-spacing: 0.5px;
					padding-bottom: 10px;
					border-bottom: 2px solid <?php echo esc_attr( $status_colors[ $status ] ); ?>;
				">
					<span style="color: <?php echo esc_attr( $status_colors[ $status ] ); ?>;">●</span>
					<?php echo esc_html( $status_labels[ $status ] ); ?>
					<span style="color: #999; font-weight: 400; float: right;">
						<?php echo count( $findings_by_status[ $status ] ); ?>
					</span>
				</h3>

				<div class="kanban-column-content" style="min-height: 400px;">
					<?php if ( $status === 'fixed' ) : // Workflows column - show workflows ?>
						<?php foreach ( $findings_by_status[ $status ] as $workflow_id => $workflow ) : 
							$workflow_name = isset( $workflow['name'] ) ? $workflow['name'] : 'Unnamed Workflow';
							$workflow_enabled = isset( $workflow['enabled'] ) ? $workflow['enabled'] : false;
							$workflow_triggers = isset( $workflow['triggers'] ) ? count( $workflow['triggers'] ) : 0;
							$workflow_actions = isset( $workflow['actions'] ) ? count( $workflow['actions'] ) : 0;
						?>
							<div class="workflow-card" 
								data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>" 
								style="
									background: #f0f9ff;
									border: 1px solid #8bc34a;
									border-left: 4px solid #8bc34a;
									border-radius: 4px;
									padding: 12px;
									margin-bottom: 10px;
									transition: all 0.2s;
								"
								onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
								onmouseout="this.style.boxShadow='none'"
							>
								<!-- Workflow Title -->
								<div style="font-weight: 600; font-size: 13px; margin: 0 0 6px 0; color: #333; display: flex; align-items: center; gap: 8px;">
									<span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; color: #8bc34a;"></span>
									<?php echo esc_html( $workflow_name ); ?>
									<?php if ( ! $workflow_enabled ) : ?>
										<span style="font-size: 10px; padding: 2px 6px; background: #fee; color: #c00; border-radius: 3px;">Disabled</span>
									<?php endif; ?>
								</div>

								<!-- Workflow Stats -->
								<div style="font-size: 11px; color: #666; margin-bottom: 8px;">
									<?php echo (int) $workflow_triggers; ?> trigger<?php echo $workflow_triggers !== 1 ? 's' : ''; ?> • 
									<?php echo (int) $workflow_actions; ?> action<?php echo $workflow_actions !== 1 ? 's' : ''; ?>
								</div>

								<!-- Edit Link -->
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflow-builder&workflow_id=' . $workflow_id ) ); ?>" 
									style="font-size: 12px; color: #2271b1; text-decoration: none;">
									Edit Workflow →
								</a>
							</div>
						<?php endforeach; ?>
					<?php else : // Regular findings columns ?>
					<?php foreach ( $findings_by_status[ $status ] as $finding ) : 
						$threat_level = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
						$threat_label = wpshadow_get_threat_label( $threat_level );
						$threat_color = wpshadow_get_threat_gauge_color( $threat_level );

						// Determine card color based on threat level
						if ( $threat_level >= 75 ) {
							$card_border = '#f44336';
							$card_bg = '#ffebee';
							$card_severity = 'critical';
						} elseif ( $threat_level >= 50 ) {
							$card_border = '#ff9800';
							$card_bg = '#fff3e0';
							$card_severity = 'high';
						} else {
							$card_border = '#2196f3';
							$card_bg = '#e3f2fd';
							$card_severity = 'medium';
						}

						$note = $status_manager->get_finding_note( $finding['id'] );
						$category_key = isset( $finding['category'] ) ? $finding['category'] : 'settings';
						$category = isset( $category_meta[ $category_key ] ) ? $category_meta[ $category_key ] : $category_meta['settings'];
					?>
						<div class="finding-card" 
							data-finding-id="<?php echo esc_attr( $finding['id'] ); ?>" 
							draggable="true"
							style="
								background: <?php echo esc_attr( $card_bg ); ?>;
								border: 1px solid <?php echo esc_attr( $card_border ); ?>;
								border-left: 4px solid <?php echo esc_attr( $card_border ); ?>;
								border-radius: 4px;
								padding: 12px;
								margin-bottom: 10px;
								cursor: move;
								transition: all 0.2s;
								user-select: none;
							"
							onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
							onmouseout="this.style.boxShadow='none'"
						>
					<!-- Title -->
					<div class="finding-title" style="
						font-weight: 600;
						font-size: 13px;
						margin: 0 0 8px 0;
						color: #333;
						line-height: 1.3;
					">
						<?php echo esc_html( $finding['title'] ); ?>
					</div>

					<!-- Description (truncated) -->
					<p style="
						font-size: 12px;
						color: #555;
						margin: 0;
						line-height: 1.4;
					">
						<?php echo esc_html( substr( $finding['description'], 0, 100 ) ); ?>
						<?php if ( strlen( $finding['description'] ) > 100 ) echo '...'; ?>
						<?php if ( ! empty( $finding['kb_link'] ) ) : ?>
							<a href="<?php echo esc_url( $finding['kb_link'] ); ?>" 
								target="_blank" 
								style="color: #2271b1; text-decoration: none; margin-left: 4px;">
								Learn more
							</a>
						<?php endif; ?>
					</p>
					<!-- Status note (if exists) -->
					<?php
							if ( $note ) : 
						?>
							<div style="
								font-size: 11px;
								color: #666;
								background: #fafafa;
								padding: 8px;
								margin-top: 8px;
								border-radius: 3px;
								border-left: 2px solid <?php echo esc_attr( $status_colors[ $status ] ); ?>;
								max-height: 50px;
								overflow: hidden;
								text-overflow: ellipsis;
							">
								<strong style="color: #333;">Note:</strong> <?php echo esc_html( $note ); ?>
							</div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
					<?php endif; // End if fixed (workflows) vs regular findings ?>

					<?php if ( empty( $findings_by_status[ $status ] ) ) : ?>
						<div class="kanban-empty-message" style="
							text-align: center;
							color: #ccc;
							padding: 40px 10px;
							font-size: 12px;
						">
							<?php if ( $status === 'fixed' ) : ?>
								No workflows yet. Drag findings here to create workflows.
							<?php else : ?>
								No findings yet
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
        <div class="wpshadow-kanban-legend" style="
		grid-column: 1 / -1;
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
		margin: 10px 0 18px 0;
		padding: 10px 12px;
		background: #fff;
		border: 1px solid #e6e6e6;
		border-radius: 6px;
	">
		<p style="font-weight: 600; color: #333; font-size: 12px;">Color legend</p>
		<?php foreach ( $severity_legend as $legend ) : ?>
			<p style="display: inline-flex; align-items: center; gap: 8px; font-size: 12px; color: #444;">
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
		<span style="font-size: 12px; color: #666; margin-left: auto;">Column dots match status colors</span>
	</div>
	</div>
    
</div>

<script>
jQuery(document).ready(function($) {
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

			// Save status change via AJAX
			$.post(ajaxurl, {
				action: 'wpshadow_change_finding_status',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_kanban' ); ?>',
				finding_id: findingId,
				new_status: newStatus
			}, function(response) {
				if (response.success) {
					// Move card to new column
					$(draggedElement).css('opacity', '1').detach();
					$column.find('.kanban-column-content').append(draggedElement);
					updateColumnCounts();
				} else {
					alert('Error: ' + (response.data.message || 'Could not update status'));
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
			nonce: '<?php echo wp_create_nonce( 'wpshadow_autofix' ); ?>',
			finding_id: findingId
		}, function(response) {
			if (response.success) {
				const $card = $btn.closest('.finding-card');
				$card.html(
					'<div style="padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px; text-align: center;">' +
					'<strong style="color: #2e7d32;">✓ Fixed!</strong>' +
					'<p style="margin: 8px 0 0 0; font-size: 12px; color: #555;">' + response.data.message + '</p>' +
					'</div>'
				);
				setTimeout(function() {
					$card.closest('.kanban-column').find('.kanban-column-content').append(
						$card.detach().attr('data-finding-id', findingId).fadeIn()
					);
					updateColumnCounts();
				}, 1500);
			} else {
				alert('Error: ' + (response.data.message || 'Could not auto-fix'));
				$btn.prop('disabled', false).text('Fix Now');
			}
		});
	});

	// Details modal (placeholder)
	$(document).on('click', '.finding-details', function(e) {
		e.preventDefault();
		const findingId = $(this).data('finding-id');
		alert('Finding details modal: ' + findingId); // TODO: Implement details modal
	});

	// Update column counts
	function updateColumnCounts() {
		$('.kanban-column').each(function() {
			const count = $(this).find('.finding-card').length;
			$(this).find('h3').find('span:last').text(count);
		});
	}
});
</script>
