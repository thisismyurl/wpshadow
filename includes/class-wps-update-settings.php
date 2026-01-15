<?php
/**
 * License & Updates Settings Page
 *
 * Admin UI for managing license keys and checking for plugin updates.
 *
 * @package    WP_Support
 * @subpackage Core
 * @since      1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Update_Settings Class
 *
 * Manages license key configuration and update settings UI.
 */
class WPSHADOW_Update_Settings {

	/**
	 * Initialize update settings.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'handle_license_submission' ) );
	}

	/**
	 * Handle license key submission
	 *
	 * @return void
	 */
	public static function handle_license_submission(): void {
		if ( ! isset( $_POST['wpshadow_license_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpshadow_license_nonce'] ) ), 'wpshadow_save_license' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['wpshadow_license_key'] ) ) {
			$license_key = sanitize_text_field( wp_unslash( $_POST['wpshadow_license_key'] ) );

			if ( ! empty( $license_key ) ) {
				update_option( 'wpshadow_license_key', $license_key );
				add_settings_error(
					'wpshadow_license',
					'license_saved',
					__( 'License key saved successfully.', 'plugin-wpshadow' ),
					'success'
				);

				// Clear update cache to force license validation.
				\WPS\CoreSupport\WPSHADOW_Update_Client::clear_cache();
			} else {
				delete_option( 'wpshadow_license_key' );
				add_settings_error(
					'wpshadow_license',
					'license_removed',
					__( 'License key removed successfully.', 'plugin-wpshadow' ),
					'success'
				);
			}
		}
	}

	/**
	 * Render license & updates settings page
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		$license_key    = get_option( 'wpshadow_license_key', '' );
	$has_license    = ! empty( $license_key );
	$masked_license = $has_license ? str_repeat( '•', max( 20, strlen( $license_key ) - 4 ) ) . substr( $license_key, -4 ) : '';

	// Get current update status.
	$update_data       = get_transient( 'wpshadow_update_data' );
	$license_valid     = false;
	$license_expire    = '';
	$available_updates = array();

	if ( $update_data && is_array( $update_data ) ) {
		$license_valid  = $update_data['license_valid'] ?? false;
		$license_expire = $update_data['license_expires'] ?? '';

		// Check for available updates.
		if ( ! empty( $update_data['plugins'] ) && is_array( $update_data['plugins'] ) ) {
			foreach ( $update_data['plugins'] as $slug => $info ) {
				$basename = \WPS\CoreSupport\WPSHADOW_Update_Client::find_plugin_basename( $slug );
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

	settings_errors( 'wpshadow_license' );
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Updates & License', 'plugin-wpshadow' ); ?></h1>

		<!-- License Status -->
		<div class="card" style="max-width: 800px;">
			<h2><?php esc_html_e( 'License Status', 'plugin-wpshadow' ); ?></h2>

			<?php if ( $has_license && $license_valid ) : ?>
				<div class="notice notice-success inline">
					<p>
						<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
						<strong><?php esc_html_e( 'License Active', 'plugin-wpshadow' ); ?></strong>
					</p>
				</div>
				<?php if ( $license_expire ) : ?>
					<p>
						<?php
						printf(
							/* translators: %s: expiration date */
							esc_html__( 'Your license expires on: %s', 'plugin-wpshadow' ),
							'<strong>' . esc_html( $license_expire ) . '</strong>'
						);
						?>
					</p>
				<?php endif; ?>
			<?php elseif ( $has_license && ! $license_valid ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<span class="dashicons dashicons-warning" style="color: #f0b849;"></span>
						<strong><?php esc_html_e( 'License Invalid or Expired', 'plugin-wpshadow' ); ?></strong>
					</p>
				</div>
				<p><?php esc_html_e( 'Please check your license key or contact support.', 'plugin-wpshadow' ); ?></p>
			<?php else : ?>
				<div class="notice notice-info inline">
					<p>
						<span class="dashicons dashicons-info" style="color: #72aee6;"></span>
						<?php esc_html_e( 'No license key configured. Updates are disabled.', 'plugin-wpshadow' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Available Updates -->
		<?php if ( ! empty( $available_updates ) ) : ?>
			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2><?php esc_html_e( 'Available Updates', 'plugin-wpshadow' ); ?></h2>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Plugin', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Current Version', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'New Version', 'plugin-wpshadow' ); ?></th>
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
						<?php esc_html_e( 'Go to Plugins Page to Update', 'plugin-wpshadow' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<!-- License Key Form -->
		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2><?php esc_html_e( 'License Key Configuration', 'plugin-wpshadow' ); ?></h2>

			<form method="post" action="">
				<?php wp_nonce_field( 'wpshadow_save_license', 'wpshadow_license_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="wpshadow_license_key"><?php esc_html_e( 'License Key', 'plugin-wpshadow' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="wpshadow_license_key"
								name="wpshadow_license_key"
								value="<?php echo esc_attr( $masked_license ); ?>"
								class="regular-text"
								placeholder="<?php esc_attr_e( 'Enter your license key', 'plugin-wpshadow' ); ?>"
								onfocus="if(this.value.includes('•')) this.value='';"
							/>
							<p class="description">
								<?php esc_html_e( 'Enter your wpshadow.com license key to receive automatic updates.', 'plugin-wpshadow' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save License Key', 'plugin-wpshadow' ); ?>
					</button>
					<?php if ( $has_license ) : ?>
						<button type="submit" name="wpshadow_license_key" value="" class="button button-secondary">
							<?php esc_html_e( 'Remove License Key', 'plugin-wpshadow' ); ?>
						</button>
					<?php endif; ?>
				</p>
			</form>
		</div>

		<!-- Manual Update Check -->
		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2><?php esc_html_e( 'Manual Update Check', 'plugin-wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Force an immediate check for updates from wpshadow.com servers.', 'plugin-wpshadow' ); ?></p>

			<button type="button" id="wps-check-updates" class="button">
				<?php esc_html_e( 'Check for Updates Now', 'plugin-wpshadow' ); ?>
			</button>

			<div id="wps-update-status" style="margin-top: 15px;"></div>
		</div>

		<!-- How It Works -->
		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2><?php esc_html_e( 'How Automatic Updates Work', 'plugin-wpshadow' ); ?></h2>
			<ol>
				<li><strong><?php esc_html_e( 'Purchase License', 'plugin-wpshadow' ); ?>:</strong> <?php esc_html_e( 'Get your license key from wpshadow.com', 'plugin-wpshadow' ); ?></li>
				<li><strong><?php esc_html_e( 'Enter Key Above', 'plugin-wpshadow' ); ?>:</strong> <?php esc_html_e( 'Save your license key in this page', 'plugin-wpshadow' ); ?></li>
				<li><strong><?php esc_html_e( 'Automatic Checks', 'plugin-wpshadow' ); ?>:</strong> <?php esc_html_e( 'WordPress checks for updates every 12 hours', 'plugin-wpshadow' ); ?></li>
				<li><strong><?php esc_html_e( 'One-Click Updates', 'plugin-wpshadow' ); ?>:</strong> <?php esc_html_e( 'Update all WPShadow plugins from the Plugins page', 'plugin-wpshadow' ); ?></li>
			</ol>

			<p>
				<strong><?php esc_html_e( 'Update Server:', 'plugin-wpshadow' ); ?></strong>
				<code>https://wpshadow.com/api/updates/check.json</code>
			</p>

			<p>
				<strong><?php esc_html_e( 'Environment Variable (Optional):', 'plugin-wpshadow' ); ?></strong>
				<code>WPSHADOW_LICENSE_KEY</code>
			</p>
		</div>
	</div>

	<script>
	document.getElementById('wps-check-updates').addEventListener('click', function() {
		const button = this;
		const status = document.getElementById('wps-update-status');

		button.disabled = true;
		button.textContent = '<?php esc_html_e( 'Checking...', 'plugin-wpshadow' ); ?>';
		status.innerHTML = '<div class="notice notice-info inline"><p><?php esc_html_e( 'Contacting update server...', 'plugin-wpshadow' ); ?></p></div>';

		fetch(ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'wpshadow_check_updates',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_check_updates' ) ); ?>'
			})
		})
	}
		.then(data => {
			button.disabled = false;
			button.textContent = '<?php esc_html_e( 'Check for Updates Now', 'plugin-wpshadow' ); ?>';

			if (data.success) {
				status.innerHTML = '<div class="notice notice-success inline"><p><strong><?php esc_html_e( 'Success!', 'plugin-wpshadow' ); ?></strong> ' + data.data.message + '</p></div>';
				setTimeout(() => location.reload(), 2000);
			} else {
				status.innerHTML = '<div class="notice notice-error inline"><p><strong><?php esc_html_e( 'Error:', 'plugin-wpshadow' ); ?></strong> ' + data.data.message + '</p></div>';
			}
		})
		.catch(error => {
			button.disabled = false;
			button.textContent = '<?php esc_html_e( 'Check for Updates Now', 'plugin-wpshadow' ); ?>';
			status.innerHTML = '<div class="notice notice-error inline"><p><strong><?php esc_html_e( 'Error:', 'plugin-wpshadow' ); ?></strong> ' + error.message + '</p></div>';
		});
	});
	</script>

	<?php
	}
}
