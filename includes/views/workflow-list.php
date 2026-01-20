<?php
/**
 * Workflow List View (IFTTT-style dashboard)
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$workflows = \WPShadow\Workflow\Workflow_Manager::get_workflows();
?>

<div class="wrap wpshadow-workflow-list">
	<h1>
		<?php esc_html_e( 'Automation Workflows', 'wpshadow' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automation&action=create' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Create Workflow', 'wpshadow' ); ?>
		</a>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Create automated workflows that trigger actions based on events, schedules, or conditions.', 'wpshadow' ); ?>
	</p>

	<?php if ( empty( $workflows ) ) : ?>
		<!-- Empty State -->
		<div class="wpshadow-empty-state">
			<div class="empty-state-icon">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<h2><?php esc_html_e( 'No Workflows Yet', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Create your first workflow to automate site maintenance, security checks, and notifications.', 'wpshadow' ); ?></p>
			
			<div class="empty-state-examples">
				<h3><?php esc_html_e( 'Popular Examples:', 'wpshadow' ); ?></h3>
				<ul>
					<li><strong><?php esc_html_e( 'Daily Health Check:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Every day at 2am, run diagnostics and email results', 'wpshadow' ); ?></li>
					<li><strong><?php esc_html_e( 'Block External Fonts:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'On every page load, check and block external fonts', 'wpshadow' ); ?></li>
					<li><strong><?php esc_html_e( 'Security Alert:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'When a plugin is activated, run security scan', 'wpshadow' ); ?></li>
					<li><strong><?php esc_html_e( 'Login Notification:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'When admin logs in, send Slack notification', 'wpshadow' ); ?></li>
				</ul>
			</div>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automation&action=create' ) ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Create Your First Workflow', 'wpshadow' ); ?>
			</a>
		</div>
	<?php else : ?>
		<!-- Workflow List -->
		<div class="wpshadow-workflows">
			<?php foreach ( $workflows as $workflow ) : ?>
				<?php
				$trigger_label = self::get_trigger_summary( $workflow );
				$action_count = ! empty( $workflow['actions'] ) ? count( $workflow['actions'] ) : 0;
				$is_enabled = ! isset( $workflow['enabled'] ) || $workflow['enabled'];
				?>
				<div class="workflow-card <?php echo $is_enabled ? 'enabled' : 'disabled'; ?>" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
					<div class="workflow-header">
						<div class="workflow-status">
							<label class="workflow-toggle">
								<input type="checkbox" class="workflow-enable-toggle" <?php checked( $is_enabled ); ?>>
								<span class="toggle-slider"></span>
							</label>
						</div>
						<div class="workflow-info">
							<h3 class="workflow-name"><?php echo esc_html( $workflow['name'] ); ?></h3>
							<p class="workflow-summary">
								<span class="workflow-trigger">
									<span class="dashicons dashicons-clock"></span>
									<?php echo esc_html( $trigger_label ); ?>
								</span>
								<span class="workflow-actions">
									<span class="dashicons dashicons-admin-tools"></span>
									<?php
									/* translators: %d: number of actions */
									echo esc_html( sprintf( _n( '%d action', '%d actions', $action_count, 'wpshadow' ), $action_count ) );
									?>
								</span>
							</p>
						</div>
					</div>

					<div class="workflow-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automation&action=edit&workflow=' . $workflow['id'] ) ); ?>" class="button button-small">
							<?php esc_html_e( 'Edit', 'wpshadow' ); ?>
						</a>
						<button class="button button-small workflow-run-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
						</button>
						<button class="button button-small button-link-delete workflow-delete-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<style>
.wpshadow-workflow-list {
	max-width: 1200px;
}

/* Empty State */
.wpshadow-empty-state {
	text-align: center;
	padding: 60px 20px;
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	margin-top: 20px;
}

.empty-state-icon {
	font-size: 64px;
	color: #ccc;
	margin-bottom: 20px;
}

.empty-state-icon .dashicons {
	width: 64px;
	height: 64px;
	font-size: 64px;
}

.wpshadow-empty-state h2 {
	font-size: 24px;
	margin-bottom: 10px;
}

.wpshadow-empty-state > p {
	font-size: 16px;
	color: #666;
	margin-bottom: 30px;
}

.empty-state-examples {
	max-width: 600px;
	margin: 30px auto;
	text-align: left;
	background: #f7f7f7;
	padding: 20px;
	border-radius: 4px;
}

.empty-state-examples h3 {
	margin-top: 0;
	font-size: 14px;
	text-transform: uppercase;
	color: #666;
}

.empty-state-examples ul {
	list-style: none;
	padding: 0;
	margin: 15px 0 0 0;
}

.empty-state-examples li {
	padding: 10px 0;
	border-bottom: 1px solid #ddd;
}

.empty-state-examples li:last-child {
	border-bottom: none;
}

/* Workflow Cards */
.wpshadow-workflows {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.workflow-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 20px;
	transition: all 0.2s ease;
}

.workflow-card:hover {
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.workflow-card.disabled {
	opacity: 0.6;
}

.workflow-header {
	display: flex;
	gap: 15px;
	margin-bottom: 15px;
}

.workflow-status {
	flex-shrink: 0;
}

.workflow-toggle {
	position: relative;
	display: inline-block;
	width: 44px;
	height: 24px;
	cursor: pointer;
}

.workflow-toggle input {
	opacity: 0;
	width: 0;
	height: 0;
}

.toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .3s;
	border-radius: 24px;
}

.toggle-slider:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: .3s;
	border-radius: 50%;
}

.workflow-toggle input:checked + .toggle-slider {
	background-color: #2271b1;
}

.workflow-toggle input:checked + .toggle-slider:before {
	transform: translateX(20px);
}

.workflow-info {
	flex: 1;
}

.workflow-name {
	margin: 0 0 8px 0;
	font-size: 18px;
	font-weight: 600;
}

.workflow-summary {
	margin: 0;
	font-size: 13px;
	color: #666;
	display: flex;
	gap: 20px;
	flex-wrap: wrap;
}

.workflow-summary span {
	display: inline-flex;
	align-items: center;
	gap: 5px;
}

.workflow-summary .dashicons {
	width: 16px;
	height: 16px;
	font-size: 16px;
}

.workflow-actions {
	display: flex;
	gap: 8px;
	padding-top: 15px;
	border-top: 1px solid #f0f0f0;
}

.workflow-actions .button {
	flex: 1;
}
</style>

<?php
/**
 * Get human-readable trigger summary
 *
 * @param array $workflow Workflow data
 * @return string Trigger summary
 */
function get_trigger_summary( $workflow ) {
	if ( empty( $workflow['trigger'] ) ) {
		return __( 'No trigger configured', 'wpshadow' );
	}

	$trigger = $workflow['trigger'];
	$type = $trigger['type'];

	$summaries = array(
		'time_trigger'      => __( 'On schedule', 'wpshadow' ),
		'page_load_trigger' => __( 'On page load', 'wpshadow' ),
		'event_trigger'     => __( 'On event', 'wpshadow' ),
		'condition_trigger' => __( 'When condition met', 'wpshadow' ),
	);

	return isset( $summaries[ $type ] ) ? $summaries[ $type ] : $type;
}
