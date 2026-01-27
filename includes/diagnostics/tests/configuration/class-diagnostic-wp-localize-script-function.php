<?php
/**
 * Diagnostic: wp_localize_script Function
 *
 * Checks if wp_localize_script is being used for passing data to JavaScript.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Localize_Script_Function
 *
 * Tests for proper use of wp_localize_script for passing data to scripts.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Localize_Script_Function extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-localize-script-function';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'wp_localize_script Function';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper use of wp_localize_script for data passing';

	/**
	 * Check wp_localize_script usage.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! is_object( $wp_scripts ) ) {
			return null;
		}

		// Check if any scripts have localized data.
		$has_localized = false;

		foreach ( $wp_scripts->registered as $script ) {
			if ( ! empty( $script->extra ) && isset( $script->extra['data'] ) ) {
				$has_localized = true;
				break;
			}
		}

		// If no localized scripts found, this is informational only.
		if ( ! $has_localized ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No wp_localize_script data found. If scripts need server data, use wp_localize_script to pass variables instead of embedding them in HTML.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_localize_script_function',
				'meta'        => array(
					'has_localized_data' => false,
				),
			);
		}

		return null;
	}
}
