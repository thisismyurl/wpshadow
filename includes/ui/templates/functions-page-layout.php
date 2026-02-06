<?php
/**
 * Page Header/Footer Helper Functions
 *
 * Provides convenient functions for rendering consistent page headers and footers.
 *
 * @package    WPShadow
 * @subpackage Views
 * @since      1.6030.211827
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WPSHADOW_PATH . 'includes/ui/templates/functions-card.php';

/**
 * Render page header with title, subtitle, and version tag.
 *
 * @since  1.6030.211827
 * @param  string $title       Page title (required)
 * @param  string $subtitle    Page subtitle/description (optional)
 * @param  string $icon_class  Dashicons CSS class (optional, e.g., 'dashicons-admin-settings')
 * @param  string $icon_color  Icon color value (optional, default: var(--wps-primary))
 * @return void
 *
 * @example
 * wpshadow_render_page_header(
 *     'Settings',
 *     'Configure plugin settings',
 *     'dashicons-admin-settings',
 *     'var(--wps-primary)'
 * );
 */
function wpshadow_render_page_header( $title = '', $subtitle = '', $icon_class = '', $icon_color = 'var(--wps-primary)' ) {
	// Load the header template (variables are available as local scope)
	include WPSHADOW_PATH . 'includes/ui/templates/page-header.php';
}

/**
 * Render page footer (closing container).
 *
 * @since  1.6030.211827
 * @return void
 */
function wpshadow_render_page_footer() {
	include WPSHADOW_PATH . 'includes/ui/templates/page-footer.php';
}

/**
 * Load and render page-specific activities component
 *
	 * Includes the page-activities component file which provides functions for
	 * rendering real-time activity displays with AJAX auto-refresh.
	 *
	 * @since  1.6030.2148
	 * @return void
	 */
function wpshadow_load_page_activities_component() {
	// First, attempt to move the file from temp location if needed
	if ( file_exists( WPSHADOW_PATH . 'includes/views/move-activities-component.php' ) ) {
		require_once WPSHADOW_PATH . 'includes/views/move-activities-component.php';
		return; // The move script will handle loading the component
	}

	// Fallback: Load directly if file exists
	if ( ! function_exists( 'wpshadow_render_page_activities' ) ) {
		$component_file = WPSHADOW_PATH . 'includes/views/components/page-activities.php';
		if ( file_exists( $component_file ) ) {
			require_once $component_file;
		}
	}
}


// Load page activities component on init
add_action( 'wp_loaded', 'wpshadow_load_page_activities_component' );

/**
 * Render a context-specific activity log section.
 *
 * Displays a card with recent activities filtered by the specified context.
 * Each page/section can show activities relevant to its purpose.
 *
 * @since  1.6032.1848
 * @param  string $context The context identifier for filtering activities.
 *                         Valid values: 'training', 'achievements', 'settings',
 *                         'guardian', 'workflow', 'diagnostics', 'general'.
 * @param  int    $limit   Optional. Number of activities to display. Default 10.
 * @return void
 */
function wpshadow_render_activity_log( $context = 'general', $limit = 10 ) {
	// Context-specific configurations
	$context_config = array(
		'training'     => array(
			'title'       => __( 'Training Activity Log', 'wpshadow' ),
			'description' => __( 'Your recent training activities and course progress.', 'wpshadow' ),
			'filters'     => array( 'category' => 'academy' ),
			'icon'        => 'dashicons-video-alt2',
		),
		'achievements' => array(
			'title'       => __( 'Achievements Activity Log', 'wpshadow' ),
			'description' => __( 'Your recent achievements and gamification activities.', 'wpshadow' ),
			'filters'     => array( 'category' => 'gamification' ),
			'icon'        => 'dashicons-awards',
		),
		'settings'     => array(
			'title'       => __( 'Settings Activity Log', 'wpshadow' ),
			'description' => __( 'Recent configuration changes and settings updates.', 'wpshadow' ),
			'filters'     => array( 'category' => 'settings' ),
			'icon'        => 'dashicons-admin-settings',
		),
		'guardian'     => array(
			'title'       => __( 'Guardian Activity Log', 'wpshadow' ),
			'description' => __( 'Recent security scans and protection activities.', 'wpshadow' ),
			'filters'     => array( 'category' => 'security' ),
			'icon'        => 'dashicons-shield',
		),
		'workflow'     => array(
			'title'       => __( 'Workflow Activity Log', 'wpshadow' ),
			'description' => __( 'Recent workflow and automation activities.', 'wpshadow' ),
			'filters'     => array( 'category' => 'workflow' ),
			'icon'        => 'dashicons-update',
		),
		'diagnostics'  => array(
			'title'       => __( 'Diagnostics Activity Log', 'wpshadow' ),
			'description' => __( 'Recent diagnostic scans and treatment applications.', 'wpshadow' ),
			'filters'     => array(
				'category' => array( 'diagnostics', 'treatments' ),
			),
			'icon'        => 'dashicons-search',
		),
		'general'      => array(
			'title'       => __( 'Recent Activity', 'wpshadow' ),
			'description' => __( 'Your latest WPShadow actions and events.', 'wpshadow' ),
			'filters'     => array(),
			'icon'        => 'dashicons-clock',
		),
	);

	// Get context configuration
	$config = isset( $context_config[ $context ] ) ? $context_config[ $context ] : $context_config['general'];

	// Apply filters to allow customization
	$config = apply_filters( 'wpshadow_activity_log_config', $config, $context );

	// Get activities
	$activities_result = \WPShadow\Core\Activity_Logger::get_activities( $config['filters'], $limit, 0 );

	// If no activities, don't render anything
	if ( empty( $activities_result['activities'] ) ) {
		return;
	}

	// Build URL with context filter if not general
	$view_all_url = admin_url( 'admin.php?page=wpshadow-reports&tab=activity' );
	if ( 'general' !== $context && ! empty( $config['filters'] ) ) {
		$view_all_url = add_query_arg(
			array(
				'context' => $context,
			),
			$view_all_url
		);
	}

	?>
	<div class="wps-card wps-mt-8">
		<div class="wps-card-header">
			<div class="wps-flex wps-items-center wps-justify-between">
				<div>
					<h2 class="wps-card-title wps-m-0">
						<span class="dashicons <?php echo esc_attr( $config['icon'] ); ?>"></span>
						<?php echo esc_html( $config['title'] ); ?>
					</h2>
					<p class="wps-card-description wps-m-0">
						<?php echo esc_html( $config['description'] ); ?>
					</p>
				</div>
				<a href="<?php echo esc_url( $view_all_url ); ?>" class="wps-btn wps-btn--secondary wps-btn--small">
					<?php esc_html_e( 'View All', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
		<div class="wps-card-body">
			<div class="wps-activity-list">
				<?php foreach ( $activities_result['activities'] as $activity ) : ?>
					<div class="wps-activity-item wps-flex wps-gap-3 wps-py-3 wps-border-bottom">
						<div class="wps-activity-icon wps-flex-shrink-0">
							<?php
							// Icon based on action type
							$icon_class = 'dashicons-admin-generic';
							if ( strpos( $activity['action'], 'diagnostic' ) !== false ) {
								$icon_class = 'dashicons-search';
							} elseif ( strpos( $activity['action'], 'treatment' ) !== false ) {
								$icon_class = 'dashicons-admin-tools';
							} elseif ( strpos( $activity['action'], 'workflow' ) !== false ) {
								$icon_class = 'dashicons-update';
							} elseif ( strpos( $activity['action'], 'scan' ) !== false ) {
								$icon_class = 'dashicons-shield';
							} elseif ( strpos( $activity['action'], 'training' ) !== false || strpos( $activity['action'], 'academy' ) !== false ) {
								$icon_class = 'dashicons-video-alt2';
							} elseif ( strpos( $activity['action'], 'achievement' ) !== false || strpos( $activity['action'], 'gamification' ) !== false ) {
								$icon_class = 'dashicons-awards';
							}
							?>
							<span class="dashicons <?php echo esc_attr( $icon_class ); ?> wps-text-2xl wps-text-muted"></span>
						</div>
						<div class="wps-flex-1">
							<div class="wps-font-medium wps-text-sm">
								<?php echo esc_html( $activity['details'] ); ?>
							</div>
							<?php if ( ! empty( $activity['category'] ) ) : ?>
								<div class="wps-text-xs wps-text-muted wps-mt-1">
									<span class="wps-badge wps-badge--<?php echo esc_attr( $activity['category'] ); ?>">
										<?php echo esc_html( ucfirst( $activity['category'] ) ); ?>
									</span>
								</div>
							<?php endif; ?>
						</div>
						<div class="wps-activity-meta wps-text-xs wps-text-muted wps-text-right wps-flex-shrink-0">
							<div><?php echo esc_html( $activity['user_name'] ); ?></div>
							<div title="<?php echo esc_attr( $activity['date'] ); ?>">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: Human-readable time difference */
										__( '%s ago', 'wpshadow' ),
										human_time_diff( $activity['timestamp'], current_time( 'timestamp' ) )
									)
								);
								?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}