<?php
/**
 * Trigger Selection Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = \WPShadow\Workflow\Workflow_Wizard::get_trigger_categories();
?>

<div class="wizard-step trigger-selection">
	<h2><?php esc_html_e( 'Choose a Trigger', 'wpshadow' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Choose what triggers your workflow. When this event occurs, your chosen actions will run automatically.', 'wpshadow' ); ?>
	</p>

	<div class="trigger-categories">
		<?php foreach ( $categories as $category_id => $category ) : ?>
			<div class="trigger-category">
				<h3>
					<span class="dashicons dashicons-<?php echo esc_attr( $category['icon'] ); ?>"></span>
					<?php echo esc_html( $category['label'] ); ?>
				</h3>
				<div class="trigger-options">
					<?php foreach ( $category['triggers'] as $trigger_id => $trigger ) : ?>
						<button class="trigger-option" data-trigger-id="<?php echo esc_attr( $trigger_id ); ?>">
							<span class="trigger-icon">
								<span class="dashicons dashicons-<?php echo esc_attr( $trigger['icon'] ); ?>"></span>
							</span>
							<span class="trigger-content">
								<strong class="trigger-label"><?php echo esc_html( $trigger['label'] ); ?></strong>
								<span class="trigger-description"><?php echo esc_html( $trigger['description'] ); ?></span>
							</span>
							<span class="trigger-arrow">
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
.wizard-step h2 {
	margin-top: 0;
	font-size: 24px;
	margin-bottom: 10px;
}

.wizard-step .description {
	font-size: 14px;
	margin-bottom: 30px;
}

.trigger-categories {
	display: flex;
	flex-direction: column;
	gap: 30px;
}

.trigger-category h3 {
	font-size: 16px;
	font-weight: 600;
	margin: 0 0 15px 0;
	display: flex;
	align-items: center;
	gap: 8px;
	color: #2271b1;
}

.trigger-category h3 .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

.trigger-options {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 12px;
}

.trigger-option {
	display: flex;
	align-items: center;
	gap: 15px;
	padding: 16px;
	background: #f9f9f9;
	border: 2px solid #ddd;
	border-radius: 6px;
	cursor: pointer;
	transition: all 0.2s ease;
	text-align: left;
	width: 100%;
}

.trigger-option:hover {
	background: #fff;
	border-color: #2271b1;
	transform: translateY(-2px);
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.trigger-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #2271b1;
	border-radius: 8px;
	color: #fff;
}

.trigger-icon .dashicons {
	width: 24px;
	height: 24px;
	font-size: 24px;
}

.trigger-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.trigger-label {
	font-size: 14px;
	font-weight: 600;
	color: #000;
	display: block;
}

.trigger-description {
	font-size: 12px;
	color: #666;
	display: block;
}

.trigger-arrow {
	flex-shrink: 0;
	color: #2271b1;
	opacity: 0;
	transition: opacity 0.2s ease;
}

.trigger-option:hover .trigger-arrow {
	opacity: 1;
}

.trigger-arrow .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
	$('.trigger-option').on('click', function() {
		const triggerId = $(this).data('trigger-id');
		
		// Store selected trigger in sessionStorage
		sessionStorage.setItem('workflow_trigger_id', triggerId);
		
		// Navigate to trigger config step
		window.location.href = '<?php echo admin_url( 'admin.php?page=wpshadow-workflows&action=create&step=trigger-config' ); ?>&trigger=' + triggerId;
	});
});
</script>
