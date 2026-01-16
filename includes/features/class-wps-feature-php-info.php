<?php
/**
 * Feature: PHP Information Viewer
 *
 * Displays PHP configuration and environment information:
 * - PHP version and loaded extensions
 * - Memory limits and execution time
 * - Loaded modules and their versions
 * - Configuration directives
 * - Server environment variables
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_PHP_Info
 *
 * PHP information and diagnostics viewer.
 */
final class WPSHADOW_Feature_PHP_Info extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'php-info',
				'name'               => __( 'PHP Information', 'plugin-wpshadow' ),
			'description'        => __( 'Shows a read only view of PHP version, extensions, limits, and server environment details in one place so you can troubleshoot compatibility or hosting issues. Useful when plugins or support teams need exact configuration without shell access, and safer than exposing phpinfo publicly.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'server-diagnostics',
				'widget_label'       => __( 'Server Diagnostics', 'plugin-wpshadow' ),
				'widget_description' => __( 'Server environment and configuration tools', 'plugin-wpshadow' ),
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-generic',
				'category'           => 'debugging',
				'priority'           => 20,
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 20,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wpshadow',
			__( 'PHP Information', 'plugin-wpshadow' ),
			__( 'PHP Info', 'plugin-wpshadow' ),
			'manage_options',
			'wpshadow-php-info',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Get PHP information array.
	 *
	 * @return array PHP information.
	 */
	private function get_php_info(): array {
		$info = array(
			'version'            => PHP_VERSION,
			'sapi'               => php_sapi_name(),
			'memory_limit'       => ini_get( 'memory_limit' ),
			'max_execution_time' => ini_get( 'max_execution_time' ),
			'post_max_size'      => ini_get( 'post_max_size' ),
			'upload_max_size'    => ini_get( 'upload_max_filesize' ),
			'display_errors'     => ini_get( 'display_errors' ),
			'error_reporting'    => ini_get( 'error_reporting' ),
			'log_errors'         => ini_get( 'log_errors' ),
			'error_log'          => ini_get( 'error_log' ),
			'timezone'           => ini_get( 'date.timezone' ),
			'extensions'         => get_loaded_extensions(),
		);

		return $info;
	}

	/**
	 * Get critical PHP extensions for WordPress.
	 *
	 * @return array Extension status.
	 */
	private function get_critical_extensions(): array {
		$critical = array(
			'curl'       => array(
				'name'        => 'cURL',
				'description' => __( 'Required for HTTP requests and external API calls', 'plugin-wpshadow' ),
			),
			'json'       => array(
				'name'        => 'JSON',
				'description' => __( 'Required for REST API and data serialization', 'plugin-wpshadow' ),
			),
			'mbstring'   => array(
				'name'        => 'Multibyte String',
				'description' => __( 'Recommended for internationalization support', 'plugin-wpshadow' ),
			),
			'xml'        => array(
				'name'        => 'XML',
				'description' => __( 'Required for XML parsing and RSS feeds', 'plugin-wpshadow' ),
			),
			'zip'        => array(
				'name'        => 'Zip',
				'description' => __( 'Required for plugin/theme installation', 'plugin-wpshadow' ),
			),
			'gd'         => array(
				'name'        => 'GD',
				'description' => __( 'Required for image manipulation', 'plugin-wpshadow' ),
			),
			'imagick'    => array(
				'name'        => 'ImageMagick',
				'description' => __( 'Alternative image manipulation library', 'plugin-wpshadow' ),
			),
			'mysqli'     => array(
				'name'        => 'MySQLi',
				'description' => __( 'Required for database connectivity', 'plugin-wpshadow' ),
			),
			'openssl'    => array(
				'name'        => 'OpenSSL',
				'description' => __( 'Required for secure connections', 'plugin-wpshadow' ),
			),
			'fileinfo'   => array(
				'name'        => 'Fileinfo',
				'description' => __( 'Required for file type detection', 'plugin-wpshadow' ),
			),
			'exif'       => array(
				'name'        => 'EXIF',
				'description' => __( 'Required for image metadata reading', 'plugin-wpshadow' ),
			),
		);

		$status = array();
		foreach ( $critical as $ext => $data ) {
			$status[ $ext ] = array(
				'name'        => $data['name'],
				'description' => $data['description'],
				'loaded'      => extension_loaded( $ext ),
			);
		}

		return $status;
	}

	/**
	 * Render PHP info page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$info       = $this->get_php_info();
		$extensions = $this->get_critical_extensions();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'PHP Information', 'plugin-wpshadow' ); ?></h1>

			<div class="card">
				<h2><?php esc_html_e( 'PHP Configuration', 'plugin-wpshadow' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<tr>
							<th style="width: 30%;"><?php esc_html_e( 'PHP Version', 'plugin-wpshadow' ); ?></th>
							<td>
								<code><?php echo esc_html( $info['version'] ); ?></code>
								<?php if ( version_compare( PHP_VERSION, '8.0', '>=' ) ) : ?>
									<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
								<?php elseif ( version_compare( PHP_VERSION, '7.4', '>=' ) ) : ?>
									<span class="dashicons dashicons-warning" style="color: #f0ad4e;"></span>
									<small><?php esc_html_e( 'Consider upgrading to PHP 8.0+', 'plugin-wpshadow' ); ?></small>
								<?php else : ?>
									<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
									<small><?php esc_html_e( 'Outdated! Upgrade recommended', 'plugin-wpshadow' ); ?></small>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Server API', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $info['sapi'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Memory Limit', 'plugin-wpshadow' ); ?></th>
							<td>
								<code><?php echo esc_html( $info['memory_limit'] ); ?></code>
								<?php
								$memory_limit = wp_convert_hr_to_bytes( $info['memory_limit'] );
								if ( $memory_limit < 128 * 1024 * 1024 ) :
									?>
									<span class="dashicons dashicons-warning" style="color: #f0ad4e;"></span>
									<small><?php esc_html_e( 'Consider increasing to 128M or higher', 'plugin-wpshadow' ); ?></small>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Max Execution Time', 'plugin-wpshadow' ); ?></th>
							<td>
								<code><?php echo esc_html( $info['max_execution_time'] ); ?></code> <?php esc_html_e( 'seconds', 'plugin-wpshadow' ); ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'POST Max Size', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $info['post_max_size'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Upload Max Filesize', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $info['upload_max_size'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Display Errors', 'plugin-wpshadow' ); ?></th>
							<td>
								<code><?php echo esc_html( $info['display_errors'] ? 'On' : 'Off' ); ?></code>
								<?php if ( $info['display_errors'] && ! WP_DEBUG ) : ?>
									<span class="dashicons dashicons-warning" style="color: #f0ad4e;"></span>
									<small><?php esc_html_e( 'Should be Off in production', 'plugin-wpshadow' ); ?></small>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Log Errors', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $info['log_errors'] ? 'On' : 'Off' ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Error Log Path', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $info['error_log'] ? $info['error_log'] : __( 'Not set', 'plugin-wpshadow' ) ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Timezone', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $info['timezone'] ? $info['timezone'] : __( 'Not set', 'plugin-wpshadow' ) ); ?></code></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Critical PHP Extensions', 'plugin-wpshadow' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 80px;"><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
							<th style="width: 200px;"><?php esc_html_e( 'Extension', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Description', 'plugin-wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $extensions as $ext_key => $ext_data ) : ?>
							<tr>
								<td>
									<?php if ( $ext_data['loaded'] ) : ?>
										<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
										<?php esc_html_e( 'Loaded', 'plugin-wpshadow' ); ?>
									<?php else : ?>
										<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
										<?php esc_html_e( 'Missing', 'plugin-wpshadow' ); ?>
									<?php endif; ?>
								</td>
								<td><strong><?php echo esc_html( $ext_data['name'] ); ?></strong></td>
								<td><?php echo esc_html( $ext_data['description'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'All Loaded Extensions', 'plugin-wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Below is a complete list of all loaded PHP extensions:', 'plugin-wpshadow' ); ?></p>
				<div style="columns: 3; -webkit-columns: 3; -moz-columns: 3;">
					<?php foreach ( $info['extensions'] as $extension ) : ?>
						<div style="margin-bottom: 5px;">
							<span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 14px;"></span>
							<code><?php echo esc_html( $extension ); ?></code>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Full phpinfo()', 'plugin-wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'View the complete PHP information output:', 'plugin-wpshadow' ); ?></p>
				<p>
					<button type="button" id="wps-show-phpinfo" class="button button-secondary">
						<?php esc_html_e( 'Show Full phpinfo()', 'plugin-wpshadow' ); ?>
					</button>
				</p>
				<div id="wps-phpinfo-output" style="display: none; margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccc; overflow: auto; max-height: 600px;">
					<?php
					ob_start();
					phpinfo();
					$phpinfo = ob_get_clean();
					
					// Extract body content from phpinfo output.
					if ( preg_match( '/<body[^>]*>(.*)<\/body>/si', $phpinfo, $matches ) ) {
						$phpinfo = $matches[1];
					}
					
					// Remove inline styles that might conflict.
					$phpinfo = preg_replace( '/<style[^>]*>.*?<\/style>/si', '', $phpinfo );
					
					// phpinfo is safe HTML output from PHP itself.
					echo $phpinfo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#wps-show-phpinfo').on('click', function() {
				const $button = $(this);
				const $output = $('#wps-phpinfo-output');
				
				if ($output.is(':visible')) {
					$output.slideUp();
					$button.text('<?php echo esc_js( __( 'Show Full phpinfo()', 'plugin-wpshadow' ) ); ?>');
				} else {
					$output.slideDown();
					$button.text('<?php echo esc_js( __( 'Hide Full phpinfo()', 'plugin-wpshadow' ) ); ?>');
				}
			});
		});
		</script>
		<?php
	}
}
