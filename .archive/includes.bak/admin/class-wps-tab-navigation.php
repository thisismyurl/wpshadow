<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Tab_Navigation {
	private const QUERY_VAR_TAB    = 'wpshadow_tab';
	private const QUERY_VAR_MODULE = 'module';
	private const QUERY_VAR_HUB    = 'wpshadow_hub'; 
	private const QUERY_VAR_SPOKE  = 'wpshadow_spoke'; 
	public const TAB_DASHBOARD_SETTINGS = 'dashboard_settings';

	public static function get_current_context(): array {

		$module = sanitize_text_field( (string) ( $_GET[ self::QUERY_VAR_MODULE ] ?? '' ) );
		$hub    = sanitize_text_field( (string) ( $_GET[ self::QUERY_VAR_HUB ] ?? '' ) );
		$spoke  = sanitize_text_field( (string) ( $_GET[ self::QUERY_VAR_SPOKE ] ?? '' ) );

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

	private static function determine_level(): string {
		if ( ! empty( $_GET['wpshadow_format'] ) ) {
			return 'format';
		}
		if ( ! empty( $_GET[ self::QUERY_VAR_SPOKE ] ) ) {
			return 'spoke';
		}

		if ( ! empty( $_GET[ self::QUERY_VAR_MODULE ] ) || ! empty( $_GET[ self::QUERY_VAR_HUB ] ) ) {
			return 'hub';
		}
		return 'core';
	}

	public static function render_tabs( array $tabs, string $active_tab ): void {
		if ( empty( $tabs ) ) {
			return;
		}

		$tabs = array_filter(
			$tabs,
			function ( $tab ) {
				return self::TAB_DASHBOARD_SETTINGS !== $tab['id'];
			}
		);

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
					<?php echo $icon . $tab_label; ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	public static function build_tab_url( string $tab_id, array $extra_args = array() ): string {
		$base_url = admin_url( 'admin.php' );
		$args     = array( 'page' => 'wpshadow' );

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

	public static function build_hub_url( string $hub_id, string $tab = 'dashboard' ): string {

		$hub_id   = self::normalize_hub_id( $hub_id );
		$base_url = admin_url( 'admin.php' );
		$args     = array(
			'page'                 => 'wpshadow',
			self::QUERY_VAR_MODULE => $hub_id,
		);

		return add_query_arg( $args, $base_url );
	}

	private static function normalize_hub_id( string $hub_id ): string {

		if ( ! str_contains( $hub_id, '-support-' ) ) {
			return $hub_id;
		}

		$parts = explode( '-support-', $hub_id );
		return $parts[0];
	}

	public static function build_spoke_url( string $hub_id, string $spoke_id, string $tab = 'dashboard' ): string {
		return self::build_tab_url(
			$tab,
			array(
				self::QUERY_VAR_HUB   => $hub_id,
				self::QUERY_VAR_SPOKE => $spoke_id,
			)
		);
	}

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

	public static function get_core_tabs(): array {
		$tabs = array(
			self::build_tab( 'dashboard', __( 'Dashboard', 'wpshadow' ), 'dashicons-dashboard' ),
			self::build_tab( 'dashboard_settings', __( 'Settings', 'wpshadow' ), 'dashicons-admin-generic' ),
			self::build_tab( 'features', __( 'Features', 'wpshadow' ), 'dashicons-admin-plugins' ),
			self::build_tab( 'help', __( 'Help', 'wpshadow' ), 'dashicons-editor-help' ),
		);

		return $tabs;
	}

	public static function get_hub_tabs( string $hub_id ): array {
		return array(
			self::build_tab( 'dashboard', __( 'Dashboard', 'wpshadow' ), 'dashicons-dashboard' ),
			self::build_tab( 'dashboard_settings', __( 'Settings', 'wpshadow' ), 'dashicons-admin-generic' ),
			self::build_tab( 'features', __( 'Features', 'wpshadow' ), 'dashicons-admin-plugins' ),
			self::build_tab( 'help', __( 'Help', 'wpshadow' ), 'dashicons-editor-help' ),
		);
	}

	public static function get_spoke_tabs( string $hub_id, string $spoke_id ): array {
		return array(
			self::build_tab( 'dashboard', __( 'Dashboard', 'wpshadow' ), 'dashicons-dashboard' ),
			self::build_tab( 'features', __( 'Features', 'wpshadow' ), 'dashicons-admin-plugins' ),
			self::build_tab( 'help', __( 'Help', 'wpshadow' ), 'dashicons-editor-help' ),
		);
	}

	public static function get_breadcrumbs( array $context ): array {
		$crumbs = array(
			array(
				'label' => __( 'Support', 'wpshadow' ),
				'url'   => admin_url( 'admin.php?page=wpshadow' ),
			),
		);

		if ( ! empty( $context['hub'] ) ) {
			$hub_label = ucfirst( $context['hub'] ) . ' ' . __( 'Hub', 'wpshadow' );
			$crumbs[]  = array(
				'label' => $hub_label,
				'url'   => self::build_hub_url( $context['hub'] ),
			);
		}

		if ( ! empty( $context['spoke'] ) ) {
			$spoke_label = strtoupper( $context['spoke'] ) . ' ' . __( 'Support', 'wpshadow' );
			$crumbs[]    = array(
				'label' => $spoke_label,
				'url'   => self::build_spoke_url( $context['hub'], $context['spoke'] ),
			);
		}

		if ( self::TAB_DASHBOARD_SETTINGS === $context['tab'] && empty( $context['hub'] ) && empty( $context['spoke'] ) ) {
			$crumbs[] = array(
				'label' => __( 'Dashboard', 'wpshadow' ),
				'url'   => admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard' ),
			);
			$crumbs[] = array(
				'label' => __( 'Dashboard Settings', 'wpshadow' ),
				'url'   => admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard_settings' ),
			);
		}

		return $crumbs;
	}

	public static function render_breadcrumbs( array $context ): void {
		$crumbs = self::get_breadcrumbs( $context );
		if ( count( $crumbs ) <= 1 ) {
			return; 
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
