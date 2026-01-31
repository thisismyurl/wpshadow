<?php
/**
 * Legacy PHP Version Not Upgraded Diagnostic
 *
 * Checks if PHP version is current.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy PHP Version Not Upgraded Diagnostic Class
 *
 * Detects outdated PHP version.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Legacy_PHP_Version_Not_Upgraded extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'legacy-php-version-not-upgraded';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Legacy PHP Version Not Upgraded';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP version is current';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check PHP version - 7.2 is the minimum recommended for WordPress
		if ( version_compare( PHP_VERSION, '7.2', '<' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %s: current PHP version */
					__( 'PHP version %s is outdated. Upgrade to PHP 8.1 or higher for better performance and security.', 'wpshadow' ),
					PHP_VERSION
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/legacy-php-version-not-upgraded',
			);
		}

		return null;
	}
}
