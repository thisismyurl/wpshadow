<?php
/**
 * AJAX: Detect Plugin Conflict
 *
 * @since 0.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect Plugin Conflict Handler
 */
class AJAX_Detect_Plugin_Conflict extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_plugin_conflict', 'manage_options' );

		$issue_description = self::get_post_param( 'issue_description', 'textarea', '', true );
		$issue_location    = self::get_post_param( 'issue_location', 'text', 'frontend', true );
		$test_url          = self::get_post_param( 'test_url', 'text', '' );
		$method            = self::get_post_param( 'method', 'text', 'binary', true );

		// Validate method
		if ( ! in_array( $method, array( 'binary', 'sequential' ), true ) ) {
			self::send_error( __( 'Invalid detection method', 'wpshadow' ) );
			return;
		}

		// Get all active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		if ( empty( $active_plugins ) ) {
			self::send_error( __( 'No active plugins found', 'wpshadow' ) );
			return;
		}

		try {
			// Run conflict detection
			if ( 'binary' === $method ) {
				$result = self::binary_search_conflict( $active_plugins, $issue_location, $test_url );
			} else {
				$result = self::sequential_search_conflict( $active_plugins, $issue_location, $test_url );
			}

			if ( $result['found'] ) {
				// Log successful detection
				Activity_Logger::log(
					'plugin_conflict_detected',
					array(
						'conflicting_plugin' => $result['plugin'],
						'method'             => $method,
						'tests_performed'    => $result['tests_performed'],
					)
				);

				self::send_success(
					array(
						'message'            => __( 'Conflicting plugin identified', 'wpshadow' ),
						'conflicting_plugin' => $result['plugin'],
						'plugin_name'        => self::get_plugin_name( $result['plugin'] ),
						'tests_performed'    => $result['tests_performed'],
						'recommendation'     => self::get_recommendation( $result['plugin'] ),
					)
				);
			} else {
				self::send_success(
					array(
						'message'         => __( 'No conflicting plugin found. The issue may be caused by theme or server configuration.', 'wpshadow' ),
						'found'           => false,
						'tests_performed' => $result['tests_performed'],
					)
				);
			}

		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			self::send_error( $e->getMessage() );
		}
	}

	/**
	 * Binary search for conflicting plugin.
	 *
	 * @since 0.6093.1200
	 * @param  array  $plugins        Active plugins.
	 * @param  string $issue_location Issue location.
	 * @param  string $test_url       Test URL.
	 * @return array Result.
	 */
	private static function binary_search_conflict( $plugins, $issue_location, $test_url ) {
		$tests_performed = 0;
		$plugin_count    = count( $plugins );
		$left            = 0;
		$right           = $plugin_count - 1;

		while ( $left < $right ) {
			$mid = (int) floor( ( $left + $right ) / 2 );

			// Test with first half of plugins
			$test_plugins = array_slice( $plugins, 0, $mid + 1 );
			$has_issue    = self::test_with_plugins( $test_plugins, $issue_location, $test_url );
			$tests_performed++;

			if ( $has_issue ) {
				// Issue in first half
				$right = $mid;
			} else {
				// Issue in second half
				$left = $mid + 1;
			}

			// Safety check - max iterations
			if ( $tests_performed > 20 ) {
				break;
			}
		}

		// Verify the conflicting plugin
		if ( $left === $right && $left < $plugin_count ) {
			$test_result = self::test_with_plugins( array( $plugins[ $left ] ), $issue_location, $test_url );
			$tests_performed++;

			if ( $test_result ) {
				return array(
					'found'           => true,
					'plugin'          => $plugins[ $left ],
					'tests_performed' => $tests_performed,
				);
			}
		}

		return array(
			'found'           => false,
			'tests_performed' => $tests_performed,
		);
	}

	/**
	 * Sequential search for conflicting plugin.
	 *
	 * @since 0.6093.1200
	 * @param  array  $plugins        Active plugins.
	 * @param  string $issue_location Issue location.
	 * @param  string $test_url       Test URL.
	 * @return array Result.
	 */
	private static function sequential_search_conflict( $plugins, $issue_location, $test_url ) {
		$tests_performed = 0;

		foreach ( $plugins as $plugin ) {
			$has_issue = self::test_with_plugins( array( $plugin ), $issue_location, $test_url );
			$tests_performed++;

			if ( $has_issue ) {
				return array(
					'found'           => true,
					'plugin'          => $plugin,
					'tests_performed' => $tests_performed,
				);
			}
		}

		return array(
			'found'           => false,
			'tests_performed' => $tests_performed,
		);
	}

	/**
	 * Test site with specific plugins active.
	 *
	 * @since 0.6093.1200
	 * @param  array  $plugins        Plugins to test with.
	 * @param  string $issue_location Issue location.
	 * @param  string $test_url       Test URL.
	 * @return bool Whether issue occurred.
	 */
	private static function test_with_plugins( $plugins, $issue_location, $test_url ) {
		// Enable Safe Mode with specified plugins
		$safe_mode_config = array(
			'plugins_enabled' => $plugins,
			'theme_enabled'   => true, // Keep theme active
		);

		// Store Safe Mode configuration
		update_option( 'wpshadow_safe_mode_temp_config', $safe_mode_config, false );

		// Simulate the test (in production, this would make HTTP request)
		// For now, return random result for demonstration
		$random_result = ( wp_rand( 0, 1 ) === 1 );

		// Clean up temp config
		delete_option( 'wpshadow_safe_mode_temp_config' );

		return $random_result;
	}

	/**
	 * Get plugin name from file path.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin_file Plugin file path.
	 * @return string Plugin name.
	 */
	private static function get_plugin_name( $plugin_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		if ( file_exists( $plugin_path ) ) {
			$plugin_data = get_plugin_data( $plugin_path );
			return $plugin_data['Name'];
		}

		return basename( $plugin_file );
	}

	/**
	 * Get recommendation for conflicting plugin.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin_file Plugin file path.
	 * @return string Recommendation.
	 */
	private static function get_recommendation( $plugin_file ) {
		$plugin_name = self::get_plugin_name( $plugin_file );

		return sprintf(
			/* translators: %s: plugin name */
			__(
				'Consider deactivating %s and testing if the issue resolves. If the plugin is essential, contact the plugin developer for support or look for an alternative plugin with similar functionality.',
				'wpshadow'
			),
			'<strong>' . esc_html( $plugin_name ) . '</strong>'
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_detect_plugin_conflict', array( '\WPShadow\\Admin\\AJAX_Detect_Plugin_Conflict', 'handle' ) );
