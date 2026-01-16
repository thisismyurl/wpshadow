<?php
/**
 * Bootstrap file for PHPUnit tests
 *
 * @package WPShadow
 */

declare(strict_types=1);

// Set error reporting
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

// Get the main plugin file
$plugin_file = dirname( dirname( __FILE__ ) ) . '/wpshadow.php';

// If we're testing, load necessary WordPress functions
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );
}

// Load Composer autoloader
require_once dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';

// Define WordPress test constants if needed
if ( ! defined( 'WP_TESTS_DIR' ) ) {
	define( 'WP_TESTS_DIR', dirname( __FILE__ ) . '/wordpress/' );
}

/**
 * Mock WordPress functions for unit tests
 */
if ( ! function_exists( 'wp_safe_remote_get' ) ) {
	function wp_safe_remote_get( $url, $args = array() ) {
		return array( 'body' => '' );
	}
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
	function wp_remote_retrieve_body( $response ) {
		return isset( $response['body'] ) ? $response['body'] : '';
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $tag ) {
		// Mock action
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value ) {
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $option ) {
		return true;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return stripslashes( $str );
	}
}
