<?php
/**
 * Gravatar Cache Not Configured Diagnostic
 *
 * Checks if Gravatar images are cached locally.
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
 * Gravatar Cache Not Configured Diagnostic Class
 *
 * Detects missing Gravatar caching.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Gravatar_Cache_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gravatar-cache-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Gravatar Cache Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Gravatar images are cached';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for Gravatar caching plugins
		$gravatar_plugins = array(
			'wp-user-gravatar/wp-user-gravatar.php',
			'gravatar-cache/gravatar-cache.php',
		);

		$gravatar_active = false;
		foreach ( $gravatar_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$gravatar_active = true;
				break;
			}
		}

		if ( ! $gravatar_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Gravatar images are not cached locally. Each comment loads Gravatar from external CDN, impacting performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gravatar-cache-not-configured',
			);
		}

		return null;
	}
}
