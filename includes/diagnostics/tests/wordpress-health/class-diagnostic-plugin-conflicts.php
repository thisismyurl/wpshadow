<?php
/**
 * Plugin Conflicts Diagnostic
 *
 * Detects potentially conflicting plugin combinations (e.g., multiple cache or security plugins).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Conflicts Class
 *
 * Flags known overlapping plugin categories that often cause conflicts.
 *
 * @since 1.6035.1315
 */
class Diagnostic_Plugin_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects potential conflicts from overlapping plugin categories';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$categories = array(
			'cache'    => array(
				'wp-rocket/wp-rocket.php',
				'w3-total-cache/w3-total-cache.php',
				'wp-super-cache/wp-cache.php',
				'litespeed-cache/litespeed-cache.php',
			),
			'security' => array(
				'wordfence/wordfence.php',
				'better-wp-security/better-wp-security.php',
				'sucuri-scanner/sucuri.php',
			),
			'backup'   => array(
				'updraftplus/updraftplus.php',
				'backwpup/backwpup.php',
				'jetpack/jetpack.php',
			),
		);

		$conflicts = array();
		foreach ( $categories as $label => $plugins ) {
			$active = array();
			foreach ( $plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$active[] = $plugin;
				}
			}
			if ( count( $active ) > 1 ) {
				$conflicts[ $label ] = $active;
			}
		}

		if ( ! empty( $conflicts ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multiple plugins in the same category are active, which can cause conflicts.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-conflicts',
				'meta'         => array(
					'conflicts' => $conflicts,
				),
			);
		}

		return null;
	}
}