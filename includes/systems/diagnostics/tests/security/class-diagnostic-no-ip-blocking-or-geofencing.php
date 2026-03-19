<?php
/**
 * No IP Blocking or Geofencing Diagnostic
 *
 * Detects when IP blocking is not configured,
 * allowing attacks from known malicious sources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No IP Blocking or Geofencing
 *
 * Checks whether IP blocking and geofencing
 * are configured to block malicious traffic.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_IP_Blocking_Or_Geofencing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-ip-blocking-geofencing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'IP Blocking & Geofencing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether IP blocking is configured';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for IP blocking plugins
		$has_ip_blocking = is_plugin_active( 'wordfence-security/wordfence.php' ) ||
			is_plugin_active( 'ithemes-security-pro/ithemes-security-pro.php' ) ||
			is_plugin_active( 'ip-geo-block/ip-geo-block.php' );

		if ( ! $has_ip_blocking ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'IP blocking isn\'t configured, which means you can\'t stop repeat attackers. IP blocking lets you: block specific IPs (after detecting attacks), block entire countries (if you only serve one region), automatically block IPs with known malicious history. Geofencing is powerful: if you\'re US-only, why allow logins from North Korea? Good security plugins maintain lists of known-bad IPs and auto-block them.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Attack Prevention',
					'potential_gain' => 'Block 50-70% of automated attacks',
					'roi_explanation' => 'IP blocking stops repeat attackers and geofencing blocks entire malicious regions, preventing 50-70% of attacks.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/ip-blocking-geofencing',
			);
		}

		return null;
	}
}
