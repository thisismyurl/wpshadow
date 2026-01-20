<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Dashboard_Registry {

	private static ?array $dashboards_cache = null;

	private const CACHE_KEY = 'wpshadow_dashboards_cache';

	private const CACHE_VERSION = '1.0.0';

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'maybe_refresh_cache' ) );
		add_action( 'wpshadow_feature_state_changed', array( __CLASS__, 'clear_cache' ) );
		add_action( 'wpshadow_widget_registered', array( __CLASS__, 'clear_cache' ) );
	}

	public static function get_dashboards( bool $force_refresh = false ): array {
		if ( null !== self::$dashboards_cache && ! $force_refresh ) {
			return self::$dashboards_cache;
		}

		$cached = get_option( self::CACHE_KEY );
		if ( ! $force_refresh && is_array( $cached ) && isset( $cached['version'] ) && $cached['version'] === self::CACHE_VERSION ) {
			self::$dashboards_cache = $cached['data'];
			return self::$dashboards_cache;
		}

		self::$dashboards_cache = self::discover_dashboards();

		update_option(
			self::CACHE_KEY,
			array(
				'version' => self::CACHE_VERSION,
				'data'    => self::$dashboards_cache,
			),
			false
		);

		return self::$dashboards_cache;
	}

	private static function discover_dashboards(): array {
		$dashboards = array(

			'overview'    => array(
				'id'          => 'overview',
				'name'        => __( 'Overview', 'wpshadow' ),
				'description' => __( 'Main dashboard with key metrics and quick actions', 'wpshadow' ),
				'icon'        => 'dashicons-dashboard',
				'context'     => 'core',
				'visible'     => true,
				'priority'    => 10,
				'widgets'     => array(),
			),
			'performance' => array(
				'id'          => 'performance',
				'name'        => __( 'Performance', 'wpshadow' ),
				'description' => __( 'Performance monitoring and optimization', 'wpshadow' ),
				'icon'        => 'dashicons-performance',
				'context'     => 'core',
				'visible'     => true,
				'priority'    => 20,
				'widgets'     => array(),
			),
			'security'    => array(
				'id'          => 'security',
				'name'        => __( 'Security', 'wpshadow' ),
				'description' => __( 'Security hardening and vulnerability monitoring', 'wpshadow' ),
				'icon'        => 'dashicons-shield',
				'context'     => 'core',
				'visible'     => true,
				'priority'    => 30,
				'widgets'     => array(),
			),
		);

		$widgets = WPSHADOW_Widget_Registry::get_widgets();
		foreach ( $widgets as $widget ) {
			$dashboard_id = $widget['dashboard'] ?? 'overview';

			if ( isset( $widget['context'] ) && 'core' !== $widget['context'] ) {
				if ( ! isset( $dashboards[ $dashboard_id ] ) ) {
					$dashboards[ $dashboard_id ] = array(
						'id'          => $dashboard_id,
						'name'        => $widget['context_name'] ?? ucfirst( str_replace( array( '-', '_' ), ' ', $dashboard_id ) ),
						'description' => sprintf( __( 'Dashboard for %s', 'wpshadow' ), $widget['context_name'] ?? $dashboard_id ),
						'icon'        => 'dashicons-admin-plugins',
						'context'     => $widget['context'],
						'visible'     => true,
						'priority'    => 100,
						'widgets'     => array(),
					);
				}
			}

			if ( ! isset( $dashboards[ $dashboard_id ]['widgets'] ) ) {
				$dashboards[ $dashboard_id ]['widgets'] = array();
			}
			$dashboards[ $dashboard_id ]['widgets'][] = $widget['id'];
		}

		uasort(
			$dashboards,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);

		return $dashboards;
	}

	public static function get_dashboard( string $dashboard_id ): ?array {
		$dashboards = self::get_dashboards();
		return $dashboards[ $dashboard_id ] ?? null;
	}

	public static function can_access_dashboard( string $dashboard_id ): bool {
		$dashboard = self::get_dashboard( $dashboard_id );
		if ( ! $dashboard ) {
			return false;
		}

		if ( ! $dashboard['visible'] ) {
			return false;
		}

		if ( isset( $dashboard['context'] ) && 'core' !== $dashboard['context'] ) {

			$context = $dashboard['context'];
			if ( ! WPSHADOW_Module_Registry::is_installed( $context ) ) {
				return false;
			}
		}

		$widgets = WPSHADOW_Widget_Registry::get_widgets_for_dashboard( $dashboard_id );
		foreach ( $widgets as $widget ) {
			if ( ! current_user_can( $widget['capability'] ?? 'manage_options' ) ) {
				return false;
			}
		}

		return true;
	}

	public static function get_accessible_dashboards(): array {
		$dashboards = self::get_dashboards();
		$accessible = array();

		foreach ( $dashboards as $dashboard_id => $dashboard ) {
			if ( self::can_access_dashboard( $dashboard_id ) ) {
				$accessible[ $dashboard_id ] = $dashboard;
			}
		}

		return $accessible;
	}

	public static function render_dashboard_tabs( string $active_dashboard = 'overview' ): void {
		$dashboards = self::get_accessible_dashboards();

		if ( empty( $dashboards ) ) {
			return;
		}

		?>
		<h2 class="nav-tab-wrapper wps-dashboard-tabs">
			<?php foreach ( $dashboards as $dashboard_id => $dashboard ) : ?>
				<a
					href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&dashboard=' . $dashboard_id ) ); ?>"
					class="nav-tab <?php echo $active_dashboard === $dashboard_id ? 'nav-tab-active' : ''; ?>"
				>
					<span class="dashicons <?php echo esc_attr( $dashboard['icon'] ); ?>"></span>
					<?php echo esc_html( $dashboard['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	public static function render_dashboard( string $dashboard_id = 'overview' ): void {
		$dashboard = self::get_dashboard( $dashboard_id );

		if ( ! $dashboard || ! self::can_access_dashboard( $dashboard_id ) ) {
			wp_die( esc_html__( 'You do not have permission to access this dashboard.', 'wpshadow' ) );
		}

		?>
		<div class="wrap wps-dashboard-wrap">
			<h1><?php echo esc_html( $dashboard['name'] ); ?></h1>
			<?php if ( ! empty( $dashboard['description'] ) ) : ?>
				<p class="description"><?php echo esc_html( $dashboard['description'] ); ?></p>
			<?php endif; ?>

			<?php self::render_dashboard_tabs( $dashboard_id ); ?>

			<div class="wps-dashboard-content">
				<?php WPSHADOW_Widget_Registry::render_widgets_for_dashboard( $dashboard_id ); ?>
			</div>
		</div>
		<?php
	}

	public static function clear_cache(): void {
		self::$dashboards_cache = null;
		delete_option( self::CACHE_KEY );
	}

	public static function maybe_refresh_cache(): void {
		$cached = get_option( self::CACHE_KEY );
		if ( ! is_array( $cached ) || ! isset( $cached['version'] ) || $cached['version'] !== self::CACHE_VERSION ) {
			self::clear_cache();
		}
	}
}
