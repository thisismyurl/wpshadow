<?php
/**
 * WordPress Updates Available Treatment
 *
 * Checks for pending WordPress core, plugin, or theme updates.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1310
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_WordPress_Updates_Available Class
 *
 * Detects available updates for WordPress core, plugins, and themes.
 *
 * @since 1.6035.1310
 */
class Treatment_WordPress_Updates_Available extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-updates-available';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Updates Available';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for pending core, plugin, or theme updates';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$core_updates   = get_core_updates();
		$plugin_updates = get_plugin_updates();
		$theme_updates  = get_theme_updates();

		$core_pending = false;
		if ( is_array( $core_updates ) ) {
			foreach ( $core_updates as $update ) {
				if ( isset( $update->response ) && 'upgrade' === $update->response ) {
					$core_pending = true;
					break;
				}
			}
		}

		if ( $core_pending || ! empty( $plugin_updates ) || ! empty( $theme_updates ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Updates are available for WordPress core, plugins, or themes. Apply updates to stay secure.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-updates-available',
				'meta'         => array(
					'core_updates'   => $core_pending,
					'plugin_updates' => count( $plugin_updates ),
					'theme_updates'  => count( $theme_updates ),
				),
			);
		}

		return null;
	}
}