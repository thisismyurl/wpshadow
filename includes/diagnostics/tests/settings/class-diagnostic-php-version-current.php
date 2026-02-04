<?php
/**
 * PHP Version Current Diagnostic
 *
 * Ensures PHP version meets minimum requirements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1460
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PHP_Version_Current Class
 *
 * Checks PHP version against recommended minimum (8.1+).
 *
 * @since 1.6035.1460
 */
class Diagnostic_PHP_Version_Current extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-version-current';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version Current';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP version is supported and current';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1460
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$php_version = PHP_VERSION;

		if ( version_compare( $php_version, '8.1', '<' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: PHP version */
					__( 'PHP version is %s. Upgrade to 8.1+ for performance and security.', 'wpshadow' ),
					esc_html( $php_version )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-version-current',
				'meta'         => array(
					'php_version' => $php_version,
				),
			);
		}

		return null;
	}
}