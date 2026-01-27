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
		<div class="wps-privacy-container">
			<!-- Consent Management -->
			<div class="wps-p-24-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-info" class="wps-privacy-icon"></span>
					<h3 class="wps-m-0"><?php esc_html_e( 'User Consent', 'wpshadow' ); ?></h3>
				</div>
				<p class="wps-m-0">
					<?php esc_html_e( 'Configure how WPShadow requests user consent for data processing.', 'wpshadow' ); ?>
				</p>
				
				<form class="wpshadow-privacy-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_privacy_settings_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_privacy_settings" />
					
					<!-- Require consent toggle -->
					<div class="wps-flex-gap-12-items-flex-start">
						<input type="checkbox" name="consent_required" <?php checked( $settings['consent_required'] ); ?> id="consent-required" class="wps-checkbox-large" />
						<div class="wps-privacy-flex-container">
							<label for="consent-required" class="wps-block">
								<?php esc_html_e( 'Require explicit user consent before processing data', 'wpshadow' ); ?>
							</label>
							<p class="wps-m-4">
								<?php esc_html_e( 'When enabled, users must explicitly consent before WPShadow collects or processes their data.', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
					
					<!-- Analytics collection toggle -->
					<div class="wps-flex-gap-12-items-flex-start">
						<input type="checkbox" name="collect_analytics" <?php checked( $settings['collect_analytics'] ); ?> id="collect-analytics" class="wps-checkbox-large" />
						<div class="wps-privacy-flex-container">
							<label for="collect-analytics" class="wps-block">
								<?php esc_html_e( 'Allow anonymized analytics collection', 'wpshadow' ); ?>
							</label>
							<p class="wps-m-4">
								<?php esc_html_e( 'Help improve WPShadow by sharing anonymized usage data (no personal information is collected).', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
					
					<!-- Data retention -->
					<div class="wps-privacy-section">
						<label class="wps-block">
							<?php esc_html_e( 'Data Retention Period (days):', 'wpshadow' ); ?>
						</label>
						<input type="number" name="data_retention_days" value="<?php echo esc_attr( $settings['data_retention_days'] ); ?>" min="7" max="730" class="wps-p-8-rounded-4" />
						<p class="wps-m-6">
							<?php esc_html_e( 'Activity logs and personal data will be automatically deleted after this period (7-730 days).', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Export/Delete options -->
					<fieldset class="wps-p-12-rounded-4">
						<legend class="wps-privacy-legend"><?php esc_html_e( 'Data Subject Rights (GDPR)', 'wpshadow' ); ?></legend>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="export_user_data" <?php checked( $settings['export_user_data'] ); ?> id="export-user-data" class="wps-checkbox-large" />
							<div class="wps-privacy-flex-container">
								<label for="export-user-data" class="wps-block">
									<?php esc_html_e( 'Allow data export (Right to Access)', 'wpshadow' ); ?>
								</label>
								<p class="wps-m-2">
									<?php esc_html_e( 'Users can request a copy of their personal data.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="delete_user_data" <?php checked( $settings['delete_user_data'] ); ?> id="delete-user-data" class="wps-checkbox-large" />
							<div class="wps-privacy-flex-container">
								<label for="delete-user-data" class="wps-block">
									<?php esc_html_e( 'Allow data deletion (Right to be Forgotten)', 'wpshadow' ); ?>
								</label>
								<p class="wps-m-2">
									<?php esc_html_e( 'Users can request deletion of their personal data.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="anonymize_on_delete" <?php checked( $settings['anonymize_on_delete'] ); ?> id="anonymize-on-delete" class="wps-checkbox-large" />
							<div class="wps-privacy-flex-container">
								<label for="anonymize-on-delete" class="wps-block">
									<?php esc_html_e( 'Anonymize instead of delete', 'wpshadow' ); ?>
								</label>
								<p class="wps-m-2">
									<?php esc_html_e( 'When deleting data, anonymize it instead of permanently removing it (retains historical records).', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</fieldset>
					
					<!-- Save Button -->
					<button type="submit" class="wps-btn wps-btn-primary">
						<?php esc_html_e( 'Save Privacy Settings', 'wpshadow' ); ?>
					</button>
					<span id="wpshadow-privacy-status" class="wps-privacy-status"></span>
				</form>
			</div>

			<!-- Privacy Info Box -->
			<div class="wps-p-16-rounded-8">
				<p class="wps-m-0">
					🔒 <?php esc_html_e( 'Privacy First', 'wpshadow' ); ?>
				</p>
				<p class="wps-m-8">
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
						$status.html('<span class="wps-status-success">✓ <?php echo esc_js( __( 'Saved', 'wpshadow' ) ); ?></span>');
					} else {
						$status.html('<span class="wps-status-error">✗ ' + (response.data.message || '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>') + '</span>');
					}
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Privacy Settings', 'wpshadow' ) ); ?>');
				});
			});
		});
		</script>
		<?php
	}
}
