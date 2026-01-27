<?php
/**
 * Magic Link Support Tool
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'magic-link-support' );

// Render header
Tool_View_Base::render_header( __( 'Magic Link Support', 'wpshadow' ) );

$magic_links  = get_option( 'wpshadow_magic_links', array() );
$active_links = array_filter(
	$magic_links,
	function ( $link ) {
		return isset( $link['expires_at'] ) && $link['expires_at'] > current_time( 'timestamp' );
	}
);
?>

<div class="wpshadow-tool-container">
	<h2><?php esc_html_e( 'Temporary Support Login', 'wpshadow' ); ?></h2>
	<p><?php esc_html_e( 'Create secure, temporary login links for developers to help fix your site without needing your password.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Create Magic Link', 'wpshadow' ); ?></h3>
		<form id="wpshadow-create-magic-link">
			<?php wp_nonce_field( 'wpshadow_magic_link_nonce', 'wpshadow_magic_link_nonce' ); ?>
			<div class="wps-settings-section">
				<div class="wps-form-group">
					<label class="wps-label" for="developer-name">
						<?php esc_html_e( 'Developer Name', 'wpshadow' ); ?>
					</label>
					<input type="text" id="developer-name" name="developer_name" class="wps-input" required />
					<span class="wps-help-text">
						<?php esc_html_e( 'Full name of the person who will use this link', 'wpshadow' ); ?>
					</span>
				</div>

				<div class="wps-form-group">
					<label class="wps-label" for="developer-email">
						<?php esc_html_e( 'Developer Email', 'wpshadow' ); ?>
					</label>
					<input type="email" id="developer-email" name="developer_email" class="wps-input" required />
					<span class="wps-help-text">
						<?php esc_html_e( 'Email for notifications about access', 'wpshadow' ); ?>
					</span>
				</div>

				<div class="wps-form-group">
					<label class="wps-label" for="access-duration">
						<?php esc_html_e( 'Access Duration', 'wpshadow' ); ?>
					</label>
					<select id="access-duration" name="duration">
						<option value="1">1 <?php esc_html_e( 'hour', 'wpshadow' ); ?></option>
						<option value="24" selected>24 <?php esc_html_e( 'hours', 'wpshadow' ); ?></option>
						<option value="72">72 <?php esc_html_e( 'hours', 'wpshadow' ); ?></option>
						<option value="168">1 <?php esc_html_e( 'week', 'wpshadow' ); ?></option>
					</select>
					<span class="wps-help-text">
						<?php esc_html_e( 'How long the link will work before expiring', 'wpshadow' ); ?>
					</div>
			</div>
			<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Generate Magic Link', 'wpshadow' ); ?>
			</button>
			<div id="wpshadow-magic-link-message" class="wps-none"></div>
		</form>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Active Links', 'wpshadow' ); ?></h3>
		<?php if ( empty( $active_links ) ) : ?>
			<p><?php esc_html_e( 'No active magic links. Create one above to provide temporary access.', 'wpshadow' ); ?></p>
		<?php else : ?>
			<table class="widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Developer', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Email', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Created', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Expires', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $active_links as $token => $link ) : ?>
						<tr>
							<td><?php echo esc_html( $link['developer_name'] ?? 'Unknown' ); ?></td>
							<td><?php echo esc_html( $link['developer_email'] ?? '' ); ?></td>
							<td><?php echo esc_html( wp_date( get_option( 'date_format' ), $link['created_at'] ?? 0 ) ); ?></td>
							<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $link['expires_at'] ?? 0 ) ); ?></td>
							<td>
								<button type="button" class="wps-btn wps-btn-secondary wpshadow-revoke-link" data-token="<?php echo esc_attr( $token ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_magic_link_nonce' ) ); ?>">
									<?php esc_html_e( 'Revoke', 'wpshadow' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Security Notes', 'wpshadow' ); ?></h3>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e( 'Links expire automatically after the set duration', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'You can revoke links anytime before expiration', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'All access is logged for security auditing', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Each link can only be used once', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Create magic link
	$('#wpshadow-create-magic-link').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');
		var $message = $('#wpshadow-magic-link-message');

		var data = {
			action: 'wpshadow_create_magic_link',
			nonce: $form.find('[name="wpshadow_magic_link_nonce"]').val(),
			developer_name: $form.find('[name="developer_name"]').val(),
			developer_email: $form.find('[name="developer_email"]').val(),
			duration: $form.find('[name="duration"]').val()
		};

		$btn.prop('disabled', true).text('<?php esc_attr_e( 'Generating...', 'wpshadow' ); ?>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function(response) {
				if (response.success) {
					var html = '<div class="notice notice-success"><p><strong><?php esc_attr_e( 'Magic Link Created!', 'wpshadow' ); ?></strong></p>' +
						'<p><?php esc_attr_e( 'Link:', 'wpshadow' ); ?></p>' +
						'<code class="wps-block-m-10-p-10">' + response.data.magic_link + '</code>' +
						'<p><?php esc_attr_e( 'Expires:', 'wpshadow' ); ?> ' + response.data.expires_at + '</p>' +
						'<button type="button" class="wps-btn wps-btn-secondary" onclick="var text = \'' + response.data.magic_link + '\'; navigator.clipboard.writeText(text); alert(\'<?php esc_attr_e( 'Link copied to clipboard!', 'wpshadow' ); ?>\');"><?php esc_attr_e( 'Copy Link', 'wpshadow' ); ?></button>' +
						'</div>';
					$message.html(html).show();
					$form[0].reset();
					// Reload page after 5 seconds to show new link in active links
					setTimeout(function() {
						location.reload();
					}, 5000);
				} else {
					$message.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? response.data.message : '<?php esc_attr_e( 'Error creating magic link.', 'wpshadow' ); ?>') + '</p></div>').show();
				}
				$btn.prop('disabled', false).text('<?php esc_attr_e( 'Generate Magic Link', 'wpshadow' ); ?>');
			},
			error: function() {
				$message.html('<div class="notice notice-error"><p><?php esc_attr_e( 'Error creating magic link.', 'wpshadow' ); ?></p></div>').show();
				$btn.prop('disabled', false).text('<?php esc_attr_e( 'Generate Magic Link', 'wpshadow' ); ?>');
			}
		});
	});

	// Revoke magic link
	$('.wpshadow-revoke-link').on('click', function() {
		var $btn = $(this);
		var token = $btn.data('token');
		var nonce = $btn.data('nonce');

		if (!confirm('<?php esc_attr_e( 'Revoke this magic link?', 'wpshadow' ); ?>')) {
			return;
		}

		$btn.prop('disabled', true).text('<?php esc_attr_e( 'Revoking...', 'wpshadow' ); ?>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_revoke_magic_link',
				nonce: nonce,
				token: token
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data && response.data.message ? response.data.message : '<?php esc_attr_e( 'Error revoking link.', 'wpshadow' ); ?>');
					$btn.prop('disabled', false).text('<?php esc_attr_e( 'Revoke', 'wpshadow' ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_attr_e( 'Error revoking link.', 'wpshadow' ); ?>');
				$btn.prop('disabled', false).text('<?php esc_attr_e( 'Revoke', 'wpshadow' ); ?>');
			}
		});
	});
});
</script>
<?php Tool_View_Base::render_footer(); ?>