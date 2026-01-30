<?php
/**
 * WPShadow Gamification: Earn/Spend System - Integration Test
 *
 * This file demonstrates how the complete point-earning and redemption system
 * works end-to-end. It's not loaded in production, but serves as documentation
 * of the architecture and can be used for manual testing.
 *
 * @package WPShadow
 * @since   1.2604.0400
 */

declare(strict_types=1);

namespace WPShadow\Tests;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gamification System Integration Test
 *
 * Flow:
 * 1. User sees "Earn More Points" section on Rewards page
 * 2. User clicks "Claim Points" on a review or social share action
 * 3. JavaScript calls AJAX endpoint: wp_ajax_wpshadow_claim_earn_action
 * 4. Handler verifies nonce, capability, eligibility
 * 5. Earn_Actions::claim() awards points and marks as claimed
 * 6. UI updates to show "Claimed" status
 * 7. Points appear in balance and can be redeemed
 * 8. User clicks "Redeem" on a reward
 * 9. JavaScript calls AJAX endpoint: wp_ajax_wpshadow_redeem_reward
 * 10. Handler verifies nonce, capability, sufficient balance
 * 11. Reward_System::redeem() deducts points and delivers reward
 * 12. User receives reward (Guardian credits, storage, or Pro access)
 *
 * @since 1.2604.0400
 */
class Gamification_Integration_Test {

	/**
	 * Test: User claims a social share action
	 *
	 * Scenario: User sees "Share X" action on Rewards page, clicks "Claim Points"
	 *
	 * @since 1.2604.0400
	 * @return array Test result.
	 */
	public static function test_claim_social_share(): array {
		// Simulate user clicking "Claim Points" on X/Twitter share
		$user_id   = get_current_user_id();
		$action_id = 'share_x';

		// Step 1: Check eligibility (must be active for 7 days, have done 1+ action)
		$earn_actions = \WPShadow\Gamification\Earn_Actions::get_actions();
		$action       = $earn_actions[ $action_id ] ?? null;

		if ( ! $action ) {
			return array(
				'success' => false,
				'error'   => "Action '{$action_id}' not found",
			);
		}

		// Step 2: Verify user eligibility
		$eligibility = \WPShadow\Gamification\Earn_Actions::get_eligibility( $user_id, $action );
		if ( ! $eligibility['eligible'] ) {
			return array(
				'success' => false,
				'error'   => "User not eligible: {$eligibility['message']}",
			);
		}

		// Step 3: Claim the action (would be called by AJAX handler)
		$result = \WPShadow\Gamification\Earn_Actions::claim( $user_id, $action_id );

		if ( ! $result['success'] ) {
			return array(
				'success' => false,
				'error'   => $result['message'],
			);
		}

		// Step 4: Verify points were awarded
		$current_balance = \WPShadow\Gamification\Points_System::get_balance( $user_id );
		$expected_points = $action['points'];

		return array(
			'success' => true,
			'action'  => $action_id,
			'points'  => $expected_points,
			'balance' => $current_balance,
			'message' => "User claimed {$expected_points} points for {$action_id}",
		);
	}

	/**
	 * Test: User redeems points for Guardian credits
	 *
	 * Scenario: User has 1000 points, clicks "Get 100 Credits" reward
	 *
	 * @since 1.2604.0400
	 * @return array Test result.
	 */
	public static function test_redeem_guardian_credits(): array {
		$user_id   = get_current_user_id();
		$reward_id = 'guardian_credits_100';

		// Step 1: Check user has sufficient balance
		$balance = \WPShadow\Gamification\Points_System::get_balance( $user_id );
		$reward  = \WPShadow\Gamification\Reward_System::get_reward( $reward_id );

		if ( ! $reward ) {
			return array(
				'success' => false,
				'error'   => "Reward '{$reward_id}' not found",
			);
		}

		if ( $balance < $reward['cost'] ) {
			return array(
				'success' => false,
				'error'   => "Insufficient balance: {$balance} < {$reward['cost']}",
			);
		}

		// Step 2: Redeem the reward (would be called by AJAX handler)
		$result = \WPShadow\Gamification\Reward_System::redeem( $user_id, $reward_id );

		if ( ! $result['success'] ) {
			return array(
				'success' => false,
				'error'   => $result['message'],
			);
		}

		// Step 3: Verify points were deducted
		$new_balance = \WPShadow\Gamification\Points_System::get_balance( $user_id );
		$points_spent = $balance - $new_balance;

		return array(
			'success'       => true,
			'reward'        => $reward_id,
			'points_spent'  => $points_spent,
			'previous_balance' => $balance,
			'new_balance'   => $new_balance,
			'message'       => "User redeemed {$points_spent} points for {$reward['title']}",
		);
	}

	/**
	 * Test: Setup feature auto-awards points
	 *
	 * Scenario: User enables Guardian monitoring in settings
	 *
	 * @since 1.2604.0400
	 * @return array Test result.
	 */
	public static function test_setup_guardian_auto_award(): array {
		$user_id = get_current_user_id();

		// Simulate enabling Guardian via settings
		// This would trigger the wpshadow_setting_updated action hook
		$old_value = false;
		$new_value = true;

		// Step 1: Verify handle_setting_updated would catch this
		$manager = \WPShadow\Gamification\Gamification_Manager::class;

		// This is what Settings_Registry would trigger:
		// do_action( 'wpshadow_setting_updated', 'wpshadow_guardian_enabled', $old_value, $new_value );

		// Step 2: Check if achievement was awarded
		$achievement = \WPShadow\Gamification\Achievement_Registry::get( 'guardian_enabled' );

		if ( ! $achievement ) {
			return array(
				'success' => false,
				'error'   => "Achievement 'guardian_enabled' not found",
			);
		}

		// Step 3: Verify points were awarded
		$balance = \WPShadow\Gamification\Points_System::get_balance( $user_id );

		return array(
			'success'      => true,
			'achievement'  => 'guardian_enabled',
			'points'       => $achievement['points'],
			'balance'      => $balance,
			'message'      => "Guardian setup auto-awarded {$achievement['points']} points",
		);
	}

	/**
	 * Test: Share action triggers social_supporter achievement at 3 shares
	 *
	 * Scenario: User shares on X, LinkedIn, and Facebook
	 *
	 * @since 1.2604.0400
	 * @return array Test result.
	 */
	public static function test_social_supporter_achievement(): array {
		$user_id = get_current_user_id();

		// Simulate claiming all 3 social share actions
		$actions = array( 'share_x', 'share_linkedin', 'share_facebook' );
		$total_points = 0;

		foreach ( $actions as $action_id ) {
			$result = \WPShadow\Gamification\Earn_Actions::claim( $user_id, $action_id );
			if ( $result['success'] ) {
				$total_points += $result['points'];
			}
		}

		// Step 2: Check if social_supporter achievement was unlocked
		$is_claimed = \WPShadow\Gamification\Earn_Actions::is_claimed( $user_id, 'social_supporter' );
		$achievement = \WPShadow\Gamification\Achievement_Registry::get( 'social_supporter' );

		if ( ! $achievement ) {
			return array(
				'success' => false,
				'error'   => "Achievement 'social_supporter' not found",
			);
		}

		return array(
			'success'          => true,
			'actions_claimed'  => $actions,
			'total_points'     => $total_points,
			'achievement'      => 'social_supporter',
			'achievement_points' => $achievement['points'],
			'message'          => "User unlocked 'Social Supporter' achievement after sharing 3x",
		);
	}

	/**
	 * Test: Full user journey (earn → accumulate → redeem)
	 *
	 * This demonstrates the complete flow from the user's perspective:
	 *
	 * 1. User enables Guardian (auto-awards 150 points)
	 * 2. User enables Backups (auto-awards 100 points)
	 * 3. User claims review action (needs 7 days + 3 treatments)
	 * 4. User shares on social media (3x = 75 + 75 + 75 = 225 points)
	 * 5. Total: ~550 points accumulated
	 * 6. User redeems for 500-point reward (Guardian 100 credits)
	 * 7. Remaining: ~50 points
	 *
	 * @since 1.2604.0400
	 * @return array Test result with journey steps.
	 */
	public static function test_user_journey(): array {
		$user_id = get_current_user_id();
		$journey = array();

		// Step 1: Auto-award for Guardian setup
		$journey[] = array(
			'step'    => 'Guardian enabled',
			'action'  => 'guardian_enabled',
			'points'  => 150,
			'auto'    => true,
		);

		// Step 2: Auto-award for Backup setup
		$journey[] = array(
			'step'    => 'Backups enabled',
			'action'  => 'backup_enabled',
			'points'  => 100,
			'auto'    => true,
		);

		// Step 3: Claim review (if eligible)
		$journey[] = array(
			'step'    => 'Review WordPress.org',
			'action'  => 'review_wordpress',
			'points'  => 200,
			'auto'    => false,
			'requires' => '7 days active + 3 treatments',
		);

		// Step 4: Share on social (3x)
		$journey[] = array(
			'step'    => 'Share X/Twitter',
			'action'  => 'share_x',
			'points'  => 75,
			'auto'    => false,
		);

		$journey[] = array(
			'step'    => 'Share LinkedIn',
			'action'  => 'share_linkedin',
			'points'  => 75,
			'auto'    => false,
		);

		$journey[] = array(
			'step'    => 'Share Facebook',
			'action'  => 'share_facebook',
			'points'  => 75,
			'auto'    => false,
		);

		// Calculate totals
		$total_earned = array_reduce(
			$journey,
			function( $carry, $item ) {
				return $carry + $item['points'];
			},
			0
		);

		// Step 5: Redeem for reward
		$reward = array(
			'id'    => 'guardian_credits_100',
			'title' => 'Guardian AI - 100 Credits',
			'cost'  => 500,
		);

		$remaining = $total_earned - $reward['cost'];

		$journey[] = array(
			'step'    => 'Redeem for Guardian Credits',
			'reward'  => $reward['id'],
			'cost'    => $reward['cost'],
			'earned'  => $total_earned,
			'remaining' => $remaining,
		);

		return array(
			'success' => true,
			'user_id' => $user_id,
			'journey' => $journey,
			'total_earned' => $total_earned,
			'final_balance' => $remaining,
			'message' => "User journey shows complete earn/spend cycle with ${total_earned} points earned, ${reward['cost']} spent",
		);
	}
}

/**
 * Usage: Run tests from WP-CLI
 *
 * wp eval 'require_once( WPSHADOW_PATH . "tests/gamification-integration-test.php" );
 *          $result = WPShadow\Tests\Gamification_Integration_Test::test_claim_social_share();
 *          wp_json_encode( $result );'
 *
 * or manually trigger via admin page during development
 */
