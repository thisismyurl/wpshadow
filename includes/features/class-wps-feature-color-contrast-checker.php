<?php
/**
 * Feature: Color Contrast Checker
 *
 * Provides a utility to check color contrast ratios between text and background
 * colors to ensure WCAG accessibility compliance. Essential for readability
 * and legal compliance regarding inclusive web design.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Color_Contrast_Checker
 *
 * Ensures text and background colors meet WCAG accessibility standards.
 */
final class WPSHADOW_Feature_Color_Contrast_Checker extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'color-contrast-checker',
				'name'               => __( 'Color Contrast Checker', 'plugin-wpshadow' ),
				'description'        => __( 'Ensures your text and background colors meet WCAG accessibility standards. This utility is vital for readability and legal compliance regarding inclusive web design. Test color combinations in real-time and get instant feedback on AA and AAA compliance levels.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-appearance',
				'category'           => 'accessibility',
				'priority'           => 15,
			)
		);
	}

	/**
	 * Enable details page for this feature.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Register AJAX handler for checking contrast.
		add_action( 'wp_ajax_wpshadow_check_contrast', array( $this, 'ajax_check_contrast' ) );

		// Register Site Health test.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'Color Contrast Checker initialized', 'info' );
	}

	/**
	 * Render the color contrast checker page.
	 *
	 * @return void
	 */
	public function render_checker_page(): void {
		// Enqueue color picker.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Enqueue custom styles and scripts.
		$this->enqueue_assets();

		?>
		<div class="wrap wpshadow-color-contrast-checker">
			<h1><?php echo esc_html__( 'Color Contrast Checker', 'plugin-wpshadow' ); ?></h1>
			<p><?php echo esc_html__( 'Test color combinations to ensure they meet WCAG accessibility standards for text readability.', 'plugin-wpshadow' ); ?></p>

			<div class="wps-contrast-checker-container">
				<div class="wps-contrast-inputs">
					<div class="wps-input-group">
						<label for="wps-text-color">
							<?php echo esc_html__( 'Text Color', 'plugin-wpshadow' ); ?>
						</label>
						<input type="text" id="wps-text-color" class="wps-color-picker" value="#000000" />
					</div>

					<div class="wps-input-group">
						<label for="wps-background-color">
							<?php echo esc_html__( 'Background Color', 'plugin-wpshadow' ); ?>
						</label>
						<input type="text" id="wps-background-color" class="wps-color-picker" value="#FFFFFF" />
					</div>

					<div class="wps-input-group">
						<label>
							<input type="checkbox" id="wps-large-text" />
							<?php echo esc_html__( 'Large Text (18pt+ or 14pt+ bold)', 'plugin-wpshadow' ); ?>
						</label>
					</div>

					<button type="button" id="wps-check-contrast" class="button button-primary">
						<?php echo esc_html__( 'Check Contrast', 'plugin-wpshadow' ); ?>
					</button>
				</div>

				<div class="wps-contrast-preview" id="wps-preview">
					<div class="wps-preview-text" id="wps-preview-text">
						<?php echo esc_html__( 'Sample Text Preview', 'plugin-wpshadow' ); ?>
					</div>
				</div>

				<div class="wps-contrast-results" id="wps-results" style="display: none;">
					<h2><?php echo esc_html__( 'Results', 'plugin-wpshadow' ); ?></h2>
					
					<div class="wps-result-item">
						<strong><?php echo esc_html__( 'Contrast Ratio:', 'plugin-wpshadow' ); ?></strong>
						<span id="wps-ratio" class="wps-ratio-value"></span>
					</div>

					<div class="wps-result-item">
						<strong><?php echo esc_html__( 'WCAG AA:', 'plugin-wpshadow' ); ?></strong>
						<span id="wps-aa-result" class="wps-compliance-badge"></span>
						<span class="wps-requirement" id="wps-aa-requirement"></span>
					</div>

					<div class="wps-result-item">
						<strong><?php echo esc_html__( 'WCAG AAA:', 'plugin-wpshadow' ); ?></strong>
						<span id="wps-aaa-result" class="wps-compliance-badge"></span>
						<span class="wps-requirement" id="wps-aaa-requirement"></span>
					</div>

					<div class="wps-info-box">
						<p>
							<strong><?php echo esc_html__( 'WCAG Standards:', 'plugin-wpshadow' ); ?></strong><br>
							• <strong>AA</strong> <?php echo esc_html__( '(Minimum):', 'plugin-wpshadow' ); ?> 
							<?php echo esc_html__( '4.5:1 for normal text, 3:1 for large text', 'plugin-wpshadow' ); ?><br>
							• <strong>AAA</strong> <?php echo esc_html__( '(Enhanced):', 'plugin-wpshadow' ); ?> 
							<?php echo esc_html__( '7:1 for normal text, 4.5:1 for large text', 'plugin-wpshadow' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue assets for the checker page.
	 *
	 * @return void
	 */
	private function enqueue_assets(): void {
		// Localize script with nonce using wp-color-picker as base.
		wp_localize_script(
			'wp-color-picker',
			'wpsColorContrast',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpshadow_check_contrast' ),
			)
		);

		// Inline CSS.
		wp_add_inline_style(
			'wp-admin',
			'
			.wpshadow-color-contrast-checker .wps-contrast-checker-container {
				max-width: 800px;
				margin-top: 20px;
			}
			.wpshadow-color-contrast-checker .wps-contrast-inputs {
				background: #fff;
				padding: 20px;
				border: 1px solid #ccd0d4;
				box-shadow: 0 1px 1px rgba(0,0,0,0.04);
			}
			.wpshadow-color-contrast-checker .wps-input-group {
				margin-bottom: 15px;
			}
			.wpshadow-color-contrast-checker .wps-input-group label {
				display: block;
				margin-bottom: 5px;
				font-weight: 600;
			}
			.wpshadow-color-contrast-checker .wps-color-picker {
				width: 100%;
				max-width: 200px;
			}
			.wpshadow-color-contrast-checker .wps-contrast-preview {
				margin-top: 20px;
				padding: 40px;
				background: #fff;
				border: 1px solid #ccd0d4;
				text-align: center;
			}
			.wpshadow-color-contrast-checker .wps-preview-text {
				font-size: 16px;
				transition: all 0.3s ease;
			}
			.wpshadow-color-contrast-checker .wps-contrast-results {
				margin-top: 20px;
				padding: 20px;
				background: #fff;
				border: 1px solid #ccd0d4;
			}
			.wpshadow-color-contrast-checker .wps-result-item {
				margin: 15px 0;
				padding: 10px;
				background: #f9f9f9;
				border-left: 4px solid #ddd;
			}
			.wpshadow-color-contrast-checker .wps-ratio-value {
				font-size: 24px;
				font-weight: bold;
				color: #2271b1;
			}
			.wpshadow-color-contrast-checker .wps-compliance-badge {
				display: inline-block;
				padding: 4px 12px;
				border-radius: 3px;
				font-weight: 600;
				margin-left: 10px;
			}
			.wpshadow-color-contrast-checker .wps-compliance-badge.pass {
				background: #00a32a;
				color: #fff;
			}
			.wpshadow-color-contrast-checker .wps-compliance-badge.fail {
				background: #d63638;
				color: #fff;
			}
			.wpshadow-color-contrast-checker .wps-requirement {
				color: #646970;
				font-size: 13px;
				margin-left: 10px;
			}
			.wpshadow-color-contrast-checker .wps-info-box {
				margin-top: 20px;
				padding: 15px;
				background: #f0f6fc;
				border-left: 4px solid #2271b1;
			}
			.wpshadow-color-contrast-checker .wps-info-box p {
				margin: 0;
				line-height: 1.8;
			}
			'
		);

		// Inline JavaScript.
		wp_add_inline_script(
			'wp-color-picker',
			"
			jQuery(document).ready(function($) {
				// Initialize color pickers
				$('.wps-color-picker').wpColorPicker({
					change: function() {
						updatePreview();
					},
					clear: function() {
						updatePreview();
					}
				});

				// Update preview when colors change
				function updatePreview() {
					var textColor = $('#wps-text-color').val();
					var bgColor = $('#wps-background-color').val();
					$('#wps-preview-text').css({
						'color': textColor,
						'background-color': bgColor
					});
				}

				// Initial preview update
				updatePreview();

				// Check contrast button
				$('#wps-check-contrast').on('click', function() {
					var textColor = $('#wps-text-color').val();
					var bgColor = $('#wps-background-color').val();
					var isLargeText = $('#wps-large-text').is(':checked');

					$.ajax({
						url: wpsColorContrast.ajaxUrl,
						type: 'POST',
						data: {
							action: 'wpshadow_check_contrast',
							nonce: wpsColorContrast.nonce,
							text_color: textColor,
							background_color: bgColor,
							is_large_text: isLargeText ? '1' : '0'
						},
						success: function(response) {
							if (response.success) {
								var data = response.data;
								$('#wps-ratio').text(data.formatted_ratio);
								
								// AA result
								$('#wps-aa-result')
									.removeClass('pass fail')
									.addClass(data.aa.passes ? 'pass' : 'fail')
									.text(data.aa.passes ? 'PASS' : 'FAIL');
								$('#wps-aa-requirement').text('(Required: ' + data.aa.required + ':1)');
								
								// AAA result
								$('#wps-aaa-result')
									.removeClass('pass fail')
									.addClass(data.aaa.passes ? 'pass' : 'fail')
									.text(data.aaa.passes ? 'PASS' : 'FAIL');
								$('#wps-aaa-requirement').text('(Required: ' + data.aaa.required + ':1)');
								
								$('#wps-results').slideDown();
							} else {
								alert('Error: ' + (response.data.message || 'Unknown error'));
							}
						},
						error: function() {
							alert('AJAX request failed. Please try again.');
						}
					});
				});
			});
			"
		);
	}

	/**
	 * AJAX handler for checking contrast.
	 *
	 * @return void
	 */
	public function ajax_check_contrast(): void {
		// Verify nonce.
		if ( ! check_ajax_referer( 'wpshadow_check_contrast', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session expired. Please refresh and try again.', 'plugin-wpshadow' ) ) );
			return;
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that.', 'plugin-wpshadow' ) ) );
			return;
		}

		// Get and sanitize input.
		$text_color       = isset( $_POST['text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['text_color'] ) ) : '';
		$background_color = isset( $_POST['background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['background_color'] ) ) : '';
		$is_large_text    = isset( $_POST['is_large_text'] ) && $_POST['is_large_text'] === '1';

		// Validate colors.
		if ( empty( $text_color ) || empty( $background_color ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter both colors.', 'plugin-wpshadow' ) ) );
			return;
		}

		// Validate hex color format.
		$text_color       = ltrim( $text_color, '#' );
		$background_color = ltrim( $background_color, '#' );

		if ( ! preg_match( '/^[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $text_color ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid text color format. Use hex colors (e.g., #FFFFFF).', 'plugin-wpshadow' ) ) );
			return;
		}

		if ( ! preg_match( '/^[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $background_color ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid background color format. Use hex colors (e.g., #FFFFFF).', 'plugin-wpshadow' ) ) );
			return;
		}

		// Add back # prefix for helper functions.
		$text_color       = '#' . $text_color;
		$background_color = '#' . $background_color;

		// Check contrast using helper functions.
		$result = WPSHADOW_check_contrast( $text_color, $background_color, $is_large_text );

		wp_send_json_success( $result );
	}
}
