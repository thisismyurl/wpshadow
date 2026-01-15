<?php
/**
 * Ghost Features System - Module Feature Discovery & Display
 *
 * Allows modules to declare their features in the catalog even when not installed.
 * Creates discoverability by showing "phantom" features that explain module benefits.
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
 * WPSHADOW_Ghost_Features Class
 *
 * Manages ghost feature declarations from uninstalled modules.
 */
class WPSHADOW_Ghost_Features {

	/**
	 * Feature cache.
	 *
	 * @var array|null
	 */
	private static ?array $features_cache = null;

	/**
	 * Initialize ghost features system.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Allow modules to register their ghost features.
		add_action( 'wpshadow_register_ghost_features', array( __CLASS__, 'register_catalog_features' ), 10 );
	}

	/**
	 * Register ghost features from module catalog.
	 *
	 * Automatically extracts feature declarations from catalog metadata.
	 *
	 * @return void
	 */
	public static function register_catalog_features(): void {
		$catalog = WPSHADOW_Module_Registry::get_catalog_modules();

		foreach ( $catalog as $module ) {
			if ( ! isset( $module['ghost_features'] ) || empty( $module['ghost_features'] ) ) {
				continue;
			}

			$module_slug = $module['slug'] ?? '';
			$is_installed = WPSHADOW_Module_Registry::is_installed( $module_slug );

			foreach ( $module['ghost_features'] as $feature ) {
				self::register_feature(
					array_merge(
						$feature,
						array(
							'module_slug'     => $module_slug,
							'module_name'     => $module['name'] ?? '',
							'module_type'     => $module['type'] ?? 'spoke',
							'is_available'    => $is_installed,
							'download_url'    => $module['download_url'] ?? '',
							'requires_core'   => $module['requires_core'] ?? '',
							'requires_php'    => $module['requires_php'] ?? '',
							'requires_wp'     => $module['requires_wp'] ?? '',
							'requires_hub'    => $module['requires_hub'] ?? '',
						)
					)
				);
			}
		}
	}

	/**
	 * Register a ghost feature.
	 *
	 * @param array $feature_data Feature metadata.
	 * @return bool True if registered successfully.
	 */
	public static function register_feature( array $feature_data ): bool {
		$required_keys = array( 'key', 'title', 'description', 'module_slug' );
		foreach ( $required_keys as $key ) {
			if ( ! isset( $feature_data[ $key ] ) ) {
				return false;
			}
		}

		if ( null === self::$features_cache ) {
			self::$features_cache = array();
		}

		$feature_key = $feature_data['key'];
		$module_slug = $feature_data['module_slug'];

		// Create module group if it doesn't exist.
		if ( ! isset( self::$features_cache[ $module_slug ] ) ) {
			self::$features_cache[ $module_slug ] = array();
		}

		// Store feature.
		self::$features_cache[ $module_slug ][ $feature_key ] = array(
			'key'             => $feature_key,
			'title'           => $feature_data['title'],
			'description'     => $feature_data['description'],
			'icon'            => $feature_data['icon'] ?? 'dashicons-star-filled',
			'category'        => $feature_data['category'] ?? 'general',
			'priority'        => $feature_data['priority'] ?? 10,
			'module_slug'     => $module_slug,
			'module_name'     => $feature_data['module_name'] ?? '',
			'module_type'     => $feature_data['module_type'] ?? 'spoke',
			'is_available'    => $feature_data['is_available'] ?? false,
			'benefits'        => $feature_data['benefits'] ?? array(),
			'use_cases'       => $feature_data['use_cases'] ?? array(),
			'requirements'    => array(
				'core' => $feature_data['requires_core'] ?? '',
				'php'  => $feature_data['requires_php'] ?? '',
				'wp'   => $feature_data['requires_wp'] ?? '',
				'hub'  => $feature_data['requires_hub'] ?? '',
			),
			'install_url'     => admin_url( 'admin.php?page=wp-support&tab=modules&install=' . $module_slug ),
			'download_url'    => $feature_data['download_url'] ?? '',
		);

		return true;
	}

	/**
	 * Get all features, grouped by module.
	 *
	 * @param bool $include_installed Include features from installed modules.
	 * @return array Features grouped by module slug.
	 */
	public static function get_all_features( bool $include_installed = true ): array {
		if ( null === self::$features_cache ) {
			do_action( 'wpshadow_register_ghost_features' );
		}

		if ( ! $include_installed ) {
			return array_filter(
				self::$features_cache ?? array(),
				function ( $module_features ) {
					return ! empty( array_filter(
						$module_features,
						function ( $feature ) {
							return ! $feature['is_available'];
						}
					) );
				}
			);
		}

		return self::$features_cache ?? array();
	}

	/**
	 * Get features for a specific module.
	 *
	 * @param string $module_slug Module slug.
	 * @return array Features for the module.
	 */
	public static function get_module_features( string $module_slug ): array {
		$all_features = self::get_all_features();
		return $all_features[ $module_slug ] ?? array();
	}

	/**
	 * Get all ghost features (unavailable features only).
	 *
	 * @return array Unavailable features grouped by module.
	 */
	public static function get_ghost_features(): array {
		return self::get_all_features( false );
	}

	/**
	 * Get features by category.
	 *
	 * @param string $category Feature category (backup, media, security, etc).
	 * @param bool   $include_installed Include installed features.
	 * @return array Features in the category.
	 */
	public static function get_features_by_category( string $category, bool $include_installed = true ): array {
		$all_features = self::get_all_features( $include_installed );
		$categorized = array();

		foreach ( $all_features as $module_slug => $module_features ) {
			foreach ( $module_features as $feature ) {
				if ( $feature['category'] === $category ) {
					$categorized[ $module_slug ][] = $feature;
				}
			}
		}

		return $categorized;
	}

	/**
	 * Render ghost features for a category.
	 *
	 * @param string $category Feature category.
	 * @param array  $args Display arguments.
	 * @return void
	 */
	public static function render_category_features( string $category, array $args = array() ): void {
		$defaults = array(
			'include_installed' => true,
			'show_install_button' => true,
			'show_benefits' => true,
			'columns' => 2,
			'context' => 'dashboard',
		);

		$args = wp_parse_args( $args, $defaults );
		$features = self::get_features_by_category( $category, $args['include_installed'] );

		if ( empty( $features ) ) {
			return;
		}

		echo '<div class="wps-ghost-features-grid" style="display: grid; grid-template-columns: repeat(' . esc_attr( $args['columns'] ) . ', 1fr); gap: 20px; margin: 20px 0;">';

		foreach ( $features as $module_slug => $module_features ) {
			foreach ( $module_features as $feature ) {
				self::render_feature_card( $feature, $args );
			}
		}

		echo '</div>';
	}

	/**
	 * Render a single feature card.
	 *
	 * @param array $feature Feature data.
	 * @param array $args Display arguments.
	 * @return void
	 */
	public static function render_feature_card( array $feature, array $args = array() ): void {
		$is_available = $feature['is_available'];
		$card_class = $is_available ? 'wps-feature-card-available' : 'wps-feature-card-ghost';
		$opacity = $is_available ? '1' : '0.7';

		?>
		<div class="wps-feature-card <?php echo esc_attr( $card_class ); ?>" style="
			border: 1px solid <?php echo $is_available ? '#46b450' : '#dba617'; ?>;
			border-radius: 8px;
			padding: 20px;
			background: <?php echo $is_available ? '#f0f9f1' : '#fef8e7'; ?>;
			opacity: <?php echo esc_attr( $opacity ); ?>;
			transition: all 0.3s ease;
		">
			<div class="wps-feature-header" style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
				<span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>" style="font-size: 32px; color: <?php echo $is_available ? '#46b450' : '#dba617'; ?>;"></span>
				<div style="flex: 1;">
					<h3 style="margin: 0; font-size: 18px; color: #1d2327;">
						<?php echo esc_html( $feature['title'] ); ?>
						<?php if ( ! $is_available ) : ?>
							<span class="wps-ghost-badge" style="
								display: inline-block;
								padding: 2px 8px;
								background: #dba617;
								color: white;
								border-radius: 3px;
								font-size: 10px;
								font-weight: 600;
								text-transform: uppercase;
								margin-left: 8px;
								vertical-align: middle;
							">
								<?php esc_html_e( 'Install to Unlock', 'plugin-wpshadow' ); ?>
							</span>
						<?php else : ?>
							<span class="wps-active-badge" style="
								display: inline-block;
								padding: 2px 8px;
								background: #46b450;
								color: white;
								border-radius: 3px;
								font-size: 10px;
								font-weight: 600;
								text-transform: uppercase;
								margin-left: 8px;
								vertical-align: middle;
							">
								<?php esc_html_e( 'Active', 'plugin-wpshadow' ); ?>
							</span>
						<?php endif; ?>
					</h3>
					<p style="margin: 5px 0 0; font-size: 12px; color: #646970;">
						<?php
						/* translators: %s: Module name */
						printf( esc_html__( 'from %s module', 'plugin-wpshadow' ), '<strong>' . esc_html( $feature['module_name'] ) . '</strong>' );
						?>
					</p>
				</div>
			</div>

			<p style="margin: 0 0 15px; color: #3c434a; line-height: 1.6;">
				<?php echo esc_html( $feature['description'] ); ?>
			</p>

			<?php if ( ! empty( $feature['benefits'] ) && ! empty( $args['show_benefits'] ) ) : ?>
				<ul style="margin: 15px 0; padding-left: 20px; color: #3c434a;">
					<?php foreach ( array_slice( $feature['benefits'], 0, 3 ) as $benefit ) : ?>
						<li style="margin-bottom: 5px;"><?php echo esc_html( $benefit ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( ! $is_available && ! empty( $args['show_install_button'] ) ) : ?>
				<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(0,0,0,0.1);">
					<a href="<?php echo esc_url( $feature['install_url'] ); ?>" class="button button-primary" style="margin-right: 10px;">
						<span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
						<?php
						/* translators: %s: Module name */
						printf( esc_html__( 'Install %s', 'plugin-wpshadow' ), esc_html( $feature['module_name'] ) );
						?>
					</a>
					<?php if ( ! empty( $feature['download_url'] ) ) : ?>
						<a href="<?php echo esc_url( $feature['download_url'] ); ?>" class="button button-secondary" target="_blank">
							<?php esc_html_e( 'Learn More', 'plugin-wpshadow' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render dashboard summary showing all modules and their features.
	 *
	 * @return void
	 */
	public static function render_dashboard_summary(): void {
		$all_features = self::get_all_features( true );
		$ghost_count = 0;
		$active_count = 0;

		// Count ghost vs active features.
		foreach ( $all_features as $module_features ) {
			foreach ( $module_features as $feature ) {
				if ( $feature['is_available'] ) {
					$active_count++;
				} else {
					$ghost_count++;
				}
			}
		}

		if ( empty( $all_features ) ) {
			return;
		}

		?>
		<div class="wps-features-dashboard" style="margin: 20px 0;">
			<div class="wps-features-stats" style="display: flex; gap: 20px; margin-bottom: 30px;">
				<div style="flex: 1; padding: 20px; background: #f0f9f1; border-left: 4px solid #46b450; border-radius: 4px;">
					<div style="font-size: 32px; font-weight: bold; color: #46b450; margin-bottom: 5px;">
						<?php echo absint( $active_count ); ?>
					</div>
					<div style="color: #3c434a; font-size: 14px;">
						<?php esc_html_e( 'Active Features', 'plugin-wpshadow' ); ?>
					</div>
				</div>
				<div style="flex: 1; padding: 20px; background: #fef8e7; border-left: 4px solid #dba617; border-radius: 4px;">
					<div style="font-size: 32px; font-weight: bold; color: #dba617; margin-bottom: 5px;">
						<?php echo absint( $ghost_count ); ?>
					</div>
					<div style="color: #3c434a; font-size: 14px;">
						<?php esc_html_e( 'Available to Install', 'plugin-wpshadow' ); ?>
					</div>
				</div>
			</div>

			<?php if ( $ghost_count > 0 ) : ?>
				<div class="wps-ghost-prompt" style="padding: 20px; background: white; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px;">
					<h3 style="margin-top: 0;">
						<span class="dashicons dashicons-info" style="color: #2271b1; font-size: 24px; vertical-align: middle;"></span>
						<?php esc_html_e( 'Unlock More Features', 'plugin-wpshadow' ); ?>
					</h3>
					<p style="color: #646970; line-height: 1.6;">
						<?php
						/* translators: %d: Number of available features */
						printf( esc_html__( 'You have %d additional features available. Install free modules to enhance your site with backup encryption, media optimization, and more.', 'plugin-wpshadow' ), absint( $ghost_count ) );
						?>
					</p>
				</div>
			<?php endif; ?>

			<div class="wps-features-by-module">
				<?php foreach ( $all_features as $module_slug => $module_features ) : ?>
					<?php
					$first_feature = reset( $module_features );
					$module_name = $first_feature['module_name'] ?? $module_slug;
					$is_installed = $first_feature['is_available'] ?? false;
					?>
					<div class="wps-module-features-section" style="margin-bottom: 30px;">
						<h2 style="display: flex; align-items: center; gap: 10px; padding-bottom: 10px; border-bottom: 2px solid #ddd;">
							<span><?php echo esc_html( $module_name ); ?></span>
							<?php if ( $is_installed ) : ?>
								<span class="wps-module-badge-active" style="
									padding: 3px 10px;
									background: #46b450;
									color: white;
									border-radius: 3px;
									font-size: 12px;
									font-weight: 600;
									text-transform: uppercase;
								">
									<?php esc_html_e( 'Installed', 'plugin-wpshadow' ); ?>
								</span>
							<?php else : ?>
								<span class="wps-module-badge-ghost" style="
									padding: 3px 10px;
									background: #dba617;
									color: white;
									border-radius: 3px;
									font-size: 12px;
									font-weight: 600;
									text-transform: uppercase;
								">
									<?php esc_html_e( 'Not Installed', 'plugin-wpshadow' ); ?>
								</span>
							<?php endif; ?>
						</h2>

						<div class="wps-ghost-features-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 20px;">
							<?php foreach ( $module_features as $feature ) : ?>
								<?php self::render_feature_card( $feature, array( 'show_benefits' => true, 'show_install_button' => true ) ); ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Clear features cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$features_cache = null;
	}
}
