<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Widget_Registry {

	private static ?array $widgets_cache = null;

	private const CACHE_KEY = 'wpshadow_widgets_cache';

	private const CACHE_VERSION = '1.0.0';

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'maybe_refresh_cache' ) );
		add_action( 'wpshadow_feature_state_changed', array( __CLASS__, 'clear_cache' ) );
	}

	public static function get_widgets( bool $force_refresh = false ): array {
		if ( null !== self::$widgets_cache && ! $force_refresh ) {
			return self::$widgets_cache;
		}

		$cached = get_option( self::CACHE_KEY );
		if ( ! $force_refresh && is_array( $cached ) && isset( $cached['version'] ) && $cached['version'] === self::CACHE_VERSION ) {
			self::$widgets_cache = $cached['data'];
			return self::$widgets_cache;
		}

		self::$widgets_cache = self::discover_widgets();

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

	private static function discover_widgets(): array {
		$widgets  = array();
		$features = WPSHADOW_Feature_Registry::get_features();

		foreach ( $features as $feature_id => $feature ) {

			if ( empty( $feature['widget_group'] ) ) {
				continue;
			}

			$widget_id = $feature['widget_group'];

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

			$widgets[ $widget_id ]['features'][] = $feature_id;

			$current_cap = $widgets[ $widget_id ]['capability'];
			$feature_cap = $feature['minimum_capability'] ?? 'manage_options';
			if ( self::is_capability_more_restrictive( $feature_cap, $current_cap ) ) {
				$widgets[ $widget_id ]['capability'] = $feature_cap;
			}
		}

		uasort(
			$widgets,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);

		return $widgets;
	}

	private static function format_widget_name( string $widget_id ): string {
		return ucwords( str_replace( array( '-', '_' ), ' ', $widget_id ) );
	}

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

	public static function get_widget( string $widget_id ): ?array {
		$widgets = self::get_widgets();
		return $widgets[ $widget_id ] ?? null;
	}

	public static function can_access_widget( string $widget_id ): bool {
		$widget = self::get_widget( $widget_id );
		if ( ! $widget ) {
			return false;
		}

		if ( ! current_user_can( $widget['capability'] ) ) {
			return false;
		}

		$min_license = 1;

		return true;
	}

	public static function render_widgets_for_dashboard( string $dashboard_id ): void {
		$widgets = self::get_widgets_for_dashboard( $dashboard_id );

		if ( empty( $widgets ) ) {
			echo '<p>' . esc_html__( 'No widgets available for this dashboard.', 'wpshadow' ) . '</p>';
			return;
		}

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
						<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel', 'wpshadow' ); ?></span>
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

	private static function render_widget_features( array $widget ): void {
		if ( empty( $widget['features'] ) ) {
			echo '<p>' . esc_html__( 'No features available.', 'wpshadow' ) . '</p>';
			return;
		}

		$features = WPSHADOW_Feature_Registry::get_features();

		foreach ( $widget['features'] as $feature_id ) {
			if ( ! isset( $features[ $feature_id ] ) ) {
				continue;
			}

			$feature = $features[ $feature_id ];

			if ( isset( $feature['minimum_capability'] ) && ! current_user_can( $feature['minimum_capability'] ) ) {
				continue;
			}

			self::render_feature( $feature );
		}
	}

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

	private static function render_locked_feature( array $feature ): void {
		$license_names = array(
			1 => __( 'Free', 'wpshadow' ),
			2 => __( 'Free (Registered)', 'wpshadow' ),
			3 => __( 'Good', 'wpshadow' ),
			4 => __( 'Better', 'wpshadow' ),
			5 => __( 'Best', 'wpshadow' ),
		);

		$required_license = $license_names[ $feature['license_level'] ] ?? __( 'Premium', 'wpshadow' );

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
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-license' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Upgrade License', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	public static function clear_cache(): void {
		self::$widgets_cache = null;
		delete_option( self::CACHE_KEY );
	}

	public static function maybe_refresh_cache(): void {
		$cached = get_option( self::CACHE_KEY );
		if ( ! is_array( $cached ) || ! isset( $cached['version'] ) || $cached['version'] !== self::CACHE_VERSION ) {
			self::clear_cache();
		}
	}
}
