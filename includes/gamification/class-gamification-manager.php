<?php
/**
 * Gamification Manager
 *
 * Central orchestrator for WPShadow gamification system.
 * Phase 8: Gamification System - Core Infrastructure
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since      1.2604.0400
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gamification Manager Class
 *
 * Coordinates achievements, badges, points, and rewards.
 *
 * @since 1.2604.0400
 */
class Gamification_Manager {

	/**
	 * Instance of the manager.
	 *
	 * @var Gamification_Manager|null
	 */
	private static $instance = null;

	/**
	 * User ID for current context.
	 *
	 * @var int
	 */
	private $user_id = 0;

	/**
	 * Get singleton instance.
	 *
	 * @since  1.2604.0400
	 * @return Gamification_Manager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize gamification system.
	 *
	 * @since  1.2604.0400
	 * @return void
	 */
	public static function init() {
		$manager = self::get_instance();

		// Register hooks
		add_action( 'init', array( $manager, 'setup_hooks' ) );
		add_action( 'wp_dashboard_setup', array( $manager, 'register_dashboard_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $manager, 'enqueue_assets' ) );
	}

	/**
	 * Setup action hooks for gamification triggers.
	 *
	 * @since  1.2604.0400
	 * @return void
	 */
	public function setup_hooks() {
		// Diagnostic events
		add_action( 'wpshadow_after_diagnostic_check', array( $this, 'handle_diagnostic_run' ), 10, 3 );

		// Treatment events
		add_action( 'wpshadow_after_treatment_apply', array( $this, 'handle_treatment_applied' ), 10, 3 );

		// Learning events
		add_action( 'wpshadow_kb_article_viewed', array( $this, 'handle_kb_article_viewed' ), 10, 2 );
		add_action( 'wpshadow_training_video_completed', array( $this, 'handle_training_completed' ), 10, 2 );

		// Guardian events
		add_action( 'wpshadow_guardian_scan_completed', array( $this, 'handle_guardian_scan' ), 10, 2 );

		// Workflow events
		add_action( 'wpshadow_workflow_completed', array( $this, 'handle_workflow_completed' ), 10, 2 );
	}

	/**
	 * Handle diagnostic run event.
	 *
	 * @since  1.2604.0400
	 * @param  string     $class   Diagnostic class name.
	 * @param  string     $slug    Diagnostic slug.
	 * @param  array|null $finding Finding result.
	 * @return void
	 */
	public function handle_diagnostic_run( $class, $slug, $finding ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return;
		}

		// Award points
		Points_System::award_points( $user_id, 5, 'diagnostic_run', array(
			'diagnostic' => $slug,
		) );

		// Check for first diagnostic achievement
		$diagnostic_count = Points_System::get_action_count( $user_id, 'diagnostic_run' );

		if ( 1 === $diagnostic_count ) {
			Achievement_Registry::unlock( $user_id, 'first_diagnostic' );
		}

		// Check for diagnostic milestone achievements
		if ( 10 === $diagnostic_count ) {
			Achievement_Registry::unlock( $user_id, 'diagnostic_novice' );
		} elseif ( 50 === $diagnostic_count ) {
			Achievement_Registry::unlock( $user_id, 'diagnostic_expert' );
		} elseif ( 100 === $diagnostic_count ) {
			Achievement_Registry::unlock( $user_id, 'diagnostic_master' );
		}
	}

	/**
	 * Handle treatment applied event.
	 *
	 * @since  1.2604.0400
	 * @param  string $class      Treatment class name.
	 * @param  string $finding_id Finding ID.
	 * @param  array  $result     Treatment result.
	 * @return void
	 */
	public function handle_treatment_applied( $class, $finding_id, $result ) {
		$user_id = get_current_user_id();

		if ( ! $user_id || empty( $result['success'] ) ) {
			return;
		}

		// Award points
		Points_System::award_points( $user_id, 10, 'treatment_applied', array(
			'treatment' => $finding_id,
		) );

		// Check for first treatment achievement
		$treatment_count = Points_System::get_action_count( $user_id, 'treatment_applied' );

		if ( 1 === $treatment_count ) {
			Achievement_Registry::unlock( $user_id, 'first_treatment' );
		}

		// Check for treatment milestone achievements
		if ( 10 === $treatment_count ) {
			Achievement_Registry::unlock( $user_id, 'healer' );
		} elseif ( 50 === $treatment_count ) {
			Achievement_Registry::unlock( $user_id, 'guardian' );
		} elseif ( 100 === $treatment_count ) {
			Achievement_Registry::unlock( $user_id, 'site_savior' );
		}
	}

	/**
	 * Handle KB article viewed event.
	 *
	 * @since  1.2604.0400
	 * @param  int    $user_id    User ID.
	 * @param  string $article_id Article ID.
	 * @return void
	 */
	public function handle_kb_article_viewed( $user_id, $article_id ) {
		if ( ! $user_id ) {
			return;
		}

		// Award points
		Points_System::award_points( $user_id, 2, 'kb_article_viewed', array(
			'article' => $article_id,
		) );

		// Check for learning achievements
		$article_count = Points_System::get_action_count( $user_id, 'kb_article_viewed' );

		if ( 1 === $article_count ) {
			Achievement_Registry::unlock( $user_id, 'knowledge_seeker' );
		} elseif ( 10 === $article_count ) {
			Achievement_Registry::unlock( $user_id, 'avid_learner' );
		} elseif ( 50 === $article_count ) {
			Achievement_Registry::unlock( $user_id, 'knowledge_master' );
		}
	}

	/**
	 * Handle training video completed event.
	 *
	 * @since  1.2604.0400
	 * @param  int    $user_id  User ID.
	 * @param  string $video_id Video ID.
	 * @return void
	 */
	public function handle_training_completed( $user_id, $video_id ) {
		if ( ! $user_id ) {
			return;
		}

		// Award points
		Points_System::award_points( $user_id, 15, 'training_video_completed', array(
			'video' => $video_id,
		) );

		// Check for training achievements
		$video_count = Points_System::get_action_count( $user_id, 'training_video_completed' );

		if ( 1 === $video_count ) {
			Achievement_Registry::unlock( $user_id, 'video_viewer' );
		} elseif ( 5 === $video_count ) {
			Achievement_Registry::unlock( $user_id, 'dedicated_student' );
		} elseif ( 20 === $video_count ) {
			Achievement_Registry::unlock( $user_id, 'academy_graduate' );
		}
	}

	/**
	 * Handle Guardian scan completed event.
	 *
	 * @since  1.2604.0400
	 * @param  int    $user_id User ID.
	 * @param  string $scan_id Scan ID.
	 * @return void
	 */
	public function handle_guardian_scan( $user_id, $scan_id ) {
		if ( ! $user_id ) {
			return;
		}

		// Award points
		Points_System::award_points( $user_id, 20, 'guardian_scan_completed', array(
			'scan_id' => $scan_id,
		) );

		// Check for Guardian achievements
		$scan_count = Points_System::get_action_count( $user_id, 'guardian_scan_completed' );

		if ( 1 === $scan_count ) {
			Achievement_Registry::unlock( $user_id, 'guardian_initiate' );
		} elseif ( 10 === $scan_count ) {
			Achievement_Registry::unlock( $user_id, 'guardian_adept' );
		} elseif ( 50 === $scan_count ) {
			Achievement_Registry::unlock( $user_id, 'guardian_champion' );
		}
	}

	/**
	 * Handle workflow completed event.
	 *
	 * @since  1.2604.0400
	 * @param  int   $user_id     User ID.
	 * @param  array $workflow_id Workflow ID.
	 * @return void
	 */
	public function handle_workflow_completed( $user_id, $workflow_id ) {
		if ( ! $user_id ) {
			return;
		}

		// Award points
		Points_System::award_points( $user_id, 25, 'workflow_completed', array(
			'workflow' => $workflow_id,
		) );

		// Check for workflow achievements
		$workflow_count = Points_System::get_action_count( $user_id, 'workflow_completed' );

		if ( 1 === $workflow_count ) {
			Achievement_Registry::unlock( $user_id, 'automation_beginner' );
		} elseif ( 10 === $workflow_count ) {
			Achievement_Registry::unlock( $user_id, 'automation_expert' );
		} elseif ( 50 === $workflow_count ) {
			Achievement_Registry::unlock( $user_id, 'automation_wizard' );
		}
	}

	/**
	 * Register dashboard widgets.
	 *
	 * @since  1.2604.0400
	 * @return void
	 */
	public function register_dashboard_widgets() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpshadow_gamification_widget',
			__( 'WPShadow Achievements', 'wpshadow' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render gamification dashboard widget.
	 *
	 * @since  1.2604.0400
	 * @return void
	 */
	public function render_dashboard_widget() {
		$user_id        = get_current_user_id();
		$points         = Points_System::get_balance( $user_id );
		$recent_badges  = Badge_System::get_recent_badges( $user_id, 3 );
		$next_milestone = Points_System::get_next_milestone( $user_id );

		?>
		<div class="wpshadow-gamification-widget">
			<div class="gamification-summary">
				<div class="points-display">
					<span class="points-icon">⭐</span>
					<div class="points-info">
						<span class="points-value"><?php echo number_format_i18n( $points ); ?></span>
						<span class="points-label"><?php esc_html_e( 'Points', 'wpshadow' ); ?></span>
					</div>
				</div>

				<?php if ( ! empty( $recent_badges ) ) : ?>
					<div class="recent-badges">
						<h4><?php esc_html_e( 'Recent Badges', 'wpshadow' ); ?></h4>
						<div class="badge-grid">
							<?php foreach ( $recent_badges as $badge ) : ?>
								<div class="badge-item" title="<?php echo esc_attr( $badge['name'] ); ?>">
									<span class="badge-emoji"><?php echo esc_html( $badge['emoji'] ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $next_milestone ) : ?>
					<div class="next-milestone">
						<h4><?php esc_html_e( 'Next Milestone', 'wpshadow' ); ?></h4>
						<div class="milestone-info">
							<span class="milestone-name"><?php echo esc_html( $next_milestone['name'] ); ?></span>
							<div class="progress-bar">
								<div class="progress-fill" style="width: <?php echo esc_attr( $next_milestone['progress'] ); ?>%;"></div>
							</div>
							<span class="progress-text">
								<?php
								printf(
									/* translators: 1: current points, 2: required points */
									esc_html__( '%1$s / %2$s points', 'wpshadow' ),
									number_format_i18n( $next_milestone['current'] ),
									number_format_i18n( $next_milestone['required'] )
								);
								?>
							</span>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="gamification-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-achievements' ) ); ?>" class="button">
					<?php esc_html_e( 'View All Achievements', 'wpshadow' ); ?>
				</a>
				<?php if ( $points >= 1000 ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-rewards' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Redeem Rewards', 'wpshadow' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<style>
			.wpshadow-gamification-widget {
				padding: 0;
			}
			.gamification-summary {
				margin-bottom: 20px;
			}
			.points-display {
				display: flex;
				align-items: center;
				gap: 15px;
				padding: 20px;
				background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
				border-radius: 8px;
				color: #fff;
				margin-bottom: 20px;
			}
			.points-icon {
				font-size: 48px;
			}
			.points-info {
				display: flex;
				flex-direction: column;
			}
			.points-value {
				font-size: 32px;
				font-weight: 700;
				line-height: 1;
			}
			.points-label {
				font-size: 14px;
				opacity: 0.9;
			}
			.recent-badges h4,
			.next-milestone h4 {
				margin: 0 0 10px 0;
				font-size: 14px;
				color: #1d2327;
			}
			.badge-grid {
				display: flex;
				gap: 10px;
			}
			.badge-item {
				width: 50px;
				height: 50px;
				display: flex;
				align-items: center;
				justify-content: center;
				background: #f0f0f0;
				border-radius: 50%;
				font-size: 24px;
			}
			.next-milestone {
				margin-top: 15px;
			}
			.milestone-info {
				background: #f0f0f0;
				padding: 15px;
				border-radius: 8px;
			}
			.milestone-name {
				font-weight: 600;
				display: block;
				margin-bottom: 10px;
			}
			.progress-bar {
				background: #ddd;
				height: 8px;
				border-radius: 4px;
				overflow: hidden;
				margin-bottom: 5px;
			}
			.progress-fill {
				background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
				height: 100%;
				transition: width 0.3s ease;
			}
			.progress-text {
				font-size: 12px;
				color: #757575;
			}
			.gamification-actions {
				display: flex;
				gap: 10px;
			}
		</style>
		<?php
	}

	/**
	 * Enqueue gamification assets.
	 *
	 * @since  1.2604.0400
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( ! str_contains( $hook, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-gamification',
			WPSHADOW_URL . 'assets/css/gamification.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-gamification',
			WPSHADOW_URL . 'assets/js/gamification.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-gamification',
			'wpShadowGamification',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_gamification' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'strings' => array(
					'achievementUnlocked' => __( 'Achievement Unlocked!', 'wpshadow' ),
					'badgeEarned'         => __( 'Badge Earned!', 'wpshadow' ),
					'pointsAwarded'       => __( 'Points Awarded!', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Get user's gamification summary.
	 *
	 * @since  1.2604.0400
	 * @param  int $user_id User ID.
	 * @return array Summary data.
	 */
	public static function get_user_summary( $user_id ) {
		return array(
			'points'              => Points_System::get_balance( $user_id ),
			'badges'              => Badge_System::get_earned_badges( $user_id ),
			'achievements'        => Achievement_Registry::get_unlocked( $user_id ),
			'rank'                => Leaderboard::get_user_rank( $user_id ),
			'next_milestone'      => Points_System::get_next_milestone( $user_id ),
			'redeemable_rewards'  => Reward_System::get_available_rewards( $user_id ),
		);
	}

	/**
	 * Check if gamification is enabled.
	 *
	 * @since  1.2604.0400
	 * @return bool True if enabled.
	 */
	public static function is_enabled() {
		return (bool) get_option( 'wpshadow_gamification_enabled', true );
	}

	/**
	 * Enable or disable gamification.
	 *
	 * @since  1.2604.0400
	 * @param  bool $enabled Whether to enable.
	 * @return bool Success.
	 */
	public static function set_enabled( $enabled ) {
		return update_option( 'wpshadow_gamification_enabled', (bool) $enabled );
	}
}
