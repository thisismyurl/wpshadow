<?php
/**
 * Modules Dashboard View
 *
 * @package TIMU_Core_Support
 */

namespace TIMU\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap timu-dashboard-wrap">
	<div class="timu-dashboard-header">
		<h1><?php esc_html_e( 'Support Dashboard', 'core-support-thisismyurl' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Manage your thisismyurl Media Suite modules and settings.', 'core-support-thisismyurl' ); ?>
		</p>
	</div>

	<div class="timu-dashboard-stats">
		<div class="timu-stat-card">
			<div class="timu-stat-icon">
				<span class="dashicons dashicons-admin-plugins"></span>
			</div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( $total ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Total Modules', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>

		<div class="timu-stat-card">
			<div class="timu-stat-icon timu-stat-enabled">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( $enabled ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Enabled', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>

		<div class="timu-stat-card">
			<div class="timu-stat-icon timu-stat-available">
				<span class="dashicons dashicons-plus-alt"></span>
			</div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( $available ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Available', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>

		<div class="timu-stat-card">
			<div class="timu-stat-icon timu-stat-update">
				<span class="dashicons dashicons-update"></span>
			</div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( $updates ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Updates', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>

		<div class="timu-stat-card">
			<div class="timu-stat-icon timu-stat-hub">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( $hubs ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Hubs', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>

		<div class="timu-stat-card">
			<div class="timu-stat-icon timu-stat-spoke">
				<span class="dashicons dashicons-admin-tools"></span>
			</div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( $spokes ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Spokes', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>
	</div>

	<div class="timu-modules-header">
		<h2><?php esc_html_e( 'Installed Modules', 'core-support-thisismyurl' ); ?></h2>
		<div class="timu-modules-filters">
			<label for="timu-filter-type"><?php esc_html_e( 'Filter:', 'core-support-thisismyurl' ); ?></label>
			<select id="timu-filter-type">
				<option value="all"><?php esc_html_e( 'All Types', 'core-support-thisismyurl' ); ?></option>
				<option value="hub"><?php esc_html_e( 'Hubs Only', 'core-support-thisismyurl' ); ?></option>
				<option value="spoke"><?php esc_html_e( 'Spokes Only', 'core-support-thisismyurl' ); ?></option>
			</select>
		</div>
	</div>

	<div class="timu-modules-grid">
		<?php if ( empty( $modules ) ) : ?>
			<div class="timu-no-modules">
				<span class="dashicons dashicons-info"></span>
				<p><?php esc_html_e( 'No modules found. Activate additional thisismyurl plugins to see them here.', 'core-support-thisismyurl' ); ?></p>
			</div>
		<?php else : ?>
			<?php foreach ( $modules as $module ) : ?>
				<?php
				$slug             = $module['slug'];
				$installed        = ! empty( $module['installed'] );
				$is_enabled       = $installed ? \TIMU\CoreSupport\TIMU_Module_Registry::is_enabled( $slug ) : false;
				$update_available = ! empty( $module['update_available'] );
				$type_class       = 'hub' === $module['type'] ? 'timu-type-hub' : 'timu-type-spoke';
				$status_class     = $installed ? ( $is_enabled ? 'timu-module-enabled' : 'timu-module-disabled' ) : 'timu-module-available';
				$status_label     = $installed ? ( $update_available ? __( 'Update available', 'core-support-thisismyurl' ) : __( 'Installed', 'core-support-thisismyurl' ) ) : __( 'Available', 'core-support-thisismyurl' );
				?>
				<div class="timu-module-card <?php echo esc_attr( $type_class . ' ' . $status_class ); ?>" data-type="<?php echo esc_attr( $module['type'] ); ?>" data-status="<?php echo esc_attr( $installed ? ( $update_available ? 'update' : 'installed' ) : 'available' ); ?>">
					<div class="timu-module-header">
						<h3 class="timu-module-name"><?php echo esc_html( $module['name'] ); ?></h3>
						<div class="timu-module-badges">
							<span class="timu-badge timu-badge-type">
								<?php echo esc_html( ucfirst( $module['type'] ) ); ?>
							</span>
							<span class="timu-badge timu-badge-status">
								<?php echo esc_html( $status_label ); ?>
							</span>
							<?php if ( $update_available ) : ?>
								<span class="timu-badge timu-badge-update">
									<?php esc_html_e( 'Update', 'core-support-thisismyurl' ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>

					<div class="timu-module-meta">
						<div class="timu-module-version">
							<span class="dashicons dashicons-tag"></span>
							<?php
							/* translators: %s: Available version number */
							printf( esc_html__( 'Catalog %s', 'core-support-thisismyurl' ), esc_html( $module['version'] ) );
							?>
						</div>
						<div class="timu-module-author">
							<span class="dashicons dashicons-admin-users"></span>
							<?php
							/* translators: %s: Author name */
							printf( esc_html__( 'By %s', 'core-support-thisismyurl' ), esc_html( $module['author'] ) );
							?>
						</div>
						<?php if ( $installed && ! empty( $module['installed_version'] ) ) : ?>
							<div class="timu-module-installed-version">
								<span class="dashicons dashicons-yes"></span>
								<?php
								/* translators: %s: Installed version number */
								printf( esc_html__( 'Installed %s', 'core-support-thisismyurl' ), esc_html( $module['installed_version'] ) );
								?>
							</div>
						<?php endif; ?>
					</div>

					<p class="timu-module-description"><?php echo esc_html( $module['description'] ); ?></p>

					<?php if ( ! empty( $module['suite_id'] ) ) : ?>
						<div class="timu-module-suite">
							<span class="dashicons dashicons-admin-network"></span>
							<code><?php echo esc_html( $module['suite_id'] ); ?></code>
						</div>
					<?php endif; ?>

					<div class="timu-module-actions">
						<label class="timu-toggle">
							<input
								type="checkbox"
								class="timu-module-toggle"
								data-slug="<?php echo esc_attr( $module['slug'] ); ?>"
								data-installed="<?php echo esc_attr( $installed ? 'true' : 'false' ); ?>"
								<?php checked( $is_enabled ); ?>
								<?php disabled( ! $installed ); ?>
							>
							<span class="timu-toggle-slider"></span>
							<span class="timu-toggle-label">
								<?php esc_html_e( 'Enable Module', 'core-support-thisismyurl' ); ?>
							</span>
						</label>

						<?php if ( ! empty( $module['download_url'] ) ) : ?>
							<a href="<?php echo esc_url( $module['download_url'] ); ?>" class="button button-primary" target="_blank">
								<?php echo $installed ? esc_html__( 'Open Release', 'core-support-thisismyurl' ) : esc_html__( 'Install from GitHub', 'core-support-thisismyurl' ); ?>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $module['uri'] ) ) : ?>
							<a href="<?php echo esc_url( $module['uri'] ); ?>" class="button button-secondary" target="_blank">
								<?php esc_html_e( 'Learn More', 'core-support-thisismyurl' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
