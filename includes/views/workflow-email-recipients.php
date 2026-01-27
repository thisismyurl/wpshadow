<?php
/**
 * Approved Email Recipients Management Page
 *
 * Admin interface for managing pre-approved email recipients in workflows
 *
 * @package WPShadow
 * @subpackage Workflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require email manager
require_once WPSHADOW_INCLUDES . 'workflow/class-email-recipient-manager.php';

use WPShadow\Workflow\Email_Recipient_Manager;

$recipients = Email_Recipient_Manager::get_approved_recipients();
$nonce      = wp_create_nonce( Email_Recipient_Manager::NONCE_ACTION );
?>

<div class="wrap wpshadow-email-recipients">
	<h1><?php esc_html_e( 'Workflow Email Recipients', 'wpshadow' ); ?></h1>
	<p class="wps-version-tag">v<?php echo esc_html( WPSHADOW_VERSION ); ?></p>
	<p class="description">
		<?php esc_html_e( 'Manage approved email recipients that can be used when sending emails from workflows. All emails must be verified or approved by an admin before they can be used.', 'wpshadow' ); ?>
	</p>

	<!-- Add New Recipient Form -->
	<div class="card">
		<h2><?php esc_html_e( 'Add New Recipient', 'wpshadow' ); ?></h2>
		<div class="wps-settings-section">
			<div class="wps-form-group">
				<label class="wps-label" for="new-email">
					<?php esc_html_e( 'Email Address', 'wpshadow' ); ?>
				</label>
				<input type="email" id="new-email" class="wps-input" placeholder="user@example.com" />
			</div>

			<div class="wps-form-group">
				<label class="wps-label">
					<?php esc_html_e( 'Verification Method', 'wpshadow' ); ?>
				</label>
				<div style="display: flex; flex-direction: column; gap: 8px;">
					<label>
						<input type="radio" name="verification-method" value="email" checked />
						<?php esc_html_e( 'Send Verification Email (recipient must approve)', 'wpshadow' ); ?>
					</label>
					<label>
						<input type="radio" name="verification-method" value="admin" />
						<?php esc_html_e( 'Admin Approval (I confirm I have permission)', 'wpshadow' ); ?>
					</label>
				</div>
			</div>

			<div class="wps-form-group">
				<button type="button" class="wps-btn wps-btn-primary" id="add-email-btn">
					<?php esc_html_e( 'Add Recipient', 'wpshadow' ); ?>
				</button>
				<span id="add-email-message" style="margin-left: 10px;"></span>
			</div>
		</div>
	</div>

	<!-- Approved Recipients List -->
	<div class="card" style="margin-top: 20px;">
		<h2><?php esc_html_e( 'Approved Recipients', 'wpshadow' ); ?></h2>
		<?php if ( ! empty( $recipients ) ) : ?>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Email Address', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Verification Method', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Added Date', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recipients as $email => $data ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $email ); ?></strong></td>
							<td>
								<?php
								if ( isset( $data['approved'] ) && $data['approved'] ) {
									echo '<span style="color: green;">✓ ' . esc_html__( 'Approved', 'wpshadow' ) . '</span>';
								} elseif ( isset( $data['pending_admin'] ) && $data['pending_admin'] ) {
									echo '<span style="color: orange;">⊙ ' . esc_html__( 'Pending Admin Approval', 'wpshadow' ) . '</span>';
								} else {
									echo '<span style="color: gray;">○ ' . esc_html__( 'Inactive', 'wpshadow' ) . '</span>';
								}
								?>
							</td>
							<td>
								<?php
								if ( isset( $data['verification'] ) ) {
									echo esc_html( ucfirst( $data['verification'] ) );
								} elseif ( isset( $data['verified_by'] ) ) {
									echo esc_html( ucfirst( str_replace( '_', ' ', $data['verified_by'] ) ) );
								} else {
									echo esc_html__( 'Unknown', 'wpshadow' );
								}
								?>
							</td>
							<td>
								<?php
								$date_field = isset( $data['approved_date'] ) ? 'approved_date' : 'added_date';
								if ( isset( $data[ $date_field ] ) ) {
									echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data[ $date_field ] ) ) );
								} else {
									echo '—';
								}
								?>
							</td>
							<td>
								<?php if ( isset( $data['pending_admin'] ) && $data['pending_admin'] ) : ?>
								<button class="wps-btn wps-btn--secondary approve-email-btn" data-email="<?php echo esc_attr( $email ); ?>">
									<?php esc_html_e( 'Approve', 'wpshadow' ); ?>
								</button>
							<?php endif; ?>
							<button class="wps-btn wps-btn--danger remove-email-btn" data-email="<?php echo esc_attr( $email ); ?>">
									<?php esc_html_e( 'Remove', 'wpshadow' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No email recipients added yet.', 'wpshadow' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<style>
	.wpshadow-email-recipients .card {
		padding: 20px;
		background: #fff;
		border: 1px solid #ccc;
		border-radius: 4px;
	}

	.wpshadow-email-recipients h2 {
		margin-top: 0;
	}

	.wpshadow-email-recipients .form-table th {
		vertical-align: middle;
	}

	#add-email-message {
		font-weight: bold;
	}

	#add-email-message.success {
		color: #265926;
	}

	#add-email-message.error {
		color: #a70b0b;
	}
</style>

<script>
	(function($) {
		const nonce = '<?php echo esc_js( $nonce ); ?>';

		// Add email recipient
		$('#add-email-btn').on('click', function() {
			const email = $('#new-email').val().trim();
			const method = $('input[name="verification-method"]:checked').val();
			const sendVerification = method === 'email';

			if (!email) {
				showMessage('Please enter an email address', 'error');
				return;
			}

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wpshadow_add_email_recipient',
					nonce: nonce,
					email: email,
					send_verification: sendVerification
				},
				success: function(response) {
					if (response.success) {
						showMessage(response.data.message, 'success');
						$('#new-email').val('');
						setTimeout(() => location.reload(), 1500);
					} else {
						showMessage(response.data.message, 'error');
					}
				},
				error: function() {
					showMessage('An error occurred. Please try again.', 'error');
				}
			});
		});

		// Approve email recipient
		$(document).on('click', '.approve-email-btn', function() {
			if (!confirm('<?php esc_js_e( 'Are you sure you want to approve this email?', 'wpshadow' ); ?>')) {
				return;
			}

			const email = $(this).data('email');

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wpshadow_approve_recipient',
					nonce: nonce,
					email: email
				},
				success: function(response) {
					if (response.success) {
						showMessage(response.data.message, 'success');
						setTimeout(() => location.reload(), 1500);
					} else {
						showMessage(response.data.message, 'error');
					}
				}
			});
		});

		// Remove email recipient
		$(document).on('click', '.remove-email-btn', function() {
			if (!confirm('<?php esc_js_e( 'Are you sure you want to remove this email?', 'wpshadow' ); ?>')) {
				return;
			}

			const email = $(this).data('email');

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wpshadow_remove_recipient',
					nonce: nonce,
					email: email
				},
				success: function(response) {
					if (response.success) {
						showMessage(response.data.message, 'success');
						setTimeout(() => location.reload(), 1500);
					} else {
						showMessage(response.data.message, 'error');
					}
				}
			});
		});

		function showMessage(message, type) {
			const $message = $('#add-email-message')
				.removeClass('success error')
				.addClass(type)
				.text(message);
		}
	})(jQuery);
</script>
