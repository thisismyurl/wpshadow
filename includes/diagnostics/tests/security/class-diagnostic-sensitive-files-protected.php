<?php
/**
 * Sensitive Files Protected Diagnostic
 *
 * Checks the webroot and wp-content directory for sensitive files such as
 * .env files, config backups, SQL dumps, debug logs, and exposed git
 * repositories that could leak credentials or site internals.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Sensitive_Files_Protected Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Sensitive_Files_Protected extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'sensitive-files-protected';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Sensitive Files Protected';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks the webroot and wp-content directory for sensitive files including .env configs, WordPress config backups, SQL dumps, debug logs, and exposed git repositories.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for the presence of a predefined list of sensitive file paths
	 * on disk. Does not verify that the files are HTTP-accessible — file
	 * presence alone is flagged since server configuration cannot be assumed.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$webroot = rtrim( ABSPATH, '/' );

		$sensitive_patterns = array(
			$webroot . '/.env'                     => '.env (environment variables)',
			$webroot . '/.env.local'               => '.env.local',
			$webroot . '/.env.production'          => '.env.production',
			$webroot . '/wp-config.php.bak'        => 'wp-config.php.bak (config backup)',
			$webroot . '/wp-config.bak'            => 'wp-config.bak (config backup)',
			$webroot . '/wp-config.old'            => 'wp-config.old (config backup)',
			$webroot . '/wp-config~'               => 'wp-config~ (editor backup)',
			$webroot . '/database.sql'             => 'database.sql',
			$webroot . '/dump.sql'                 => 'dump.sql',
			$webroot . '/backup.sql'               => 'backup.sql',
			$webroot . '/error_log'                => 'error_log',
			$webroot . '/phpinfo.php'              => 'phpinfo.php',
			$webroot . '/.git/config'              => '.git/config (exposed git repository)',
			WP_CONTENT_DIR . '/debug.log'          => 'wp-content/debug.log (WordPress debug log)',
		);

		$found = array();
		foreach ( $sensitive_patterns as $path => $label ) {
			if ( file_exists( $path ) ) {
				$found[] = $label;
			}
		}

		if ( empty( $found ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of found sensitive files */
				__( 'Potentially sensitive files were found in accessible locations: %s. These files may be publicly reachable depending on server configuration and can expose credentials, database contents, debug output, or source code. Remove unnecessary files, move them outside the webroot, or block access via server rules.', 'wpshadow' ),
				implode( '; ', $found )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'details'      => array(
				'exposed_files' => $found,
			),
		);
	}
}
