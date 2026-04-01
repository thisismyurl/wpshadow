<?php
/**
 * NPS Score Implementation
 *
 * Checks if NPS (Net Promoter Score) is being tracked.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NPS Score Implementation Diagnostic
 */
class Diagnostic_Nps_Score_Implementation extends Diagnostic_Base {

	protected static $slug = 'nps-score-implementation';
	protected static $title = 'NPS Score Implementation';
	protected static $description = 'Checks if NPS scoring is implemented';
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$nps_plugins = array(
			'delighted/delighted.php'       => 'Delighted',
			'promoter-io/promoter-io.php'   => 'Promoter.io',
			'survicate/survicate.php'       => 'Survicate',
			'nice-nps/nice-nps.php'         => 'Nice NPS',
		);

		$active_plugins = array();
		foreach ( $nps_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins[] = $name;
			}
		}

		$stats['nps_tools_found'] = count( $active_plugins );

		if ( empty( $active_plugins ) ) {
			$issues[] = __( 'No NPS tracking tool found on your site', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'NPS helps you understand customer loyalty and identify promoters vs. detractors', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/nps-score-implementation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array( 'stats' => $stats, 'issues' => $issues ),
			);
		}

		return null;
	}
}
