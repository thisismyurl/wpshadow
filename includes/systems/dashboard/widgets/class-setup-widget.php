<?php
/**
 * WPShadow Setup Dashboard Widget
 *
 * Displays a WooCommerce-style setup progress widget on the WordPress dashboard
 * when initial configuration is incomplete.
 *
 * Philosophy #1: Helpful Neighbor - Guide users through setup
 * Philosophy #8: Inspire Confidence - Show clear progress
 *
 * @package WPShadow
 * @since 1.6030.2201
 */

namespace WPShadow\Dashboard\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup Widget Class
 *
 * Registers and renders a dashboard widget that guides users through
 * the WPShadow setup process.
 */
class Setup_Widget {

	/**
	 * Initialize the setup widget
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_widget' ) );
	}

	/**
	 * Register the dashboard widget
	 *
	 * Only registers if user hasn't completed onboarding
	 *
	 * @return void
	 */
	public static function register_widget(): void {
		// Check if onboarding is needed
		if ( ! class_exists( '\WPShadow\Onboarding\Onboarding_Manager' ) ) {
			return;
		}

		if ( ! \WPShadow\Onboarding\Onboarding_Manager::needs_onboarding() ) {
			return;
		}

		// Register the widget
		wp_add_dashboard_widget(
			'wpshadow_setup_widget',
			__( 'WPShadow Setup', 'wpshadow' ),
			array( __CLASS__, 'render' ),
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render the setup widget
	 *
	 * @return void
	 */
	public static function render(): void {
		$user_id = get_current_user_id();

		// Get current setup status
		$completed_steps = self::get_completed_steps( $user_id );
		$total_steps     = 5;
		$current_step    = count( $completed_steps ) + 1;

		// Calculate progress percentage
		$progress_percent = ( count( $completed_steps ) / $total_steps ) * 100;

		?>
		<div class="wpshadow-setup-widget">
			<div class="wpshadow-setup-header">
				<div class="wpshadow-setup-progress-text">
					<?php
					printf(
						/* translators: 1: Current step number, 2: Total steps */
						esc_html__( 'Step %1$d of %2$d', 'wpshadow' ),
						esc_html( $current_step ),
						esc_html( $total_steps )
					);
					?>
				</div>
				<div class="wpshadow-setup-tagline">
					<?php esc_html_e( 'You\'re almost there! Once you complete setup, WPShadow can start helping improve your site.', 'wpshadow' ); ?>
				</div>
			</div>
			
			<div class="wpshadow-setup-progress-bar">
				<div class="wpshadow-setup-progress-fill" style="width: <?php echo esc_attr( $progress_percent ); ?>%;"></div>
			</div>
			
			<div class="wpshadow-setup-steps">
				<?php self::render_step( 1, __( 'Choose Your Platform', 'wpshadow' ), __( 'Tell us what you\'re familiar with', 'wpshadow' ), $completed_steps ); ?>
				<?php self::render_step( 2, __( 'Technical Comfort', 'wpshadow' ), __( 'How do you like to learn?', 'wpshadow' ), $completed_steps ); ?>
				<?php self::render_step( 3, __( 'Configure Features', 'wpshadow' ), __( 'Choose helpful features', 'wpshadow' ), $completed_steps ); ?>
				<?php self::render_step( 4, __( 'Privacy Settings', 'wpshadow' ), __( 'Set your preferences', 'wpshadow' ), $completed_steps ); ?>
				<?php self::render_step( 5, __( 'Confirm & Complete', 'wpshadow' ), __( 'Start using WPShadow', 'wpshadow' ), $completed_steps ); ?>
			</div>
			
			<div class="wpshadow-setup-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&onboarding=start' ) ); ?>" class="wps-btn wps-btn-primary wps-btn-lg">
					<?php esc_html_e( 'Continue Setup', 'wpshadow' ); ?>
				</a>
				<a href="#" class="wpshadow-setup-skip" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_onboarding' ) ); ?>">
					<?php esc_html_e( 'Skip for now', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
		
		<style>
		.wpshadow-setup-widget {
			padding: 20px;
			background: #fff;
		}
		
		.wpshadow-setup-header {
			margin-bottom: 20px;
		}
		
		.wpshadow-setup-progress-text {
			font-size: 18px;
			font-weight: 600;
			color: #1d2327;
			margin-bottom: 8px;
		}
		
		.wpshadow-setup-tagline {
			font-size: 14px;
			color: #646970;
			line-height: 1.6;
		}
		
		.wpshadow-setup-progress-bar {
			height: 8px;
			background: #e5e5e5;
			border-radius: 4px;
			margin-bottom: 20px;
			overflow: hidden;
		}
		
		.wpshadow-setup-progress-fill {
			height: 100%;
			background: linear-gradient(90deg, #0073aa 0%, #00a0d2 100%);
			border-radius: 4px;
			transition: width 0.3s ease;
		}
		
		.wpshadow-setup-steps {
			margin-bottom: 20px;
		}
		
		.wpshadow-setup-step {
			display: flex;
			align-items: flex-start;
			padding: 12px 0;
			border-bottom: 1px solid #e5e5e5;
		}
		
		.wpshadow-setup-step:last-child {
			border-bottom: none;
		}
		
		.wpshadow-setup-step-number {
			flex-shrink: 0;
			width: 28px;
			height: 28px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 13px;
			font-weight: 600;
			margin-right: 12px;
			background: #e5e5e5;
			color: #646970;
		}
		
		.wpshadow-setup-step.completed .wpshadow-setup-step-number {
			background: #2e7d32;
			color: #fff;
		}
		
		.wpshadow-setup-step.completed .wpshadow-setup-step-number::before {
			content: "✓";
		}
		
		.wpshadow-setup-step.current .wpshadow-setup-step-number {
			background: #0073aa;
			color: #fff;
		}
		
		.wpshadow-setup-step-content {
			flex: 1;
			min-width: 0;
		}
		
		.wpshadow-setup-step-title {
			font-size: 14px;
			font-weight: 600;
			color: #1d2327;
			margin-bottom: 2px;
		}
		
		.wpshadow-setup-step.completed .wpshadow-setup-step-title {
			color: #646970;
		}
		
		.wpshadow-setup-step-description {
			font-size: 13px;
			color: #646970;
		}
		
		.wpshadow-setup-actions {
			display: flex;
			align-items: center;
			gap: 15px;
			padding-top: 10px;
		}
		
		.wpshadow-setup-skip {
			color: #646970;
			text-decoration: none;
			font-size: 13px;
		}
		
		.wpshadow-setup-skip:hover {
			color: #1d2327;
		}
		</style>
		
		<script>
		jQuery(document).ready(function($) {
			$('.wpshadow-setup-skip').on('click', function(e) {
				e.preventDefault();
				
				if (!confirm('<?php echo esc_js( __( 'Are you sure you want to skip setup? You can always complete it later from the WPShadow menu.', 'wpshadow' ) ); ?>')) {
					return;
				}
				
				var nonce = $(this).data('nonce');
				
				$.post(ajaxurl, {
					action: 'wpshadow_skip_onboarding',
					nonce: nonce
				}, function(response) {
					if (response.success) {
						$('#wpshadow_setup_widget').fadeOut(function() {
							$(this).remove();
						});
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Render a single setup step
	 *
	 * @param int    $step_number Step number
	 * @param string $title Step title
	 * @param string $description Step description
	 * @param array  $completed_steps Array of completed step numbers
	 * @return void
	 */
	private static function render_step( int $step_number, string $title, string $description, array $completed_steps ): void {
		$is_completed = in_array( $step_number, $completed_steps, true );
		$is_current   = ! $is_completed && ( count( $completed_steps ) + 1 ) === $step_number;

		$classes = array( 'wpshadow-setup-step' );
		if ( $is_completed ) {
			$classes[] = 'completed';
		} elseif ( $is_current ) {
			$classes[] = 'current';
		}

		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<div class="wpshadow-setup-step-number">
				<?php if ( ! $is_completed ) : ?>
					<?php echo esc_html( $step_number ); ?>
				<?php endif; ?>
			</div>
			<div class="wpshadow-setup-step-content">
				<div class="wpshadow-setup-step-title"><?php echo esc_html( $title ); ?></div>
				<div class="wpshadow-setup-step-description"><?php echo esc_html( $description ); ?></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get completed setup steps for user
	 *
	 * Determines which steps have been completed based on saved user meta.
	 * This is a simplified approach - returns empty array if not completed,
	 * or all steps if completed.
	 *
	 * @param int $user_id User ID
	 * @return array Array of completed step numbers
	 */
	private static function get_completed_steps( int $user_id ): array {
		if ( ! class_exists( '\WPShadow\Onboarding\Onboarding_Manager' ) ) {
			return array();
		}

		// If onboarding is complete, return all steps
		if ( ! \WPShadow\Onboarding\Onboarding_Manager::needs_onboarding( $user_id ) ) {
			return array( 1, 2, 3, 4, 5 );
		}

		// Check individual steps based on saved data
		$completed = array();

		// Step 1: Platform selected
		$platform = get_user_meta( $user_id, 'wpshadow_onboarding_platform', true );
		if ( ! empty( $platform ) ) {
			$completed[] = 1;
		}

		// Step 2: Comfort level selected
		$comfort = get_user_meta( $user_id, 'wpshadow_onboarding_comfort_level', true );
		if ( ! empty( $comfort ) ) {
			$completed[] = 2;
		}

		// Step 3: Configuration preferences set
		$config = get_user_meta( $user_id, 'wpshadow_config_preferences', true );
		if ( is_array( $config ) && ! empty( $config ) ) {
			$completed[] = 3;
		}

		// Step 4: Privacy preferences set
		$privacy = get_user_meta( $user_id, 'wpshadow_privacy_preferences', true );
		if ( is_array( $privacy ) && ! empty( $privacy ) ) {
			$completed[] = 4;
		}

		// Step 5 is only completed when onboarding is fully complete

		return $completed;
	}
}
