/**
 * WPS Update Simulator Scripts
 *
 * @package WPS_WP_SUPPORT
 * @since 1.2601.1111
 */

(function($) {
	'use strict';

	const UpdateSimulator = {
		init() {
			$('.wps-simulate-btn').on('click', this.simulateUpdate.bind(this));
			$('.wps-view-changelog').on('click', this.viewChangelog.bind(this));
			$('.wps-modal-close, .wps-modal-overlay').on('click', this.closeModal.bind(this));
			$('.wps-rollback-btn').on('click', this.rollback.bind(this));
			$(document).on('click', '.wps-deploy-btn', this.deployUpdate.bind(this));
		},

		simulateUpdate(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const plugin = $button.data('plugin');

			if (!confirm('Create backup and simulate this update in staging?')) {
				return;
			}

			$.ajax({
				url: wpsUpdateSimulator.ajax_url,
				method: 'POST',
				data: {
					action: 'wps_simulate_update',
					nonce: wpsUpdateSimulator.nonce,
					plugin: plugin
				},
				beforeSend() {
					$button.prop('disabled', true).text('Simulating...');
				},
				success(response) {
					if (response.success) {
						alert('Simulation prepared! Check staging site to test the update.');
						
						const $row = $button.closest('tr');
						$button.replaceWith(
							'<button type="button" class="button button-primary wps-deploy-btn" data-plugin="' + plugin + 
							'" data-snapshot="' + (response.data.snapshot_id || '') + '">Deploy to Live</button>'
						);
					} else {
						alert('Simulation failed: ' + response.data.message);
					}
				},
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false).text('Simulate Update');
				}
			});
		},

		deployUpdate(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const plugin = $button.data('plugin');

			if (!confirm('Deploy this update to live site? A backup will be created automatically.')) {
				return;
			}

			$.ajax({
				url: wpsUpdateSimulator.ajax_url,
				method: 'POST',
				data: {
					action: 'wps_deploy_update',
					nonce: wpsUpdateSimulator.nonce,
					plugin: plugin
				},
				beforeSend() {
					$button.prop('disabled', true).text('Deploying...');
				},
				success(response) {
					if (response.success) {
						alert('Update deployed successfully! Test your site to ensure everything works.');
						location.reload();
					} else {
						alert('Deployment failed: ' + response.data.message);
					}
				},
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false).text('Deploy to Live');
				}
			});
		},

		rollback(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const snapshotId = $button.data('snapshot');

			if (!confirm('Rollback to this snapshot? This will restore your site to its previous state.')) {
				return;
			}

			$.ajax({
				url: wpsUpdateSimulator.ajax_url,
				method: 'POST',
				data: {
					action: 'wps_rollback_update',
					nonce: wpsUpdateSimulator.nonce,
					snapshot_id: snapshotId
				},
				beforeSend() {
					$button.prop('disabled', true).text('Rolling back...');
				},
				success(response) {
					if (response.success) {
						alert('Rollback successful! Your site has been restored.');
						location.reload();
					} else {
						alert('Rollback failed: ' + response.data.message);
					}
				},
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false).text('Rollback');
				}
			});
		},

		viewChangelog(e) {
			e.preventDefault();
			const changelog = $(e.currentTarget).data('changelog');
			
			$('.wps-changelog-content').html(changelog);
			$('#wps-changelog-modal').fadeIn();
		},

		closeModal() {
			$('#wps-changelog-modal').fadeOut();
		}
	};

	$(document).ready(() => UpdateSimulator.init());

})(jQuery);
