<?php
/**
 * Treatment: Remove REST API Head Link
 *
 * Stores a WPShadow option that instructs the plugin bootstrap to call
 * remove_action( 'wp_head', 'rest_output_link_wp_head' ) on every request.
 * This removes the REST API discovery link tag from <head>. The REST API
 * itself remains fully functional — only the <head> advertisement is removed.
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
 * Removes the REST API discovery link from <head>.
 *
 * Removing this link does not disable the REST API — it only stops
 * advertising the endpoint URL in every page's <head>.
 */
class Treatment_Rest_Api_Head_Link extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'rest-api-head-link';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set the removal toggle.
	 *
	 * @return array
	 */
	public static function apply() {
		update_option( 'wpshadow_remove_rest_api_head_link', true, false );

		return array(
			'success' => true,
			'message' => __( 'REST API discovery link removed from <head>. The REST API itself is unaffected. Takes effect on the next page load.', 'wpshadow' ),
		);
	}

	/**
	 * Remove the toggle, restoring the default <head> output.
	 *
	 * @return array
	 */
	public static function undo() {
		delete_option( 'wpshadow_remove_rest_api_head_link' );

		return array(
			'success' => true,
			'message' => __( 'REST API discovery link restored to <head>.', 'wpshadow' ),
		);
	}
}
