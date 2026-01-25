<?php
/**
 * Visual Workflow Builder - Scratch-style block-based automation interface
 *
 * Modern, accessible workflow builder with clean design
 *
 * @package WPShadow
 * @subpackage Views
 * @since 1.2601.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
}

// Get available blocks.
$triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
$actions  = \WPShadow\Workflow\Block_Registry::get_actions();
?>

<div class="wrap wps-workflow-builder">
	<!-- Header -->
	<div class="wps-workflow-builder-header">
		<div>
			<h1 class="wps-workflow-builder-title">
				<span class="dashicons dashicons-block-default" aria-hidden="true"></span>
				<?php esc_html_e( 'Visual Workflow Builder', 'wpshadow' ); ?>
			</h1>
			<p class="wps-workflow-builder-description">
				<?php esc_html_e( 'Build automation workflows using visual blocks. Create "if-then" rules like Scratch programming.', 'wpshadow' ); ?>
			</p>
		</div>
		<div class="wps-workflow-toolbar">
			<button id="wps-save-workflow" class="wps-btn primary" aria-label="<?php esc_attr_e( 'Save workflow', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-cloud-saved" aria-hidden="true"></span>
				<?php esc_html_e( 'Save Workflow', 'wpshadow' ); ?>
			</button>
			<button id="wps-test-workflow" class="wps-btn secondary" aria-label="<?php esc_attr_e( 'Test workflow', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-media-play" aria-hidden="true"></span>
				<?php esc_html_e( 'Test Run', 'wpshadow' ); ?>
			</button>
			<button id="wps-clear-canvas" class="wps-btn ghost" aria-label="<?php esc_attr_e( 'Clear canvas', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-trash" aria-hidden="true"></span>
				<?php esc_html_e( 'Clear', 'wpshadow' ); ?>
			</button>
			<a href="<?php echo esc_url( 'https://wpshadow.com/kb/workflows' ); ?>" class="wps-btn ghost" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Learn about workflows (opens in new window)', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-book" aria-hidden="true"></span>
				<?php esc_html_e( 'Learn Workflows', 'wpshadow' ); ?>
			</a>
		</div>
	</div>

	<!-- Main Container -->
	<div class="wps-workflow-builder-container">
		<!-- Block Palette Sidebar -->
		<div class="wps-workflow-palette" role="toolbar" aria-label="<?php esc_attr_e( 'Workflow blocks', 'wpshadow' ); ?>">
			<!-- Triggers Section -->
			<div class="wps-palette-section">
				<h3 class="wps-palette-heading">
					<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
					<?php esc_html_e( 'Triggers (IF)', 'wpshadow' ); ?>
				</h3>
				<div class="wps-palette-blocks">
					<?php foreach ( $triggers as $trigger_id => $block ) : ?>
						<div 
							class="wps-block-item trigger" 
							draggable="true" 
							data-block-id="<?php echo esc_attr( $trigger_id ); ?>" 
							data-block-type="trigger"
							role="button"
							tabindex="0"
							aria-label="
							<?php
								/* translators: 1: Trigger label, 2: Trigger description */
								echo esc_attr( sprintf( __( 'Trigger: %1$s - %2$s', 'wpshadow' ), $block['label'], $block['description'] ) );
							?>
							"
						>
							<span class="wps-block-icon" aria-hidden="true">
								<span class="dashicons <?php echo esc_attr( $block['icon'] ); ?>"></span>
							</span>
							<div class="wps-block-info">
								<strong><?php echo esc_html( $block['label'] ); ?></strong>
								<small><?php echo esc_html( $block['description'] ); ?></small>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Actions Section -->
			<div class="wps-palette-section">
				<h3 class="wps-palette-heading">
					<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
					<?php esc_html_e( 'Actions (THEN)', 'wpshadow' ); ?>
				</h3>
				<div class="wps-palette-blocks">
					<?php foreach ( $actions as $action_id => $block ) : ?>
						<div 
							class="wps-block-item action" 
							draggable="true" 
							data-block-id="<?php echo esc_attr( $action_id ); ?>" 
							data-block-type="action"
							role="button"
							tabindex="0"
							aria-label="
							<?php
								/* translators: 1: Action label, 2: Action description */
								echo esc_attr( sprintf( __( 'Action: %1$s - %2$s', 'wpshadow' ), $block['label'], $block['description'] ) );
							?>
							"
						>
							<span class="wps-block-icon" aria-hidden="true">
								<span class="dashicons <?php echo esc_attr( $block['icon'] ); ?>"></span>
							</span>
							<div class="wps-block-info">
								<strong><?php echo esc_html( $block['label'] ); ?></strong>
								<small><?php echo esc_html( $block['description'] ); ?></small>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Canvas Area -->
		<div class="wps-workflow-canvas-wrapper">
			<!-- Workflow Name Input -->
			<div class="wps-workflow-header">
				<input 
					type="text" 
					id="wps-workflow-name" 
					class="wps-workflow-name-input"
					placeholder="<?php esc_attr_e( 'Name your workflow (e.g., Daily Security Scan)', 'wpshadow' ); ?>"
					aria-label="<?php esc_attr_e( 'Workflow name', 'wpshadow' ); ?>"
					value=""
				/>
			</div>

			<!-- Canvas -->
			<div 
				class="wps-workflow-canvas" 
				id="wps-canvas"
				role="main"
				aria-label="<?php esc_attr_e( 'Workflow canvas', 'wpshadow' ); ?>"
				aria-describedby="canvas-instructions"
			>
				<!-- Empty State -->
				<div class="wps-canvas-empty" data-empty-state>
					<span class="dashicons dashicons-block-default" aria-hidden="true"></span>
					<h3><?php esc_html_e( 'Build Your Workflow', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Drag blocks from the left to get started', 'wpshadow' ); ?></p>
					<ol class="wps-steps" id="canvas-instructions">
						<li data-step="1"><?php esc_html_e( 'Add a TRIGGER block (IF condition)', 'wpshadow' ); ?></li>
						<li data-step="2"><?php esc_html_e( 'Add ACTION blocks (THEN what to do)', 'wpshadow' ); ?></li>
						<li data-step="3"><?php esc_html_e( 'Configure each block', 'wpshadow' ); ?></li>
						<li data-step="4"><?php esc_html_e( 'Save and test your workflow', 'wpshadow' ); ?></li>
					</ol>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
