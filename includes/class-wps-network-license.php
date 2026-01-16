<?php
/**
 * Network-wide license broadcasting for multisite.
 *
 * Allows Super Admin to register a license once in Network Admin and push
 * that key to all sites in the network (or a selected subset).
 *
 * Author:              WPShadow
 * Author URI:          https://wpshadow.com/?source=plugin-wpshadow
 * Plugin Name:         WPShadow - wpshadow
 * Plugin URI:          https://wpshadow.com/plugin-wpshadow/?source=plugin-wpshadow
 * Donate link:         https://wpshadow.com/plugin-wpshadow/#register?source=plugin-wpshadow
 * Description:         Foundation plugin licensing and module management for the wpshadow plugin suite.
 * Tags:                licensing,module-management,multisite,support-dashboard
 * Version:             1.2601.73001
 * Requires at least:   6.4
 * Requires PHP:        8.2
 * Update URI:          https://github.com/thisismyurl/plugin-plugin-wpshadow
 * GitHub Plugin URI:   https://github.com/thisismyurl/plugin-plugin-wpshadow
 * Primary Branch:      main
 * Text Domain:         plugin-wpshadow
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * @package WPSHADOW_WORDPRESS_SUPPORT
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Network License Broadcaster class.
 *
 * Handles network-wide license registration and broadcast to all sites.
 *
 * @since 1.2601.73000
 */
class WPSHADOW_Network_License {

	/**
	 * Option name for network license key.
	 *
	 * @var string
	 */
	private const NETWORK_LICENSE_OPTION = 'wpshadow_network_license_key';

	/**
	 * Option name for network broadcast audit log.
	 *
	 * @var string
	 */
	private const NETWORK_BROADCAST_LOG = 'wpshadow_network_broadcast_log';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( ! is_multisite() ) {
			return;
		}

		add_action( 'network_admin_menu', array( __CLASS__, 'add_submenu_page' ) );
		add_action( 'admin_post_WPSHADOW_broadcast_license', array( __CLASS__, 'handle_broadcast_submission' ) );
	}

	/**
	 * Add License Broadcast submenu page to Network Settings.
	 *
	 * @return void
	 */
	public static function add_submenu_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'License Broadcast', 'plugin-wpshadow' ),
			__( 'License Broadcast', 'plugin-wpshadow' ),
			'manage_network_options',
			'wps-network-license-broadcast',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the License Broadcast page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
		}

		$network_key = get_site_option( self::NETWORK_LICENSE_OPTION, '' );
		$sites       = get_sites( array( 'number' => 9999 ) );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Network License Broadcast', 'plugin-wpshadow' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Register a license key once and push it to all sites in the network or a selected subset.', 'plugin-wpshadow' ); ?></p>

			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['wpshadow_broadcast_success'] ) ) :
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'License key successfully broadcast to selected sites.', 'plugin-wpshadow' ); ?></p>
				</div>
			<?php endif; ?>

			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['wpshadow_broadcast_error'] ) ) :
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'An error occurred while broadcasting the license key.', 'plugin-wpshadow' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Network License Input -->
			<div class="card">
				<h2><?php esc_html_e( 'Network License Key', 'plugin-wpshadow' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_broadcast_license', 'wpshadow_broadcast_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_broadcast_license" />

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="wpshadow_network_key"><?php esc_html_e( 'License Key', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input type="text" id="wpshadow_network_key" name="wpshadow_network_key" value="<?php echo esc_attr( $network_key ); ?>" class="regular-text code" />
								<p class="description"><?php esc_html_e( 'Enter a valid license key. This will be stored in network options and pushed to all sites.', 'plugin-wpshadow' ); ?></p>
							</td>
						</tr>
					</table>

					<!-- Site Selection -->
					<h3><?php esc_html_e( 'Select Sites to Broadcast To', 'plugin-wpshadow' ); ?></h3>
					<fieldset style="margin-bottom: 20px;">
						<legend class="screen-reader-text"><?php esc_html_e( 'Site selection', 'plugin-wpshadow' ); ?></legend>
						<div style="margin-bottom: 10px;">
							<label>
								<input type="checkbox" id="wpshadow_select_all_sites" />
								<strong><?php esc_html_e( 'Select All Sites', 'plugin-wpshadow' ); ?></strong>
							</label>
						</div>

						<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
							<?php foreach ( $sites as $site ) : ?>
								<label style="display: block; margin-bottom: 8px;">
									<input type="checkbox" class="wpshadow_site_checkbox" name="wpshadow_broadcast_sites[]" value="<?php echo absint( $site->blog_id ); ?>" />
									<?php echo esc_html( $site->blogname ) . ' (' . esc_html( $site->domain . $site->path ) . ')'; ?>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>

					<p class="submit">
						<button type="submit" class="button button-primary" name="wpshadow_broadcast_action" value="broadcast">
							<?php esc_html_e( 'Broadcast License to Selected Sites', 'plugin-wpshadow' ); ?>
						</button>
					</p>
				</form>
			</div>

			<!-- Broadcast Audit Log -->
			<?php self::render_audit_log(); ?>
		</div>

		<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			const selectAll = document.getElementById( 'wpshadow_select_all_sites' );
			const checkboxes = document.querySelectorAll( '.WPSHADOW_site_checkbox' );

			if ( selectAll ) {
				selectAll.addEventListener( 'change', function() {
					checkboxes.forEach( function( checkbox ) {
						checkbox.checked = selectAll.checked;
					});
				});

				checkboxes.forEach( function( checkbox ) {
					checkbox.addEventListener( 'change', function() {
						selectAll.checked = Array.from( checkboxes ).every( c => c.checked );
					});
				});
			}
		});
		</script>
		<?php
	}

	/**
	 * Render the audit log of broadcast actions.
	 *
	 * @return void
	 */
	private static function render_audit_log(): void {
		$log = (array) get_site_option( self::NETWORK_BROADCAST_LOG, array() );

		if ( empty( $log ) ) {
			echo '<div class="card"><p><em>' . esc_html__( 'No broadcast history yet.', 'plugin-wpshadow' ) . '</em></p></div>';
			return;
		}

		// Reverse to show newest first.
		$log = array_reverse( $log );

		?>
		<div class="card">
			<h2><?php esc_html_e( 'Broadcast History', 'plugin-wpshadow' ); ?></h2>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Timestamp', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'User', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Sites Updated', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $log as $entry ) : ?>
						<?php
						$timestamp = isset( $entry['timestamp'] ) ? strtotime( $entry['timestamp'] ) : 0;
						$time_text = $timestamp ? date_i18n( 'M j, Y g:i a', $timestamp ) : '—';
						$user_id   = $entry['user_id'] ?? 0;
						$user      = $user_id > 0 ? get_user_by( 'id', $user_id ) : null;
						$user_name = $user && $user->exists() ? $user->display_name : __( 'Unknown', 'plugin-wpshadow' );
						$count     = count( $entry['site_ids'] ?? array() );
						$status    = $entry['status'] ?? 'success';
						?>
						<tr>
							<td><?php echo esc_html( $time_text ); ?></td>
							<td><?php echo esc_html( $user_name ); ?></td>
							<td><?php echo absint( $count ) . ' ' . esc_html( _n( 'site', 'sites', $count, 'plugin-wpshadow' ) ); ?></td>
							<td>
								<?php
								if ( 'success' === $status ) {
									echo '<span style="color: green;">' . esc_html__( 'Success', 'plugin-wpshadow' ) . '</span>';
								} elseif ( 'partial' === $status ) {
									echo '<span style="color: orange;">' . esc_html__( 'Partial', 'plugin-wpshadow' ) . '</span>';
								} else {
									echo '<span style="color: red;">' . esc_html__( 'Failed', 'plugin-wpshadow' ) . '</span>';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Handle broadcast submission from admin form.
	 *
	 * @return void
	 */
	public static function handle_broadcast_submission(): void {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_safe_redirect( network_admin_url( 'admin.php?page=wps-network-license-broadcast&WPSHADOW_broadcast_error=1' ) );
			exit;
		}

		check_admin_referer( 'wpshadow_broadcast_license', 'wpshadow_broadcast_nonce' );

		$network_key = sanitize_text_field( wp_unslash( $_POST['wpshadow_network_key'] ?? '' ) );
		$site_ids    = isset( $_POST['wpshadow_broadcast_sites'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['wpshadow_broadcast_sites'] ) ) : array();

		if ( empty( $network_key ) || empty( $site_ids ) ) {
			wp_safe_redirect( network_admin_url( 'admin.php?page=wps-network-license-broadcast&WPSHADOW_broadcast_error=1' ) );
			exit;
		}

		// Store network license key.
		update_site_option( self::NETWORK_LICENSE_OPTION, $network_key );

		// Broadcast to selected sites.
		$successful_sites = 0;
		$failed_sites     = 0;

		foreach ( $site_ids as $blog_id ) {
			$result = update_blog_option( $blog_id, 'wpshadow_license_key', $network_key );
			if ( $result ) {
				++$successful_sites;
			} else {
				++$failed_sites;
			}
		}

		// Log the broadcast action.
		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'user_id'   => get_current_user_id(),
			'site_ids'  => $site_ids,
			'status'    => $failed_sites > 0 ? 'partial' : 'success',
		);

		$log   = (array) get_site_option( self::NETWORK_BROADCAST_LOG, array() );
		$log[] = $log_entry;
		update_site_option( self::NETWORK_BROADCAST_LOG, $log );

		wp_safe_redirect( network_admin_url( 'admin.php?page=wps-network-license-broadcast&WPSHADOW_broadcast_success=1' ) );
		exit;
	}
}

/* @changelog
- Version 1.2601.73000: Initial implementation of network-wide license broadcast
	- Added WPSHADOW_Network_License class for Super Admin license broadcasting
	- Network License Broadcast submenu page in Network Admin
	- Select all/individual sites for license push
	- Broadcast audit log to track licensing changes
	- Nonce verification and capability checks for security
	- Automatic option update for each site (WPSHADOW_license_key)
*/


