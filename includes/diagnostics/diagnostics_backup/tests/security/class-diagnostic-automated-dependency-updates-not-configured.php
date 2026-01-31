<?php
/**
 * Automated Dependency Updates Not Configured Diagnostic
 *
 * Checks if dependency updates are automated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automated Dependency Updates Not Configured Diagnostic Class
 *
 * Detects missing automated updates.
 *
 * @since 1.2601.2351
 */
class Diagnostic_Automated_Dependency_Updates_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automated-dependency-updates-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automated Dependency Updates Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dependency updates are automated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if automatic updates are enabled
		if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) || AUTOMATIC_UPDATER_DISABLED ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automated dependency updates are not configured. Enable automatic updates for WordPress, plugins, and themes to stay secure.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/automated-dependency-updates-not-configured',
			);
		}

		return null;
	}
}
