<?php
/**
 * Plugin Conflict Detection Diagnostic
 *
 * Checks for conflicts between active plugins.
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
 * Plugin Conflict Detection Diagnostic Class
 *
 * Detects conflicting plugins.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Plugin_Conflict_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-conflict-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Conflict Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugin conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$conflicting_pairs = array(
			array( 'wordfence/wordfence.php', 'iThemes-Security-Pro/iThemes-Security-Pro.php' ),
			array( 'jetpack/jetpack.php', 'akismet/akismet.php' ),
			array( 'w3-total-cache/w3-total-cache.php', 'wp-super-cache/wp-cache.php' ),
		);

		foreach ( $conflicting_pairs as $pair ) {
			$first_active = is_plugin_active( $pair[0] );
			$second_active = is_plugin_active( $pair[1] );

			if ( $first_active && $second_active ) {
				$first_name = explode( '/', $pair[0] )[0];
				$second_name = explode( '/', $pair[1] )[0];

				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__( 'Conflicting plugins detected: "%s" and "%s". Deactivate one to avoid issues.', 'wpshadow' ),
						$first_name,
						$second_name
					),
					'severity'      => 'high',
					'threat_level'  => 60,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/plugin-conflict-detection',
				);
			}
		}

		return null;
	}
}
