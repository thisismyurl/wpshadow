<?php
/**
 * CPT Features Functionality Diagnostic
 *
 * Verifies that all CPT enhancement features (drag-drop, live preview,
 * analytics, etc.) are properly initialized and functioning.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6034.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CPT_Features Class
 *
 * Checks if all CPT enhancement features are active and working.
 *
 * @since 1.6034.1230
 */
class Diagnostic_CPT_Features extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-features';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Enhancement Features';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies all CPT enhancement features are properly initialized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Expected feature classes
	 *
	 * @var array
	 */
	private static $expected_features = array(
		'WPShadow\Content\CPT_Block_Patterns'      => 'Block Patterns Library',
		'WPShadow\Content\CPT_Drag_Drop_Ordering'  => 'Drag & Drop Ordering',
		'WPShadow\Content\CPT_Live_Preview'        => 'Live Preview',
		'WPShadow\Content\CPT_Conditional_Display' => 'Conditional Display',
		'WPShadow\Content\CPT_Analytics_Dashboard' => 'Analytics Dashboard',
		'WPShadow\Content\CPT_Inline_Editing'      => 'Inline Editing',
		'WPShadow\Content\CPT_Block_Presets'       => 'Block Presets',
		'WPShadow\Content\CPT_Multi_Language'      => 'Multi-Language Support',
		'WPShadow\Content\CPT_Version_History'     => 'Version History (Vault Lite)',
	);

	/**
	 * Optional Cloud-gated features
	 *
	 * @var array
	 */
	private static $cloud_features = array(
		'WPShadow\Content\CPT_AI_Content' => 'AI Content Suggestions',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that all CPT feature classes exist and are initialized.
	 *
	 * @since  1.6034.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$missing_features  = array();
		$inactive_features = array();

		// Check core features.
		foreach ( self::$expected_features as $class_name => $feature_name ) {
			if ( ! class_exists( $class_name ) ) {
				$missing_features[] = $feature_name;
				continue;
			}

			// Check if class has init method and it's been called.
			if ( method_exists( $class_name, 'init' ) ) {
				// We can't directly check if init() was called, but we can check for hooks.
				$has_hooks = self::has_registered_hooks( $class_name );
				if ( ! $has_hooks ) {
					$inactive_features[] = $feature_name;
				}
			}
		}

		// Check Cloud features (only warn if API key exists but feature is missing).
		$has_cloud_key = ! empty( get_option( 'wpshadow_cloud_api_key' ) );
		if ( $has_cloud_key ) {
			foreach ( self::$cloud_features as $class_name => $feature_name ) {
				if ( ! class_exists( $class_name ) ) {
					$missing_features[] = $feature_name . ' (Cloud)';
				}
			}
		}

		// If any features are missing or inactive, report finding.
		if ( ! empty( $missing_features ) || ! empty( $inactive_features ) ) {
			$description = '';

			if ( ! empty( $missing_features ) ) {
				$description .= sprintf(
					/* translators: %s: comma-separated list of feature names */
					__( 'The following CPT features are not available: %s. ', 'wpshadow' ),
					implode( ', ', $missing_features )
				);
			}

			if ( ! empty( $inactive_features ) ) {
				$description .= sprintf(
					/* translators: %s: comma-separated list of feature names */
					__( 'The following CPT features are installed but not initialized: %s. ', 'wpshadow' ),
					implode( ', ', $inactive_features )
				);
			}

			$description .= __( 'These features enhance content management productivity. Ensure all feature files are present and properly initialized in wpshadow.php.', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-features-overview',
				'academy_link' => 'https://wpshadow.com/academy/maximizing-cpt-productivity',
			);
		}

		return null; // All features are available and initialized.
	}

	/**
	 * Check if class has registered WordPress hooks
	 *
	 * @since  1.6034.1230
	 * @param  string $class_name Class name to check.
	 * @return bool True if hooks are registered.
	 */
	private static function has_registered_hooks( $class_name ) {
		global $wp_filter;

		// Common hook patterns used by CPT features.
		$hook_patterns = array(
			'admin_enqueue_scripts',
			'add_meta_boxes',
			'save_post',
			'admin_menu',
			'wp_ajax_',
			'init',
		);

		foreach ( $wp_filter as $tag => $callbacks ) {
			foreach ( $hook_patterns as $pattern ) {
				if ( strpos( $tag, $pattern ) !== false ) {
					foreach ( $callbacks as $priority => $registered_callbacks ) {
						foreach ( $registered_callbacks as $callback ) {
							// Check if callback belongs to our class.
							if ( is_array( $callback['function'] ) &&
								is_string( $callback['function'][0] ) &&
								$callback['function'][0] === $class_name ) {
								return true;
							}
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get count of active features
	 *
	 * @since  1.6034.1230
	 * @return int Count of active features.
	 */
	public static function get_active_count() {
		$count = 0;

		foreach ( array_keys( self::$expected_features ) as $class_name ) {
			if ( class_exists( $class_name ) ) {
				++$count;
			}
		}

		// Add Cloud features if key exists.
		$has_cloud_key = ! empty( get_option( 'wpshadow_cloud_api_key' ) );
		if ( $has_cloud_key ) {
			foreach ( array_keys( self::$cloud_features ) as $class_name ) {
				if ( class_exists( $class_name ) ) {
					++$count;
				}
			}
		}

		return $count;
	}

	/**
	 * Get total expected feature count
	 *
	 * @since  1.6034.1230
	 * @return int Expected feature count.
	 */
	public static function get_expected_count() {
		$count = count( self::$expected_features );

		$has_cloud_key = ! empty( get_option( 'wpshadow_cloud_api_key' ) );
		if ( $has_cloud_key ) {
			$count += count( self::$cloud_features );
		}

		return $count;
	}
}
