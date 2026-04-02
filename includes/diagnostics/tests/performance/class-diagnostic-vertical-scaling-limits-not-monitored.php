<?php
/**
 * Vertical Scaling Limits Not Monitored Diagnostic
 *
 * Checks vertical scaling.
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
 * Diagnostic_Vertical_Scaling_Limits_Not_Monitored Class
 *
 * Performs diagnostic check for Vertical Scaling Limits Not Monitored.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Vertical_Scaling_Limits_Not_Monitored extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'vertical-scaling-limits-not-monitored';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Vertical Scaling Limits Not Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks vertical scaling';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'monitor_scaling_limits' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Vertical scaling limits are not being monitored. Keeping an eye on CPU and memory ceilings helps prevent slowdowns during traffic spikes.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/vertical-scaling-limits-not-monitored',
			);
		}

		return null;
	}
}
