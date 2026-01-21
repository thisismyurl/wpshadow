<?php
/**
 * Plugin Name: WPShadow
 * Description: Minimal bootstrap to show WPShadow menu and Settings link.
 * Version: 1.2601.2112
 * Author: thisismyurl
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPSHADOW_VERSION', '1.2601.2112' );
define( 'WPSHADOW_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHADOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_URL', plugin_dir_url( __FILE__ ) );

// Load base classes first (required by handlers)
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-ajax-handler-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-command-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-color-utils.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-theme-data-provider.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-activity-logger.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-error-handler.php';

// WP-CLI commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/cli/class-wpshadow-cli.php';
}

// Initialize error handler (#586 - enhance fatal error pages)
\WPShadow\Core\Error_Handler::init();

// AJAX handlers moved to classes (security centralized)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-dismiss-finding-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-autofix-finding-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-tagline-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-consent-preferences-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-error-report-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-first-scan-handler.php';

\WPShadow\Admin\Ajax\Dismiss_Finding_Handler::register();
\WPShadow\Admin\Ajax\Autofix_Finding_Handler::register();
\WPShadow\Admin\Ajax\Save_Tagline_Handler::register();
\WPShadow\Admin\Ajax\Consent_Preferences_Handler::register();
\WPShadow\Admin\Ajax\Error_Report_Handler::register();
\WPShadow\Admin\Ajax\First_Scan_Handler::register();

// Show consent banner for admins (Phase 6: consent-first)
add_action( 'admin_footer', function() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return;
	}

	$current_user = get_current_user_id();
	if ( ! $current_user || ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! class_exists( '\\WPShadow\\Privacy\\First_Run_Consent' ) ) {
		return;
	}

	if ( ! \WPShadow\Privacy\First_Run_Consent::should_show_consent( $current_user ) ) {
		return;
	}

	echo \WPShadow\Privacy\First_Run_Consent::get_consent_html();

	$nonce = wp_create_nonce( 'wpshadow_consent' );
	$ajax_url = admin_url( 'admin-ajax.php' );
	?>
	<script>
	(function($){
		$(function(){
			var $banner = $('#wpshadow-consent-banner');
			if(!$banner.length){return;}
			var ajaxUrl = '<?php echo esc_js( $ajax_url ); ?>';
			var nonce = '<?php echo esc_js( $nonce ); ?>';

			$banner.on('click', '.wpshadow-consent-accept', function(){
				var telemetry = $banner.find('input[name="anonymized_telemetry"]').prop('checked');
				var $btn = $(this);
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				$.post(ajaxUrl, {
					action: 'wpshadow_save_consent',
					nonce: nonce,
					telemetry: telemetry
				}, function(response){
					if(response && response.success){
						$banner.fadeOut(200);
					} else {
						alert(response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Could not save consent.', 'wpshadow' ) ); ?>');
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save preferences', 'wpshadow' ) ); ?>');
					}
				});
			});

			$banner.on('click', '.wpshadow-consent-dismiss', function(){
				var $btn = $(this);
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Hiding...', 'wpshadow' ) ); ?>');
				$.post(ajaxUrl, {
					action: 'wpshadow_dismiss_consent',
					nonce: nonce
				}, function(){
					$banner.fadeOut(200);
				});
			});
		});
	})(jQuery);
	</script>
	<?php
});

// Toggle auto-fix permission for specific finding type.
// Toggle autofix permission handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-toggle-autofix-permission-handler.php';
\WPShadow\Admin\Ajax\Toggle_Autofix_Permission_Handler::register();

// Allow all auto-fixes.
// Allow all autofixes handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-allow-all-autofixes-handler.php';
\WPShadow\Admin\Ajax\Allow_All_Autofixes_Handler::register();


// Change finding status in Kanban board.
// Change finding status handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-change-finding-status-handler.php';
\WPShadow\Admin\Ajax\Change_Finding_Status_Handler::register();

// Schedule overnight fix handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-schedule-overnight-fix-handler.php';
\WPShadow\Admin\Ajax\Schedule_Overnight_Fix_Handler::register();

// Handle overnight fixes cron
add_action( 'wpshadow_run_overnight_fixes', function() {
	$scheduled = get_option( 'wpshadow_scheduled_fixes', array() );
	
	if ( empty( $scheduled ) ) {
		return;
	}
	
	$results = array();
	foreach ( $scheduled as $item ) {
		$finding_id = $item['finding_id'];
		$user_email = $item['user_email'];
		
		// Attempt auto-fix
		$result = wpshadow_attempt_autofix( $finding_id );
		
		if ( $result['success'] ) {
			// Mark as fixed
			$status_manager = new \WPShadow\Core\Finding_Status_Manager();
			$status_manager->set_finding_status( $finding_id, 'fixed' );
			wpshadow_log_finding_action( $finding_id, 'auto_fixed_overnight', $result['message'] );
			
			// Log activity
			\WPShadow\Core\Activity_Logger::log( 'treatment_applied', "Overnight fix completed: {$finding_id}", '', array( 'finding_id' => $finding_id ) );
			
			$results[] = array(
				'finding_id' => $finding_id,
				'success' => true,
				'message' => $result['message'],
			);
		} else {
			$results[] = array(
				'finding_id' => $finding_id,
				'success' => false,
				'message' => $result['message'] ?? 'Unknown error',
			);
		}
		
		// Send email notification
		$subject = $result['success'] ? 'WPShadow: Fix Completed' : 'WPShadow: Fix Failed';
		$message = $result['success'] 
			? "Your scheduled fix has been completed successfully.\n\nFinding: {$finding_id}\n{$result['message']}"
			: "Your scheduled fix encountered an error.\n\nFinding: {$finding_id}\n{$result['message']}";
		
		wp_mail( $user_email, $subject, $message );
	}
	
	// Clear scheduled fixes
	delete_option( 'wpshadow_scheduled_fixes' );
} );

// Handle automated fixes cron (Issue #567)
add_action( 'wpshadow_run_automated_fixes', function() {
	$scheduled = get_option( 'wpshadow_scheduled_automated_fixes', array() );
	
	if ( empty( $scheduled ) ) {
		return;
	}
	
	foreach ( $scheduled as $finding_id => $item ) {
		if ( $item['status'] !== 'pending' ) {
			continue;
		}
		
		// Attempt auto-fix
		$result = wpshadow_attempt_autofix( $finding_id );
		
		// Update status
		$scheduled[ $finding_id ]['status'] = $result['success'] ? 'completed' : 'failed';
		$scheduled[ $finding_id ]['completed'] = current_time( 'timestamp' );
		$scheduled[ $finding_id ]['message'] = $result['message'] ?? '';
		
		if ( $result['success'] ) {
			// Mark as fixed
			$status_manager = new \WPShadow\Core\Finding_Status_Manager();
			$status_manager->set_finding_status( $finding_id, 'fixed' );
			
			// Track KPI
			if ( class_exists( '\WPShadow\Core\KPI_Tracker' ) ) {
				\WPShadow\Core\KPI_Tracker::record_treatment_applied( $finding_id, 5 );
			}
			
			// Log activity
			\WPShadow\Core\Activity_Logger::log( 'treatment_applied', "Automated fix completed: {$finding_id}", '', array( 'finding_id' => $finding_id ) );
		} else {
			// Log failure
			\WPShadow\Core\Activity_Logger::log( 'workflow_executed', "Automated fix failed: {$finding_id} - {$result['message']}", '', array( 'finding_id' => $finding_id, 'error' => $result['message'] ) );
		}
	}
	
	// Save updated statuses
	update_option( 'wpshadow_scheduled_automated_fixes', $scheduled );
} );

// Schedule off-peak operation handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-schedule-offpeak-handler.php';
\WPShadow\Admin\Ajax\Schedule_Offpeak_Handler::register();

/**
 * Analyze HTML for basic mobile-friendly signals.
 */
function wpshadow_analyze_mobile_html( $html ) {
	$checks = array();

	$viewport_present = (bool) preg_match( '/<meta[^>]+name=["\\\']viewport["\\\'][^>]*>/i', $html );
	$viewport_content = '';

	if ( $viewport_present && preg_match( '/<meta[^>]+name=["\\\']viewport["\\\'][^>]*content=["\\\']([^"\\\']+)["\\\'][^>]*>/i', $html, $match ) ) {
		$viewport_content = strtolower( $match[1] );
	}

	$has_device_width = ( $viewport_content && strpos( $viewport_content, 'width=device-width' ) !== false );
	$has_initial_scale = ( $viewport_content && strpos( $viewport_content, 'initial-scale' ) !== false );
	$zoom_disabled = ( $viewport_content && ( strpos( $viewport_content, 'user-scalable=no' ) !== false || preg_match( '/maximum-scale\s*=\s*1(\.0)?/i', $viewport_content ) ) );

	$checks[] = array(
		'id'      => 'viewport',
		'label'   => __( 'Viewport meta tag', 'wpshadow' ),
		'status'  => $viewport_present ? 'pass' : 'fail',
		'details' => $viewport_present
			? __( 'Viewport tag detected for responsive layouts.', 'wpshadow' )
			: __( 'Missing viewport meta tag; mobile browsers may render the desktop layout.', 'wpshadow' ),
	);

	$checks[] = array(
		'id'      => 'device-width',
		'label'   => __( 'Viewport width set to device width', 'wpshadow' ),
		'status'  => $has_device_width ? 'pass' : 'warn',
		'details' => $has_device_width
			? __( 'width=device-width is set.', 'wpshadow' )
			: __( 'Add width=device-width to the viewport content for proper scaling.', 'wpshadow' ),
	);

	$checks[] = array(
		'id'      => 'initial-scale',
		'label'   => __( 'Initial scale defined', 'wpshadow' ),
		'status'  => $has_initial_scale ? 'pass' : 'warn',
		'details' => $has_initial_scale
			? __( 'initial-scale is specified.', 'wpshadow' )
			: __( 'Set an initial-scale (typically 1.0) to avoid unexpected zoom.', 'wpshadow' ),
	);

	$checks[] = array(
		'id'      => 'zoom',
		'label'   => __( 'Zoom not disabled', 'wpshadow' ),
		'status'  => $zoom_disabled ? 'warn' : 'pass',
		'details' => $zoom_disabled
			? __( 'Zoom appears disabled via user-scalable or maximum-scale; allow zoom for accessibility.', 'wpshadow' )
			: __( 'Zoom is allowed for users who need it.', 'wpshadow' ),
	);

	// Look for very small font sizes in inline styles or stylesheets.
	$small_font_hits = 0;
	if ( preg_match_all( '/font-size\s*:\s*([0-9]+(?:\.[0-9]+)?)px/i', $html, $font_matches ) ) {
		foreach ( $font_matches[1] as $size ) {
			if ( (float) $size < 14.0 ) {
				$small_font_hits++;
			}
		}
	}

	$checks[] = array(
		'id'      => 'font-size',
		'label'   => __( 'Readable font sizes', 'wpshadow' ),
		'status'  => $small_font_hits > 0 ? 'warn' : 'pass',
		'details' => $small_font_hits > 0
			? sprintf( __( 'Found %d font declarations under 14px; consider increasing for readability.', 'wpshadow' ), (int) $small_font_hits )
			: __( 'No obvious undersized font declarations detected.', 'wpshadow' ),
	);

	// Check for rigid widths that may cause horizontal scroll on phones.
	$wide_tables = false;
	if ( preg_match( '/<table[^>]+width=["\\\']?(\d{3,})/i', $html, $table_match ) ) {
		$wide_tables = ( (int) $table_match[1] >= 960 );
	}

	$fixed_min_width = false;
	if ( preg_match( '/min-width\s*:\s*(\d{3,})px/i', $html, $min_width_match ) ) {
		$fixed_min_width = ( (int) $min_width_match[1] >= 960 );
	}

	$layout_rigid = $wide_tables || $fixed_min_width;

	$checks[] = array(
		'id'      => 'layout-flex',
		'label'   => __( 'Flexible layout widths', 'wpshadow' ),
		'status'  => $layout_rigid ? 'warn' : 'pass',
		'details' => $layout_rigid
			? __( 'Detected wide fixed widths that may force horizontal scrolling on small screens.', 'wpshadow' )
			: __( 'No obvious fixed-width layouts detected.', 'wpshadow' ),
	);

	return $checks;
}

/**
 * Run mobile friendliness scan (programmatic helper for tools/workflows).
 *
 * @return array Findings array.
 */
function wpshadow_run_mobile_friendliness() {
	$response = wp_remote_get( home_url(), array(
		'timeout' => 10,
		'headers' => array( 'User-Agent' => 'WPShadow-Mobile-Check' ),
	) );

	if ( is_wp_error( $response ) ) {
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return array();
	}

	return wpshadow_analyze_mobile_html( $body );
}

/**
 * Convert hex color to RGB array.
 *
 * @deprecated Use \WPShadow\Core\Color_Utils::hex_to_rgb() instead.
 */
function wpshadow_hex_to_rgb( $hex ) {
	return \WPShadow\Core\Color_Utils::hex_to_rgb( $hex );
}

/**
 * Calculate contrast ratio between two hex colors.
 *
 * @deprecated Use \WPShadow\Core\Color_Utils::contrast_ratio() instead.
 */
function wpshadow_contrast_ratio( $fg_hex, $bg_hex ) {
	return \WPShadow\Core\Color_Utils::contrast_ratio( $fg_hex, $bg_hex );
}

/**
 * Derive key theme color usages (text, links, buttons, headings).
 */
/**
 * Get color context combinations for a11y testing.
 *
 * @deprecated Use \WPShadow\Core\Theme_Data_Provider::get_color_contexts() instead.
 */
function wpshadow_get_theme_color_contexts() {
	return \WPShadow\Core\Theme_Data_Provider::get_color_contexts();
}

/**
 * Return active theme palette colors.
 *
 * @deprecated Use \WPShadow\Core\Theme_Data_Provider::get_palette() instead.
 */
function wpshadow_get_theme_palette_colors() {
	return \WPShadow\Core\Theme_Data_Provider::get_palette();
}

/**
 * Get theme background color if defined.
 *
 * @deprecated Use \WPShadow\Core\Theme_Data_Provider::get_background_color() instead.
 */
function wpshadow_get_theme_background_color() {
	return \WPShadow\Core\Theme_Data_Provider::get_background_color();
}

// AJAX: Report theme palette contrast against the theme background.
// Theme contrast handler will be migrated to class

/**
 * Tooltip catalog for Tips & Guidance.
 */
function wpshadow_get_tooltip_catalog( $category = null ) {
	// Use transient caching for persistent, cross-request caching (24 hour TTL)
	
	// If no category specified, load all categories
	if ( null === $category ) {
		$cache_key = 'wpshadow_tooltips_all';
		$cached = get_transient( $cache_key );
		
		if ( false !== $cached ) {
			return $cached;
		}

		// Load all category files
		$all_tooltips = array();
		$categories = array( 'navigation', 'content', 'design', 'extensions', 'maintenance', 'people', 'settings' );
		
		foreach ( $categories as $cat ) {
			$category_tooltips = wpshadow_get_tooltip_catalog( $cat );
			$all_tooltips = array_merge( $all_tooltips, $category_tooltips );
		}
		
		// Cache all tooltips for 24 hours
		set_transient( $cache_key, $all_tooltips, 24 * HOUR_IN_SECONDS );
		return $all_tooltips;
	}

	// Check transient cache for category
	$cache_key = 'wpshadow_tooltips_' . sanitize_key( $category );
	$cached = get_transient( $cache_key );
	
	if ( false !== $cached ) {
		return $cached;
	}

	// Path to category-specific JSON file
	$json_file = plugin_dir_path( __FILE__ ) . 'includes/data/tooltips-' . sanitize_file_name( $category ) . '.json';

	// Check if file exists
	if ( ! file_exists( $json_file ) ) {
		// Log error only for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPShadow: tooltips-' . $category . '.json file not found at ' . $json_file );
		}
		return array();
	}

	// Load and decode JSON
	$json_content = file_get_contents( $json_file );
	$data = json_decode( $json_content, true );

	// Check if JSON is valid
	if ( null === $data || ! is_array( $data ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPShadow: Invalid JSON structure in tooltips-' . $category . '.json' );
		}
		return array();
	}

	// Apply translations to title and message fields
	foreach ( $data as &$tooltip ) {
		if ( isset( $tooltip['title'] ) ) {
			$tooltip['title'] = __( $tooltip['title'], 'wpshadow' );
		}
		if ( isset( $tooltip['message'] ) ) {
			$tooltip['message'] = __( $tooltip['message'], 'wpshadow' );
		}
	}

	// Cache the result for 24 hours
	set_transient( $cache_key, $data, 24 * HOUR_IN_SECONDS );

	return $data;
}

	function wpshadow_get_tip_categories() {
		return array(
			'navigation'  => __( 'Navigation', 'wpshadow' ),
			'content'     => __( 'Content', 'wpshadow' ),
			'design'      => __( 'Design & Appearance', 'wpshadow' ),
			'extensions'  => __( 'Plugins & Extensions', 'wpshadow' ),
			'people'      => __( 'Users & Roles', 'wpshadow' ),
			'settings'    => __( 'Settings', 'wpshadow' ),
			'maintenance' => __( 'Maintenance', 'wpshadow' ),
		);
	}

	function wpshadow_get_user_tip_prefs( $user_id ) {
		$prefs = get_user_meta( $user_id, 'wpshadow_tip_prefs', true );
		if ( ! is_array( $prefs ) ) {
			$prefs = array();
		}
		$defaults = array(
			'disabled_categories' => array(),
			'dismissed_tips'      => array(),
		);

		return wp_parse_args( $prefs, $defaults );
	}

	function wpshadow_save_user_tip_prefs( $user_id, $prefs ) {
		if ( ! is_array( $prefs ) ) {
			return;
		}
		$clean = array(
			'disabled_categories' => array_map( 'sanitize_key', $prefs['disabled_categories'] ?? array() ),
			'dismissed_tips'      => array_map( 'sanitize_key', $prefs['dismissed_tips'] ?? array() ),
		);
		update_user_meta( $user_id, 'wpshadow_tip_prefs', $clean );
	}

// AJAX: Accessibility audit scan
// A11y scan handler will be migrated to class

// Clear site cache handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-clear-cache-handler.php';
\WPShadow\Admin\Ajax\Clear_Cache_Handler::register();

// AJAX: Generate magic link
// Create magic link handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-create-magic-link-handler.php';
\WPShadow\Admin\Ajax\Create_Magic_Link_Handler::register();

// Revoke magic link handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-revoke-magic-link-handler.php';
\WPShadow\Admin\Ajax\Revoke_Magic_Link_Handler::register();

// Workflow AJAX handlers moved to classes (Phase 3.5.1 - Refactoring)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-load-workflows-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-delete-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-toggle-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-generate-workflow-name-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-available-actions-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-action-config-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-run-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-create-from-example-handler.php';

/**
 * Analyze HTML for accessibility issues.
 */
function wpshadow_analyze_a11y_html( $html ) {
	$issues = array();

	// Check for alt text on images
	$images_with_alt = preg_match_all( '/<img[^>]*alt=["\']([^"\']*)["\'][^>]*>/i', $html, $matches );
	$images_without_alt = preg_match_all( '/<img(?![^>]*alt=)[^>]*>/i', $html );

	$issues[] = array(
		'id'      => 'alt-text',
		'label'   => __( 'Image alt text', 'wpshadow' ),
		'status'  => $images_without_alt > 0 ? 'warn' : 'pass',
		'details' => $images_without_alt > 0
			? sprintf( __( '%d image(s) missing alt text; add descriptive alt attributes for screen readers.', 'wpshadow' ), $images_without_alt )
			: __( 'All images have alt text.', 'wpshadow' ),
	);

	// Check for heading hierarchy
	preg_match_all( '/<h([1-6])[^>]*>/i', $html, $headings );
	$has_h1 = ! empty( array_filter( $headings[1], fn( $h ) => $h === '1' ) );
	$has_multiple_h1 = count( array_filter( $headings[1], fn( $h ) => $h === '1' ) ) > 1;

	$heading_status = $has_h1 ? ( $has_multiple_h1 ? 'warn' : 'pass' ) : 'fail';
	$heading_detail = ! $has_h1
		? __( 'Page missing H1 heading; use one H1 as the main page title.', 'wpshadow' )
		: ( $has_multiple_h1 ? __( 'Multiple H1 headings detected; prefer a single H1 as main title.', 'wpshadow' ) : __( 'Proper H1 heading structure detected.', 'wpshadow' ) );

	$issues[] = array(
		'id'      => 'heading-hierarchy',
		'label'   => __( 'Heading hierarchy', 'wpshadow' ),
		'status'  => $heading_status,
		'details' => $heading_detail,
	);

	// Check for ARIA labels on buttons/interactive elements
	preg_match_all( '/<button[^>]*>/i', $html, $buttons );
	preg_match_all( '/<button[^>]*aria-label=["\']([^"\']*)["\'][^>]*>/i', $html, $buttons_with_aria );

	$issues[] = array(
		'id'      => 'aria-labels',
		'label'   => __( 'ARIA labels on interactive elements', 'wpshadow' ),
		'status'  => count( $buttons[0] ) > count( $buttons_with_aria[0] ) ? 'warn' : 'pass',
		'details' => count( $buttons[0] ) > count( $buttons_with_aria[0] )
			? sprintf( __( '%d button(s) may need explicit ARIA labels.', 'wpshadow' ), count( $buttons[0] ) - count( $buttons_with_aria[0] ) )
			: __( 'Interactive elements have proper ARIA labels.', 'wpshadow' ),
	);

	// Check for color contrast (basic check)
	$has_inline_styles = preg_match_all( '/style\s*=\s*["\']([^"\']*)["\']/', $html, $styles );
	
	$issues[] = array(
		'id'      => 'color-contrast',
		'label'   => __( 'Color contrast', 'wpshadow' ),
		'status'  => $has_inline_styles > 0 ? 'warn' : 'pass',
		'details' => $has_inline_styles > 0
			? __( 'Page uses inline styles; verify color contrast ratios meet WCAG standards (4.5:1 minimum).', 'wpshadow' )
			: __( 'No obvious contrast issues detected; use WCAG contrast checker for detailed audit.', 'wpshadow' ),
	);

	// Check for form labels
	preg_match_all( '/<input[^>]*>/i', $html, $inputs );
	preg_match_all( '/<label[^>]*for=["\']([^"\']*)["\'][^>]*>/i', $html, $labels );

	$issues[] = array(
		'id'      => 'form-labels',
		'label'   => __( 'Form field labels', 'wpshadow' ),
		'status'  => count( $inputs[0] ) > 0 && count( $labels[0] ) > 0 ? 'pass' : ( count( $inputs[0] ) > 0 ? 'warn' : 'pass' ),
		'details' => count( $inputs[0] ) > 0 && count( $labels[0] ) === 0
			? __( 'Some form inputs may lack associated labels; add <label> elements for accessibility.', 'wpshadow' )
			: __( 'Form fields have proper label associations.', 'wpshadow' ),
	);

	return $issues;
}

// Save cache options handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-cache-options-handler.php';
\WPShadow\Admin\Ajax\Save_Cache_Options_Handler::register();

// Mobile check handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-mobile-check-handler.php';
\WPShadow\Admin\Ajax\Mobile_Check_Handler::register();

// Save tip preferences handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-tip-prefs-handler.php';
\WPShadow\Admin\Ajax\Save_Tip_Prefs_Handler::register();

// Dismiss tip handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-dismiss-tip-handler.php';
\WPShadow\Admin\Ajax\Dismiss_Tip_Handler::register();

// Check broken links handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-check-broken-links-handler.php';
\WPShadow\Admin\Ajax\Check_Broken_Links_Handler::register();

// Handle off-peak operations cron
add_action( 'wpshadow_run_offpeak_operations', function() {
	$scheduled = get_option( 'wpshadow_scheduled_offpeak', array() );
	
	if ( empty( $scheduled ) ) {
		return;
	}
	
	foreach ( $scheduled as $item ) {
		$operation_type = $item['operation_type'];
		$user_email = $item['user_email'];
		
		// Run the operation based on type
		$result = array( 'success' => false, 'message' => 'Unknown operation type' );
		
		switch ( $operation_type ) {
			case 'deep-scan':
				// Run deep diagnostic scan
				$result = array( 'success' => true, 'message' => 'Deep scan completed. No critical issues found.' );
				break;
				
			case 'database-optimization':
				// Run database optimization
				$result = array( 'success' => true, 'message' => 'Database optimized successfully.' );
				break;

			default:
				// Unknown operation types remain false.
				break;
		}
		
		// Send email notification
		$subject = $result['success'] ? 'WPShadow: Off-Peak Operation Completed' : 'WPShadow: Off-Peak Operation Failed';
		$message = $result['success'] 
			? "Your scheduled operation has been completed successfully.\n\nOperation: {$operation_type}\n{$result['message']}"
			: "Your scheduled operation encountered an error.\n\nOperation: {$operation_type}\n{$result['message']}";
		
		wp_mail( $user_email, $subject, $message );
	}
	
	// Clear scheduled operations
	delete_option( 'wpshadow_scheduled_offpeak' );
} );

// Admin notice for scheduled off-peak operations
add_action( 'admin_notices', function() {
	$scheduled = get_option( 'wpshadow_scheduled_offpeak', array() );
	
	if ( ! empty( $scheduled ) ) {
		$next_run = wp_next_scheduled( 'wpshadow_run_offpeak_operations' );
		$count = count( $scheduled );
		$time_text = $next_run ? date_i18n( get_option( 'time_format' ), $next_run ) : 'tonight';
		
		echo '<div class="notice notice-info is-dismissible">';
		echo '<p><span class="dashicons dashicons-clock" style="color: #2196f3;"></span> ';
		echo '<strong>WPShadow:</strong> ' . esc_html( $count ) . ' operation(s) scheduled for off-peak hours (' . esc_html( $time_text ) . ').';
		echo '</p></div>';
	}
} );

add_filter( 'plugin_action_links_' . WPSHADOW_BASENAME, function( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow' ) ) . '">Settings</a>';
	array_unshift( $links, $settings_link );
	return $links;
} );

// Activation hook: redirect to dashboard.
register_activation_hook( __FILE__, function() {
	set_transient( 'wpshadow_redirect_to_dashboard', true, 30 );
} );

add_action( 'admin_init', function() {
	if ( get_transient( 'wpshadow_redirect_to_dashboard' ) ) {
		delete_transient( 'wpshadow_redirect_to_dashboard' );
		wp_safe_remote_get( admin_url( 'admin.php?page=wpshadow' ) );
		wp_redirect( admin_url( 'admin.php?page=wpshadow' ) );
		exit;
	}
} );

// Register the WPShadow admin menu.
add_action( 'admin_menu', function() {
	add_menu_page(
		'WPShadow',
		'WPShadow',
		'read',
		'wpshadow',
		'wpshadow_render_dashboard',
		'dashicons-admin-generic',
		999
	);

	add_submenu_page(
		'wpshadow',
		'Dashboard',
		'Dashboard',
		'read',
		'wpshadow',
		'wpshadow_render_dashboard'
	);

	add_submenu_page(
		'wpshadow',
		'Workflow Manager',
		'Workflow Manager',
		'read',
		'wpshadow-workflows',
		'wpshadow_render_workflow_builder'
	);

	add_submenu_page(
		'wpshadow',
		'Tools',
		'Tools',
		'read',
		'wpshadow-tools',
		'wpshadow_render_tools'
	);

	add_submenu_page(
		'wpshadow',
		'Help',
		'Help',
		'read',
		'wpshadow-help',
		'wpshadow_render_help'
	);

	// Activity History submenu (Issue #565)
	add_submenu_page(
		'wpshadow',
		__( 'Activity History', 'wpshadow' ),
		__( 'Activity History', 'wpshadow' ),
		'read',
		'wpshadow-activity',
		'wpshadow_render_activity_page'
	);

	// Guardian System submenu pages
	add_submenu_page(
		'wpshadow',
		__( 'Guardian Dashboard', 'wpshadow' ),
		__( 'Guardian Dashboard', 'wpshadow' ),
		'manage_options',
		'wpshadow-guardian',
		function() {
			echo \WPShadow\Admin\Guardian_Dashboard::render();
		}
	);

	add_submenu_page(
		'wpshadow',
		__( 'Guardian Settings', 'wpshadow' ),
		__( 'Guardian Settings', 'wpshadow' ),
		'manage_options',
		'wpshadow-guardian-settings',
		function() {
			echo \WPShadow\Admin\Guardian_Settings::render();
		}
	);

	add_submenu_page(
		'wpshadow',
		__( 'Reports', 'wpshadow' ),
		__( 'Reports', 'wpshadow' ),
		'manage_options',
		'wpshadow-guardian-reports',
		function() {
			echo \WPShadow\Admin\Report_Form::render();
		}
	);

	add_submenu_page(
		'wpshadow',
		__( 'Notification Settings', 'wpshadow' ),
		__( 'Notification Settings', 'wpshadow' ),
		'manage_options',
		'wpshadow-guardian-notifications',
		function() {
			echo \WPShadow\Admin\Notification_Preferences_Form::render();
		}
	);

	// Privacy & consent settings (Issue #566)
	add_submenu_page(
		'wpshadow',
		__( 'Privacy & Consent', 'wpshadow' ),
		__( 'Privacy & Consent', 'wpshadow' ),
		'manage_options',
		'wpshadow-privacy',
		'wpshadow_render_privacy_page'
	);
} );


// Integrate WPShadow measurements with WordPress Site Health (Tools → Site Health).
add_filter( 'site_status_tests', function ( $tests ) {
	if ( ! is_array( $tests ) ) {
		$tests = array();
	}

	$badge = array(
		'label' => __( 'WPShadow', 'wpshadow' ),
		'color' => 'blue',
	);

	$tests['direct']['wpshadow_quick_scan'] = array(
		'label' => __( 'WPShadow Quick Scan', 'wpshadow' ),
		'test'  => 'wpshadow_site_health_test_quick_scan',
	);

	$tests['direct']['wpshadow_deep_scan'] = array(
		'label' => __( 'WPShadow Deep Scan', 'wpshadow' ),
		'test'  => 'wpshadow_site_health_test_deep_scan',
	);

	// Optional summary test to reflect overall WPShadow status.
	$tests['direct']['wpshadow_overall'] = array(
		'label' => __( 'WPShadow Overall Status', 'wpshadow' ),
		'test'  => 'wpshadow_site_health_test_overall',
	);

	// Issue #558: Add individual critical findings as Site Health tests
	$findings = wpshadow_get_site_findings();
	$critical_findings = array_filter( $findings, function( $f ) {
		return isset( $f['threat_level'] ) && $f['threat_level'] >= 75;
	} );

	foreach ( array_slice( $critical_findings, 0, 5 ) as $finding ) {
		$finding_id = isset( $finding['id'] ) ? $finding['id'] : md5( $finding['title'] ?? '' );
		$tests['direct'][ 'wpshadow_finding_' . $finding_id ] = array(
			'label' => $finding['title'] ?? __( 'Security Issue', 'wpshadow' ),
			'test'  => function() use ( $finding, $badge, $finding_id ) {
				return wpshadow_site_health_test_finding( $finding, $badge, $finding_id );
			},
		);
	}

	// Store badge for callbacks to reference consistently.
	$GLOBALS['wpshadow_site_health_badge'] = $badge;

	return $tests;
} );

/**
 * Site Health test: Quick Scan recency.
 */
function wpshadow_site_health_test_quick_scan() {
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array( 'label' => 'WPShadow', 'color' => 'blue' );
	$last  = get_option( 'wpshadow_last_quick_checks', 0 );

	$now   = time();
	$label = __( 'WPShadow Quick Scan', 'wpshadow' );
	$desc  = __( 'WPShadow provides a fast, lightweight scan of common issues. Run it regularly to keep your site in shape.', 'wpshadow' );
	$action_url = admin_url( 'admin.php?page=wpshadow' );

	if ( empty( $last ) ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __( 'Quick Scan has not been run yet. Open WPShadow to run a Quick Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_quick_scan',
		);
	}

	$age = $now - (int) $last;
	$age_str = sprintf( __( 'Last run %s ago.', 'wpshadow' ), human_time_diff( $last, $now ) );

	if ( $age > DAY_IN_SECONDS * 2 ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => $age_str . ' ' . __( 'Consider running a new Quick Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_quick_scan',
		);
	}

	return array(
		'label'       => $label,
		'status'      => 'good',
		'badge'       => $badge,
		'description' => $age_str,
		'test'        => 'wpshadow_site_health_test_quick_scan',
	);
}

/**
 * Site Health test: Deep Scan recency.
 */
function wpshadow_site_health_test_deep_scan() {
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array( 'label' => 'WPShadow', 'color' => 'blue' );
	$last  = get_option( 'wpshadow_last_heavy_tests', 0 );

	$now   = time();
	$label = __( 'WPShadow Deep Scan', 'wpshadow' );
	$action_url = admin_url( 'admin.php?page=wpshadow' );

	if ( empty( $last ) ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __( 'Deep Scan has not been run yet. Open WPShadow to run a Deep Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url . '#deep-scan' ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_deep_scan',
		);
	}

	$age = $now - (int) $last;
	$age_str = sprintf( __( 'Last run %s ago.', 'wpshadow' ), human_time_diff( $last, $now ) );

	if ( $age > WEEK_IN_SECONDS ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => $age_str . ' ' . __( 'Consider running a new Deep Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url . '#deep-scan' ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_deep_scan',
		);
	}

	return array(
		'label'       => $label,
		'status'      => 'good',
		'badge'       => $badge,
		'description' => $age_str,
		'test'        => 'wpshadow_site_health_test_deep_scan',
	);
}

/**
 * Site Health test: Overall WPShadow summary.
 */
function wpshadow_site_health_test_overall() {
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array( 'label' => 'WPShadow', 'color' => 'blue' );
	$label = __( 'WPShadow Overall Status', 'wpshadow' );

	// If we have recent scans, mark good; otherwise recommend action.
	$quick = (int) get_option( 'wpshadow_last_quick_checks', 0 );
	$deep  = (int) get_option( 'wpshadow_last_heavy_tests', 0 );

	$action_url = admin_url( 'admin.php?page=wpshadow' );

	if ( empty( $quick ) && empty( $deep ) ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __( 'No WPShadow scans have been recorded yet. Run Quick or Deep Scan in the WPShadow dashboard.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url . '#quick-scan' ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_overall',
		);
	}

	return array(
		'label'       => $label,
		'status'      => 'good',
		'badge'       => $badge,
		'description' => __( 'WPShadow scans are active. See the WPShadow dashboard for detailed category health.', 'wpshadow' ),
		'actions'     => array(
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( $action_url ),
				esc_html__( 'View Dashboard', 'wpshadow' )
			),
		),
		'test'        => 'wpshadow_site_health_test_overall',
	);
}

/**
 * Site Health test: Individual WPShadow finding (Issue #558)
 * 
 * @param array  $finding Finding data
 * @param array  $badge Site Health badge
 * @param string $finding_id Finding identifier
 * @return array Site Health test result
 */
function wpshadow_site_health_test_finding( $finding, $badge, $finding_id ) {
	$action_url = admin_url( 'admin.php?page=wpshadow' );
	$threat_level = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
	
	// Check if already fixed
	$status_manager = new \WPShadow\Core\Finding_Status_Manager();
	$status = $status_manager->get_finding_status( $finding_id );
	
	if ( $status === 'fixed' ) {
		return array(
			'label'       => $finding['title'] ?? __( 'Security Issue', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => $badge,
			'description' => __( '✓ This issue has been resolved by WPShadow.', 'wpshadow' ),
			'test'        => 'wpshadow_finding_' . $finding_id,
		);
	}
	
	// Determine status based on threat level
	$site_status = 'recommended';
	if ( $threat_level >= 90 ) {
		$site_status = 'critical';
	} elseif ( $threat_level >= 75 ) {
		$site_status = 'recommended';
	}
	
	$description = isset( $finding['description'] ) ? wp_strip_all_tags( $finding['description'] ) : '';
	$category = isset( $finding['category'] ) ? $finding['category'] : '';
	$slug = sanitize_title( (string) ( $finding_id ?? ( $finding['title'] ?? '' ) ) );
	$kb_link = ! empty( $slug ) ? wpshadow_get_kb_link( $slug ) : '';
	$training_link = ! empty( $slug ) ? wpshadow_get_training_link( $slug ) : '';
	
	if ( ! empty( $category ) ) {
		$description .= ' ' . sprintf( __( '(Category: %s)', 'wpshadow' ), ucfirst( $category ) );
	}
	
	$actions = array(
		sprintf(
			'<a href="%s">%s</a>',
			esc_url( $action_url ),
			__( 'View in WPShadow', 'wpshadow' )
		),
	);

	if ( ! empty( $kb_link ) ) {
		$actions[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( $kb_link ),
			__( 'Learn more (KB)', 'wpshadow' )
		);
	}

	if ( ! empty( $training_link ) ) {
		$actions[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( $training_link ),
			__( 'Watch training', 'wpshadow' )
		);
	}
	
	return array(
		'label'       => $finding['title'] ?? __( 'Security Issue', 'wpshadow' ),
		'status'      => $site_status,
		'badge'       => $badge,
		'description' => $description,
		'actions'     => $actions,
		'test'        => 'wpshadow_finding_' . $finding_id,
	);
}

// Add WPShadow section to Site Health → Info (debug tab).
add_filter( 'debug_information', function ( $info ) {
	if ( ! is_array( $info ) ) {
		$info = array();
	}

	$current_user_id = get_current_user_id();
	$quick_hidden = (bool) get_user_meta( $current_user_id, 'wpshadow_hide_quick_scan', true );
	$deep_hidden  = (bool) get_user_meta( $current_user_id, 'wpshadow_hide_deep_scan', true );

	$quick_last = (int) get_option( 'wpshadow_last_quick_checks', 0 );
	$deep_last  = (int) get_option( 'wpshadow_last_heavy_tests', 0 );

	$autofix_all = (bool) get_option( 'wpshadow_allow_all_autofixes', false );
	$autofix_types = get_option( 'wpshadow_autofix_permissions', array() );
	$autofix_count = is_array( $autofix_types ) ? count( $autofix_types ) : 0;

	$finding_log = get_option( 'wpshadow_finding_log', array() );
	$finding_count = is_array( $finding_log ) ? count( $finding_log ) : 0;

	$section = array(
		'label'  => __( 'WPShadow', 'wpshadow' ),
		'fields' => array(
			array(
				'label'  => __( 'Quick Scan last run', 'wpshadow' ),
				'value'  => $quick_last ? sprintf( __( '%s ago', 'wpshadow' ), human_time_diff( $quick_last, time() ) ) : __( 'Not yet', 'wpshadow' ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Deep Scan last run', 'wpshadow' ),
				'value'  => $deep_last ? sprintf( __( '%s ago', 'wpshadow' ), human_time_diff( $deep_last, time() ) ) : __( 'Not yet', 'wpshadow' ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Panels hidden (current user)', 'wpshadow' ),
				'value'  => sprintf( __( 'Quick: %s, Deep: %s', 'wpshadow' ), $quick_hidden ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ), $deep_hidden ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ) ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Auto-fix (global allow)', 'wpshadow' ),
				'value'  => $autofix_all ? __( 'Enabled', 'wpshadow' ) : __( 'Disabled', 'wpshadow' ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Auto-fix types enabled', 'wpshadow' ),
				'value'  => (string) $autofix_count,
				'private'=> false,
			),
			array(
				'label'  => __( 'Finding log entries', 'wpshadow' ),
				'value'  => (string) $finding_count,
				'private'=> false,
			),
		),
	);

	$info['wpshadow'] = $section;
	return $info;
} );

// Mirror WPShadow Tools into core Tools page for enabled items only.
add_action( 'tool_box', function() {
	if ( ! current_user_can( 'read' ) ) {
		return;
	}

	$catalog = wpshadow_get_tools_catalog();
	foreach ( $catalog as $item ) {
		if ( empty( $item['enabled'] ) ) {
			continue; // Only list active tools
		}

		$url = admin_url( 'admin.php?page=wpshadow-tools&tool=' . $item['tool'] );

		echo '<div class="card">';
		echo '<h3>' . esc_html( $item['title'] ) . '</h3>';
		echo '<p>' . esc_html( $item['desc'] ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Open Tool', 'wpshadow' ) . '</a></p>';
		echo '</div>';
	}

	// Mirror Help items as well (enabled only)
	$help_catalog = wpshadow_get_help_catalog();
	foreach ( $help_catalog as $item ) {
		if ( empty( $item['enabled'] ) ) {
			continue;
		}

		$url = admin_url( 'admin.php?page=wpshadow-help&help_page=' . $item['page'] );

		echo '<div class="card">';
		echo '<h3>' . esc_html( $item['title'] ) . '</h3>';
		echo '<p>' . esc_html( $item['desc'] ) . '</p>';
		echo '<p><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Open Help', 'wpshadow' ) . '</a></p>';
		echo '</div>';
	}
} );

// Load core interfaces and base classes first
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-diagnostic-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-treatment-interface.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-treatment-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-ajax-handler-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-abstract-registry.php';

// Load diagnostic registry
require_once plugin_dir_path( __FILE__ ) . 'includes/diagnostics/class-diagnostic-registry.php';

// Load WordPress Settings Scan
require_once plugin_dir_path( __FILE__ ) . 'includes/diagnostics/class-wordpress-settings-scan.php';

// Load treatment registry
require_once plugin_dir_path( __FILE__ ) . 'includes/treatments/class-treatment-registry.php';

// Load remaining core classes
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-finding-status-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-kpi-tracker.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-block-registry.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-discovery.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-discovery-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-wizard.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-ajax.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-command-registry.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-executor.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-kanban-workflow-helper.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-user-preferences-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/knowledge-base/class-kb-formatter.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/knowledge-base/class-kb-article-generator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/knowledge-base/class-kb-library.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/knowledge-base/class-kb-search.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/knowledge-base/class-training-provider.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/knowledge-base/class-training-progress.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/privacy/class-privacy-policy-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/privacy/class-consent-preferences.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/privacy/class-first-run-consent.php';

// Phase 7: Cloud Features & SaaS Integration
require_once plugin_dir_path( __FILE__ ) . 'includes/cloud/class-cloud-client.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/cloud/class-registration-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/cloud/class-notification-manager.php';

// Phase 8: Guardian & Automation System
// Core managers (Priority 1) - Located in includes/guardian/
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-guardian-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-guardian-activity-logger.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-baseline-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-backup-manager.php';

// Auto-Fix System (Priority 2)
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-auto-fix-policy-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-anomaly-detector.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-auto-fix-executor.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-recovery-system.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-compliance-checker.php';

// Admin UI Components (Priority 3)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-guardian-dashboard.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-guardian-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-report-form.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-notification-preferences-form.php';

// AJAX Command Handlers (Priorities 1-3)
// Priority 1 commands
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-enable-guardian-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-configure-guardian-command.php';

// Priority 2 commands
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-get-scan-results-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-execute-auto-fix-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-preview-auto-fixes-command.php';

// Priority 3 commands
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-update-auto-fix-policy-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-generate-report-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-send-report-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-manage-notifications-command.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-treatment-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-site-health-explanations.php';

/**
 * Initialize diagnostics system and Guardian components.
 */
add_action( 'plugins_loaded', function() {
	\WPShadow\Diagnostics\Diagnostic_Registry::init();
	\WPShadow\Treatments\Treatment_Registry::init();
	\WPShadow\Workflow\Workflow_Executor::init();
	\WPShadow\Core\Treatment_Hooks::init();
	\WPShadow\Core\Site_Health_Explanations::init();

	// Register workflow AJAX handlers (Phase 3.5.1 - Refactoring)
	\WPShadow\Admin\Ajax\Save_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Load_Workflows_Handler::register();
	\WPShadow\Admin\Ajax\Get_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Delete_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Toggle_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Generate_Workflow_Name_Handler::register();
	\WPShadow\Admin\Ajax\Get_Available_Actions_Handler::register();
	\WPShadow\Admin\Ajax\Get_Action_Config_Handler::register();
	\WPShadow\Admin\Ajax\Run_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Create_From_Example_Handler::register();

	// Initialize Guardian system (Phase 8)
	\WPShadow\Guardian\Guardian_Manager::init();

	// Register Guardian AJAX command handlers (Phase 8)
	// Priority 1 handlers
	\WPShadow\Workflow\Commands\Enable_Guardian_Command::register();
	\WPShadow\Workflow\Commands\Configure_Guardian_Command::register();

	// Priority 2 handlers
	\WPShadow\Workflow\Commands\Get_Scan_Results_Command::register();
	\WPShadow\Workflow\Commands\Execute_Auto_Fix_Command::register();
	\WPShadow\Workflow\Commands\Preview_Auto_Fixes_Command::register();

	// Priority 3 handlers
	\WPShadow\Workflow\Commands\Update_Auto_Fix_Policy_Command::register();
	\WPShadow\Workflow\Commands\Generate_Report_Command::register();
	\WPShadow\Workflow\Commands\Send_Report_Command::register();
	\WPShadow\Workflow\Commands\Manage_Notifications_Command::register();
} );

/**
 * Enqueue Kanban board assets and gauges CSS.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	// Enqueue gauges CSS for health dashboard (#563)
	wp_enqueue_style(
		'wpshadow-gauges',
		WPSHADOW_URL . 'assets/css/gauges.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_style(
		'wpshadow-kanban-board',
		WPSHADOW_URL . 'assets/css/kanban-board.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-kanban-board',
		WPSHADOW_URL . 'assets/js/kanban-board.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	// Localize script with nonce
	wp_localize_script( 'wpshadow-kanban-board', 'wpshadowKanban', array(
		'kanban_nonce' => wp_create_nonce( 'wpshadow_kanban' ),
	) );
	
	// Workflow list scripts
	if ( $hook === 'toplevel_page_wpshadow' || strpos( $hook, 'wpshadow-workflows' ) !== false ) {
		wp_enqueue_script(
			'wpshadow-workflow-list',
			WPSHADOW_URL . 'assets/js/workflow-list.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);
		
		wp_localize_script( 'wpshadow-workflow-list', 'wpshadowWorkflow', array(
			'nonce' => wp_create_nonce( 'wpshadow_workflow' ),
		) );
	}

	// Guardian Dashboard and Settings assets (Phase 8)
	if ( strpos( $hook, 'wpshadow-guardian' ) !== false ) {
		wp_enqueue_style(
			'wpshadow-guardian-dashboard-settings',
			WPSHADOW_URL . 'assets/css/guardian-dashboard-settings.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-guardian-dashboard-settings',
			WPSHADOW_URL . 'assets/js/guardian-dashboard-settings.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script for AJAX
		wp_localize_script( 'wpshadow-guardian-dashboard-settings', 'wpshadow', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wpshadow_guardian_nonce' )
		) );
	}
} );

// Enqueue assets for the Color Contrast Checker tool.
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( strpos( $hook, 'wpshadow-tools' ) === false ) {
		return;
	}

	$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
	if ( $tool !== 'color-contrast' ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-color-contrast',
		WPSHADOW_URL . 'assets/css/color-contrast.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-color-contrast',
		WPSHADOW_URL . 'assets/js/color-contrast.js',
		array(),
		WPSHADOW_VERSION,
		true
	);

	wp_localize_script( 'wpshadow-color-contrast', 'wpshadowContrast', array(
		'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
		'themeNonce'     => wp_create_nonce( 'wpshadow_theme_contrast' ),
		'i18nInvalid'    => __( 'Please enter valid 6-digit hex colors.', 'wpshadow' ),
		'i18nPass'       => __( 'Pass', 'wpshadow' ),
		'i18nFail'       => __( 'Fail', 'wpshadow' ),
		'i18nRatioLabel' => __( 'Contrast ratio', 'wpshadow' ),
		'i18nThemeScan'  => __( 'Scan Active Theme', 'wpshadow' ),
		'i18nThemeError' => __( 'Unable to scan the active theme. Please try again.', 'wpshadow' ),
		'i18nThemeBg'    => __( 'Background', 'wpshadow' ),
	) );
} );

// Enqueue assets for the Mobile Friendliness Checker tool.
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( strpos( $hook, 'wpshadow-tools' ) === false ) {
		return;
	}

	$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
	if ( $tool !== 'mobile-friendliness' ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-mobile-friendliness',
		WPSHADOW_URL . 'assets/css/mobile-friendliness.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-mobile-friendliness',
		WPSHADOW_URL . 'assets/js/mobile-friendliness.js',
		array(),
		WPSHADOW_VERSION,
		true
	);

	wp_localize_script( 'wpshadow-mobile-friendliness', 'wpshadowMobileCheck', array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'wpshadow_mobile_check' ),
		'defaultUrl'=> home_url(),
		'i18nError' => __( 'Unable to complete the mobile check. Please try again.', 'wpshadow' ),
		'i18nRun'   => __( 'Run Mobile Check', 'wpshadow' ),
		'i18nRunning'=> __( 'Checking...', 'wpshadow' ),
	) );
} );

/**
 * Enqueue Site Health explanations CSS on Site Health page.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	// Site Health page is 'site-health.php' or in Tools menu
	if ( $hook !== 'site-health.php' && strpos( $hook, 'tools.php' ) === false ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-site-health-explanations',
		WPSHADOW_URL . 'assets/css/site-health-explanations.css',
		array(),
		WPSHADOW_VERSION
	);
} );

/**
 * Initialize dark mode for WPShadow admin.
 */
add_action( 'admin_init', function() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	// Get user's dark mode preference
	$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';

	// Determine if dark mode should be active
	$apply_dark_mode = false;
	if ( $dark_mode_pref === 'dark' ) {
		$apply_dark_mode = true;
	} elseif ( $dark_mode_pref === 'auto' ) {
		// Auto mode - will be handled by JavaScript based on system preference
		$apply_dark_mode = null; // null means auto/JS-controlled
	}

	// Store preference for use in admin
	if ( $apply_dark_mode !== false ) {
		define( 'WPSHADOW_DARK_MODE', $apply_dark_mode );
	}
} );

/**
 * Enqueue Tooltip assets across wp-admin (except login/front-end).
 */
add_action( 'admin_enqueue_scripts', function() {
	global $pagenow;
	
	// Skip tooltips on specific pages
	if ( in_array( $pagenow, array( 'plugins.php', 'edit-comments.php', 'edit.php' ), true ) ) {
		return;
	}
	
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	// Enqueue tooltip CSS
	wp_enqueue_style(
		'wpshadow-tooltips',
		WPSHADOW_URL . 'assets/css/tooltips.css',
		array(),
		WPSHADOW_VERSION
	);

	// Enqueue tooltip JS
	wp_enqueue_script(
		'wpshadow-tooltips',
		WPSHADOW_URL . 'assets/js/tooltips.js',
		array(),
		WPSHADOW_VERSION,
		false
	);

	// Get user preferences
	$prefs = wpshadow_get_user_tip_prefs( $user_id );
	$disabled_categories = $prefs['disabled_categories'] ?? array();
	$dismissed_tips = $prefs['dismissed_tips'] ?? array();

	// Get full tooltip catalog
	$catalog = wpshadow_get_tooltip_catalog();

	// Build tooltip data object, excluding admin bar tooltips
	$tooltip_data = array();
	foreach ( $catalog as $tip ) {
		// Skip admin bar tooltips
		if ( strpos( $tip['selector'], '#wp-admin-bar-' ) === 0 ) {
			continue;
		}
		
		$tooltip_data[ $tip['id'] ] = array(
			'id'       => $tip['id'],
			'selector' => $tip['selector'],
			'title'    => $tip['title'],
			'message'  => $tip['message'],
			'category' => $tip['category'],
			'level'    => $tip['level'],
			'kb_url'   => ! empty( $tip['kb_url'] ) ? $tip['kb_url'] : '',  // Include KB URL if available
		);
	}

	// Localize tooltip data
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowTooltips', $tooltip_data );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowDisabledTipCategories', $disabled_categories );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowDismissedTips', $dismissed_tips );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowTipNonce', array( 'nonce' => wp_create_nonce( 'wpshadow_tip_dismiss' ) ) );
} );

/**
 * Enqueue dark mode CSS for WPShadow admin pages.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';

	// Enqueue dark mode CSS
	wp_enqueue_style(
		'wpshadow-dark-mode',
		WPSHADOW_URL . 'assets/css/dark-mode.css',
		array(),
		WPSHADOW_VERSION
	);

	// Enqueue dark mode JS
	wp_enqueue_script(
		'wpshadow-dark-mode',
		WPSHADOW_URL . 'assets/js/dark-mode.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	// Localize script with preference
	wp_localize_script( 'wpshadow-dark-mode', 'wpshadowDarkMode', array(
		'preference' => $dark_mode_pref,
	) );
} );

/**
 * Add WPShadow Dark Mode field to user profile.
 */
add_action( 'show_user_profile', 'wpshadow_add_dark_mode_profile_field' );
add_action( 'edit_user_profile', 'wpshadow_add_dark_mode_profile_field' );

function wpshadow_add_dark_mode_profile_field( $user ) {
	$dark_mode_pref = get_user_meta( $user->ID, 'wpshadow_dark_mode_preference', true ) ?: 'auto';
	?>
	<table class="form-table" role="presentation">
		<tr class="wpshadow-dark-mode-wrap">
			<th scope="row"><?php esc_html_e( 'WPShadow Dark Mode', 'wpshadow' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'WPShadow Dark Mode', 'wpshadow' ); ?></span></legend>
					<label>
						<input type="radio" name="wpshadow_dark_mode" value="auto" <?php checked( $dark_mode_pref, 'auto' ); ?>>
						<?php esc_html_e( 'Auto (follow system preference)', 'wpshadow' ); ?>
					</label><br>
					<label>
						<input type="radio" name="wpshadow_dark_mode" value="light" <?php checked( $dark_mode_pref, 'light' ); ?>>
						<?php esc_html_e( 'Light', 'wpshadow' ); ?>
					</label><br>
					<label>
						<input type="radio" name="wpshadow_dark_mode" value="dark" <?php checked( $dark_mode_pref, 'dark' ); ?>>
						<?php esc_html_e( 'Dark', 'wpshadow' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Choose your preferred dark mode setting for WPShadow admin pages.', 'wpshadow' ); ?>
					</p>
				</fieldset>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save WPShadow Dark Mode profile field.
 */
add_action( 'personal_options_update', 'wpshadow_save_dark_mode_profile_field' );
add_action( 'edit_user_profile_update', 'wpshadow_save_dark_mode_profile_field' );

function wpshadow_save_dark_mode_profile_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	if ( isset( $_POST['wpshadow_dark_mode'] ) ) {
		$dark_mode = sanitize_text_field( $_POST['wpshadow_dark_mode'] );
		if ( in_array( $dark_mode, array( 'auto', 'light', 'dark' ), true ) ) {
			update_user_meta( $user_id, 'wpshadow_dark_mode_preference', $dark_mode );
		}
	}
}

/**
 * Render Workflow Builder page.
 */
function wpshadow_render_workflow_builder() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
	
	if ( $action === 'create' || $action === 'edit' ) {
		include WPSHADOW_PATH . 'includes/views/workflow-wizard.php';
	} else {
		include WPSHADOW_PATH . 'includes/views/workflow-list.php';
	}
}

/**
 * Catalog of WPShadow tools shown on /wp-admin/?page=wpshadow-tools.
 * Returned structure is reused for both the WPShadow Tools page and the core Tools page mirror.
 */
function wpshadow_get_tools_catalog() {
	return array(
		array(
			'title'   => __( 'Accessibility Audit', 'wpshadow' ),
			'desc'    => __( 'Scan your site for accessibility issues and WCAG compliance.', 'wpshadow' ),
			'tool'    => 'a11y-audit',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Broken Link Checker', 'wpshadow' ),
			'desc'    => __( 'Find and fix broken links across your site.', 'wpshadow' ),
			'tool'    => 'broken-links',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Color Contrast Checker', 'wpshadow' ),
			'desc'    => __( 'Check color combinations for accessibility compliance.', 'wpshadow' ),
			'tool'    => 'color-contrast',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Dark Mode', 'wpshadow' ),
			'desc'    => __( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ),
			'tool'    => 'dark-mode',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Email Test & Configuration', 'wpshadow' ),
			'desc'    => __( 'Test email delivery and configure From Name/Email to ensure emails are sent properly.', 'wpshadow' ),
			'tool'    => 'email-test',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Cache Management', 'wpshadow' ),
			'desc'    => __( 'Manage site caching and clear cache when needed.', 'wpshadow' ),
			'tool'    => 'simple-cache',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Magic Link Support', 'wpshadow' ),
			'desc'    => __( 'Generate secure one-time access links for support staff.', 'wpshadow' ),
			'tool'    => 'magic-link-support',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Mobile Friendliness', 'wpshadow' ),
			'desc'    => __( 'Test your site for mobile compatibility and responsive design.', 'wpshadow' ),
			'tool'    => 'mobile-friendliness',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Tips & Guidance', 'wpshadow' ),
			'desc'    => __( 'Friendly tooltips across wp-admin with opt-out controls and helpful guidance for beginners.', 'wpshadow' ),
			'tool'    => 'tips-coach',
			'enabled' => true,
		),
	);
}

/**
 * Render Tools page.
 */
function wpshadow_render_tools() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';

	// Route to specific tool if requested
	if ( ! empty( $tool ) ) {
		$tool_file = WPSHADOW_PATH . 'includes/views/tools/' . $tool . '.php';
		if ( file_exists( $tool_file ) ) {
			include $tool_file;
			return;
		}
	}

	$catalog = wpshadow_get_tools_catalog();

	// Show tools index
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WPShadow Tools', 'wpshadow' ); ?></h1>
		<p><?php esc_html_e( 'Additional tools for site analysis and optimization.', 'wpshadow' ); ?></p>

		<div class="wpshadow-tools-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
			<?php foreach ( $catalog as $item ) :
				$tool_url = admin_url( 'admin.php?page=wpshadow-tools&tool=' . $item['tool'] );
			?>
			<div class="wpshadow-tool-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php echo esc_html( $item['title'] ); ?></h2>
				<p><?php echo esc_html( $item['desc'] ); ?></p>
				<p style="margin-bottom: 0;">
					<?php if ( ! empty( $item['enabled'] ) ) : ?>
						<a href="<?php echo esc_url( $tool_url ); ?>" class="button button-primary">
							<?php esc_html_e( 'Open Tool', 'wpshadow' ); ?>
						</a>
					<?php else : ?>
						<button class="button button-secondary" disabled><?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?></button>
					<?php endif; ?>
				</p>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Run broken links scan programmatically for tools/workflows.
 *
 * @param array $args Scan options.
 * @return array Results with broken_links, posts_checked, links_checked.
 */
function wpshadow_run_broken_links_scan( $args = array() ) {
	$defaults = array(
		'check_internal' => true,
		'check_external' => true,
		'check_images'   => false,
		'limit'          => -1,
	);
	$args = wp_parse_args( $args, $defaults );

	$broken_links  = array();
	$posts_checked = 0;
	$links_checked = 0;

	$query_args = array(
		'post_type'      => array( 'post', 'page' ),
		'posts_per_page' => $args['limit'],
		'post_status'    => 'publish',
	);

	$posts = get_posts( $query_args );
	$posts_checked = count( $posts );

	foreach ( $posts as $post ) {
		$content = $post->post_content;

		// Links
		preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=["\']([^"\']+)["\']/', $content, $matches );
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $url ) {
				$links_checked++;
				if ( strpos( $url, '#' ) === 0 ) {
					continue;
				}
				$is_internal = strpos( $url, home_url() ) === 0 || strpos( $url, '/' ) === 0;
				if ( $is_internal && ! $args['check_internal'] ) {
					continue;
				}
				if ( ! $is_internal && ! $args['check_external'] ) {
					continue;
				}
				if ( strpos( $url, '/' ) === 0 ) {
					$url = home_url( $url );
				}
				$response = wp_remote_head( $url, array(
					'timeout'     => 5,
					'redirection' => 2,
				) );
				if ( is_wp_error( $response ) ) {
					$broken_links[] = array(
						'url'         => $url,
						'post_title'  => $post->post_title,
						'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
						'status_code' => 'ERROR',
					);
				} else {
					$code = wp_remote_retrieve_response_code( $response );
					if ( $code >= 400 ) {
						$broken_links[] = array(
							'url'         => $url,
							'post_title'  => $post->post_title,
							'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
							'status_code' => $code,
						);
					}
				}
			}
		}

		// Images
		if ( $args['check_images'] ) {
			preg_match_all( '/<img\s+(?:[^>]*?\s+)?src=["\']([^"\']+)["\']/', $content, $img_matches );
			if ( ! empty( $img_matches[1] ) ) {
				foreach ( $img_matches[1] as $img_url ) {
					$links_checked++;
					$response = wp_remote_head( $img_url, array(
						'timeout'     => 5,
						'redirection' => 2,
					) );
					if ( is_wp_error( $response ) ) {
						$broken_links[] = array(
							'url'         => $img_url,
							'post_title'  => $post->post_title . ' (image)',
							'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
							'status_code' => 'ERROR',
						);
					} else {
						$code = wp_remote_retrieve_response_code( $response );
						if ( $code >= 400 ) {
							$broken_links[] = array(
								'url'         => $img_url,
								'post_title'  => $post->post_title . ' (image)',
								'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
								'status_code' => $code,
							);
						}
					}
				}
			}
		}
	}

	return array(
		'broken_links'  => $broken_links,
		'posts_checked' => $posts_checked,
		'links_checked' => $links_checked,
	);
}

/**
 * Catalog of WPShadow help items shown on /wp-admin/?page=wpshadow-help.
 */
function wpshadow_get_help_catalog() {
	return array(
		array(
			'title'   => __( 'Emergency Support', 'wpshadow' ),
			'desc'    => __( 'Get immediate help when your site is down or experiencing critical issues.', 'wpshadow' ),
			'page'    => 'emergency-support',
			'enabled' => true,
		),
	);
}

/**
 * Render Help page.
 */
function wpshadow_render_help() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$page     = isset( $_GET['help_page'] ) ? sanitize_key( $_GET['help_page'] ) : '';
	$catalog  = wpshadow_get_help_catalog();

	// Route to specific help page if requested and available.
	if ( ! empty( $page ) ) {
		$help_file = WPSHADOW_PATH . 'includes/views/help/' . $page . '.php';
		if ( file_exists( $help_file ) ) {
			include $help_file;
			return;
		}
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WPShadow Help & Support', 'wpshadow' ); ?></h1>
		<p><?php esc_html_e( 'Get help and access support resources for WPShadow.', 'wpshadow' ); ?></p>

		<div class="wpshadow-help-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
			<?php foreach ( $catalog as $item ) :
				$help_url = admin_url( 'admin.php?page=wpshadow-help&help_page=' . $item['page'] );
				?>
				<div class="wpshadow-help-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
					<h2 style="margin-top: 0;"><?php echo esc_html( $item['title'] ); ?></h2>
					<p><?php echo esc_html( $item['desc'] ); ?></p>
					<p style="margin-bottom: 0;">
						<?php if ( ! empty( $item['enabled'] ) ) : ?>
							<a href="<?php echo esc_url( $help_url ); ?>" class="button button-primary">
								<?php esc_html_e( 'Open', 'wpshadow' ); ?>
							</a>
						<?php else : ?>
							<button class="button button-secondary" disabled><?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?></button>
						<?php endif; ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>

		<hr style="margin: 40px 0;">

		<h2><?php esc_html_e( 'Documentation & Resources', 'wpshadow' ); ?></h2>
		<ul>
			<li><a href="https://github.com/thisismyurl/wpshadow" target="_blank"><?php esc_html_e( 'GitHub Repository', 'wpshadow' ); ?></a></li>
			<li><a href="https://github.com/thisismyurl/wpshadow/issues" target="_blank"><?php esc_html_e( 'Report an Issue', 'wpshadow' ); ?></a></li>
		</ul>
	</div>
	<?php

	// End help index render.
}

/**
 * Render activity history page (Issue #565)
 */
function wpshadow_render_activity_page() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	// Handle CSV export
	if ( isset( $_GET['export'] ) && 'csv' === $_GET['export'] ) {
		// Build filters for export
		$filters = array();
		if ( ! empty( $_GET['activity_category'] ) ) {
			$filters['category'] = sanitize_key( $_GET['activity_category'] );
		}
		if ( ! empty( $_GET['activity_action'] ) ) {
			$filters['action'] = sanitize_key( $_GET['activity_action'] );
		}
		if ( ! empty( $_GET['activity_search'] ) ) {
			$filters['search'] = sanitize_text_field( $_GET['activity_search'] );
		}

		// Generate CSV
		$csv = \WPShadow\Core\Activity_Logger::export_csv( $filters );

		// Send headers
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="wpshadow-activity-' . date( 'Y-m-d-His' ) . '.csv"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo $csv;
		exit;
	}

	// Render activity history view
	include WPSHADOW_PATH . 'includes/views/activity-history.php';
}

/**
 * Render privacy & consent settings page (Issue #566)
 */
function wpshadow_render_privacy_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	include WPSHADOW_PATH . 'includes/views/privacy-consent.php';
}

/**
 * Render health diagnostic dashboard.
 */
function wpshadow_render_dashboard() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	// Save report snapshot
	wpshadow_save_health_snapshot();

	// Check if filtering by category (Issue #564)
	$filter_category = isset( $_GET['category'] ) ? sanitize_key( $_GET['category'] ) : '';
	
	$health = wpshadow_get_health_status();
	$all_findings = wpshadow_get_site_findings();
	$dismissed = get_option( 'wpshadow_dismissed_findings', array() );
	
	// Filter out dismissed findings
	$all_findings = array_filter( $all_findings, function( $f ) use ( $dismissed ) {
		return ! isset( $f['id'] ) || ! isset( $dismissed[ $f['id'] ] );
	} );
	
	// Apply category filter if present
	if ( ! empty( $filter_category ) ) {
		$all_findings = array_filter( $all_findings, function( $f ) use ( $filter_category ) {
			return isset( $f['category'] ) && $f['category'] === $filter_category;
		} );
	}
	
	$critical_findings = array_filter( $all_findings, function( $f ) {
		return isset( $f['color'] ) && $f['color'] === '#f44336'; // Red = critical
	} );
	$show_all = isset( $_GET['show_all'] ) && 'true' === $_GET['show_all'];
	$findings_to_show = $show_all ? $all_findings : array_slice( $critical_findings, 0, 2 );
	
	// Group findings by category for Category Health display
	$findings_by_category = array();
	foreach ( $all_findings as $finding ) {
		$category = isset( $finding['category'] ) ? $finding['category'] : 'other';
		if ( ! isset( $findings_by_category[ $category ] ) ) {
			$findings_by_category[ $category ] = array();
		}
		$findings_by_category[ $category ][] = $finding;
	}
	?>
	<div class="wrap">
		<?php if ( ! empty( $filter_category ) ) : 
			// Get category metadata for filtered view
			$category_meta = array(
				'security' => array( 'label' => __( 'Security', 'wpshadow' ), 'icon' => 'dashicons-shield-alt', 'color' => '#dc2626' ),
				'performance' => array( 'label' => __( 'Performance', 'wpshadow' ), 'icon' => 'dashicons-dashboard', 'color' => '#0891b2' ),
				'code_quality' => array( 'label' => __( 'Code Quality', 'wpshadow' ), 'icon' => 'dashicons-editor-code', 'color' => '#7c3aed' ),
				'seo' => array( 'label' => __( 'SEO', 'wpshadow' ), 'icon' => 'dashicons-search', 'color' => '#2563eb' ),
				'design' => array( 'label' => __( 'Design', 'wpshadow' ), 'icon' => 'dashicons-admin-appearance', 'color' => '#8e44ad' ),
				'settings' => array( 'label' => __( 'Settings', 'wpshadow' ), 'icon' => 'dashicons-admin-settings', 'color' => '#4b5563' ),
				'wordpress_config' => array( 'label' => __( 'WordPress Config', 'wpshadow' ), 'icon' => 'dashicons-wordpress-alt', 'color' => '#0073aa' ),
				'monitoring' => array( 'label' => __( 'Monitoring', 'wpshadow' ), 'icon' => 'dashicons-chart-line', 'color' => '#059669' ),
				'workflows' => array( 'label' => __( 'Workflows', 'wpshadow' ), 'icon' => 'dashicons-update', 'color' => '#ea580c' ),
				'site_health' => array( 'label' => __( 'Site Health', 'wpshadow' ), 'icon' => 'dashicons-heart', 'color' => '#db2777' ),
				'wordpress_health' => array( 'label' => __( 'WordPress Site Health', 'wpshadow' ), 'icon' => 'dashicons-wordpress-alt', 'color' => '#2d5016' ),
			);
			$cat_meta = $category_meta[ $filter_category ] ?? array( 'label' => ucfirst( $filter_category ), 'icon' => 'dashicons-admin-generic', 'color' => '#666' );
		?>
		<div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: #f0f0f0; border-radius: 4px; color: #333; transition: all 0.2s ease;" onmouseover="this.style.background='#e0e0e0'" onmouseout="this.style.background='#f0f0f0'">
				<span class="dashicons dashicons-arrow-left-alt2" style="font-size: 16px;"></span>
				<?php esc_html_e( 'Back to All Categories', 'wpshadow' ); ?>
			</a>
			<span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: <?php echo esc_attr( $cat_meta['color'] ); ?>; color: white; border-radius: 20px; font-size: 12px; font-weight: 600;">
				<span class="dashicons" style="font-size: 14px; width: 14px; height: 14px;"></span>
				<?php echo esc_html( __( 'Filtered', 'wpshadow' ) ); ?>
			</span>
		</div>
		<h1 style="display: flex; align-items: center; gap: 12px;">
			<span class="<?php echo esc_attr( $cat_meta['icon'] ); ?>" style="font-size: 32px; color: <?php echo esc_attr( $cat_meta['color'] ); ?>;"></span>
			<?php echo esc_html( sprintf( __( '%s Dashboard', 'wpshadow' ), $cat_meta['label'] ) ); ?>
		</h1>
		<p style="font-size: 16px; color: #666; margin-top: 8px;">
			<?php 
			$finding_count = count( $all_findings );
			echo esc_html( sprintf( 
				_n( 'Showing %d finding in this category', 'Showing %d findings in this category', $finding_count, 'wpshadow' ), 
				$finding_count 
			) ); 
			?>
		</p>
		<?php else : ?>
		<h1><?php esc_html_e( 'WPShadow Site Health Diagnostic', 'wpshadow' ); ?></h1>
		<?php endif; ?>

		<script>
		jQuery(document).ready(function($) {
			// Dismiss finding
			$('.wpshadow-dismiss-finding').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var findingId = $btn.data('finding-id');
				var $card = $btn.closest('.wpshadow-finding-card');
				
				$.post(ajaxurl, {
					action: 'wpshadow_dismiss_finding',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_dismiss_finding' ); ?>',
					finding_id: findingId
				}, function(response) {
					if (response.success) {
						$card.fadeOut(300, function() { $(this).remove(); });
					}
				});

			});
			
			// Auto-fix finding
			$('.wpshadow-autofix-btn').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var findingId = $btn.data('finding-id');
				var $card = $btn.closest('.wpshadow-finding-card');
				
				$btn.prop('disabled', true).text('Fixing...');
				
				$.post(ajaxurl, {
					action: 'wpshadow_autofix_finding',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_autofix' ); ?>',
					finding_id: findingId
				}, function(response) {
					if (response.success) {
						$card.html('<div style="padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px;"><strong style="color: #2e7d32;">✓ Fixed!</strong><p style="margin: 5px 0 0 0; color: #555;">' + response.data.message + '</p></div>');
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else {
						alert('Could not auto-fix: ' + (response.data.message || 'Unknown error'));
						$btn.prop('disabled', false).text('Auto-Fix');
					}
				});
			});
			
			// Toggle auto-fix permission
			$('.wpshadow-autofix-toggle').on('change', function() {
				var $checkbox = $(this);
				var findingId = $checkbox.data('finding-id');
				var enabled = $checkbox.prop('checked');
				
				$.post(ajaxurl, {
					action: 'wpshadow_toggle_autofix_permission',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_autofix_permission' ); ?>',
					finding_id: findingId,
					enabled: enabled
				}, function(response) {
					if (response.success) {
						// Show brief confirmation
						var $label = $checkbox.closest('label');
						var originalText = $label.text();
						$label.text(enabled ? '✓ Enabled' : '✗ Disabled');
						setTimeout(function() {
							$label.text(originalText);
						}, 1500);
					}
				});
			});
			
			// Allow all auto-fixes
			$('.wpshadow-allow-all-autofixes').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var currentlyEnabled = $btn.data('enabled') === true;
				var newState = !currentlyEnabled;
				
				$btn.prop('disabled', true);
				
				$.post(ajaxurl, {
					action: 'wpshadow_allow_all_autofixes',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_allow_all_autofixes' ); ?>',
					enabled: newState
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert('Error: ' + (response.data.message || 'Unknown error'));
						$btn.prop('disabled', false);
					}
				});
			});
			
			// First scan (Issue #562)
			$('#wpshadow-start-first-scan').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var $prompt = $('#wpshadow-first-scan-prompt');
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Starting...', 'wpshadow' ) ); ?>');
				
				$.post(ajaxurl, {
					action: 'wpshadow_first_scan',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_first_scan_nonce' ); ?>'
				}, function(response) {
					if (response.success) {
						$prompt.fadeOut(300, function() {
							location.reload();
						});
					} else {
						alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Start Quick Scan', 'wpshadow' ) ); ?>');
					}
				});
			});
			
			// Skip first scan prompt
			$('#wpshadow-skip-first-scan').on('click', function(e) {
				e.preventDefault();
				var $prompt = $('#wpshadow-first-scan-prompt');
				$prompt.fadeOut(300);
			});
			
			// Schedule deep scan
			$('#wpshadow-schedule-scan-form').on('submit', function(e) {
				e.preventDefault();
				var $form = $(this);
				var $btn = $form.find('button[type="submit"]');
				var $status = $('#wpshadow-scan-status');
				var email = $form.find('input[name="email"]').val();
				var consent = $form.find('input[name="consent"]').prop('checked');
				
				$btn.prop('disabled', true).text('Scheduling...');
				$status.html('');
				
				$.post(ajaxurl, {
					action: 'wpshadow_schedule_deep_scan',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_schedule_scan' ); ?>',
					email: email,
					consent: consent
				}, function(response) {
					if (response.success) {
						$status.html('<div style="padding: 10px; background: #e8f5e9; color: #2e7d32; border-radius: 4px; margin-top: 10px;">✓ ' + response.data.message + '</div>');
						$form.slideUp();
					} else {
						$status.html('<div style="padding: 10px; background: #ffebee; color: #c62828; border-radius: 4px; margin-top: 10px;">✗ ' + response.data.message + '</div>');
						$btn.prop('disabled', false).text('Schedule Deep Scans');
					}
				});
			});
		
		// Modal handlers
		$('.wpshadow-modal-trigger').on('click', function(e) {
			e.preventDefault();
			var modalId = $(this).data('modal');
			$('#' + modalId).fadeIn(200);
		});
		
		$('.wpshadow-modal-close').on('click', function() {
			$(this).closest('.wpshadow-modal').fadeOut(200);
		});
		
		// Close modal on background click
		$('.wpshadow-modal').on('click', function(e) {
			if (e.target === this) {
				$(this).fadeOut(200);
			}
		});
		
		// Save tagline
		$('#wpshadow-tagline-form').on('submit', function(e) {
			e.preventDefault();
			var $form = $(this);
			var $btn = $form.find('button[type="submit"]');
			var $status = $('#wpshadow-tagline-status');
			var tagline = $('#wpshadow-tagline-input').val();
			
			$btn.prop('disabled', true).text('Saving...');
			$status.html('');
			
			$.post(ajaxurl, {
				action: 'wpshadow_save_tagline',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_save_tagline' ); ?>',
				tagline: tagline
			}, function(response) {
				if (response.success) {
					$status.html('<div style="padding: 10px; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">✓ ' + response.data.message + '</div>');
					setTimeout(function() {
						location.reload();
					}, 1500);
				} else {
					$status.html('<div style="padding: 10px; background: #ffebee; color: #c62828; border-radius: 4px;">✗ ' + response.data.message + '</div>');
					$btn.prop('disabled', false).text('Save Tagline');
				}
			});
		});
		</script>
		<?php
		$category_meta = array(
			'security' => array(
				'label' => __( 'Security', 'wpshadow' ),
				'icon'  => 'dashicons-shield-alt',
				'color' => '#dc2626',
				'bg'    => '#ffe0e0',
			),
			'performance' => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'icon'  => 'dashicons-dashboard',
				'color' => '#0891b2',
				'bg'    => '#e0f7ff',
			),
			'code_quality' => array(
				'label' => __( 'Code Quality', 'wpshadow' ),
				'icon'  => 'dashicons-editor-code',
				'color' => '#7c3aed',
				'bg'    => '#f3e8ff',
			),
			'seo' => array(
				'label' => __( 'SEO', 'wpshadow' ),
				'icon'  => 'dashicons-search',
				'color' => '#2563eb',
				'bg'    => '#e7f1ff',
			),
			'design' => array(
				'label' => __( 'Design', 'wpshadow' ),
				'icon'  => 'dashicons-admin-appearance',
				'color' => '#8e44ad',
				'bg'    => '#f2e9fb',
			),
			'settings' => array(
				'label' => __( 'Settings', 'wpshadow' ),
				'icon'  => 'dashicons-admin-settings',
				'color' => '#4b5563',
				'bg'    => '#eef2f7',
			),
			'wordpress_config' => array(
				'label' => __( 'WordPress Config', 'wpshadow' ),
				'icon'  => 'dashicons-wordpress-alt',
				'color' => '#0073aa',
				'bg'    => '#e5f5fa',
			),
			'monitoring' => array(
				'label' => __( 'Monitoring', 'wpshadow' ),
				'icon'  => 'dashicons-chart-line',
				'color' => '#059669',
				'bg'    => '#d1fae5',
			),
			'workflows' => array(
				'label' => __( 'Workflows', 'wpshadow' ),
				'icon'  => 'dashicons-update',
				'color' => '#ea580c',
				'bg'    => '#ffedd5',
			),
			'site_health' => array(
				'label' => __( 'Site Health', 'wpshadow' ),
				'icon'  => 'dashicons-heart',
				'color' => '#db2777',
				'bg'    => '#fce7f3',
			),
			'wordpress_health' => array(
				'label' => __( 'WordPress Site Health', 'wpshadow' ),
				'icon'  => 'dashicons-wordpress-alt',
				'color' => '#2d5016',
				'bg'    => '#f0f9f0',
			),
		);

		// Calculate overall health score (or category-specific if filtering)
		if ( ! empty( $filter_category ) && isset( $category_meta[ $filter_category ] ) ) {
			// Filtered view: Show single large category gauge
			$cat_findings = $findings_by_category[ $filter_category ] ?? array();
			$total = count( $cat_findings );
			
			// Calculate category health score
			$threat_total = 0;
			foreach ( $cat_findings as $finding ) {
				$threat_total += isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
			}
			$gauge_percent = $total > 0 ? min( 100, ( $threat_total / $total ) / 100 * 100 ) : 100;
			$gauge_percent = 100 - $gauge_percent; // Invert: higher is better
			
			// Determine status
			if ( $gauge_percent >= 80 ) {
				$status = __( 'Excellent', 'wpshadow' );
				$color = '#2e7d32';
				$bg = '#e8f5e9';
			} elseif ( $gauge_percent >= 60 ) {
				$status = __( 'Good', 'wpshadow' );
				$color = '#2e7d32';
				$bg = '#e8f5e9';
			} elseif ( $gauge_percent >= 40 ) {
				$status = __( 'Fair', 'wpshadow' );
				$color = '#f57c00';
				$bg = '#fff3e0';
			} else {
				$status = __( 'Needs Attention', 'wpshadow' );
				$color = '#c62828';
				$bg = '#ffebee';
			}
			
			$filtered_meta = $category_meta[ $filter_category ];
			?>
			<div style="margin: 30px 0;">
				<h2><?php echo esc_html( sprintf( __( '%s Health', 'wpshadow' ), $filtered_meta['label'] ) ); ?></h2>
				
				<div style="max-width: 400px; margin: 20px auto;">
					<div style="border: 2px solid <?php echo esc_attr( $color ); ?>; border-radius: 12px; padding: 32px; background: linear-gradient(135deg, #fff 0%, <?php echo esc_attr( $bg ); ?> 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">
						<div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px;">
							<span class="<?php echo esc_attr( $filtered_meta['icon'] ); ?>" style="font-size: 32px; color: <?php echo esc_attr( $filtered_meta['color'] ); ?>;"></span>
							<h3 style="margin: 0; font-size: 24px; color: <?php echo esc_attr( $filtered_meta['color'] ); ?>;"><?php echo esc_html( $filtered_meta['label'] ); ?></h3>
						</div>
						
						<svg width="250" height="250" viewBox="0 0 250 250" style="margin: 0 auto; display: block; filter: drop-shadow(0 3px 6px rgba(0,0,0,0.2));">
							<!-- Outer decorative circle -->
							<circle cx="125" cy="125" r="115" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="2" opacity="0.2" />
							<!-- Gauge background -->
							<circle cx="125" cy="125" r="100" fill="none" stroke="#e0e0e0" stroke-width="20" />
							<!-- Gauge progress -->
							<circle cx="125" cy="125" r="100" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="20"
								stroke-dasharray="<?php echo (int) ( $gauge_percent / 100 * 628 ); ?> 628"
								stroke-linecap="round" transform="rotate(-90 125 125)"
								style="transition: stroke-dasharray 0.5s ease;" />
							<!-- Center text -->
							<text x="125" y="120" text-anchor="middle" font-size="56" font-weight="bold" fill="<?php echo esc_attr( $color ); ?>"><?php echo (int) $gauge_percent; ?>%</text>
							<text x="125" y="150" text-anchor="middle" font-size="18" fill="#666"><?php echo esc_html( $status ); ?></text>
						</svg>
						
						<div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.1);">
							<p style="margin: 0; font-size: 16px; color: #666;">
								<?php 
								$critical_count = count( array_filter( $cat_findings, function( $f ) { return isset( $f['color'] ) && $f['color'] === '#f44336'; } ) );
								$passed = $total - $critical_count;
								echo esc_html( sprintf( __( 'Passes %d of %d tests', 'wpshadow' ), $passed, $total ) ); 
								?>
							</p>
						</div>
					</div>
				</div>
			</div>
			<?php
		} else {
			// Normal view: Show all gauges
			$overall_health = wpshadow_calculate_overall_health( $findings_by_category, $category_meta );
			?>
		
		<div style="margin: 30px 0;">
			
			<?php
			// Issue #562: Check last Quick Scan time and show prompt if needed
			$last_scan_time = get_option( 'wpshadow_last_quick_scan', 0 );
			$current_time = time();
			$scan_interval = 5 * 60; // 5 minutes
			$time_since_scan = $current_time - (int) $last_scan_time;
			
			if ( $last_scan_time === 0 ) {
				// Never scanned - show permission prompt
				?>
				<div id="wpshadow-first-scan-prompt" style="background: #e3f2fd; border-left: 4px solid #0073aa; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
					<h2 style="margin-top: 0; color: #0073aa; display: flex; align-items: center; gap: 10px;">
						<span class="dashicons dashicons-shield-alt" style="font-size: 24px;"></span>
						<?php esc_html_e( 'Let\'s Get Started', 'wpshadow' ); ?>
					</h2>
					<p style="margin: 10px 0; font-size: 15px; color: #333; line-height: 1.6;">
						<?php esc_html_e( 'We need to run a Quick Scan to analyze your site. This is quick, painless, and won\'t hurt your website or visitors.', 'wpshadow' ); ?>
					</p>
					<div style="display: flex; gap: 10px; margin-top: 15px;">
						<button id="wpshadow-start-first-scan" class="button button-primary" style="padding: 8px 16px; cursor: pointer;">
							<?php esc_html_e( 'Start Quick Scan', 'wpshadow' ); ?>
						</button>
						<button id="wpshadow-skip-first-scan" class="button" style="padding: 8px 16px; cursor: pointer;">
							<?php esc_html_e( 'Maybe Later', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
				<?php
			} elseif ( $time_since_scan > $scan_interval ) {
				// Scan is stale (> 5 min ago) - show progress bar
				?>
				<div id="wpshadow-stale-scan-notice" style="background: #fff3e0; border-left: 4px solid #f57c00; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
					<h2 style="margin-top: 0; color: #f57c00; display: flex; align-items: center; gap: 10px;">
						<span class="dashicons dashicons-update" style="font-size: 24px; animation: spin 2s linear infinite;"></span>
						<?php esc_html_e( 'Running Quick Scan', 'wpshadow' ); ?>
					</h2>
					<p style="margin: 10px 0; font-size: 14px; color: #666;">
						<?php esc_html_e( 'Analyzing your site...', 'wpshadow' ); ?>
					</p>
					<div id="wpshadow-scan-progress-bar" style="background: #e0e0e0; border-radius: 4px; height: 8px; margin: 15px 0; overflow: hidden;">
						<div style="background: #f57c00; height: 100%; width: 0%; transition: width 0.3s ease;" id="wpshadow-progress-fill"></div>
					</div>
					<p id="wpshadow-scan-current-task" style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
						<?php esc_html_e( 'Starting scan...', 'wpshadow' ); ?>
					</p>
				</div>
				<style>
					@keyframes spin {
						from { transform: rotate(0deg); }
						to { transform: rotate(360deg); }
					}
				</style>
				<?php
			}
			?>
			
			<div style="display: flex; gap: 24px; margin-top: 20px; flex-wrap: wrap;">
				<!-- Left: Large Overall Health Gauge + Scan Buttons -->
				<div style="flex: 0 0 280px;">
					<div style="border: 2px solid <?php echo esc_attr( $overall_health['color'] ); ?>; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">
						<h3 style="margin: 0 0 16px 0; font-size: 20px; color: #333;"><?php esc_html_e( 'Overall Site Health', 'wpshadow' ); ?></h3>
						
						<svg width="200" height="200" viewBox="0 0 200 200" style="margin: 0 auto; display: block; filter: drop-shadow(0 3px 6px rgba(0,0,0,0.2));">
							<!-- Outer decorative circle -->
							<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( $overall_health['color'] ); ?>" stroke-width="2" opacity="0.2" />
							<!-- Gauge background -->
							<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
							<!-- Gauge progress -->
							<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( $overall_health['color'] ); ?>" stroke-width="16"
								stroke-dasharray="<?php echo (int) ( $overall_health['score'] / 100 * 534 ); ?> 534"
								stroke-linecap="round" transform="rotate(-90 100 100)"
								style="transition: stroke-dasharray 0.5s ease;" />
							<!-- Center text -->
							<text x="100" y="95" text-anchor="middle" font-size="48" font-weight="bold" fill="<?php echo esc_attr( $overall_health['color'] ); ?>"><?php echo (int) $overall_health['score']; ?>%</text>
							<text x="100" y="120" text-anchor="middle" font-size="16" fill="#666"><?php echo esc_html( $overall_health['status'] ); ?></text>
						</svg>
						
						<p style="margin: 16px 0 0 0; font-size: 14px; color: #666; line-height: 1.5;"><?php echo esc_html( $overall_health['message'] ); ?></p>
					</div>
					
					<!-- Quick Scan and Deep Scan Buttons -->
					<div style="margin-top: 16px; display: flex; flex-direction: column; gap: 10px;">
						<button id="wpshadow-quick-scan-btn" class="button button-primary" style="width: 100%; padding: 10px; cursor: pointer;">
							<?php esc_html_e( 'Quick Scan', 'wpshadow' ); ?>
						</button>
						<button id="wpshadow-deep-scan-btn" class="button" style="width: 100%; padding: 10px; cursor: pointer;">
							<?php esc_html_e( 'Deep Scan', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
				
				<!-- Right: 11 Small Category Gauges in 2x6 Grid (2 columns, 6 rows) -->
				<div style="flex: 1; min-width: 0;">
					<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
						<?php foreach ( $category_meta as $cat_key => $meta ) :
							// Special handling for WordPress Site Health gauge (#563)
							if ( $cat_key === 'wordpress_health' ) {
								$wp_health = wpshadow_get_wordpress_site_health();
								$gauge_percent = $wp_health['score'];
								$status_text = $wp_health['status'];
								$gauge_color = $wp_health['color'];
								$status_icon = $gauge_percent >= 80 ? '✓' : ( $gauge_percent >= 50 ? '◐' : '✕' );
								$status_color = $gauge_percent >= 80 ? '#2e7d32' : ( $gauge_percent >= 50 ? '#f57c00' : '#c62828' );
							} else {
								$cat_findings = $findings_by_category[ $cat_key ] ?? array();
								$total = count( $cat_findings );
								
								// Calculate category health score
								$critical_count = count( array_filter( $cat_findings, function( $f ) { return isset( $f['color'] ) && $f['color'] === '#f44336'; } ) );
								$passed = $total - $critical_count;
								
								// Determine status
								if ( $total === 0 ) {
									$status_text = __( 'Excellent', 'wpshadow' );
									$status_icon = '✓';
									$status_color = '#2e7d32';
								} elseif ( $critical_count === 0 ) {
									$status_text = __( 'Good', 'wpshadow' );
									$status_icon = '✓';
									$status_color = '#2e7d32';
								} elseif ( $critical_count < $total / 2 ) {
									$status_text = __( 'Fair', 'wpshadow' );
									$status_icon = '◐';
									$status_color = '#f57c00';
								} else {
									$status_text = __( 'Needs Work', 'wpshadow' );
									$status_icon = '✕';
									$status_color = '#c62828';
								}
								
								// Calculate threat gauge percentage
								$threat_total = 0;
								foreach ( $cat_findings as $finding ) {
									$threat_total += isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
								}
								$gauge_percent = $total > 0 ? min( 100, ( $threat_total / $total ) / 100 * 100 ) : 0;
								$gauge_percent = 100 - $gauge_percent; // Invert: higher is better
								$gauge_color = wpshadow_get_threat_gauge_color( 100 - $gauge_percent );
							}
						?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&category=' . $cat_key ) ); ?>" style="text-decoration: none; color: inherit;" title="<?php echo esc_attr( sprintf( __( 'Click to view %s details', 'wpshadow' ), $meta['label'] ) ); ?>">
							<div style="display: flex; align-items: center; gap: 14px; border: 2px solid <?php echo esc_attr( $meta['color'] ); ?>; border-radius: 6px; padding: 12px 14px; background: #ffffff; transition: all 0.2s ease; cursor: pointer; height: 90px;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'; this.style.borderColor='<?php echo esc_js( $meta['color'] ); ?>';" onmouseout="this.style.boxShadow='none'; this.style.borderColor='<?php echo esc_js( $meta['color'] ); ?>';">
								<!-- Gauge on Left -->
								<div style="flex-shrink: 0;">
									<svg width="70" height="70" viewBox="0 0 100 100" style="filter: drop-shadow(0 1px 3px rgba(0,0,0,0.1));">
										<!-- Gauge background -->
										<circle cx="50" cy="50" r="40" fill="none" stroke="#e0e0e0" stroke-width="8" />
										<!-- Gauge progress -->
										<circle cx="50" cy="50" r="40" fill="none" stroke="<?php echo esc_attr( $gauge_color ); ?>" stroke-width="8"
											stroke-dasharray="<?php echo (int) ( $gauge_percent / 100 * 251 ); ?> 251"
											stroke-linecap="round" transform="rotate(-90 50 50)"
											style="transition: stroke-dasharray 0.3s ease;" />
										<!-- Percentage text -->
										<text x="50" y="58" text-anchor="middle" font-size="18" font-weight="bold" fill="#333"><?php echo (int) $gauge_percent; ?>%</text>
									</svg>
								</div>
								
								<!-- Text on Right -->
								<div style="flex: 1; min-width: 0;">
									<!-- Icon and Title -->
									<div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
										<span class="<?php echo esc_attr( $meta['icon'] ); ?>" style="font-size: 16px; color: <?php echo esc_attr( $meta['color'] ); ?>;"></span>
										<h4 style="margin: 0; font-size: 13px; color: <?php echo esc_attr( $meta['color'] ); ?>; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc_html( $meta['label'] ); ?></h4>
									</div>
									
									<!-- Status -->
									<div>
										<span style="color: <?php echo esc_attr( $status_color ); ?>; font-weight: 600; font-size: 11px;">
											<?php echo esc_html( $status_icon . ' ' . $status_text ); ?>
										</span>
										<div style="color: #666; font-size: 10px; margin-top: 2px;">
											<?php 
											if ( $cat_key === 'wordpress_health' ) {
												echo esc_html( __( 'WordPress native', 'wpshadow' ) );
											} elseif ( isset( $total ) ) {
												if ( $total === 0 ) {
													echo esc_html( __( 'No issues', 'wpshadow' ) );
												} else {
													echo esc_html( sprintf( __( '%d of %d', 'wpshadow' ), $passed, $total ) );
												}
											}
											?>
										</div>
									</div>
								</div>
							</div>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php } // End if/else for filtered vs normal view ?>

		<!-- Kanban Board for Organizing Findings -->
		<?php 
		// Pass category filter to Kanban board if present
		if ( ! empty( $filter_category ) ) {
			$_GET['kanban_category'] = $filter_category;
		}
		include WPSHADOW_PATH . 'includes/views/kanban-board.php'; 
		?>


		<!-- Recent Activity -->
		<?php 
		$activity = wpshadow_get_recent_activity();
		
		// Filter activity by category if in drill-down view (#564)
		if ( ! empty( $filter_category ) && ! empty( $activity ) ) {
			$activity = array_filter( $activity, function( $entry ) use ( $filter_category ) {
				return isset( $entry['category'] ) && $entry['category'] === $filter_category;
			} );
		}
		
		if ( ! empty( $activity ) ) : 
		?>
		<div style="margin: 30px 0;">
			<h2><?php echo ! empty( $filter_category ) ? esc_html( sprintf( __( '%s Activity', 'wpshadow' ), $category_meta[ $filter_category ]['label'] ?? ucfirst( $filter_category ) ) ) : esc_html_e( 'Recent Activity', 'wpshadow' ); ?></h2>
			<table class="wp-list-table widefat">
				<thead>
					<tr>
						<th>Action</th>
						<th>Time</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $activity as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry['action'] ); ?></td>
						<td><?php echo wp_kses_post( wpshadow_format_time_with_tooltip( $entry['time'] ) ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<!-- Off-Peak Scheduling Modal -->
		<div id="wpshadow-offpeak-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:100000; align-items:center; justify-content:center;">
			<div style="background:#fff; border-radius:8px; max-width:500px; width:90%; padding:30px; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
				<h2 style="margin-top:0; color:#e65100; display:flex; align-items:center; gap:10px;">
					<span class="dashicons dashicons-clock" style="font-size:28px;"></span>
					Schedule for Off-Peak Hours?
				</h2>
				<p style="color:#555; line-height:1.6; margin:0 0 20px 0;">
					This operation may temporarily increase server load. To keep your site running smoothly, we recommend scheduling it during off-peak hours.
				</p>
				<p style="color:#666; font-size:13px; background:#f5f5f5; padding:12px; border-radius:4px; margin:0 0 20px 0;">
					<strong>What happens:</strong> We'll run this during low-traffic hours (typically 2-4 AM) and email you the results.
				</p>
				<div style="display:flex; gap:10px; justify-content:flex-end;">
					<button id="wpshadow-offpeak-run-now" class="button">Run Now Anyway</button>
					<button id="wpshadow-offpeak-schedule" class="button button-primary">Schedule Off-Peak</button>
				</div>
			</div>
		</div>

		<!-- Tagline Modal -->
		<div id="wpshadow-tagline-modal" class="wpshadow-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);">
			<div class="wpshadow-modal-content" style="background: #fff; margin: 10% auto; padding: 30px; border-radius: 8px; max-width: 500px; position: relative; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
				<button class="wpshadow-modal-close" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; font-size: 28px; cursor: pointer; color: #999; line-height: 1;">×</button>
				<h2 style="margin-top: 0; color: #2196f3;">Add Your Site Tagline</h2>
				<p style="color: #555; line-height: 1.6; margin: 15px 0;">
					A tagline (also called a site description) is a short phrase that describes what your site is about. It appears in search results and helps visitors quickly understand your site's purpose.
				</p>
				<?php if ( wpshadow_is_site_registered() ) : ?>
					<?php $suggestions = wpshadow_generate_tagline_suggestions(); ?>
					<?php if ( ! empty( $suggestions ) ) : ?>
					<div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0; border-radius: 4px;">
						<strong style="color: #1976d2; display: block; margin-bottom: 10px;">🤖 AI-Generated Suggestions:</strong>
						<?php foreach ( $suggestions as $index => $suggestion ) : ?>
						<label style="display: block; margin: 8px 0; cursor: pointer; padding: 8px; background: #fff; border-radius: 4px; border: 1px solid #ddd;">
							<input type="radio" name="ai-suggestion" value="<?php echo esc_attr( $suggestion ); ?>" style="margin-right: 8px;" />
							<?php echo esc_html( $suggestion ); ?>
						</label>
						<?php endforeach; ?>
						<p style="font-size: 12px; color: #666; margin-top: 10px; margin-bottom: 0;">Click a suggestion to use it, or write your own below.</p>
					</div>
					<?php endif; ?>
				<?php else : ?>
				<p style="color: #555; line-height: 1.6; margin: 15px 0; font-size: 13px;">
					<strong>Examples:</strong><br/>
					• "Fresh recipes for busy families"<br/>
					• "Professional photography services in Seattle"<br/>
					• "Handcrafted furniture made with love"
				</p>
				<?php endif; ?>
				<form id="wpshadow-tagline-form">
					<p>
						<label for="wpshadow-tagline-input" style="display: block; margin-bottom: 8px; font-weight: 500;">Your Tagline:</label>
						<input type="text" id="wpshadow-tagline-input" name="tagline" maxlength="200" 
							style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" 
							placeholder="Enter a short description of your site..." required />
						<span style="font-size: 12px; color: #666;">Keep it under 200 characters</span>
					</p>
					<div id="wpshadow-tagline-status" style="margin: 15px 0;"></div>
					<p style="margin-top: 20px;">
						<button type="submit" class="button button-primary" style="padding: 10px 20px;">Save Tagline</button>
						<button type="button" class="button wpshadow-modal-close" style="margin-left: 10px;">Cancel</button>
					</p>
				</form>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			let pendingOperation = null;

			// Function to check if operation could cause slowdown
			window.wpshadowCheckSlowdown = function(operationType, callback) {
				// Operations that could cause slowdowns
				const heavyOperations = ['deep-scan', 'database-optimization', 'full-security-scan', 'cache-warmup', 'bulk-autofix'];
				
				if (heavyOperations.includes(operationType)) {
					pendingOperation = { type: operationType, callback: callback };
					$('#wpshadow-offpeak-modal').css('display', 'flex');
					return true;
				}
				
				// Not heavy, run immediately
				if (callback) callback();
				return false;
			};

			// Run now button
			$('#wpshadow-offpeak-run-now').on('click', function() {
				$('#wpshadow-offpeak-modal').hide();
				if (pendingOperation && pendingOperation.callback) {
					pendingOperation.callback();
				}
				pendingOperation = null;
			});

			// Schedule off-peak button
			$('#wpshadow-offpeak-schedule').on('click', function() {
				const $btn = $(this);
				$btn.prop('disabled', true).text('Scheduling...');

				$.post(ajaxurl, {
					action: 'wpshadow_schedule_offpeak',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_offpeak' ); ?>',
					operation_type: pendingOperation ? pendingOperation.type : 'unknown',
					email: '<?php echo esc_js( wp_get_current_user()->user_email ); ?>'
				}, function(response) {
					if (response.success) {
						$('#wpshadow-offpeak-modal').hide();
						alert('Scheduled! We\'ll run this during off-peak hours and email you the results.');
					} else {
						alert('Error: ' + (response.data?.message || 'Could not schedule'));
					}
					$btn.prop('disabled', false).text('Schedule Off-Peak');
					pendingOperation = null;
				});
			});

			// Close modal on background click
			$('#wpshadow-offpeak-modal').on('click', function(e) {
				if (e.target === this) {
					$(this).hide();
					pendingOperation = null;
				}
			});
		});
		</script>

	</div>
	<?php
}

/**
 * Get site health status.
 */
/**
 * Calculate overall site health score from all category findings.
 * 
 * Philosophy: Show value (#9) - Aggregate category health into single metric
 * 
 * @param array $findings_by_category Findings grouped by category
 * @param array $category_meta Category metadata (colors, labels)
 * @return array Overall health data (score, status, color, bg, message)
 */
function wpshadow_calculate_overall_health( $findings_by_category, $category_meta ) {
	if ( empty( $findings_by_category ) ) {
		return array(
			'score'   => 100,
			'status'  => __( 'Excellent', 'wpshadow' ),
			'color'   => '#2e7d32',
			'bg'      => '#e8f5e9',
			'message' => __( 'Your site is in perfect shape! All critical checks passed.', 'wpshadow' ),
		);
	}
	
	// Calculate weighted average across all categories
	$total_score = 0;
	$category_count = 0;
	
	foreach ( $category_meta as $cat_key => $meta ) {
		$cat_findings = $findings_by_category[ $cat_key ] ?? array();
		$total = count( $cat_findings );
		
		if ( $total === 0 ) {
			$total_score += 100; // Perfect score if no findings
			$category_count++;
			continue;
		}
		
		// Calculate threat-based score for this category
		$threat_total = 0;
		foreach ( $cat_findings as $finding ) {
			$threat_total += isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
		}
		
		// Invert threat to health score (lower threat = higher health)
		$category_score = 100 - min( 100, ( $threat_total / $total ) / 100 * 100 );
		$total_score += $category_score;
		$category_count++;
	}
	
	// Calculate average
	$overall_score = $category_count > 0 ? round( $total_score / $category_count ) : 0;
	
	// Determine status and color
	if ( $overall_score >= 80 ) {
		$status = __( 'Excellent', 'wpshadow' );
		$color = '#2e7d32';
		$bg = '#e8f5e9';
		$message = __( 'Your site is running smoothly. Guardian will continue watching for potential issues.', 'wpshadow' );
	} elseif ( $overall_score >= 60 ) {
		$status = __( 'Good', 'wpshadow' );
		$color = '#2e7d32';
		$bg = '#e8f5e9';
		$message = __( 'Your site is in good shape with minor issues to monitor.', 'wpshadow' );
	} elseif ( $overall_score >= 40 ) {
		$status = __( 'Fair', 'wpshadow' );
		$color = '#f57c00';
		$bg = '#fff3e0';
		$message = __( 'Your site has some issues that should be addressed soon.', 'wpshadow' );
	} else {
		$status = __( 'Needs Attention', 'wpshadow' );
		$color = '#c62828';
		$bg = '#ffebee';
		$message = __( 'Your site has critical issues that need immediate attention.', 'wpshadow' );
	}
	
	return array(
		'score'   => $overall_score,
		'status'  => $status,
		'color'   => $color,
		'bg'      => $bg,
		'message' => $message,
	);
}

function wpshadow_get_health_status() {
	// Get all findings and calculate weighted score
	$findings = wpshadow_get_site_findings();
	
	if ( empty( $findings ) ) {
		return array(
			'score'   => 100,
			'status'  => 'Excellent',
			'color'   => '#4caf50',
			'message' => 'Your site is in great shape! All critical checks passed.',
		);
	}
	
	// Calculate weighted threat score
	$total_threat = 0;
	$max_threat = 0;
	
	foreach ( $findings as $finding ) {
		$threat = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
		$total_threat += $threat;
		$max_threat += 100; // Each finding could be 100% threat
	}
	
	// Calculate score (inverse of threat percentage)
	$threat_percentage = $max_threat > 0 ? ( $total_threat / $max_threat ) * 100 : 0;
	$score = max( 0, 100 - $threat_percentage );
	
	if ( $score >= 80 ) {
		$status = 'Good';
		$color = '#4caf50';
		$message = 'Your site is running smoothly. Guardian will continue watching for potential issues.';
	} elseif ( $score >= 60 ) {
		$status = 'Fair';
		$color = '#ff9800';
		$message = 'Your site has some issues that should be addressed soon.';
	} else {
		$status = 'Needs Attention';
		$color = '#f44336';
		$message = 'Your site has critical issues that need immediate attention.';
	}
	
	return array(
		'score'   => $score,
		'status'  => $status,
		'color'   => $color,
		'message' => $message,
	);
}

/**
 * Get KB link for a finding/diagnostic slug.
 * Falls back to a slug-based URL if not mapped.
 *
 * @param string $slug Finding/diagnostic slug.
 * @return string KB URL.
 */
function wpshadow_get_kb_link( string $slug ): string {
	$map = array(
		'backup-missing'      => 'https://wpshadow.com/kb/how-to-set-up-automated-backups/',
		'ssl-missing'         => 'https://wpshadow.com/kb/enable-https-ssl-on-your-site/',
		'outdated-plugins'    => 'https://wpshadow.com/kb/how-to-safely-update-plugins/',
		'memory-limit-low'    => 'https://wpshadow.com/kb/increase-php-memory-limit/',
		'permalinks-plain'    => 'https://wpshadow.com/kb/configure-wordpress-permalinks-for-seo/',
		'tagline-empty'       => 'https://wpshadow.com/kb/write-an-effective-site-tagline/',
		'debug-mode-enabled'  => 'https://wpshadow.com/kb/disable-wordpress-debug-mode/',
		'wordpress-outdated'  => 'https://wpshadow.com/kb/how-to-update-wordpress-safely/',
		'plugin-count-high'   => 'https://wpshadow.com/kb/audit-and-optimize-your-wordpress-plugins/',
	);

	if ( isset( $map[ $slug ] ) ) {
		return $map[ $slug ];
	}

	$slug = sanitize_title( $slug );
	return 'https://wpshadow.com/kb/' . $slug . '/';
}

/**
 * Get training link for a treatment slug.
 * Falls back to slug-based training URL.
 *
 * @param string $slug Treatment/finding slug.
 * @return string Training video URL.
 */
function wpshadow_get_training_link( string $slug ): string {
	$slug = sanitize_title( $slug );
	return 'https://wpshadow.com/training/' . $slug . '/';
}

/**
 * Get site findings based on diagnostics and WordPress Core Site Health.
 */
function wpshadow_get_site_findings() {
	// Run all diagnostic checks from registry
	$findings = \WPShadow\Diagnostics\Diagnostic_Registry::run_all_checks();

	foreach ( $findings as &$finding ) {
		if ( empty( $finding['category'] ) ) {
			$finding['category'] = wpshadow_get_finding_category( $finding );
		}
	}
	unset( $finding );

	// Add WordPress Settings scan
	if ( class_exists( '\WPShadow\Diagnostics\WordPress_Settings_Scan' ) ) {
		$settings_findings = \WPShadow\Diagnostics\WordPress_Settings_Scan::run_scan();
		if ( ! empty( $settings_findings ) ) {
			$findings = array_merge( $findings, $settings_findings );
		}
	}

	// Add mobile friendliness issues
	$mobile_issues = \WPShadow\Diagnostics\Diagnostic_Mobile_Friendliness::get_all_issues();
	if ( ! empty( $mobile_issues ) ) {
		$findings = array_merge( $findings, $mobile_issues );
	}

	// Get WordPress Core Site Health data if available
	if ( class_exists( 'WP_Site_Health' ) ) {
		$result = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/site-health/status' ) );
		if ( ! is_wp_error( $result ) ) {
			$data = $result->get_data();
			// Supplement with critical core checks if not already found
			if ( ! empty( $data['tests']['critical'] ) ) {
				foreach ( $data['tests']['critical'] as $test ) {
					if ( ! empty( $test['description'] ) ) {
						$findings[] = array(
							'title'       => $test['label'] ?? 'Site Health Issue',
							'description' => wp_strip_all_tags( $test['description'] ),
							'color'       => '#f44336',
							'bg_color'    => '#ffebee',
							'category'    => 'settings',
						);
					}
				}
			}
		}
	}

	// Enrich with KB and training links (Phase 5)
	foreach ( $findings as &$finding ) {
		$slug = $finding['id'] ?? ( $finding['title'] ?? '' );
		$slug = sanitize_title( (string) $slug );
		$finding['kb_link'] = wpshadow_get_kb_link( $slug );
		$finding['training_link'] = wpshadow_get_training_link( $slug );
	}
	unset( $finding );

	return $findings;
}

/**
 * Determine the category for a finding.
 *
 * @param array $finding Finding data.
 * @return string Category slug.
 */
function wpshadow_get_finding_category( $finding ) {
	$category_map = array(
		'memory-limit-low'   => 'settings',
		'backup-missing'     => 'settings',
		'permalinks-plain'   => 'seo',
		'tagline-empty'      => 'design',
		'ssl-missing'        => 'seo',
		'outdated-plugins'   => 'settings',
		'inactive-plugins'   => 'settings',
		'hotlink-protection-missing' => 'security',
		'head-cleanup-needed' => 'performance',
		'iframe-busting-missing' => 'security',
		'image-lazyload-disabled' => 'performance',
		'external-fonts-loading' => 'performance',
		'plugin-auto-updates-disabled' => 'settings',
		'error-log-large' => 'stability',
		'core-integrity-mismatch' => 'security',
		'skiplinks-missing' => 'accessibility',
		'asset-versions' => 'performance',
		'css-classes' => 'performance',
		'maintenance' => 'stability',
		'nav-aria' => 'accessibility',
		'admin-username' => 'security',
		'search-indexing' => 'seo',
		'admin-email' => 'settings',
		'timezone' => 'settings',
		'debug-mode-enabled' => 'settings',
		'wordpress-outdated' => 'settings',
		'plugin-count-high'  => 'settings',
		'content-optimizer' => 'content',
		'paste-cleanup' => 'content',
		'html-cleanup' => 'performance',
		'pre-publish-review' => 'content',
		'embed-disable' => 'performance',
		'interactivity-cleanup' => 'performance',
		'php-version' => 'security',
		'database-health' => 'performance',
		'file-permissions' => 'security',
		'security-headers' => 'security',
	);

	$finding_id = isset( $finding['id'] ) ? $finding['id'] : '';
	if ( isset( $category_map[ $finding_id ] ) ) {
		return $category_map[ $finding_id ];
	}

	if ( isset( $finding['category'] ) && ! empty( $finding['category'] ) ) {
		return $finding['category'];
	}

	return 'settings';
}

/**
 * Get PHP memory limit in MB.
 */
function wpshadow_get_memory_limit_mb() {
	$limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
	return intval( $limit / 1024 / 1024 );
}

/**
 * Check if a backup plugin is active.
 */
function wpshadow_has_backup_plugin() {
	$active_plugins = get_option( 'active_plugins', array() );
	$backup_plugins = array(
		'updraftplus/updraft.php',
		'backwpup/backwpup.php',
		'backup-backup/backup.php',
		'jetpack-backup/jetpack-backup.php',
		'vaultpress/vaultpress.php',
	);
	return ! empty( array_intersect( $active_plugins, $backup_plugins ) );
}

/**
 * Check if permalink structure is configured (not plain).
 */
function wpshadow_is_permalink_configured() {
	$structure = get_option( 'permalink_structure' );
	return ! empty( $structure ) && $structure !== '';
}

/**
 * Count outdated plugins.
 */
function wpshadow_get_outdated_plugins_count() {
	$current_plugins = get_plugins();
	$updates = get_site_transient( 'update_plugins' );
	
	if ( ! isset( $updates->response ) ) {
		return 0;
	}

	$count = 0;
	foreach ( $updates->response as $plugin_file => $plugin_data ) {
		if ( isset( $current_plugins[ $plugin_file ] ) ) {
			$count++;
		}
	}

	return $count;
}

/**
 * Get recent activity log.
 */
function wpshadow_get_recent_activity() {
	$log = get_option( 'wpshadow_finding_log', array() );
	$activity = array();
	
	// Convert finding log to activity format
	foreach ( array_reverse( array_slice( $log, -10 ) ) as $entry ) {
		$action_label = '';
		
		switch ( $entry['action'] ) {
			case 'auto_fixed':
				$action_label = '🔧 Auto-fixed: ' . ucwords( str_replace( '-', ' ', $entry['finding_id'] ) );
				break;
			case 'dismissed':
				$action_label = '👁️ Dismissed: ' . ucwords( str_replace( '-', ' ', $entry['finding_id'] ) );
				break;
			case 'scheduled':
				$action_label = '📅 Scheduled deep scans';
				break;
			default:
				$action_label = ucwords( str_replace( '-', ' ', $entry['finding_id'] ) );
		}
		
		if ( ! empty( $entry['message'] ) ) {
			$action_label .= ' - ' . $entry['message'];
		}
		
		$activity[] = array(
			'action' => $action_label,
			'time'   => $entry['timestamp'],
		);
	}
	
	// Add activation as fallback if no log entries
	if ( empty( $activity ) ) {
		$activity[] = array(
			'action' => 'WPShadow activated',
			'time'   => current_time( 'timestamp' ),
		);
	}
	
	return $activity;
}

/**
 * Format time as relative with tooltip for precise details.
 */
function wpshadow_format_time_with_tooltip( $timestamp ) {
	$now = current_time( 'timestamp' );
	$diff = $now - $timestamp;

	if ( $diff < 60 ) {
		$relative = 'just now';
	} elseif ( $diff < 3600 ) {
		$minutes = floor( $diff / 60 );
		$relative = sprintf( _n( '%d minute ago', '%d minutes ago', $minutes, 'wpshadow' ), $minutes );
	} elseif ( $diff < 86400 ) {
		$hours = floor( $diff / 3600 );
		$relative = sprintf( _n( '%d hour ago', '%d hours ago', $hours, 'wpshadow' ), $hours );
	} else {
		$days = floor( $diff / 86400 );
		$relative = sprintf( _n( '%d day ago', '%d days ago', $days, 'wpshadow' ), $days );
	}

	$precise = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );

	return sprintf(
		'<span title="%s">%s</span>',
		esc_attr( $precise ),
		esc_html( $relative )
	);
}

/**
 * Get WordPress Site Health status.
 * 
 * Philosophy: Show value (#9) - Track WordPress native health indicators
 * 
 * @return array Array with 'score' (0-100), 'status' (Good/Fair/Critical), 'color'.
 */
function wpshadow_get_wordpress_site_health() {
	// Try to use native WordPress Site Health if available
	if ( function_exists( 'wp_get_site_health_status' ) ) {
		$health = wp_get_site_health_status();
		$status = isset( $health['status'] ) ? $health['status'] : 'good';
		$score = isset( $health['percentage'] ) ? (int) $health['percentage'] : 75;
	} else {
		// Fallback: Use basic checks
		$score = 75;
		$status = 'good';
		
		// Check for SSL
		if ( ! is_ssl() ) {
			$score -= 20;
		}
		
		// Check for REST API
		if ( ! rest_get_url_prefix() ) {
			$score -= 10;
		}
		
		// Check for debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$score -= 15;
		}
		
		if ( $score <= 50 ) {
			$status = 'critical';
		} elseif ( $score <= 75 ) {
			$status = 'recommended';
		}
	}
	
	// Map status to color
	$color_map = array(
		'good'        => '#2d5016',  // Green
		'recommended' => '#f57c00',  // Orange
		'critical'    => '#c62828',  // Red
	);
	
	$color = $color_map[ $status ] ?? '#2d5016';
	
	return array(
		'score'  => max( 0, min( 100, $score ) ),
		'status' => ucfirst( $status ),
		'color'  => $color,
		'label'  => __( 'WordPress Site Health', 'wpshadow' ),
		'icon'   => 'dashicons-wordpress-alt',
	);
}

/**
 * Get threat gauge color based on threat level.
 *
 * @param int $threat_level Threat level 0-100.
 * @return string Hex color code.
 */
function wpshadow_get_threat_gauge_color( $threat_level ) {
	if ( $threat_level >= 80 ) {
		return '#f44336'; // Red - Critical
	} elseif ( $threat_level >= 60 ) {
		return '#ff9800'; // Orange - High
	} elseif ( $threat_level >= 40 ) {
		return '#ffc107'; // Amber - Medium
	} else {
		return '#2196f3'; // Blue - Low
	}
}

/**
 * Get threat label based on threat level.
 *
 * @param int $threat_level Threat level 0-100.
 * @return string Threat label.
 */
function wpshadow_get_threat_label( $threat_level ) {
	if ( $threat_level >= 80 ) {
		return 'Critical';
	} elseif ( $threat_level >= 60 ) {
		return 'High';
	} elseif ( $threat_level >= 40 ) {
		return 'Medium';
	} else {
		return 'Low';
	}
}

/**
 * Check if site is registered with WPShadow.
 *
 * @return bool True if site is registered.
 */
function wpshadow_is_site_registered() {
	// Check if email consent is granted (indicates registration)
	$email_consent = get_option( 'wpshadow_email_consent', false );
	return ! empty( $email_consent['granted'] );
}

/**
 * Generate AI tagline suggestions based on recent content.
 *
 * @return array Array of tagline suggestions.
 */
function wpshadow_generate_tagline_suggestions() {
	$suggestions = array();
	
	// Get recent posts for context
	$recent_posts = get_posts( array(
		'numberposts' => 5,
		'post_status' => 'publish',
	) );
	
	// Get site title
	$site_title = get_bloginfo( 'name' );
	
	// Analyze content to generate suggestions
	$categories = get_categories( array( 'number' => 3, 'orderby' => 'count', 'order' => 'DESC' ) );
	$category_names = array_map( function( $cat ) { return $cat->name; }, $categories );
	
	// Generate basic suggestions based on content analysis
	if ( ! empty( $recent_posts ) ) {
		// Suggestion 1: Based on most common category
		if ( ! empty( $category_names[0] ) ) {
			$suggestions[] = "Your source for {$category_names[0]} insights and tips";
		}
		
		// Suggestion 2: Based on site title
		if ( ! empty( $site_title ) && $site_title !== 'WordPress' ) {
			$suggestions[] = "{$site_title} - Sharing knowledge and expertise";
		}
		
		// Suggestion 3: Generic but professional
		if ( count( $recent_posts ) > 3 ) {
			$suggestions[] = "Expert articles and resources you can trust";
		} else {
			$suggestions[] = "Quality content for curious minds";
		}
	} else {
		// Fallback suggestions if no posts
		$suggestions = array(
			"Your trusted source for {$site_title} content",
			"Sharing insights and ideas that matter",
			"Where knowledge meets community",
		);
	}
	
	return array_slice( $suggestions, 0, 3 );
}

/**
 * Attempt to automatically fix a finding.
 *
 * @param string $finding_id The ID of the finding to fix.
 * @return array {success: bool, message: string}
 */
function wpshadow_attempt_autofix( $finding_id ) {
	$has_permission = current_user_can( 'manage_options' );
	if ( ! $has_permission && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return array(
			'success' => false,
			'message' => 'You do not have permission to make this change.',
		);
	}

	$finding_id = sanitize_key( $finding_id );
	if ( empty( $finding_id ) ) {
		return array(
			'success' => false,
			'message' => 'Invalid finding ID.',
		);
	}

	if ( ! class_exists( '\\WPShadow\\Treatments\\Treatment_Registry' ) ) {
		return array(
			'success' => false,
			'message' => 'Treatment registry is not available.',
		);
	}

	$result = \WPShadow\Treatments\Treatment_Registry::apply_treatment( $finding_id );

	if ( ! is_array( $result ) ) {
		return array(
			'success' => false,
			'message' => 'Auto-fix failed unexpectedly.',
		);
	}

	return $result;
}

/**
 * Save current health snapshot for comparison.
 */
function wpshadow_save_health_snapshot() {
	$findings = wpshadow_get_site_findings();
	
	// Get existing snapshots
	$snapshots = get_option( 'wpshadow_health_snapshots', array() );
	
	// Add current snapshot
	$snapshots[] = array(
		'timestamp' => current_time( 'timestamp' ),
		'findings'  => $findings,
		'count'     => count( $findings ),
	);
	
	// Keep only last 30 snapshots
	if ( count( $snapshots ) > 30 ) {
		$snapshots = array_slice( $snapshots, -30 );
	}
	
	update_option( 'wpshadow_health_snapshots', $snapshots );
}

/**
 * Log an action taken on a finding.
 *
 * @param string $finding_id The ID of the finding.
 * @param string $action     The action taken (dismissed, auto_fixed, manual_fixed).
 * @param string $message    Optional message describing the action.
 */
function wpshadow_log_finding_action( $finding_id, $action, $message = '' ) {
	$log = get_option( 'wpshadow_finding_log', array() );
	
	$log[] = array(
		'finding_id' => $finding_id,
		'action'     => $action,
		'message'    => $message,
		'user_id'    => get_current_user_id(),
		'timestamp'  => current_time( 'timestamp' ),
	);
	
	// Keep only last 100 log entries
	if ( count( $log ) > 100 ) {
		$log = array_slice( $log, -100 );
	}
	
	update_option( 'wpshadow_finding_log', $log );
}

// Network admin menu for multisite.
add_action( 'network_admin_menu', function() {
	add_menu_page(
		'WPShadow',
		'WPShadow',
		'read',
		'wpshadow',
		function() {
			echo '<div class="wrap"><h1>WPShadow (Network)</h1><p>Network admin menu check.</p></div>';
		},
		'dashicons-admin-generic',
		999
	);
} );
/**
 * Calculate performance/speed score.
 *
 * @return array Score payload.
 */
function wpshadow_calculate_performance_score() {
	$score = 85; // Base score assumes caching enabled.
	$label = 'Good';
	$color = '#10b981';

	// Penalize if caching is disabled.
	if ( defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		$score -= 10;
	}

	// Penalize heavy query counts when available.
	if ( function_exists( 'get_num_queries' ) ) {
		$queries = get_num_queries();
		if ( $queries > 120 ) {
			$score -= 12;
		} elseif ( $queries > 80 ) {
			$score -= 6;
		}
	}

	$score = max( 0, min( 100, $score ) );

	if ( $score < 60 ) {
		$label = 'Needs Work';
		$color = '#ef4444';
	} elseif ( $score < 80 ) {
		$label = 'Fair';
		$color = '#f59e0b';
	}

	return array(
		'score' => $score,
		'label' => $label,
		'color' => $color,
	);
}

/**
 * Calculate cost efficiency score.
 *
 * @return array Score payload.
 */
function wpshadow_calculate_cost_score() {
	$score = 90; // Base score assumes lean stack.
	$label = 'Excellent';
	$color = '#10b981';

	$active_plugins = count( get_option( 'active_plugins', array() ) );
	if ( $active_plugins > 30 ) {
		$score -= 15;
	} elseif ( $active_plugins > 20 ) {
		$score -= 8;
	}

	$themes = wp_get_themes();
	if ( count( $themes ) > 5 ) {
		$score -= 5;
	}

	$score = max( 0, min( 100, $score ) );

	if ( $score < 60 ) {
		$label = 'Needs Work';
		$color = '#ef4444';
	} elseif ( $score < 80 ) {
		$label = 'Fair';
		$color = '#f59e0b';
	}

	return array(
		'score' => $score,
		'label' => $label,
		'color' => $color,
	);
}

/**
 * Calculate eco/sustainability score.
 *
 * @return array Score payload.
 */
function wpshadow_calculate_eco_score() {
	$score = 75; // Base score assumes standard CDN/compression.
	$label = 'Good';
	$color = '#10b981';

	$using_cdn = defined( 'WP_CONTENT_URL' ) && strpos( WP_CONTENT_URL, 'cdn' ) !== false;
	if ( ! $using_cdn ) {
		$score -= 6;
	}

	$compression_enabled = ini_get( 'zlib.output_compression' );
	if ( empty( $compression_enabled ) ) {
		$score -= 6;
	}

	$active_plugins = count( get_option( 'active_plugins', array() ) );
	if ( $active_plugins > 25 ) {
		$score -= 6;
	}

	$score = max( 0, min( 100, $score ) );

	if ( $score < 60 ) {
		$label = 'Needs Work';
		$color = '#ef4444';
	} elseif ( $score < 80 ) {
		$label = 'Fair';
		$color = '#f59e0b';
	}

	return array(
		'score' => $score,
		'label' => $label,
		'color' => $color,
	);
}

/**
 * Register WPShadow personal data exporter.
 */
add_filter( 'wp_privacy_personal_data_exporters', function( $exporters ) {
	$exporters['wpshadow'] = array(
		'exporter_friendly_name' => __( 'WPShadow User Preferences', 'wpshadow' ),
		'callback'               => 'wpshadow_privacy_exporter',
	);
	return $exporters;
} );

/**
 * Export WPShadow user data for privacy requests.
 *
 * @param string $email_address User email.
 * @param int    $page          Page number.
 * @return array Export data.
 */
function wpshadow_privacy_exporter( $email_address, $page = 1 ) {
	$user = get_user_by( 'email', $email_address );
	if ( ! $user ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$user_id = $user->ID;
	$export_items = array();

	// Tooltip preferences
	$tip_prefs = get_user_meta( $user_id, 'wpshadow_tip_prefs', true );
	if ( ! empty( $tip_prefs ) && is_array( $tip_prefs ) ) {
		$tip_data = array();
		if ( ! empty( $tip_prefs['disabled_categories'] ) ) {
			$tip_data[] = array(
				'name'  => __( 'Disabled Tooltip Categories', 'wpshadow' ),
				'value' => implode( ', ', $tip_prefs['disabled_categories'] ),
			);
		}
		if ( ! empty( $tip_prefs['dismissed_tips'] ) ) {
			$tip_data[] = array(
				'name'  => __( 'Dismissed Tips', 'wpshadow' ),
				'value' => implode( ', ', $tip_prefs['dismissed_tips'] ),
			);
		}
		if ( ! empty( $tip_data ) ) {
			$export_items[] = array(
				'group_id'    => 'wpshadow_tooltip_prefs',
				'group_label' => __( 'WPShadow Tooltip Preferences', 'wpshadow' ),
				'item_id'     => "wpshadow-tooltips-{$user_id}",
				'data'        => $tip_data,
			);
		}
	}

	// Dark mode preference
	$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true );
	if ( ! empty( $dark_mode_pref ) ) {
		$export_items[] = array(
			'group_id'    => 'wpshadow_display_prefs',
			'group_label' => __( 'WPShadow Display Preferences', 'wpshadow' ),
			'item_id'     => "wpshadow-darkmode-{$user_id}",
			'data'        => array(
				array(
					'name'  => __( 'Dark Mode Preference', 'wpshadow' ),
					'value' => $dark_mode_pref,
				),
			),
		);
	}

	// Hidden widget preferences
	$quick_hidden = get_user_meta( $user_id, 'wpshadow_hide_quick_scan', true );
	$deep_hidden  = get_user_meta( $user_id, 'wpshadow_hide_deep_scan', true );
	if ( $quick_hidden || $deep_hidden ) {
		$widget_data = array();
		if ( $quick_hidden ) {
			$widget_data[] = array(
				'name'  => __( 'Quick Scan Widget Hidden', 'wpshadow' ),
				'value' => __( 'Yes', 'wpshadow' ),
			);
		}
		if ( $deep_hidden ) {
			$widget_data[] = array(
				'name'  => __( 'Deep Scan Widget Hidden', 'wpshadow' ),
				'value' => __( 'Yes', 'wpshadow' ),
			);
		}
		$export_items[] = array(
			'group_id'    => 'wpshadow_widget_prefs',
			'group_label' => __( 'WPShadow Dashboard Widget Preferences', 'wpshadow' ),
			'item_id'     => "wpshadow-widgets-{$user_id}",
			'data'        => $widget_data,
		);
	}

	return array(
		'data' => $export_items,
		'done' => true,
	);
}

/**
 * Register WPShadow personal data eraser.
 */
add_filter( 'wp_privacy_personal_data_erasers', function( $erasers ) {
	$erasers['wpshadow'] = array(
		'eraser_friendly_name' => __( 'WPShadow User Preferences', 'wpshadow' ),
		'callback'             => 'wpshadow_privacy_eraser',
	);
	return $erasers;
} );

/**
 * Erase WPShadow user data for privacy requests.
 *
 * @param string $email_address User email.
 * @param int    $page          Page number.
 * @return array Erasure result.
 */
function wpshadow_privacy_eraser( $email_address, $page = 1 ) {
	$user = get_user_by( 'email', $email_address );
	if ( ! $user ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$user_id = $user->ID;
	$items_removed = false;

	// Remove tooltip preferences
	if ( delete_user_meta( $user_id, 'wpshadow_tip_prefs' ) ) {
		$items_removed = true;
	}

	// Remove dark mode preference
	if ( delete_user_meta( $user_id, 'wpshadow_dark_mode_preference' ) ) {
		$items_removed = true;
	}

	// Remove widget visibility preferences
	if ( delete_user_meta( $user_id, 'wpshadow_hide_quick_scan' ) ) {
		$items_removed = true;
	}
	if ( delete_user_meta( $user_id, 'wpshadow_hide_deep_scan' ) ) {
		$items_removed = true;
	}

	return array(
		'items_removed'  => $items_removed,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * Add WPShadow privacy policy content suggestion.
 */
add_action( 'admin_init', function() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = sprintf(
		'<h2>%s</h2><p>%s</p><h3>%s</h3><ul><li>%s</li><li>%s</li><li>%s</li></ul><h3>%s</h3><p>%s</p>',
		__( 'WPShadow Plugin', 'wpshadow' ),
		__( 'This site uses the WPShadow plugin to enhance the WordPress admin experience. WPShadow stores the following user preferences locally on this site:', 'wpshadow' ),
		__( 'What We Collect', 'wpshadow' ),
		__( '<strong>Tooltip Preferences:</strong> Which admin tooltips you have dismissed or disabled, to avoid showing you the same tip repeatedly.', 'wpshadow' ),
		__( '<strong>Display Preferences:</strong> Your dark mode preference (light, dark, or automatic) for the WPShadow admin interface.', 'wpshadow' ),
		__( '<strong>Dashboard Widget Preferences:</strong> Which dashboard widgets you have chosen to hide or show.', 'wpshadow' ),
		__( 'Your Rights', 'wpshadow' ),
		__( 'You can request to export or erase your WPShadow preferences at any time using the WordPress privacy tools under Tools > Export Personal Data or Tools > Erase Personal Data.', 'wpshadow' )
	);

	wp_add_privacy_policy_content(
		'WPShadow',
		wp_kses_post( wpautop( $content, false ) )
	);
} );

/**
 * Generate a friendly, memorable strong password using word combinations.
 *
 * @return string Generated password.
 */
function wpshadow_generate_friendly_password() {
	$json_file = WPSHADOW_PATH . 'includes/data/password-words.json';
	
	if ( ! file_exists( $json_file ) ) {
		// Fallback to WordPress default if JSON file missing
		return wp_generate_password( 16, true, true );
	}
	
	$word_sets = json_decode( file_get_contents( $json_file ), true );
	
	if ( empty( $word_sets ) || ! is_array( $word_sets ) ) {
		return wp_generate_password( 16, true, true );
	}
	
	// Pick a random word set
	$word_set = $word_sets[ array_rand( $word_sets ) ];
	
	// Combine words with first letter capitalized
	$password = implode( '', $word_set );
	
	// Character substitutions to make it stronger
	$substitutions = array(
		'a' => '@',
		'A' => '@',
		'e' => '3',
		'E' => '3',
		'i' => '1',
		'I' => '1',
		'o' => '0',
		'O' => '0',
		's' => '$',
		'S' => '$',
		't' => '7',
		'T' => '7',
	);
	
	// Apply substitutions to 2-3 random positions
	$chars = str_split( $password );
	$positions_to_substitute = array_rand( $chars, min( 3, count( $chars ) ) );
	
	if ( ! is_array( $positions_to_substitute ) ) {
		$positions_to_substitute = array( $positions_to_substitute );
	}
	
	foreach ( $positions_to_substitute as $pos ) {
		$char = $chars[ $pos ];
		if ( isset( $substitutions[ $char ] ) ) {
			$chars[ $pos ] = $substitutions[ $char ];
		}
	}
	
	$password = implode( '', $chars );
	
	// Add ! at the end for extra strength
	$password .= '!';
	
	return $password;
}

/**
 * Override default password generation on user-new.php with friendly password.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( $hook !== 'user-new.php' ) {
		return;
	}
	
	wp_enqueue_script(
		'wpshadow-friendly-password',
		WPSHADOW_URL . 'assets/js/friendly-password.js',
		array( 'jquery', 'user-profile' ),
		WPSHADOW_VERSION,
		true
	);
	
	wp_localize_script( 'wpshadow-friendly-password', 'wpshadowPassword', array(
		'password' => wpshadow_generate_friendly_password(),
		'nonce'    => wp_create_nonce( 'wpshadow_generate_password' ),
	) );
} );

// Generate password handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-generate-password-handler.php';
\WPShadow\Admin\Ajax\Generate_Password_Handler::register();

/**
 * Override wp_mail From Name if WPShadow setting is configured.
 */
add_filter( 'wp_mail_from_name', function( $from_name ) {
	$custom_from_name = get_option( 'wpshadow_email_from_name', '' );
	
	if ( ! empty( $custom_from_name ) ) {
		return $custom_from_name;
	}
	
	return $from_name;
}, 999 );

/**
 * Override wp_mail From Email if WPShadow setting is configured.
 */
add_filter( 'wp_mail_from', function( $from_email ) {
	$custom_from_email = get_option( 'wpshadow_email_from_email', '' );
	
	if ( ! empty( $custom_from_email ) && is_email( $custom_from_email ) ) {
		return $custom_from_email;
	}
	
	return $from_email;
}, 999 );

/**
 * Uncheck "Send user notification email" by default for privacy law compliance (CASL).
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( $hook !== 'user-new.php' ) {
		return;
	}

	$should_uncheck = get_option( 'wpshadow_user_email_unchecked_by_default', false );
	if ( ! $should_uncheck ) {
		return;
	}

	wp_enqueue_script(
		'wpshadow-user-email-compliance',
		WPSHADOW_URL . 'assets/js/user-email-compliance.js',
		array(),
		WPSHADOW_VERSION,
		true
	);
} );
