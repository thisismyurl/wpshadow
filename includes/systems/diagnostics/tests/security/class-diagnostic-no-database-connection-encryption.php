<?php
/**
 * No Database Connection Encryption Diagnostic
 *
 * Detects when database connections are not encrypted,
 * exposing data in transit to interception.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Database Connection Encryption
 *
 * Checks whether database connections use
 * SSL/TLS encryption for data in transit.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Database_Connection_Encryption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-database-connection-encryption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Encryption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether DB connections are encrypted';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if database SSL is configured
		$db_ssl = defined( 'MYSQL_CLIENT_FLAGS' ) && ( MYSQL_CLIENT_FLAGS & MYSQLI_CLIENT_SSL );

		if ( ! $db_ssl ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Database connections aren\'t encrypted, which exposes data in transit. If attacker intercepts network traffic between WordPress and database server, they see: all database queries (including passwords), user data, sensitive information. This matters when: database on separate server, shared hosting, cloud databases. Fix: enable MySQL SSL/TLS in wp-config.php (define MYSQL_CLIENT_FLAGS with MYSQLI_CLIENT_SSL). Check with hosting provider for SSL support and certificates.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Data in Transit Protection',
					'potential_gain' => 'Encrypt database traffic to prevent interception',
					'roi_explanation' => 'Database encryption prevents network eavesdropping on database queries containing sensitive data.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/database-connection-encryption',
			);
		}

		return null;
	}
}
