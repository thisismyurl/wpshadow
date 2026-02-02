/**
 * WPShadow Guardian Scan Interface JavaScript
 *
 * Handles Guardian scan UI interactions for Phase 7.
 *
 * @package WPShadow
 * @since   1.2604.0300
 */

(function($) {
    'use strict';

    const GuardianScan = {
        /**
         * Initialize Guardian scan interface.
         */
        init: function() {
            this.bindEvents();
            this.checkPendingScans();
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            $(document).on('click', '.run-scan', this.handleRunScan);
            $(document).on('click', '.check-status', this.handleCheckStatus);
            $(document).on('click', '.view-results', this.handleViewResults);
            $(document).on('click', '#connect-existing', this.showConnectModal);
            $(document).on('click', '.modal-close', this.hideConnectModal);
            $(document).on('submit', '#connect-form', this.handleConnect);
            $(document).on('click', '#disconnect-guardian', this.handleDisconnect);
        },

        /**
         * Handle run scan button click.
         */
        handleRunScan: function(e) {
            e.preventDefault();

            const button = $(this);
            const scanType = button.data('scan-type');

            if (button.prop('disabled')) {
                return;
            }

            // Disable button
            button.prop('disabled', true).text(wpShadowGuardian.strings.scanning);

            // Show progress UI
            GuardianScan.showProgress(scanType);

            // Request scan
            $.ajax({
                url: wpShadowGuardian.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'wpshadow_guardian_scan',
                    nonce: wpShadowGuardian.nonce,
                    scan_type: scanType
                },
                success: function(response) {
                    if (response.success) {
                        // Start polling for results
                        GuardianScan.pollScanStatus(response.data.scan_id);
                    } else {
                        GuardianScan.showError(response.data.message);
                        button.prop('disabled', false).text('Run ' + GuardianScan.formatScanType(scanType) + ' Scan');
                    }
                },
                error: function() {
                    GuardianScan.showError('Network error. Please try again.');
                    button.prop('disabled', false).text('Run ' + GuardianScan.formatScanType(scanType) + ' Scan');
                }
            });
        },

        /**
         * Handle check status button click.
         */
        handleCheckStatus: function(e) {
            e.preventDefault();

            const button = $(this);
            const scanId = button.data('scan-id');

            button.prop('disabled', true).text(wpShadowGuardian.strings.checkingStatus);

            GuardianScan.checkScan(scanId, function(data) {
                if (data.status === 'complete') {
                    location.reload(); // Refresh to show completed status
                } else {
                    button.prop('disabled', false).text('Check Status');
                    GuardianScan.showNotice('Scan is still in progress. Please check back in a few minutes.');
                }
            }, function() {
                button.prop('disabled', false).text('Check Status');
            });
        },

        /**
         * Handle view results link click.
         */
        handleViewResults: function(e) {
            e.preventDefault();

            const scanId = $(this).data('scan-id');
            GuardianScan.showResults(scanId);
        },

        /**
         * Show connect modal.
         */
        showConnectModal: function(e) {
            e.preventDefault();
            $('#connect-modal').fadeIn(200);
        },

        /**
         * Hide connect modal.
         */
        hideConnectModal: function(e) {
            e.preventDefault();
            $('#connect-modal').fadeOut(200);
        },

        /**
         * Handle account connection.
         */
        handleConnect: function(e) {
            e.preventDefault();

            const apiKey = $('#guardian-api-key').val().trim();

            if (!apiKey) {
                WPShadowModal.alert({
                    title: 'API Key Required',
                    message: 'Please enter your API key.',
                    type: 'warning'
                });
                return;
            }

            const button = $(this).find('button[type="submit"]');
            const originalText = button.text();

            button.prop('disabled', true).text('Connecting...');

            $.ajax({
                url: wpShadowGuardian.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'wpshadow_guardian_connect',
                    nonce: wpShadowGuardian.nonce,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        GuardianScan.showNotice('Successfully connected to Guardian!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        GuardianScan.showNotice(response.data.message, 'error');
                        button.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    GuardianScan.showNotice('Network error. Please try again.', 'error');
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle account disconnection.
         */
        handleDisconnect: function(e) {
            e.preventDefault();

            const button = $(this);
            WPShadowModal.confirm({
                title: 'Disconnect Guardian',
                message: 'Are you sure you want to disconnect Guardian? You can reconnect anytime.',
                confirmText: 'Disconnect',
                cancelText: 'Cancel',
                type: 'warning',
                onConfirm: function() {
                    button.prop('disabled', true).text('Disconnecting...');

            $.ajax({
                url: wpShadowGuardian.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'wpshadow_guardian_disconnect',
                    nonce: wpShadowGuardian.nonce
                },
                success: function(response) {
                    if (response.success) {
                        GuardianScan.showNotice('Successfully disconnected from Guardian.', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        GuardianScan.showNotice(response.data.message, 'error');
                        button.prop('disabled', false).text('Disconnect Guardian');
                    }
                },
                error: function() {
                    GuardianScan.showNotice('Network error. Please try again.', 'error');
                        button.prop('disabled', false).text('Disconnect');
                    }
                });
                }
            });
        },

        /**
         * Show progress UI.
         */
        showProgress: function(scanType) {
            const $progress = $('#scan-progress');
            $progress.find('.progress-message').text('Analyzing your site with ' + GuardianScan.formatScanType(scanType) + ' scan...');
            $progress.fadeIn(300);

            // Animate progress bar (simulated)
            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 20;
                if (progress > 90) {
                    progress = 90;
                    clearInterval(interval);
                }
                $progress.find('.progress-fill').css('width', progress + '%');
            }, 500);
        },

        /**
         * Poll scan status.
         */
        pollScanStatus: function(scanId, attempts) {
            attempts = attempts || 0;

            if (attempts > 60) { // Stop after 5 minutes (60 * 5 seconds)
                GuardianScan.showError('Scan is taking longer than expected. Please check status later.');
                return;
            }

            setTimeout(function() {
                GuardianScan.checkScan(scanId, function(data) {
                    if (data.status === 'complete') {
                        GuardianScan.showResults(scanId, data);
                    } else if (data.status === 'error') {
                        GuardianScan.showError(data.message || 'Scan failed. Please try again.');
                    } else {
                        // Keep polling
                        GuardianScan.pollScanStatus(scanId, attempts + 1);
                    }
                }, function() {
                    GuardianScan.showError('Failed to check scan status.');
                });
            }, 5000); // Poll every 5 seconds
        },

        /**
         * Check scan status.
         */
        checkScan: function(scanId, successCallback, errorCallback) {
            $.ajax({
                url: wpShadowGuardian.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'wpshadow_guardian_check_scan',
                    nonce: wpShadowGuardian.nonce,
                    scan_id: scanId
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        errorCallback();
                    }
                },
                error: errorCallback
            });
        },

        /**
         * Show scan results.
         */
        showResults: function(scanId, data) {
            const $progress = $('#scan-progress');
            const $results = $('#scan-results');

            // Hide progress
            $progress.fadeOut(300);

            // Build results HTML
            let html = '<h2>Scan Results</h2>';

            if (data && data.findings) {
                html += '<div class="results-summary">';
                html += '<div class="summary-card">';
                html += '<h3>Issues Found</h3>';
                html += '<span class="big-number">' + data.findings.length + '</span>';
                html += '</div>';
                html += '</div>';

                html += '<table class="widefat striped">';
                html += '<thead><tr>';
                html += '<th>Severity</th>';
                html += '<th>Issue</th>';
                html += '<th>Description</th>';
                html += '</tr></thead>';
                html += '<tbody>';

                data.findings.forEach(function(finding) {
                    html += '<tr>';
                    html += '<td><span class="severity-badge severity-' + finding.severity + '">' + finding.severity + '</span></td>';
                    html += '<td>' + GuardianScan.escapeHtml(finding.title) + '</td>';
                    html += '<td>' + GuardianScan.escapeHtml(finding.description) + '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table>';
            } else {
                html += '<p>No results available yet. The scan may still be processing.</p>';
            }

            $results.html(html).fadeIn(300);

            // Re-enable scan buttons
            $('.run-scan').prop('disabled', false).each(function() {
                const scanType = $(this).data('scan-type');
                $(this).text('Run ' + GuardianScan.formatScanType(scanType) + ' Scan');
            });
        },

        /**
         * Show error message.
         */
        showError: function(message) {
            $('#scan-progress').fadeOut(300);
            GuardianScan.showNotice(message, 'error');

            // Re-enable scan buttons
            $('.run-scan').prop('disabled', false).each(function() {
                const scanType = $(this).data('scan-type');
                $(this).text('Run ' + GuardianScan.formatScanType(scanType) + ' Scan');
            });
        },

        /**
         * Show admin notice.
         */
        showNotice: function(message, type) {
            type = type || 'info';
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + GuardianScan.escapeHtml(message) + '</p></div>');
            $('.wpshadow-guardian-content').prepend($notice);

            // Make dismissible
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Check for pending scans on page load.
         */
        checkPendingScans: function() {
            const $pendingScans = $('.status-pending').closest('tr');

            if ($pendingScans.length > 0) {
                // Auto-refresh after 30 seconds if there are pending scans
                setTimeout(function() {
                    location.reload();
                }, 30000);
            }
        },

        /**
         * Format scan type for display.
         */
        formatScanType: function(scanType) {
            return scanType.charAt(0).toUpperCase() + scanType.slice(1);
        },

        /**
         * Escape HTML to prevent XSS.
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.wpshadow-guardian-page').length > 0) {
            GuardianScan.init();
        }
    });

})(jQuery);
