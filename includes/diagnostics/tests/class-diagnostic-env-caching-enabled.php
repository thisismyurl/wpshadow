<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Caching Enabled
 *
 * Category: Environment & Infrastructure
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is caching enabled on the site?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 3 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Env_Caching_Enabled extends Diagnostic_Base {

	protected static $slug         = 'env-caching-enabled';
	protected static $title        = 'Caching Enabled';
	protected static $description  = 'Is caching enabled on the site?';
	protected static $category     = 'Environment & Infrastructure';
	protected static $threat_level = 'low';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Check if caching plugin is active
		$caching_plugins = array(
			'wp-super-cache',
			'w3-total-cache',
			'wp-rocket',
			'cache-enabler',
			'litespeed-cache',
			'swift-performance',
			'hummingbird-performance',
		);

		$has_cache = false;
		foreach ( $caching_plugins as $plugin ) {
			if (
				is_plugin_active( $plugin . '/' . $plugin . '.php' ) ||
				is_plugin_active( $plugin )
			) {
				$has_cache = true;
				break;
			}
		}

		// Also check for server-level caching or object cache
		if ( ! $has_cache ) {
			// Check if object cache is enabled
			if ( function_exists( 'wp_cache_get_last_changed' ) ) {
				$has_cache = true;
			}
		}

		if ( ! $has_cache ) {
			return Diagnostic_Lean_Checks::build_finding(
				'env-caching-enabled',
				'No Caching Plugin Detected',
				'Consider enabling a caching plugin to improve site performance.',
				'Environment & Infrastructure',
				'low',
				'low'
			);
		}

		return null;
	}
}
