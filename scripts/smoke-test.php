#!/usr/bin/env php
<?php

declare(strict_types=1);

$wp_root = $argv[1] ?? getenv( 'WPSHADOW_TEST_WP_ROOT' ) ?: '/workspaces/wp-smoke';
$wp_root = rtrim( (string) $wp_root, '/\\' );
$wp_load = $wp_root . '/wp-load.php';

if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "Smoke test failed: wp-load.php not found at {$wp_load}\n" );
	exit( 2 );
}

if ( ! defined( 'WP_ADMIN' ) ) {
	define( 'WP_ADMIN', true );
}

require_once $wp_load;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_basename = 'wpshadow/wpshadow.php';
$failures        = array();

$record = static function ( string $label, bool $passed, string $detail = '' ) use ( &$failures ): void {
	$status = $passed ? 'PASS' : 'FAIL';
	echo $status . ' ' . $label;
	if ( '' !== $detail ) {
		echo ' - ' . $detail;
	}
	echo PHP_EOL;

	if ( ! $passed ) {
		$failures[] = $label . ( '' !== $detail ? ': ' . $detail : '' );
	}
};

$admins = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
	)
);

$record( 'Administrator user available', ! empty( $admins ) );

if ( ! empty( $admins ) ) {
	wp_set_current_user( (int) $admins[0]->ID );
}

if ( ! is_plugin_active( $plugin_basename ) ) {
	$result = activate_plugin( $plugin_basename );
	$record(
		'Plugin activation',
		! is_wp_error( $result ),
		is_wp_error( $result ) ? $result->get_error_message() : ''
	);
}

$record( 'Plugin is active', is_plugin_active( $plugin_basename ) );

if ( function_exists( 'set_current_screen' ) ) {
	set_current_screen( 'dashboard' );
}

$record( 'Dashboard callback exists', function_exists( 'wpshadow_render_dashboard_v2' ) );
$record( 'Guardian callback exists', function_exists( 'wpshadow_render_guardian_page' ) );
$record( 'Settings callback exists', function_exists( 'wpshadow_render_settings' ) );

do_action( 'admin_menu' );

global $menu;

$top_level_slugs = array();
foreach ( (array) $menu as $item ) {
	if ( is_array( $item ) && isset( $item[2] ) ) {
		$top_level_slugs[] = (string) $item[2];
	}
}

$record( 'Top-level admin menu registered', in_array( 'wpshadow', $top_level_slugs, true ) );
$record( 'Dashboard AJAX registered', false !== has_action( 'wp_ajax_wpshadow_get_dashboard_data' ) );
$record( 'Post-scan AJAX registered', false !== has_action( 'wp_ajax_wpshadow_post_scan_treatments' ) );

if ( function_exists( 'wpshadow_render_dashboard_v2' ) ) {
	try {
		ob_start();
		wpshadow_render_dashboard_v2();
		ob_end_clean();
		$record( 'Dashboard render callback runs', true );
	} catch ( Throwable $throwable ) {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
		$record( 'Dashboard render callback runs', false, $throwable->getMessage() );
	}
}

if ( function_exists( 'wpshadow_render_settings' ) ) {
	try {
		ob_start();
		wpshadow_render_settings();
		ob_end_clean();
		$record( 'Settings render callback runs', true );
	} catch ( Throwable $throwable ) {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
		$record( 'Settings render callback runs', false, $throwable->getMessage() );
	}
}

if ( empty( $failures ) ) {
	echo 'Smoke test completed successfully.' . PHP_EOL;
	exit( 0 );
}

echo PHP_EOL . 'Smoke test failures:' . PHP_EOL;
foreach ( $failures as $failure ) {
	echo '- ' . $failure . PHP_EOL;
}

exit( 1 );