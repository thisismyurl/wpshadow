<?php
/**
 * Visual Health Journey
 *
 * Creates interactive timeline visualizations showing site health improvements,
 * milestones, and progress storytelling. Celebrates wins, not just problems.
 *
 * Philosophy:
 * - #8 Inspire Confidence: Celebrate progress and improvements
 * - #11 Talk-About-Worthy: Shareable success stories
 * - #1 Helpful Neighbor: "Look how far you've come!"
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.6030.2200
 */

declare(strict_types=1);

namespace WPShadow\Reports;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Health Journey Class
 *
 * Generates visual progress reports and milestone celebrations.
 *
 * @since 1.6030.2200
 */
class Visual_Health_Journey {

	/**
	 * Generate complete health journey
	 *
	 * @since  1.6030.2200
	 * @param  int $days Days of history to include.
	 * @return array Journey data with timeline and milestones.
	 */
	public static function generate_journey( int $days = 90 ): array {
		$timeline = self::build_timeline( $days );
		$milestones = self::identify_milestones( $timeline );
		$achievements = self::get_achievements();
		$progress_stats = self::calculate_progress_stats( $timeline );

		return array(
			'generated_at'    => current_time( 'Y-m-d H:i:s' ),
			'period_days'     => $days,
			'timeline'        => $timeline,
			'milestones'      => $milestones,
			'achievements'    => $achievements,
			'progress_stats'  => $progress_stats,
			'share_data'      => self::generate_share_data( $progress_stats, $achievements ),
			'visualization'   => self::generate_visualization_data( $timeline ),
		);
	}

	/**
	 * Build timeline of health events
	 *
	 * @since  1.6030.2200
	 * @param  int $days Days to include.
	 * @return array Timeline entries.
	 */
	private static function build_timeline( int $days ): array {
		$timeline = array();
		
		// Get health history
		$health_history = get_option( 'wpshadow_health_history', array() );
		$cutoff = strtotime( "-{$days} days" );

		foreach ( $health_history as $entry ) {
			if ( $entry['timestamp'] >= $cutoff ) {
				$timeline[] = array(
					'timestamp'    => $entry['timestamp'],
					'date'         => date( 'Y-m-d', $entry['timestamp'] ),
					'type'         => 'health_check',
					'health_score' => $entry['health_score'],
					'title'        => sprintf(
						/* translators: %d: health score */
						__( 'Health Score: %d', 'wpshadow' ),
						$entry['health_score']
					),
					'description'  => self::get_health_description( $entry['health_score'] ),
					'icon'         => self::get_health_icon( $entry['health_score'] ),
				);
			}
		}

		// Get activities
		$activities = Activity_Logger::get_activities(
			array(
				'date_from' => date( 'Y-m-d', $cutoff ),
				'date_to'   => date( 'Y-m-d' ),
			),
			1000,
			0
		);

		foreach ( $activities['activities'] ?? array() as $activity ) {
			$event_type = self::classify_activity( $activity );
			
			if ( $event_type ) {
				$timeline[] = array(
					'timestamp'   => $activity['timestamp'],
					'date'        => date( 'Y-m-d', $activity['timestamp'] ),
					'type'        => $event_type,
					'title'       => $activity['action'],
					'description' => $activity['details'],
					'icon'        => self::get_event_icon( $event_type ),
					'impact'      => self::get_event_impact( $event_type ),
				);
			}
		}

		// Sort by timestamp (newest first)
		usort( $timeline, function( $a, $b ) {
			return $b['timestamp'] <=> $a['timestamp'];
		} );

		return $timeline;
	}

	/**
	 * Identify significant milestones
	 *
	 * @since  1.6030.2200
	 * @param  array $timeline Timeline data.
	 * @return array Milestones.
	 */
	private static function identify_milestones( array $timeline ): array {
		$milestones = array();

		// Health score improvements
		$health_scores = array_filter( array_map( function( $entry ) {
			return $entry['type'] === 'health_check' ? $entry['health_score'] : null;
		}, $timeline ) );

		if ( count( $health_scores ) >= 2 ) {
			$first = reset( $health_scores );
			$last = end( $health_scores );
			$improvement = $last - $first;

			if ( $improvement >= 10 ) {
				$milestones[] = array(
					'type'        => 'health_improvement',
					'title'       => __( 'Significant Health Improvement', 'wpshadow' ),
					'description' => sprintf(
						/* translators: 1: start score, 2: end score, 3: improvement */
						__( 'Health score improved from %1$d to %2$d (+%3$d points)', 'wpshadow' ),
						$first,
						$last,
						$improvement
					),
					'icon'        => '🎉',
					'achieved_at' => end( $timeline )['timestamp'],
					'impact'      => 'high',
				);
			}
		}

		// Check for specific milestones
		$kpi_data = KPI_Tracker::get_kpi_summary();

		// 100 issues fixed
		if ( ( $kpi_data['total_fixes'] ?? 0 ) >= 100 ) {
			$milestones[] = array(
				'type'        => 'fixes_milestone',
				'title'       => __( '100 Issues Fixed!', 'wpshadow' ),
				'description' => __( 'Reached 100 total issues resolved. Outstanding work!', 'wpshadow' ),
				'icon'        => '🏆',
				'achieved_at' => time(),
				'impact'      => 'high',
			);
		}

		// Milestone: 30 days without top-severity issues.
		$last_critical = self::get_last_critical_issue_date();
		if ( $last_critical && ( time() - $last_critical ) > ( 30 * 86400 ) ) {
			$milestones[] = array(
				'type'        => 'security_milestone',
				'title'       => __( '30 Days Critical-Free', 'wpshadow' ),
				'description' => __( 'No critical issues for 30 consecutive days', 'wpshadow' ),
				'icon'        => '🛡️',
				'achieved_at' => time(),
				'impact'      => 'medium',
			);
		}

		return $milestones;
	}

	/**
	 * Get achievements/badges
	 *
	 * @since  1.6030.2200
	 * @return array Earned achievements.
	 */
	private static function get_achievements(): array {
		$achievements = array();
		$kpi_data = KPI_Tracker::get_kpi_summary();

		// Security Champion
		if ( ( $kpi_data['security_issues_fixed'] ?? 0 ) >= 10 ) {
			$achievements[] = array(
				'id'          => 'security_champion',
				'title'       => __( 'Security Champion', 'wpshadow' ),
				'description' => __( 'Fixed 10+ security issues', 'wpshadow' ),
				'icon'        => '🛡️',
				'badge_color' => '#3b82f6',
				'earned_at'   => time(),
			);
		}

		// Performance Master
		$health = get_option( 'wpshadow_health_status', array() );
		if ( ( $health['health_score'] ?? 0 ) >= 90 ) {
			$achievements[] = array(
				'id'          => 'performance_master',
				'title'       => __( 'Performance Master', 'wpshadow' ),
				'description' => __( 'Achieved 90+ health score', 'wpshadow' ),
				'icon'        => '⚡',
				'badge_color' => '#10b981',
				'earned_at'   => time(),
			);
		}

		// Automation Expert
		if ( ( $kpi_data['workflows_created'] ?? 0 ) >= 5 ) {
			$achievements[] = array(
				'id'          => 'automation_expert',
				'title'       => __( 'Automation Expert', 'wpshadow' ),
				'description' => __( 'Created 5+ automated workflows', 'wpshadow' ),
				'icon'        => '🤖',
				'badge_color' => '#8b5cf6',
				'earned_at'   => time(),
			);
		}

		// Early Adopter
		$install_date = get_option( 'wpshadow_install_date' );
		if ( $install_date && ( time() - $install_date ) > ( 90 * 86400 ) ) {
			$achievements[] = array(
				'id'          => 'early_adopter',
				'title'       => __( 'Early Adopter', 'wpshadow' ),
				'description' => __( 'Using WPShadow for 90+ days', 'wpshadow' ),
				'icon'        => '🌟',
				'badge_color' => '#f59e0b',
				'earned_at'   => time(),
			);
		}

		return $achievements;
	}

	/**
	 * Calculate progress statistics
	 *
	 * @since  1.6030.2200
	 * @param  array $timeline Timeline data.
	 * @return array Progress stats.
	 */
	private static function calculate_progress_stats( array $timeline ): array {
		$health_entries = array_filter( $timeline, function( $entry ) {
			return $entry['type'] === 'health_check';
		} );

		if ( empty( $health_entries ) ) {
			return array(
				'current_score'      => 0,
				'starting_score'     => 0,
				'improvement'        => 0,
				'improvement_percent' => 0,
				'trend'              => 'unknown',
			);
		}

		$scores = array_map( function( $entry ) {
			return $entry['health_score'];
		}, $health_entries );

		$current = reset( $scores );
		$starting = end( $scores );
		$improvement = $current - $starting;
		$improvement_percent = $starting > 0 ? ( $improvement / $starting ) * 100 : 0;

		$kpi_data = KPI_Tracker::get_kpi_summary();

		return array(
			'current_score'       => $current,
			'starting_score'      => $starting,
			'improvement'         => $improvement,
			'improvement_percent' => round( $improvement_percent, 1 ),
			'trend'               => $improvement > 0 ? 'improving' : ( $improvement < 0 ? 'declining' : 'stable' ),
			'issues_fixed'        => $kpi_data['total_fixes'] ?? 0,
			'time_saved_hours'    => $kpi_data['total_time_saved_hours'] ?? 0,
			'workflows_created'   => $kpi_data['workflows_created'] ?? 0,
			'days_tracked'        => count( $health_entries ),
		);
	}

	/**
	 * Generate shareable data
	 *
	 * @since  1.6030.2200
	 * @param  array $progress Progress statistics.
	 * @param  array $achievements Achievements earned.
	 * @return array Share data.
	 */
	private static function generate_share_data( array $progress, array $achievements ): array {
		$improvement = $progress['improvement'] ?? 0;
		$achievement_count = count( $achievements );

		return array(
			'share_text'  => sprintf(
				/* translators: 1: improvement, 2: achievement count */
				__( 'My WordPress site improved by %1$d points and earned %2$d achievements with WPShadow!', 'wpshadow' ),
				abs( $improvement ),
				$achievement_count
			),
			'tweet_text'  => sprintf(
				/* translators: %d: improvement */
				__( 'My #WordPress site improved by %d points with @WPShadow! 🚀', 'wpshadow' ),
				abs( $improvement )
			),
			'share_image' => self::generate_share_image_url( $progress ),
			'share_url'   => 'https://wpshadow.com/share',
		);
	}

	/**
	 * Generate visualization data
	 *
	 * @since  1.6030.2200
	 * @param  array $timeline Timeline entries.
	 * @return array Visualization configuration.
	 */
	private static function generate_visualization_data( array $timeline ): array {
		$health_data = array();
		$events = array();

		foreach ( $timeline as $entry ) {
			if ( $entry['type'] === 'health_check' ) {
				$health_data[] = array(
					'date'  => $entry['date'],
					'score' => $entry['health_score'],
				);
			} else {
				$events[] = array(
					'date'        => $entry['date'],
					'title'       => $entry['title'],
					'type'        => $entry['type'],
					'description' => $entry['description'],
				);
			}
		}

		return array(
			'type'        => 'timeline',
			'health_data' => array_reverse( $health_data ),
			'events'      => array_reverse( $events ),
			'chart_config' => array(
				'type'   => 'line',
				'smooth' => true,
				'colors' => array(
					'line' => '#3b82f6',
					'area' => '#93c5fd',
				),
			),
		);
	}

	/**
	 * Get health description
	 *
	 * @since  1.6030.2200
	 * @param  float $score Health score.
	 * @return string Description.
	 */
	private static function get_health_description( float $score ): string {
		if ( $score >= 90 ) {
			return __( 'Excellent health', 'wpshadow' );
		} elseif ( $score >= 80 ) {
			return __( 'Good health', 'wpshadow' );
		} elseif ( $score >= 70 ) {
			return __( 'Fair health', 'wpshadow' );
		} else {
			return __( 'Needs attention', 'wpshadow' );
		}
	}

	/**
	 * Get health icon
	 *
	 * @since  1.6030.2200
	 * @param  float $score Health score.
	 * @return string Icon.
	 */
	private static function get_health_icon( float $score ): string {
		if ( $score >= 90 ) {
			return '🌟';
		} elseif ( $score >= 80 ) {
			return '✅';
		} elseif ( $score >= 70 ) {
			return '⚠️';
		} else {
			return '🔴';
		}
	}

	/**
	 * Classify activity type
	 *
	 * @since  1.6030.2200
	 * @param  array $activity Activity data.
	 * @return string|null Event type or null.
	 */
	private static function classify_activity( array $activity ): ?string {
		$action = $activity['action'] ?? '';
		$category = $activity['category'] ?? '';

		if ( strpos( $action, 'treatment' ) !== false || strpos( $action, 'fixed' ) !== false ) {
			return 'fix_applied';
		}
		if ( strpos( $action, 'workflow' ) !== false ) {
			return 'workflow';
		}
		if ( $category === 'security' ) {
			return 'security';
		}
		if ( strpos( $action, 'diagnostic' ) !== false ) {
			return 'diagnostic';
		}

		return null;
	}

	/**
	 * Get event icon
	 *
	 * @since  1.6030.2200
	 * @param  string $type Event type.
	 * @return string Icon.
	 */
	private static function get_event_icon( string $type ): string {
		$icons = array(
			'fix_applied' => '🔧',
			'workflow'    => '⚙️',
			'security'    => '🛡️',
			'diagnostic'  => '🔍',
		);

		return $icons[ $type ] ?? '📋';
	}

	/**
	 * Get event impact level
	 *
	 * @since  1.6030.2200
	 * @param  string $type Event type.
	 * @return string Impact level.
	 */
	private static function get_event_impact( string $type ): string {
		$impacts = array(
			'fix_applied' => 'high',
			'workflow'    => 'medium',
			'security'    => 'high',
			'diagnostic'  => 'low',
		);

		return $impacts[ $type ] ?? 'low';
	}

	/**
	 * Get last top-severity issue date.
	 *
	 * @since  1.6030.2200
	 * @return int|null Timestamp or null.
	 */
	private static function get_last_critical_issue_date(): ?int {
		$activities = Activity_Logger::get_activities( array(), 1000, 0 );

		foreach ( $activities['activities'] ?? array() as $activity ) {
			if ( isset( $activity['severity'] ) && $activity['severity'] === 'critical' ) {
				return $activity['timestamp'];
			}
		}

		return null;
	}

	/**
	 * Generate share image URL
	 *
	 * @since  1.6030.2200
	 * @param  array $progress Progress data.
	 * @return string Image URL.
	 */
	private static function generate_share_image_url( array $progress ): string {
		// Future: Generate dynamic social share image
		return plugins_url( 'assets/images/share-default.png', WPSHADOW_BASENAME );
	}

	/**
	 * Render journey HTML
	 *
	 * @since  1.6030.2200
	 * @param  array $journey Journey data.
	 * @return string HTML output.
	 */
	public static function render_html( array $journey ): string {
		$version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0';

		wp_enqueue_style(
			'wpshadow-visual-health-journey',
			WPSHADOW_URL . 'assets/css/visual-health-journey.css',
			array(),
			$version
		);

		wp_enqueue_script(
			'wpshadow-visual-health-journey',
			WPSHADOW_URL . 'assets/js/visual-health-journey.js',
			array(),
			$version,
			true
		);

		ob_start();
		?>
		<div class="wpshadow-health-journey">
			<!-- Progress Header -->
			<div class="journey-header">
				<h2><?php esc_html_e( 'Your Health Journey', 'wpshadow' ); ?></h2>
				<div class="progress-summary">
					<div class="stat">
						<span class="value"><?php echo esc_html( $journey['progress_stats']['improvement'] ); ?></span>
						<span class="label"><?php esc_html_e( 'Point Improvement', 'wpshadow' ); ?></span>
					</div>
					<div class="stat">
						<span class="value"><?php echo esc_html( $journey['progress_stats']['issues_fixed'] ); ?></span>
						<span class="label"><?php esc_html_e( 'Issues Fixed', 'wpshadow' ); ?></span>
					</div>
					<div class="stat">
						<span class="value"><?php echo esc_html( count( $journey['achievements'] ) ); ?></span>
						<span class="label"><?php esc_html_e( 'Achievements', 'wpshadow' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Achievements -->
			<?php if ( ! empty( $journey['achievements'] ) ) : ?>
			<div class="achievements-section">
				<h3><?php esc_html_e( 'Achievements', 'wpshadow' ); ?></h3>
				<div class="achievements-grid">
					<?php foreach ( $journey['achievements'] as $achievement ) : ?>
					<div class="achievement-badge <?php echo esc_attr( self::get_achievement_badge_class( $achievement['id'] ?? '' ) ); ?>">
						<span class="badge-icon"><?php echo esc_html( $achievement['icon'] ); ?></span>
						<h4><?php echo esc_html( $achievement['title'] ); ?></h4>
						<p><?php echo esc_html( $achievement['description'] ); ?></p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<!-- Timeline Visualization -->
			<div class="timeline-visualization">
				<canvas id="wpshadow-journey-chart" width="800" height="400" data-journey-visualization="<?php echo esc_attr( wp_json_encode( $journey['visualization'] ) ); ?>"></canvas>
			</div>

			<!-- Milestones -->
			<?php if ( ! empty( $journey['milestones'] ) ) : ?>
			<div class="milestones-section">
				<h3><?php esc_html_e( 'Milestones', 'wpshadow' ); ?></h3>
				<?php foreach ( $journey['milestones'] as $milestone ) : ?>
				<div class="milestone">
					<span class="milestone-icon"><?php echo esc_html( $milestone['icon'] ); ?></span>
					<div class="milestone-content">
						<h4><?php echo esc_html( $milestone['title'] ); ?></h4>
						<p><?php echo esc_html( $milestone['description'] ); ?></p>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<!-- Share Section -->
			<div class="share-section">
				<h3><?php esc_html_e( 'Share Your Success', 'wpshadow' ); ?></h3>
				<p><?php echo esc_html( $journey['share_data']['share_text'] ); ?></p>
				<div class="share-buttons">
					<button class="share-twitter" data-text="<?php echo esc_attr( $journey['share_data']['tweet_text'] ); ?>">
						<?php esc_html_e( 'Share on Twitter', 'wpshadow' ); ?>
					</button>
					<button class="share-linkedin">
						<?php esc_html_e( 'Share on LinkedIn', 'wpshadow' ); ?>
					</button>
					<button class="copy-link">
						<?php esc_html_e( 'Copy Link', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get achievement badge CSS class.
	 *
	 * @since  1.6030.2200
	 * @param  string $achievement_id Achievement identifier.
	 * @return string Badge class name.
	 */
	private static function get_achievement_badge_class( string $achievement_id ): string {
		$classes = array(
			'security_champion'  => 'achievement-badge--security',
			'performance_master' => 'achievement-badge--performance',
			'automation_expert'  => 'achievement-badge--automation',
			'early_adopter'      => 'achievement-badge--early-adopter',
		);

		return $classes[ $achievement_id ] ?? 'achievement-badge--default';
	}
}
