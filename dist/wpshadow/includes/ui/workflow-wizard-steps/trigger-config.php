<?php
/**
 * Trigger Configuration Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;

$trigger_id  = Form_Param_Helper::get( 'trigger', 'key', '' );
$workflow_id = Form_Param_Helper::get( 'workflow', 'key', '' );

if ( empty( $trigger_id ) ) {
	include __DIR__ . '/trigger-selection.php';
	return;
}

$categories   = \WPShadow\Workflow\Workflow_Wizard::get_trigger_categories();
$trigger_data = null;
foreach ( $categories as $category ) {
	if ( isset( $category['triggers'][ $trigger_id ] ) ) {
		$trigger_data = $category['triggers'][ $trigger_id ];
		break;
	}
}

if ( ! $trigger_data ) {
	include __DIR__ . '/trigger-selection.php';
	return;
}

$config_fields = \WPShadow\Workflow\Workflow_Wizard::get_trigger_config( $trigger_id );
$has_config    = ! empty( $config_fields );
$back_url      = admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=trigger' );
?>

<div class="wps-page-container">
	<?php wpshadow_render_wizard_back_button( $back_url ); ?>
	<?php
	wpshadow_render_page_header(
		__( 'Configure Trigger', 'wpshadow' ),
		__( 'Set the conditions for when this workflow should start.', 'wpshadow' ),
		'dashicons-controls-play'
	);
	?>

	<?php if ( ! empty( $trigger_data ) ) : ?>
		<div class="wps-alert wps-alert--info wps-wizard-trigger-alert">
			<strong><?php esc_html_e( 'Selected trigger:', 'wpshadow' ); ?></strong>
			<?php echo esc_html( $trigger_data['label'] ); ?>
			<?php if ( ! empty( $trigger_data['description'] ) ) : ?>
				<span class="wps-text-muted">- <?php echo esc_html( $trigger_data['description'] ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $has_config ) : ?>
		<div class="wps-card wps-wizard-step-card">
			<div class="wps-card-body">
				<form id="trigger-config-form" class="wps-form">
					<input type="hidden" name="trigger_id" value="<?php echo esc_attr( $trigger_id ); ?>">
					<input type="hidden" name="workflow_id" value="<?php echo esc_attr( $workflow_id ); ?>">

					<?php foreach ( $config_fields as $field ) : ?>
						<div class="wps-form-group">
							<?php if ( ! in_array( $field['type'], array( 'checkbox', 'checkbox_group', 'hidden' ), true ) ) : ?>
								<label for="field_<?php echo esc_attr( $field['id'] ); ?>" class="wps-form-label">
									<?php echo esc_html( $field['label'] ); ?>
									<?php if ( ! empty( $field['required'] ) ) : ?>
										<span class="wps-text-danger">*</span>
									<?php endif; ?>
								</label>
							<?php endif; ?>

							<?php
							switch ( $field['type'] ) {
								case 'hidden':
									?>
									<input
										type="hidden"
										name="<?php echo esc_attr( $field['id'] ); ?>"
										value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
									>
									<?php
									break;

								case 'text':
									?>
									<input
										type="text"
										id="field_<?php echo esc_attr( $field['id'] ); ?>"
										name="<?php echo esc_attr( $field['id'] ); ?>"
										placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
										value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
										<?php echo ! empty( $field['readonly'] ) ? 'readonly' : ''; ?>
										<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
										class="wps-input"
									>
									<?php
									break;

								case 'number':
									?>
									<input
										type="number"
										id="field_<?php echo esc_attr( $field['id'] ); ?>"
										name="<?php echo esc_attr( $field['id'] ); ?>"
										placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
										value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
										<?php echo isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : ''; ?>
										<?php echo isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : ''; ?>
										<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
										class="wps-input wps-wizard-input-sm"
									>
									<?php
									break;

								case 'time':
									?>
									<input
										type="time"
										id="field_<?php echo esc_attr( $field['id'] ); ?>"
										name="<?php echo esc_attr( $field['id'] ); ?>"
										value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
										<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
										class="wps-input wps-wizard-input-md"
									>
									<?php
									break;

								case 'select':
									?>
									<select
										id="field_<?php echo esc_attr( $field['id'] ); ?>"
										name="<?php echo esc_attr( $field['id'] ); ?>"
										<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
										class="wps-select"
									>
										<?php foreach ( (array) ( $field['options'] ?? array() ) as $value => $label ) : ?>
											<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $field['default'] ?? '', $value ); ?>>
												<?php echo esc_html( $label ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<?php
									break;

								case 'textarea':
									?>
									<textarea
										id="field_<?php echo esc_attr( $field['id'] ); ?>"
										name="<?php echo esc_attr( $field['id'] ); ?>"
										placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
										rows="<?php echo esc_attr( $field['rows'] ?? 3 ); ?>"
										<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
										class="wps-textarea"
									><?php echo esc_textarea( $field['default'] ?? '' ); ?></textarea>
									<?php
									break;

								case 'checkbox':
									$checked = ! empty( $field['default'] );
									?>
									<label class="wps-checkbox-wrapper">
										<input
											type="checkbox"
											id="field_<?php echo esc_attr( $field['id'] ); ?>"
											name="<?php echo esc_attr( $field['id'] ); ?>"
											value="1"
											class="wps-checkbox"
											<?php checked( $checked ); ?>
										>
										<span class="wps-checkbox-label"><?php echo esc_html( $field['label'] ); ?></span>
									</label>
									<?php
									break;

								case 'checkbox_group':
									$default_values = $field['default'] ?? array();
									if ( ! is_array( $default_values ) ) {
										$default_values = array( $default_values );
									}
									?>
									<fieldset class="wps-checkbox-group">
										<legend><?php echo esc_html( $field['label'] ); ?></legend>
										<?php foreach ( (array) ( $field['options'] ?? array() ) as $value => $label ) : ?>
											<label class="wps-checkbox-wrapper">
												<input
													type="checkbox"
													name="<?php echo esc_attr( $field['id'] ); ?>[]"
													value="<?php echo esc_attr( $value ); ?>"
													class="wps-checkbox"
													<?php checked( in_array( $value, $default_values, true ) ); ?>
												>
												<span class="wps-checkbox-label"><?php echo esc_html( $label ); ?></span>
											</label>
										<?php endforeach; ?>
									</fieldset>
									<?php
									break;

								case 'user_search':
									$search_id  = 'field_' . $field['id'] . '_search';
									$hidden_id  = 'field_' . $field['id'];
									$results_id = 'field_' . $field['id'] . '_results';
									?>
									<input
										type="text"
										id="<?php echo esc_attr( $search_id ); ?>"
										placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
										autocomplete="off"
										class="wps-input wps-user-search"
										data-ajax-action="<?php echo esc_attr( $field['ajax_action'] ?? '' ); ?>"
										data-nonce="<?php echo esc_attr( $field['nonce'] ?? '' ); ?>"
										data-target="#<?php echo esc_attr( $hidden_id ); ?>"
										data-results="#<?php echo esc_attr( $results_id ); ?>"
									>
									<input
										type="hidden"
										id="<?php echo esc_attr( $hidden_id ); ?>"
										name="<?php echo esc_attr( $field['id'] ); ?>"
										value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
									>
									<div id="<?php echo esc_attr( $results_id ); ?>" class="wps-layout-stack wps-layout-stack-sm wps-user-search-results" aria-live="polite"></div>
									<?php
									break;

								case 'diagnostic_search':
									$popular_items = $field['popular'] ?? array();
									?>
									<div class="wps-diagnostic-search" data-ajax-action="<?php echo esc_attr( $field['ajax_action'] ?? '' ); ?>" data-nonce="<?php echo esc_attr( $field['nonce'] ?? '' ); ?>">
										<input
											type="text"
											id="field_<?php echo esc_attr( $field['id'] ); ?>_search"
											class="wps-input wps-diagnostic-search-input"
											placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
											autocomplete="off"
										>
										<input
											type="hidden"
											name="<?php echo esc_attr( $field['id'] ); ?>"
											value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
										>

										<?php if ( ! empty( $popular_items ) ) : ?>
											<div class="wps-diagnostic-popular wps-wizard-popular-list">
												<div class="wps-text-xs wps-text-muted wps-wizard-popular-label">
													<?php esc_html_e( 'Most popular diagnostics', 'wpshadow' ); ?>
												</div>
												<div class="wps-layout-stack wps-layout-stack-sm">
													<?php foreach ( $popular_items as $item ) : ?>
														<button type="button" class="wps-btn wps-btn--secondary wps-btn--sm wps-diagnostic-popular-item" data-slug="<?php echo esc_attr( $item['slug'] ?? '' ); ?>">
															<?php echo esc_html( $item['label'] ?? '' ); ?>
														</button>
													<?php endforeach; ?>
												</div>
											</div>
										<?php endif; ?>

										<div class="wps-layout-stack wps-layout-stack-sm wps-diagnostic-results wps-wizard-results-spacing"></div>
									</div>
									<?php
									break;
							}
							?>

							<?php if ( ! empty( $field['note'] ) ) : ?>
								<p class="wps-help-text"><?php echo esc_html( $field['note'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>

					<div class="wps-form-actions wps-wizard-form-actions">
						<button type="submit" class="wps-btn wps-btn--primary">
							<?php esc_html_e( 'Continue to Actions', 'wpshadow' ); ?>
							<span class="dashicons dashicons-arrow-right-alt2 wps-wizard-inline-icon" aria-hidden="true"></span>
						</button>
					</div>
				</form>
			</div>
		</div>
	<?php else : ?>
		<div class="wps-card wps-wizard-step-card wps-wizard-empty-card">
			<div class="wps-card-body wps-wizard-empty-card-body">
				<p class="wps-text-base wps-text-muted">
					<?php esc_html_e( 'This trigger does not need additional configuration. Redirecting to actions…', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<script>
			const workflowId = '<?php echo esc_js( $workflow_id ); ?>';
			const triggerId = '<?php echo esc_js( $trigger_id ); ?>';
			const baseUrl = workflowId ? '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>';
			window.location.href = baseUrl + '&step=action-selection&trigger=' + triggerId;
		</script>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('#trigger-config-form').on('submit', function(e) {
		e.preventDefault();

		const formData = {};
		$(this).serializeArray().forEach(function(field) {
			if (field.name.endsWith('[]')) {
				const key = field.name.replace('[]', '');
				if (!formData[key]) {
					formData[key] = [];
				}
				formData[key].push(field.value);
			} else {
				formData[field.name] = field.value;
			}
		});

		sessionStorage.setItem('workflow_trigger_config', JSON.stringify(formData));

		const triggerId = formData.trigger_id;
		const workflowId = formData.workflow_id;
		const baseUrl = workflowId ? '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>';
		window.location.href = baseUrl + '&step=action-selection&trigger=' + triggerId;
	});

	$('.wps-user-search').each(function() {
		const $input = $(this);
		const ajaxAction = $input.data('ajax-action');
		const nonce = $input.data('nonce');
		const $hidden = $($input.data('target'));
		const $results = $($input.data('results'));
		let searchTimer = null;

		if (!ajaxAction || !$hidden.length || !$results.length) {
			return;
		}

		$input.on('input', function() {
			const query = $(this).val().trim();
			$hidden.val('');
			clearTimeout(searchTimer);
			if (query.length < 2) {
				$results.empty();
				return;
			}

			searchTimer = setTimeout(function() {
				$.post(ajaxurl, {
					action: ajaxAction,
					nonce: nonce,
					term: query
				}, function(response) {
					$results.empty();
					if (!response.success || !response.data.users || response.data.users.length === 0) {
						$results.append('<div class="wps-text-sm wps-text-muted"><?php echo esc_js( __( 'No users found.', 'wpshadow' ) ); ?></div>');
						return;
					}

					response.data.users.forEach(function(user) {
						const $button = $('<button type="button" class="wps-btn wps-btn--secondary wps-btn--sm">').text(user.label);
						$button.on('click', function() {
							$hidden.val(user.id);
							$input.val(user.label);
							$results.empty();
						});
						$results.append($button);
					});
				});
			}, 300);
		});
	});

	$('.wps-diagnostic-search').each(function() {
		const $wrap = $(this);
		const ajaxAction = $wrap.data('ajax-action');
		const nonce = $wrap.data('nonce');
		const $input = $wrap.find('.wps-diagnostic-search-input');
		const $hidden = $wrap.find('input[type="hidden"]');
		const $results = $wrap.find('.wps-diagnostic-results');
		const $popular = $wrap.find('.wps-diagnostic-popular-item');
		let searchTimer = null;

		$input.on('input', function() {
			const query = $(this).val().trim();
			clearTimeout(searchTimer);
			if (query.length < 2) {
				$results.empty();
				return;
			}
			searchTimer = setTimeout(function() {
				$.post(ajaxurl, {
					action: ajaxAction,
					nonce: nonce,
					search: query
				}, function(response) {
					$results.empty();
					if (!response.success || !response.data.items || response.data.items.length === 0) {
						$results.append('<div class="wps-text-sm wps-text-muted"><?php echo esc_js( __( 'No diagnostics found.', 'wpshadow' ) ); ?></div>');
						return;
					}
					response.data.items.forEach(function(item) {
						const $button = $('<button type="button" class="wps-btn wps-btn--secondary wps-btn--sm">').text(item.label);
						$button.on('click', function() {
							$hidden.val(item.slug);
							$input.val(item.label);
							$results.empty();
						});
						$results.append($button);
					});
				});
			}, 300);
		});

		$popular.on('click', function() {
			const slug = $(this).data('slug');
			$hidden.val(slug);
			$input.val($(this).text());
			$results.empty();
		});
	});
});
</script>
