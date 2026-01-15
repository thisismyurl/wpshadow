<?php
/**
 * Widget Registry - Auto-discovers and manages widgets from features
 *
 * Widgets are logical groupings of related features that appear as dashboard panels.
 * They are auto-discovered by scanning feature metadata.
 *
 * @package WP_Support
 * @subpackage Core
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Widget_Registry Class
 *
 * Manages widget discovery, registration, and rendering.
 */
class WPSHADOW_Widget_Registry {

	/**
	 * Widget cache.
	 *
	 * @var array|null
	 */
	private static ?array $widgets_cache = null;

	/**
	 * Cache option name.
	 */
	private const CACHE_KEY = 'wpshadow_widgets_cache';

	/**
	 * Cache version for invalidation.
	 */
	private const CACHE_VERSION = '1.0.0';

	/**
	 * Initialize widget registry.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'maybe_refresh_cache' ) );
		add_action( 'wpshadow_feature_state_changed', array( __CLASS__, 'clear_cache' ) );
	}

	/**
	 * Get all registered widgets.
	 *
	 * @param bool $force_refresh Force cache refresh.
	 * @return array Widget data.
	 */
	public static function get_widgets( bool $force_refresh = false ): array {
		if ( null !== self::$widgets_cache && ! $force_refresh ) {
			return self::$widgets_cache;
		}

		// Try to load from cache.
		$cached = get_option( self::CACHE_KEY );
		if ( ! $force_refresh && is_array( $cached ) && isset( $cached['version'] ) && $cached['version'] === self::CACHE_VERSION ) {
			self::$widgets_cache = $cached['data'];
			return self::$widgets_cache;
		}

		// Build widgets from features.
		self::$widgets_cache = self::discover_widgets();

		// Save to cache.
		update_option(
			self::CACHE_KEY,
			array(
				'version' => self::CACHE_VERSION,
				'data'    => self::$widgets_cache,
			),
			false
		);

		return self::$widgets_cache;
	}

	/**
	 * Discover widgets from features.
	 *
	 * @return array Widget data.
	 */
	private static function discover_widgets(): array {
		$widgets  = array();
		$features = WPSHADOW_Feature_Registry::get_features();

		foreach ( $features as $feature_id => $feature ) {
			// Skip features without widget assignment.
			if ( empty( $feature['widget_group'] ) ) {
				continue;
			}

			$widget_id = $feature['widget_group'];

			// Initialize widget if not exists.
			if ( ! isset( $widgets[ $widget_id ] ) ) {
				$widgets[ $widget_id ] = array(
					'id'          => $widget_id,
					'name'        => $feature['widget_name'] ?? self::format_widget_name( $widget_id ),
					'description' => $feature['widget_description'] ?? '',
					'dashboard'   => $feature['dashboard'] ?? 'overview',
					'column'      => $feature['widget_column'] ?? 'left',
					'priority'    => $feature['widget_priority'] ?? 50,
					'icon'        => $feature['widget_icon'] ?? 'dashicons-admin-generic',
					'capability'  => $feature['minimum_capability'] ?? 'manage_options',
					'context'     => $feature['context'] ?? 'core',
					'features'    => array(),
				);
			}

			// Add feature to widget.
			$widgets[ $widget_id ]['features'][] = $feature_id;

			// Use most restrictive capability.
			$current_cap = $widgets[ $widget_id ]['capability'];
			$feature_cap = $feature['minimum_capability'] ?? 'manage_options';
			if ( self::is_capability_more_restrictive( $feature_cap, $current_cap ) ) {
				$widgets[ $widget_id ]['capability'] = $feature_cap;
			}
		}

		// Sort widgets by priority.
		uasort(
			$widgets,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);

		return $widgets;
	}

	/**
	 * Format widget name from ID.
	 *
	 * @param string $widget_id Widget ID.
	 * @return string Formatted widget name.
	 */
	private static function format_widget_name( string $widget_id ): string {
		return ucwords( str_replace( array( '-', '_' ), ' ', $widget_id ) );
	}

	/**
	 * Check if one capability is more restrictive than another.
	 *
	 * @param string $cap1 First capability.
	 * @param string $cap2 Second capability.
	 * @return bool True if cap1 is more restrictive.
	 */
	private static function is_capability_more_restrictive( string $cap1, string $cap2 ): bool {
		$hierarchy = array(
			'read'               => 1,
			'edit_posts'         => 2,
			'publish_posts'      => 3,
			'edit_others_posts'  => 4,
			'manage_categories'  => 5,
			'manage_options'     => 6,
			'activate_plugins'   => 7,
			'install_plugins'    => 8,
			'update_core'        => 9,
			'manage_network'     => 10,
		);

		$level1 = $hierarchy[ $cap1 ] ?? 5;
		$level2 = $hierarchy[ $cap2 ] ?? 5;

		return $level1 > $level2;
	}

	/**
	 * Get widgets for a specific dashboard.
	 *
	 * @param string $dashboard_id Dashboard ID.
	 * @return array Widgets for dashboard.
	 */
	public static function get_widgets_for_dashboard( string $dashboard_id ): array {
		$all_widgets = self::get_widgets();
		$widgets     = array();

		foreach ( $all_widgets as $widget_id => $widget ) {
			if ( ( $widget['dashboard'] ?? 'overview' ) === $dashboard_id ) {
				$widgets[ $widget_id ] = $widget;
			}
		}

		return $widgets;
	}

	/**
	 * Get a specific widget.
	 *
	 * @param string $widget_id Widget ID.
	 * @return array|null Widget data or null if not found.
	 */
	public static function get_widget( string $widget_id ): ?array {
		$widgets = self::get_widgets();
		return $widgets[ $widget_id ] ?? null;
	}

	/**
	 * Check if user can access widget.
	 *
	 * @param string $widget_id Widget ID.
	 * @return bool True if user has access.
	 */
	public static function can_access_widget( string $widget_id ): bool {
		$widget = self::get_widget( $widget_id );
		if ( ! $widget ) {
			return false;
		}

		// Check capability.
		if ( ! current_user_can( $widget['capability'] ) ) {
			return false;
		}

		// Check license level (get minimum from features).
		$min_license = 1;
		foreach ( $widget['features'] as $feature_id ) {
			$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
			if ( $feature && isset( $feature['license_level'] ) ) {
				$min_license = max( $min_license, $feature['license_level'] );
			}
		}

		if ( class_exists( 'wpshadow_License' ) ) {
			$user_license = WPSHADOW_License::get_user_level();
			if ( $user_license < $min_license ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Render widgets for a dashboard.
	 *
	 * @param string $dashboard_id Dashboard ID.
	 * @return void
	 */
	public static function render_widgets_for_dashboard( string $dashboard_id ): void {
		$widgets = self::get_widgets_for_dashboard( $dashboard_id );

		if ( empty( $widgets ) ) {
			echo '<p>' . esc_html__( 'No widgets available for this dashboard.', 'plugin-wpshadow' ) . '</p>';
			return;
		}

		// Group widgets by column.
		$left_widgets  = array();
		$right_widgets = array();

		foreach ( $widgets as $widget_id => $widget ) {
			if ( ! self::can_access_widget( $widget_id ) ) {
				continue;
			}

			$column = $widget['column'] ?? 'left';
			if ( 'right' === $column ) {
				$right_widgets[] = $widget;
			} else {
				$left_widgets[] = $widget;
			}
		}

		// Render two-column layout.
		?>
		<div class="wps-dashboard-columns">
			<div class="wps-dashboard-column wps-dashboard-column-left">
				<?php
				foreach ( $left_widgets as $widget ) {
					self::render_widget( $widget );
				}
				?>
			</div>
			<div class="wps-dashboard-column wps-dashboard-column-right">
				<?php
				foreach ( $right_widgets as $widget ) {
					self::render_widget( $widget );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single widget.
	 *
	 * @param array $widget Widget data.
	 * @return void
	 */
	private static function render_widget( array $widget ): void {
		$widget_id = $widget['id'];

		?>
		<div class="wps-widget postbox" id="wps-widget-<?php echo esc_attr( $widget_id ); ?>">
			<div class="postbox-header">
				<h2 class="hndle">
					<span class="dashicons <?php echo esc_attr( $widget['icon'] ); ?>"></span>
					<?php echo esc_html( $widget['name'] ); ?>
				</h2>
				<div class="handle-actions">
					<button type="button" class="handlediv" aria-expanded="true">
						<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel', 'plugin-wpshadow' ); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
				</div>
			</div>
			<div class="inside">
				<?php if ( ! empty( $widget['description'] ) ) : ?>
					<p class="description"><?php echo esc_html( $widget['description'] ); ?></p>
				<?php endif; ?>

				<div class="wps-widget-features">
					<?php self::render_widget_features( $widget ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render features within a widget.
	 *
	 * @param array $widget Widget data.
	 * @return void
	 */
	private static function render_widget_features( array $widget ): void {
		if ( empty( $widget['features'] ) ) {
			echo '<p>' . esc_html__( 'No features available.', 'plugin-wpshadow' ) . '</p>';
			return;
		}

		$features = WPSHADOW_Feature_Registry::get_features();

		foreach ( $widget['features'] as $feature_id ) {
			if ( ! isset( $features[ $feature_id ] ) ) {
				continue;
			}

			$feature = $features[ $feature_id ];

			// Check license level.
			if ( isset( $feature['license_level'] ) && class_exists( 'wpshadow_License' ) ) {
				$user_license = WPSHADOW_License::get_user_level();
				if ( $user_license < $feature['license_level'] ) {
					self::render_locked_feature( $feature );
					continue;
				}
			}

			// Check capability.
			if ( isset( $feature['minimum_capability'] ) && ! current_user_can( $feature['minimum_capability'] ) ) {
				continue;
			}

			// Render feature.
			self::render_feature( $feature );
		}
	}

	/**
	 * Render a feature within a widget.
	 *
	 * @param array $feature Feature data.
	 * @return void
	 */
	private static function render_feature( array $feature ): void {
		$feature_id = $feature['id'];
		$enabled    = $feature['enabled'] ?? false;

		?>
		<div class="wps-feature" data-feature-id="<?php echo esc_attr( $feature_id ); ?>">
			<div class="wps-feature-header">
				<span class="dashicons <?php echo esc_attr( $feature['icon'] ?? 'dashicons-admin-generic' ); ?>"></span>
				<h4><?php echo esc_html( $feature['name'] ); ?></h4>
				<label class="wps-toggle">
					<input
						type="checkbox"
						class="wps-feature-toggle"
						data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
						<?php checked( $enabled ); ?>
					/>
					<span class="wps-toggle-slider"></span>
				</label>
			</div>
			<?php if ( ! empty( $feature['description'] ) ) : ?>
				<p class="description"><?php echo esc_html( $feature['description'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $feature['sub_features'] ) ) : ?>
				<div class="wps-sub-features">
					<?php foreach ( $feature['sub_features'] as $sub_feature ) : ?>
						<div class="wps-sub-feature">
							<label>
								<input type="checkbox" <?php checked( $sub_feature['enabled'] ?? false ); ?> />
								<?php echo esc_html( $sub_feature['name'] ); ?>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a locked feature (license upgrade required).
	 *
	 * @param array $feature Feature data.
	 * @return void
	 */
	private static function render_locked_feature( array $feature ): void {
		$license_names = array(
			1 => __( 'Free', 'plugin-wpshadow' ),
			2 => __( 'Free (Registered)', 'plugin-wpshadow' ),
			3 => __( 'Good', 'plugin-wpshadow' ),
			4 => __( 'Better', 'plugin-wpshadow' ),
			5 => __( 'Best', 'plugin-wpshadow' ),
		);

		$required_license = $license_names[ $feature['license_level'] ] ?? __( 'Premium', 'plugin-wpshadow' );

		?>
		<div class="wps-feature wps-feature-locked" data-feature-id="<?php echo esc_attr( $feature['id'] ); ?>">
			<div class="wps-feature-header">
				<span class="dashicons dashicons-lock"></span>
				<h4><?php echo esc_html( $feature['name'] ); ?></h4>
				<span class="wps-license-badge"><?php echo esc_html( $required_license ); ?></span>
			</div>
			<?php if ( ! empty( $feature['description'] ) ) : ?>
				<p class="description"><?php echo esc_html( $feature['description'] ); ?></p>
			<?php endif; ?>
			<p class="wps-upgrade-prompt">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support-license' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Upgrade License', 'plugin-wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Clear widget cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$widgets_cache = null;
		delete_option( self::CACHE_KEY );
	}

	/**
	 * Maybe refresh cache if version changed.
	 *
	 * @return void
	 */
	public static function maybe_refresh_cache(): void {
		$cached = get_option( self::CACHE_KEY );
		if ( ! is_array( $cached ) || ! isset( $cached['version'] ) || $cached['version'] !== self::CACHE_VERSION ) {
			self::clear_cache();
		}
	}
}
