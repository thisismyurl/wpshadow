<?php
/**
 * Registration Page View
 *
 * Provides seamless registration experience with pre-populated user details,
 * newsletter opt-in, and AJAX registration flow.
 *
 * @package WP_Support
 * @since   1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current user information for pre-population.
$current_user = wp_get_current_user();
$site_title   = get_bloginfo( 'name' );
$site_url     = home_url();
$admin_email  = get_option( 'admin_email' );

// Get license status to show appropriate messaging.
$license_state = WPS_License::get_state( false );
$is_licensed   = ( 'valid' === $license_state['status'] );
?>

<div class="wrap wps-register-page">
	<h1><?php esc_html_e( 'Register WordPress Support', 'plugin-wp-support-thisismyurl' ); ?></h1>
	
	<?php if ( $is_licensed ) : ?>
		<div class="notice notice-success">
			<p><strong><?php esc_html_e( 'Your site is already registered!', 'plugin-wp-support-thisismyurl' ); ?></strong></p>
			<p><?php esc_html_e( 'You have an active license and are receiving updates and support.', 'plugin-wp-support-thisismyurl' ); ?></p>
		</div>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&WPS_tab=settings' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Manage License', 'plugin-wp-support-thisismyurl' ); ?>
			</a>
		</p>
	<?php else : ?>
		<!-- Registration Benefits Section -->
		<div class="wps-register-benefits">
			<div class="wps-benefits-intro">
				<h2><?php esc_html_e( 'Why Register?', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Registration is free and provides you with essential benefits to keep your WordPress site secure and optimized.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
			</div>

			<div class="wps-benefits-grid">
				<div class="wps-benefit-card">
					<span class="dashicons dashicons-update"></span>
					<h3><?php esc_html_e( 'Automatic Updates', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<p><?php esc_html_e( 'Receive automatic plugin updates with the latest features, security patches, and performance improvements.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>

				<div class="wps-benefit-card">
					<span class="dashicons dashicons-shield"></span>
					<h3><?php esc_html_e( 'Security Notifications', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<p><?php esc_html_e( 'Get immediate alerts about security vulnerabilities and critical issues affecting your WordPress installation.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>

				<div class="wps-benefit-card">
					<span class="dashicons dashicons-sos"></span>
					<h3><?php esc_html_e( 'Priority Support', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<p><?php esc_html_e( 'Access to dedicated support team for troubleshooting, questions, and technical assistance.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>

				<div class="wps-benefit-card">
					<span class="dashicons dashicons-admin-plugins"></span>
					<h3><?php esc_html_e( 'Premium Features', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<p><?php esc_html_e( 'Unlock advanced features including performance optimization, backup verification, and site documentation tools.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Registration Form -->
		<div class="wps-register-form-container">
			<h2><?php esc_html_e( 'Create Your Free Account', 'plugin-wp-support-thisismyurl' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Fill in the details below to register your site. Your information is pre-populated from your WordPress settings.', 'plugin-wp-support-thisismyurl' ); ?>
			</p>

			<form id="wps-register-form" method="post" action="">
				<?php wp_nonce_field( 'wps_register_site', 'wps_register_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="wps_site_name"><?php esc_html_e( 'Site Name', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input type="text" id="wps_site_name" name="wps_site_name" 
									   value="<?php echo esc_attr( $site_title ); ?>" 
									   class="regular-text" required />
								<p class="description"><?php esc_html_e( 'The name of your WordPress site.', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="wps_site_url"><?php esc_html_e( 'Site URL', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input type="url" id="wps_site_url" name="wps_site_url" 
									   value="<?php echo esc_url( $site_url ); ?>" 
									   class="regular-text" required readonly />
								<p class="description"><?php esc_html_e( 'Your WordPress site URL (cannot be changed).', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="wps_admin_name"><?php esc_html_e( 'Your Name', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input type="text" id="wps_admin_name" name="wps_admin_name" 
									   value="<?php echo esc_attr( $current_user->display_name ); ?>" 
									   class="regular-text" required />
								<p class="description"><?php esc_html_e( 'Your full name or display name.', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="wps_admin_email"><?php esc_html_e( 'Email Address', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input type="email" id="wps_admin_email" name="wps_admin_email" 
									   value="<?php echo esc_attr( $current_user->user_email ); ?>" 
									   class="regular-text" required />
								<p class="description"><?php esc_html_e( 'We will use this email to send you updates and important notifications.', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php esc_html_e( 'Email Preferences', 'plugin-wp-support-thisismyurl' ); ?>
							</th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<?php esc_html_e( 'Email Preferences', 'plugin-wp-support-thisismyurl' ); ?>
									</legend>
									<label for="wps_opt_in_updates">
										<input type="checkbox" id="wps_opt_in_updates" name="wps_opt_in_updates" value="1" checked />
										<?php esc_html_e( 'Receive plugin update notifications (recommended)', 'plugin-wp-support-thisismyurl' ); ?>
									</label>
									<br />
									<label for="wps_opt_in_security">
										<input type="checkbox" id="wps_opt_in_security" name="wps_opt_in_security" value="1" checked />
										<?php esc_html_e( 'Receive security alerts and important announcements (recommended)', 'plugin-wp-support-thisismyurl' ); ?>
									</label>
									<br />
									<label for="wps_opt_in_newsletter">
										<input type="checkbox" id="wps_opt_in_newsletter" name="wps_opt_in_newsletter" value="1" />
										<?php esc_html_e( 'Subscribe to newsletter for WordPress tips and best practices', 'plugin-wp-support-thisismyurl' ); ?>
									</label>
									<br />
									<label for="wps_opt_in_marketing">
										<input type="checkbox" id="wps_opt_in_marketing" name="wps_opt_in_marketing" value="1" />
										<?php esc_html_e( 'Receive promotional offers and product announcements', 'plugin-wp-support-thisismyurl' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php esc_html_e( 'Terms & Privacy', 'plugin-wp-support-thisismyurl' ); ?>
							</th>
							<td>
								<label for="wps_agree_terms">
									<input type="checkbox" id="wps_agree_terms" name="wps_agree_terms" value="1" required />
									<?php
									printf(
										/* translators: %1$s: terms URL, %2$s: privacy URL */
										wp_kses_post( __( 'I agree to the <a href="%1$s" target="_blank">Terms of Service</a> and <a href="%2$s" target="_blank">Privacy Policy</a>', 'plugin-wp-support-thisismyurl' ) ),
										esc_url( 'https://thisismyurl.com/terms' ),
										esc_url( 'https://thisismyurl.com/privacy' )
									);
									?>
								</label>
							</td>
						</tr>
					</tbody>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary button-hero" id="wps-register-submit">
						<?php esc_html_e( 'Register My Site', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
					<span class="spinner" style="float: none; margin-left: 10px;"></span>
				</p>

				<div id="wps-register-message" class="wps-register-message"></div>
			</form>

			<div class="wps-register-footer">
				<p>
					<strong><?php esc_html_e( 'Already have a license key?', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&WPS_tab=settings' ) ); ?>">
						<?php esc_html_e( 'Enter it in settings instead', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
				</p>
			</div>
		</div>
	<?php endif; ?>
</div>

<style>
.wps-register-page {
	max-width: 1200px;
	margin: 20px auto;
}

.wps-register-benefits {
	background: #fff;
	padding: 30px;
	margin: 20px 0;
	border: 1px solid #ddd;
	border-radius: 4px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.wps-benefits-intro {
	text-align: center;
	margin-bottom: 30px;
}

.wps-benefits-intro h2 {
	margin-bottom: 10px;
	font-size: 24px;
}

.wps-benefits-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin-top: 30px;
}

.wps-benefit-card {
	padding: 20px;
	background: #f8f9fa;
	border: 1px solid #e5e5e5;
	border-radius: 4px;
	text-align: center;
	transition: transform 0.2s, box-shadow 0.2s;
}

.wps-benefit-card:hover {
	transform: translateY(-3px);
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.wps-benefit-card .dashicons {
	font-size: 48px;
	width: 48px;
	height: 48px;
	color: #2271b1;
	margin-bottom: 15px;
}

.wps-benefit-card h3 {
	font-size: 16px;
	margin: 15px 0 10px;
	color: #1d2327;
}

.wps-benefit-card p {
	font-size: 14px;
	color: #646970;
	line-height: 1.6;
	margin: 0;
}

.wps-register-form-container {
	background: #fff;
	padding: 30px;
	margin: 20px 0;
	border: 1px solid #ddd;
	border-radius: 4px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.wps-register-form-container h2 {
	margin-top: 0;
	font-size: 22px;
}

.wps-register-form-container .form-table th {
	width: 200px;
}

.wps-register-message {
	margin-top: 20px;
	padding: 12px;
	border-radius: 4px;
	display: none;
}

.wps-register-message.success {
	display: block;
	background: #d7f4d7;
	border-left: 4px solid #46b450;
	color: #2c662d;
}

.wps-register-message.error {
	display: block;
	background: #fcf0f1;
	border-left: 4px solid #d63638;
	color: #b32d2e;
}

.wps-register-footer {
	margin-top: 30px;
	padding-top: 20px;
	border-top: 1px solid #ddd;
	text-align: center;
}

#wps-register-submit:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.spinner.is-active {
	visibility: visible;
	display: inline-block;
}
</style>

<script>
jQuery(document).ready(function($) {
	'use strict';

	$('#wps-register-form').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $submit = $('#wps-register-submit');
		var $spinner = $form.find('.spinner');
		var $message = $('#wps-register-message');

		// Validate terms checkbox
		if (!$('#wps_agree_terms').is(':checked')) {
			$message.removeClass('success').addClass('error')
				.text('<?php echo esc_js( __( 'You must agree to the Terms of Service and Privacy Policy to register.', 'plugin-wp-support-thisismyurl' ) ); ?>')
				.show();
			return;
		}

		// Disable submit button and show spinner
		$submit.prop('disabled', true);
		$spinner.addClass('is-active');
		$message.hide();

		// Prepare form data
		var formData = {
			action: 'wps_register_site',
			nonce: $('#wps_register_nonce').val(),
			site_name: $('#wps_site_name').val(),
			site_url: $('#wps_site_url').val(),
			admin_name: $('#wps_admin_name').val(),
			admin_email: $('#wps_admin_email').val(),
			opt_in_updates: $('#wps_opt_in_updates').is(':checked') ? 1 : 0,
			opt_in_security: $('#wps_opt_in_security').is(':checked') ? 1 : 0,
			opt_in_newsletter: $('#wps_opt_in_newsletter').is(':checked') ? 1 : 0,
			opt_in_marketing: $('#wps_opt_in_marketing').is(':checked') ? 1 : 0,
			agree_terms: $('#wps_agree_terms').is(':checked') ? 1 : 0
		};

		// Send AJAX request
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				$submit.prop('disabled', false);
				$spinner.removeClass('is-active');

				if (response.success) {
					$message.removeClass('error').addClass('success')
						.html(response.data.message)
						.show();

					// Redirect after 2 seconds
					setTimeout(function() {
						window.location.href = response.data.redirect || '<?php echo esc_js( admin_url( 'admin.php?page=wp-support' ) ); ?>';
					}, 2000);
				} else {
					$message.removeClass('success').addClass('error')
						.text(response.data.message || '<?php echo esc_js( __( 'Registration failed. Please try again.', 'plugin-wp-support-thisismyurl' ) ); ?>')
						.show();
				}
			},
			error: function(xhr, status, error) {
				$submit.prop('disabled', false);
				$spinner.removeClass('is-active');
				$message.removeClass('success').addClass('error')
					.text('<?php echo esc_js( __( 'An error occurred. Please check your internet connection and try again.', 'plugin-wp-support-thisismyurl' ) ); ?>')
					.show();
			}
		});
	});
});
</script>
