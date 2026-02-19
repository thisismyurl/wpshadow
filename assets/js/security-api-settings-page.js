(function() {
	'use strict';

	const cfg = window.wpshadowSecurityApiSettings || {};
	const strings = cfg.strings || {};
	const testNonce = cfg.testNonce || '';
	const saveNonce = cfg.saveNonce || '';

	document.querySelectorAll('.wpshadow-test-connection').forEach(function(btn) {
		btn.addEventListener('click', function(e) {
			e.preventDefault();
			const service = this.dataset.service;
			const statusEl = document.getElementById(service + '-status');
			if (!statusEl) {
				return;
			}

			statusEl.textContent = strings.testing || 'Testing...';
			statusEl.className = 'wpshadow-test-status';

			const formData = new FormData();
			formData.append('action', 'wpshadow_test_api_connection');
			formData.append('service', service);
			formData.append('nonce', testNonce);

			fetch(ajaxurl, {
				method: 'POST',
				body: formData
			})
				.then(function(response) { return response.json(); })
				.then(function(data) {
					if (data.success) {
						statusEl.textContent = '✅ ' + (strings.connected_success || 'Connected successfully');
						statusEl.className = 'wpshadow-test-status success';
					} else {
						statusEl.textContent = '❌ ' + (data.data && data.data.message ? data.data.message : (strings.connection_failed || 'Connection failed'));
						statusEl.className = 'wpshadow-test-status error';
					}
				})
				.catch(function() {
					statusEl.textContent = '❌ ' + (strings.connection_failed || 'Connection failed');
					statusEl.className = 'wpshadow-test-status error';
				});
		});
	});

	document.querySelectorAll('#wpshadow-api-settings-form input[type="checkbox"]').forEach(function(input) {
		input.addEventListener('change', function() {
			const form = this.closest('form');
			if (!form) {
				return;
			}

			const formData = new FormData(form);
			formData.append('action', 'wpshadow_save_api_keys');
			formData.append('nonce', saveNonce);

			fetch(ajaxurl, {
				method: 'POST',
				body: formData
			});
		});
	});
})();
