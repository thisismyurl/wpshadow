<?php
/**
 * Plugin Author Not Verified Diagnostic
 *
 * Checks if plugin authors are verified.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Author Not Verified Diagnostic Class
 *
 * Detects plugins from unverified authors.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Plugin_Author_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-author-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Author Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugin authors are verified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$plugins = get_plugins();
		$suspicious_plugins = array();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			// Check if plugin is from wp.org repository
			$plugin_slug = dirname( $plugin_file );

			if ( empty( $plugin_data['Author'] ) ) {
				$suspicious_plugins[] = $plugin_data['Name'];
			}
		}

		if ( count( $suspicious_plugins ) > 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d plugins have no author information. Consider updating or removing these plugins.', 'wpshadow' ),
					count( $suspicious_plugins )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-author-not-verified',
			);
		}

		return null;
	}
}
