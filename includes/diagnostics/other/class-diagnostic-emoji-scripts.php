<?php
declare(strict_types=1);
/**
 * Emoji Scripts Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WordPress emoji scripts are loading unnecessarily.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Emoji_Scripts extends Diagnostic_Base {

	protected static $slug        = 'emoji-scripts';
	protected static $title       = 'Emoji Scripts Loading';
	protected static $description = 'WordPress loads emoji detection scripts on every page. Most modern browsers handle emojis natively.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if treatment is already applied
		$disabled = get_option( 'wpshadow_emoji_scripts_disabled', false );

		if ( $disabled ) {
			return null;
		}

		// Check if emoji scripts are enabled
		$has_emoji_frontend = has_action( 'wp_head', 'print_emoji_detection_script' ) !== false;
		$has_emoji_admin    = has_action( 'admin_print_scripts', 'print_emoji_detection_script' ) !== false;

		if ( ! $has_emoji_frontend && ! $has_emoji_admin ) {
			return null;
		}

		$locations = array();
		if ( $has_emoji_frontend ) {
			$locations[] = 'frontend';
		}
		if ( $has_emoji_admin ) {
			$locations[] = 'admin';
		}

		return array(
			'id'          => 'emoji-scripts',
			'title'       => 'Emoji Scripts Loading',
			'description' => 'WordPress loads emoji detection scripts on the ' . implode( ' and ', $locations ) . '. Modern browsers handle emojis natively, so these scripts are unnecessary for 95% of users and add ~11KB to every page load.',
			'severity'    => 'info',
			'category'    => 'performance',
			'impact'      => 'Adds 11KB JavaScript to every page',
			'fix_time'    => '1 second',
			'kb_article'  => 'emoji-scripts',
		);
	}
}
