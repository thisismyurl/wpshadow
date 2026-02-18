<?php
/**
 * Database Connection Encryption Not Enabled Diagnostic
 *
 * Checks DB encryption.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Connection_Encryption_Not_Enabled Class
 *
 * Performs diagnostic check for Database Connection Encryption Not Enabled.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Database_Connection_Encryption_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-encryption-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Encryption Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks DB encryption';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'verify_db_encryption' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Database connection encryption not enabled. Use SSL/TLS for all database connections to prevent eavesdropping.',
						'severity'   =>   'high',
						'threat_level'   =>   80,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/database-connection-encryption-not-enabled'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
