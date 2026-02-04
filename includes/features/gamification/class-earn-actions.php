<?php
/**
 * Earn Actions
 *
 * Defines non-invasive, value-driven actions users can complete
 * to earn points (e.g., reviews, shares, and setup milestones).
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
 * Earn Actions Class
 *
 * Provides definitions, eligibility checks, and claim handling
 * for optional point-earning actions.
 *
 * @since 1.6004.0400
 */
class Earn_Actions {

	/**
	 * User meta key for claimed earn actions.
	 */
	const CLAIMS_META_KEY = 'wpshadow_earn_claims';

	/**
	 * Get all available earn actions.
	 *
	 * @since  1.6004.0400
	 * @return array Earn actions definitions.
	 */
	public static function get_actions(): array {
		$site_url  = home_url( '/' );
		$site_name = get_bloginfo( 'name' );
		$app_name  = $site_name ? $site_name : __( 'my site', 'wpshadow' );

		$share_text = sprintf(
			/* translators: %s: site name */
			__( 'I use WPShadow to keep %s healthy and secure.', 'wpshadow' ),
			$app_name
		);

		return array(
			// Community support (manual claim, honor system)
			'review_wordpress' => array(
				'name'         => __( 'Leave a WordPress.org review', 'wpshadow' ),
				'description'  => __( 'If WPShadow helped you, a quick review helps the community. Honor system — we can’t verify reviews.', 'wpshadow' ),
				'points'       => 200,
				'category'     => 'community',
				'url'          => 'https://wordpress.org/support/plugin/wpshadow/reviews/?rate=5#new-post',
				'claimable'    => true,
				'achievement'  => 'community_reviewer',
				'requirements' => array(
					'min_days'    => 7,
					'min_actions' => array(
						'treatment_applied' => 3,
						'diagnostic_run'    => 10,
					),
				),
			),
			'share_x' => array(
				'name'        => __( 'Share on X (Twitter)', 'wpshadow' ),
				'description' => __( 'Tell others how you keep your site healthy. Honor system — we can’t verify shares.', 'wpshadow' ),
				'points'      => 75,
				'category'    => 'community',
				'url'         => 'https://twitter.com/intent/tweet?text=' . rawurlencode( $share_text ) . '&url=' . rawurlencode( $site_url ),
				'claimable'   => true,
			),
			'share_linkedin' => array(
				'name'        => __( 'Share on LinkedIn', 'wpshadow' ),
				'description' => __( 'Share your progress with your professional network. Honor system — we can’t verify shares.', 'wpshadow' ),
				'points'      => 75,
				'category'    => 'community',
				'url'         => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $site_url ),
				'claimable'   => true,
			),
			'share_facebook' => array(
				'name'        => __( 'Share on Facebook', 'wpshadow' ),
				'description' => __( 'Share your progress with friends. Honor system — we can’t verify shares.', 'wpshadow' ),
				'points'      => 75,
				'category'    => 'community',
				'url'         => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $site_url ),
				'claimable'   => true,
			),

			// Setup milestones (auto-awarded)
			'setup_guardian' => array(
				'name'        => __( 'Enable Guardian monitoring', 'wpshadow' ),
				'description' => __( 'Turn on automated monitoring to catch issues early.', 'wpshadow' ),
				'points'      => 150,
				'category'    => 'setup',
				'auto'        => true,
				'setting'     => 'wpshadow_guardian_enabled',
				'achievement' => 'guardian_enabled',
			),
			'setup_backups' => array(
				'name'        => __( 'Enable backups', 'wpshadow' ),
				'description' => __( 'Create safety snapshots before changes.', 'wpshadow' ),
				'points'      => 100,
				'category'    => 'setup',
				'auto'        => true,
				'setting'     => 'wpshadow_backup_enabled',
				'achievement' => 'backup_enabled',
			),
			'setup_backup_schedule' => array(
				'name'        => __( 'Schedule automated backups', 'wpshadow' ),
				'description' => __( 'Set up scheduled backups for ongoing protection.', 'wpshadow' ),
				'points'      => 75,
				'category'    => 'setup',
				'auto'        => true,
				'setting'     => 'wpshadow_backup_schedule_enabled',
				'achievement' => 'backup_scheduled',
			),
			'setup_cloud' => array(
				'name'        => __( 'Connect WPShadow Cloud', 'wpshadow' ),
				'description' => __( 'Enable cloud features like uptime monitoring and AI tools.', 'wpshadow' ),
				'points'      => 150,
				'category'    => 'setup',
				'auto'        => true,
				'setting'     => 'wpshadow_cloud_api_key',
				'achievement' => 'cloud_connected',
			),
		);
	}

	/**
	 * Get action status for the current user.
	 *
	 * @since  1.6004.0400
	 * @param  int $user_id User ID.
	 * @return array Action status data.
	 */
	public static function get_user_status( $user_id ): array {
		$actions = self::get_actions();
		$status  = array();

		foreach ( $actions as $action_id => $action ) {
			$is_auto      = ! empty( $action['auto'] );
			$is_claimed   = self::is_claimed( $user_id, $action_id );
			$is_completed = $is_auto ? self::is_auto_completed( $action ) : false;
			$eligible     = ! $is_auto;
			$message      = '';

			if ( ! $is_auto && ! $is_claimed ) {
				$eligibility = self::get_eligibility( $user_id, $action );
				$eligible    = $eligibility['eligible'];
				$message     = $eligibility['message'];
			} elseif ( $is_claimed ) {
				$eligible = false;
			}

			$status[ $action_id ] = array(
				'claimed'   => $is_claimed,
				'eligible'  => $eligible,
				'completed' => $is_completed,
				'message'   => $message,
			);
		}

		return $status;
	}

	/**
	 * Claim a manual earn action.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id   User ID.
	 * @param  string $action_id Action ID.
	 * @return array Result data.
	 */
	public static function claim( $user_id, $action_id ): array {
		$actions = self::get_actions();

		if ( ! isset( $actions[ $action_id ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Unknown action.', 'wpshadow' ),
			);
		}

		$action = $actions[ $action_id ];

		if ( ! empty( $action['auto'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'This action is awarded automatically.', 'wpshadow' ),
			);
		}

		if ( self::is_claimed( $user_id, $action_id ) ) {
			return array(
				'success' => false,
				'message' => __( 'You already claimed this reward.', 'wpshadow' ),
			);
		}

		$eligibility = self::get_eligibility( $user_id, $action );
		if ( ! $eligibility['eligible'] ) {
			return array(
				'success' => false,
				'message' => $eligibility['message'],
			);
		}

		// Award via achievement or direct points.
		if ( 0 === strpos( $action_id, 'share_' ) ) {
			Points_System::award_points(
				$user_id,
				(int) $action['points'],
				'social_share',
				array( 'network' => $action_id )
			);

			$share_count = Points_System::get_action_count( $user_id, 'social_share' );
			if ( $share_count >= 3 ) {
				Achievement_Registry::unlock( $user_id, 'social_supporter' );
			}
		} elseif ( ! empty( $action['achievement'] ) ) {
			Achievement_Registry::unlock( $user_id, $action['achievement'] );
		} else {
			Points_System::award_points(
				$user_id,
				(int) $action['points'],
				'action_claimed',
				array( 'action' => $action_id )
			);
		}

		self::mark_claimed( $user_id, $action_id );

		return array(
			'success' => true,
			'message' => __( 'Points awarded. Thanks for supporting WPShadow!', 'wpshadow' ),
			'points'  => (int) $action['points'],
		);
	}

	/**
	 * Mark an action as claimed for the user.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id   User ID.
	 * @param  string $action_id Action ID.
	 * @return void
	 */
	public static function mark_claimed( $user_id, $action_id ): void {
		$claims = get_user_meta( $user_id, self::CLAIMS_META_KEY, true );
		if ( ! is_array( $claims ) ) {
			$claims = array();
		}

		$claims[ $action_id ] = current_time( 'mysql' );
		update_user_meta( $user_id, self::CLAIMS_META_KEY, $claims );
	}

	/**
	 * Check if an action has been claimed.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id   User ID.
	 * @param  string $action_id Action ID.
	 * @return bool True if claimed.
	 */
	public static function is_claimed( $user_id, $action_id ): bool {
		$claims = get_user_meta( $user_id, self::CLAIMS_META_KEY, true );
		return is_array( $claims ) && isset( $claims[ $action_id ] );
	}

	/**
	 * Determine if an auto action is completed.
	 *
	 * @since  1.6004.0400
	 * @param  array $action Action data.
	 * @return bool True if completed.
	 */
	private static function is_auto_completed( array $action ): bool {
		if ( empty( $action['setting'] ) ) {
			return false;
		}

		$value = get_option( $action['setting'], '' );

		return (bool) rest_sanitize_boolean( $value ) || ( ! empty( $value ) && ! is_bool( $value ) );
	}

	/**
	 * Get eligibility for a claimable action.
	 *
	 * @since  1.6004.0400
	 * @param  int   $user_id User ID.
	 * @param  array $action  Action data.
	 * @return array {eligible:bool, message:string}
	 */
	private static function get_eligibility( $user_id, array $action ): array {
		$requirements = $action['requirements'] ?? array();

		if ( empty( $requirements ) ) {
			return array(
				'eligible' => true,
				'message'  => '',
			);
		}

		$meets = false;

		if ( ! empty( $requirements['min_days'] ) ) {
			$install_date = (int) get_option( 'wpshadow_install_date', time() );
			$days_active  = floor( ( time() - $install_date ) / DAY_IN_SECONDS );
			if ( $days_active >= (int) $requirements['min_days'] ) {
				$meets = true;
			}
		}

		if ( ! empty( $requirements['min_actions'] ) && is_array( $requirements['min_actions'] ) ) {
			foreach ( $requirements['min_actions'] as $action_key => $min_count ) {
				if ( Points_System::get_action_count( $user_id, $action_key ) >= (int) $min_count ) {
					$meets = true;
					break;
				}
			}
		}

		if ( $meets ) {
			return array(
				'eligible' => true,
				'message'  => '',
			);
		}

		return array(
			'eligible' => false,
			'message'  => __( 'Use WPShadow a bit more before claiming this reward.', 'wpshadow' ),
		);
	}
}
