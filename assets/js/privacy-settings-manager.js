jQuery(document).ready(function($) {
	const cfg = window.wpshadowPrivacySettings || {};
	const strings = cfg.strings || {};

	$('.wpshadow-privacy-form').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');
		var $status = $('#wpshadow-privacy-status');

		var data = {
			action: 'wpshadow_update_privacy_settings',
			nonce: $form.find('input[name="_wpnonce"]').val(),
			consent_required: $form.find('input[name="consent_required"]').prop('checked'),
			collect_analytics: $form.find('input[name="collect_analytics"]').prop('checked'),
			data_retention_days: $form.find('input[name="data_retention_days"]').val(),
			export_user_data: $form.find('input[name="export_user_data"]').prop('checked'),
			delete_user_data: $form.find('input[name="delete_user_data"]').prop('checked'),
			anonymize_on_delete: $form.find('input[name="anonymize_on_delete"]').prop('checked')
		};

		$btn.prop('disabled', true).text(strings.saving || 'Saving...');
		$status.html('');

		$.post(ajaxurl, data, function(response) {
			if (response.success) {
				$status.html('<span class="wps-status-success">✓ ' + (strings.saved || 'Saved') + '</span>');
			} else {
				$status.html('<span class="wps-status-error">✗ ' + (response.data.message || (strings.error || 'Error')) + '</span>');
			}
			$btn.prop('disabled', false).text(strings.save_button || 'Save Privacy Settings');
		});
	});
});
