/**
 * WPShadow Reports Dashboard JavaScript
 */

(function ($) {
	'use strict';

	const WPShadowReports = {
		init: function () {
			this.bindEvents();
		},

		bindEvents: function () {
			$('#btn-refresh-issues').on('click', this.refreshIssues.bind(this));
			$('#btn-export-pdf').on('click', this.exportPdf.bind(this));
			$('.btn-dismiss').on('click', this.deleteIssue.bind(this));
			$('.btn-snooze').on('click', this.showSnoozeMenu.bind(this));
			$('.btn-details').on('click', this.toggleDetails.bind(this));
			$('#issues-filter-form').on('submit', this.submitFilter.bind(this));
		},

		refreshIssues: function (e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const originalText = $btn.text();

			$btn.prop('disabled', true).text(wpShadowReports.i18n.refreshing);

			$.ajax({
				url: wpShadowReports.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_refresh_issues',
					nonce: wpShadowReports.nonce,
				},
				success: (response) => {
					if (response.success) {
						this.updateDashboard(response.data);
						this.showNotice('success', 'Issues refreshed successfully!');
						setTimeout(() => location.reload(), 1500);
					} else {
						this.showNotice('error', response.data || wpShadowReports.i18n.error);
					}
				},
				error: () => {
					this.showNotice('error', wpShadowReports.i18n.error);
				},
				complete: () => {
					$btn.prop('disabled', false).text(originalText);
				},
			});
		},

		exportPdf: function (e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const originalText = $btn.text();

			$btn.prop('disabled', true).text(wpShadowReports.i18n.exporting);

			$.ajax({
				url: wpShadowReports.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_export_pdf',
					nonce: wpShadowReports.nonce,
				},
				success: (response) => {
					if (response.success) {
						this.downloadJson(response.data.data, response.data.filename);
						this.showNotice('success', wpShadowReports.i18n.exportComplete);
					} else {
						this.showNotice('error', response.data || wpShadowReports.i18n.error);
					}
				},
				error: () => {
					this.showNotice('error', wpShadowReports.i18n.error);
				},
				complete: () => {
					$btn.prop('disabled', false).text(originalText);
				},
			});
		},

		showSnoozeMenu: function (e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const $row = $btn.closest('tr');
			const issueId = $row.data('issue-id');

			// Remove existing menu if present
			$('.snooze-menu').remove();

			// Create snooze menu
			const menuHtml = `
				<div class="snooze-menu">
					<button type="button" class="snooze-option" data-duration="24">Snooze 24 hours</button>
					<button type="button" class="snooze-option" data-duration="48">Snooze 48 hours</button>
					<button type="button" class="snooze-option" data-duration="72">Snooze 72 hours</button>
					<button type="button" class="snooze-option" data-duration="week">Snooze 1 week</button>
					<button type="button" class="snooze-option permanent" data-duration="permanent">Dismiss Permanently</button>
					<button type="button" class="snooze-cancel">Cancel</button>
				</div>
			`;

			$btn.after(menuHtml);

			// Bind click handlers
			$('.snooze-option').on('click', (event) => {
				const duration = $(event.currentTarget).data('duration');
				if (duration === 'permanent') {
					this.dismissIssuePermanent(issueId, $row);
				} else {
					this.snoozeIssue(issueId, duration, $row);
				}
				$('.snooze-menu').remove();
			});

			$('.snooze-cancel').on('click', () => {
				$('.snooze-menu').remove();
			});

			// Close menu if clicking outside
			$(document).one('click', (event) => {
				if (!$(event.target).closest('.snooze-menu, .btn-snooze').length) {
					$('.snooze-menu').remove();
				}
			});
		},

		snoozeIssue: function (issueId, duration, $row) {
			$.ajax({
				url: wpShadowReports.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_snooze_issue',
					issue_id: issueId,
					duration: duration,
					nonce: wpShadowReports.nonce,
				},
				success: (response) => {
					if (response.success) {
						const snoozeText = response.data.duration_label || 'snoozed';
						$row.fadeOut(300, function () {
							$(this).remove();
						});
						this.showNotice('success', 'Issue snoozed - ' + snoozeText);
					} else {
						this.showNotice('error', response.data.message || wpShadowReports.i18n.error);
					}
				},
				error: () => {
					this.showNotice('error', wpShadowReports.i18n.error);
				},
			});
		},

		dismissIssuePermanent: function (issueId, $row) {
			if (!confirm('Permanently dismiss this issue? You can restore it later from history.')) {
				return;
			}

			$.ajax({
				url: wpShadowReports.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_dismiss_issue_permanent',
					issue_id: issueId,
					nonce: wpShadowReports.nonce,
				},
				success: (response) => {
					if (response.success) {
						$row.fadeOut(300, function () {
							$(this).next('.details-row').fadeOut(300, function () {
								$(this).remove();
							});
							$(this).remove();
						});
						this.showNotice('success', 'Issue permanently dismissed');
					} else {
						this.showNotice('error', response.data.message || wpShadowReports.i18n.error);
					}
				},
				error: () => {
					this.showNotice('error', wpShadowReports.i18n.error);
				},
			});
		},

		deleteIssue: function (e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const issueId = $btn.data('issue-id');

			if (!confirm('Are you sure you want to dismiss this issue?')) {
				return;
			}

			const originalText = $btn.text();
			$btn.prop('disabled', true).text(wpShadowReports.i18n.deleting);

			$.ajax({
				url: wpShadowReports.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_delete_issue',
					issue_id: issueId,
					nonce: wpShadowReports.nonce,
				},
				success: (response) => {
					if (response.success) {
						$btn.closest('tr').fadeOut(300, function () {
							$(this).next('.details-row').fadeOut(300, function () {
								$(this).remove();
							});
							$(this).remove();
						});
						this.showNotice('success', wpShadowReports.i18n.deleted);
					} else {
						this.showNotice('error', response.data || wpShadowReports.i18n.error);
						$btn.prop('disabled', false).text(originalText);
					}
				},
				error: () => {
					this.showNotice('error', wpShadowReports.i18n.error);
					$btn.prop('disabled', false).text(originalText);
				},
			});
		},

		toggleDetails: function (e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const $row = $btn.closest('tr');
			const $detailsRow = $row.next('.details-row');

			if ($detailsRow.hasClass('active')) {
				$detailsRow.fadeOut(300, function () {
					$(this).removeClass('active');
				});
				$btn.text('Details');
			} else {
				$detailsRow.addClass('active').hide().fadeIn(300);
				$btn.text('Hide');
			}
		},

		submitFilter: function (e) {
			// Allow default form submission
			return true;
		},

		updateDashboard: function (data) {
			// Update summary cards
			$('.summary-card.critical .summary-value').text(data.breakdown.critical || 0);
			$('.summary-card.high .summary-value').text(data.breakdown.high || 0);
			$('.summary-card.medium .summary-value').text(data.breakdown.medium || 0);
			$('.summary-card.low .summary-value').text(data.breakdown.low || 0);

			// Update total issues
			$('.wpshadow-stats .stat-box:first .stat-value').text(data.total_issues || 0);
		},

		downloadJson: function (jsonData, filename) {
			const element = document.createElement('a');
			element.setAttribute('href', 'data:application/json;charset=utf-8,' + encodeURIComponent(jsonData));
			element.setAttribute('download', filename);
			element.style.display = 'none';
			document.body.appendChild(element);
			element.click();
			document.body.removeChild(element);
		},

		showNotice: function (type, message) {
			const noticeClass = 'notice-' + (type === 'success' ? 'success' : 'error');
			const $notice = $('<div class="notice ' + noticeClass + '"><p>' + message + '</p></div>');

			$('.wpshadow-reports').prepend($notice);

			setTimeout(() => {
				$notice.fadeOut(300, function () {
					$(this).remove();
				});
			}, 5000);
		},
	};

	$(document).ready(function () {
		WPShadowReports.init();
	});

})(jQuery);
