<?php
/**
 * IP Blocking Rules Not Configured Diagnostic
 *
 * Checks IP blocking.
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
 * Diagnostic_IP_Blocking_Rules_Not_Configured Class
 *
 * Performs diagnostic check for Ip Blocking Rules Not Configured.
 *
 * @since 1.6033.2033
 */
class Diagnostic_IP_Blocking_Rules_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ip-blocking-rules-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'IP Blocking Rules Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks IP blocking';

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
		if ( ! get_option( 'ip_blocklist_configured' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'IP blocking rules not configured. Block known bad IP ranges and implement adaptive IP blocking to prevent brute force and DDoS attacks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/ip-blocking-rules-not-configured',
			);
		}

		return null;
	}
}
