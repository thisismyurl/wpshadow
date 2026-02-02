/**
 * WPShadow Guardian Scripts
 * Consolidated JavaScript for Guardian dashboard functionality
 * Extracted from inline scripts in class-guardian-dashboard.php
 * 
 * @package WPShadow
 */

(function($) {
    'use strict';

    /**
     * WPShadow Guardian Dashboard Module
     */
    const WPShadowGuardian = {
        
        /**
         * Initialize Guardian functionality
         */
        init: function() {
            this.initToggleSwitch();
            this.initScanControls();
            this.initIssueActions();
            this.initAutoRefresh();
        },

        /**
         * Initialize Guardian status toggle switch
         */
        initToggleSwitch: function() {
            const self = this;

            $(document).on('change', '.wps-guardian-toggle input[type="checkbox"]', function() {
                const isChecked = $(this).is(':checked');
                self.toggleGuardian(isChecked, $(this));
            });
        },

        /**
         * Toggle Guardian on/off
         */
        toggleGuardian: function(enabled, toggleElement) {
            const self = this;
            const toggleSwitch = toggleElement.closest('.wps-toggle-switch');

            toggleElement.prop('disabled', true);

            $.ajax({
                url: wpshadowGuardian.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wps_toggle_guardian',
                    enabled: enabled ? 1 : 0,
                    nonce: wpshadowGuardian.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPShadowAdmin.showNotice('success', response.message);
                        
                        // Update status display
                        const statusBadge = $('.wps-guardian-status-badge');
                        if (enabled) {
                            statusBadge.removeClass('inactive').addClass('active');
                            statusBadge.find('.dashicons').removeClass('dashicons-no').addClass('dashicons-yes');
                            statusBadge.text(wpshadowGuardian.i18n.active || 'Active');
                        } else {
                            statusBadge.removeClass('active').addClass('inactive');
                            statusBadge.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-no');
                            statusBadge.text(wpshadowGuardian.i18n.inactive || 'Inactive');
                        }
                    } else {
                        // Revert toggle on error
                        toggleElement.prop('checked', !enabled);
                        WPShadowAdmin.showNotice('error', response.message);
                    }
                },
                error: function() {
                    // Revert toggle on error
                    toggleElement.prop('checked', !enabled);
                    WPShadowAdmin.showNotice('error', wpshadowGuardian.i18n.error);
                },
                complete: function() {
                    toggleElement.prop('disabled', false);
                }
            });
        },

        /**
         * Initialize scan controls (Run scan, Stop scan buttons)
         */
        initScanControls: function() {
            const self = this;

            $(document).on('click', '[data-scan-action]', function(e) {
                e.preventDefault();
                const action = $(this).data('scan-action');
                self.handleScanAction(action, $(this));
            });
        },

        /**
         * Handle scan actions (run, stop, reset)
         */
        handleScanAction: function(action, button) {
            const self = this;
            const originalText = button.text();
            const confirm = button.data('confirm');

                const executeAction = function() {
                    button.prop('disabled', true);

                    $.ajax({
                        url: wpshadowGuardian.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'wps_guardian_scan_' + action,
                            nonce: wpshadowGuardian.nonce
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                WPShadowAdmin.showNotice('success', response.message);
                            
                                if (action === 'run') {
                                    // Start monitoring progress
                                    self.monitorScanProgress();
                                } else if (action === 'reset') {
                                    // Reload page to show fresh state
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1500);
                                }
                            } else {
                                WPShadowAdmin.showNotice('error', response.message);
                            }
                        },
                        error: function() {
                            WPShadowAdmin.showNotice('error', wpshadowGuardian.i18n.error);
                            button.text(originalText);
                        },
                        complete: function() {
                            button.prop('disabled', false);
                        }
                    });
                };

                if (confirm && window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
                    window.WPShadowDesign.confirm(confirm, executeAction, function() {
                        button.text(originalText);
                    });
                    return;
                }

                if (confirm) {
                    WPShadowModal.confirm({
                        title: 'Confirm',
                        message: confirm,
                        onConfirm: executeAction,
                        onCancel: function() {
                            button.text(originalText);
                        }
                    });
                    return;
                }

                executeAction();
        },

        /**
         * Monitor and update scan progress in real-time
         */
        monitorScanProgress: function() {
            const self = this;
            const progressInterval = setInterval(() => {
                $.ajax({
                    url: wpshadowGuardian.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wps_guardian_scan_progress',
                        nonce: wpshadowGuardian.nonce
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update progress bar
                            const progress = response.data.progress || 0;
                            const status = response.data.status || '';
                            
                            $('.wps-scan-progress-fill').css('width', progress + '%');
                            $('.wps-scan-progress-percent').text(progress + '%');
                            
                            if (status) {
                                $('.wps-scan-progress-title').text(status);
                            }
                            
                            // If scan is complete, stop monitoring and reload
                            if (progress >= 100 || response.data.complete) {
                                clearInterval(progressInterval);
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            }
                        }
                    },
                    error: function() {
                        clearInterval(progressInterval);
                    }
                });
            }, 1000); // Update every second
        },

        /**
         * Initialize issue action buttons
         */
        initIssueActions: function() {
            const self = this;

            $(document).on('click', '.wps-guardian-issue-action a', function(e) {
                e.preventDefault();
                const action = $(this).data('action');
                const issueId = $(this).data('issue-id');
                
                if (action) {
                    self.handleIssueAction(action, issueId, $(this));
                }
            });
        },

        /**
         * Handle issue-specific actions (fix, ignore, detail)
         */
        handleIssueAction: function(action, issueId, link) {
            const self = this;
            const card = link.closest('.wps-guardian-issue-card');

            if (action === 'detail' || action === 'view') {
                // Navigate to detail page
                window.location.href = link.attr('href');
                return;
            }

            link.prop('disabled', true);
            const originalText = link.text();

            $.ajax({
                url: wpshadowGuardian.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wps_guardian_issue_' + action,
                    issue_id: issueId,
                    nonce: wpshadowGuardian.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPShadowAdmin.showNotice('success', response.message);
                        
                        if (action === 'ignore') {
                            // Fade out the card
                            card.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else if (action === 'fix') {
                            // Show fixing indicator
                            card.css('opacity', '0.6');
                            link.text(wpshadowGuardian.i18n.fixing || 'Fixing...');
                            
                            // Poll for fix completion
                            self.pollIssueStatus(issueId, card, link, originalText);
                        }
                    } else {
                        WPShadowAdmin.showNotice('error', response.message);
                    }
                },
                error: function() {
                    WPShadowAdmin.showNotice('error', wpshadowGuardian.i18n.error);
                },
                complete: function() {
                    link.prop('disabled', false);
                }
            });
        },

        /**
         * Poll issue status after attempting fix
         */
        pollIssueStatus: function(issueId, card, link, originalText) {
            const self = this;
            const pollInterval = setInterval(() => {
                $.ajax({
                    url: wpshadowGuardian.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wps_guardian_issue_status',
                        issue_id: issueId,
                        nonce: wpshadowGuardian.nonce
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const status = response.data.status;
                            
                            if (status === 'fixed') {
                                clearInterval(pollInterval);
                                card.fadeOut(300, function() {
                                    $(this).remove();
                                });
                                WPShadowAdmin.showNotice('success', wpshadowGuardian.i18n.issuefixed || 'Issue fixed!');
                            } else if (status === 'failed') {
                                clearInterval(pollInterval);
                                card.css('opacity', '1');
                                link.text(originalText).prop('disabled', false);
                                WPShadowAdmin.showNotice('error', response.data.message || wpshadowGuardian.i18n.fixFailed);
                            }
                        }
                    },
                    error: function() {
                        clearInterval(pollInterval);
                    }
                });
            }, 1000); // Check every second
        },

        /**
         * Auto-refresh Guardian status at intervals
         */
        initAutoRefresh: function() {
            const self = this;
            
            // Only refresh if Guardian dashboard is visible
            if (!$('.wps-guardian-dashboard').length) {
                return;
            }

            // Auto-refresh every 2 minutes by default
            const refreshInterval = wpshadowGuardian.refreshInterval || 120000;
            
            setInterval(() => {
                $.ajax({
                    url: wpshadowGuardian.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wps_guardian_refresh_status',
                        nonce: wpshadowGuardian.nonce
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data.issues_count !== undefined) {
                            // Update issue counts in badges
                            $('.wps-guardian-issue-count').each(function() {
                                const issueType = $(this).data('issue-type');
                                if (response.data[issueType]) {
                                    $(this).text(response.data[issueType]);
                                }
                            });
                            
                            // Update overall status
                            if (response.data.status) {
                                $('.wps-guardian-message-text').text(response.data.status);
                            }
                        }
                    }
                });
            }, refreshInterval);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        if ($('.wps-guardian-dashboard').length) {
            WPShadowGuardian.init();
            window.WPShadowGuardian = WPShadowGuardian;
        }
    });

})(jQuery);
