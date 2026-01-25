<?php

/**
 * Email Test Tool
 *
 * Test email delivery and configuration including From Name/Email settings.
 *
 * @package WPShadow
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

// Handle test email submission
$test_results = null;
if (isset($_POST['wpshadow_send_test_email']) && check_admin_referer('wpshadow_email_test', 'wpshadow_email_test_nonce')) {
	if (! current_user_can('manage_options')) {
		wp_die('Insufficient permissions.');
	}

	$to_email    = isset($_POST['to_email']) ? sanitize_email($_POST['to_email']) : '';
	$from_name   = isset($_POST['from_name']) ? sanitize_text_field($_POST['from_name']) : '';
	$from_email  = isset($_POST['from_email']) ? sanitize_email($_POST['from_email']) : '';
	$save_config = isset($_POST['save_config']) && $_POST['save_config'] === '1';

	// Save configuration if requested
	if ($save_config && ! empty($from_name) && ! empty($from_email)) {
		update_option('wpshadow_email_from_name', $from_name);
		update_option('wpshadow_email_from_email', $from_email);
	}

	// Prepare test email
	$subject = sprintf(__('Test Email from %s', 'wpshadow'), get_bloginfo('name'));
	$message = sprintf(
		__("This is a test email from your WordPress site.\n\nSite: %1\$s\nTimestamp: %2\$s\n\nIf you received this email, your WordPress email configuration is working correctly.\n\n---\nSent by WPShadow Email Test Tool", 'wpshadow'),
		home_url(),
		current_time('mysql')
	);

	$headers = array();
	if (! empty($from_name) && ! empty($from_email)) {
		$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
	}

	// Send test email
	$sent = wp_mail($to_email, $subject, $message, $headers);

	// Capture any errors
	global $phpmailer;
	$smtp_error = '';
	if (isset($phpmailer) && is_object($phpmailer) && ! empty($phpmailer->ErrorInfo)) {
		$smtp_error = $phpmailer->ErrorInfo;
	}

	// Store email test status
	if ($sent) {
		update_option('wpshadow_last_email_test_status', 'passed');
		update_option('wpshadow_last_email_test_time', current_time('timestamp'));
	} else {
		update_option('wpshadow_last_email_test_status', 'failed');
		update_option('wpshadow_last_email_test_time', current_time('timestamp'));
	}

	$test_results = array(
		'success'    => $sent,
		'to'         => $to_email,
		'from_name'  => $from_name,
		'from_email' => $from_email,
		'error'      => $smtp_error,
	);
}

// Handle compliance settings update
if (isset($_POST['wpshadow_update_compliance']) && check_admin_referer('wpshadow_compliance_settings', 'wpshadow_compliance_nonce')) {
	if (! current_user_can('manage_options')) {
		wp_die('Insufficient permissions.');
	}

	$uncheck_by_default = isset($_POST['email_unchecked_by_default']) && $_POST['email_unchecked_by_default'] === '1';
	update_option('wpshadow_user_email_unchecked_by_default', $uncheck_by_default);

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Privacy compliance settings updated.', 'wpshadow') . '</p></div>';
}

// Get current configuration
$current_from_name  = get_option('wpshadow_email_from_name', get_bloginfo('name'));
$current_from_email = get_option('wpshadow_email_from_email', get_option('admin_email'));
$current_user_email = wp_get_current_user()->user_email;

// Get WordPress defaults (what would be used without our override)
$wp_from_email = 'wordpress@' . preg_replace('#^www\.#', '', wp_parse_url(home_url(), PHP_URL_HOST));

?>

<div class="wrap">
	<h1><?php esc_html_e('Email Test & Configuration', 'wpshadow'); ?></h1>
	<p><?php esc_html_e('Test your WordPress email delivery and configure the From Name and From Email address.', 'wpshadow'); ?></p>

	<?php if ($test_results) : ?>
		<div class="notice notice-<?php echo esc_attr($test_results['success'] ? 'success' : 'error'); ?> is-dismissible">
			<p>
				<?php if ($test_results['success']) : ?>
					<strong><?php esc_html_e('Test email sent successfully!', 'wpshadow'); ?></strong><br>
					<?php
					printf(
						/* translators: 1: recipient email address */
						esc_html__('A test email was sent to %s. Please check your inbox (and spam folder).', 'wpshadow'),
						'<code>' . esc_html($test_results['to']) . '</code>'
					);
					?>
				<?php else : ?>
					<strong><?php esc_html_e('Failed to send test email.', 'wpshadow'); ?></strong><br>
					<?php if (! empty($test_results['error'])) : ?>
						<?php
						printf(
							/* translators: 1: error message */
							esc_html__('Error: %s', 'wpshadow'),
							'<code>' . esc_html($test_results['error']) . '</code>'
						);
						?>
					<?php else : ?>
						<?php esc_html_e('WordPress was unable to send the email. Check your server mail configuration or consider using an SMTP plugin.', 'wpshadow'); ?>
					<?php endif; ?>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<div class="card" style="max-width: 800px;">
		<h2><?php esc_html_e('Current Email Configuration', 'wpshadow'); ?></h2>
		<table class="widefat striped" style="margin-bottom: 20px;">
			<tbody>
				<tr>
					<th style="width: 200px;"><?php esc_html_e('From Name', 'wpshadow'); ?></th>
					<td>
						<code><?php echo esc_html($current_from_name); ?></code>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e('From Email', 'wpshadow'); ?></th>
					<td>
						<code><?php echo esc_html($current_from_email); ?></code>
						<?php if ($current_from_email === $wp_from_email) : ?>
							<span class="dashicons dashicons-warning" style="color: #d63638;"></span>
							<span style="color: #d63638;">
								<?php esc_html_e('Using WordPress default (may be rejected by mail servers)', 'wpshadow'); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e('WordPress Default', 'wpshadow'); ?></th>
					<td><code><?php echo esc_html($wp_from_email); ?></code></td>
				</tr>
				<tr>
					<th><?php esc_html_e('Admin Email', 'wpshadow'); ?></th>
					<td><code><?php echo esc_html(get_option('admin_email')); ?></code></td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e('Send Test Email', 'wpshadow'); ?></h2>
		<form method="post" action="">
			<?php wp_nonce_field('wpshadow_email_test', 'wpshadow_email_test_nonce'); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="to_email"><?php esc_html_e('Send To', 'wpshadow'); ?></label>
						</th>
						<td>
							<input type="email"
								id="to_email"
								name="to_email"
								value="<?php echo esc_attr($current_user_email); ?>"
								class="regular-text"
								required>
							<p class="description">
								<?php esc_html_e('The email address where the test email will be sent.', 'wpshadow'); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="from_name"><?php esc_html_e('From Name', 'wpshadow'); ?></label>
						</th>
						<td>
							<input type="text"
								id="from_name"
								name="from_name"
								value="<?php echo esc_attr($current_from_name); ?>"
								class="regular-text"
								required>
							<p class="description">
								<?php esc_html_e('The name that appears as the email sender.', 'wpshadow'); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="from_email"><?php esc_html_e('From Email', 'wpshadow'); ?></label>
						</th>
						<td>
							<input type="email"
								id="from_email"
								name="from_email"
								value="<?php echo esc_attr($current_from_email); ?>"
								class="regular-text"
								required>
							<p class="description">
								<?php
								printf(
									/* translators: 1: opening anchor tag, 2: closing anchor tag */
									esc_html__('The email address emails are sent from. Use a valid address from your domain. %1$sLearn more about email deliverability%2$s.', 'wpshadow'),
									'<a href="https://wordpress.org/support/article/settings-general-screen/#email-address" target="_blank">',
									'</a>'
								);
								?>
							</p>
							<?php if ($current_from_email === $wp_from_email) : ?>
								<div class="notice notice-warning inline wps-m-10-p-10">
									<p>
										<strong><?php esc_html_e('Warning:', 'wpshadow'); ?></strong>
										<?php
										printf(
											/* translators: 1: default WordPress email address */
											esc_html__('You are using the WordPress default email address (%s). Many mail servers will reject emails from this address. Consider using a real email address from your domain (e.g., noreply@yourdomain.com).', 'wpshadow'),
											'<code>' . esc_html($wp_from_email) . '</code>'
										);
										?>
									</p>
								</div>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"></th>
						<td>
							<label>
								<input type="checkbox" name="save_config" value="1" checked>
								<?php esc_html_e('Save From Name and From Email as defaults for all site emails', 'wpshadow'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('If checked, these settings will be applied to all emails sent by WordPress.', 'wpshadow'); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit">
				<button type="submit" name="wpshadow_send_test_email" class="button button-primary">
					<?php esc_html_e('Send Test Email', 'wpshadow'); ?>
				</button>
			</p>
		</form>
	</div>

	<div class="card" style="max-width: 800px; margin-top: 20px;">
		<h2><?php esc_html_e('Troubleshooting Email Delivery', 'wpshadow'); ?></h2>
		<p><?php esc_html_e('If emails are not being delivered, consider these common issues:', 'wpshadow'); ?></p>
		<ul style="list-style: disc; margin-left: 20px;">
			<li>
				<strong><?php esc_html_e('Server mail configuration:', 'wpshadow'); ?></strong>
				<?php esc_html_e('Your hosting server may not be configured to send mail properly.', 'wpshadow'); ?>
			</li>
			<li>
				<strong><?php esc_html_e('SPF/DKIM records:', 'wpshadow'); ?></strong>
				<?php esc_html_e('Your domain may not have proper DNS records for email authentication.', 'wpshadow'); ?>
			</li>
			<li>
				<strong><?php esc_html_e('From Email address:', 'wpshadow'); ?></strong>
				<?php esc_html_e('Using an address that doesn\'t match your domain can cause delivery failures.', 'wpshadow'); ?>
			</li>
			<li>
				<strong><?php esc_html_e('SMTP plugin:', 'wpshadow'); ?></strong>
				<?php
				printf(
					/* translators: 1: opening anchor tag for WP Mail SMTP, 2: closing anchor tag */
					esc_html__('Consider using an SMTP plugin like %1$sWP Mail SMTP%2$s for more reliable delivery.', 'wpshadow'),
					'<a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank">',
					'</a>'
				);
				?>
			</li>
			<li>
				<strong><?php esc_html_e('Email service provider:', 'wpshadow'); ?></strong>
				<?php esc_html_e('Use a transactional email service like SendGrid, Mailgun, or Amazon SES for production sites.', 'wpshadow'); ?>
			</li>
		</ul>
	</div>

	<div class="card" style="max-width: 800px; margin-top: 20px;">
		<h2><?php esc_html_e('Privacy & New User Emails', 'wpshadow'); ?></h2>
		<p><strong><?php esc_html_e('Regarding "Send the new user an email about their account":', 'wpshadow'); ?></strong></p>
		<p><?php esc_html_e('Under GDPR and most privacy laws, it is generally acceptable to send new user account emails because:', 'wpshadow'); ?></p>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e('It is a transactional email necessary for account functionality (providing login credentials)', 'wpshadow'); ?></li>
			<li><?php esc_html_e('The admin creating the account typically has legitimate interest or consent from the user', 'wpshadow'); ?></li>
			<li><?php esc_html_e('It is opt-in via the checkbox (not automatically sent)', 'wpshadow'); ?></li>
			<li><?php esc_html_e('It contains essential information for the user to access their account', 'wpshadow'); ?></li>
		</ul>
		<p>
			<strong><?php esc_html_e('Best practices:', 'wpshadow'); ?></strong>
		</p>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e('Only send the email when the checkbox is checked', 'wpshadow'); ?></li>
			<li><?php esc_html_e('Ensure you have permission from the user before creating their account', 'wpshadow'); ?></li>
			<li><?php esc_html_e('Document in your privacy policy that you send account creation emails', 'wpshadow'); ?></li>
			<li><?php esc_html_e('Use the generated friendly passwords to make the email more user-friendly', 'wpshadow'); ?></li>
		</ul>

		<h3><?php esc_html_e('Compliance Settings', 'wpshadow'); ?></h3>
		<?php
		$email_unchecked = get_option('wpshadow_user_email_unchecked_by_default', false);
		?>
		<form method="post" action="">
			<?php wp_nonce_field('wpshadow_compliance_settings', 'wpshadow_compliance_nonce'); ?>

			<div class="wps-p-15-rounded-4">
				<label>
					<input type="checkbox"
						name="email_unchecked_by_default"
						value="1"
						<?php checked($email_unchecked, true); ?>>
					<strong><?php esc_html_e('Uncheck email notification by default', 'wpshadow'); ?></strong>
				</label>
				<p class="description" style="margin-top: 10px;">
					<?php esc_html_e('When enabled, the "Send the new user an email about their account" checkbox on user-new.php will be unchecked by default. This ensures CASL (Canada), GDPR (EU), and CCPA (US) compliance by requiring explicit opt-in.', 'wpshadow'); ?>
				</p>
				<p class="description">
					<?php esc_html_e('Recommendation: Enable this for strict privacy law compliance, especially if your site serves Canadian users.', 'wpshadow'); ?>
				</p>
			</div>

			<p class="submit" style="margin-top: 15px;">
				<button type="submit" name="wpshadow_update_compliance" class="button button-primary">
					<?php esc_html_e('Update Compliance Settings', 'wpshadow'); ?>
				</button>
			</p>
		</form>
	</div>
</div>
