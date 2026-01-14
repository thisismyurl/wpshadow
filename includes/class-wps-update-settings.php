<?php
/**
 * License & Updates Settings Page
 *
 * Admin UI for managing license keys and checking for plugin updates.
 *
 * @package    WP_Support
 * @subpackage Core
 * @since      1.2601.73001
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle license key submission
 */
function wp_support_handle_license_submission(): void {
	if ( ! isset( $_POST['wps_license_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_license_nonce'] ) ), 'wps_save_license' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['wps_license_key'] ) ) {
		$license_key = sanitize_text_field( wp_unslash( $_POST['wps_license_key'] ) );

		if ( ! empty( $license_key ) ) {
			update_option( 'wps_license_key', $license_key );
			add_settings_error(
				'wps_license',
				'license_saved',
				__( 'License key saved successfully.', 'plugin-wp-support-thisismyurl' ),
				'success'
			);

			// Clear update cache to force license validation.
			\WPS\CoreSupport\WPS_Update_Client::clear_cache();
		} else {
			delete_option( 'wps_license_key' );
			add_settings_error(
				'wps_license',
				'license_removed',
				__( 'License key removed successfully.', 'plugin-wp-support-thisismyurl' ),
				'success'
			);
		}
	}
}
add_action( 'admin_init', 'wp_support_handle_license_submission' );

/**
 * Render license & updates settings page
 */
function wp_support_render_updates_page(): void {
	$license_key    = get_option( 'wps_license_key', '' );
	$has_license    = ! empty( $license_key );
	$masked_license = $has_license ? str_repeat( '•', max( 20, strlen( $license_key ) - 4 ) ) . substr( $license_key, -4 ) : '';

	// Get current update status.
	$update_data       = get_transient( 'wps_update_data' );
	$license_valid     = false;
	$license_expire    = '';
	$available_updates = array();

	if ( $update_data && is_array( $update_data ) ) {
		$license_valid  = $update_data['license_valid'] ?? false;
		$license_expire = $update_data['license_expires'] ?? '';

		// Check for available updates.
		if ( ! empty( $update_data['plugins'] ) && is_array( $update_data['plugins'] ) ) {
			foreach ( $update_data['plugins'] as $slug => $info ) {
				$basename = \WPS\CoreSupport\WPS_Update_Client::find_plugin_basename( $slug );
				if ( ! $basename ) {
					continue;
				}

				$plugin_file = WP_PLUGIN_DIR . '/' . $basename;
				if ( ! file_exists( $plugin_file ) ) {
					continue;
				}

				if ( ! function_exists( 'get_plugin_data' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$plugin_data     = get_plugin_data( $plugin_file );
				$current_version = $plugin_data['Version'];
				$latest_version  = $info['version'] ?? '';

				if ( version_compare( $current_version, $latest_version, '<' ) ) {
					$available_updates[] = array(
						'name'    => $info['name'] ?? $plugin_data['Name'],
						'current' => $current_version,
						'latest'  => $latest_version,
					);
				}
			}
		}
	}

	settings_errors( 'wps_license' );
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Updates & License', 'plugin-wp-support-thisismyurl' ); ?></h1>

		<!-- License Status -->
		<div class="card" style="max-width: 800px;">
			<h2><?php esc_html_e( 'License Status', 'plugin-wp-support-thisismyurl' ); ?></h2>

			<?php if ( $has_license && $license_valid ) : ?>
				<div class="notice notice-success inline">
					<p>
						<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
						<strong><?php esc_html_e( 'License Active', 'plugin-wp-support-thisismyurl' ); ?></strong>
					</p>
				</div>
				<?php if ( $license_expire ) : ?>
					<p>
						<?php
						printf(
							/* translators: %s: expiration date */
							esc_html__( 'Your license expires on: %s', 'plugin-wp-support-thisismyurl' ),
							'<strong>' . esc_html( $license_expire ) . '</strong>'
						);
						?>
					</p>
				<?php endif; ?>
			<?php elseif ( $has_license && ! $license_valid ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<span class="dashicons dashicons-warning" style="color: #f0b849;"></span>
						<strong><?php esc_html_e( 'License Invalid or Expired', 'plugin-wp-support-thisismyurl' ); ?></strong>
					</p>
				</div>
				<p><?php esc_html_e( 'Please check your license key or contact support.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php else : ?>
				<div class="notice notice-info inline">
					<p>
						<span class="dashicons dashicons-info" style="color: #72aee6;"></span>
						<?php esc_html_e( 'No license key configured. Updates are disabled.', 'plugin-wp-support-thisismyurl' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Available Updates -->
		<?php if ( ! empty( $available_updates ) ) : ?>
			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2><?php esc_html_e( 'Available Updates', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Plugin', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Current Version', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'New Version', 'plugin-wp-support-thisismyurl' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $available_updates as $update ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $update['name'] ); ?></strong></td>
								<td><?php echo esc_html( $update['current'] ); ?></td>
								<td><strong style="color: #2271b1;"><?php echo esc_html( $update['latest'] ); ?></strong></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p style="margin-top: 15px;">
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Go to Plugins Page to Update', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<!-- License Key Form -->
		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2><?php esc_html_e( 'License Key Configuration', 'plugin-wp-support-thisismyurl' ); ?></h2>

			<form method="post" action="">
				<?php wp_nonce_field( 'wps_save_license', 'wps_license_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="wps_license_key"><?php esc_html_e( 'License Key', 'plugin-wp-support-thisismyurl' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="wps_license_key"
								name="wps_license_key"
								value="<?php echo esc_attr( $masked_license ); ?>"
								class="regular-text"
								placeholder="<?php esc_attr_e( 'Enter your license key', 'plugin-wp-support-thisismyurl' ); ?>"
								onfocus="if(this.value.includes('•')) this.value='';"
							/>
							<p class="description">
								<?php esc_html_e( 'Enter your thisismyurl.com license key to receive automatic updates.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save License Key', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
					<?php if ( $has_license ) : ?>
						<button type="submit" name="wps_license_key" value="" class="button button-secondary">
							<?php esc_html_e( 'Remove License Key', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					<?php endif; ?>
				</p>
			</form>
		</div>

		<!-- Manual Update Check -->
		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2><?php esc_html_e( 'Manual Update Check', 'plugin-wp-support-thisismyurl' ); ?></h2>
			<p><?php esc_html_e( 'Force an immediate check for updates from thisismyurl.com servers.', 'plugin-wp-support-thisismyurl' ); ?></p>

			<button type="button" id="wps-check-updates" class="button">
				<?php esc_html_e( 'Check for Updates Now', 'plugin-wp-support-thisismyurl' ); ?>
			</button>

			<div id="wps-update-status" style="margin-top: 15px;"></div>
		</div>

		<!-- How It Works -->
		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2><?php esc_html_e( 'How Automatic Updates Work', 'plugin-wp-support-thisismyurl' ); ?></h2>
			<ol>
				<li><strong><?php esc_html_e( 'Purchase License', 'plugin-wp-support-thisismyurl' ); ?>:</strong> <?php esc_html_e( 'Get your license key from thisismyurl.com', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><strong><?php esc_html_e( 'Enter Key Above', 'plugin-wp-support-thisismyurl' ); ?>:</strong> <?php esc_html_e( 'Save your license key in this page', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><strong><?php esc_html_e( 'Automatic Checks', 'plugin-wp-support-thisismyurl' ); ?>:</strong> <?php esc_html_e( 'WordPress checks for updates every 12 hours', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><strong><?php esc_html_e( 'One-Click Updates', 'plugin-wp-support-thisismyurl' ); ?>:</strong> <?php esc_html_e( 'Update all TIMU plugins from the Plugins page', 'plugin-wp-support-thisismyurl' ); ?></li>
			</ol>

			<p>
				<strong><?php esc_html_e( 'Update Server:', 'plugin-wp-support-thisismyurl' ); ?></strong>
				<code>https://thisismyurl.com/api/updates/check.json</code>
			</p>

			<p>
				<strong><?php esc_html_e( 'Environment Variable (Optional):', 'plugin-wp-support-thisismyurl' ); ?></strong>
				<code>WPS_LICENSE_KEY</code>
			</p>
		</div>
	</div>

	<script>
	document.getElementById('wps-check-updates').addEventListener('click', function() {
		const button = this;
		const status = document.getElementById('wps-update-status');

		button.disabled = true;
		button.textContent = '<?php esc_html_e( 'Checking...', 'plugin-wp-support-thisismyurl' ); ?>';
		status.innerHTML = '<div class="notice notice-info inline"><p><?php esc_html_e( 'Contacting update server...', 'plugin-wp-support-thisismyurl' ); ?></p></div>';

		fetch(ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'wps_check_updates',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wps_check_updates' ) ); ?>'
			})
		})
		.then(response => response.json())
		.then(data => {
			button.disabled = false;
			button.textContent = '<?php esc_html_e( 'Check for Updates Now', 'plugin-wp-support-thisismyurl' ); ?>';

			if (data.success) {
				status.innerHTML = '<div class="notice notice-success inline"><p><strong><?php esc_html_e( 'Success!', 'plugin-wp-support-thisismyurl' ); ?></strong> ' + data.data.message + '</p></div>';
				setTimeout(() => location.reload(), 2000);
			} else {
				status.innerHTML = '<div class="notice notice-error inline"><p><strong><?php esc_html_e( 'Error:', 'plugin-wp-support-thisismyurl' ); ?></strong> ' + data.data.message + '</p></div>';
			}
		})
		.catch(error => {
			button.disabled = false;
			button.textContent = '<?php esc_html_e( 'Check for Updates Now', 'plugin-wp-support-thisismyurl' ); ?>';
			status.innerHTML = '<div class="notice notice-error inline"><p><strong><?php esc_html_e( 'Error:', 'plugin-wp-support-thisismyurl' ); ?></strong> ' + error.message + '</p></div>';
		});
	});
	</script>

	<?php
}
