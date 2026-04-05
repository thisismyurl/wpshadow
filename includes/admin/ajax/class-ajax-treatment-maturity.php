<?php
/**
 * AJAX: Treatment Maturity
 *
 * Returns a structured breakdown of all shipped WPShadow treatments:
 * maturity distribution (shipped vs guidance), risk level breakdown,
 * category breakdown, and reversible count.
 *
 * The data layer powers the "Treatments" section of the dashboard and
 * governance reporting, giving site owners a clear answer to:
 * "Which treatments are production-ready, reversible, and trustworthy today?"
 *
 * @package WPShadow
 * @since   0.7055.1300
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Treatment_Metadata;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment maturity AJAX handler.
 */
class AJAX_Treatment_Maturity extends AJAX_Handler_Base {

	/**
	 * Handle treatment maturity request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_manage_options_request( 'wpshadow_scan_nonce' );

		if ( ! class_exists( Treatment_Metadata::class ) ) {
			self::send_error( __( 'Treatment_Metadata class is unavailable.', 'wpshadow' ) );
			return;
		}

		$counts = Treatment_Metadata::get_counts();

		self::send_success(
			array(
				'counts'       => $counts,
				'summary'      => array(
					/* translators: %d: number of automated treatments */
					'shipped_label'   => sprintf( _n( '%d automated treatment', '%d automated treatments', $counts['shipped'], 'wpshadow' ), $counts['shipped'] ),
					/* translators: %d: number of reversible treatments */
					'reversible_label' => sprintf( _n( '%d reversible', '%d reversible', $counts['reversible'], 'wpshadow' ), $counts['reversible'] ),
					/* translators: %d: number of guidance-only treatments */
					'guidance_label'  => sprintf( _n( '%d guidance-only treatment', '%d guidance-only treatments', $counts['guidance'], 'wpshadow' ), $counts['guidance'] ),
				),
				'generated_at' => time(),
			)
		);
	}
}

\add_action(
	'wp_ajax_wpshadow_treatment_maturity',
	array( '\\WPShadow\\Admin\\Ajax\\AJAX_Treatment_Maturity', 'handle' )
);
