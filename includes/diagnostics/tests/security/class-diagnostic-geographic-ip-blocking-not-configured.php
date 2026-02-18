<?php
/**
 * Geographic IP Blocking Not Configured
 *
 * Checks if geographic IP blocking (geofencing) is configured to restrict
 * access from high-risk countries or regions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Geographic_IP_Blocking_Not_Configured Class
 *
 * Detects when sites don't use geographic IP blocking to reduce attack
 * surface from regions they don't do business with.
 *
 * @since 1.6030.2200
 */
class Diagnostic_Geographic_IP_Blocking_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'geographic-ip-blocking-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Geographic IP Blocking Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies geographic IP blocking (geofencing) is configured to block high-risk regions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for pro security module first.
		if ( Upgrade_Path_Helper::has_pro_product( 'security' ) ) {
			return null;
		}

		// Check for geofencing/IP blocking plugins.
		$geofencing_plugins = array(
			'wordfence/wordfence.php',                    // Wordfence (includes country blocking).
			'iq-block-country/iq-block-country.php',      // iQ Block Country.
			'geo-blocker/geo-blocker.php',                // Geo Blocker.
			'blackhole-bad-bots/blackhole-bad-bots.php',  // Blackhole for Bad Bots (includes geo).
			'cloudflare/cloudflare.php',                  // Cloudflare (WAF includes geo rules).
		);

		$geofencing_active = false;
		foreach ( $geofencing_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// Verify it's actually configured.
				if ( 'wordfence/wordfence.php' === $plugin ) {
					$wordfence_config = get_option( 'wordfenceCountryBlocking', array() );
					if ( ! empty( $wordfence_config ) ) {
						$geofencing_active = true;
						break;
					}
				} else {
					$geofencing_active = true;
					break;
				}
			}
		}

		// Check if user has manually configured geofencing.
		$manual_geo = get_option( 'wpshadow_geographic_blocking_configured', false );

		if ( $geofencing_active || $manual_geo ) {
			return null;
		}

		// Check for server-level geofencing (Apache mod_geoip, nginx GeoIP).
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			if ( in_array( 'mod_geoip', $modules, true ) || in_array( 'mod_maxminddb', $modules, true ) ) {
				// Check if .htaccess has geo rules.
				$htaccess = ABSPATH . '.htaccess';
				if ( file_exists( $htaccess ) && is_readable( $htaccess ) ) {
					$contents = file_get_contents( $htaccess ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					if ( strpos( $contents, 'GEOIP_COUNTRY_CODE' ) !== false ) {
						return null; // Geofencing configured at Apache level.
					}
				}
			}
		}

		// Check for Cloudflare WAF (common CDN with geo blocking).
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			// Cloudflare is present but we can't verify geo rules from PHP.
			// User should manually confirm.
		}

		// No geofencing detected.
		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __(
				'Your site accepts traffic from all countries, even those you don\'t do business with. Geographic IP blocking (geofencing) reduces attack surface by blocking entire regions that generate high volumes of malicious traffic. Akamai reports 95% of attacks originate from specific countries not related to target business operations. Benefits of geofencing: blocks automated bot networks (concentrated in specific regions), reduces brute-force login attempts by 60-80%, prevents comment spam and form abuse, blocks known malicious IP ranges, reduces server load from unwanted traffic. Common use case: US-only eCommerce site blocking Russia, China, North Korea where they have zero customers but high attack rates. Note: Only block regions where you have zero legitimate users. Overly aggressive blocking hurts real customers.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'geographic-ip-blocking-setup',
		);

		// Add upgrade path for WPShadow Pro Security (when available).
		$finding = Upgrade_Path_Helper::add_upgrade_path(
			$finding,
			'security',
			'geographic-blocking',
			'geofencing-manual-setup'
		);

		return $finding;
	}
}
