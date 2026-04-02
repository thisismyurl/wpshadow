<?php
/**
 * Team Collaboration Dashboard Widget
 *
 * Collaborative reporting and team insights. Shows who's fixing what,
 * team performance, and client-ready reports. Multi-stakeholder views.
 *
 * Philosophy:
 * - #8 Inspire Confidence: Team visibility and recognition
 * - #9 Show Value: Track team contributions
 * - #1 Helpful Neighbor: Celebrate team wins
 *
 * @package    WPShadow
 * @subpackage Dashboard\Widgets
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Dashboard\Widgets;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Team Collaboration Widget Class
 *
 * Displays team performance metrics and collaboration features.
 *
 * @since 0.6093.1200
 */
class Team_Collaboration_Widget {

	/**
	 * Render widget
	 *
	 * @since 0.6093.1200
	 * @return string HTML output.
	 */
	public static function render(): string {
		$team_data = self::get_team_data();

		ob_start();
		?>
		<div class="wpshadow-team-collaboration-widget">
			<!-- Team Performance Header -->
			<div class="team-header">
				<h2><?php esc_html_e( 'Team Performance', 'wpshadow' ); ?></h2>
				<div class="team-selector">
					<select id="team-view-selector">
						<option value="all"><?php esc_html_e( 'All Team Members', 'wpshadow' ); ?></option>
						<option value="developers"><?php esc_html_e( 'Developers', 'wpshadow' ); ?></option>
						<option value="managers"><?php esc_html_e( 'Managers', 'wpshadow' ); ?></option>
					</select>
					<select id="team-period-selector">
						<option value="7"><?php esc_html_e( 'Last 7 Days', 'wpshadow' ); ?></option>
						<option value="30" selected><?php esc_html_e( 'Last 30 Days', 'wpshadow' ); ?></option>
						<option value="90"><?php esc_html_e( 'Last 90 Days', 'wpshadow' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Team Stats -->
			<div class="team-stats-grid">
				<div class="team-stat">
					<div class="stat-icon">👥</div>
					<div class="stat-value"><?php echo esc_html( $team_data['total_contributors'] ); ?></div>
					<div class="stat-label"><?php esc_html_e( 'Active Contributors', 'wpshadow' ); ?></div>
				</div>
				<div class="team-stat">
					<div class="stat-icon">🏆</div>
					<div class="stat-value"><?php echo esc_html( $team_data['total_achievements'] ); ?></div>
					<div class="stat-label"><?php esc_html_e( 'Team Achievements', 'wpshadow' ); ?></div>
				</div>
				<div class="team-stat">
					<div class="stat-icon">⚡</div>
					<div class="stat-value"><?php echo esc_html( $team_data['avg_response_time'] ); ?>h</div>
					<div class="stat-label"><?php esc_html_e( 'Avg Response Time', 'wpshadow' ); ?></div>
				</div>
				<div class="team-stat">
					<div class="stat-icon">📊</div>
					<div class="stat-value"><?php echo esc_html( $team_data['collaboration_score'] ); ?>%</div>
					<div class="stat-label"><?php esc_html_e( 'Collaboration Score', 'wpshadow' ); ?></div>
				</div>
			</div>

			<!-- Active Tasks -->
			<div class="active-tasks">
				<h3><?php esc_html_e( 'Current Tasks', 'wpshadow' ); ?></h3>
				<div class="tasks-list">
					<?php foreach ( $team_data['active_tasks'] as $task ) : ?>
					<div class="task-item priority-<?php echo esc_attr( $task['priority'] ); ?>">
						<div class="task-header">
							<span class="task-priority"><?php echo esc_html( ucfirst( $task['priority'] ) ); ?></span>
							<span class="task-type"><?php echo esc_html( $task['type'] ); ?></span>
						</div>
						<div class="task-title"><?php echo esc_html( $task['title'] ); ?></div>
						<div class="task-meta">
							<span class="task-assignee">
								<?php echo get_avatar( $task['assignee_id'], 24 ); ?>
								<?php echo esc_html( $task['assignee_name'] ); ?>
							</span>
							<span class="task-status"><?php echo esc_html( $task['status'] ); ?></span>
						</div>
						<div class="task-notes">
							<?php if ( ! empty( $task['notes'] ) ) : ?>
								<button class="view-notes" data-task-id="<?php echo esc_attr( $task['id'] ); ?>">
									<?php esc_html_e( 'View Notes', 'wpshadow' ); ?>
								</button>
							<?php endif; ?>
							<button class="add-note" data-task-id="<?php echo esc_attr( $task['id'] ); ?>">
								<?php esc_html_e( 'Add Note', 'wpshadow' ); ?>
							</button>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Team Activity Feed -->
			<div class="team-activity-feed">
				<h3><?php esc_html_e( 'Recent Team Activity', 'wpshadow' ); ?></h3>
				<div class="activity-timeline">
					<?php foreach ( $team_data['recent_activity'] as $activity ) : ?>
					<div class="activity-item">
						<div class="activity-time"><?php echo esc_html( human_time_diff( $activity['timestamp'], time() ) ); ?> <?php esc_html_e( 'ago', 'wpshadow' ); ?></div>
						<div class="activity-content">
							<?php echo get_avatar( $activity['user_id'], 32 ); ?>
							<div class="activity-details">
								<strong><?php echo esc_html( $activity['user_name'] ); ?></strong>
								<?php echo esc_html( $activity['action'] ); ?>
								<span class="activity-target"><?php echo esc_html( $activity['target'] ); ?></span>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Client Reports -->
			<div class="client-reports">
				<h3><?php esc_html_e( 'Client Reports', 'wpshadow' ); ?></h3>
				<p class="section-description">
					<?php esc_html_e( 'Generate white-label reports showing value delivered to clients', 'wpshadow' ); ?>
				</p>
				<div class="report-templates">
					<div class="report-template">
						<div class="template-icon">📄</div>
						<h4><?php esc_html_e( 'Monthly Summary', 'wpshadow' ); ?></h4>
						<p><?php esc_html_e( 'High-level overview of work completed', 'wpshadow' ); ?></p>
						<button class="button generate-report" data-template="monthly">
							<?php esc_html_e( 'Generate', 'wpshadow' ); ?>
						</button>
					</div>
					<div class="report-template">
						<div class="template-icon">📊</div>
						<h4><?php esc_html_e( 'Technical Detail', 'wpshadow' ); ?></h4>
						<p><?php esc_html_e( 'Detailed technical work breakdown', 'wpshadow' ); ?></p>
						<button class="button generate-report" data-template="technical">
							<?php esc_html_e( 'Generate', 'wpshadow' ); ?>
						</button>
					</div>
					<div class="report-template">
						<div class="template-icon">💼</div>
						<h4><?php esc_html_e( 'Executive Brief', 'wpshadow' ); ?></h4>
						<p><?php esc_html_e( 'Business impact and ROI summary', 'wpshadow' ); ?></p>
						<button class="button generate-report" data-template="executive">
							<?php esc_html_e( 'Generate', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Team Goals -->
			<div class="team-goals">
				<h3><?php esc_html_e( 'Team Goals', 'wpshadow' ); ?></h3>
				<?php foreach ( $team_data['goals'] as $goal ) : ?>
				<div class="goal-item">
					<div class="goal-header">
						<h4><?php echo esc_html( $goal['title'] ); ?></h4>
						<span class="goal-deadline"><?php echo esc_html( date( 'M j', $goal['deadline'] ) ); ?></span>
					</div>
					<div class="goal-progress">
						<div class="progress-bar">
							<div class="progress-fill" style="width: <?php echo esc_attr( $goal['progress'] ); ?>%"></div>
						</div>
						<span class="progress-text"><?php echo esc_html( $goal['progress'] ); ?>%</span>
					</div>
					<div class="goal-meta">
						<span><?php echo esc_html( $goal['completed'] ); ?> / <?php echo esc_html( $goal['total'] ); ?> <?php esc_html_e( 'tasks', 'wpshadow' ); ?></span>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<!-- Communication Hub -->
			<div class="communication-hub">
				<h3><?php esc_html_e( 'Team Communication', 'wpshadow' ); ?></h3>
				<div class="comm-actions">
					<button class="button send-announcement">
						<span class="dashicons dashicons-megaphone"></span>
						<?php esc_html_e( 'Send Team Announcement', 'wpshadow' ); ?>
					</button>
					<button class="button schedule-meeting">
						<span class="dashicons dashicons-calendar"></span>
						<?php esc_html_e( 'Schedule Review Meeting', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Add Note Modal -->
		<div id="add-note-modal" class="wpshadow-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="add-note-modal-title" aria-hidden="true" data-wpshadow-modal="static" data-overlay-close="true" data-esc-close="true">
			<div class="wpshadow-modal wpshadow-modal--medium" role="document">
				<button type="button" class="wpshadow-modal-close" aria-label="<?php echo esc_attr__( 'Close dialog', 'wpshadow' ); ?>" data-wpshadow-modal-close="add-note-modal">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="wpshadow-modal-header">
					<h3 id="add-note-modal-title" class="wpshadow-modal-title"><?php esc_html_e( 'Add Team Note', 'wpshadow' ); ?></h3>
				</div>
				<div class="wpshadow-modal-body">
					<textarea id="team-note-content" rows="5" placeholder="<?php esc_attr_e( 'Add context or updates for this task...', 'wpshadow' ); ?>"></textarea>
					<label>
						<input type="checkbox" id="notify-team" />
						<?php esc_html_e( 'Notify team members', 'wpshadow' ); ?>
					</label>
				</div>
				<div class="wpshadow-modal-footer">
					<button class="button button-primary save-note"><?php esc_html_e( 'Save Note', 'wpshadow' ); ?></button>
					<button class="button cancel-note"><?php esc_html_e( 'Cancel', 'wpshadow' ); ?></button>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get team collaboration data
	 *
	 * @since 0.6093.1200
	 * @return array Team data.
	 */
	private static function get_team_data(): array {
		$activities = Activity_Logger::get_activities( array(), 1000, 0 );
		$kpi_data = KPI_Tracker::get_kpi_summary();

		// Get unique contributors
		$contributors = array();
		foreach ( $activities['activities'] ?? array() as $activity ) {
			if ( ! empty( $activity['user_id'] ) ) {
				$contributors[ $activity['user_id'] ] = $activity['user_name'];
			}
		}

		// Get active tasks
		$active_tasks = self::get_active_tasks();

		// Get recent activity
		$recent_activity = array_slice( $activities['activities'] ?? array(), 0, 10 );

		// Calculate collaboration score
		$collaboration_score = self::calculate_collaboration_score( $activities['activities'] ?? array() );

		return array(
			'total_contributors'    => count( $contributors ),
			'total_achievements'    => $kpi_data['total_fixes'] ?? 0,
			'avg_response_time'     => self::calculate_avg_response_time(),
			'collaboration_score'   => $collaboration_score,
			'active_tasks'          => $active_tasks,
			'recent_activity'       => $recent_activity,
			'goals'                 => self::get_team_goals(),
		);
	}

	/**
	 * Get active tasks
	 *
	 * @since 0.6093.1200
	 * @return array Active tasks.
	 */
	private static function get_active_tasks(): array {
		// Placeholder - would integrate with actual task tracking
		return array(
			array(
				'id'            => 1,
				'title'         => __( 'Update outdated plugins', 'wpshadow' ),
				'type'          => 'maintenance',
				'priority'      => 'high',
				'status'        => 'in_progress',
				'assignee_id'   => 1,
				'assignee_name' => 'Admin',
				'notes'         => array(),
			),
			array(
				'id'            => 2,
				'title'         => __( 'Optimize database tables', 'wpshadow' ),
				'type'          => 'performance',
				'priority'      => 'medium',
				'status'        => 'pending',
				'assignee_id'   => 1,
				'assignee_name' => 'Admin',
				'notes'         => array(),
			),
		);
	}

	/**
	 * Calculate average response time
	 *
	 * @since 0.6093.1200
	 * @return float Response time in hours.
	 */
	private static function calculate_avg_response_time(): float {
		// Placeholder - would track actual response times
		return 2.5;
	}

	/**
	 * Calculate collaboration score
	 *
	 * @since 0.6093.1200
	 * @param  array $activities Activity data.
	 * @return int Collaboration score (0-100).
	 */
	private static function calculate_collaboration_score( array $activities ): int {
		// Simple scoring based on activity frequency and diversity
		$unique_contributors = array();
		$activity_types = array();

		foreach ( $activities as $activity ) {
			if ( ! empty( $activity['user_id'] ) ) {
				$unique_contributors[ $activity['user_id'] ] = true;
			}
			if ( ! empty( $activity['category'] ) ) {
				$activity_types[ $activity['category'] ] = true;
			}
		}

		$contributor_score = min( 100, count( $unique_contributors ) * 20 );
		$diversity_score = min( 100, count( $activity_types ) * 10 );
		$volume_score = min( 100, count( $activities ) / 10 );

		return (int) ( ( $contributor_score + $diversity_score + $volume_score ) / 3 );
	}

	/**
	 * Get team goals
	 *
	 * @since 0.6093.1200
	 * @return array Team goals.
	 */
	private static function get_team_goals(): array {
		return array(
			array(
				'title'     => __( 'Achieve 90% Health Score', 'wpshadow' ),
				'progress'  => 85,
				'completed' => 17,
				'total'     => 20,
				'deadline'  => strtotime( '+7 days' ),
			),
			array(
				'title'     => __( 'Fix All Critical Issues', 'wpshadow' ),
				'progress'  => 100,
				'completed' => 5,
				'total'     => 5,
				'deadline'  => time(),
			),
			array(
				'title'     => __( 'Reduce Page Load to < 2s', 'wpshadow' ),
				'progress'  => 60,
				'completed' => 3,
				'total'     => 5,
				'deadline'  => strtotime( '+14 days' ),
			),
		);
	}
}
