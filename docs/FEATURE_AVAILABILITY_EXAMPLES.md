<?php
/**
 * EXAMPLE: Guardian Dashboard KPI Cards with Version Checking
 *
 * This file shows how to refactor an existing feature to use Version_Checker
 * for conditional rendering of KPI cards.
 *
 * Location: includes/admin/class-guardian-dashboard.php
 *
 * @package WPShadow
 * @subpackage Examples
 */

// BEFORE (Current Implementation - no version checking)
// ===================================================
private static function render_kpi_cards_old(): string {
	$ob = ob_get_clean();
	?>
	<!-- KPI Cards Grid -->
	<div class="wps-grid wps-grid-auto-250 wps-gap-3 wps-mb-4">
		<!-- Issues Found Card -->
		<div class="wps-card">
			<h3><?php echo esc_html__( 'Issues Found', 'wpshadow' ); ?></h3>
			<!-- Card content -->
		</div>

		<!-- Time Saved Card -->
		<div class="wps-card">
			<h3><?php echo esc_html__( 'Time Saved', 'wpshadow' ); ?></h3>
			<!-- Card content -->
		</div>

		<!-- Business Value Card (Future Feature) -->
		<div class="wps-card">
			<h3><?php echo esc_html__( 'Business Value', 'wpshadow' ); ?></h3>
			<!-- Card content -->
		</div>
	</div>
	<?php
	return ob_get_clean();
}

// AFTER (With Version Checking)
// ==============================

/**
 * Render KPI cards - only shows available features
 *
 * Example of using Version_Checker to conditionally display cards
 * based on feature availability.
 *
 * @since  1.6035.2150
 * @return string HTML output
 */
private static function render_kpi_cards(): string {
	ob_start();
	?>
	<!-- KPI Cards Grid -->
	<div class="wps-grid wps-grid-auto-250 wps-gap-3 wps-mb-4" role="region" aria-label="<?php esc_attr_e( 'Key performance indicators', 'wpshadow' ); ?>">
		<?php
		// Issues Found - always available (core feature)
		wpshadow_render_feature_card_if_live(
			'WPShadow\Admin\Guardian_Dashboard',
			__( 'Issues Found', 'wpshadow' ),
			__( 'Total security and performance issues detected', 'wpshadow' ),
			'dashicons-warning'
		);

		// Time Saved - check if KPI_Tracker is live
		wpshadow_render_feature_card_if_live(
			'WPShadow\Core\KPI_Tracker',
			__( 'Time Saved', 'wpshadow' ),
			__( 'Estimated time saved by automated fixes', 'wpshadow' ),
			'dashicons-clock'
		);

		// Business Value - check if Business_Value_Calculator is live
		wpshadow_render_feature_card_if_live(
			'WPShadow\Reporting\Business_Value_Calculator',
			__( 'Business Value', 'wpshadow' ),
			__( 'Estimated business impact of improvements', 'wpshadow' ),
			'dashicons-chart-line'
		);

		// Advanced Metrics - future feature, shows "coming soon"
		if ( ! \WPShadow\Core\Version_Checker::is_feature_live( 'WPShadow\Reporting\Advanced_Metrics' ) ) {
			wpshadow_render_coming_soon_card(
				__( 'Advanced Metrics', 'wpshadow' ),
				'WPShadow\Reporting\Advanced_Metrics'
			);
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * INTEGRATION EXAMPLE: Conditional Menu Rendering
 *
 * Shows how to refactor menu registration to use Version_Checker
 */

// In includes/systems/core/class-menu-manager.php:

public static function register_menus() {
	// Top-level menu (always available)
	add_menu_page(
		'WPShadow',
		'WPShadow',
		'read',
		'wpshadow',
		'wpshadow_render_dashboard',
		'dashicons-shield-alt',
		999
	);

	// Standard menus
	self::register_standard_menus();

	// Advanced menus - only if available
	if ( \WPShadow\Core\Version_Checker::is_feature_live( 'WPShadow\Admin\Advanced_Analytics_Page' ) ) {
		add_submenu_page(
			'wpshadow',
			__( 'Advanced Analytics', 'wpshadow' ),
			__( 'Advanced Analytics', 'wpshadow' ),
			'manage_options',
			'wpshadow-advanced-analytics',
			'wpshadow_render_advanced_analytics'
		);
	}

	// Future features - show placeholder if enabled in settings
	if ( get_option( 'wpshadow_show_coming_soon_menu', false ) ) {
		// Only show if not already live
		if ( ! \WPShadow\Core\Version_Checker::is_feature_live( 'WPShadow\Admin\AI_Optimization_Page' ) ) {
			add_submenu_page(
				'wpshadow',
				__( 'AI Optimization', 'wpshadow' ) . ' (Coming Soon)',
				__( 'AI Optimization', 'wpshadow' ) . ' (Coming Soon)',
				'manage_options',
				'wpshadow-ai-optimization-coming-soon',
				array( __CLASS__, 'render_coming_soon_page' )
			);
		}
	}
}

/**
 * FILTERING EXAMPLE: Collect Only Available Treatments
 *
 * Shows how to filter treatment/diagnostic lists to exclude coming-soon features
 */

public static function get_available_treatments(): array {
	// Get all treatments
	$all_treatments = [
		'WPShadow\Treatments\Treatment_CSRF_Protection',
		'WPShadow\Treatments\Treatment_SSL_Redirect',
		'WPShadow\Treatments\Treatment_Database_Optimization',
		'WPShadow\Treatments\Treatment_Advanced_Backup',  // v1.6050.1000 - not yet live
		'WPShadow\Treatments\Treatment_ML_Analysis',      // v1.6100.0000 - not yet live
	];

	// Filter to only available ones
	$available = \WPShadow\Core\Version_Checker::filter_live_features( $all_treatments );

	// Instantiate available treatments
	$treatments = [];
	foreach ( $available as $class_name ) {
		if ( class_exists( $class_name ) ) {
			$treatments[] = new $class_name();
		}
	}

	return $treatments;
}

/**
 * SETTINGS PAGE EXAMPLE: Dynamic Feature Settings
 *
 * Shows how to render only available settings
 */

public static function render_advanced_settings_tab() {
	?>
	<div class="wps-settings-section">
		<?php
		// Database Optimization Settings - always available
		if ( \WPShadow\Core\Version_Checker::is_feature_live( 'WPShadow\Settings\Database_Optimization' ) ) {
			?>
			<div class="wps-setting-group">
				<h4><?php esc_html_e( 'Database Optimization', 'wpshadow' ); ?></h4>
				<!-- Settings UI -->
			</div>
			<?php
		}

		// Cache Management Settings - check availability
		wpshadow_render_feature_card_if_live(
			'WPShadow\Settings\CacheManagement',
			__( 'Cache Management', 'wpshadow' ),
			__( 'Configure advanced caching options', 'wpshadow' ),
			'dashicons-performance'
		);

		// API Rate Limiting - check availability
		if ( ! \WPShadow\Core\Version_Checker::is_feature_live( 'WPShadow\Settings\APIRateLimiting' ) ) {
			?>
			<div class="wps-setting-group wps-opacity-50">
				<h4><?php esc_html_e( 'API Rate Limiting', 'wpshadow' ); ?></h4>
				<p style="color: #999;">
					<?php
					printf(
						/* translators: %s: version number */
						esc_html__( 'Coming in v%s', 'wpshadow' ),
						esc_html( \WPShadow\Core\Version_Checker::get_feature_since( 'WPShadow\Settings\APIRateLimiting' ) )
					);
					?>
				</p>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * ACADEMY/LEARNING PAGE EXAMPLE: Course Availability
 */

public static function render_courses(): void {
	$courses = [
		'WPShadow\Academy\Course\Security',
		'WPShadow\Academy\Course\Performance',
		'WPShadow\Academy\Course\WordPress_Core',
		'WPShadow\Academy\Course\Advanced_Optimization',  // v1.6050.0000
		'WPShadow\Academy\Course\AI_Training',            // v1.6100.0000
	];

	$available_courses = \WPShadow\Core\Version_Checker::filter_live_features( $courses );

	?>
	<div class="wps-grid wps-grid-auto-320">
		<?php
		foreach ( $available_courses as $course_class ) {
			// Render available course
		}

		// Show "coming soon" for unavailable courses
		foreach ( $courses as $course_class ) {
			if ( ! in_array( $course_class, $available_courses, true ) ) {
				wpshadow_render_coming_soon_card(
					$this->get_course_title( $course_class ),
					$course_class
				);
			}
		}
		?>
	</div>
	<?php
}

/**
 * BEST PRACTICES CHECKLIST
 *
 * When refactoring existing pages:
 *
 * ✅ Add @since tags to all new classes
 * ✅ Wrap feature display in version checks
 * ✅ Show "coming soon" placeholders for unreleased features
 * ✅ Cache version checks internally
 * ✅ Test with different WPSHADOW_VERSION values
 * ✅ Document which features are version-gated
 * ✅ Update FEATURE_AVAILABILITY_GUIDE.md when adding new features
 */
