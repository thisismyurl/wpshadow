<?php
/**
 * No Birthday or Anniversary Rewards Program Diagnostic
 *
 * Checks if customer loyalty rewards are incentivized by special occasions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Birthday/Anniversary Rewards Diagnostic
 *
 * Detects when business isn't leveraging customer birthdays/anniversaries
 * for loyalty and retention. Birthday offers have 40% higher redemption rates
 * and generate goodwill that increases retention. Simple to implement, high ROI.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Birthday_Or_Anniversary_Rewards extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-birthday-anniversary-rewards';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Birthday & Anniversary Rewards Program Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer loyalty rewards are incentivized by special occasions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_rewards = self::check_birthday_rewards();

		if ( ! $has_rewards ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No birthday/anniversary rewards detected. You\'re missing easy, high-ROI loyalty moments. Birthday offers have 40% higher redemption rates and generate goodwill. Implement: 1) Collect birthdays at signup, 2) Send birthday email 2 days before, 3) Offer $10-15% discount (or product), 4) Anniversary discount for account/purchase date. Track: Birthday email open rate (30-50%), redemption rate (10-20%), revenue impact.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/birthday-anniversary-rewards?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'rewards_active'        => false,
					'reward_types'          => self::get_reward_types(),
					'implementation_steps'  => self::get_implementation_steps(),
					'business_impact'       => '40% higher redemption vs regular offers, loyalty boost, repeat purchase rate +10-15%',
					'recommendation'        => __( 'Implement birthday email with discount/gift in next 30 days', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if birthday rewards exist
	 *
	 * @since 0.6093.1200
	 * @return bool True if birthday rewards active
	 */
	private static function check_birthday_rewards(): bool {
		// Check for rewards/loyalty plugins
		$plugins = get_plugins();

		$reward_keywords = array( 'birthday', 'anniversary', 'loyalty', 'rewards', 'points' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $reward_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get reward types
	 *
	 * @since 0.6093.1200
	 * @return array Array of reward types
	 */
	private static function get_reward_types(): array {
		return array(
			array(
				'reward'       => 'Discount %',
				'example'      => '$10 off or 15% discount',
				'value_sent'   => '$10-50 depending on AOV',
				'effort'       => 'Low (coupon code)',
				'redemption'   => '40-50%',
			),
			array(
				'reward'       => 'Free Product',
				'example'      => 'Free sample, free upgrade for 1 month',
				'value_sent'   => '$5-25',
				'effort'       => 'Medium (fulfillment)',
				'redemption'   => '35-45%',
			),
			array(
				'reward'       => 'Loyalty Points',
				'example'      => '500 loyalty points (worth $10-15)',
				'value_sent'   => '$10-15',
				'effort'       => 'Medium (point system)',
				'redemption'   => '50-60%',
			),
			array(
				'reward'       => 'Free Shipping',
				'example'      => 'Free shipping on birthday order',
				'value_sent'   => '$5-15 (shipping cost)',
				'effort'       => 'Low',
				'redemption'   => '30-40%',
			),
			array(
				'reward'       => 'Early Access/Exclusive',
				'example'      => 'Early access to new product or sale',
				'value_sent'   => '$0 cost, psychological value high',
				'effort'       => 'Low',
				'redemption'   => '20-30%',
			),
		);
	}

	/**
	 * Get implementation steps
	 *
	 * @since 0.6093.1200
	 * @return array Array of implementation steps
	 */
	private static function get_implementation_steps(): array {
		return array(
			'Step 1' => 'Collect birthdays: Add birthday field to checkout/registration',
			'Step 2' => 'Set up email: Create birthday email template with offer',
			'Step 3' => 'Schedule automation: Send 2 days before birthday',
			'Step 4' => 'Create coupon: Generate reusable birthday discount code',
			'Step 5' => 'Track metrics: Monitor opens, clicks, redemption rate',
			'Step 6' => 'Optimize: Test subject lines, offers, timing',
			'Step 7' => 'Scale: Add anniversary/milestone emails (first purchase, account anniversary)',
		);
	}
}
