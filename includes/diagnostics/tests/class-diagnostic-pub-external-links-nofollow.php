<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: External Links Use No-Follow
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Are external links properly marked with rel=nofollow?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5+ implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_External_Links_Nofollow extends Diagnostic_Base {
	protected static $slug = 'pub-external-links-nofollow';
	protected static $title = 'External Links Use No-Follow';
	protected static $description = 'Are external links properly marked with rel=nofollow?';
	protected static $category = 'Content Publishing';
	protected static $threat_level = 'low';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Check if SEO or link management plugin is active
		$link_plugins = [
			'wordpress-seo',
			'all-in-one-seo-pack',
			'rank-math',
			'broken-link-checker',
			'jetpack'
		];

		$has_plugin = false;
		foreach ( $link_plugins as $plugin ) {
			if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ||
				 is_plugin_active( $plugin ) ) {
				$has_plugin = true;
				break;
			}
		}

		if ( ! $has_plugin ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-external-links-nofollow',
				'No Link Management Plugin',
				'Consider using a plugin to manage external link rel attributes and prevent SEO juice leakage.',
				'Content Publishing',
				'low',
				'informational'
			);
		}

		return null;
	}
}
