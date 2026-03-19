<?php
/**
 * MX Records Configuration Diagnostic
 *
 * Verifies that MX records are configured for email routing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MX Records Configuration Diagnostic Class
 *
 * Detects missing or invalid MX records for the site domain.
 *
 * @since 1.6093.1200
 */
class Diagnostic_MX_Records_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mx-records-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'MX Records Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that MX records exist for email delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$domain = Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );
		if ( empty( $domain ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to determine the site domain for MX validation. Check Site Address URL settings.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records-configuration',
			);
		}

		if ( ! function_exists( 'dns_get_record' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'MX record validation is unavailable because dns_get_record is disabled.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records-configuration',
			);
		}

		$records = @dns_get_record( $domain, DNS_MX );
		if ( empty( $records ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No MX records were found for your domain. Email delivery may fail without proper MX routing.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records-configuration',
				'meta'         => array(
					'domain' => $domain,
				),
			);
		}

		$targets = array();
		foreach ( $records as $record ) {
			if ( ! empty( $record['target'] ) ) {
				$targets[] = $record['target'];
			}
		}

		if ( empty( $targets ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'MX records are present but no targets were detected. Verify DNS configuration with your email provider.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records-configuration',
				'meta'         => array(
					'domain'  => $domain,
					'targets' => $targets,
				),
			);
		}

		return null;
	}
}
