<?php
/**
 * Traffic Analysis Not Prevented Diagnostic
 *
 * Checks traffic analysis.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Traffic_Analysis_Not_Prevented Class
 *
 * Performs diagnostic check for Traffic Analysis Not Prevented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Traffic_Analysis_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'traffic-analysis-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Traffic Analysis Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks traffic analysis';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'prevent_traffic_analysis' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Traffic analysis protections are not detected yet. Adding uniform response handling can reduce information leakage patterns.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/traffic-analysis-not-prevented',
			);
		}

		return null;
	}
}
