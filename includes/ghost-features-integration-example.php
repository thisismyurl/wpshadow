<?php
/**
 * Ghost Features Integration Example
 *
 * This file shows how to integrate the Ghost Features system with your module catalog.
 * 
 * Steps:
 * 1. Add ghost_features array to catalog entries in class-wps-module-registry.php
 * 2. Initialize ghost features system in main plugin file
 * 3. Register catalog features on plugins_loaded
 * 4. Display features on dashboard or settings pages
 *
 * @package    WP_Support
 * @subpackage Core
 * @since      1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize Ghost Features System
 *
 * Call this in your main plugin file after all classes are loaded.
 *
 * @return void
 */
function init_ghost_features_system(): void {
	// Initialize ghost features.
	WPSHADOW_Ghost_Features::init();
	
	// Initialize dashboard widget.
	WPSHADOW_Features_Discovery_Widget::init();
	
	// Register features from catalog.
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\register_ghost_features_from_catalog', 20 );
}

/**
 * Register ghost features from module catalog.
 *
 * This function loads the catalog and registers all ghost features.
 *
 * @return void
 */
function register_ghost_features_from_catalog(): void {
	// Load ghost features definitions.
	require_once __DIR__ . '/ghost-features-catalog.php';
	
	$catalog = get_ghost_features_catalog();
	
	foreach ( $catalog as $module_slug => $features ) {
		// Check if module is installed.
		$is_installed = WPSHADOW_Module_Registry::is_installed( $module_slug );
		
		// Get module metadata from registry.
		$module_data = self::get_module_metadata( $module_slug );
		
		foreach ( $features as $feature ) {
			WPSHADOW_Ghost_Features::register_feature(
				array_merge(
					$feature,
					array(
						'module_slug'     => $module_slug,
						'module_name'     => $module_data['name'] ?? '',
						'module_type'     => $module_data['type'] ?? 'spoke',
						'is_available'    => $is_installed,
						'download_url'    => $module_data['download_url'] ?? '',
						'requires_core'   => $module_data['requires_core'] ?? '',
						'requires_php'    => $module_data['requires_php'] ?? '',
						'requires_wp'     => $module_data['requires_wp'] ?? '',
						'requires_hub'    => $module_data['requires_hub'] ?? '',
					)
				)
			);
		}
	}
}

/**
 * Get module metadata from registry.
 *
 * @param string $module_slug Module slug.
 * @return array Module metadata.
 */
function get_module_metadata( string $module_slug ): array {
	$catalog = WPSHADOW_Module_Registry::get_catalog_modules();
	
	foreach ( $catalog as $module ) {
		if ( $module['slug'] === $module_slug ) {
			return $module;
		}
	}
	
	return array();
}

// ============================================================================
// EXAMPLE USAGE IN ADMIN PAGES
// ============================================================================

/**
 * Display ghost features on a settings page.
 *
 * Example of how to show ghost features in your admin interface.
 *
 * @return void
 */
function example_display_backup_features(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Backup Features', 'plugin-wpshadow' ); ?></h1>
		
		<!-- Show backup verification (core feature) -->
		<div class="wps-core-features">
			<h2><?php esc_html_e( 'Active Backup Features', 'plugin-wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'These features are currently active in your installation:', 'plugin-wpshadow' ); ?></p>
			
			<!-- Your existing backup UI here -->
		</div>
		
		<!-- Show ghost features for backup category -->
		<div class="wps-enhanced-features">
			<h2><?php esc_html_e( 'Enhanced Backup Features', 'plugin-wpshadow' ); ?></h2>
			<?php
			WPSHADOW_Ghost_Features::render_category_features(
				'backup',
				array(
					'include_installed' => false, // Only show unavailable features.
					'show_install_button' => true,
					'show_benefits' => true,
					'columns' => 2,
				)
			);
			?>
		</div>
	</div>
	<?php
}

/**
 * Display compact feature upgrade prompt.
 *
 * Example of inline upgrade prompts in existing UI.
 *
 * @return void
 */
function example_inline_upgrade_prompt(): void {
	if ( ! WPSHADOW_Feature_Detector::has_vault() ) {
		?>
		<div class="notice notice-info inline" style="margin: 15px 0;">
			<p>
				<strong><?php esc_html_e( '🔒 Want encrypted backups?', 'plugin-wpshadow' ); ?></strong>
				<?php esc_html_e( 'Install the free Vault module for AES-256 encryption, cloud offload, and file versioning.', 'plugin-wpshadow' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&tab=modules&install=vault-wpshadow' ) ); ?>" class="button button-primary" style="margin-left: 10px;">
					<?php esc_html_e( 'Install Vault (Free)', 'plugin-wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}

/**
 * Display feature comparison table.
 *
 * Example of comparing core vs enhanced features.
 *
 * @return void
 */
function example_feature_comparison_table(): void {
	?>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Feature', 'plugin-wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Core', 'plugin-wpshadow' ); ?></th>
				<th><?php esc_html_e( 'With Vault', 'plugin-wpshadow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Backup Verification', 'plugin-wpshadow' ); ?></td>
				<td>✅ <?php esc_html_e( 'Included', 'plugin-wpshadow' ); ?></td>
				<td>✅ <?php esc_html_e( 'Included', 'plugin-wpshadow' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Encrypted Storage', 'plugin-wpshadow' ); ?></td>
				<td>❌ <?php esc_html_e( 'Not available', 'plugin-wpshadow' ); ?></td>
				<td>✅ <?php esc_html_e( 'AES-256 Encryption', 'plugin-wpshadow' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Cloud Offload', 'plugin-wpshadow' ); ?></td>
				<td>❌ <?php esc_html_e( 'Not available', 'plugin-wpshadow' ); ?></td>
				<td>✅ <?php esc_html_e( 'S3, Wasabi, Backblaze', 'plugin-wpshadow' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'File Versioning', 'plugin-wpshadow' ); ?></td>
				<td>❌ <?php esc_html_e( 'Not available', 'plugin-wpshadow' ); ?></td>
				<td>✅ <?php esc_html_e( 'Point-in-time Recovery', 'plugin-wpshadow' ); ?></td>
			</tr>
		</tbody>
	</table>
	
	<?php if ( ! WPSHADOW_Feature_Detector::has_vault() ) : ?>
		<p style="margin-top: 15px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&tab=modules&install=vault-wpshadow' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Upgrade to Vault (Free)', 'plugin-wpshadow' ); ?>
			</a>
		</p>
	<?php endif; ?>
	<?php
}

// ============================================================================
// EXAMPLE: CUSTOM GHOST FEATURE REGISTRATION
// ============================================================================

/**
 * Register custom ghost features programmatically.
 *
 * Example of registering features via hook instead of catalog.
 *
 * @return void
 */
function example_register_custom_ghost_features(): void {
	add_action( 'wpshadow_register_ghost_features', function() {
		// Example: Register a pro feature that doesn't exist yet.
		WPSHADOW_Ghost_Features::register_feature(
			array(
				'key'         => 'multi_cloud_backup',
				'title'       => __( 'Multi-Cloud Backup Redundancy', 'plugin-wpshadow' ),
				'description' => __( 'Automatically sync backups to multiple cloud providers for maximum redundancy.', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-cloud-saved',
				'category'    => 'backup',
				'priority'    => 100,
				'module_slug' => 'vault-pro-wpshadow', // Future pro version.
				'module_name' => 'Vault Pro',
				'module_type' => 'hub',
				'is_available' => false, // Not available yet.
				'benefits'    => array(
					__( 'Sync to S3, Wasabi, Backblaze simultaneously', 'plugin-wpshadow' ),
					__( 'Geographic redundancy across continents', 'plugin-wpshadow' ),
					__( '99.999% availability guarantee', 'plugin-wpshadow' ),
				),
				'use_cases'   => array(
					__( 'Enterprise clients with strict SLAs', 'plugin-wpshadow' ),
					__( 'Mission-critical applications', 'plugin-wpshadow' ),
					__( 'Regulated industries requiring multi-cloud compliance', 'plugin-wpshadow' ),
				),
			)
		);
	}, 10 );
}

// ============================================================================
// INTEGRATION CHECKLIST
// ============================================================================

/**
 * To integrate the Ghost Features system into your plugin:
 *
 * 1. ✅ Include class-wps-ghost-features.php
 * 2. ✅ Include class-wps-features-discovery-widget.php
 * 3. ✅ Include ghost-features-catalog.php
 * 4. ✅ Call init_ghost_features_system() in main plugin file
 * 5. ✅ Add ghost_features to catalog JSON in class-wps-module-registry.php
 * 6. ✅ Display features using WPSHADOW_Ghost_Features::render_*() methods
 * 7. ✅ Test with modules installed and uninstalled
 * 8. ✅ Verify install links work correctly
 * 9. ✅ Check feature badges display properly
 * 10. ✅ Ensure ghost features don't block functionality
 */
