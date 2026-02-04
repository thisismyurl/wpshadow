<?php
/**
 * Reward System
 *
 * Manages reward catalog and redemption.
 * Phase 8: Gamification System - Rewards
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since      1.6004.0400
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reward System Class
 *
 * Handles reward redemption and delivery.
 *
 * @since 1.6004.0400
 */
class Reward_System {

	/**
	 * Reward catalog.
	 *
	 * @var array
	 */
	private static $rewards = array();

	/**
	 * Initialize reward system.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function init() {
		self::register_rewards();
	}

	/**
	 * Register all rewards.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	private static function register_rewards() {
		// Guardian AI Credits
		self::register( 'guardian_100', array(
			'name'        => __( '100 Guardian AI Credits', 'wpshadow' ),
			'description' => __( 'Run 100 Guardian AI scans', 'wpshadow' ),
			'cost'        => 1000,
			'type'        => 'guardian_credits',
			'value'       => 100,
			'emoji'       => '🛡️',
			'category'    => 'guardian',
		) );

		self::register( 'guardian_500', array(
			'name'        => __( '500 Guardian AI Credits', 'wpshadow' ),
			'description' => __( 'Run 500 Guardian AI scans', 'wpshadow' ),
			'cost'        => 4500,
			'type'        => 'guardian_credits',
			'value'       => 500,
			'emoji'       => '🛡️',
			'category'    => 'guardian',
		) );

		// Vault Storage
		self::register( 'vault_5gb', array(
			'name'        => __( '5GB Vault Storage', 'wpshadow' ),
			'description' => __( 'Unlock 5GB backup storage', 'wpshadow' ),
			'cost'        => 2000,
			'type'        => 'vault_storage',
			'value'       => 5,
			'emoji'       => '💾',
			'category'    => 'vault',
		) );

		self::register( 'vault_25gb', array(
			'name'        => __( '25GB Vault Storage', 'wpshadow' ),
			'description' => __( 'Unlock 25GB backup storage', 'wpshadow' ),
			'cost'        => 8000,
			'type'        => 'vault_storage',
			'value'       => 25,
			'emoji'       => '💾',
			'category'    => 'vault',
		) );

		// WPShadow Pro Subscriptions
		self::register( 'pro_1month', array(
			'name'        => __( 'WPShadow Pro (1 Month)', 'wpshadow' ),
			'description' => __( 'Unlock all Pro features for 30 days', 'wpshadow' ),
			'cost'        => 3000,
			'type'        => 'pro_subscription',
			'value'       => 30,
			'emoji'       => '⭐',
			'category'    => 'pro',
		) );

		self::register( 'pro_3months', array(
			'name'        => __( 'WPShadow Pro (3 Months)', 'wpshadow' ),
			'description' => __( 'Unlock all Pro features for 90 days', 'wpshadow' ),
			'cost'        => 8000,
			'type'        => 'pro_subscription',
			'value'       => 90,
			'emoji'       => '⭐',
			'category'    => 'pro',
		) );

		// Academy Pro Access
		self::register( 'academy_pro', array(
			'name'        => __( 'Academy Pro (1 Year)', 'wpshadow' ),
			'description' => __( 'Premium training courses and certification', 'wpshadow' ),
			'cost'        => 5000,
			'type'        => 'academy_subscription',
			'value'       => 365,
			'emoji'       => '🎓',
			'category'    => 'academy',
		) );

		// Digital Swag
		self::register( 'desktop_wallpaper', array(
			'name'        => __( 'Exclusive Desktop Wallpaper Pack', 'wpshadow' ),
			'description' => __( '10 high-res WPShadow wallpapers', 'wpshadow' ),
			'cost'        => 250,
			'type'        => 'digital_download',
			'value'       => 'wallpapers',
			'emoji'       => '🖼️',
			'category'    => 'swag',
		) );

		self::register( 'wp_tips_ebook', array(
			'name'        => __( 'WordPress Security & Performance eBook', 'wpshadow' ),
			'description' => __( '100+ expert tips and tricks', 'wpshadow' ),
			'cost'        => 500,
			'type'        => 'digital_download',
			'value'       => 'ebook',
			'emoji'       => '📖',
			'category'    => 'swag',
		) );
	}

	/**
	 * Register a reward.
	 *
	 * @since  1.6004.0400
	 * @param  string $id     Reward ID.
	 * @param  array  $reward Reward data.
	 * @return void
	 */
	public static function register( $id, $reward ) {
		self::$rewards[ $id ] = wp_parse_args(
			$reward,
			array(
				'name'        => '',
				'description' => '',
				'cost'        => 0,
				'type'        => '',
				'value'       => '',
				'emoji'       => '🎁',
				'category'    => 'other',
			)
		);
	}

	/**
	 * Get reward definition.
	 *
	 * @since  1.6004.0400
	 * @param  string $reward_id Reward ID.
	 * @return array|null Reward data or null.
	 */
	public static function get( $reward_id ) {
		return self::$rewards[ $reward_id ] ?? null;
	}

	/**
	 * Get all rewards.
	 *
	 * @since  1.6004.0400
	 * @param  string $category Optional category filter.
	 * @return array All rewards.
	 */
	public static function get_all( $category = '' ) {
		if ( $category ) {
			return array_filter(
				self::$rewards,
				function( $reward ) use ( $category ) {
					return $reward['category'] === $category;
				}
			);
		}

		return self::$rewards;
	}

	/**
	 * Redeem a reward.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id   User ID.
	 * @param  string $reward_id Reward ID.
	 * @return array {
	 *     Redemption result.
	 *
	 *     @type bool   $success Whether redemption succeeded.
	 *     @type string $message Result message.
	 *     @type mixed  $data    Additional data.
	 * }
	 */
	public static function redeem( $user_id, $reward_id ) {
		$reward = self::get( $reward_id );

		if ( ! $reward ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid reward', 'wpshadow' ),
			);
		}

		// Check points balance
		$balance = Points_System::get_balance( $user_id );

		if ( $balance < $reward['cost'] ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: 1: required points, 2: current balance */
					__( 'Insufficient points. Need %1$d, have %2$d', 'wpshadow' ),
					$reward['cost'],
					$balance
				),
			);
		}

		// Spend points
		$spent = Points_System::spend_points(
			$user_id,
			$reward['cost'],
			sprintf(
				/* translators: %s: reward name */
				__( 'Redeemed: %s', 'wpshadow' ),
				$reward['name']
			)
		);

		if ( ! $spent ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to process redemption', 'wpshadow' ),
			);
		}

		// Deliver reward
		$delivery = self::deliver_reward( $user_id, $reward_id, $reward );

		// Record redemption
		self::record_redemption( $user_id, $reward_id, $reward );

		// Trigger action
		do_action( 'wpshadow_reward_redeemed', $user_id, $reward_id, $reward );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'reward_redeemed',
				sprintf(
					/* translators: %s: reward name */
					__( 'Reward redeemed: %s', 'wpshadow' ),
					$reward['name']
				),
				'',
				array( 'reward_id' => $reward_id, 'cost' => $reward['cost'] )
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: reward name */
				__( 'Successfully redeemed: %s', 'wpshadow' ),
				$reward['name']
			),
			'data'    => $delivery,
		);
	}

	/**
	 * Deliver redeemed reward.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id   User ID.
	 * @param  string $reward_id Reward ID.
	 * @param  array  $reward    Reward data.
	 * @return array Delivery data.
	 */
	private static function deliver_reward( $user_id, $reward_id, $reward ) {
		$delivery = array(
			'type'     => $reward['type'],
			'value'    => $reward['value'],
			'delivered' => false,
		);

		switch ( $reward['type'] ) {
			case 'guardian_credits':
				// Add Guardian credits
				$current = (int) get_user_meta( $user_id, 'wpshadow_guardian_credits', true );
				update_user_meta( $user_id, 'wpshadow_guardian_credits', $current + (int) $reward['value'] );
				$delivery['delivered'] = true;
				$delivery['new_balance'] = $current + (int) $reward['value'];
				break;

			case 'vault_storage':
				// Add vault storage (GB)
				$current = (int) get_user_meta( $user_id, 'wpshadow_vault_storage_gb', true );
				update_user_meta( $user_id, 'wpshadow_vault_storage_gb', $current + (int) $reward['value'] );
				$delivery['delivered'] = true;
				$delivery['new_storage'] = $current + (int) $reward['value'];
				break;

			case 'pro_subscription':
				// Extend Pro subscription
				$expiry = get_user_meta( $user_id, 'wpshadow_pro_expiry', true );
				$start = $expiry && strtotime( $expiry ) > time() ? strtotime( $expiry ) : time();
				$new_expiry = date( 'Y-m-d H:i:s', strtotime( "+{$reward['value']} days", $start ) );
				update_user_meta( $user_id, 'wpshadow_pro_expiry', $new_expiry );
				$delivery['delivered'] = true;
				$delivery['expiry'] = $new_expiry;
				break;

			case 'academy_subscription':
				// Extend Academy Pro
				$expiry = get_user_meta( $user_id, 'wpshadow_academy_pro_expiry', true );
				$start = $expiry && strtotime( $expiry ) > time() ? strtotime( $expiry ) : time();
				$new_expiry = date( 'Y-m-d H:i:s', strtotime( "+{$reward['value']} days", $start ) );
				update_user_meta( $user_id, 'wpshadow_academy_pro_expiry', $new_expiry );
				$delivery['delivered'] = true;
				$delivery['expiry'] = $new_expiry;
				break;

			case 'digital_download':
				// Generate download link
				$delivery['delivered'] = true;
				$delivery['download_url'] = self::generate_download_url( $user_id, $reward['value'] );
				break;
		}

		return $delivery;
	}

	/**
	 * Generate secure download URL.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id User ID.
	 * @param  string $item    Item identifier.
	 * @return string Download URL.
	 */
	private static function generate_download_url( $user_id, $item ) {
		$token = wp_hash( $user_id . $item . time() );
		update_user_meta( $user_id, "wpshadow_download_token_{$item}", $token );

		return add_query_arg(
			array(
				'wpshadow_download' => $item,
				'token'             => $token,
				'uid'               => $user_id,
			),
			home_url()
		);
	}

	/**
	 * Record redemption.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id   User ID.
	 * @param  string $reward_id Reward ID.
	 * @param  array  $reward    Reward data.
	 * @return void
	 */
	private static function record_redemption( $user_id, $reward_id, $reward ) {
		$history = get_user_meta( $user_id, 'wpshadow_redemption_history', true );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$history[] = array(
			'timestamp'  => current_time( 'mysql' ),
			'reward_id'  => $reward_id,
			'reward_name' => $reward['name'],
			'cost'       => $reward['cost'],
		);

		update_user_meta( $user_id, 'wpshadow_redemption_history', $history );
	}

	/**
	 * Get user's redemption history.
	 *
	 * @since  1.6004.0400
	 * @param  int $user_id User ID.
	 * @param  int $limit   Number to return.
	 * @return array Redemption history.
	 */
	public static function get_history( $user_id, $limit = 20 ) {
		$history = get_user_meta( $user_id, 'wpshadow_redemption_history', true );

		if ( ! is_array( $history ) ) {
			return array();
		}

		$history = array_reverse( $history );

		return array_slice( $history, 0, $limit );
	}

	/**
	 * Get reward categories.
	 *
	 * @since  1.6004.0400
	 * @return array Category labels.
	 */
	public static function get_categories() {
		return array(
			'guardian' => __( 'Guardian AI', 'wpshadow' ),
			'vault'    => __( 'Vault Storage', 'wpshadow' ),
			'pro'      => __( 'Pro Features', 'wpshadow' ),
			'academy'  => __( 'Academy', 'wpshadow' ),
			'swag'     => __( 'Digital Swag', 'wpshadow' ),
			'other'    => __( 'Other', 'wpshadow' ),
		);
	}
}
