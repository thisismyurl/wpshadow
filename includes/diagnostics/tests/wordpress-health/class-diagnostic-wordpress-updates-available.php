<?php
/**
 * WordPress Updates Available Diagnostic
 *
 * Checks for pending WordPress core, plugin, or theme updates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WordPress_Updates_Available Class
 *
 * Detects available updates for WordPress core, plugins, and themes.
 *
 * @since 0.6093.1200
 */
class Diagnostic_WordPress_Updates_Available extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-updates-available';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Updates Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for pending core, plugin, or theme updates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
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
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-updates-available?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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