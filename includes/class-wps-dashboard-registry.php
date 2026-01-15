<?php
/**
 * Dashboard Registry - Auto-discovers and manages dashboards
 *
 * Dashboards are tab-based views that contain widgets.
 * They are auto-discovered from widget and feature metadata.
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
 * WPSHADOW_Dashboard_Registry Class
 *
 * Manages dashboard discovery, registration, and rendering.
 */
class WPSHADOW_Dashboard_Registry {

	/**
	 * Dashboard cache.
	 *
	 * @var array|null
	 */
	private static ?array $dashboards_cache = null;

	/**
	 * Cache option name.
	 */
	private const CACHE_KEY = 'wpshadow_dashboards_cache';

	/**
	 * Cache version for invalidation.
	 */
	private const CACHE_VERSION = '1.0.0';

	/**
	 * Initialize dashboard registry.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'maybe_refresh_cache' ) );
		add_action( 'wpshadow_feature_state_changed', array( __CLASS__, 'clear_cache' ) );
		add_action( 'wpshadow_widget_registered', array( __CLASS__, 'clear_cache' ) );
	}

	/**
	 * Get all registered dashboards.
	 *
	 * @param bool $force_refresh Force cache refresh.
	 * @return array Dashboard data.
	 */
	public static function get_dashboards( bool $force_refresh = false ): array {
		if ( null !== self::$dashboards_cache && ! $force_refresh ) {
			return self::$dashboards_cache;
		}

		// Try to load from cache.
		$cached = get_option( self::CACHE_KEY );
		if ( ! $force_refresh && is_array( $cached ) && isset( $cached['version'] ) && $cached['version'] === self::CACHE_VERSION ) {
			self::$dashboards_cache = $cached['data'];
			return self::$dashboards_cache;
		}

		// Build dashboards from scratch.
		self::$dashboards_cache = self::discover_dashboards();

		// Save to cache.
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

	/**
	 * Discover dashboards from widgets and features.
	 *
	 * @return array Dashboard data.
	 */
	private static function discover_dashboards(): array {
		$dashboards = array(
			// Core dashboards (always present).
			'overview'    => array(
				'id'          => 'overview',
				'name'        => __( 'Overview', 'plugin-wpshadow' ),
				'description' => __( 'Main dashboard with key metrics and quick actions', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-dashboard',
				'context'     => 'core',
				'visible'     => true,
				'priority'    => 10,
				'widgets'     => array(),
			),
			'performance' => array(
				'id'          => 'performance',
				'name'        => __( 'Performance', 'plugin-wpshadow' ),
				'description' => __( 'Performance monitoring and optimization', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-performance',
				'context'     => 'core',
				'visible'     => true,
				'priority'    => 20,
				'widgets'     => array(),
			),
			'security'    => array(
				'id'          => 'security',
				'name'        => __( 'Security', 'plugin-wpshadow' ),
				'description' => __( 'Security hardening and vulnerability monitoring', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-shield',
				'context'     => 'core',
				'visible'     => true,
				'priority'    => 30,
				'widgets'     => array(),
			),
		);

		// Discover additional dashboards from widgets.
		$widgets = WPSHADOW_Widget_Registry::get_widgets();
		foreach ( $widgets as $widget ) {
			$dashboard_id = $widget['dashboard'] ?? 'overview';

			// If this is a hub/spoke specific dashboard, create it.
			if ( isset( $widget['context'] ) && 'core' !== $widget['context'] ) {
				if ( ! isset( $dashboards[ $dashboard_id ] ) ) {
					$dashboards[ $dashboard_id ] = array(
						'id'          => $dashboard_id,
						'name'        => $widget['context_name'] ?? ucfirst( str_replace( array( '-', '_' ), ' ', $dashboard_id ) ),
						'description' => sprintf( __( 'Dashboard for %s', 'plugin-wpshadow' ), $widget['context_name'] ?? $dashboard_id ),
						'icon'        => 'dashicons-admin-plugins',
						'context'     => $widget['context'],
						'visible'     => true,
						'priority'    => 100,
						'widgets'     => array(),
					);
				}
			}

			// Add widget to dashboard's widget list.
			if ( ! isset( $dashboards[ $dashboard_id ]['widgets'] ) ) {
				$dashboards[ $dashboard_id ]['widgets'] = array();
			}
			$dashboards[ $dashboard_id ]['widgets'][] = $widget['id'];
		}

		// Sort dashboards by priority.
		uasort(
			$dashboards,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);

		return $dashboards;
	}

	/**
	 * Get a specific dashboard.
	 *
	 * @param string $dashboard_id Dashboard ID.
	 * @return array|null Dashboard data or null if not found.
	 */
	public static function get_dashboard( string $dashboard_id ): ?array {
		$dashboards = self::get_dashboards();
		return $dashboards[ $dashboard_id ] ?? null;
	}

	/**
	 * Check if user can access dashboard.
	 *
	 * @param string $dashboard_id Dashboard ID.
	 * @return bool True if user has access.
	 */
	public static function can_access_dashboard( string $dashboard_id ): bool {
		$dashboard = self::get_dashboard( $dashboard_id );
		if ( ! $dashboard ) {
			return false;
		}

		// Check if dashboard is visible.
		if ( ! $dashboard['visible'] ) {
			return false;
		}

		// Check context-based access (hub/spoke).
		if ( isset( $dashboard['context'] ) && 'core' !== $dashboard['context'] ) {
			// Check if required hub/spoke is active.
			$context = $dashboard['context'];
			if ( ! WPSHADOW_Module_Registry::is_installed( $context ) ) {
				return false;
			}
		}

		// Check user capability (minimum across all widgets).
		$widgets = WPSHADOW_Widget_Registry::get_widgets_for_dashboard( $dashboard_id );
		foreach ( $widgets as $widget ) {
			if ( ! current_user_can( $widget['capability'] ?? 'manage_options' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get dashboards accessible by current user.
	 *
	 * @return array Accessible dashboards.
	 */
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

	/**
	 * Render dashboard tabs navigation.
	 *
	 * @param string $active_dashboard Currently active dashboard ID.
	 * @return void
	 */
	public static function render_dashboard_tabs( string $active_dashboard = 'overview' ): void {
		$dashboards = self::get_accessible_dashboards();

		if ( empty( $dashboards ) ) {
			return;
		}

		?>
		<h2 class="nav-tab-wrapper wps-dashboard-tabs">
			<?php foreach ( $dashboards as $dashboard_id => $dashboard ) : ?>
				<a
					href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&dashboard=' . $dashboard_id ) ); ?>"
					class="nav-tab <?php echo $active_dashboard === $dashboard_id ? 'nav-tab-active' : ''; ?>"
				>
					<span class="dashicons <?php echo esc_attr( $dashboard['icon'] ); ?>"></span>
					<?php echo esc_html( $dashboard['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	/**
	 * Render complete dashboard.
	 *
	 * @param string $dashboard_id Dashboard ID to render.
	 * @return void
	 */
	public static function render_dashboard( string $dashboard_id = 'overview' ): void {
		$dashboard = self::get_dashboard( $dashboard_id );

		if ( ! $dashboard || ! self::can_access_dashboard( $dashboard_id ) ) {
			wp_die( esc_html__( 'You do not have permission to access this dashboard.', 'plugin-wpshadow' ) );
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

	/**
	 * Clear dashboard cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$dashboards_cache = null;
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
