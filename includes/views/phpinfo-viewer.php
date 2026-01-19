<?php
/**
 * PHP Info Viewer - Display detailed PHP configuration and modules.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wps-phpinfo-viewer-page">
	<h1><?php esc_html_e( 'PHP Info Viewer', 'wpshadow' ); ?></h1>
	
	<p class="description">
		<?php esc_html_e( 'View detailed PHP configuration, loaded extensions, and system information. Useful for troubleshooting compatibility issues and verifying PHP settings.', 'wpshadow' ); ?>
	</p>

	<div class="wps-phpinfo-actions">
		<button type="button" class="button button-primary" id="wps-refresh-phpinfo">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Refresh', 'wpshadow' ); ?>
		</button>
		
		<button type="button" class="button" id="wps-copy-phpinfo">
			<span class="dashicons dashicons-clipboard"></span>
			<?php esc_html_e( 'Copy All', 'wpshadow' ); ?>
		</button>
	</div>

	<div class="wps-phpinfo-container">
		<!-- PHP Version Info -->
		<div class="wps-phpinfo-card">
			<h2><?php esc_html_e( 'PHP Version', 'wpshadow' ); ?></h2>
			<div class="wps-phpinfo-content">
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Version:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-version">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'SAPI:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-sapi">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Operating System:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-os">-</span>
				</div>
			</div>
		</div>

		<!-- PHP Configuration -->
		<div class="wps-phpinfo-card">
			<h2><?php esc_html_e( 'PHP Configuration', 'wpshadow' ); ?></h2>
			<div class="wps-phpinfo-content">
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Memory Limit:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-memory-limit">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Max Execution Time:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-max-execution-time">-</span> <span class="wps-info-unit"><?php esc_html_e( 'seconds', 'wpshadow' ); ?></span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Max Input Time:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-max-input-time">-</span> <span class="wps-info-unit"><?php esc_html_e( 'seconds', 'wpshadow' ); ?></span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Upload Max Size:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-upload-max-filesize">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Post Max Size:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-post-max-size">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Default Charset:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-default-charset">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Date Timezone:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-date-timezone">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Display Errors:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-display-errors">-</span>
				</div>
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Error Reporting:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-error-reporting">-</span>
				</div>
			</div>
		</div>

		<!-- Required Extensions -->
		<div class="wps-phpinfo-card">
			<h2><?php esc_html_e( 'Required Extensions', 'wpshadow' ); ?></h2>
			<div class="wps-phpinfo-content">
				<div class="wps-extensions-list">
					<!-- Extensions will be loaded via AJAX -->
				</div>
			</div>
		</div>

		<!-- Loaded Extensions -->
		<div class="wps-phpinfo-card">
			<h2><?php esc_html_e( 'Loaded Extensions', 'wpshadow' ); ?></h2>
			<div class="wps-phpinfo-content">
				<div class="wps-info-row">
					<span class="wps-info-label"><?php esc_html_e( 'Total Extensions:', 'wpshadow' ); ?></span>
					<span class="wps-info-value php-extensions-count">-</span>
				</div>
				<div class="wps-extensions-tags">
					<!-- Extensions will be loaded via AJAX -->
				</div>
			</div>
		</div>
	</div>

	<style>
		.wps-phpinfo-viewer-page {
			margin: 20px;
		}

		.wps-phpinfo-actions {
			margin: 20px 0;
			display: flex;
			gap: 10px;
		}

		.wps-phpinfo-container {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
			gap: 20px;
			margin-top: 20px;
		}

		.wps-phpinfo-card {
			background: #fff;
			border: 1px solid #ccc;
			border-radius: 4px;
			padding: 20px;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		}

		.wps-phpinfo-card h2 {
			margin: 0 0 15px 0;
			font-size: 18px;
			color: #1d2327;
			border-bottom: 2px solid #0073aa;
			padding-bottom: 10px;
		}

		.wps-phpinfo-content {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.wps-info-row {
			display: flex;
			justify-content: space-between;
			padding: 8px 0;
			border-bottom: 1px solid #e5e5e5;
		}

		.wps-info-row:last-child {
			border-bottom: none;
		}

		.wps-info-label {
			font-weight: 600;
			color: #1d2327;
			min-width: 150px;
		}

		.wps-info-value {
			color: #555;
			word-break: break-word;
			font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
			font-size: 12px;
		}

		.wps-info-unit {
			color: #999;
			font-size: 12px;
			margin-left: 5px;
		}

		.wps-extensions-list {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}

		.wps-extension-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 8px;
			border-radius: 3px;
			background: #f5f5f5;
		}

		.wps-extension-row.installed {
			background: #d4edda;
			border-left: 3px solid #28a745;
		}

		.wps-extension-row.not-installed {
			background: #f8d7da;
			border-left: 3px solid #dc3545;
		}

		.wps-extension-label {
			font-weight: 500;
		}

		.wps-extension-status {
			font-size: 12px;
			font-weight: 600;
		}

		.wps-extension-status.yes {
			color: #28a745;
		}

		.wps-extension-status.no {
			color: #dc3545;
		}

		.wps-extensions-tags {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			margin-top: 10px;
		}

		.wps-extension-tag {
			display: inline-block;
			padding: 4px 12px;
			background: #0073aa;
			color: #fff;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 500;
		}

		.wps-loading {
			text-align: center;
			padding: 40px;
		}

		.wps-loading .spinner {
			display: inline-block;
			vertical-align: middle;
			margin-right: 10px;
		}

		@media (max-width: 768px) {
			.wps-phpinfo-container {
				grid-template-columns: 1fr;
			}

			.wps-info-row {
				flex-direction: column;
				gap: 5px;
			}

			.wps-info-label {
				min-width: auto;
			}
		}
	</style>

	<script>
		( function() {
			'use strict';

			const PhpInfo = {
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_diagnostics' ) ); ?>',
				ajaxUrl: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',

				init() {
					document.getElementById( 'wps-refresh-phpinfo' ).addEventListener( 'click', () => this.loadPhpInfo() );
					document.getElementById( 'wps-copy-phpinfo' ).addEventListener( 'click', () => this.copyToClipboard() );
					this.loadPhpInfo();
				},

				loadPhpInfo() {
					const container = document.querySelector( '.wps-phpinfo-container' );
					container.innerHTML = '<div class="wps-loading"><span class="spinner is-active"></span><?php esc_html_e( 'Loading PHP information...', 'wpshadow' ); ?></div>';

					fetch( this.ajaxUrl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams( {
							action: 'wpshadow_get_phpinfo',
							nonce: this.nonce,
						} ),
					} )
					.then( response => response.json() )
					.then( data => {
						if ( data.success ) {
							this.displayPhpInfo( data.data );
						} else {
							container.innerHTML = '<div class="notice notice-error"><p>' + ( data.data?.message || '<?php esc_html_e( 'Failed to load PHP information', 'wpshadow' ); ?>' ) + '</p></div>';
						}
					} )
					.catch( error => {
						console.error( error );
						container.innerHTML = '<div class="notice notice-error"><p><?php esc_html_e( 'Error loading PHP information', 'wpshadow' ); ?></p></div>';
					} );
				},

				displayPhpInfo( data ) {
					const phpInfo = data.php_info || {};
					const extensionsStatus = data.extensions_status || {};

					// Update version info
					document.querySelector( '.php-version' ).textContent = phpInfo.version || '-';
					document.querySelector( '.php-sapi' ).textContent = phpInfo.sapi || '-';
					document.querySelector( '.php-os' ).textContent = phpInfo.os || '-';

					// Update configuration
					document.querySelector( '.php-memory-limit' ).textContent = phpInfo.memory_limit || '-';
					document.querySelector( '.php-max-execution-time' ).textContent = phpInfo.max_execution_time || '-';
					document.querySelector( '.php-max-input-time' ).textContent = phpInfo.max_input_time || '-';
					document.querySelector( '.php-upload-max-filesize' ).textContent = phpInfo.upload_max_filesize || '-';
					document.querySelector( '.php-post-max-size' ).textContent = phpInfo.post_max_size || '-';
					document.querySelector( '.php-default-charset' ).textContent = phpInfo.default_charset || '-';
					document.querySelector( '.php-date-timezone' ).textContent = phpInfo.date_timezone || '-';
					document.querySelector( '.php-display-errors' ).textContent = phpInfo.display_errors || '-';
					document.querySelector( '.php-error-reporting' ).textContent = phpInfo.error_reporting || '-';
					document.querySelector( '.php-extensions-count' ).textContent = phpInfo.extensions_count || '0';

					// Update required extensions
					const extensionsList = document.querySelector( '.wps-extensions-list' );
					extensionsList.innerHTML = '';
					for ( const [ ext, status ] of Object.entries( extensionsStatus ) ) {
						const row = document.createElement( 'div' );
						row.className = 'wps-extension-row ' + ( status.installed ? 'installed' : 'not-installed' );
						row.innerHTML = `
							<span class="wps-extension-label">${this.escapeHtml( status.label )}</span>
							<span class="wps-extension-status ${status.installed ? 'yes' : 'no'}">
								${status.installed ? '✓ <?php esc_html_e( 'Installed', 'wpshadow' ); ?>' : '✗ <?php esc_html_e( 'Not Installed', 'wpshadow' ); ?>'}
							</span>
						`;
						extensionsList.appendChild( row );
					}

					// Update all loaded extensions
					const extensionsTags = document.querySelector( '.wps-extensions-tags' );
					extensionsTags.innerHTML = '';
					if ( phpInfo.extensions && phpInfo.extensions.length > 0 ) {
						phpInfo.extensions.forEach( ext => {
							const tag = document.createElement( 'span' );
							tag.className = 'wps-extension-tag';
							tag.textContent = ext;
							extensionsTags.appendChild( tag );
						} );
					}
				},

				copyToClipboard() {
					let text = 'PHP Information Report\n';
					text += '======================\n\n';
					text += 'Version: ' + ( document.querySelector( '.php-version' ).textContent || '-' ) + '\n';
					text += 'SAPI: ' + ( document.querySelector( '.php-sapi' ).textContent || '-' ) + '\n';
					text += 'OS: ' + ( document.querySelector( '.php-os' ).textContent || '-' ) + '\n';
					text += 'Memory Limit: ' + ( document.querySelector( '.php-memory-limit' ).textContent || '-' ) + '\n';
					text += 'Max Execution Time: ' + ( document.querySelector( '.php-max-execution-time' ).textContent || '-' ) + ' seconds\n';
					text += 'Upload Max Size: ' + ( document.querySelector( '.php-upload-max-filesize' ).textContent || '-' ) + '\n';
					text += 'Extensions: ' + ( document.querySelector( '.php-extensions-count' ).textContent || '0' ) + '\n';

					navigator.clipboard.writeText( text ).then( () => {
						alert( '<?php esc_html_e( 'PHP information copied to clipboard', 'wpshadow' ); ?>' );
					} ).catch( () => {
						alert( '<?php esc_html_e( 'Failed to copy to clipboard', 'wpshadow' ); ?>' );
					} );
				},

				escapeHtml( text ) {
					const div = document.createElement( 'div' );
					div.textContent = text;
					return div.innerHTML;
				},
			};

			document.addEventListener( 'DOMContentLoaded', () => PhpInfo.init() );
		}() );
	</script>
</div>
