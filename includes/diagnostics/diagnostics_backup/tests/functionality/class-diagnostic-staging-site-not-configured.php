<?php
/**
 * Staging Site Not Configured Diagnostic
 *
 * Checks if a staging site is available.
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
 * Staging Site Not Configured Diagnostic Class
 *
 * Detects missing staging site.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Staging_Site_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'staging-site-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Staging Site Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if staging site is available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for staging plugins
		$staging_plugins = array(
			'wp-staging/wp-staging.php',
			'duplicator/duplicator.php',
			'jetpack/jetpack.php',
		);

		$staging_active = false;
		foreach ( $staging_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$staging_active = true;
				break;
			}
		}

		if ( ! $staging_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No staging site plugin is installed. Testing updates and changes on production is risky. Set up a staging environment first.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/staging-site-not-configured',
			);
		}

		return null;
	}
}
