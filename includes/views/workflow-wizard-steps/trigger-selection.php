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

<div class="wizard-step trigger-selection">
	<h2><?php esc_html_e( 'Choose a Trigger', 'wpshadow' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Choose what triggers your workflow. When this event occurs, your chosen actions will run automatically.', 'wpshadow' ); ?>
	</p>

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

	<div class="trigger-categories">
		<?php foreach ( $categories as $category_id => $category ) : ?>
			<div class="trigger-category">
				<h3>
					<span class="dashicons dashicons-<?php echo esc_attr( $category['icon'] ); ?>"></span>
					<?php echo esc_html( $category['label'] ); ?>
				</h3>
				<div class="trigger-options">
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

						// Build the URL for trigger config
						$trigger_url = admin_url( 'admin.php?page=wpshadow-workflows' );
						if ( ! empty( $workflow ) && ! empty( $workflow['id'] ) ) {
							$trigger_url .= '&action=edit&workflow=' . $workflow['id'];
						} else {
							$trigger_url .= '&action=create';
						}
						$trigger_url .= '&step=trigger-config&trigger=' . $trigger_id;
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
		<?php endforeach; ?>
	</div>

	<?php
	// Display current trigger banner at bottom if editing
	if ( $current_trigger ) {
		?>
		<div class="current-trigger-banner">
			<div class="banner-icon">
				<span class="dashicons dashicons-<?php echo esc_attr( $current_trigger['icon'] ); ?>"></span>
			</div>
			<div class="banner-content">
				<div class="banner-label">
					<?php esc_html_e( 'Currently Active Trigger', 'wpshadow' ); ?>
				</div>
				<div class="banner-title">
					<?php echo esc_html( $current_trigger['label'] ); ?>
				</div>
				<?php if ( ! empty( $schedule_display ) ) : ?>
					<div class="banner-schedule">
						<?php echo esc_html( $schedule_display ); ?>
					</div>
				<?php endif; ?>
				<div class="banner-description">
					<?php echo esc_html( $current_trigger['description'] ); ?>
				</div>
			</div>
			<div class="banner-badge">
				<span class="dashicons dashicons-yes-alt"></span>
				<span class="badge-text"><?php esc_html_e( 'Active', 'wpshadow' ); ?></span>
			</div>
		</div>
		<?php
	}
	?>
</div>

<style>
.wizard-step h2 {
	margin-top: 0;
	font-size: 24px;
	margin-bottom: 10px;
}

.wizard-step .description {
	font-size: 14px;
	margin-bottom: 30px;
}

.current-trigger-banner {
	display: flex;
	align-items: center;
	gap: 16px;
	background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
	border: 3px solid #ff9800;
	border-radius: 8px;
	padding: 20px;
	margin-top: 32px;
	box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3), inset 0 0 20px rgba(255, 255, 255, 0.1);
	animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
	0%, 100% {
		box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3), inset 0 0 20px rgba(255, 255, 255, 0.1);
	}
	50% {
		box-shadow: 0 6px 20px rgba(255, 152, 0, 0.5), inset 0 0 30px rgba(255, 255, 255, 0.2);
	}
}

.banner-icon {
	flex-shrink: 0;
	font-size: 32px;
	color: #ff9800;
}

.banner-content {
	flex: 1;
	color: white;
}

.banner-label {
	font-size: 11px;
	text-transform: uppercase;
	letter-spacing: 1px;
	color: #ffb74d;
	font-weight: 600;
	margin-bottom: 4px;
}

.banner-title {
	font-size: 18px;
	font-weight: bold;
	color: #fff;
	margin-bottom: 6px;
}

.banner-schedule {
	font-size: 13px;
	color: #b3e5fc;
	margin-bottom: 6px;
	font-style: italic;
}

.banner-description {
	font-size: 13px;
	color: #e0f2f1;
	margin-top: 8px;
	opacity: 0.9;
}

.banner-badge {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 6px;
	background-color: rgba(76, 175, 80, 0.2);
	border-radius: 8px;
	padding: 12px 16px;
	flex-shrink: 0;
	border: 2px solid #4caf50;
}

.banner-badge .dashicons {
	font-size: 28px;
	color: #4caf50;
	width: 28px;
	height: 28px;
}

.badge-text {
	font-size: 11px;
	font-weight: bold;
	color: #4caf50;
	text-transform: uppercase;
}

.trigger-categories {
	display: flex;
	flex-direction: column;
	gap: 30px;
}

.trigger-category h3 {
	font-size: 16px;
	font-weight: 600;
	margin: 0 0 15px 0;
	display: flex;
	align-items: center;
	gap: 8px;
	color: #2271b1;
}

.trigger-category h3 .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

.trigger-options {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 12px;
}

.trigger-option {
	display: flex;
	align-items: center;
	gap: 15px;
	padding: 16px;
	background: #f9f9f9;
	border: 2px solid #ddd;
	border-radius: 6px;
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
	border-color: #2271b1;
	transform: translateY(-2px);
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.trigger-option-current {
	background: linear-gradient(135deg, #fff9c4 0%, #fffde7 100%);
	border: 3px solid #fbc02d;
	box-shadow: 0 0 0 6px rgba(251, 192, 45, 0.2), inset 0 0 15px rgba(251, 192, 45, 0.1);
	position: relative;
	padding-top: 36px;
}

.trigger-option-current::before {
	content: '★ ACTIVE TRIGGER ★';
	position: absolute;
	top: 6px;
	left: 16px;
	right: 16px;
	background-color: #ff6f00;
	color: white;
	font-size: 11px;
	font-weight: bold;
	padding: 4px 8px;
	border-radius: 3px;
	text-align: center;
	letter-spacing: 0.5px;
	z-index: 5;
	box-shadow: 0 2px 4px rgba(255, 111, 0, 0.3);
}

.trigger-option-current .trigger-icon {
	background: #fbc02d;
	color: #ff6f00;
	font-weight: bold;
}

.trigger-option-current .trigger-label {
	color: #333;
	font-weight: 700;
}

.current-badge {
	position: absolute;
	top: -8px;
	right: -8px;
	background: #00a32a;
	color: white;
	border-radius: 50%;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	border: 3px solid white;
	font-size: 18px;
	font-weight: bold;
	box-shadow: 0 2px 8px rgba(0, 163, 42, 0.4);
}

.current-badge .dashicons {
	width: 18px;
	height: 18px;
	font-size: 18px;
}

.trigger-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #2271b1;
	border-radius: 8px;
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
	gap: 4px;
}

.trigger-label {
	font-size: 14px;
	font-weight: 600;
	color: #000;
	display: block;
}

.trigger-description {
	font-size: 12px;
	color: #666;
	display: block;
}

.trigger-arrow {
	flex-shrink: 0;
	color: #2271b1;
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
</style>

<script>
jQuery(document).ready(function($) {
	// Scroll to and highlight the current trigger if editing
	const currentTriggerElement = $('.trigger-option-current');
	if (currentTriggerElement.length > 0) {
		// Scroll the banner and trigger into view with a small delay
		setTimeout(function() {
			// First scroll to the banner
			const banner = $('.current-trigger-banner');
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
				
				// Add a pulse animation to draw attention
				currentTriggerElement.addClass('pulse-highlight');
			}, 500);
		}, 300);
	}
});
</script>

<style>
.pulse-highlight {
	animation: pulse-highlight-animation 1s ease-in-out 3 !important;
}

@keyframes pulse-highlight-animation {
	0%, 100% {
		box-shadow: 0 0 0 6px rgba(251, 192, 45, 0.2), inset 0 0 15px rgba(251, 192, 45, 0.1);
	}
	50% {
		box-shadow: 0 0 0 12px rgba(251, 192, 45, 0.5), inset 0 0 25px rgba(251, 192, 45, 0.3);
	}
}
</style>
</script>
