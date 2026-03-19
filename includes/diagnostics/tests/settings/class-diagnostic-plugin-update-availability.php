<?php
/**
 * Plugin Update Availability Diagnostic
 *
 * Reports available plugin updates and security patches.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Update Availability Diagnostic
 *
 * Checks if plugin updates are available and pending.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Update_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-update-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Update Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Reports available plugin updates and security patches';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$updates = get_transient( 'update_plugins' );
		if ( empty( $updates ) || empty( $updates->response ) ) {
			return null;
		}

		$pending_updates = array();
		$security_updates = array();

		foreach ( $updates->response as $plugin_file => $data ) {
			$pending_updates[] = $plugin_file;

			if ( ! empty( $data->upgrade_notice ) && false !== strpos( strtolower( $data->upgrade_notice ), 'security' ) ) {
				$security_updates[] = $plugin_file;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Plugin updates are available', 'wpshadow' ),
			'severity'     => ! empty( $security_updates ) ? 'high' : 'medium',
			'threat_level' => ! empty( $security_updates ) ? 80 : 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/plugin-update-availability',
			'details'      => array(
				'pending_updates'  => $pending_updates,
				'security_updates' => $security_updates,
			),
		);
	}
}
