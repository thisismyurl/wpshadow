<?php
/**
 * Treatment: Remove RSD Link
 *
 * Stores a WPShadow option that instructs the plugin bootstrap to call
 * remove_action( 'wp_head', 'rsd_link' ) on every request. This removes
 * the legacy Really Simple Discovery link from <head>.
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
 * Removes the Really Simple Discovery (RSD) link from <head>.
 */
class Treatment_Rsd_Link extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'rsd-link';

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
		update_option( 'wpshadow_remove_rsd_link', true, false );

		return array(
			'success' => true,
			'message' => __( 'RSD link will no longer appear in your <head>. Takes effect on the next page load.', 'wpshadow' ),
		);
	}

	/**
	 * Remove the toggle, restoring the default output.
	 *
	 * @return array
	 */
	public static function undo() {
		delete_option( 'wpshadow_remove_rsd_link' );

		return array(
			'success' => true,
			'message' => __( 'RSD link restored to <head>.', 'wpshadow' ),
		);
	}
}
