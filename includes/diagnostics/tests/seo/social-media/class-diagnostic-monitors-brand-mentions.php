<?php
/**
 * Listening Program Active Diagnostic
 *
 * Tests if brand mentions are monitored across platforms.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Listening Program Active Diagnostic Class
 *
 * Verifies that brand mention monitoring is configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Monitors_Brand_Mentions extends Diagnostic_Base {

	protected static $slug = 'monitors-brand-mentions';
	protected static $title = 'Listening Program Active';
	protected static $description = 'Tests if brand mentions are monitored across platforms';
	protected static $family = 'social-media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_brand_mentions_monitoring' );
		if ( $manual_flag ) {
			return null;
		}

		$last_review = (int) get_option( 'wpshadow_brand_mentions_last_review' );
		if ( $last_review ) {
			$days = floor( ( time() - $last_review ) / DAY_IN_SECONDS );
			if ( $days <= 30 ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No brand mention monitoring detected. Use a listening tool to respond quickly to reviews and feedback.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/listening-program-active?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'publisher',
		);
	}
}
