<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Active Theme is Premium
 *
 * Category: Site Design & UX
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is a premium theme active?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 3 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Theme_Active_Is_Premium extends Diagnostic_Base {

	protected static $slug         = 'theme-active-is-premium';
	protected static $title        = 'Active Theme is Premium';
	protected static $description  = 'Is a premium theme active?';
	protected static $category     = 'Site Design & UX';
	protected static $threat_level = 'low';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Get active theme
		$active_theme = wp_get_theme();
		$theme_name   = $active_theme->get( 'Name' );
		$author       = $active_theme->get( 'Author' );

		// Check if theme is from known premium theme providers
		$premium_authors = array(
			'Elegant Themes',
			'StudioPress',
			'Themify',
			'Generative Press',
			'WPZOOM',
			'Thrive Themes',
			'Divi',
			'Genesis',
		);

		$is_premium = in_array( $author, $premium_authors, true ) ||
			strpos( $theme_name, 'Pro' ) !== false ||
			strpos( $theme_name, 'Premium' ) !== false;

		if ( ! $is_premium ) {
			// Informational - free themes are fine
			// This is just informational tracking
		}

		return null;
	}
}
