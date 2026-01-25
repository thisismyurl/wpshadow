<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive CSS Files in Admin
 *
 * Tests if wp-admin loads too many CSS files, which can cause:
 * - Slower page load times
 * - Render blocking
 * - HTTP overhead
 *
 * Pattern: Similar to front-end tests but uses WordPress internal $wp_styles API
 * Context: Requires admin context, uses global $wp_styles
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #8 Inspire Confidence - Fast admin = professional experience
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test: Admin CSS File Count
 *
 * Checks if wp-admin has excessive CSS files enqueued (> 15)
 *
 * @verified Not yet tested
 */
class Test_Admin_CSS_File_Count extends Diagnostic_Base {


	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array {
		// Only run in admin context
		if ( ! is_admin() ) {
			return null;
		}

		global $wp_styles;

		// Ensure wp_styles is initialized
		if ( ! isset( $wp_styles ) || ! is_object( $wp_styles ) ) {
			return null;
		}

		// Count enqueued + registered stylesheets
		$enqueued_count   = count( $wp_styles->queue ?? array() );
		$registered_count = count( $wp_styles->registered ?? array() );

		// Get actual enqueued handles for context
		$enqueued_handles = $wp_styles->queue ?? array();

		// Threshold: More than 15 enqueued stylesheets is excessive
		$threshold = 15;

		if ( $enqueued_count <= $threshold ) {
			return null; // Pass
		}

		// Identify plugin vs core stylesheets
		$plugin_styles = array();
		$theme_styles  = array();
		$core_styles   = array();

		foreach ( $enqueued_handles as $handle ) {
			if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
				continue;
			}

			$src = $wp_styles->registered[ $handle ]->src ?? '';

			if ( strpos( $src, 'wp-includes' ) !== false || strpos( $src, 'wp-admin' ) !== false ) {
				$core_styles[] = $handle;
			} elseif ( strpos( $src, 'wp-content/plugins' ) !== false ) {
				$plugin_styles[] = $handle;
			} elseif ( strpos( $src, 'wp-content/themes' ) !== false ) {
				$theme_styles[] = $handle;
			}
		}

		$plugin_count = count( $plugin_styles );
		$theme_count  = count( $theme_styles );

		return array(
			'id'            => 'admin-css-file-count',
			'title'         => 'Too Many CSS Files in Admin Dashboard',
			'description'   => sprintf(
				'WordPress admin is loading %d CSS files (%d from plugins, %d from theme). This causes render blocking and slower page loads. Recommended: Under %d files.',
				$enqueued_count,
				$plugin_count,
				$theme_count,
				$threshold
			)
			'kb_link'      => 'https://wpshadow.com/kb/admin-css-bloat',
			'training_link' => 'https://wpshadow.com/training/optimize-admin-assets',
			'auto_fixable'  => false,
			'threat_level'  => 45, // Medium-high priority
			'module'        => 'admin-performance',
			'priority'      => 3,
			'meta'          => array(
				'css_count'     => $enqueued_count,
				'plugin_styles' => $plugin_count,
				'theme_styles'  => $theme_count,
				'threshold'     => $threshold,
				'top_culprits'  => array_slice( $plugin_styles, 0, 5 ), // Show top 5 plugin styles
			),
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array {
		return array(
			'name'        => 'Admin CSS File Count',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects excessive CSS files loaded in WordPress admin',
		);
	}
}
