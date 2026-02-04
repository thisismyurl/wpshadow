<?php
/**
 * Client Site Uptime % Diagnostic
 *
 * Reports uptime percentage for the last 30 days.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Client_Site_Uptime Class
 *
 * Evaluates uptime percentage stored in monitoring data.
 *
 * @since 1.6035.1430
 */
class Diagnostic_Client_Site_Uptime extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'client-site-uptime';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Client Site Uptime %';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks 30-day uptime percentage for the site';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$uptime = (float) get_option( 'wpshadow_uptime_percentage_30d', 0 );

		if ( 0 === $uptime ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Uptime percentage data not available. Enable monitoring to track availability.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/client-site-uptime',
			);
		}

		if ( $uptime < 99.9 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: uptime percentage */
					__( 'Uptime over the last 30 days is %s%%. Aim for 99.9%% or higher.', 'wpshadow' ),
					number_format_i18n( $uptime, 3 )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/client-site-uptime',
				'meta'         => array(
					'uptime_30d' => $uptime,
				),
			);
		}

		return null;
	}
}