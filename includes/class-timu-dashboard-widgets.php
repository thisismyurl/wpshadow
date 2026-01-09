<?php
/**
 * Dashboard widget system for tab-based interface.
 *
 * @package TIMU_Core_Support
 * @since 1.0.0
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Widgets Manager
 * Mimics WordPress Core dashboard functionality.
 */
class TIMU_Dashboard_Widgets {
	/**
	 * Render Core-level dashboard.
	 *
	 * @return void
	 */
	public static function render_core_dashboard(): void {
		?>
		<div class="wrap timu-dashboard">
			<h1><?php echo esc_html__( 'Support Dashboard', 'core-support-thisismyurl' ); ?></h1>

			<div class="timu-dashboard-widgets-wrap">
				<div class="timu-dashboard-col-container">
					<div id="timu-dashboard-col-1" class="timu-dashboard-col">
						<?php self::widget_suite_overview(); ?>
						<?php self::widget_active_hubs(); ?>
					</div>

					<div id="timu-dashboard-col-2" class="timu-dashboard-col">
						<?php self::widget_recent_activity(); ?>
						<?php self::widget_quick_actions(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Hub-level dashboard.
	 *
	 * @param string $hub_id Hub identifier.
	 * @return void
	 */
	public static function render_hub_dashboard( string $hub_id ): void {
		$hub_name = ucfirst( $hub_id );
		?>
		<div class="wrap timu-dashboard">
			<h1><?php echo esc_html( sprintf( __( '%s Hub Dashboard', 'core-support-thisismyurl' ), $hub_name ) ); ?></h1>

			<div class="timu-dashboard-widgets-wrap">
				<div class="timu-dashboard-col-container">
					<div id="timu-dashboard-col-1" class="timu-dashboard-col">
						<?php self::widget_hub_overview( $hub_id ); ?>
						<?php self::widget_active_spokes( $hub_id ); ?>
					</div>

					<div id="timu-dashboard-col-2" class="timu-dashboard-col">
						<?php self::widget_hub_stats( $hub_id ); ?>
						<?php self::widget_hub_quick_actions( $hub_id ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Spoke-level dashboard.
	 *
	 * @param string $hub_id Hub identifier.
	 * @param string $spoke_id Spoke identifier.
	 * @return void
	 */
	public static function render_spoke_dashboard( string $hub_id, string $spoke_id ): void {
		$spoke_name = strtoupper( $spoke_id );
		?>
		<div class="wrap timu-dashboard">
			<h1><?php echo esc_html( sprintf( __( '%s Support Dashboard', 'core-support-thisismyurl' ), $spoke_name ) ); ?></h1>

			<div class="timu-dashboard-widgets-wrap">
				<div class="timu-dashboard-col-container">
					<div id="timu-dashboard-col-1" class="timu-dashboard-col">
						<?php self::widget_spoke_overview( $hub_id, $spoke_id ); ?>
						<?php self::widget_spoke_features( $spoke_id ); ?>
					</div>

					<div id="timu-dashboard-col-2" class="timu-dashboard-col">
						<?php self::widget_spoke_stats( $spoke_id ); ?>
						<?php self::widget_spoke_quick_actions( $hub_id, $spoke_id ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/* ====== CORE WIDGETS ====== */

	public static function render_metabox_health(): void {
		self::widget_health();
	}

	public static function render_metabox_at_a_glance(): void {
		self::widget_at_a_glance();
	}

	public static function render_metabox_activity(): void {
		self::widget_activity();
	}

	public static function render_metabox_scheduled_tasks(): void {
		self::widget_scheduled_tasks();
	}

	public static function render_metabox_quick_actions(): void {
		self::widget_quick_actions();
	}

	public static function render_metabox_events_and_news(): void {
		self::widget_events_and_news();
	}

	public static function render_metabox_modules(): void {
		self::widget_modules();
	}

	private static function widget_health(): void {
		$catalog  = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$total    = count( $catalog );
		$active   = count( array_filter( $catalog, fn( $m ) => ! empty( $m['status']['active'] ) ) );
		$inactive = $total - $active;
		$health   = 100;

		// Deduct points for inactive modules.
		if ( $inactive > 0 ) {
			$health = max( 50, 100 - ( $inactive * 10 ) );
		}

		// Color code based on health.
		if ( $health >= 90 ) {
			$color_class = 'timu-health-good';
			$label       = __( 'Good', 'core-support-thisismyurl' );
		} elseif ( $health >= 70 ) {
			$color_class = 'timu-health-warning';
			$label       = __( 'Warning', 'core-support-thisismyurl' );
		} else {
			$color_class = 'timu-health-critical';
			$label       = __( 'Critical', 'core-support-thisismyurl' );
		}
		?>
		<div class="timu-widget-content">
			<div class="timu-health-status <?php echo esc_attr( $color_class ); ?>">
				<div class="timu-health-score"><?php echo esc_html( $health ); ?>%</div>
				<div class="timu-health-label"><?php echo esc_html( $label ); ?></div>
			</div>
			<ul class="timu-health-checks">
					<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html( sprintf( __( '%d Active Modules', 'core-support-thisismyurl' ), $active ) ); ?></li>
				<?php if ( $inactive > 0 ) : ?>
					<li class="warning"><span class="dashicons dashicons-warning"></span> <?php echo esc_html( sprintf( __( '%d Inactive Modules', 'core-support-thisismyurl' ), $inactive ) ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php
	}

	private static function widget_at_a_glance(): void {
		$catalog = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$total   = count( $catalog );
		$active  = count( array_filter( $catalog, fn( $m ) => ! empty( $m['status']['active'] ) ) );
		$hubs    = count( array_filter( $catalog, fn( $m ) => 'hub' === ( $m['type'] ?? '' ) ) );
		$spokes  = count( array_filter( $catalog, fn( $m ) => 'spoke' === ( $m['type'] ?? '' ) ) );
		?>
		<div class="timu-widget-content">
			<ul class="timu-stats-list">
					<li><span class="dashicons dashicons-admin-plugins"></span> <?php echo esc_html( sprintf( __( '%d Total Modules', 'core-support-thisismyurl' ), $total ) ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html( sprintf( __( '%d Active', 'core-support-thisismyurl' ), $active ) ); ?></li>
					<li><span class="dashicons dashicons-networking"></span> <?php echo esc_html( sprintf( __( '%d Hubs', 'core-support-thisismyurl' ), $hubs ) ); ?></li>
				<li><span class="dashicons dashicons-hammer"></span> <?php echo esc_html( sprintf( __( '%d Spokes', 'core-support-thisismyurl' ), $spokes ) ); ?></li>
			</ul>
		</div>
		<?php
	}

	private static function widget_activity(): void {
		?>
		<div class="timu-widget-content">
			<p><em><?php esc_html_e( 'Activity log integration coming soon.', 'core-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_scheduled_tasks(): void {
		?>
		<div class="timu-widget-content">
			<p><em><?php esc_html_e( 'No scheduled tasks configured.', 'core-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_modules(): void {
		$context = TIMU_Tab_Navigation::get_current_context();
		$catalog = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();

		// Determine which modules to show based on current level.
		$next_level_modules = array();

		if ( 'core' === $context['level'] ) {
			// On core: show hubs (Media, License, etc.)
			$next_level_modules = array_filter(
				$catalog,
				fn( $m ) => 'hub' === ( $m['type'] ?? '' ) && ! empty( $m['status']['active'] )
			);
		} elseif ( 'hub' === $context['level'] && ! empty( $context['hub'] ) ) {
			// On hub (e.g., image): show spokes under that hub.
			$hub_prefix         = $context['hub'] . '-';
			$next_level_modules = array_filter(
				$catalog,
				fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
					&& ! empty( $m['status']['active'] )
					&& str_starts_with( $m['slug'] ?? '', $hub_prefix )
			);
		}

		?>
		<div class="timu-widget-content">
			<?php if ( empty( $next_level_modules ) ) : ?>
				<p><?php esc_html_e( 'No modules available at this level.', 'core-support-thisismyurl' ); ?></p>
			<?php else : ?>
				<ul class="timu-modules-list" style="list-style: none; padding: 0; margin: 0;">
					<?php foreach ( $next_level_modules as $module ) : ?>
						<?php
						$module_slug    = sanitize_key( $module['slug'] ?? '' );
						$module_name    = esc_html( $module['name'] ?? '' );
						$module_version = esc_html( $module['version'] ?? '?.?.?' );
						$is_hub         = 'hub' === ( $module['type'] ?? '' );

						// Build navigation URL.
						if ( $is_hub ) {
							$module_url = TIMU_Tab_Navigation::build_hub_url( $module_slug );
						} else {
							$module_url = TIMU_Tab_Navigation::build_spoke_url(
								$context['hub'] ?? '',
								$module_slug
							);
						}
						?>
						<li style="padding: 10px 0; border-bottom: 1px solid #e5e5e5;">
							<a href="<?php echo esc_url( $module_url ); ?>" style="text-decoration: none; color: inherit;">
								<span class="dashicons dashicons-admin-plugins"></span>
								<strong><?php echo $module_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
								<br />
								<small style="color: #666;">v<?php echo $module_version; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></small>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function widget_quick_actions(): void {
		?>
		<div class="timu-widget-content">
			<p>
				<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_tab_url( 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Configure Settings', 'core-support-thisismyurl' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	private static function widget_events_and_news(): void {
		?>
		<div class="timu-widget-content">
			<div class="timu-events-feed">
				<p><em><?php esc_html_e( 'Loading latest events and news from Support Suite repositories...', 'core-support-thisismyurl' ); ?></em></p>
				<ul class="timu-events-list" style="list-style: none; padding: 0; margin: 0;">
					<li style="padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
						<strong><?php esc_html_e( 'Release Updates', 'core-support-thisismyurl' ); ?></strong>
						<br />
						<small><?php esc_html_e( 'Latest plugin releases and updates', 'core-support-thisismyurl' ); ?></small>
					</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
						<strong><?php esc_html_e( 'GitHub Issues', 'core-support-thisismyurl' ); ?></strong>
						<br />
						<small><?php esc_html_e( 'Recent discussions and feature requests', 'core-support-thisismyurl' ); ?></small>
					</li>
					<li style="padding: 8px 0;">
						<strong><?php esc_html_e( 'Suite Announcements', 'core-support-thisismyurl' ); ?></strong>
						<br />
						<small><?php esc_html_e( 'Important suite-wide updates', 'core-support-thisismyurl' ); ?></small>
					</li>
				</ul>
				<p style="margin-top: 12px; text-align: center;">
					<a href="https://github.com/thisismyurl?tab=repositories" target="_blank" rel="noopener"><?php esc_html_e( 'Visit GitHub →', 'core-support-thisismyurl' ); ?></a>
				</p>
			</div>
		</div>
		<?php
	}

	/* ====== HUB WIDGETS ====== */

	private static function widget_hub_overview( string $hub_id ): void {
		?>
		<div class="timu-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s processing and distribution.', 'core-support-thisismyurl' ), strtoupper( $hub_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_active_spokes( string $hub_id ): void {
		$catalog = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$spokes  = array_filter(
			$catalog,
			fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
				&& ! empty( $m['status']['active'] )
				&& str_starts_with( $m['id'] ?? '', $hub_id )
		);
		?>
		<div class="timu-widget-content">
			<?php if ( empty( $spokes ) ) : ?>
				<p><?php esc_html_e( 'No spokes currently active for this hub.', 'core-support-thisismyurl' ); ?></p>
			<?php else : ?>
				<ul class="timu-spoke-list">
					<?php foreach ( $spokes as $spoke ) : ?>
						<?php
						$spoke_id   = sanitize_key( str_replace( $hub_id . '-', '', $spoke['id'] ?? '' ) );
						$spoke_name = esc_html( $spoke['name'] ?? $spoke_id );
						$spoke_url  = TIMU_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id );
						?>
						<li>
							<a href="<?php echo esc_url( $spoke_url ); ?>">
								<span class="dashicons dashicons-hammer"></span>
								<?php echo $spoke_name; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function widget_hub_stats( string $hub_id ): void {
		?>
		<div class="timu-widget-content">
			<p><em><?php esc_html_e( 'Processing stats coming soon.', 'core-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_hub_quick_actions( string $hub_id ): void {
		?>
		<div class="timu-widget-content">
			<p>
				<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_hub_url( $hub_id, 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Hub Settings', 'core-support-thisismyurl' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/* ====== SPOKE WIDGETS ====== */

	private static function widget_spoke_overview( string $hub_id, string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s format support.', 'core-support-thisismyurl' ), strtoupper( $spoke_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_spoke_features( string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<ul class="timu-features-list">
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Format Detection', 'core-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Conversion Support', 'core-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Metadata Handling', 'core-support-thisismyurl' ); ?></li>
			</ul>
		</div>
		<?php
	}

	private static function widget_spoke_stats( string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<p><em><?php esc_html_e( 'Format-specific stats coming soon.', 'core-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_spoke_quick_actions( string $hub_id, string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<p>
				<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id, 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Spoke Settings', 'core-support-thisismyurl' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}

/* @changelog Added TIMU_Dashboard_Widgets for tab-based dashboard rendering */
