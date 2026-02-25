<?php
/**
 * Trigger Selection Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = \WPShadow\Workflow\Workflow_Wizard::get_trigger_categories();
?>

	<?php
	wpshadow_render_page_header(
		__( 'Choose a Trigger', 'wpshadow' ),
		__( 'Choose what triggers your workflow. When this event occurs, your chosen actions will run automatically.', 'wpshadow' ),
		'dashicons-controls-play'
	);
	?>

	<?php
	// Store current trigger info for display at bottom
	$current_trigger_id     = null;
	$current_trigger_config = null;
	$current_trigger        = null;
	$schedule_display       = '';

	if ( ! empty( $workflow ) ) {
		// Try new format first (blocks)
		if ( ! empty( $workflow['blocks'] ) ) {
			foreach ( $workflow['blocks'] as $block ) {
				if ( 'trigger' === $block['type'] ) {
					$current_trigger_id     = $block['id'];
					$current_trigger_config = isset( $block['config'] ) ? $block['config'] : array();
					break;
				}
			}
		}
		// Fall back to old format (trigger key)
		elseif ( ! empty( $workflow['trigger'] ) ) {
			$current_trigger_id     = $workflow['trigger'];
			$current_trigger_config = array();
		}
	}

	if ( $current_trigger_id ) {
		$all_triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
		if ( isset( $all_triggers[ $current_trigger_id ] ) ) {
			$current_trigger = $all_triggers[ $current_trigger_id ];

			// Format schedule for time triggers
			if ( 'time_daily' === $current_trigger_id ) {
				$frequency = isset( $current_trigger_config['frequency'] ) ? $current_trigger_config['frequency'] : 'daily';
				$time      = isset( $current_trigger_config['time'] ) ? $current_trigger_config['time'] : '02:00';

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
					$schedule_display = sprintf( __( 'Daily at %s', 'wpshadow' ), $time_display );
				} elseif ( 'weekly' === $frequency ) {
					$day              = isset( $current_trigger_config['day'] ) ? ucfirst( $current_trigger_config['day'] ) : 'Sunday';
					$schedule_display = sprintf( __( 'Weekly on %1$s at %2$s', 'wpshadow' ), $day, $time_display );
				} elseif ( 'monthly' === $frequency ) {
					$day              = isset( $current_trigger_config['day'] ) ? $current_trigger_config['day'] : '1';
					$schedule_display = sprintf( __( 'Monthly on day %1$s at %2$s', 'wpshadow' ), $day, $time_display );
				}
			}
		}
	}
	?>

	<div class="wps-layout-stack wps-layout-stack-lg">
		<?php foreach ( $categories as $category_id => $category ) : ?>
			<div class="wps-card">
				<div class="wps-card-header">
					<h3 class="wps-card-title">
						<span class="dashicons dashicons-<?php echo esc_attr( $category['icon'] ); ?>" style="margin-right: var(--wps-space-2);"></span>
						<?php echo esc_html( $category['label'] ); ?>
					</h3>
				</div>
				<div class="wps-card-body">
					<div class="wps-grid wps-grid-cols-2">
					<?php foreach ( $category['triggers'] as $trigger_id => $trigger ) : ?>
						<?php
						// Check if this is the current trigger when editing
						$is_current = false;
						if ( ! empty( $workflow ) && ! empty( $workflow['blocks'] ) ) {
							foreach ( $workflow['blocks'] as $block ) {
								if ( 'trigger' === $block['type'] && $trigger_id === $block['id'] ) {
									$is_current = true;
									break;
								}
							}
						}

						$has_config_fields = ! empty(
							\WPShadow\Workflow\Workflow_Wizard::get_trigger_config( $trigger_id )
						);

						// Build the URL for trigger config
						$trigger_url = admin_url( 'admin.php?page=wpshadow-automations' );
						if ( ! empty( $workflow ) && ! empty( $workflow['id'] ) ) {
							$trigger_url .= '&action=edit&workflow=' . $workflow['id'];
						} else {
							$trigger_url .= '&action=create';
						}
						$trigger_url .= $has_config_fields ? '&step=trigger-config&trigger=' . $trigger_id : '&step=action-selection&trigger=' . $trigger_id;
						?>
						<a href="<?php echo esc_url( $trigger_url ); ?>" class="trigger-option <?php echo $is_current ? 'trigger-option-current' : ''; ?>">
							<?php if ( $is_current ) : ?>
								<span class="current-badge" title="<?php esc_attr_e( 'Currently selected trigger', 'wpshadow' ); ?>">
									<span class="dashicons dashicons-yes-alt"></span>
								</span>
							<?php endif; ?>
							<span class="trigger-icon">
								<span class="dashicons dashicons-<?php echo esc_attr( $trigger['icon'] ); ?>"></span>
							</span>
							<span class="trigger-content">
								<strong class="trigger-label"><?php echo esc_html( $trigger['label'] ); ?></strong>
								<span class="trigger-description"><?php echo esc_html( $trigger['description'] ); ?></span>
							</span>
							<span class="trigger-arrow">
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</span>
						</a>
					<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php
	// Display current trigger banner at bottom if editing
	if ( $current_trigger ) {
		?>
		<div class="wps-card wps-card-success" style="margin-top: var(--wps-space-8);">
			<div class="wps-card-body" style="display: flex; align-items: center; gap: var(--wps-space-4);">
				<div style="flex-shrink: 0; font-size: 32px; color: var(--wps-success-dark);">
					<span class="dashicons dashicons-<?php echo esc_attr( $current_trigger['icon'] ); ?>"></span>
				</div>
				<div style="flex: 1;">
					<div class="wps-text-xs wps-text-muted" style="text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
						<?php esc_html_e( 'Currently Active Trigger', 'wpshadow' ); ?>
					</div>
					<h3 class="wps-text-lg" style="margin: var(--wps-space-1) 0;">
						<?php echo esc_html( $current_trigger['label'] ); ?>
					</h3>
					<?php if ( ! empty( $schedule_display ) ) : ?>
						<div class="wps-text-sm wps-text-muted" style="font-style: italic; margin-bottom: var(--wps-space-1);">
							<?php echo esc_html( $schedule_display ); ?>
						</div>
					<?php endif; ?>
					<p class="wps-text-sm" style="margin: 0; color: var(--wps-gray-600);">
						<?php echo esc_html( $current_trigger['description'] ); ?>
					</p>
				</div>
				<div class="wps-badge wps-badge-success">
					<span class="dashicons dashicons-yes-alt" style="font-size: 16px; margin-right: var(--wps-space-1);"></span>
					<?php esc_html_e( 'Active', 'wpshadow' ); ?>
				</div>
			</div>
		</div>
		<?php
	}
	?>

<style>
/* Trigger Option Cards - Custom Styling */
.trigger-option {
	display: flex;
	align-items: center;
	gap: var(--wps-space-4);
	padding: var(--wps-space-4);
	background: var(--wps-gray-50);
	border: 2px solid var(--wps-border-color);
	border-radius: var(--wps-radius-md);
	cursor: pointer;
	transition: all 0.2s ease;
	text-align: left;
	width: 100%;
	position: relative;
	text-decoration: none;
	color: inherit;
}

.trigger-option:hover {
	background: #fff;
	border-color: var(--wps-primary);
	transform: translateY(-2px);
	box-shadow: var(--wps-shadow-md);
}

.trigger-option:focus-visible {
	outline: 3px solid var(--wps-focus-ring);
	outline-offset: 2px;
}

/* Active Trigger Highlight */
.trigger-option-current {
	background: var(--wps-warning-lightest);
	border: 3px solid var(--wps-warning);
	box-shadow: 0 0 0 6px rgba(251, 192, 45, 0.15);
	position: relative;
	padding-top: calc(var(--wps-space-9));
}

.trigger-option-current::before {
	content: '★ ACTIVE TRIGGER ★';
	position: absolute;
	top: var(--wps-space-2);
	left: var(--wps-space-4);
	right: var(--wps-space-4);
	background-color: var(--wps-warning-dark);
	color: #fff;
	font-size: var(--wps-text-xs);
	font-weight: 700;
	padding: var(--wps-space-1) var(--wps-space-2);
	border-radius: var(--wps-radius-sm);
	text-align: center;
	letter-spacing: 0.05em;
}

.trigger-option-current .trigger-icon {
	background: var(--wps-warning);
	color: var(--wps-warning-dark);
}

.trigger-option-current .trigger-label {
	color: var(--wps-gray-900);
	font-weight: 700;
}

/* Trigger Components */
.trigger-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--wps-primary);
	border-radius: var(--wps-radius-md);
	color: #fff;
}

.trigger-icon .dashicons {
	width: 24px;
	height: 24px;
	font-size: 24px;
}

.trigger-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: var(--wps-space-1);
}

.trigger-label {
	font-size: var(--wps-text-sm);
	font-weight: 600;
	color: var(--wps-gray-900);
	display: block;
}

.trigger-description {
	font-size: var(--wps-text-xs);
	color: var(--wps-gray-600);
	display: block;
}

.trigger-arrow {
	flex-shrink: 0;
	color: var(--wps-primary);
	opacity: 0;
	transition: opacity 0.2s ease;
}

.trigger-option:hover .trigger-arrow {
	opacity: 1;
}

.trigger-arrow .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

/* Keep header content stacked on separate lines. */
.wps-page-header {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}

.current-badge {
	position: absolute;
	top: -8px;
	right: -8px;
	background: var(--wps-success-dark);
	color: white;
	border-radius: 50%;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	border: 3px solid white;
	box-shadow: var(--wps-shadow-md);
}

.current-badge .dashicons {
	width: 18px;
	height: 18px;
	font-size: 18px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Scroll to and highlight the current trigger if editing
	const currentTriggerElement = $('.trigger-option-current');
	if (currentTriggerElement.length > 0) {
		// Scroll the banner and trigger into view with a small delay
		setTimeout(function() {
			// First scroll to the banner
			const banner = $('.wps-card-success');
			if (banner.length > 0) {
				$('html, body').animate({
					scrollTop: banner.offset().top - 100
				}, 500);
			}
			
			// Then scroll to the current trigger after a short delay
			setTimeout(function() {
				$('html, body').animate({
					scrollTop: currentTriggerElement.offset().top - 200
				}, 300);
			}, 500);
		}, 300);
	}
});
</script>
