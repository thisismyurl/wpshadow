<?php
/**
 * Cloud Integration Block for KB Articles
 *
 * Displays contextual cloud information to logged-in users:
 * - Last backup timestamp
 * - One-click backup button
 * - Site connection status
 * - Link to cloud dashboard
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\KB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Cloud Integration Block
 */
class KB_Cloud_Integration_Block {

	/**
	 * Register the block.
	 */
	public static function register(): void {
		register_block_type(
			'wpshadow/kb-cloud-integration',
			[
				'render_callback' => [ __CLASS__, 'render' ],
				'editor_script'   => 'wpshadow-kb-cloud-integration',
				'attributes'      => [
					'title'    => [
						'type'    => 'string',
						'default' => 'Your Site Status',
					],
					'showLastBackup' => [
						'type'    => 'boolean',
						'default' => true,
					],
					'showBackupButton' => [
						'type'    => 'boolean',
						'default' => true,
					],
					'backgroundColor' => [
						'type'    => 'string',
						'default' => '#f5f5f5',
					],
				],
			]
		);

		// Register block editor assets
		self::register_assets();
	}

	/**
	 * Register block assets.
	 */
	private static function register_assets(): void {
		$script_url = plugin_dir_url( __FILE__ ) . 'assets/kb-cloud-integration-block.js';
		wp_register_script(
			'wpshadow-kb-cloud-integration',
			$script_url,
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n' ],
			'1.0.0',
			true
		);
	}

	/**
	 * Render the block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public static function render( array $attributes ): string {
		// Check if user is logged in (for now, always show - Guardian handles auth)
		$is_registered = get_option( 'wpshadow_cloud_token' );
		$is_connected  = ! empty( $is_registered );

		// If not connected, show login prompt
		if ( ! $is_connected ) {
			return self::render_not_connected( $attributes );
		}

		// User is connected - show status
		return self::render_connected( $attributes );
	}

	/**
	 * Render "not connected" state.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	private static function render_not_connected( array $attributes ): string {
		$title = isset( $attributes['title'] ) ? esc_html( $attributes['title'] ) : 'Your Site Status';
		$bg    = isset( $attributes['backgroundColor'] ) ? esc_attr( $attributes['backgroundColor'] ) : '#f5f5f5';

		$html = '<div class="wpshadow-kb-cloud-integration wpshadow-kb-not-connected" style="background-color: ' . $bg . '; padding: 20px; border-radius: 8px; margin: 20px 0;">';
		$html .= '<h3>' . $title . '</h3>';
		$html .= '<p><strong>Connect your site to WPShadow Cloud</strong></p>';
		$html .= '<p>Sign up for free at <a href="https://wpshadow.com" target="_blank" rel="noopener">wpshadow.com</a> to:</p>';
		$html .= '<ul>';
		$html .= '<li>See backup status right here in your KB articles</li>';
		$html .= '<li>Run one-click backups from your WordPress site</li>';
		$html .= '<li>Monitor site health across all your websites</li>';
		$html .= '<li>Get security alerts and recommendations</li>';
		$html .= '</ul>';
		$html .= '<p><a href="' . admin_url( 'admin.php?page=wpshadow' ) . '" class="button button-primary">Register Your Site</a></p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render "connected" state with backup info.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	private static function render_connected( array $attributes ): string {
		$title = isset( $attributes['title'] ) ? esc_html( $attributes['title'] ) : 'Your Site Status';
		$bg    = isset( $attributes['backgroundColor'] ) ? esc_attr( $attributes['backgroundColor'] ) : '#e8f5e9';
		$show_backup = isset( $attributes['showLastBackup'] ) ? (bool) $attributes['showLastBackup'] : true;
		$show_button = isset( $attributes['showBackupButton'] ) ? (bool) $attributes['showBackupButton'] : true;

		$html = '<div class="wpshadow-kb-cloud-integration wpshadow-kb-connected" style="background-color: ' . $bg . '; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4caf50;">';
		$html .= '<h3 style="margin-top: 0;">✓ ' . $title . '</h3>';

		if ( $show_backup ) {
			$last_backup = self::get_last_backup_info();
			if ( $last_backup ) {
				$html .= '<p><strong>Last Backup:</strong> ' . $last_backup . '</p>';
			} else {
				$html .= '<p><strong>Last Backup:</strong> No backups found</p>';
			}
		}

		if ( $show_button ) {
			$html .= '<p>';
			$html .= '<button class="wpshadow-backup-button button button-primary" ';
			$html .= 'data-site-id="' . esc_attr( get_option( 'wpshadow_site_id', '' ) ) . '" ';
			$html .= 'style="margin-right: 10px;">';
			$html .= 'Run Backup Now';
			$html .= '</button>';
			$html .= '<a href="https://wpshadow.com/dashboard" target="_blank" rel="noopener" class="button">View in Cloud Dashboard</a>';
			$html .= '</p>';

			// Add inline script for backup button
			$html .= self::get_backup_button_script();
		}

		$html .= '<p class="wps-m-10">';
		$html .= 'Connected to WPShadow Cloud • ';
		$html .= '<a href="' . admin_url( 'admin.php?page=wpshadow' ) . '">Manage Settings</a>';
		$html .= '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get formatted last backup info.
	 *
	 * @return string|false Formatted timestamp or false if not found.
	 */
	private static function get_last_backup_info() {
		// Get from cloud API
		$last_backup = get_transient( 'wpshadow_last_backup_time' );

		if ( false === $last_backup ) {
			// Try to fetch from cloud
			$last_backup = self::fetch_last_backup_from_cloud();

			if ( $last_backup ) {
				// Cache for 1 hour
				set_transient( 'wpshadow_last_backup_time', $last_backup, HOUR_IN_SECONDS );
			}
		}

		if ( ! $last_backup ) {
			return false;
		}

		// Format for display
		$backup_time = strtotime( $last_backup );
		if ( ! $backup_time ) {
			return false;
		}

		return wp_date( 'F j, Y \a\t g:i A', $backup_time );
	}

	/**
	 * Fetch last backup from cloud API.
	 *
	 * @return string|false ISO timestamp of last backup or false.
	 */
	private static function fetch_last_backup_from_cloud() {
		// Use Cloud_Client if available
		if ( ! class_exists( '\WPShadow\Cloud\Cloud_Client' ) ) {
			return false;
		}

		$response = \WPShadow\Cloud\Cloud_Client::request( 'GET', '/backups/latest' );

		if ( isset( $response['error'] ) ) {
			return false;
		}

		return $response['timestamp'] ?? false;
	}

	/**
	 * Get inline JavaScript for backup button.
	 *
	 * @return string Script tag.
	 */
	private static function get_backup_button_script(): string {
		ob_start();
		?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	const backupBtn = document.querySelector('.wpshadow-backup-button');
	if (!backupBtn) return;

	backupBtn.addEventListener('click', async function(e) {
		e.preventDefault();
		const siteId = this.dataset.siteId;
		const originalText = this.textContent;

		// Disable button and show loading state
		this.disabled = true;
		this.textContent = 'Running backup...';

		try {
			// Call backup API via cloud
			const response = await fetch('https://api.wpshadow.com/v1/backups/trigger', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + wpshadow.token,
					'X-Site-ID': siteId,
				},
			});

			if (response.ok) {
				this.textContent = '✓ Backup started!';
				this.style.backgroundColor = '#4caf50';
				setTimeout(() => {
					this.textContent = originalText;
					this.disabled = false;
					this.style.backgroundColor = '';
				}, 3000);
				// Invalidate cache
				fetch(wpshadow.ajaxUrl, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'action=wpshadow_clear_backup_cache&_wpnonce=' + wpshadow.nonce,
				});
			} else {
				throw new Error('Backup failed');
			}
		} catch (err) {
			alert('Error starting backup: ' + err.message);
			this.textContent = originalText;
			this.disabled = false;
		}
	});
});
</script>
		<?php
		return ob_get_clean();
	}
}

// Initialization happens from module.php, no auto-registration needed
