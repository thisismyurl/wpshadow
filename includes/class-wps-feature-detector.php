<?php
/**
 * Feature Detector - Determines available functionality based on installed modules
 *
 * Provides graceful degradation and upgrade prompts for enhanced features.
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
 * WPS_Feature_Detector Class
 *
 * Detects which modules are installed and what features are available.
 */
class WPS_Feature_Detector {

	/**
	 * Feature capability cache.
	 *
	 * @var array|null
	 */
	private static ?array $capabilities_cache = null;

	/**
	 * Check if Vault module is installed and active.
	 *
	 * @return bool True if Vault is available.
	 */
	public static function has_vault(): bool {
		return class_exists( '\\WPS\\VaultSupport\\WPS_Vault' );
	}

	/**
	 * Check if advanced backup features are available.
	 *
	 * @return bool True if advanced backup is available.
	 */
	public static function has_advanced_backup(): bool {
		return self::has_vault();
	}

	/**
	 * Check if encrypted storage is available.
	 *
	 * @return bool True if encryption is available.
	 */
	public static function has_encrypted_storage(): bool {
		return self::has_vault();
	}

	/**
	 * Check if cloud offload is available.
	 *
	 * @return bool True if cloud offload is available.
	 */
	public static function has_cloud_offload(): bool {
		return self::has_vault();
	}

	/**
	 * Get all feature capabilities.
	 *
	 * Integrates with Ghost Features system for consistent feature detection.
	 *
	 * @return array Feature availability map.
	 */
	public static function get_capabilities(): array {
		if ( null !== self::$capabilities_cache ) {
			return self::$capabilities_cache;
		}

		$has_vault = self::has_vault();

		// Core plugin capabilities (always available).
		$core_capabilities = array(
			'backup_verification'   => array(
				'available'   => true,
				'level'       => 'core',
				'description' => __( 'Snapshot-based backup verification and restore testing', 'plugin-wp-support-thisismyurl' ),
			),
			'automated_scheduling'  => array(
				'available'   => true,
				'level'       => 'core',
				'description' => __( 'Schedule automatic backup verification tests', 'plugin-wp-support-thisismyurl' ),
			),
			'integrity_monitoring'  => array(
				'available'   => true,
				'level'       => 'core',
				'description' => __( 'Database and file integrity checks', 'plugin-wp-support-thisismyurl' ),
			),
			'restore_simulation'    => array(
				'available'   => true,
				'level'       => 'core',
				'description' => __( 'Test restore procedures in staging environment', 'plugin-wp-support-thisismyurl' ),
			),
		);

		// Vault module capabilities (available when installed).
		$vault_capabilities = array(
			'encrypted_backups'     => array(
				'available'   => $has_vault,
				'level'       => $has_vault ? 'vault' : 'upgrade',
				'description' => __( 'AES-256 encrypted backup storage for sensitive data', 'plugin-wp-support-thisismyurl' ),
				'upgrade_url' => admin_url( 'admin.php?page=wp-support&tab=modules&install=vault-support-thisismyurl' ),
			),
			'cloud_offload'         => array(
				'available'   => $has_vault,
				'level'       => $has_vault ? 'vault' : 'upgrade',
				'description' => __( 'Automatic offsite backup to cloud storage providers', 'plugin-wp-support-thisismyurl' ),
				'upgrade_url' => admin_url( 'admin.php?page=wp-support&tab=modules&install=vault-support-thisismyurl' ),
			),
			'file_versioning'       => array(
				'available'   => $has_vault,
				'level'       => $has_vault ? 'vault' : 'upgrade',
				'description' => __( 'Keep multiple versions of files with one-click rollback', 'plugin-wp-support-thisismyurl' ),
				'upgrade_url' => admin_url( 'admin.php?page=wp-support&tab=modules&install=vault-support-thisismyurl' ),
			),
			'compression'           => array(
				'available'   => $has_vault,
				'level'       => $has_vault ? 'vault' : 'upgrade',
				'description' => __( 'Intelligent compression reduces storage by up to 70%', 'plugin-wp-support-thisismyurl' ),
				'upgrade_url' => admin_url( 'admin.php?page=wp-support&tab=modules&install=vault-support-thisismyurl' ),
			),
			'deduplication'         => array(
				'available'   => $has_vault,
				'level'       => $has_vault ? 'vault' : 'upgrade',
				'description' => __( 'Eliminate duplicate files to save space', 'plugin-wp-support-thisismyurl' ),
				'upgrade_url' => admin_url( 'admin.php?page=wp-support&tab=modules&install=vault-support-thisismyurl' ),
			),
			'broken_link_guardian'  => array(
				'available'   => $has_vault,
				'level'       => $has_vault ? 'vault' : 'upgrade',
				'description' => __( 'Prevent broken links when media files are deleted', 'plugin-wp-support-thisismyurl' ),
				'upgrade_url' => admin_url( 'admin.php?page=wp-support&tab=modules&install=vault-support-thisismyurl' ),
			),
		);

		self::$capabilities_cache = array_merge( $core_capabilities, $vault_capabilities );

		// Allow Ghost Features system to sync with Feature Detector.
		self::$capabilities_cache = apply_filters( 'WPS_feature_capabilities', self::$capabilities_cache );

		return self::$capabilities_cache;
	}

	/**
	 * Get missing features that require Vault.
	 *
	 * @return array List of unavailable features.
	 */
	public static function get_missing_vault_features(): array {
		$capabilities = self::get_capabilities();
		return array_filter(
			$capabilities,
			function ( $capability ) {
				return ! $capability['available'] && 'upgrade' === $capability['level'];
			}
		);
	}

	/**
	 * Render upgrade prompt for missing features.
	 *
	 * @param string $context Context where prompt is shown (backup, storage, etc).
	 * @return void
	 */
	public static function render_upgrade_prompt( string $context = 'backup' ): void {
		if ( self::has_vault() ) {
			return;
		}

		$missing_features = self::get_missing_vault_features();
		if ( empty( $missing_features ) ) {
			return;
		}

		$install_url = admin_url( 'admin.php?page=wp-support&tab=modules&install=vault-support-thisismyurl' );
		?>
		<div class="notice notice-info wps-vault-upgrade-prompt" style="border-left-color: #2271b1; padding: 15px;">
			<h3 style="margin-top: 0;">
				<span class="dashicons dashicons-vault" style="font-size: 24px; vertical-align: middle;"></span>
				<?php esc_html_e( 'Unlock Advanced Backup Features', 'plugin-wp-support-thisismyurl' ); ?>
			</h3>
			
			<p>
				<?php esc_html_e( 'Install the free Vault module to enhance your backup capabilities:', 'plugin-wp-support-thisismyurl' ); ?>
			</p>

			<ul style="list-style: disc; margin-left: 25px; margin-bottom: 15px;">
				<?php foreach ( array_slice( $missing_features, 0, 4 ) as $feature ) : ?>
					<li><strong><?php echo esc_html( $feature['description'] ); ?></strong></li>
				<?php endforeach; ?>
			</ul>

			<p>
				<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary">
					<span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
					<?php esc_html_e( 'Install Vault Module (Free)', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
				<a href="https://thisismyurl.com/vault-support" target="_blank" class="button button-secondary">
					<?php esc_html_e( 'Learn More', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
			</p>

			<p class="description">
				<?php esc_html_e( '✓ Free forever  •  ✓ No credit card needed  •  ✓ Install in one click', 'plugin-wp-support-thisismyurl' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render feature badge (Core, Vault, or Pro).
	 *
	 * @param string $level Feature level (core, vault, pro).
	 * @return void
	 */
	public static function render_feature_badge( string $level ): void {
		$badges = array(
			'core'    => array(
				'label' => __( 'Core', 'plugin-wp-support-thisismyurl' ),
				'color' => '#46b450',
				'icon'  => 'dashicons-yes-alt',
			),
			'vault'   => array(
				'label' => __( 'Vault', 'plugin-wp-support-thisismyurl' ),
				'color' => '#2271b1',
				'icon'  => 'dashicons-vault',
			),
			'upgrade' => array(
				'label' => __( 'Upgrade', 'plugin-wp-support-thisismyurl' ),
				'color' => '#dba617',
				'icon'  => 'dashicons-lock',
			),
			'pro'     => array(
				'label' => __( 'Pro', 'plugin-wp-support-thisismyurl' ),
				'color' => '#9b51e0',
				'icon'  => 'dashicons-star-filled',
			),
		);

		$badge = $badges[ $level ] ?? $badges['core'];
		?>
		<span class="wps-feature-badge" style="
			display: inline-block;
			padding: 2px 8px;
			background: <?php echo esc_attr( $badge['color'] ); ?>;
			color: white;
			border-radius: 3px;
			font-size: 11px;
			font-weight: 600;
			text-transform: uppercase;
			line-height: 1.4;
			vertical-align: middle;
		">
			<span class="dashicons <?php echo esc_attr( $badge['icon'] ); ?>" style="font-size: 12px; line-height: 1.4; vertical-align: middle;"></span>
			<?php echo esc_html( $badge['label'] ); ?>
		</span>
		<?php
	}

	/**
	 * Clear capabilities cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$capabilities_cache = null;
	}

	/**
	 * Get feature level for display.
	 *
	 * @param string $feature_key Feature key from capabilities array.
	 * @return string Level (core, vault, upgrade, pro).
	 */
	public static function get_feature_level( string $feature_key ): string {
		$capabilities = self::get_capabilities();
		return $capabilities[ $feature_key ]['level'] ?? 'core';
	}

	/**
	 * Check if a specific feature is available.
	 *
	 * @param string $feature_key Feature key from capabilities array.
	 * @return bool True if available.
	 */
	public static function has_feature( string $feature_key ): bool {
		$capabilities = self::get_capabilities();
		return $capabilities[ $feature_key ]['available'] ?? false;
	}
}
