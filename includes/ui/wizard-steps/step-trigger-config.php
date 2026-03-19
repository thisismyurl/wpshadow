<?php
/**
 * Wizard Step: Trigger Configuration
 *
 * Allows user to configure the selected trigger.
 *
 * @package WPShadow
 * @subpackage Views
 * @since 1.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get selected trigger from session
$selected_trigger = isset( $_GET['trigger_id'] ) ? sanitize_key( $_GET['trigger_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( empty( $selected_trigger ) ) {
	echo '<p>' . esc_html__( 'Please select a trigger first.', 'wpshadow' ) . '</p>';
	return;
}

// Get trigger config from workflow if editing
$trigger_config = array();
if ( ! empty( $workflow['blocks'] ) ) {
	foreach ( $workflow['blocks'] as $block ) {
		if ( $block['type'] === 'trigger' && $block['id'] === $selected_trigger ) {
			$trigger_config = $block['config'] ?? array();
			break;
		}
	}
}

// Get trigger from registry
$triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
$trigger_data = $triggers[ $selected_trigger ] ?? array();
?>

<h2><?php echo esc_html( $trigger_data['label'] ?? __( 'Configure Trigger', 'wpshadow' ) ); ?></h2>
<p><?php echo esc_html( $trigger_data['description'] ?? '' ); ?></p>

<div class="wpshadow-config-form">
	<?php
	// Show configuration options based on trigger type
	if ( $trigger_data['type'] === 'time_trigger' ) :
		// Time-based trigger configuration
		$frequency = $trigger_config['frequency'] ?? 'daily';
		$time = $trigger_config['time'] ?? '02:00';
		$day = $trigger_config['day'] ?? '1';
		$use_offpeak = $trigger_config['use_offpeak'] ?? false;
		?>

		<div class="wpshadow-form-group">
			<label><?php esc_html_e( 'When should this run?', 'wpshadow' ); ?></label>
			<div style="display: flex; gap: 10px; flex-wrap: wrap;">
				<label style="display: flex; align-items: center; gap: 8px; font-weight: 400;">
					<input type="radio" name="frequency" value="daily" <?php checked( $frequency, 'daily' ); ?> class="wpshadow-frequency-select">
					<?php esc_html_e( 'Daily', 'wpshadow' ); ?>
				</label>
				<label style="display: flex; align-items: center; gap: 8px; font-weight: 400;">
					<input type="radio" name="frequency" value="weekly" <?php checked( $frequency, 'weekly' ); ?> class="wpshadow-frequency-select">
					<?php esc_html_e( 'Weekly', 'wpshadow' ); ?>
				</label>
				<label style="display: flex; align-items: center; gap: 8px; font-weight: 400;">
					<input type="radio" name="frequency" value="monthly" <?php checked( $frequency, 'monthly' ); ?> class="wpshadow-frequency-select">
					<?php esc_html_e( 'Monthly', 'wpshadow' ); ?>
				</label>
			</div>
		</div>

		<div class="wpshadow-form-group" id="wpshadow-day-selector" style="<?php echo $frequency !== 'weekly' ? 'display: none;' : ''; ?>">
			<label><?php esc_html_e( 'Day of the week', 'wpshadow' ); ?></label>
			<select name="day">
				<?php
				$days = array(
					'monday'    => __( 'Monday', 'wpshadow' ),
					'tuesday'   => __( 'Tuesday', 'wpshadow' ),
					'wednesday' => __( 'Wednesday', 'wpshadow' ),
					'thursday'  => __( 'Thursday', 'wpshadow' ),
					'friday'    => __( 'Friday', 'wpshadow' ),
					'saturday'  => __( 'Saturday', 'wpshadow' ),
					'sunday'    => __( 'Sunday', 'wpshadow' ),
				);
				foreach ( $days as $day_key => $day_label ) :
					?>
					<option value="<?php echo esc_attr( $day_key ); ?>" <?php selected( $day, $day_key ); ?>>
						<?php echo esc_html( $day_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="wpshadow-form-group" id="wpshadow-date-selector" style="<?php echo $frequency !== 'monthly' ? 'display: none;' : ''; ?>">
			<label><?php esc_html_e( 'Day of the month', 'wpshadow' ); ?></label>
			<select name="month_day">
				<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $day, (string) $i ); ?>>
						<?php echo esc_html( sprintf( __( 'Day %d', 'wpshadow' ), $i ) ); ?>
					</option>
				<?php endfor; ?>
			</select>
		</div>

		<div class="wpshadow-form-group">
			<label><?php esc_html_e( 'Time of day', 'wpshadow' ); ?></label>
			<input type="time" name="time" value="<?php echo esc_attr( $time ); ?>">
		</div>

		<div class="wpshadow-checkbox-group">
			<input 
				type="checkbox" 
				id="use_offpeak" 
				name="use_offpeak" 
				value="1"
				<?php checked( $use_offpeak ); ?>
			>
			<label for="use_offpeak">
				<?php esc_html_e( 'Run during off-peak hours (11 PM - 6 AM)', 'wpshadow' ); ?>
			</label>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.wpshadow-frequency-select').on('change', function() {
				var frequency = $(this).val();
				$('#wpshadow-day-selector').toggle(frequency === 'weekly');
				$('#wpshadow-date-selector').toggle(frequency === 'monthly');
			});
		});
		</script>

	<?php else : ?>
		<p><?php esc_html_e( 'This trigger requires no additional configuration.', 'wpshadow' ); ?></p>
	<?php endif; ?>
</div>

<input type="hidden" name="trigger_id" value="<?php echo esc_attr( $selected_trigger ); ?>">
