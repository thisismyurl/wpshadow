<?php
/**
 * Net Promoter Score (NPS) Implementation Diagnostic
 *
 * Checks if NPS survey tools are implemented for customer loyalty measurement.
 *
 * @package WPShadow\Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Net Promoter Score Implementation
 *
 * Detects whether the site measures customer loyalty through NPS surveys.
 */
class Diagnostic_Net_Promoter_Score_Implementation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'nps-implementation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Net Promoter Score (NPS) Implementation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for NPS survey implementation';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'delighted/delighted.php'              => 'Delighted NPS',
			'typeform/typeform.php'                => 'Typeform',
			'qualtrics/qualtrics.php'              => 'Qualtrics',
			'surveysparrow/surveysparrow.php'      => 'SurveySparrow',
			'netpromoterscore/nps.php'             => 'NPS Plugin',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_nps_tools']    = count( $active );
		$stats['nps_plugins_found']   = $active;

		// Check for NPS or customer satisfaction tracking via options
		$nps_enabled = get_option( 'wpshadow_nps_enabled' );
		$stats['nps_option_found']    = ! empty( $nps_enabled );

		if ( empty( $active ) && empty( $nps_enabled ) ) {
			$issues[] = __( 'No Net Promoter Score system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Net Promoter Score (NPS) is a key metric for measuring customer loyalty and satisfaction. By regularly surveying customers, you can identify detractors and promoters, allowing you to improve customer relationships and grow your business through referrals.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/nps-surveys',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
