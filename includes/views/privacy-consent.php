<?php
/**
 * Privacy & Consent Settings View
 *
 * @package WPShadow
 */

declare(strict_types=1);

use WPShadow\Privacy\Consent_Preferences;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = get_current_user_id();
$prefs        = Consent_Preferences::get_preferences( $current_user );
$nonce        = wp_create_nonce( 'wpshadow_consent' );
$ajax_url     = admin_url( 'admin-ajax.php' );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Privacy & Consent', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Control how WPShadow collects anonymized analytics. Essential functions and error reporting are always on to keep the plugin working reliably.', 'wpshadow' ); ?></p>

	<div style="max-width: 900px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-top: 20px;">
		<div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
			<h2 style="margin-top: 0;"><?php esc_html_e( 'Consent Preferences', 'wpshadow' ); ?></h2>
			<p style="color: #555; font-size: 13px; margin-bottom: 12px;">
				<?php esc_html_e( 'Anonymous analytics help us understand which features save you the most time. No personal data, IPs, or passwords are ever collected.', 'wpshadow' ); ?>
			</p>

			<label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 12px;">
				<input type="checkbox" id="wpshadow-telemetry" <?php checked( ! empty( $prefs['anonymized_telemetry'] ) ); ?> />
				<div>
					<strong><?php esc_html_e( 'Anonymous Analytics (optional)', 'wpshadow' ); ?></strong>
					<p style="margin: 4px 0 0; color: #666; font-size: 12px;">
						<?php esc_html_e( 'Share anonymized feature usage so we can improve WPShadow. Turn off anytime.', 'wpshadow' ); ?>
					</p>
				</div>
			</label>

			<div style="font-size: 12px; color: #555; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; margin-bottom: 12px;">
				<strong><?php esc_html_e( 'Always on for reliability:', 'wpshadow' ); ?></strong>
				<ul style="margin: 6px 0 0 16px; list-style: disc;">
					<li><?php esc_html_e( 'Essential functions required for WPShadow to run', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Error reporting without personal data', 'wpshadow' ); ?></li>
				</ul>
			</div>

			<div style="display: flex; gap: 10px; align-items: center;">
				<button id="wpshadow-save-consent" class="button button-primary">
					<?php esc_html_e( 'Save preferences', 'wpshadow' ); ?>
				</button>
				<button id="wpshadow-dismiss-consent" class="button">
					<?php esc_html_e( 'Snooze for 30 days', 'wpshadow' ); ?>
				</button>
				<a href="https://wpshadow.com/privacy/?utm_source=wpshadow&utm_medium=plugin&utm_campaign=consent" target="_blank" style="font-size: 12px; text-decoration: none; color: #2563eb; font-weight: 600;">
					<?php esc_html_e( 'Read our privacy approach', 'wpshadow' ); ?> →
				</a>
			</div>

			<div id="wpshadow-consent-status" style="margin-top: 12px; font-size: 12px; color: #555;"></div>
		</div>

		<div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
			<h2 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-shield"></span>
				<?php esc_html_e( 'What we collect (summary)', 'wpshadow' ); ?>
			</h2>
			<ul style="margin: 0 0 12px 18px; color: #555; line-height: 1.5;">
				<li><?php esc_html_e( 'Plugin usage counts (features clicked, flows completed)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Anonymized environment data (PHP/WordPress versions, no IPs)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Error events (no content or personal data)', 'wpshadow' ); ?></li>
			</ul>
			<p style="margin: 0; color: #444; font-weight: 600;">
				<?php esc_html_e( 'You are always in control. Turn anonymous analytics on or off anytime.', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
</div>

<script>
(function($){
	$(function(){
		var ajaxUrl = '<?php echo esc_js( $ajax_url ); ?>';
		var nonce = '<?php echo esc_js( $nonce ); ?>';
		var $status = $('#wpshadow-consent-status');

		$('#wpshadow-save-consent').on('click', function(e){
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
			$status.text('');

			$.post(ajaxUrl, {
				action: 'wpshadow_save_consent',
				nonce: nonce,
				telemetry: $('#wpshadow-telemetry').prop('checked')
			}, function(response){
				if (response && response.success) {
					$status.text(response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Preferences saved.', 'wpshadow' ) ); ?>');
				} else {
					$status.text(response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Could not save preferences.', 'wpshadow' ) ); ?>');
				}
				$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save preferences', 'wpshadow' ) ); ?>');
			});
		});

		$('#wpshadow-dismiss-consent').on('click', function(e){
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Snoozing...', 'wpshadow' ) ); ?>');
			$status.text('');

			$.post(ajaxUrl, {
				action: 'wpshadow_dismiss_consent',
				nonce: nonce
			}, function(response){
				$status.text(response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Consent prompt snoozed for 30 days.', 'wpshadow' ) ); ?>');
				$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Snooze for 30 days', 'wpshadow' ) ); ?>');
			});
		});
	});
})(jQuery);
</script>
