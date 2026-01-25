<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Outdated Plugins Count
 *
 * Category: Plugins & Extensions
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * How many active plugins need updates?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 3 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Plugins_Outdated_Count extends Diagnostic_Base {

	protected static $slug         = 'plugins-outdated-count';
	protected static $title        = 'Outdated Plugins Count';
	protected static $description  = 'How many active plugins need updates?';
	protected static $category     = 'Plugins & Extensions';
	protected static $threat_level = 'medium';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Get all plugins with update info
		$updates = get_site_transient( 'update_plugins' );

		if ( ! $updates || empty( $updates->response ) ) {
			// All plugins are up to date
			return null;
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$outdated_count = 0;

		foreach ( $updates->response as $plugin_path => $plugin_data ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				++$outdated_count;
			}
		}

		if ( $outdated_count > 5 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'plugins-outdated-count',
				'Multiple Plugin Updates Available',
				sprintf( '%d active plugins have updates available. Consider updating them.', $outdated_count ),
				'Plugins & Extensions',
				'medium',
				'medium'
			);
		} elseif ( $outdated_count > 0 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'plugins-outdated-count',
				'Plugin Updates Available',
				sprintf( '%d active plugin(s) have updates available.', $outdated_count ),
				'Plugins & Extensions',
				'low',
				'low'
			);
		}

		return null;
	}
}
