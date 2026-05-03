/**
 * File Write Review Page — Frontend JS
 *
 * Handles all interactive behaviour on the Review Proposed File Changes page:
 *   - Dry-run preview rendering
 *   - Backup creation / download
 *   - Restore with confirmation
 *   - Apply with SFTP acknowledgment modal gate
 *   - Global trust preference toggle
 *
 * Depends on: jQuery, thisismyurlShadowFileReview (localized data)
 *
 * @package ThisIsMyURL\Shadow
 * @since 0.6093.1300
 */

/* global thisismyurlShadowFileReview, thisismyurlShadowModal */

(function ($) {
	'use strict';

	var cfg = thisismyurlShadowFileReview || {};
	var nonces = cfg.nonces || {};
	var i18n = cfg.i18n || {};
	var ajaxUrl = cfg.ajaxUrl || '';

	// Tracks which Apply button triggered the SFTP modal so the confirm
	// handler can issue the correct AJAX call.
	var _pendingApply = null;

	// =========================================================================
	// Bootstrap
	// =========================================================================

	$(function () {
		bindDryRun();
		bindBackup();
		bindRestore();
		bindApply();
		bindSftpModal();
		bindTrustAllCheckbox();
	});

	function sendRequest(data, options) {
		var requestOptions = options || {};

		return $.post(ajaxUrl, data)
			.done(function (res) {
				if (res && res.success) {
					if (typeof requestOptions.onSuccess === 'function') {
						requestOptions.onSuccess(res);
					}
					return;
				}

				if (typeof requestOptions.onError === 'function') {
					requestOptions.onError(getErrorMessage(res), res);
				}
			})
			.fail(function () {
				if (typeof requestOptions.onError === 'function') {
					requestOptions.onError(requestOptions.fallbackError || 'Request failed. Please try again.');
				}
			})
			.always(function () {
				if (typeof requestOptions.onAlways === 'function') {
					requestOptions.onAlways();
				}
			});
	}

	// =========================================================================
	// Dry-run preview
	// =========================================================================

	function bindDryRun() {
		$(document).on('click', '.thisismyurl-shadow-btn-dry-run', function () {
			var $btn = $(this);
			var findingId = $btn.data('finding-id');

			setCardStatus(findingId, 'info', i18n.dryRunPending || 'Running preview…');
			$btn.prop('disabled', true).text('Previewing…');

			sendRequest({
				action: 'thisismyurl_shadow_file_write_dry_run',
				nonce: nonces.dryRun,
				finding_id: findingId
			}, {
				fallbackError: 'Preview request failed. Please try again.',
				onSuccess: function (res) {
					if (res.data && res.data.diff_lines) {
						renderDiff(findingId, res.data.diff_lines);
					}
					setCardStatus(findingId, 'success', (res.data && res.data.message) || 'Preview generated.');
				},
				onError: function (message) {
					setCardStatus(findingId, 'error', message);
				},
				onAlways: function () {
					$btn.prop('disabled', false).text('Preview Changes');
				}
			});
		});
	}

	/**
	 * Render a diff line array into the diff area for a card.
	 *
	 * @param {string} findingId
	 * @param {Array} lines  Array of {type, content} objects.
	 */
	function renderDiff(findingId, lines) {
		var $area  = $('#thisismyurl-shadow-diff-' + findingId);
		var $inner = $area.find('.thisismyurl-shadow-diff-inner');
		var html   = '<table class="wps-file-review-diff-table">';

		lines.forEach(function (line) {
			var prefix = ' ';
			var rowClass = '';

			if (line.type === 'add') {
				prefix = '+';
				rowClass = ' wps-file-review-diff-row--add';
			} else if (line.type === 'remove') {
				prefix = '-';
				rowClass = ' wps-file-review-diff-row--remove';
			}

			html += '<tr class="wps-file-review-diff-row' + rowClass + '">';
			html += '<td class="wps-file-review-diff-prefix">' + prefix + '</td>';
			html += '<td class="wps-file-review-diff-content">' + escapeHtml(line.content) + '</td>';
			html += '</tr>';
		});

		html += '</table>';
		$inner.html(html);
		$area.show();
	}

	// =========================================================================
	// Backup
	// =========================================================================

	function bindBackup() {
		$(document).on('click', '.thisismyurl-shadow-btn-backup', function () {
			var $btn      = $(this);
			var findingId = $btn.data('finding-id');

			$btn.prop('disabled', true).text('Creating backup…');
			setCardStatus(findingId, 'info', 'Creating backup…');

			sendRequest({
				action: 'thisismyurl_shadow_file_write_backup',
				nonce: nonces.backup,
				finding_id: findingId
			}, {
				fallbackError: i18n.backupFailed || 'Backup failed.',
				onSuccess: function (res) {
					var msg = (i18n.backupSuccess || 'Backup created.') + ' ' + ((res.data && res.data.created_at_human) || '');
					var $card = $('#thisismyurl-shadow-review-card-' + findingId);

					$card.find('.thisismyurl-shadow-backup-status').each(function () {
						$(this)
							.removeClass('wps-file-review-status--warning wps-file-review-status--error')
							.addClass('wps-file-review-status wps-file-review-status--success')
							.text('✓ Backup created ' + ((res.data && res.data.created_at_human) || 'just now'));
					});

					$card.find('.thisismyurl-shadow-btn-restore')
						.removeClass('wps-file-review-restore--hidden')
						.show();

					if (res.data && res.data.download_url) {
						triggerDownload(res.data.download_url, 'thisismyurl-shadow-backup-' + findingId + '.txt');
					}

					setCardStatus(findingId, 'success', msg.trim());
					$btn.text('Refresh Backup');
				},
				onError: function (message) {
					setCardStatus(findingId, 'error', message);
					$btn.text('Create Backup');
				},
				onAlways: function () {
					$btn.prop('disabled', false);
				}
			});
		});
	}

	// =========================================================================
	// Restore
	// =========================================================================

	function bindRestore() {
		$(document).on('click', '.thisismyurl-shadow-btn-restore', function () {
			var $btn      = $(this);
			var findingId = $btn.data('finding-id');
			var confirmMsg = i18n.confirmRestore || 'Restore the file to its backup state?';

			if (typeof thisismyurlShadowModal !== 'undefined' && thisismyurlShadowModal.confirm) {
				thisismyurlShadowModal.confirm({
					title:       'Restore from Backup',
					message:     confirmMsg,
					confirmText: 'Yes, Restore',
					cancelText:  'Cancel',
					type:        'warning',
					onConfirm:   function () { doRestore($btn, findingId); }
				});
			} else if (window.confirm(confirmMsg)) {
				doRestore($btn, findingId);
			}
		});
	}

	function doRestore($btn, findingId) {
		$btn.prop('disabled', true).text('Restoring…');
		setCardStatus(findingId, 'info', 'Restoring from backup…');

		sendRequest({
			action: 'thisismyurl_shadow_file_write_restore',
			nonce: nonces.restore,
			finding_id: findingId
		}, {
			fallbackError: i18n.restoreFailed || 'Restore failed.',
			onSuccess: function () {
				setCardStatus(findingId, 'success', i18n.restoreSuccess || 'File restored.');
				markCardResolved(findingId);
			},
			onError: function (message) {
				setCardStatus(findingId, 'error', message);
			},
			onAlways: function () {
				$btn.prop('disabled', false).text('Restore from Backup');
			}
		});
	}

	// =========================================================================
	// Apply (with or without SFTP modal gate)
	// =========================================================================

	function bindApply() {
		$(document).on('click', '.thisismyurl-shadow-btn-apply', function () {
			var $btn           = $(this);
			var findingId      = $btn.data('finding-id');
			var filePath       = $btn.data('file-path');
			var needsWarning   = $btn.data('needs-warning') === 1 || $btn.data('needs-warning') === '1';
			var sftpInstructions = $btn.data('sftp-instructions') || '';
			var fileLabel      = $btn.data('file-label') || filePath;

			if ( needsWarning ) {
				// Show the SFTP acknowledgment modal.
				openSftpModal({
					findingId:         findingId,
					filePath:          filePath,
					fileLabel:         fileLabel,
					sftpInstructions:  sftpInstructions,
					$applyBtn:         $btn
				});
			} else {
				// Trust already established — apply directly.
				doApply(findingId, $btn, false, false, false);
			}
		});
	}

	// =========================================================================
	// SFTP Acknowledgment Modal
	// =========================================================================

	function bindSftpModal() {
		var $modal   = $('#thisismyurl-shadow-sftp-modal');
		var $overlay = $modal.find('.thisismyurl-shadow-modal-overlay');
		var $cancel  = $('#thisismyurl-shadow-sftp-modal-cancel');
		var $confirm = $('#thisismyurl-shadow-sftp-modal-confirm');
		var $ackRead = $('#thisismyurl-shadow-ack-read');

		// Enable/disable the Confirm button based on the "I have read" checkbox.
		$ackRead.on('change', function () {
			$confirm.prop('disabled', !$(this).is(':checked'));
		});

		// Cancel / overlay click → close.
		$cancel.on('click', closeSftpModal);
		$overlay.on('click', closeSftpModal);

		// Keyboard escape.
		$(document).on('keydown.thisismyurl-shadow-sftp-modal', function (e) {
			if (e.key === 'Escape' && $modal.is(':visible')) {
				closeSftpModal();
			}
		});

		// Confirm button → apply.
		$confirm.on('click', function () {
			if (!_pendingApply) { return; }

			var trustFile = $('#thisismyurl-shadow-ack-file-trust').is(':checked');
			var trustAll  = $('#thisismyurl-shadow-ack-all-trust').is(':checked');

			closeSftpModal();
			doApply(_pendingApply.findingId, _pendingApply.$applyBtn, true, trustFile, trustAll);
		});
	}

	function openSftpModal(opts) {
		_pendingApply = opts;

		var $modal = $('#thisismyurl-shadow-sftp-modal');

		// Reset state.
		$('#thisismyurl-shadow-ack-read').prop('checked', false);
		$('#thisismyurl-shadow-ack-file-trust').prop('checked', false);
		$('#thisismyurl-shadow-ack-all-trust').prop('checked', false);
		$('#thisismyurl-shadow-sftp-modal-confirm').prop('disabled', true);

		// Populate file label.
		$('#thisismyurl-shadow-sftp-modal-file-label').html(
			'<strong>File:</strong> <code>' + escapeHtml(opts.fileLabel) + '</code>'
		);

		// Populate per-file trust label.
		$('#thisismyurl-shadow-ack-file-trust-label').text(
			'Skip this warning for ' + opts.fileLabel + ' in future (per-file trust)'
		);

		// Render SFTP instructions as ordered list items.
		var $list = $('#thisismyurl-shadow-sftp-modal-instructions');
		$list.empty();

		var instructions = buildSftpInstructions(opts.fileLabel, opts.filePath, opts.sftpInstructions);
		instructions.forEach(function (step) {
			$list.append($('<li>').text(step));
		});

		// Show modal.
		$modal.show();
		document.body.style.overflow = 'hidden';
		$('#thisismyurl-shadow-sftp-modal-cancel').trigger('focus');
	}

	function closeSftpModal() {
		$('#thisismyurl-shadow-sftp-modal').hide();
		document.body.style.overflow = '';

		// Return focus to the Apply button.
		if (_pendingApply && _pendingApply.$applyBtn) {
			_pendingApply.$applyBtn.trigger('focus');
		}
		_pendingApply = null;
	}

	/**
	 * Build a list of SFTP recovery instruction steps.
	 *
	 * Falls back to generic steps if no custom instructions were provided.
	 *
	 * @param {string} fileLabel       Human-friendly file name.
	 * @param {string} filePath        Absolute file path.
	 * @param {string} customInstructions  Custom instructions from treatment (may be empty).
	 * @returns {string[]}
	 */
	function buildSftpInstructions(fileLabel, filePath, customInstructions) {
		if (customInstructions && customInstructions.trim().length > 0) {
			// Split on double-newline or numbered-list pattern if it's a block.
			return customInstructions.split(/\n+/).filter(Boolean);
		}

		// Generic fallback.
		return [
			'Open your FTP / SFTP client (e.g. FileZilla, Cyberduck, or Transmit).',
			'Connect to your server using your hosting credentials.',
			'Navigate to the file: ' + filePath,
			'Download a copy of the file to your computer as a local backup (if you haven\'t already).',
			'Open the This Is My URL Shadow backup you downloaded and copy its content.',
			'Upload the backup copy back to the server at the same path, overwriting the modified file.',
			'Reload your WordPress site to confirm it is working correctly.',
			'If you do not have SFTP access, use your hosting\'s cPanel File Manager or contact your host\'s support team.'
		];
	}

	// =========================================================================
	// AJAX: Execute Apply
	// =========================================================================

	function doApply(findingId, $btn, acknowledged, trustFile, trustAll) {
		$btn.prop('disabled', true).text('Applying…');
		setCardStatus(findingId, 'info', 'Applying fix…');

		sendRequest({
			action:       'thisismyurl_shadow_file_write_apply',
			nonce:        nonces.apply,
			finding_id:   findingId,
			acknowledged: acknowledged ? 1 : 0,
			trust_file:   trustFile ? 1 : 0,
			trust_all:    trustAll ? 1 : 0
		}, {
			fallbackError: i18n.applyFailed || 'Apply failed. Please try again.',
			onSuccess: function (res) {
				setCardStatus(findingId, 'success', (res.data && res.data.message) || i18n.applySuccess);
				markCardResolved(findingId);
			},
			onError: function (message) {
				setCardStatus(findingId, 'error', message);
				$btn.prop('disabled', false).text('Apply Fix');
			}
		});
	}

	// =========================================================================
	// Global trust checkbox (outside modal)
	// =========================================================================

	function bindTrustAllCheckbox() {
		$('#thisismyurl-shadow-trust-all').on('change', function () {
			var checked = $(this).is(':checked');
			// We piggyback on the apply nonce for trust-only preference updates.
			$.post(ajaxUrl, {
				action:       'thisismyurl_shadow_file_write_apply',
				nonce:        nonces.apply,
				finding_id:   '__trust_only__',
				acknowledged: 0,
				trust_file:   0,
				trust_all:    checked ? 1 : 0
			});
			// No need to wait for response — this is a best-effort preference save.
		});
	}

	// =========================================================================
	// UI helpers
	// =========================================================================

	/**
	 * Show an inline status message inside a card.
	 *
	 * @param {string} findingId
	 * @param {string} type  'success' | 'error' | 'info'
	 * @param {string} message
	 */
	function setCardStatus(findingId, type, message) {
		var $el = $('#thisismyurl-shadow-status-' + findingId);
		var typeClass = 'wps-file-review-status-box--' + (type || 'info');

		$el
			.removeClass('wps-file-review-status-box--success wps-file-review-status-box--error wps-file-review-status-box--info')
			.addClass('is-visible ' + typeClass)
			.text(message);
	}

	/**
	 * Mark a card as resolved (applied or restored) with a success overlay.
	 *
	 * @param {string} findingId
	 */
	function markCardResolved(findingId) {
		var $card = $('#thisismyurl-shadow-review-card-' + findingId);
		$card.find('.thisismyurl-shadow-btn-apply').prop('disabled', true).text('✓ Applied');
		$card.addClass('is-resolved');
	}

	/**
	 * Extract a user-friendly error message from a jQuery AJAX response.
	 *
	 * @param {Object} res
	 * @returns {string}
	 */
	function getErrorMessage(res) {
		if (res && res.data && (res.data.message || typeof res.data === 'string')) {
			return res.data.message || res.data;
		}
		return 'An unexpected error occurred. Please try again.';
	}

	/**
	 * Trigger a file download using a data URI.
	 *
	 * @param {string} dataUri
	 * @param {string} filename
	 */
	function triggerDownload(dataUri, filename) {
		var $a = $('<a>')
			.attr('href', dataUri)
			.attr('download', filename)
			.prop('hidden', true);
		$('body').append($a);
		$a[0].click();
		$a.remove();
	}

	/**
	 * Escape a string for safe HTML insertion.
	 *
	 * @param {string} str
	 * @returns {string}
	 */
	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

}(jQuery));
