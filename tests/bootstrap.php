<?php

declare(strict_types=1);

$wp_root = getenv( 'thisismyurl_shadow_TEST_WP_ROOT' );

if ( ! is_string( $wp_root ) || '' === $wp_root ) {
	$wp_root = '/workspaces/wp-smoke';
}

$wp_load = rtrim( $wp_root, '/\\' ) . '/wp-load.php';

if ( ! file_exists( $wp_load ) ) {
	throw new RuntimeException( 'Could not find wp-load.php at ' . $wp_load );
}

if ( ! defined( 'WP_ADMIN' ) ) {
	define( 'WP_ADMIN', true );
}

require_once $wp_load;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_basename = 'thisismyurl-shadow/thisismyurl-shadow.php';

if ( ! is_plugin_active( $plugin_basename ) ) {
	$result = activate_plugin( $plugin_basename );
	if ( is_wp_error( $result ) ) {
		throw new RuntimeException( 'Plugin activation failed: ' . $result->get_error_message() );
	}
}

$admins = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
	)
);

if ( empty( $admins ) ) {
	throw new RuntimeException( 'No administrator user found for test bootstrap.' );
}

wp_set_current_user( (int) $admins[0]->ID );

if ( function_exists( 'set_current_screen' ) ) {
	set_current_screen( 'dashboard' );
}