<?php
/**
 * Page Activity Display Component
 *
 * Reusable component for displaying page-specific activities with real-time AJAX updates.
 * Displays filtered activities based on current page context (tools, reports, guardian, etc.)
 *
 * @package WPShadow
 * @subpackage Views
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render page-specific activity display
 *
 * @param string $context Page context (tools, reports, guardian, workflows, settings, security, performance)
 * @param int    $limit Maximum activities to display (default: 10)
 * @param string $report_slug Optional report slug for report-specific filtering
 * @return void
 */
function wpshadow_render_page_activities( string $context = '', int $limit = 10, string $report_slug = '' ): void {
	$show_activities = (bool) apply_filters( 'wpshadow_show_page_activities', false, $context, $limit, $report_slug );
	if ( ! $show_activities ) {
		return;
	}

	if ( empty( $context ) ) {
		return;
	}

	wp_enqueue_script(
		'wpshadow-page-activities',
		WPSHADOW_URL . 'assets/js/page-activities.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	$report_slug = sanitize_key( $report_slug );

	$nonce = wp_create_nonce( 'wpshadow_get_activities' );
	?>
	<div class="wps-card wps-mt-8">
		<div class="wps-card-header">
			<h3 class="wps-card-title wps-m-0">
				<span class="dashicons dashicons-clock wps-icon-mr-2" aria-hidden="true"></span>
				<?php esc_html_e( 'Recent Activity', 'wpshadow' ); ?>
			</h3>
			<span class="wps-heartbeat-dot"
				  role="status"
				  aria-label="<?php esc_attr_e( 'Live — updates automatically when diagnostics run', 'wpshadow' ); ?>"
				  title="<?php esc_attr_e( 'Live — updates automatically when diagnostics run', 'wpshadow' ); ?>"
			></span>
		</div>
		<div class="wps-card-body">
			<div class="wps-activity-timeline wps-activity-ajax-container" 
				 role="list" 
				 aria-label="<?php esc_attr_e( 'Recent page activity', 'wpshadow' ); ?>"
				 data-context="<?php echo esc_attr( $context ); ?>"
				 data-limit="<?php echo esc_attr( (string) $limit ); ?>"
				 data-nonce="<?php echo esc_attr( $nonce ); ?>"
					 data-refresh-interval="3000"
					 data-current-page="0"
					 <?php if ( ! empty( $report_slug ) ) : ?>data-report="<?php echo esc_attr( $report_slug ); ?>"<?php endif; ?>>
				<div class="wps-activity-loading">
					<span class="spinner"></span>
					<p><?php esc_html_e( 'Loading activity...', 'wpshadow' ); ?></p>
				</div>
			</div>
			<!-- Pagination -->
			<div class="wps-activity-pagination">
				<button class="wps-activity-pagination-prev button" disabled><?php esc_html_e( '← Previous', 'wpshadow' ); ?></button>
				<span class="wps-activity-pagination-info"></span>
				<button class="wps-activity-pagination-next button" disabled><?php esc_html_e( 'Next →', 'wpshadow' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Emit activity logged event (can be called from PHP when activity is logged)
 *
 * @param array $activity Activity entry
 * @return void
 */
function wpshadow_emit_activity_logged_event( array $activity ): void {
	?>
	<script>
	(function() {
		const event = new CustomEvent('wpshadow_activity_logged', {
			detail: <?php echo wp_json_encode( $activity ); ?>
		});
		document.dispatchEvent(event);
	})();
	</script>
	<?php
}

/**
 * Localization data for activity display
 */
function wpshadow_activity_display_localization(): void {
	if ( is_admin() && function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( ! $screen || strpos( (string) $screen->id, 'wpshadow' ) === false ) {
			return;
		}
	}

	if ( ! wp_script_is( 'wpshadow-page-activities', 'enqueued' ) ) {
		return;
	}

	wp_localize_script( 'wpshadow-page-activities', 'wpshadow_i18n', array(
		'no_activities' => __( 'No activities yet', 'wpshadow' ),
		'loading'       => __( 'Loading activity...', 'wpshadow' ),
		'report_label'  => __( 'Report', 'wpshadow' ),
		'page_label'    => __( 'Page', 'wpshadow' ),
		'of_label'      => __( 'of', 'wpshadow' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'wpshadow_activity_display_localization' );
add_action( 'admin_enqueue_scripts', 'wpshadow_activity_display_localization' );
