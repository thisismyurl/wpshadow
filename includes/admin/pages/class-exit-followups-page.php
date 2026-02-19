<?php
/**
 * Exit Followups Admin Page
 *
 * Admin interface for viewing and managing exit interview followups.
 *
 * @since   1.6030.2148
 * @package WPShadow\Screens
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

use WPShadow\Engagement\Exit_Followup_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exit Followups Page Class
 */
class Exit_Followups_Page {

	/**
	 * Initialize the page
	 *
	 * @since 1.6030.2148
	 * @return void
	 */
	public static function init() {
		if ( ! \apply_filters( 'wpshadow_exit_followups_enabled', false ) ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ), 99 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Add menu page
	 *
	 * @since 1.6030.2148
	 * @return void
	 */
	public static function add_menu_page() {
		add_submenu_page(
			'wpshadow',
			__( 'Exit Followups', 'wpshadow' ),
			__( 'Exit Followups', 'wpshadow' ),
			'manage_options',
			'wpshadow-exit-followups',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue scripts
	 *
	 * @since  1.6030.2148
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_scripts( $hook ) {
		if ( 'wpshadow_page_wpshadow-exit-followups' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-exit-followups',
			WPSHADOW_URL . 'assets/css/exit-followups.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-exit-followups',
			WPSHADOW_URL . 'assets/js/exit-followups.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-exit-followups',
			'wpShadowExitFollowups',
			'wpshadow_get_exit_followups',
			array(
				'updateNonce' => wp_create_nonce( 'wpshadow_update_exit_followup' ),
				'cancelNonce' => wp_create_nonce( 'wpshadow_cancel_exit_followups' ),
				'strings'     => array(
					'loading'       => __( 'Loading followups...', 'wpshadow' ),
					'noFollowups'   => __( 'No followups scheduled', 'wpshadow' ),
					'error'         => __( 'Error loading followups', 'wpshadow' ),
					'updateSuccess' => __( 'Followup updated successfully', 'wpshadow' ),
					'updateError'   => __( 'Failed to update followup', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render the admin page
	 *
	 * @since 1.6030.2148
	 * @return void
	 */
	public static function render_page() {
		// Get statistics
		$stats = Exit_Followup_Manager::get_statistics();

		?>
		<div class="wrap wpshadow-exit-followups-page">
			<h1><?php esc_html_e( 'Exit Interview Followups', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>

			<p class="description">
				<?php
				esc_html_e(
					'Manage scheduled followup contacts with users who allowed us to reach out after deactivating the plugin.',
					'wpshadow'
				);
				?>
			</p>

			<!-- Statistics Cards -->
			<div class="wpshadow-stats-cards">
				<div class="wpshadow-stat-card">
					<div class="stat-value"><?php echo esc_html( $stats['total_interviews_with_contact'] ); ?></div>
					<div class="stat-label"><?php esc_html_e( 'Total Interviews with Contact Permission', 'wpshadow' ); ?></div>
				</div>

				<div class="wpshadow-stat-card">
					<div class="stat-value">
						<?php echo esc_html( $stats['followups_by_status']['pending'] ?? 0 ); ?>
					</div>
					<div class="stat-label"><?php esc_html_e( 'Pending Followups', 'wpshadow' ); ?></div>
				</div>

				<div class="wpshadow-stat-card">
					<div class="stat-value">
						<?php echo esc_html( $stats['pending_due_count'] ?? 0 ); ?>
					</div>
					<div class="stat-label"><?php esc_html_e( 'Due Now', 'wpshadow' ); ?></div>
				</div>

				<div class="wpshadow-stat-card">
					<div class="stat-value">
						<?php echo esc_html( $stats['followups_by_status']['completed'] ?? 0 ); ?>
					</div>
					<div class="stat-label"><?php esc_html_e( 'Completed', 'wpshadow' ); ?></div>
				</div>
			</div>

			<!-- Filters -->
			<div class="wpshadow-followups-filters">
				<label for="followup-status-filter"><?php esc_html_e( 'Status:', 'wpshadow' ); ?></label>
				<select id="followup-status-filter">
					<option value=""><?php esc_html_e( 'All', 'wpshadow' ); ?></option>
					<option value="pending"><?php esc_html_e( 'Pending', 'wpshadow' ); ?></option>
					<option value="due"><?php esc_html_e( 'Due Now', 'wpshadow' ); ?></option>
					<option value="sent"><?php esc_html_e( 'Sent', 'wpshadow' ); ?></option>
					<option value="completed"><?php esc_html_e( 'Completed', 'wpshadow' ); ?></option>
					<option value="cancelled"><?php esc_html_e( 'Cancelled', 'wpshadow' ); ?></option>
				</select>

				<button type="button" class="button" id="refresh-followups">
					<?php esc_html_e( 'Refresh', 'wpshadow' ); ?>
				</button>
			</div>

			<!-- Followups Table -->
			<div id="wpshadow-followups-container">
				<p><?php esc_html_e( 'Loading...', 'wpshadow' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render followups table (called via AJAX or directly)
	 *
	 * @since  1.6030.2148
	 * @param  array $followups Array of followup records.
	 * @return void
	 */
	public static function render_followups_table( $followups ) {
		if ( empty( $followups ) ) {
			?>
			<p class="wpshadow-no-followups">
				<?php esc_html_e( 'No followups found.', 'wpshadow' ); ?>
			</p>
			<?php
			return;
		}

		?>
		<table class="wp-list-table widefat fixed striped wpshadow-followups-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Type', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Contact Email', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Exit Reason', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Scheduled Date', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $followups as $followup ) : ?>
					<tr data-followup-id="<?php echo esc_attr( $followup['id'] ); ?>">
						<td>
							<?php echo esc_html( self::get_followup_type_label( $followup['followup_type'] ) ); ?>
						</td>
						<td>
							<?php echo esc_html( $followup['contact_email'] ); ?>
						</td>
						<td>
							<?php echo esc_html( $followup['exit_reason'] ?? __( 'Not specified', 'wpshadow' ) ); ?>
						</td>
						<td>
							<?php
							echo esc_html(
								date_i18n(
									get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
									strtotime( $followup['scheduled_date'] )
								)
							);
							?>
							<?php if ( strtotime( $followup['scheduled_date'] ) <= time() && 'pending' === $followup['status'] ) : ?>
								<span class="wpshadow-badge wpshadow-badge-danger">
									<?php esc_html_e( 'Due', 'wpshadow' ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td>
							<span class="wpshadow-status-badge wpshadow-status-<?php echo esc_attr( $followup['status'] ); ?>">
								<?php echo esc_html( ucfirst( $followup['status'] ) ); ?>
							</span>
						</td>
						<td>
							<?php if ( 'pending' === $followup['status'] ) : ?>
								<button
									type="button"
									class="button button-small wpshadow-mark-sent"
									data-followup-id="<?php echo esc_attr( $followup['id'] ); ?>"
								>
									<?php esc_html_e( 'Mark as Sent', 'wpshadow' ); ?>
								</button>
							<?php endif; ?>

							<?php if ( 'sent' === $followup['status'] ) : ?>
								<button
									type="button"
									class="button button-small wpshadow-mark-completed"
									data-followup-id="<?php echo esc_attr( $followup['id'] ); ?>"
								>
									<?php esc_html_e( 'Mark as Completed', 'wpshadow' ); ?>
								</button>
							<?php endif; ?>

							<button
								type="button"
								class="button button-small wpshadow-view-details"
								data-followup-id="<?php echo esc_attr( $followup['id'] ); ?>"
							>
								<?php esc_html_e( 'View Details', 'wpshadow' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Get human-readable followup type label
	 *
	 * @since  1.6030.2148
	 * @param  string $type Followup type.
	 * @return string Human-readable label.
	 */
	private static function get_followup_type_label( $type ) {
		$labels = array(
			'immediate'  => __( 'Immediate (3 days)', 'wpshadow' ),
			'short_term' => __( 'Short-term (14 days)', 'wpshadow' ),
			'long_term'  => __( 'Long-term (30 days)', 'wpshadow' ),
		);

		return $labels[ $type ] ?? $type;
	}
}

// Initialize the page
Exit_Followups_Page::init();
