<?php
/**
 * PHPUnit Bootstrap for WPShadow Tests
 *
 * Loads WordPress test environment and WPShadow plugin.
 *
 * @package WPShadow\Tests
 * @since   1.6030.2148
 */

declare(strict_types=1);

// Define constants
define( 'WPSHADOW_TESTS', true );
define( 'WPSHADOW_TESTS_DIR', __DIR__ );
define( 'WPSHADOW_PLUGIN_DIR', dirname( __DIR__ ) );

// Load Composer autoloader
require_once WPSHADOW_PLUGIN_DIR . '/vendor/autoload.php';

// Check if WordPress test suite is available
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Try to load WordPress test suite
if ( file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	require_once $_tests_dir . '/includes/functions.php';
	
	/**
	 * Manually load plugin for testing
	 */
	function _manually_load_plugin() {
		require WPSHADOW_PLUGIN_DIR . '/wpshadow.php';
	}
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
	
	// Start WordPress test environment
	require $_tests_dir . '/includes/bootstrap.php';
} else {
	// Fallback: Load WordPress constants and basic mocks if WP test suite not available
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', WPSHADOW_PLUGIN_DIR . '/../../' );
	}
	
	// Define WordPress mock functions BEFORE loading plugin
	if ( ! function_exists( 'add_action' ) ) {
		function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			global $wp_filter;
			if ( ! isset( $wp_filter ) ) {
				$wp_filter = array();
			}
			if ( ! isset( $wp_filter[ $tag ] ) ) {
				$wp_filter[ $tag ] = array();
			}
			if ( ! isset( $wp_filter[ $tag ][ $priority ] ) ) {
				$wp_filter[ $tag ][ $priority ] = array();
			}
			$wp_filter[ $tag ][ $priority ][] = $function_to_add;
			return true;
		}
	}
	
	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			global $wp_filter;
			if ( ! isset( $wp_filter ) ) {
				$wp_filter = array();
			}
			if ( ! isset( $wp_filter[ $tag ] ) ) {
				$wp_filter[ $tag ] = array();
			}
			if ( ! isset( $wp_filter[ $tag ][ $priority ] ) ) {
				$wp_filter[ $tag ][ $priority ] = array();
			}
			$wp_filter[ $tag ][ $priority ][] = $function_to_add;
			return true;
		}
	}
	
	if ( ! function_exists( 'remove_all_filters' ) ) {
		function remove_all_filters( $tag, $priority = false ) {
			global $wp_filter;
			if ( isset( $wp_filter[ $tag ] ) ) {
				if ( false === $priority ) {
					unset( $wp_filter[ $tag ] );
				} elseif ( isset( $wp_filter[ $tag ][ $priority ] ) ) {
					unset( $wp_filter[ $tag ][ $priority ] );
				}
			}
			return true;
		}
	}
	
	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook( $file, $function ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook( $file, $function ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'do_action' ) ) {
		function do_action( $tag, ...$args ) {
			global $wp_filter;
			if ( ! isset( $wp_filter[ $tag ] ) ) {
				return;
			}
			ksort( $wp_filter[ $tag ] );
			foreach ( $wp_filter[ $tag ] as $priority => $functions ) {
				foreach ( $functions as $function ) {
					call_user_func_array( $function, $args );
				}
			}
		}
	}
	
	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $tag, $value, ...$args ) {
			global $wp_filter;
			if ( ! isset( $wp_filter[ $tag ] ) ) {
				return $value;
			}
			ksort( $wp_filter[ $tag ] );
			foreach ( $wp_filter[ $tag ] as $priority => $functions ) {
				foreach ( $functions as $function ) {
					$value = call_user_func_array( $function, array_merge( array( $value ), $args ) );
				}
			}
			return $value;
		}
	}
	
	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook( $file, $callback ) {
			// Mock implementation for testing
			return true;
		}
	}
	
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook( $file, $callback ) {
			// Mock implementation for testing
			return true;
		}
	}
	
	if ( ! function_exists( 'register_setting' ) ) {
		function register_setting( $option_group, $option_name, $args = array() ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook( $file, $callback ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'plugin_basename' ) ) {
		function plugin_basename( $file ) {
			return basename( dirname( $file ) ) . '/' . basename( $file );
		}
	}
	
	if ( ! function_exists( 'plugin_dir_path' ) ) {
		function plugin_dir_path( $file ) {
			return trailingslashit( dirname( $file ) );
		}
	}
	
	if ( ! function_exists( 'plugin_dir_url' ) ) {
		function plugin_dir_url( $file ) {
			return 'http://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
		}
	}
	
	if ( ! function_exists( 'trailingslashit' ) ) {
		function trailingslashit( $string ) {
			return rtrim( $string, '/\\' ) . '/';
		}
	}
	
	if ( ! function_exists( '__' ) ) {
		function __( $text, $domain = 'default' ) {
			return $text;
		}
	}
	
	if ( ! function_exists( '_e' ) ) {
		function _e( $text, $domain = 'default' ) {
			echo $text;
		}
	}
	
	if ( ! function_exists( 'esc_html__' ) ) {
		function esc_html__( $text, $domain = 'default' ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}
	
	if ( ! function_exists( 'esc_html_e' ) ) {
		function esc_html_e( $text, $domain = 'default' ) {
			echo htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}
	
	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}
	
	if ( ! function_exists( 'esc_attr' ) ) {
		function esc_attr( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}
	
	if ( ! function_exists( 'esc_url' ) ) {
		function esc_url( $url ) {
			return filter_var( $url, FILTER_SANITIZE_URL );
		}
	}
	
	if ( ! function_exists( 'sanitize_text_field' ) ) {
		function sanitize_text_field( $str ) {
			return strip_tags( $str );
		}
	}
	
	if ( ! function_exists( 'sanitize_key' ) ) {
		function sanitize_key( $key ) {
			return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
		}
	}
	
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook( $file, $callback ) {
			// Mock implementation for testing
			return true;
		}
	}
	
	if ( ! function_exists( 'wp_parse_args' ) ) {
		function wp_parse_args( $args, $defaults = array() ) {
			if ( is_object( $args ) ) {
				$parsed_args = get_object_vars( $args );
			} elseif ( is_array( $args ) ) {
				$parsed_args = &$args;
			} else {
				parse_str( $args, $parsed_args );
			}
			return array_merge( $defaults, $parsed_args );
		}
	}
	
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook( $file, $callback ) {
	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook( $file, $callback ) {
	if ( ! function_exists( 'current_theme_supports' ) ) {
		function current_theme_supports( $feature ) {
	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook( $file, $function ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook( $file, $callback ) {
			return true;
		}
	}
	
	// Load WPShadow plugin
	require_once WPSHADOW_PLUGIN_DIR . '/wpshadow.php';
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook( $file, $callback ) {
		function register_deactivation_hook( $file, $function ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'get_option' ) ) {
		function get_option( $option, $default = false ) {
			static $options = array();
			return $options[ $option ] ?? $default;
		}
	}
	
	if ( ! function_exists( 'update_option' ) ) {
		function update_option( $option, $value ) {
			static $options = array();
			$options[ $option ] = $value;

	if ( ! function_exists( 'load_plugin_textdomain' ) ) {
		function load_plugin_textdomain( $domain, $deprecated = false, $plugin_rel_path = false ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'get_transient' ) ) {
		function get_transient( $transient ) {
			return false;
		}
	}
	
	if ( ! function_exists( 'set_transient' ) ) {
		function set_transient( $transient, $value, $expiration = 0 ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'current_theme_supports' ) ) {
		function current_theme_supports( $feature ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'has_post_thumbnail' ) ) {
		function has_post_thumbnail( $post = null ) {
	if ( ! function_exists( 'get_intermediate_image_sizes' ) ) {
		function get_intermediate_image_sizes() {
			// Return default WordPress image sizes, with filter support for testing
			$sizes = array( 'thumbnail', 'medium', 'medium_large', 'large', '1536x1536', '2048x2048' );
			return apply_filters( 'intermediate_image_sizes', $sizes );
		}
	}
	
	if ( ! function_exists( 'absint' ) ) {
		function absint( $maybeint ) {
			return abs( (int) $maybeint );
		}
	}
	
	if ( ! function_exists( 'wp_unslash' ) ) {
		function wp_unslash( $value ) {
			return is_array( $value ) ? array_map( 'stripslashes', $value ) : stripslashes( $value );
		}
	}
	
	if ( ! function_exists( 'is_admin' ) ) {
		function is_admin() {
			return false;
		}
	}
	
	if ( ! function_exists( 'wp_get_theme' ) ) {
		function wp_get_theme( $stylesheet = null ) {
			return new class {
				public function get( $header ) {
					return 'Test Theme';
				}
			};
		}
	}
	
	if ( ! function_exists( 'get_stylesheet' ) ) {
		function get_stylesheet() {
			return 'test-theme';
		}
	}
	
	if ( ! function_exists( 'get_template_directory' ) ) {
		function get_template_directory() {
			return '/tmp/test-theme';
		}
	}
	
	if ( ! function_exists( 'sprintf' ) && ! function_exists( '_n' ) ) {
		function _n( $single, $plural, $number, $domain = 'default' ) {
			return $number === 1 ? $single : $plural;
		}
	}
	
	if ( ! function_exists( 'get_template_directory' ) ) {
		function get_template_directory() {
			return '/tmp/theme';
		}
	}
	
	if ( ! function_exists( '_n' ) ) {
		function _n( $single, $plural, $count, $domain = 'default' ) {
			return $count === 1 ? $single : $plural;
		}
	}
	
	if ( ! function_exists( 'get_transient' ) ) {
		function get_transient( $transient ) {
			return false;
		}
	}
	
	if ( ! function_exists( 'set_transient' ) ) {
		function set_transient( $transient, $value, $expiration = 0 ) {
			return true;
		}
	}
	
	if ( ! defined( 'DAY_IN_SECONDS' ) ) {
		define( 'DAY_IN_SECONDS', 24 * 60 * 60 );
	}
	
	// Load WPShadow plugin
	require_once WPSHADOW_PLUGIN_DIR . '/wpshadow.php';
	
	// Create test helper functions with shared state
	global $test_options;
	$test_options = array();
	
	if ( ! function_exists( 'get_option' ) ) {
		function get_option( $option, $default = false ) {
			global $test_options;
			return $test_options[ $option ] ?? $default;
			global $wp_options_mock;
			if ( ! isset( $wp_options_mock ) ) {
				$wp_options_mock = array();
			}
			return $wp_options_mock[ $option ] ?? $default;
		}
	}
	
	if ( ! function_exists( 'update_option' ) ) {
		function update_option( $option, $value ) {
			global $test_options;
			$test_options[ $option ] = $value;
			global $wp_options_mock;
			if ( ! isset( $wp_options_mock ) ) {
				$wp_options_mock = array();
			}
			$wp_options_mock[ $option ] = $value;
			return true;
		}
	}
	
	if ( ! function_exists( 'add_action' ) ) {
		function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'do_action' ) ) {
		function do_action( $hook, ...$args ) {
			return null;
		}
	}
	
	// Mock Treatment_Registry class for tests
	if ( ! class_exists( 'WPShadow\\Treatments\\Treatment_Registry' ) ) {
		class Treatment_Registry {
			public static function register( $id, $class ) {
				return true;
			}
			public static function get( $id ) {
				return null;
			}
			public static function get_all() {
				return array();
			}
		}
	}
	
	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $hook, $value, ...$args ) {
			return $value;
		}
	}
	
	if ( ! function_exists( 'wp_die' ) ) {
		function wp_die( $message ) {
			throw new Exception( $message );
		}
	}
	
	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}
	
	if ( ! function_exists( 'esc_attr' ) ) {
		function esc_attr( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}
	
	if ( ! function_exists( 'esc_url' ) ) {
		function esc_url( $url ) {
			return filter_var( $url, FILTER_SANITIZE_URL );
		}
	}
	
	if ( ! function_exists( 'sanitize_text_field' ) ) {
		function sanitize_text_field( $str ) {
			return strip_tags( $str );
		}
	}
	
	if ( ! function_exists( '__' ) ) {
		function __( $text, $domain = 'default' ) {
			return $text;
		}
	}
	
	if ( ! function_exists( '_e' ) ) {
		function _e( $text, $domain = 'default' ) {
			echo $text;
		}
	}
	
	if ( ! function_exists( 'current_user_can' ) ) {
		function current_user_can( $capability ) {
			return true;
		}
	}
	
	if ( ! function_exists( 'home_url' ) ) {
		function home_url( $path = '' ) {
			return 'http://example.com' . $path;
		}
	}
	
	if ( ! function_exists( 'get_posts' ) ) {
		function get_posts( $args = array() ) {
			return array();
		}
	}
	
	if ( ! function_exists( 'get_permalink' ) ) {
		function get_permalink( $id = 0 ) {
			return 'http://example.com/sample-post/';
		}
	}
	
	if ( ! function_exists( 'get_post_type_archive_link' ) ) {
		function get_post_type_archive_link( $post_type ) {
			return 'http://example.com/blog/';
		}
	}
	
	if ( ! function_exists( 'get_post_types' ) ) {
		function get_post_types( $args = array(), $output = 'names' ) {
			return array();
		}
	}
	
	if ( ! function_exists( 'wp_remote_head' ) ) {
		function wp_remote_head( $url, $args = array() ) {
			return array(
				'response' => array(
					'code' => 200,
				),
			);
		}
	}
	
	if ( ! function_exists( 'is_wp_error' ) ) {
		function is_wp_error( $thing ) {
			return false;
		}
	}
	
	if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
		function wp_remote_retrieve_response_code( $response ) {
			return isset( $response['response']['code'] ) ? $response['response']['code'] : 200;
		}
	}
	
	if ( ! function_exists( 'file_exists' ) ) {
		// PHP built-in, available
	}
	
	if ( ! function_exists( 'get_current_user_id' ) ) {
		function get_current_user_id() {
			return 1; // Mock admin user
		}
	}
	
	if ( ! function_exists( 'is_multisite' ) ) {
		function is_multisite() {
			return false;
		}
	}
	
	if ( ! function_exists( 'is_network_admin' ) ) {
		function is_network_admin() {
			return false;
		}
	}

	echo "⚠️  WordPress test suite not found. Using fallback mode.\n";
	echo "   To enable full WordPress testing:\n";
	echo "   1. Install WordPress test suite\n";
	echo "   2. Set WP_TESTS_DIR environment variable\n\n";
}

// Load TestCase base class
require_once __DIR__ . '/TestCase.php';
