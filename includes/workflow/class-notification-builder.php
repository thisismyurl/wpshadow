<?php
/**
 * Notification Builder - Reusable notification/action builder for Settings pages
 *
 * This class provides a filtered version of the Workflow Wizard
 * to allow admins to create simple trigger→action rules without full workflow complexity.
 *
 * Used by:
 * - Settings → Notifications tab (trigger any + send dashboard notification)
 * - Settings → Email tab (trigger any + send email)
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Import WordPress functions into this namespace for use in render()
use function wp_nonce_field;
use function wp_json_encode;
use function wp_kses_post;
use function human_time_diff;
use function esc_html;
use function esc_attr;
use function esc_js;
use function wp_create_nonce;
use function esc_html_e;
use function esc_attr_e;
use function __;
use function get_option;
use function update_option;
use function current_user_can;

/**
 * Notification Builder class
 */
class Notification_Builder {

	/**
	 * Builder mode: 'notification' or 'email'
	 *
	 * @var string
	 */
	private static $builder_mode = 'notification';

	/**
	 * Set builder mode
	 *
	 * @param string $mode Either 'notification' or 'email'.
	 */
	public static function set_mode( $mode ) {
		self::$builder_mode = $mode;
	}

	/**
	 * Get all triggers available for this builder
	 *
	 * @return array Categorized triggers
	 */
	public static function get_triggers() {
		return Workflow_Wizard::get_trigger_categories();
	}

	/**
	 * Get actions for this builder (filtered to notification or email only)
	 *
	 * @return array Action groups
	 */
	public static function get_actions() {
		if ( self::$builder_mode === 'email' ) {
			return self::get_email_actions();
		}

		return self::get_notification_actions();
	}

	/**
	 * Get notification actions (dashboard notifications only)
	 *
	 * @return array
	 */
	private static function get_notification_actions() {
		return array(
			'notifications' => array(
				'label'       => __( 'Notification Type', 'wpshadow' ),
				'description' => __( 'Choose how to notify the admin', 'wpshadow' ),
				'icon'        => 'bell',
				'actions'     => array(
					'send_notification' => array(
						'label'       => __( 'Dashboard Notification', 'wpshadow' ),
						'description' => __( 'Show a pop-up notification in WordPress admin', 'wpshadow' ),
						'icon'        => 'bell',
						'config'      => array(
							'message'      => array(
								'type'        => 'textarea',
								'label'       => __( 'Notification Message', 'wpshadow' ),
								'placeholder' => __( 'Enter custom message', 'wpshadow' ),
								'help'        => __( 'Use {finding_name}, {severity}, {category} for variables', 'wpshadow' ),
							),
							'message_type' => array(
								'type'    => 'select',
								'label'   => __( 'Message Type', 'wpshadow' ),
								'options' => array(
									'info'    => __( 'Info (Blue)', 'wpshadow' ),
									'success' => __( 'Success (Green)', 'wpshadow' ),
									'warning' => __( 'Warning (Orange)', 'wpshadow' ),
									'error'   => __( 'Error (Red)', 'wpshadow' ),
								),
								'default' => 'info',
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Get email actions (send email only)
	 *
	 * @return array
	 */
	private static function get_email_actions() {
		return array(
			'notifications' => array(
				'label'       => __( 'Email Action', 'wpshadow' ),
				'description' => __( 'Send email notification', 'wpshadow' ),
				'icon'        => 'email',
				'actions'     => array(
					'send_email' => array(
						'label'       => __( 'Send Email', 'wpshadow' ),
						'description' => __( 'Send email to admin', 'wpshadow' ),
						'icon'        => 'email',
						'config'      => array(
							'subject'      => array(
								'type'        => 'text',
								'label'       => __( 'Email Subject', 'wpshadow' ),
								'placeholder' => __( 'Enter email subject', 'wpshadow' ),
								'help'        => __( 'Use {finding_name}, {severity}, {category} for variables', 'wpshadow' ),
								'required'    => true,
							),
							'message'      => array(
								'type'        => 'textarea',
								'label'       => __( 'Email Message', 'wpshadow' ),
								'placeholder' => __( 'Enter email body', 'wpshadow' ),
								'help'        => __( 'Use {finding_name}, {severity}, {category}, {description} for variables', 'wpshadow' ),
								'required'    => true,
							),
							'send_to'      => array(
								'type'    => 'select',
								'label'   => __( 'Send To', 'wpshadow' ),
								'options' => array(
									'admin'  => __( 'Site Admin Email', 'wpshadow' ),
									'custom' => __( 'Custom Email', 'wpshadow' ),
								),
								'default' => 'admin',
							),
							'custom_email' => array(
								'type'        => 'email',
								'label'       => __( 'Custom Email Address', 'wpshadow' ),
								'placeholder' => __( 'user@example.com', 'wpshadow' ),
								'conditional' => 'send_to == "custom"',
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Get all configured notifications/email rules
	 *
	 * @return array
	 */
	public static function get_configured_rules() {
		$option_key = self::$builder_mode === 'email' ? 'wpshadow_email_rules' : 'wpshadow_notification_rules';
		return get_option( $option_key, array() );
	}

	/**
	 * Save a notification/email rule
	 *
	 * @param array $rule Rule data.
	 * @return int Rule ID or false.
	 */
	public static function save_rule( $rule ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$rules   = self::get_configured_rules();
		$rule_id = isset( $rule['id'] ) ? $rule['id'] : md5( wp_json_encode( $rule ) . time() );

		$rule['id']         = $rule_id;
		$rule['created_at'] = isset( $rule['created_at'] ) ? $rule['created_at'] : time();
		$rule['updated_at'] = time();

		// Update or add rule
		$rules[ $rule_id ] = $rule;

		$option_key = self::$builder_mode === 'email' ? 'wpshadow_email_rules' : 'wpshadow_notification_rules';
		update_option( $option_key, $rules );

		return $rule_id;
	}

	/**
	 * Ensure default notifications/email rules exist for first-time users
	 *
	 * @return void
	 */
	public static function ensure_default_rules() {
		if ( self::$builder_mode !== 'notification' ) {
			return; // Only for notifications, not emails
		}

		$rules = self::get_configured_rules();

		// Check if we already have rules
		if ( ! empty( $rules ) ) {
			return;
		}

		// No rules exist, create defaults
		$default_rules = array(
			array(
				'name'    => __( 'Alert: Security Threat Detected', 'wpshadow' ),
				'trigger' => array(
					'type'  => 'security_threat_detected',
					'label' => __( 'Security Threat Detected', 'wpshadow' ),
				),
				'action'  => array(
					'type'  => 'send_notification',
					'label' => __( 'Dashboard Notification', 'wpshadow' ),
				),
				'config'  => array(
					'message' => __( '⚠️ Security Alert: A potential security threat has been detected on your site. Please review the WPShadow dashboard immediately.', 'wpshadow' ),
					'style'   => 'error',
				),
			),
			array(
				'name'    => __( 'Notify: Critical Issues Found', 'wpshadow' ),
				'trigger' => array(
					'type'  => 'critical_issue_found',
					'label' => __( 'Critical Issue Found', 'wpshadow' ),
				),
				'action'  => array(
					'type'  => 'send_notification',
					'label' => __( 'Dashboard Notification', 'wpshadow' ),
				),
				'config'  => array(
					'message' => __( '🔴 Critical Issue Detected: WPShadow has identified a critical issue that requires your attention. Check the dashboard for details and recommended fixes.', 'wpshadow' ),
					'style'   => 'error',
				),
			),
			array(
				'name'    => __( 'Notify: Backup Completed', 'wpshadow' ),
				'trigger' => array(
					'type'  => 'backup_completed',
					'label' => __( 'Backup Completed', 'wpshadow' ),
				),
				'action'  => array(
					'type'  => 'send_notification',
					'label' => __( 'Dashboard Notification', 'wpshadow' ),
				),
				'config'  => array(
					'message' => __( '✅ Backup Complete: Your site backup has completed successfully.', 'wpshadow' ),
					'style'   => 'success',
				),
			),
		);

		// Save each default rule
		foreach ( $default_rules as $rule ) {
			self::save_rule( $rule );
		}
	}

	/**
	 * Delete a notification/email rule
	 *
	 * @param string $rule_id Rule ID to delete.
	 * @return bool
	 */
	public static function delete_rule( $rule_id ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$rules      = self::get_configured_rules();
		$option_key = self::$builder_mode === 'email' ? 'wpshadow_email_rules' : 'wpshadow_notification_rules';

		if ( isset( $rules[ $rule_id ] ) ) {
			unset( $rules[ $rule_id ] );
			update_option( $option_key, $rules );
			return true;
		}

		return false;
	}

	/**
	 * Get a single rule by ID
	 *
	 * @param string $rule_id Rule ID.
	 * @return array|null
	 */
	public static function get_rule( $rule_id ) {
		$rules = self::get_configured_rules();
		return isset( $rules[ $rule_id ] ) ? $rules[ $rule_id ] : null;
	}

	/**
	 * Render the notification/email builder UI
	 *
	 * @param string $mode Either 'notification' or 'email'.
	 */
	public static function render( $mode = 'notification' ) {
		self::set_mode( $mode );

		// Ensure default rules exist for notifications
		if ( $mode === 'notification' ) {
			self::ensure_default_rules();
		}

		$page_title       = $mode === 'email' ? __( 'Email Rules', 'wpshadow' ) : __( 'Notification Rules', 'wpshadow' );
		$page_description = $mode === 'email'
			? __( 'Create custom email rules: choose any trigger and send an email.', 'wpshadow' )
			: __( 'Create custom notification rules: choose any trigger and get alerted in WordPress admin.', 'wpshadow' );

		$rules    = self::get_configured_rules();
		$triggers = self::get_triggers();
		$actions  = self::get_actions();
		?>

		<div class="wps-notification-builder">
			<div class="wps-builder-header">
				<h2 class="wps-builder-title">
					<span class="dashicons dashicons-<?php echo $mode === 'email' ? 'email' : 'bell'; ?>"></span>
					<?php echo esc_html( $page_title ); ?>
				</h2>
				<p class="wps-builder-description">
					<?php echo esc_html( $page_description ); ?>
				</p>
			</div>

			<!-- Create New Rule Button -->
			<button type="button" class="wps-btn wps-btn-primary" id="wpshadow-create-notification-rule">
				<span class="dashicons dashicons-plus"></span>
				<?php echo $mode === 'email' ? esc_html__( 'Create Email Rule', 'wpshadow' ) : esc_html__( 'Create Notification Rule', 'wpshadow' ); ?>
			</button>

			<!-- Rules List -->
			<div class="wps-notification-rules" style="margin-top: 24px;">
				<?php if ( empty( $rules ) ) : ?>
					<div class="wps-empty-state" style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; border: 1px solid #ddd;">
						<span class="dashicons" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></span>
						<p style="color: #666; font-size: 16px; margin: 0;">
							<?php echo $mode === 'email' ? esc_html__( 'No email rules yet. Create one to get started!', 'wpshadow' ) : esc_html__( 'No notification rules yet. Create one to get started!', 'wpshadow' ); ?>
						</p>
					</div>
				<?php else : ?>
					<?php foreach ( $rules as $rule_id => $rule ) : ?>
						<div class="wps-notification-rule-card" data-rule-id="<?php echo esc_attr( $rule_id ); ?>" style="margin-bottom: 16px; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
							<div style="flex: 1;">
								<h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">
									<?php echo esc_html( $rule['name'] ?? __( 'Unnamed Rule', 'wpshadow' ) ); ?>
								</h4>
								<p style="margin: 0; font-size: 13px; color: #666;">
									<strong><?php esc_html_e( 'Trigger:', 'wpshadow' ); ?></strong>
									<?php echo esc_html( $rule['trigger']['label'] ?? $rule['trigger']['type'] ); ?>
									<br />
									<strong><?php esc_html_e( 'Action:', 'wpshadow' ); ?></strong>
									<?php echo esc_html( $rule['action']['label'] ?? $rule['action']['type'] ); ?>
								</p>
								<p style="margin: 8px 0 0 0; font-size: 12px; color: #999;">
									<?php printf( esc_html__( 'Created %s ago', 'wpshadow' ), esc_html( human_time_diff( $rule['created_at'] ) ) ); ?>
								</p>
							</div>
							<div style="display: flex; gap: 8px;">
								<button type="button" class="wps-btn wps-btn-secondary wps-edit-rule" data-rule-id="<?php echo esc_attr( $rule_id ); ?>" title="<?php esc_attr_e( 'Edit this rule', 'wpshadow' ); ?>">
									<span class="dashicons dashicons-edit"></span>
								</button>
								<button type="button" class="wps-btn wps-btn-secondary wps-delete-rule" data-rule-id="<?php echo esc_attr( $rule_id ); ?>" title="<?php esc_attr_e( 'Delete this rule', 'wpshadow' ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<!-- Modal Centering CSS -->
			<style>
				#wpshadow-notification-builder-modal {
					flex-direction: column;
					align-items: center;
					justify-content: center;
				}
				#wpshadow-notification-builder-modal.wps-modal {
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					z-index: 999999;
					background: rgba(0, 0, 0, 0.7);
				}
				#wpshadow-notification-builder-modal .wps-modal-content {
					width: 90%;
					max-width: 800px;
					margin: auto;
				}
			</style>

			<!-- Builder Modal -->
		<div id="wpshadow-notification-builder-modal" class="wps-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7);">
			<div class="wps-modal-content" style="background: #fff; padding: 40px; border-radius: 12px; max-width: 800px; width: 90%; position: relative; box-shadow: 0 8px 32px rgba(0,0,0,0.4); max-height: 85vh; overflow-y: auto; margin: auto;">
				<button type="button" class="wps-modal-close" style="position: absolute; top: 20px; right: 20px; background: transparent; border: none; font-size: 32px; cursor: pointer; color: #999; line-height: 1;">×</button>
					<h2 style="margin-top: 0; color: #0073aa;">
						<?php echo $mode === 'email' ? esc_html__( 'Create Email Rule', 'wpshadow' ) : esc_html__( 'Create Notification Rule', 'wpshadow' ); ?>
					</h2>

					<form id="wpshadow-notification-builder-form">
						<?php wp_nonce_field( 'wpshadow_notification_builder', 'wpshadow_notification_nonce' ); ?>
						<input type="hidden" name="wpshadow_builder_mode" value="<?php echo esc_attr( $mode ); ?>" />
						<input type="hidden" name="rule_id" value="" />

						<!-- Rule Name -->
						<div class="wps-form-group">
							<label class="wps-form-label">
								<?php esc_html_e( 'Rule Name', 'wpshadow' ); ?>
								<span style="color: #d32f2f;">*</span>
							</label>
							<input type="text" name="rule_name" class="wps-input" placeholder="<?php esc_attr_e( 'E.g., Alert on RSS Diagnostic', 'wpshadow' ); ?>" required />
							<p class="wps-form-help">
								<?php esc_html_e( 'A friendly name to identify this rule.', 'wpshadow' ); ?>
							</p>
						</div>

						<!-- Trigger Selection -->
						<div class="wps-form-group">
							<label class="wps-form-label">
								<?php esc_html_e( 'When This Happens', 'wpshadow' ); ?>
								<span style="color: #d32f2f;">*</span>
							</label>
						<p class="wps-form-help" style="margin-top: 4px; margin-bottom: 12px;">
							<?php esc_html_e( 'Choose from scheduled tasks, content events, system changes, or diagnostic test results.', 'wpshadow' ); ?>
						</p>
						<div id="wpshadow-trigger-categories" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 16px;">
							<?php foreach ( $triggers as $category_key => $category ) : ?>
								<button type="button" class="wps-trigger-category-btn" data-category="<?php echo esc_attr( $category_key ); ?>" style="padding: 14px; border: 2px solid #ddd; border-radius: 8px; background: #fff; cursor: pointer; text-align: left; transition: all 0.2s; font-size: 13px;">
									<span class="dashicons dashicons-<?php echo esc_attr( $category['icon'] ?? 'admin-generic' ); ?>" style="vertical-align: middle; margin-right: 6px; font-size: 18px; color: var(--wps-primary, #123456);"></span>
										<strong><?php echo esc_html( $category['label'] ); ?></strong>
									</button>
								<?php endforeach; ?>
							</div>
							<div id="wpshadow-trigger-items" style="display: none; padding: 16px; background: #f9f9f9; border-radius: 6px; margin-bottom: 16px;">
								<!-- Populated by JavaScript -->
							</div>
							<select name="trigger_type" id="wpshadow-trigger-select" class="wps-input" required style="display: none;">
								<option value="">-- <?php esc_html_e( 'Select a trigger', 'wpshadow' ); ?> --</option>
							</select>
						</div>

						<!-- Then Action Section -->
						<div style="padding: 12px; background: #e3f2fd; border-radius: 6px; margin: 20px 0; text-align: center;">
							<strong style="color: #1976d2;"><?php esc_html_e( 'Then', 'wpshadow' ); ?></strong>
						</div>

						<!-- Action Configuration -->
						<div class="wps-form-group">
							<label class="wps-form-label">
								<?php
								if ( $mode === 'email' ) {
									esc_html_e( 'Send Email', 'wpshadow' );
								} else {
									esc_html_e( 'Show Notification', 'wpshadow' );
								}
								?>
							</label>
							<div id="wpshadow-action-config" style="background: #fafafa; padding: 16px; border-radius: 6px;">
								<?php if ( $mode === 'email' ) : ?>
									<div class="wps-form-group">
										<label class="wps-form-label">
											<?php esc_html_e( 'Email Subject', 'wpshadow' ); ?>
											<span style="color: #d32f2f;">*</span>
										</label>
										<input type="text" name="action_subject" class="wps-input" placeholder="<?php esc_attr_e( 'E.g., RSS Diagnostic Test Found an Issue', 'wpshadow' ); ?>" required />
										<p class="wps-form-help">
											<?php esc_html_e( 'Use {trigger_name}, {severity} for variables', 'wpshadow' ); ?>
										</p>
									</div>

									<div class="wps-form-group">
										<label class="wps-form-label">
											<?php esc_html_e( 'Email Message', 'wpshadow' ); ?>
											<span style="color: #d32f2f;">*</span>
										</label>
										<textarea name="action_message" class="wps-input" rows="6" placeholder="<?php esc_attr_e( 'Enter email body...', 'wpshadow' ); ?>" required></textarea>
										<p class="wps-form-help">
											<?php esc_html_e( 'Use {trigger_name}, {severity}, {details} for variables', 'wpshadow' ); ?>
										</p>
									</div>
								<?php else : ?>
									<div class="wps-form-group">
										<label class="wps-form-label">
											<?php esc_html_e( 'Notification Message', 'wpshadow' ); ?>
											<span style="color: #d32f2f;">*</span>
										</label>
										<textarea name="action_message" class="wps-input" rows="4" placeholder="<?php esc_attr_e( 'Enter notification message...', 'wpshadow' ); ?>" required></textarea>
										<p class="wps-form-help">
											<?php esc_html_e( 'Use {trigger_name}, {severity} for variables', 'wpshadow' ); ?>
										</p>
									</div>

									<div class="wps-form-group">
										<label class="wps-form-label">
											<?php esc_html_e( 'Notification Style', 'wpshadow' ); ?>
										</label>
										<select name="action_style" class="wps-input">
											<option value="info"><?php esc_html_e( 'Info (Blue)', 'wpshadow' ); ?></option>
											<option value="success"><?php esc_html_e( 'Success (Green)', 'wpshadow' ); ?></option>
											<option value="warning"><?php esc_html_e( 'Warning (Orange)', 'wpshadow' ); ?></option>
											<option value="error"><?php esc_html_e( 'Error (Red)', 'wpshadow' ); ?></option>
										</select>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Form Actions -->
						<div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
							<button type="button" class="wps-btn wps-btn-secondary wps-modal-close">
								<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
							</button>
							<button type="submit" class="wps-btn wps-btn-primary">
								<?php esc_html_e( 'Save Rule', 'wpshadow' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			const mode = '<?php echo esc_js( $mode ); ?>';
			const triggers = <?php echo wp_json_encode( $triggers ); ?>;

			// Populate triggers
			function populateTriggerItems(category) {
				const categoryData = triggers[category];
				const itemsHtml = Object.entries(categoryData.triggers || {}).map(([key, trigger]) => {
					return `<label style="display: block; margin: 8px 0; cursor: pointer; padding: 12px; background: #fff; border: 1px solid #ddd; border-radius: 6px; transition: all 0.2s;">
						<input type="radio" name="trigger_type" value="${key}" class="wps-trigger-item" style="margin-right: 8px;" />
						<strong style="font-size: 14px;">${trigger.label}</strong>
						<p style="margin: 4px 0 0 24px; font-size: 12px; color: #666; line-height: 1.5;">${trigger.description}</p>
					</label>`;
				}).join('');

				$('#wpshadow-trigger-items').html(itemsHtml).show();
				
				// Update hidden select
				$('#wpshadow-trigger-select').html(
					'<option value="">-- Select a trigger --</option>' +
					Object.entries(categoryData.triggers || {}).map(([key, trigger]) => {
						return `<option value="${key}">${trigger.label}</option>`;
					}).join('')
				);

				// Highlight selected category
				$('.wps-trigger-category-btn').removeClass('wps-selected').css({
					'background': '#fff',
					'border-color': '#ddd',
					'border-width': '2px'
				});
				$(`[data-category="${category}"]`).addClass('wps-selected').css({
					'background': '#e3f2fd',
					'border-color': 'var(--wps-primary, #2196f3)',
					'border-width': '2px'
				});
			}

			// Category button handlers
			$('.wps-trigger-category-btn').on('click', function(e) {
				e.preventDefault();
				const category = $(this).data('category');
				populateTriggerItems(category);
			});

			// Trigger selection
			$(document).on('change', '.wps-trigger-item', function() {
				$('#wpshadow-trigger-select').val($(this).val());
			});

			// Create new rule
			$('#wpshadow-create-notification-rule').on('click', function() {
				$('#wpshadow-notification-builder-form')[0].reset();
				$('input[name="rule_id"]').val('');
				$('.wps-trigger-category-btn').first().click();
				$('#wpshadow-notification-builder-modal').css('display', 'flex').hide().fadeIn(200);
			});

			// Close modal
			$('.wps-modal-close').on('click', function() {
				$('#wpshadow-notification-builder-modal').fadeOut(200);
			});

			// Submit form
			$('#wpshadow-notification-builder-form').on('submit', function(e) {
				e.preventDefault();
				
				const formData = {
					action: 'wpshadow_save_notification_rule',
					nonce: $('input[name="wpshadow_notification_nonce"]').val(),
					mode: mode,
					rule_id: $('input[name="rule_id"]').val(),
					name: $('input[name="rule_name"]').val(),
					trigger_type: $('select[name="trigger_type"]').val(),
					action_message: $('textarea[name="action_message"]').val(),
					action_subject: $('input[name="action_subject"]').val(),
					action_style: $('select[name="action_style"]').val()
				};

				$.post(ajaxurl, formData, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data?.message || 'Error saving rule');
					}
				});
			});

			// Delete rule
			$(document).on('click', '.wps-delete-rule', function() {
				if (!confirm('<?php esc_js( __( 'Are you sure?', 'wpshadow' ) ); ?>')) {
					return;
				}

				const ruleId = $(this).data('rule-id');
				$.post(ajaxurl, {
					action: 'wpshadow_delete_notification_rule',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_notification_builder' ); ?>',
					mode: mode,
					rule_id: ruleId
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data?.message || 'Error deleting rule');
					}
				});
			});
		});
		</script>
		<?php
	}
}
