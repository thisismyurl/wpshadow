<?php
/**
 * Network Segmentation Not Implemented Diagnostic
 *
 * Checks network segmentation.
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
 * Diagnostic_Network_Segmentation_Not_Implemented Class
 *
 * Performs diagnostic check for Network Segmentation Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Network_Segmentation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'network-segmentation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Network Segmentation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks network segmentation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if network segmentation is implemented.
		// Network segmentation isolates database servers and application servers
		// on separate network segments, limiting damage if one is compromised.

		// This is an infrastructure-level configuration, not a WordPress setting.
		// We check for common practices and warn if not observed.

		// Check if database is on same server as WordPress (less secure).
		global $wpdb;

		$db_host = DB_HOST;
		$site_url = get_site_url();

		// Extract domain/IP from site URL.
		$app_server = wp_parse_url( $site_url, PHP_URL_HOST );

		// If DB host and app server are localhost or same, segmentation not implemented.
		$same_server = (
			strtolower( $db_host ) === 'localhost' ||
			strtolower( $db_host ) === '127.0.0.1' ||
			strtolower( $db_host ) === $app_server
		);

		if ( $same_server ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your database and web server are on the same machine (like storing your safe in the bedroom instead of a separate vault). If one is compromised, both are at risk. Production sites should use separate servers for database and web application. This is done at the hosting level - contact your provider about moving the database to a dedicated server.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/network-segmentation-not-implemented',
				'context'      => array(
					'db_host'     => $db_host,
					'app_server'  => $app_server,
					'same_server' => $same_server,
				),
			);
		}

		// Database and app server are separated - good!
		return null;
	}
}
