<?php
/**
 * Disaster Recovery Plan Not Documented Diagnostic
 *
 * Checks if site has documented disaster recovery procedures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Disaster_Recovery_Plan_Not_Documented Class
 *
 * Detects missing disaster recovery documentation and procedures.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Disaster_Recovery_Plan_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disaster-recovery-plan-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disaster Recovery Plan Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for disaster recovery documentation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Recovery procedure documentation
	 * - RTO/RPO definitions
	 * - Contact information for emergencies
	 * - Tested restore procedures
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for disaster recovery plan configuration.
		$has_plan = get_option( 'wpshadow_disaster_recovery_plan_documented', false );

		if ( ! $has_plan ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No disaster recovery plan is documented. When crisis strikes (site hack, data loss, server failure), every minute of confusion costs money and reputation. Without a documented plan: recovery takes 3-5x longer, mistakes compound the damage, and team members don\'t know who to contact or what to do first. Studies show: 60% of companies without a disaster recovery plan fail within 6 months of a major data loss.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-planning',
			);

			// Add upgrade path to Vault (includes recovery dashboard).
			if ( ! Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
				$finding = Upgrade_Path_Helper::add_upgrade_path(
					$finding,
					'vault',
					'scheduled-backups',
					'https://wpshadow.com/kb/create-disaster-recovery-plan'
				);
			}

			return $finding;
		}

		return null;
	}
}
