<?php
/**
 * SQL Injection Attack Prevention Not Tested Diagnostic
 *
 * Checks if SQL injection prevention is tested.
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
 * SQL Injection Attack Prevention Not Tested Diagnostic Class
 *
 * Detects untested SQL injection prevention.
 *
 * @since 1.2601.2352
 */
class Diagnostic_SQL_Injection_Attack_Prevention_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sql-injection-attack-prevention-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SQL Injection Attack Prevention Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SQL injection prevention is tested';

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
		// Check for security scanning plugins
		if ( ! is_plugin_active( 'wordfence/wordfence.php' ) && ! is_plugin_active( 'sucuri-scanner/sucuri.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SQL injection prevention is not tested. Implement automated security testing to detect and prevent SQL injection vulnerabilities.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/sql-injection-attack-prevention-not-tested',
			);
		}

		return null;
	}
}
