<?php
/**
 * Referral or Advocate Program Diagnostic
 *
 * Detects when referral or customer advocate programs are not implemented
 * to leverage satisfied customers as growth channels.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Referral or Advocate Program
 *
 * Checks whether the site has implemented referral programs
 * or customer advocate initiatives.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Referral_Or_Advocate_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-referral-advocate-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Referral & Advocate Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether referral or advocate programs are in place';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for referral plugins
		$has_referral_plugin = is_plugin_active( 'referral-rock/referral-rock.php' ) ||
			is_plugin_active( 'friendbuy/friendbuy.php' ) ||
			is_plugin_active( 'viral-loops/viral-loops.php' ) ||
			is_plugin_active( 'growsumo/growsumo.php' );

		// Check for custom referral option
		$has_custom_referral = get_option( 'wpshadow_referral_program' );

		if ( ! $has_referral_plugin && ! $has_custom_referral ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not leveraging your happy customers as growth ambassadors. Think of it this way: people trust recommendations from friends 4x more than advertising. Referral programs turn satisfied customers into active marketers. You can offer rewards (discounts, commission, exclusive access) for successful referrals. Most referral customers have 25% higher lifetime value than cold prospects.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Acquisition Cost',
					'potential_gain' => 'Referred customers have 25% higher LTV',
					'roi_explanation' => 'Referred customers trust recommendations more (4x), have lower acquisition cost, and higher retention rates.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/referral-advocate-program',
			);
		}

		return null;
	}
}
