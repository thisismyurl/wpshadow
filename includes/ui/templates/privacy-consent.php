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

<div class="wrap wps-page-container">
	<?php
	wpshadow_render_page_header(
		__( 'Privacy & Consent', 'wpshadow' ),
		__( 'Control how WPShadow collects anonymized analytics. Essential functions and error reporting are always on to keep the plugin working reliably.', 'wpshadow' ),
		'dashicons-shield'
	);
	?>

		<div class="wps-grid wps-grid-auto-260 wps-gap-4 wps-mt-4 wps-max-w-900">
		<div class="wps-card wps-mb-0">
			<h2 class="wps-mt-0"><?php esc_html_e( 'Consent Preferences', 'wpshadow' ); ?></h2>
			<p class="wps-text-sm wps-text-gray-600 wps-mb-3">
				<?php esc_html_e( 'Anonymous analytics help us understand which features save you the most time. No personal data, IPs, or passwords are ever collected.', 'wpshadow' ); ?>
			</p>

			<label class="wps-flex-gap-10-items-flex-start-p-10-rounde">
				<input type="checkbox" id="wpshadow-telemetry" <?php checked( ! empty( $prefs['anonymized_telemetry'] ) ); ?> />
				<div>
					<strong><?php esc_html_e( 'Anonymous Analytics (optional)', 'wpshadow' ); ?></strong>
					<p class="wps-m-4">
						<?php esc_html_e( 'Share anonymized feature usage so we can improve WPShadow. Turn off anytime.', 'wpshadow' ); ?>
					</p>
				</div>
			</label>

			<div class="wps-p-10-rounded-6">
				<strong><?php esc_html_e( 'Always on for reliability:', 'wpshadow' ); ?></strong>
				<ul class="wps-m-6">
					<li><?php esc_html_e( 'Essential functions required for WPShadow to run', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Error reporting without personal data', 'wpshadow' ); ?></li>
				</ul>
			</div>

			<div class="wps-flex-gap-10-items-center">
				<button id="wpshadow-save-consent" class="wps-btn wps-btn-primary" aria-label="<?php esc_attr_e( 'Save your WPShadow privacy and consent preferences', 'wpshadow' ); ?>">
					<?php esc_html_e( 'Save preferences', 'wpshadow' ); ?>
				</button>
				<button id="wpshadow-dismiss-consent" class="wps-btn wps-btn-secondary" aria-label="<?php esc_attr_e( 'Snooze the consent prompt for 30 days', 'wpshadow' ); ?>">
					<?php esc_html_e( 'Snooze for 30 days', 'wpshadow' ); ?>
				</button>
				<a href="https://wpshadow.com/privacy/?utm_source=wpshadow&utm_medium=plugin&utm_campaign=consent" target="_blank" class="wps-text-xs wps-no-underline wps-text-blue-600 wps-font-600">
					<?php esc_html_e( 'Read our privacy approach', 'wpshadow' ); ?> →
				</a>
			</div>

			<div id="wpshadow-consent-status" class="wps-mt-3 wps-text-xs wps-text-gray-600" role="status" aria-live="polite"></div>
		</div>

		<div class="wps-card wps-mb-0">
			<h2 class="wps-flex-gap-8-items-center">
				<span class="dashicons dashicons-shield"></span>
				<?php esc_html_e( 'What we collect (summary)', 'wpshadow' ); ?>
			</h2>
			<ul class="wps-m-0">
				<li><?php esc_html_e( 'Plugin usage counts (features clicked, flows completed)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Anonymized environment data (PHP/WordPress versions, no IPs)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Error events (no content or personal data)', 'wpshadow' ); ?></li>
			</ul>
			<p class="wps-m-0">
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
