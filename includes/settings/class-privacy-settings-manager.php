<?php
declare(strict_types=1);

namespace WPShadow\Settings;

/**
 * Privacy Settings Manager
 *
 * Manages privacy, consent, and GDPR compliance settings.
 * Philosophy: Beyond Pure (#10) - Privacy-first, transparent, consent-based
 * Philosophy: Show Value (#9) - Track user preferences
 *
 * @since 1.2601
 * @package WPShadow
 */
class Privacy_Settings_Manager {

	/**
	 * Option key for privacy settings
	 */
	const OPTION_KEY = 'wpshadow_privacy_settings';

	/**
	 * Get all privacy settings with defaults
	 *
	 * @return array Privacy settings
	 */
	public static function get_all_settings() {
		return get_option(
			self::OPTION_KEY,
			array(
				'consent_required'      => true,
				'collect_analytics'     => false,
				'allow_data_processing' => false,
				'data_retention_days'   => 90,
				'export_user_data'      => true,
				'delete_user_data'      => true,
				'anonymize_on_delete'   => true,
			)
		);
	}

	/**
	 * Update privacy setting
	 *
	 * @param string $key Setting key
	 * @param mixed  $value Setting value
	 * @return bool Success status
	 */
	public static function update_setting( $key, $value ) {
		if ( empty( $key ) ) {
			return false;
		}

		$settings         = self::get_all_settings();
		$settings[ $key ] = $value;

		$result = update_option( self::OPTION_KEY, $settings );

		// Log activity
		if ( $result && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'privacy_setting_updated',
				sprintf( 'Privacy setting updated: %s', $key ),
				'',
				array( 'setting_key' => $key )
			);
		}

		return $result;
	}

	/**
	 * Record user consent
	 *
	 * @param int   $user_id User ID
	 * @param bool  $analytics User consented to analytics
	 * @param bool  $processing User consented to data processing
	 * @return bool Success status
	 */
	public static function record_user_consent( $user_id, $analytics = false, $processing = false ) {
		$consent_data = array(
			'timestamp'  => current_time( 'timestamp' ),
			'user_id'    => $user_id,
			'analytics'  => $analytics,
			'processing' => $processing,
			'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
		);

		return update_user_meta( $user_id, 'wpshadow_privacy_consent', $consent_data );
	}

	/**
	 * Get user consent record
	 *
	 * @param int $user_id User ID
	 * @return array|false Consent data or false if not found
	 */
	public static function get_user_consent( $user_id ) {
		return get_user_meta( $user_id, 'wpshadow_privacy_consent', true );
	}

	/**
	 * Render privacy settings UI
	 *
	 * Philosophy: Inspire Confidence (#8) - Clear, transparent settings
	 *
	 * @return void
	 */
	public static function render_privacy_ui() {
		$settings = self::get_all_settings();
		?>
		<div style="max-width: 900px;">
			<!-- Consent Management -->
			<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; margin-bottom: 20px;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
					<span class="dashicons dashicons-info" style="font-size: 24px; color: #0073aa;"></span>
					<h3 style="margin: 0;"><?php esc_html_e( 'User Consent', 'wpshadow' ); ?></h3>
				</div>
				<p style="color: #666; margin: 0 0 16px 0;">
					<?php esc_html_e( 'Configure how WPShadow requests user consent for data processing.', 'wpshadow' ); ?>
				</p>
				
				<form class="wpshadow-privacy-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_privacy_settings_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_privacy_settings" />
					
					<!-- Require consent toggle -->
					<div style="margin-bottom: 16px; display: flex; align-items: flex-start; gap: 12px;">
						<input type="checkbox" name="consent_required" <?php checked( $settings['consent_required'] ); ?> id="consent-required" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
						<div style="flex: 1;">
							<label for="consent-required" style="cursor: pointer; font-weight: 500; display: block;">
								<?php esc_html_e( 'Require explicit user consent before processing data', 'wpshadow' ); ?>
							</label>
							<p style="font-size: 12px; color: #666; margin: 4px 0 0 0;">
								<?php esc_html_e( 'When enabled, users must explicitly consent before WPShadow collects or processes their data.', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
					
					<!-- Analytics collection toggle -->
					<div style="margin-bottom: 16px; display: flex; align-items: flex-start; gap: 12px;">
						<input type="checkbox" name="collect_analytics" <?php checked( $settings['collect_analytics'] ); ?> id="collect-analytics" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
						<div style="flex: 1;">
							<label for="collect-analytics" style="cursor: pointer; font-weight: 500; display: block;">
								<?php esc_html_e( 'Allow anonymized analytics collection', 'wpshadow' ); ?>
							</label>
							<p style="font-size: 12px; color: #666; margin: 4px 0 0 0;">
								<?php esc_html_e( 'Help improve WPShadow by sharing anonymized usage data (no personal information is collected).', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
					
					<!-- Data retention -->
					<div style="margin-bottom: 16px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 500;">
							<?php esc_html_e( 'Data Retention Period (days):', 'wpshadow' ); ?>
						</label>
						<input type="number" name="data_retention_days" value="<?php echo esc_attr( $settings['data_retention_days'] ); ?>" min="7" max="730" style="width: 100%; max-width: 200px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
						<p style="font-size: 12px; color: #666; margin: 6px 0 0 0;">
							<?php esc_html_e( 'Activity logs and personal data will be automatically deleted after this period (7-730 days).', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Export/Delete options -->
					<fieldset style="margin-bottom: 16px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px;">
						<legend style="font-weight: 500;"><?php esc_html_e( 'Data Subject Rights (GDPR)', 'wpshadow' ); ?></legend>
						
						<div style="margin-top: 12px; display: flex; align-items: flex-start; gap: 12px;">
							<input type="checkbox" name="export_user_data" <?php checked( $settings['export_user_data'] ); ?> id="export-user-data" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<div style="flex: 1;">
								<label for="export-user-data" style="cursor: pointer; font-weight: 500; display: block;">
									<?php esc_html_e( 'Allow data export (Right to Access)', 'wpshadow' ); ?>
								</label>
								<p style="font-size: 12px; color: #666; margin: 2px 0 0 0;">
									<?php esc_html_e( 'Users can request a copy of their personal data.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
						
						<div style="margin-top: 12px; display: flex; align-items: flex-start; gap: 12px;">
							<input type="checkbox" name="delete_user_data" <?php checked( $settings['delete_user_data'] ); ?> id="delete-user-data" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<div style="flex: 1;">
								<label for="delete-user-data" style="cursor: pointer; font-weight: 500; display: block;">
									<?php esc_html_e( 'Allow data deletion (Right to be Forgotten)', 'wpshadow' ); ?>
								</label>
								<p style="font-size: 12px; color: #666; margin: 2px 0 0 0;">
									<?php esc_html_e( 'Users can request deletion of their personal data.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
						
						<div style="margin-top: 12px; display: flex; align-items: flex-start; gap: 12px;">
							<input type="checkbox" name="anonymize_on_delete" <?php checked( $settings['anonymize_on_delete'] ); ?> id="anonymize-on-delete" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<div style="flex: 1;">
								<label for="anonymize-on-delete" style="cursor: pointer; font-weight: 500; display: block;">
									<?php esc_html_e( 'Anonymize instead of delete', 'wpshadow' ); ?>
								</label>
								<p style="font-size: 12px; color: #666; margin: 2px 0 0 0;">
									<?php esc_html_e( 'When deleting data, anonymize it instead of permanently removing it (retains historical records).', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</fieldset>
					
					<!-- Save Button -->
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save Privacy Settings', 'wpshadow' ); ?>
					</button>
					<span id="wpshadow-privacy-status" style="margin-left: 10px;"></span>
				</form>
			</div>

			<!-- Privacy Info Box -->
			<div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 16px;">
				<p style="margin: 0; color: #1565c0; font-weight: 500;">
					🔒 <?php esc_html_e( 'Privacy First', 'wpshadow' ); ?>
				</p>
				<p style="margin: 8px 0 0 0; font-size: 12px; color: #0d47a1;">
					<?php esc_html_e( 'WPShadow is built on privacy-first principles. All your settings and data are stored locally on your site. No personal information is sent anywhere without your explicit consent.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.wpshadow-privacy-form').on('submit', function(e) {
				e.preventDefault();
				var $form = $(this);
				var $btn = $form.find('button[type="submit"]');
				var $status = $('#wpshadow-privacy-status');
				
				var data = {
					action: 'wpshadow_update_privacy_settings',
					nonce: $form.find('input[name="_wpnonce"]').val(),
					consent_required: $form.find('input[name="consent_required"]').prop('checked'),
					collect_analytics: $form.find('input[name="collect_analytics"]').prop('checked'),
					data_retention_days: $form.find('input[name="data_retention_days"]').val(),
					export_user_data: $form.find('input[name="export_user_data"]').prop('checked'),
					delete_user_data: $form.find('input[name="delete_user_data"]').prop('checked'),
					anonymize_on_delete: $form.find('input[name="anonymize_on_delete"]').prop('checked'),
				};
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				$status.html('');
				
				$.post(ajaxurl, data, function(response) {
					if (response.success) {
						$status.html('<span style="color: #2e7d32;">✓ <?php echo esc_js( __( 'Saved', 'wpshadow' ) ); ?></span>');
					} else {
						$status.html('<span style="color: #c62828;">✗ ' + (response.data.message || '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>') + '</span>');
					}
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Privacy Settings', 'wpshadow' ) ); ?>');
				});
			});
		});
		</script>
		<?php
	}
}
