<?php
/**
 * Social Media Sharing Plugin Implementation
 *
 * Validates that social media sharing buttons and plugins are properly configured.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Social_Media_Sharing_Plugin Class
 *
 * Checks for proper social media sharing implementation and button visibility.
 *
 * @since 1.6030.2148
 */
class Treatment_Social_Media_Sharing_Plugin extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-sharing-plugin';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Sharing Plugin Implementation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates social media sharing buttons and plugin setup';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Social_Media_Sharing_Plugin' );
	}

	/**
	 * Check if any sharing plugin is installed.
	 *
	 * @since  1.6030.2148
	 * @return bool True if sharing plugin active.
	 */
	private static function has_sharing_plugin() {
		$sharing_plugins = array(
			'social-warfare/index.php',
			'social-pug/index.php',
			'jetpack/jetpack.php',
			'sharethis-share-buttons/sharethis.php',
			'ultimate-social-warfare/index.php',
			'sumo-social-sharing/sumo-social-sharing.php',
			'sassy-social-share/sassy-social-share.php',
		);

		foreach ( $sharing_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get currently active sharing plugin.
	 *
	 * @since  1.6030.2148
	 * @return string Plugin slug or empty string.
	 */
	private static function get_active_sharing_plugin() {
		$sharing_plugins = array(
			'social-warfare/index.php' => 'social-warfare',
			'social-pug/index.php' => 'social-pug',
			'jetpack/jetpack.php' => 'jetpack',
			'sharethis-share-buttons/sharethis.php' => 'sharethis',
			'ultimate-social-warfare/index.php' => 'ultimate-social-warfare',
		);

		foreach ( $sharing_plugins as $plugin => $slug ) {
			if ( is_plugin_active( $plugin ) ) {
				return $slug;
			}
		}

		return '';
	}

	/**
	 * Check plugin configuration.
	 *
	 * @since  1.6030.2148
	 * @param  string $plugin Plugin slug.
	 * @return array Plugin configuration data.
	 */
	private static function check_plugin_configuration( $plugin ) {
		return array(
			'name' => ucwords( str_replace( '-', ' ', $plugin ) ),
			'has_posts' => true,
			'enabled_for_posts' => get_option( $plugin . '_enable_posts', true ),
			'has_visible_buttons' => true,
		);
	}

	/**
	 * Count configured social networks.
	 *
	 * @since  1.6030.2148
	 * @param  string $plugin Plugin slug.
	 * @return int Number of networks.
	 */
	private static function count_configured_networks( $plugin ) {
		$networks = get_option( $plugin . '_networks', array() );
		return is_array( $networks ) ? count( $networks ) : 0;
	}

	/**
	 * Get button placement configuration.
	 *
	 * @since  1.6030.2148
	 * @param  string $plugin Plugin slug.
	 * @return string Placement type.
	 */
	private static function get_button_placement( $plugin ) {
		return get_option( $plugin . '_placement', 'default' );
	}
}
