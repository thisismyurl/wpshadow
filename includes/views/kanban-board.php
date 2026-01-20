<?php
/**
 * Kanban Board UI for organizing findings by status.
 * Displays findings in 5 columns: Detected, Ignore, Manual, Automated, Fixed
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

// Organize findings by status
$findings_by_status = array(
	'detected'  => array(),
	'ignored'   => array(),
	'manual'    => array(),
	'automated' => array(),
	'fixed'     => array(),
);

foreach ( $all_findings as $finding ) {
	$status = $status_manager->get_finding_status( $finding['id'] );
	if ( ! $status ) {
		// New findings default to 'detected'
		$status = 'detected';
	}
	$finding['status'] = $status;
	$findings_by_status[ $status ][] = $finding;
}

$status_labels = array(
	'detected'  => 'Detected',
	'ignored'   => 'Ignore',
	'manual'    => 'Manual Fix',
	'automated' => 'Auto-fix',
	'fixed'     => 'Fixed',
);

$status_colors = array(
	'detected'  => '#2196f3', // Blue
	'ignored'   => '#9e9e9e', // Gray
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

<div class="wpshadow-kanban-container" style="margin: 30px 0;">
	<h2 style="margin-top: 0;">Organize Your Findings</h2>
	<p style="color: #666; margin: 0 0 20px 0;">
		Drag findings between columns to decide how to handle them. 
		<a href="https://wpshadow.com/kb/kanban-workflow/?utm_source=wpshadow" target="_blank">Learn about the workflow</a>
	</p>
	<?php wp_nonce_field( 'wpshadow_kanban', 'wpshadow_kanban_nonce' ); ?>

	

	<div class="wpshadow-kanban-board" style="
		display: grid;
		grid-template-columns: repeat(5, 1fr);
		gap: 15px;
		padding: 20px;
		background: #f5f5f5;
		border-radius: 8px;
		min-height: 500px;
	">
		<?php foreach ( array( 'detected', 'ignored', 'manual', 'automated', 'fixed' ) as $status ) : ?>
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
					<!-- Meta -->
					<div class="finding-meta" style="
						display: flex;
						align-items: center;
						flex-wrap: wrap;
						gap: 8px;
						margin: 0 0 8px 0;
					">
						<span style="
							display: inline-flex;
							align-items: center;
							gap: 6px;
							padding: 5px 8px;
							border-radius: 999px;
							border: 1px solid <?php echo esc_attr( $category['color'] ); ?>;
							background: <?php echo esc_attr( $category['bg'] ); ?>;
							color: <?php echo esc_attr( $category['color'] ); ?>;
							font-size: 11px;
							font-weight: 600;
							letter-spacing: 0.2px;
						">
							<span class="dashicons <?php echo esc_attr( $category['icon'] ); ?>" style="width: 16px; height: 16px; font-size: 14px;"></span>
							<span><?php echo esc_html( $category['label'] ); ?></span>
						</span>
						<span style="
							display: inline-flex;
							align-items: center;
							gap: 6px;
							padding: 4px 7px;
							border-radius: 999px;
							border: 1px solid <?php echo esc_attr( $card_border ); ?>;
							color: <?php echo esc_attr( $card_border ); ?>;
							background: #fff;
							font-size: 11px;
							text-transform: uppercase;
							letter-spacing: 0.3px;
						">
							<span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?php echo esc_attr( $card_border ); ?>;"></span>
							<span><?php echo esc_html( ucfirst( $card_severity ) ); ?></span>
						</span>
					</div>

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

					<?php if ( empty( $findings_by_status[ $status ] ) ) : ?>
						<div class="kanban-empty-message" style="
							text-align: center;
							color: #ccc;
							padding: 40px 10px;
							font-size: 12px;
						">
							No findings yet
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
        <div class="wpshadow-kanban-legend" style="
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
