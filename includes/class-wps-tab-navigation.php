<?php
/**
 * Tab-based navigation system for thisismyurl Suite.
 *
 * @package wp_support_Support
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tab Navigation Manager
 * Hierarchical: Core > Hub > Spoke > Format
 */
class WPS_Tab_Navigation {
	private const QUERY_VAR_TAB    = 'WPS_tab';
	private const QUERY_VAR_MODULE = 'module';
	private const QUERY_VAR_HUB    = 'WPS_hub'; // Legacy support
	private const QUERY_VAR_SPOKE  = 'WPS_spoke'; // Legacy support

	/**
	 * Get the current tab context from query parameters.
	 *
	 * @return array{level: string, hub: string, spoke: string, tab: string}
	 */
	public static function get_current_context(): array {
		// Check for new 'module' parameter first, fallback to legacy WPS_hub/WPS_spoke.
		$module = sanitize_text_field( (string) ( $_GET[ self::QUERY_VAR_MODULE ] ?? '' ) );
		$hub    = sanitize_text_field( (string) ( $_GET[ self::QUERY_VAR_HUB ] ?? '' ) );
		$spoke  = sanitize_text_field( (string) ( $_GET[ self::QUERY_VAR_SPOKE ] ?? '' ) );

		// If 'module' is set, use it as the hub (for now, treat all modules as hubs).
		if ( ! empty( $module ) ) {
			$hub = $module;
		}

		return array(
			'hub'   => $hub,
			'spoke' => $spoke,
			'tab'   => sanitize_text_field( (string) ( $_GET[ self::QUERY_VAR_TAB ] ?? 'dashboard' ) ),
			'level' => self::determine_level(),
		);
	}

	/**
	 * Determine current navigation level.
	 *
	 * @return string 'core'|'hub'|'spoke'|'format'
	 */
	private static function determine_level(): string {
		if ( ! empty( $_GET['WPS_format'] ) ) {
			return 'format';
		}
		if ( ! empty( $_GET[ self::QUERY_VAR_SPOKE ] ) ) {
			return 'spoke';
		}
		// Check both new 'module' param and legacy 'WPS_hub'.
		if ( ! empty( $_GET[ self::QUERY_VAR_MODULE ] ) || ! empty( $_GET[ self::QUERY_VAR_HUB ] ) ) {
			return 'hub';
		}
		return 'core';
	}

	/**
	 * Render tab navigation HTML.
	 *
	 * @param array<array{id: string, label: string, icon?: string, url?: string}> $tabs Tab definitions.
	 * @param string $active_tab Current active tab ID.
	 * @return void
	 */
	public static function render_tabs( array $tabs, string $active_tab ): void {
		if ( empty( $tabs ) ) {
			return;
		}
		?>
		<h2 class="nav-tab-wrapper wps-tab-navigation">
			<?php foreach ( $tabs as $tab ) : ?>
				<?php
				$tab_id    = esc_attr( $tab['id'] );
				$tab_label = esc_html( $tab['label'] );
				$tab_url   = isset( $tab['url'] ) ? esc_url( $tab['url'] ) : self::build_tab_url( $tab['id'] );
				$is_active = ( $tab_id === $active_tab );
				$classes   = $is_active ? 'nav-tab nav-tab-active' : 'nav-tab';
				$icon      = isset( $tab['icon'] ) ? '<span class="dashicons ' . esc_attr( $tab['icon'] ) . '"></span> ' : '';
				?>
				<a href="<?php echo $tab_url; ?>" class="<?php echo esc_attr( $classes ); ?>">
					<?php echo $icon . $tab_label; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	/**
	 * Build URL for a tab preserving context.
	 *
	 * @param string $tab_id Tab identifier.
	 * @param array<string, string> $extra_args Additional query args.
	 * @return string Tab URL.
	 */
	public static function build_tab_url( string $tab_id, array $extra_args = array() ): string {
		$base_url = admin_url( 'admin.php' );
		$args     = array( 'page' => 'wp-support' );

		// Preserve context - prioritize new 'module' param.
		if ( ! empty( $_GET[ self::QUERY_VAR_MODULE ] ) ) {
			$args[ self::QUERY_VAR_MODULE ] = sanitize_text_field( (string) $_GET[ self::QUERY_VAR_MODULE ] );
		} elseif ( ! empty( $_GET[ self::QUERY_VAR_HUB ] ) ) {
			$args[ self::QUERY_VAR_HUB ] = sanitize_text_field( (string) $_GET[ self::QUERY_VAR_HUB ] );
		}
		if ( ! empty( $_GET[ self::QUERY_VAR_SPOKE ] ) ) {
			$args[ self::QUERY_VAR_SPOKE ] = sanitize_text_field( (string) $_GET[ self::QUERY_VAR_SPOKE ] );
		}

		$args[ self::QUERY_VAR_TAB ] = $tab_id;
		$args                        = array_merge( $args, $extra_args );

		return add_query_arg( $args, $base_url );
	}

	/**
	 * Build hub navigation URL.
	 *
	 * @param string $hub_id Hub identifier (e.g., 'media-support-thisismyurl', 'vault-support-thisismyurl') or short form ('media', 'vault').
	 * @param string $tab Current tab (default: 'dashboard').
	 * @return string Hub URL.
	 */
	public static function build_hub_url( string $hub_id, string $tab = 'dashboard' ): string {
		// Extract short hub name from full slug if needed.
		$hub_id   = self::normalize_hub_id( $hub_id );
		$base_url = admin_url( 'admin.php' );
		$args     = array(
			'page'                 => 'wp-support',
			self::QUERY_VAR_MODULE => $hub_id,
		);

		return add_query_arg( $args, $base_url );
	}

	/**
	 * Normalize hub ID to short form.
	 *
	 * Converts 'media-support-thisismyurl' to 'media', 'vault-support-thisismyurl' to 'vault', etc.
	 *
	 * @param string $hub_id Full or short hub identifier.
	 * @return string Short hub identifier.
	 */
	private static function normalize_hub_id( string $hub_id ): string {
		// If already short form, return as-is.
		if ( ! str_contains( $hub_id, '-support-' ) ) {
			return $hub_id;
		}

		// Extract the prefix before '-support-'.
		$parts = explode( '-support-', $hub_id );
		return $parts[0];
	}

	/**
	 * Build spoke navigation URL.
	 *
	 * @param string $hub_id Hub identifier.
	 * @param string $spoke_id Spoke identifier (e.g., 'avif', 'webp').
	 * @param string $tab Current tab (default: 'dashboard').
	 * @return string Spoke URL.
	 */
	public static function build_spoke_url( string $hub_id, string $spoke_id, string $tab = 'dashboard' ): string {
		return self::build_tab_url(
			$tab,
			array(
				self::QUERY_VAR_HUB   => $hub_id,
				self::QUERY_VAR_SPOKE => $spoke_id,
			)
		);
	}

	/**
	 * Build standard tab array with defaults.
	 *
	 * @param string $id Tab identifier.
	 * @param string $label Tab label (will be translated).
	 * @param string $icon Dashicons class (e.g., 'dashicons-dashboard').
	 * @param string|null $url Optional custom URL (auto-generated if null).
	 * @return array{id: string, label: string, icon: string, url?: string} Tab definition.
	 */
	private static function build_tab( string $id, string $label, string $icon, ?string $url = null ): array {
		$tab = array(
			'id'    => $id,
			'label' => $label,
			'icon'  => $icon,
		);

		if ( null !== $url ) {
			$tab['url'] = $url;
		}

		return $tab;
	}

	/**
	 * Get tabs for Core level.
	 *
	 * @return array<array{id: string, label: string, icon: string}>
	 */
	public static function get_core_tabs(): array {
		return array(
			self::build_tab( 'dashboard', __( 'Dashboard', 'plugin-wp-support-thisismyurl' ), 'dashicons-dashboard' ),
			self::build_tab( 'settings', __( 'Settings', 'plugin-wp-support-thisismyurl' ), 'dashicons-admin-generic' ),
			self::build_tab( 'features', __( 'Features', 'plugin-wp-support-thisismyurl' ), 'dashicons-admin-plugins' ),
			self::build_tab( 'help', __( 'Help', 'plugin-wp-support-thisismyurl' ), 'dashicons-editor-help' ),
		);
	}

	/**
	 * Get tabs for Hub level.
	 *
	 * @param string $hub_id Hub identifier.
	 * @return array<array{id: string, label: string, icon: string}>
	 */
	public static function get_hub_tabs( string $hub_id ): array {
		return array(
			self::build_tab( 'dashboard', __( 'Dashboard', 'plugin-wp-support-thisismyurl' ), 'dashicons-dashboard' ),
			self::build_tab( 'settings', __( 'Settings', 'plugin-wp-support-thisismyurl' ), 'dashicons-admin-generic' ),
			self::build_tab( 'features', __( 'Features', 'plugin-wp-support-thisismyurl' ), 'dashicons-admin-plugins' ),
			self::build_tab( 'help', __( 'Help', 'plugin-wp-support-thisismyurl' ), 'dashicons-editor-help' ),
		);
	}

	/**
	 * Get tabs for Spoke level.
	 *
	 * @param string $hub_id Hub identifier.
	 * @param string $spoke_id Spoke identifier.
	 * @return array<array{id: string, label: string, icon: string}>
	 */
	public static function get_spoke_tabs( string $hub_id, string $spoke_id ): array {
		return array(
			self::build_tab( 'dashboard', __( 'Dashboard', 'plugin-wp-support-thisismyurl' ), 'dashicons-dashboard' ),
			self::build_tab( 'features', __( 'Features', 'plugin-wp-support-thisismyurl' ), 'dashicons-admin-plugins' ),
			self::build_tab( 'help', __( 'Help', 'plugin-wp-support-thisismyurl' ), 'dashicons-editor-help' ),
		);
	}

	/**
	 * Get breadcrumb navigation.
	 *
	 * @param array{level: string, hub: string, spoke: string, tab: string} $context Current context.
	 * @return array<array{label: string, url: string}>
	 */
	public static function get_breadcrumbs( array $context ): array {
		$crumbs = array(
			array(
				'label' => __( 'Support', 'plugin-wp-support-thisismyurl' ),
				'url'   => admin_url( 'admin.php?page=wp-support' ),
			),
		);

		if ( ! empty( $context['hub'] ) ) {
			$hub_label = ucfirst( $context['hub'] ) . ' ' . __( 'Hub', 'plugin-wp-support-thisismyurl' );
			$crumbs[]  = array(
				'label' => $hub_label,
				'url'   => self::build_hub_url( $context['hub'] ),
			);
		}

		if ( ! empty( $context['spoke'] ) ) {
			$spoke_label = strtoupper( $context['spoke'] ) . ' ' . __( 'Support', 'plugin-wp-support-thisismyurl' );
			$crumbs[]    = array(
				'label' => $spoke_label,
				'url'   => self::build_spoke_url( $context['hub'], $context['spoke'] ),
			);
		}

		return $crumbs;
	}

	/**
	 * Render breadcrumb navigation.
	 *
	 * @param array{level: string, hub: string, spoke: string, tab: string} $context Current context.
	 * @return void
	 */
	public static function render_breadcrumbs( array $context ): void {
		$crumbs = self::get_breadcrumbs( $context );
		if ( count( $crumbs ) <= 1 ) {
			return; // No breadcrumbs needed at root level.
		}
		?>
		<div class="wps-breadcrumbs">
			<?php
			$last_index = count( $crumbs ) - 1;
			foreach ( $crumbs as $index => $crumb ) :
				$is_last = ( $index === $last_index );
				?>
				<?php if ( ! $is_last ) : ?>
					<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
					<span class="separator"> &raquo; </span>
				<?php else : ?>
					<span class="current"><?php echo esc_html( $crumb['label'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
}

/* @changelog Added WPS_Tab_Navigation for hierarchical tab-based admin UI */


