<?php
/**
 * Treatment: Remove WP Generator Tag
 *
 * Stores a WPShadow option that instructs the plugin bootstrap to call
 * remove_action( 'wp_head', 'wp_generator' ) on the next request. Undo
 * simply deletes the option, restoring the default tag on next load.
 *
 * Risk level: safe — fully reversible option toggle, no file edits.
 *
 * @package WPShadow
 * @since   0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes the WordPress version generator tag from <head>.
 */
class Treatment_Wp_Generator_Tag extends Treatment_Base {

	/**
	 * Finding ID this treatment addresses.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-generator-tag';

	/**
	 * Risk level — option-only toggle, no structural changes.
	 *
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Apply the treatment.
	 *
	 * Sets {@see WPSHADOW_OPT_REMOVE_WP_GENERATOR} so the plugin bootstrap
	 * registers remove_action( 'wp_head', 'wp_generator' ) on every request.
	 *
	 * @return array
	 */
	public static function apply() {
		update_option( 'wpshadow_remove_wp_generator_tag', true, false );

		return array(
			'success' => true,
			'message' => __( 'The WordPress version generator tag will no longer appear in your <head>. The change takes effect on the next page load.', 'wpshadow' ),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Deletes the toggle option so the bootstrap no longer suppresses the tag.
	 *
	 * @return array
	 */
	public static function undo() {
		delete_option( 'wpshadow_remove_wp_generator_tag' );

		return array(
			'success' => true,
			'message' => __( 'WordPress version generator tag restored to <head>.', 'wpshadow' ),
		);
	}
}
