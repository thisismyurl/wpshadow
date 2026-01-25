<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Posts Have Descriptions
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Do posts have meta descriptions (for SEO)?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5+ implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Description_Length extends Diagnostic_Base {
	protected static $slug         = 'pub-description-length';
	protected static $title        = 'Posts Have Descriptions';
	protected static $description  = 'Do posts have meta descriptions (for SEO)?';
	protected static $category     = 'Content Publishing';
	protected static $threat_level = 'low';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Check if SEO plugin is active (they handle meta descriptions)
		$seo_plugins = array(
			'wordpress-seo',
			'all-in-one-seo-pack',
			'rank-math',
			'the-seo-framework',
			'seo-by-yoast',
			'jetpack',
		);

		$has_seo_plugin = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ||
				is_plugin_active( $plugin ) ) {
				$has_seo_plugin = true;
				break;
			}
		}

		if ( ! $has_seo_plugin ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-description-length',
				'No SEO Plugin for Meta Descriptions',
				'Consider installing an SEO plugin to manage meta descriptions and improve search visibility.',
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}
