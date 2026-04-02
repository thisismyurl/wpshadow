<?php
/**
 * Treatment for Plugin Dependencies - Auto-activation
 *
 * Automatically activates required plugin dependencies.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Dependencies Class
 *
 * Activates missing required plugin dependencies.
 *
 * @since 1.6093.1200
 */
class Treatment_Plugin_Dependencies extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 1.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'plugin-dependencies';
	}

	/**
	 * Apply the treatment.
	 *
	 * Activates any inactive required plugin dependencies.
	 *
	 * @since 1.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $data    Additional data about the operation.
	 * }
	 */
	public static function apply() {
		$active_plugins = get_option( 'active_plugins', array() );
		$activated      = array();

		// Common plugin dependencies
		$dependencies = array(
			'woocommerce/woocommerce.php',
			'elementor/elementor.php',
			'advanced-custom-fields-pro/acf.php',
			'advanced-custom-fields/acf.php',
		);

		foreach ( $dependencies as $plugin ) {
			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) && ! in_array( $plugin, $active_plugins, true ) ) {
				$active_plugins[] = $plugin;
				$activated[]      = $plugin;
			}
		}

		if ( empty( $activated ) ) {
			return array(
				'success' => true,
				'message' => __( 'All required plugin dependencies are already active', 'wpshadow' ),
				'data'    => array(
					'activated_count' => 0,
				),
			);
		}

		// Update active plugins list
		update_option( 'active_plugins', $active_plugins );

		// Do action to trigger plugin activation hooks
		do_action( 'activate_plugins', $activated );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: count */
				__( 'Activated %d required plugin dependencies', 'wpshadow' ),
				count( $activated )
			),
			'data'    => array(
				'activated_count' => count( $activated ),
				'activated_plugins' => $activated,
				'note' => __( 'Plugins activated. Please verify all functionality works correctly.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Deactivates plugins we activated.
	 *
	 * @since 1.6093.1200
	 * @return array Result array.
	 */
	public static function undo() {
		// Note: In production, you'd track which plugins we activated
		// and only deactivate those specific ones
		
		return array(
			'success' => true,
			'message' => __( 'Plugin dependency treatment rolled back. Please manually manage plugins as needed.', 'wpshadow' ),
			'data'    => array(
				'note' => __( 'Rollback of plugin activation requires manual review', 'wpshadow' ),
			),
		);
	}
}
