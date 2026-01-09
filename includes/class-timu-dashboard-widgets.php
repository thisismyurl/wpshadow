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

	public static function render_metabox_suite_overview(): void {
		self::widget_suite_overview();
	}

	public static function render_metabox_active_hubs(): void {
		self::widget_active_hubs();
	}

	public static function render_metabox_recent_activity(): void {
		self::widget_recent_activity();
	}

	public static function render_metabox_quick_actions(): void {
		self::widget_quick_actions();
	}

	private static function widget_suite_overview(): void {
		$catalog   = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$total     = count( $catalog );
		$active    = count( array_filter( $catalog, fn( $m ) => ! empty( $m['status']['active'] ) ) );
		$hubs      = count( array_filter( $catalog, fn( $m ) => 'hub' === ( $m['type'] ?? '' ) ) );
		$spokes    = count( array_filter( $catalog, fn( $m ) => 'spoke' === ( $m['type'] ?? '' ) ) );
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Suite Overview', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<ul class="timu-stats-list">
					<li><span class="dashicons dashicons-admin-plugins"></span> <?php echo esc_html( sprintf( __( '%d Total Modules', 'core-support-thisismyurl' ), $total ) ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html( sprintf( __( '%d Active Modules', 'core-support-thisismyurl' ), $active ) ); ?></li>
					<li><span class="dashicons dashicons-networking"></span> <?php echo esc_html( sprintf( __( '%d Hubs', 'core-support-thisismyurl' ), $hubs ) ); ?></li>
					<li><span class="dashicons dashicons-hammer"></span> <?php echo esc_html( sprintf( __( '%d Spokes', 'core-support-thisismyurl' ), $spokes ) ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}

	private static function widget_active_hubs(): void {
		$catalog     = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$active_hubs = array_filter( $catalog, fn( $m ) => 'hub' === ( $m['type'] ?? '' ) && ! empty( $m['status']['active'] ) );
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Active Hubs', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<?php if ( empty( $active_hubs ) ) : ?>
					<p><?php esc_html_e( 'No hubs currently active.', 'core-support-thisismyurl' ); ?></p>
				<?php else : ?>
					<ul class="timu-hub-list">
						<?php foreach ( $active_hubs as $hub ) : ?>
							<?php
							$hub_id   = sanitize_key( $hub['id'] ?? '' );
							$hub_name = esc_html( $hub['name'] ?? $hub_id );
							$hub_url  = TIMU_Tab_Navigation::build_hub_url( $hub_id );
							?>
							<li>
								<a href="<?php echo esc_url( $hub_url ); ?>">
									<span class="dashicons dashicons-networking"></span>
									<?php echo $hub_name; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	private static function widget_recent_activity(): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Recent Activity', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p><em><?php esc_html_e( 'Activity tracking coming soon.', 'core-support-thisismyurl' ); ?></em></p>
			</div>
		</div>
		<?php
	}

	private static function widget_quick_actions(): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Quick Actions', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p>
					<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_tab_url( 'modules' ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-admin-plugins"></span>
						<?php esc_html_e( 'Manage Modules', 'core-support-thisismyurl' ); ?>
					</a>
				</p>
				<p>
					<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_tab_url( 'settings' ) ); ?>" class="button">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Configure Settings', 'core-support-thisismyurl' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/* ====== HUB WIDGETS ====== */

	private static function widget_hub_overview( string $hub_id ): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Hub Overview', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p><?php echo esc_html( sprintf( __( 'Managing %s processing and distribution.', 'core-support-thisismyurl' ), strtoupper( $hub_id ) ) ); ?></p>
			</div>
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
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Active Spokes', 'core-support-thisismyurl' ); ?></h2>
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
		</div>
		<?php
	}

	private static function widget_hub_stats( string $hub_id ): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Statistics', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p><em><?php esc_html_e( 'Processing stats coming soon.', 'core-support-thisismyurl' ); ?></em></p>
			</div>
		</div>
		<?php
	}

	private static function widget_hub_quick_actions( string $hub_id ): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Quick Actions', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p>
					<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_hub_url( $hub_id, 'settings' ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Hub Settings', 'core-support-thisismyurl' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/* ====== SPOKE WIDGETS ====== */

	private static function widget_spoke_overview( string $hub_id, string $spoke_id ): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Spoke Overview', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p><?php echo esc_html( sprintf( __( 'Managing %s format support.', 'core-support-thisismyurl' ), strtoupper( $spoke_id ) ) ); ?></p>
			</div>
		</div>
		<?php
	}

	private static function widget_spoke_features( string $spoke_id ): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Features', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<ul class="timu-features-list">
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Format Detection', 'core-support-thisismyurl' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Conversion Support', 'core-support-thisismyurl' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Metadata Handling', 'core-support-thisismyurl' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}

	private static function widget_spoke_stats( string $spoke_id ): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Statistics', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p><em><?php esc_html_e( 'Format-specific stats coming soon.', 'core-support-thisismyurl' ); ?></em></p>
			</div>
		</div>
		<?php
	}

	private static function widget_spoke_quick_actions( string $hub_id, string $spoke_id ): void {
		?>
		<div class="timu-dashboard-widget">
			<h2><?php esc_html_e( 'Quick Actions', 'core-support-thisismyurl' ); ?></h2>
			<div class="timu-widget-content">
				<p>
					<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id, 'settings' ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Spoke Settings', 'core-support-thisismyurl' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}
}

/* @changelog Added TIMU_Dashboard_Widgets for tab-based dashboard rendering */
