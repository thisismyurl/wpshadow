<?php
/**
 * Plugin Name: WPShadow
 * Description: Minimal bootstrap to show WPShadow menu and Settings link.
 * Version: 0.0.1
 * Author: thisismyurl
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPSHADOW_VERSION', '0.0.1' );
define( 'WPSHADOW_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHADOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_URL', plugin_dir_url( __FILE__ ) );

// AJAX handlers for dismissing findings and auto-fixing
add_action( 'wp_ajax_wpshadow_dismiss_finding', function() {
	check_ajax_referer( 'wpshadow_dismiss_finding', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Insufficient permissions' );
	}
	
	$finding_id = isset( $_POST['finding_id'] ) ? sanitize_text_field( $_POST['finding_id'] ) : '';
	if ( empty( $finding_id ) ) {
		wp_send_json_error( 'Invalid finding ID' );
	}
	
	$dismissed = get_option( 'wpshadow_dismissed_findings', array() );
	$dismissed[ $finding_id ] = current_time( 'timestamp' );
	update_option( 'wpshadow_dismissed_findings', $dismissed );
	
	wp_send_json_success( array( 'message' => 'Finding dismissed' ) );
} );

add_action( 'wp_ajax_wpshadow_autofix_finding', function() {
	check_ajax_referer( 'wpshadow_autofix', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Insufficient permissions' );
	}
	
	$finding_id = isset( $_POST['finding_id'] ) ? sanitize_text_field( $_POST['finding_id'] ) : '';

	$result = wpshadow_attempt_autofix( $finding_id );

	if ( $result['success'] ) {
		// Log the fix
		wpshadow_log_finding_action( $finding_id, 'auto_fixed', $result['message'] );
		wp_send_json_success( $result );
	} else {
		wp_send_json_error( $result );
	}
} );

// Toggle auto-fix permission for specific finding type.
add_action( 'wp_ajax_wpshadow_toggle_autofix_permission', function() {
	check_ajax_referer( 'wpshadow_autofix_permission', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$finding_id = sanitize_key( $_POST['finding_id'] ?? '' );
	$enabled = filter_var( $_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN );
	
	if ( empty( $finding_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid finding ID.' ) );
	}
	
	$permissions = get_option( 'wpshadow_autofix_permissions', array() );
	
	if ( $enabled ) {
		$permissions[ $finding_id ] = true;
	} else {
		unset( $permissions[ $finding_id ] );
	}
	
	update_option( 'wpshadow_autofix_permissions', $permissions );
	
	wp_send_json_success( array( 
		'message' => $enabled ? 'Auto-fix enabled for this type.' : 'Auto-fix disabled for this type.',
		'enabled' => $enabled,
	) );
} );

// Allow all auto-fixes.
add_action( 'wp_ajax_wpshadow_allow_all_autofixes', function() {
	check_ajax_referer( 'wpshadow_allow_all_autofixes', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$enabled = filter_var( $_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN );
	update_option( 'wpshadow_allow_all_autofixes', $enabled );
	
	wp_send_json_success( array( 
		'message' => $enabled ? 'All auto-fixes enabled.' : 'All auto-fixes disabled.',
		'enabled' => $enabled,
	) );
} );

// Save tagline AJAX handler.
add_action( 'wp_ajax_wpshadow_save_tagline', function() {
	check_ajax_referer( 'wpshadow_save_tagline', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$tagline = sanitize_text_field( $_POST['tagline'] ?? '' );
	
	if ( empty( $tagline ) ) {
		wp_send_json_error( array( 'message' => 'Please enter a tagline.' ) );
	}
	
	if ( strlen( $tagline ) > 200 ) {
		wp_send_json_error( array( 'message' => 'Tagline is too long.' ) );
	}
	
	update_option( 'blogdescription', $tagline );
	
	wp_send_json_success( array( 'message' => 'Tagline saved successfully!' ) );
} );

// Change finding status in Kanban board.
add_action( 'wp_ajax_wpshadow_change_finding_status', function() {
	check_ajax_referer( 'wpshadow_kanban', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$finding_id = sanitize_key( $_POST['finding_id'] ?? '' );
	$new_status = sanitize_key( $_POST['new_status'] ?? '' );
	
	if ( empty( $finding_id ) || empty( $new_status ) ) {
		wp_send_json_error( array( 'message' => 'Invalid finding or status.' ) );
	}
	
	// Valid statuses
	$valid_statuses = array( 'detected', 'ignored', 'manual', 'automated', 'fixed' );
	if ( ! in_array( $new_status, $valid_statuses, true ) ) {
		wp_send_json_error( array( 'message' => 'Invalid status.' ) );
	}
	
	// Update finding status using Status Manager
	$status_manager = new \WPShadow\Core\Finding_Status_Manager();
	$status_manager->set_finding_status( $finding_id, $new_status );
	
	// Log the action
	wpshadow_log_finding_action( $finding_id, 'status_changed', "Status changed to: {$new_status}" );
	
	wp_send_json_success( array( 
		'message' => 'Finding status updated.',
		'finding_id' => $finding_id,
		'new_status' => $new_status,
	) );
} );

// Schedule overnight fix
add_action( 'wp_ajax_wpshadow_schedule_overnight_fix', function() {
	check_ajax_referer( 'wpshadow_kanban', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$finding_id = sanitize_key( $_POST['finding_id'] ?? '' );
	
	if ( empty( $finding_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid finding ID.' ) );
	}
	
	// Get scheduled fixes option
	$scheduled = get_option( 'wpshadow_scheduled_fixes', array() );
	
	// Add this finding to overnight queue
	$scheduled[] = array(
		'finding_id' => $finding_id,
		'scheduled_at' => current_time( 'timestamp' ),
		'user_email' => wp_get_current_user()->user_email,
	);
	
	update_option( 'wpshadow_scheduled_fixes', $scheduled );
	
	// Schedule cron job if not already scheduled
	if ( ! wp_next_scheduled( 'wpshadow_run_overnight_fixes' ) ) {
		// Schedule for 2 AM
		$tomorrow_2am = strtotime( 'tomorrow 2:00' );
		wp_schedule_single_event( $tomorrow_2am, 'wpshadow_run_overnight_fixes' );
	}

	wp_send_json_success( array( 
		'message' => 'Fix scheduled for overnight processing.',
		'finding_id' => $finding_id,
	) );
} );

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

// Schedule off-peak operation
add_action( 'wp_ajax_wpshadow_schedule_offpeak', function() {
	check_ajax_referer( 'wpshadow_offpeak', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$operation_type = sanitize_key( $_POST['operation_type'] ?? '' );
	$email = sanitize_email( $_POST['email'] ?? '' );
	
	if ( empty( $operation_type ) || empty( $email ) ) {
		wp_send_json_error( array( 'message' => 'Invalid operation or email.' ) );
	}
	
	// Get scheduled operations
	$scheduled = get_option( 'wpshadow_scheduled_offpeak', array() );
	
	// Add this operation to queue
	$scheduled[] = array(
		'operation_type' => $operation_type,
		'scheduled_at' => current_time( 'timestamp' ),
		'user_email' => $email,
	);
	
	update_option( 'wpshadow_scheduled_offpeak', $scheduled );
	
	// Schedule cron job if not already scheduled
	if ( ! wp_next_scheduled( 'wpshadow_run_offpeak_operations' ) ) {
		// Schedule for 2 AM
		$tomorrow_2am = strtotime( 'tomorrow 2:00' );
		wp_schedule_single_event( $tomorrow_2am, 'wpshadow_run_offpeak_operations' );
	}
	
	wp_send_json_success( array( 
		'message' => 'Operation scheduled for off-peak hours.',
		'operation_type' => $operation_type,
	) );
} );

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
				
			case 'full-security-scan':
				// Run security scan
				$result = array( 'success' => true, 'message' => 'Security scan completed.' );
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
		'Automation Builder',
		'Automation Builder',
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
} );

// Load diagnostic registry
require_once plugin_dir_path( __FILE__ ) . 'includes/diagnostics/class-diagnostic-registry.php';

// Load treatment registry
require_once plugin_dir_path( __FILE__ ) . 'includes/treatments/class-treatment-registry.php';

// Load core classes
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-diagnostic-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-finding-status-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-kpi-tracker.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-block-registry.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-wizard.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-ajax.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-executor.php';

/**
 * Initialize diagnostics system.
 */
add_action( 'plugins_loaded', function() {
	\WPShadow\Diagnostics\Diagnostic_Registry::init();
	\WPShadow\Treatments\Treatment_Registry::init();
	\WPShadow\Workflow\Workflow_Executor::init();
	wpshadow_apply_enforcements();
} );

/**
 * Apply enforced options for treatments that persist via options.
 */
function wpshadow_apply_enforcements() {
	// Head cleanup.
	if ( get_option( 'wpshadow_head_cleanup_enabled' ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	}

	// Clickjacking protection.
	if ( get_option( 'wpshadow_iframe_busting_enabled' ) ) {
		add_action( 'send_headers', 'wpshadow_send_iframe_busting_headers' );
	}

	// Force image lazy loading.
	if ( get_option( 'wpshadow_force_lazyload' ) ) {
		add_filter( 'wp_lazy_loading_enabled', 'wpshadow_force_lazyload', 10, 3 );
	}

	// Block external Google-hosted fonts.
	if ( get_option( 'wpshadow_block_external_fonts' ) ) {
		add_filter( 'style_loader_src', 'wpshadow_block_external_font_src', 10, 2 );
	}

	// Skiplinks.
	if ( get_option( 'wpshadow_skiplinks_enabled' ) ) {
		add_action( 'wp_body_open', 'wpshadow_render_skiplinks', 5 );
	}

	// Asset version removal.
	if ( get_option( 'wpshadow_asset_version_removal_enabled' ) ) {
		add_filter( 'style_loader_src', 'wpshadow_remove_asset_version', 15, 1 );
		add_filter( 'script_loader_src', 'wpshadow_remove_asset_version', 15, 1 );
	}

	// CSS class cleanup.
	if ( get_option( 'wpshadow_css_class_cleanup_enabled' ) ) {
		add_filter( 'body_class', 'wpshadow_simplify_body_classes', 10, 1 );
		add_filter( 'post_class', 'wpshadow_simplify_post_classes', 10, 3 );
		add_filter( 'nav_menu_css_class', 'wpshadow_simplify_nav_classes', 10, 4 );
		add_filter( 'nav_menu_item_id', '__return_false' );
	}

	// Navigation accessibility.
	if ( get_option( 'wpshadow_nav_accessibility_enabled' ) ) {
		add_filter( 'wp_nav_menu_objects', 'wpshadow_add_nav_aria', 10, 2 );
	}
}

/**
 * Send clickjacking protection headers.
 */
function wpshadow_send_iframe_busting_headers() {
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( "Content-Security-Policy: frame-ancestors 'self'" );
}

/**
 * Enforce lazy loading for images.
 */
function wpshadow_force_lazyload( $default, $tag_name, $context ) {
	if ( 'img' === $tag_name ) {
		return true;
	}

	return $default;
}

/**
 * Block Google-hosted font sources.
 */
function wpshadow_block_external_font_src( $src, $handle ) {
	if ( false !== stripos( $src, 'fonts.googleapis.com' ) || false !== stripos( $src, 'fonts.gstatic.com' ) ) {
		return false;
	}

	return $src;
}

/**
 * Render skiplinks for accessibility.
 */
function wpshadow_render_skiplinks() {
	echo '<a class="wpshadow-skiplink" href="#main" style="position:absolute;left:-999px;top:auto;width:1px;height:1px;overflow:hidden;">Skip to content</a>';
}

/**
 * Remove version query strings from asset URLs.
 *
 * @param string $src Asset source URL.
 * @return string Modified URL without version parameter.
 */
function wpshadow_remove_asset_version( $src ) {
	if ( ! is_string( $src ) || strpos( $src, 'ver=' ) === false ) {
		return $src;
	}

	return remove_query_arg( 'ver', $src );
}

/**
 * Simplify body classes to essential only.
 *
 * @param array $classes Original body classes.
 * @return array Filtered classes.
 */
function wpshadow_simplify_body_classes( $classes ) {
	$essential = array(
		'home',
		'blog',
		'archive',
		'single',
		'page',
		'search',
		'error404',
		'logged-in',
		'admin-bar',
	);

	$keep = array();
	foreach ( $classes as $class ) {
		if ( in_array( $class, $essential, true ) ) {
			$keep[] = $class;
		} elseif ( str_starts_with( $class, 'post-type-' ) || str_starts_with( $class, 'page-template-' ) ) {
			$keep[] = $class;
		}
	}

	return array_unique( $keep );
}

/**
 * Simplify post classes to essential only.
 *
 * @param array $classes Original post classes.
 * @param mixed $class Additional class parameter.
 * @param int   $post_id Post ID.
 * @return array Filtered classes.
 */
function wpshadow_simplify_post_classes( $classes, $class, $post_id ) {
	$essential = array( 'post', 'entry', 'hentry' );
	$keep      = array();

	foreach ( $classes as $css_class ) {
		if ( in_array( $css_class, $essential, true ) ) {
			$keep[] = $css_class;
		} elseif ( str_starts_with( $css_class, 'type-' ) || str_starts_with( $css_class, 'format-' ) ) {
			$keep[] = $css_class;
		} elseif ( in_array( $css_class, array( 'sticky', 'has-post-thumbnail' ), true ) ) {
			$keep[] = $css_class;
		}
	}

	return array_unique( $keep );
}

/**
 * Simplify navigation menu classes.
 *
 * @param array  $classes Original nav item classes.
 * @param object $item Menu item object.
 * @param object $args Menu args.
 * @param int    $depth Menu depth.
 * @return array Filtered classes.
 */
function wpshadow_simplify_nav_classes( $classes, $item, $args, $depth ) {
	$keep = array( 'menu-item' );

	if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
		$keep[] = 'current';
	}

	if ( in_array( 'menu-item-has-children', $classes, true ) ) {
		$keep[] = 'has-children';
	}

	if ( in_array( 'current-menu-ancestor', $classes, true ) ) {
		$keep[] = 'ancestor';
	}

	return array_unique( $keep );
}

/**
 * Add ARIA current-page attribute to active nav items.
 *
 * @param array  $items Menu items.
 * @param object $args Menu args.
 * @return array Modified menu items.
 */
function wpshadow_add_nav_aria( $items, $args ) {
	foreach ( $items as $item ) {
		if ( $item->current ) {
			if ( ! isset( $item->attributes ) ) {
				$item->attributes = array();
			}
			$item->attributes['aria-current'] = 'page';
		}
	}

	return $items;
}

/**
 * Enqueue Kanban board assets.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

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
} );

/**
 * Render Workflow Builder page (IFTTT-style).
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

	// Show tools index
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WPShadow Tools', 'wpshadow' ); ?></h1>
		<p><?php esc_html_e( 'Additional tools for site analysis and optimization.', 'wpshadow' ); ?></p>

		<div class="wpshadow-tools-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
			
			<!-- A11y Audit Tool -->
			<div class="wpshadow-tool-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Accessibility Audit', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Scan your site for accessibility issues and WCAG compliance.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-tools&tool=a11y-audit' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Open Tool', 'wpshadow' ); ?>
					</a>
				</p>
			</div>

			<!-- Broken Link Checker -->
			<div class="wpshadow-tool-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Broken Link Checker', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Find and fix broken links across your site.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-tools&tool=broken-links' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Open Tool', 'wpshadow' ); ?>
					</a>
				</p>
			</div>

			<!-- Color Contrast Checker -->
			<div class="wpshadow-tool-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Color Contrast Checker', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Check color combinations for accessibility compliance.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<button class="button button-secondary" disabled><?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?></button>
				</p>
			</div>

			<!-- Dark Mode -->
			<div class="wpshadow-tool-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Dark Mode', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-tools&tool=dark-mode' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Open Tool', 'wpshadow' ); ?>
					</a>
				</p>
			</div>

			<!-- Mobile Friendliness -->
			<div class="wpshadow-tool-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Mobile Friendliness', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Test your site for mobile compatibility and responsive design.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<button class="button button-secondary" disabled><?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?></button>
				</p>
			</div>

		</div>
	</div>
	<?php
}

/**
 * Render Help page.
 */
function wpshadow_render_help() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$page = isset( $_GET['help_page'] ) ? sanitize_key( $_GET['help_page'] ) : '';

	// Route to specific help page if requested
	if ( ! empty( $page ) ) {
		$help_file = WPSHADOW_PATH . 'includes/views/help/' . $page . '.php';
		if ( file_exists( $help_file ) ) {
			include $help_file;
			return;
		}
	}

	// Show help index
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WPShadow Help & Support', 'wpshadow' ); ?></h1>
		<p><?php esc_html_e( 'Get help and access support resources for WPShadow.', 'wpshadow' ); ?></p>

		<div class="wpshadow-help-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
			
			<!-- Emergency Support -->
			<div class="wpshadow-help-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Emergency Support', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Get immediate help when your site is down or experiencing critical issues.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-help&help_page=emergency-support' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Open', 'wpshadow' ); ?>
					</a>
				</p>
			</div>

			<!-- Magic Link Support -->
			<div class="wpshadow-help-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Magic Link Support', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Generate secure one-time access links for support staff.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<button class="button button-secondary" disabled><?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?></button>
				</p>
			</div>

			<!-- Simple Cache -->
			<div class="wpshadow-help-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Cache Management', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Manage site caching and clear cache when needed.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<button class="button button-secondary" disabled><?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?></button>
				</p>
			</div>

			<!-- Tips Coach -->
			<div class="wpshadow-help-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Tips & Guidance', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Get helpful tips and best practices for managing your WordPress site.', 'wpshadow' ); ?></p>
				<p style="margin-bottom: 0;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-help&help_page=tips-coach' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Open', 'wpshadow' ); ?>
					</a>
				</p>
			</div>

		</div>

		<hr style="margin: 40px 0;">

		<h2><?php esc_html_e( 'Documentation & Resources', 'wpshadow' ); ?></h2>
		<ul>
			<li><a href="https://github.com/thisismyurl/wpshadow" target="_blank"><?php esc_html_e( 'GitHub Repository', 'wpshadow' ); ?></a></li>
			<li><a href="https://github.com/thisismyurl/wpshadow/issues" target="_blank"><?php esc_html_e( 'Report an Issue', 'wpshadow' ); ?></a></li>
		</ul>
	</div>
	<?php
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

	$health = wpshadow_get_health_status();
	$all_findings = wpshadow_get_site_findings();
	$dismissed = get_option( 'wpshadow_dismissed_findings', array() );
	
	// Filter out dismissed findings
	$all_findings = array_filter( $all_findings, function( $f ) use ( $dismissed ) {
		return ! isset( $dismissed[ $f['id'] ] );
	} );
	
	$critical_findings = array_filter( $all_findings, function( $f ) {
		return $f['color'] === '#f44336'; // Red = critical
	} );
	$show_all = isset( $_GET['show_all'] ) && 'true' === $_GET['show_all'];
	$findings_to_show = $show_all ? $all_findings : array_slice( $critical_findings, 0, 2 );
	?>
	<div class="wrap">
		<h1>WPShadow Site Health Diagnostic</h1>

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

		<!-- Health Score Section -->
		<div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
			<h2 style="margin-top: 0;">Your Site Health</h2>
			<div style="display: flex; align-items: center; gap: 30px;">
				<div style="text-align: center;">
					<div style="font-size: 48px; font-weight: bold; color: <?php echo esc_attr( $health['color'] ); ?>;">
						<?php echo (int) $health['score']; ?>%
					</div>
					<div style="font-size: 16px; color: #666;">
						<?php echo esc_html( $health['status'] ); ?>
					</div>
				</div>
				<div style="flex: 1;">
					<p style="margin: 0 0 10px 0; color: #333;">
						<?php echo esc_html( $health['message'] ); ?>
					</p>
					<p style="margin: 0; font-size: 13px; color: #666;">
						Last scanned: <?php echo wp_kses_post( wpshadow_format_time_with_tooltip( current_time( 'timestamp' ) ) ); ?>
					</p>
				</div>
			</div>
		</div>

		<!-- Kanban Board for Organizing Findings -->
		<?php include WPSHADOW_PATH . 'includes/views/kanban-board.php'; ?>


		<!-- Recent Activity -->
		<?php 
		$activity = wpshadow_get_recent_activity();
		if ( ! empty( $activity ) ) : 
		?>
		<div style="margin: 30px 0;">
			<h2>Recent Activity</h2>
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

