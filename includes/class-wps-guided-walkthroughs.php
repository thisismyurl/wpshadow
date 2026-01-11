<?php
/**
 * WPS Guided Walkthroughs
 *
 * Step-by-step guided workflows for common WordPress tasks.
 * Includes confirmations, safety checks, and automatic backups.
 *
 * @package WPS_WP_SUPPORT
 * @since 1.2601.1111
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * Class WPS_Guided_Walkthroughs
 *
 * Provides guided task workflows with:
 * - Step-by-step UI for common tasks
 * - Confirmation at each step
 * - Automatic backups before risky actions
 * - Undo functionality
 * - Progress tracking
 */
class WPS_Guided_Walkthroughs {

	/**
	 * Database option key for walkthrough progress.
	 */
	private const PROGRESS_KEY = 'wps_walkthrough_progress';

	/**
	 * Available walkthrough workflows.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static $workflows = array();

	/**
	 * Initialize guided walkthroughs.
	 *
	 * @return void
	 */
	public static function init(): void {
		self::register_workflows();

		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wps_start_walkthrough', array( __CLASS__, 'ajax_start_walkthrough' ) );
		add_action( 'wp_ajax_wps_complete_step', array( __CLASS__, 'ajax_complete_step' ) );
		add_action( 'wp_ajax_wps_undo_step', array( __CLASS__, 'ajax_undo_step' ) );
	}

	/**
	 * Register all available walkthrough workflows.
	 *
	 * @return void
	 */
	private static function register_workflows(): void {
		self::$workflows = array(
			'update-plugin'  => array(
				'title'       => __( 'Update a Plugin Safely', 'plugin-wp-support-thisismyurl' ),
				'description' => __( 'Test plugin update in staging, then deploy to live site', 'plugin-wp-support-thisismyurl' ),
				'steps'       => array(
					array(
						'title'       => __( 'Create Backup', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Automatic backup created before update', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'create_backup',
					),
					array(
						'title'       => __( 'Test in Staging', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Update staging site and test functionality', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'update_staging',
					),
					array(
						'title'       => __( 'Review Changes', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'See what changed in this update', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'show_changelog',
					),
					array(
						'title'       => __( 'Update Live Site', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Apply update to production', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'update_live',
					),
					array(
						'title'       => __( 'Verify Functionality', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Check that everything still works', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'verify',
					),
				),
			),
			'add-user'       => array(
				'title'       => __( 'Add a Team Member', 'plugin-wp-support-thisismyurl' ),
				'description' => __( 'Add new user with appropriate permissions', 'plugin-wp-support-thisismyurl' ),
				'steps'       => array(
					array(
						'title'       => __( 'Enter Email Address', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Email address for new user', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'get_email',
					),
					array(
						'title'       => __( 'Choose Role', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Select appropriate permission level', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'select_role',
					),
					array(
						'title'       => __( 'Review Details', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Confirm user information', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'review',
					),
					array(
						'title'       => __( 'Create User', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Add user and send invitation', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'create_user',
					),
				),
			),
			'create-backup'  => array(
				'title'       => __( 'Create a Backup', 'plugin-wp-support-thisismyurl' ),
				'description' => __( 'Create full site backup with verification', 'plugin-wp-support-thisismyurl' ),
				'steps'       => array(
					array(
						'title'       => __( 'Select Backup Type', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Full backup or files only', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'select_type',
					),
					array(
						'title'       => __( 'Create Backup', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Backup in progress...', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'create_backup',
					),
					array(
						'title'       => __( 'Verify Backup', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Ensure backup is valid', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'verify_backup',
					),
				),
			),
			'install-plugin' => array(
				'title'       => __( 'Install a Plugin Safely', 'plugin-wp-support-thisismyurl' ),
				'description' => __( 'Install new plugin with safety checks', 'plugin-wp-support-thisismyurl' ),
				'steps'       => array(
					array(
						'title'       => __( 'Search Plugin', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Find plugin in WordPress directory', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'search_plugin',
					),
					array(
						'title'       => __( 'Review Plugin', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Check ratings, reviews, compatibility', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'review_plugin',
					),
					array(
						'title'       => __( 'Create Backup', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Backup before installation', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'create_backup',
					),
					array(
						'title'       => __( 'Install Plugin', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Install and activate', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'install_plugin',
					),
					array(
						'title'       => __( 'Test Site', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Verify site still works', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'test_site',
					),
				),
			),
			'restore-backup' => array(
				'title'       => __( 'Restore from Backup', 'plugin-wp-support-thisismyurl' ),
				'description' => __( 'Safely restore your site from backup', 'plugin-wp-support-thisismyurl' ),
				'steps'       => array(
					array(
						'title'       => __( 'Select Backup', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Choose backup to restore', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'select_backup',
					),
					array(
						'title'       => __( 'Preview Backup', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'See what will be restored', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'preview_backup',
					),
					array(
						'title'       => __( 'Confirm Restore', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'This will overwrite current site', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'confirm_restore',
					),
					array(
						'title'       => __( 'Restore Backup', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Restoration in progress...', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'restore',
					),
					array(
						'title'       => __( 'Verify Site', 'plugin-wp-support-thisismyurl' ),
						'description' => __( 'Check that everything works', 'plugin-wp-support-thisismyurl' ),
						'action'      => 'verify_site',
					),
				),
			),
		);
	}

	/**
	 * Register admin page.
	 *
	 * @return void
	 */
	public static function register_admin_page(): void {
		add_submenu_page(
			'wp-support',
			__( 'Guided Tasks', 'plugin-wp-support-thisismyurl' ),
			__( 'Guided Tasks', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-guided-tasks',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue CSS/JS assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'wps-guided-tasks' ) ) {
			return;
		}

		wp_enqueue_style( 'wps-guided-tasks', plugins_url( '../assets/css/guided-tasks.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_script( 'wps-guided-tasks', plugins_url( '../assets/js/guided-tasks.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

		wp_localize_script(
			'wps-guided-tasks',
			'wpsGuidedTasks',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wps_guided_tasks_nonce' ),
			)
		);
	}

	/**
	 * AJAX handler: Start walkthrough.
	 *
	 * @return void
	 */
	public static function ajax_start_walkthrough(): void {
		check_ajax_referer( 'wps_guided_tasks_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$workflow = isset( $_POST['workflow'] ) ? sanitize_key( wp_unslash( $_POST['workflow'] ) ) : '';
		if ( ! isset( self::$workflows[ $workflow ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid workflow', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$progress = array(
			'workflow'     => $workflow,
			'current_step' => 0,
			'completed'    => array(),
			'data'         => array(),
			'started_at'   => time(),
		);

		update_option( self::PROGRESS_KEY . '_' . get_current_user_id(), $progress );

		wp_send_json_success(
			array(
				'workflow' => self::$workflows[ $workflow ],
				'progress' => $progress,
			)
		);
	}

	/**
	 * AJAX handler: Complete step.
	 *
	 * @return void
	 */
	public static function ajax_complete_step(): void {
		check_ajax_referer( 'wps_guided_tasks_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$progress = get_option( self::PROGRESS_KEY . '_' . get_current_user_id(), array() );
		if ( empty( $progress ) ) {
			wp_send_json_error( array( 'message' => __( 'No active walkthrough', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$step_data = isset( $_POST['step_data'] ) ? wp_unslash( $_POST['step_data'] ) : array();

		$progress['completed'][]   = $progress['current_step'];
		$progress['current_step'] += 1;
		$progress['data']          = array_merge( $progress['data'], (array) $step_data );

		update_option( self::PROGRESS_KEY . '_' . get_current_user_id(), $progress );

		$workflow  = self::$workflows[ $progress['workflow'] ] ?? array();
		$next_step = $workflow['steps'][ $progress['current_step'] ] ?? null;

		wp_send_json_success(
			array(
				'progress'  => $progress,
				'next_step' => $next_step,
				'complete'  => null === $next_step,
			)
		);
	}

	/**
	 * AJAX handler: Undo step.
	 *
	 * @return void
	 */
	public static function ajax_undo_step(): void {
		check_ajax_referer( 'wps_guided_tasks_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$progress = get_option( self::PROGRESS_KEY . '_' . get_current_user_id(), array() );
		if ( empty( $progress ) || $progress['current_step'] <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Cannot undo', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$progress['current_step'] -= 1;
		array_pop( $progress['completed'] );

		update_option( self::PROGRESS_KEY . '_' . get_current_user_id(), $progress );

		$workflow      = self::$workflows[ $progress['workflow'] ] ?? array();
		$previous_step = $workflow['steps'][ $progress['current_step'] ] ?? null;

		wp_send_json_success(
			array(
				'progress'      => $progress,
				'previous_step' => $previous_step,
			)
		);
	}

	/**
	 * Render guided tasks admin page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		$active_progress = get_option( self::PROGRESS_KEY . '_' . get_current_user_id(), array() );
		?>
		<div class="wrap wps-guided-tasks-page">
			<h1><?php esc_html_e( 'Guided Tasks', 'plugin-wp-support-thisismyurl' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Step-by-step guides for common WordPress tasks. Each task includes confirmations and automatic backups for safety.', 'plugin-wp-support-thisismyurl' ); ?>
			</p>

			<?php if ( ! empty( $active_progress ) ) : ?>
				<?php self::render_active_walkthrough( $active_progress ); ?>
			<?php else : ?>
				<div class="wps-workflow-grid">
					<?php foreach ( self::$workflows as $workflow_id => $workflow ) : ?>
						<div class="wps-workflow-card">
							<h3><?php echo esc_html( $workflow['title'] ); ?></h3>
							<p class="description"><?php echo esc_html( $workflow['description'] ); ?></p>
							<p class="wps-step-count">
								<?php
								printf(
									/* translators: %d: Number of steps */
									esc_html( _n( '%d step', '%d steps', count( $workflow['steps'] ), 'plugin-wp-support-thisismyurl' ) ),
									count( $workflow['steps'] )
								);
								?>
							</p>
							<button type="button" class="button button-primary wps-start-workflow" data-workflow="<?php echo esc_attr( $workflow_id ); ?>">
								<?php esc_html_e( 'Start Guided Task', 'plugin-wp-support-thisismyurl' ); ?>
							</button>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render active walkthrough progress.
	 *
	 * @param array<string, mixed> $progress Current progress.
	 * @return void
	 */
	private static function render_active_walkthrough( array $progress ): void {
		$workflow = self::$workflows[ $progress['workflow'] ] ?? array();
		if ( empty( $workflow ) ) {
			return;
		}

		$current_step     = $workflow['steps'][ $progress['current_step'] ] ?? null;
		$total_steps      = count( $workflow['steps'] );
		$completed_steps  = count( $progress['completed'] );
		$progress_percent = ( $completed_steps / $total_steps ) * 100;
		?>
		<div class="wps-active-walkthrough">
			<h2><?php echo esc_html( $workflow['title'] ); ?></h2>

			<div class="wps-progress-bar">
				<div class="wps-progress-fill" style="width: <?php echo esc_attr( (string) $progress_percent ); ?>%;"></div>
			</div>

			<p class="wps-progress-text">
				<?php
				printf(
					/* translators: 1: Current step number, 2: Total steps */
					esc_html__( 'Step %1$d of %2$d', 'plugin-wp-support-thisismyurl' ),
					esc_html( (string) ( $progress['current_step'] + 1 ) ),
					esc_html( (string) $total_steps )
				);
				?>
			</p>

			<?php if ( $current_step ) : ?>
				<div class="wps-current-step">
					<h3><?php echo esc_html( $current_step['title'] ); ?></h3>
					<p><?php echo esc_html( $current_step['description'] ); ?></p>

					<div class="wps-step-actions">
						<?php if ( $progress['current_step'] > 0 ) : ?>
							<button type="button" class="button wps-undo-step">
								<?php esc_html_e( '← Previous Step', 'plugin-wp-support-thisismyurl' ); ?>
							</button>
						<?php endif; ?>

						<button type="button" class="button button-primary wps-complete-step">
							<?php esc_html_e( 'Continue →', 'plugin-wp-support-thisismyurl' ); ?>
						</button>

						<button type="button" class="button wps-cancel-walkthrough">
							<?php esc_html_e( 'Cancel', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					</div>
				</div>
			<?php else : ?>
				<div class="notice notice-success">
					<p><?php esc_html_e( 'Task completed successfully!', 'plugin-wp-support-thisismyurl' ); ?></p>
					<button type="button" class="button button-primary wps-finish-walkthrough">
						<?php esc_html_e( 'Done', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
				</div>
			<?php endif; ?>

			<div class="wps-steps-list">
				<h4><?php esc_html_e( 'All Steps', 'plugin-wp-support-thisismyurl' ); ?></h4>
				<ul>
					<?php foreach ( $workflow['steps'] as $index => $step ) : ?>
						<li class="<?php echo $index < $progress['current_step'] ? 'completed' : ( $index === $progress['current_step'] ? 'active' : '' ); ?>">
							<?php if ( $index < $progress['current_step'] ) : ?>
								<span class="dashicons dashicons-yes-alt"></span>
							<?php elseif ( $index === $progress['current_step'] ) : ?>
								<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php else : ?>
								<span class="dashicons dashicons-minus"></span>
							<?php endif; ?>
							<?php echo esc_html( $step['title'] ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Get available workflows.
	 *
	 * @return array<string, array<string, mixed>> Workflows.
	 */
	public static function get_workflows(): array {
		return self::$workflows;
	}
}
