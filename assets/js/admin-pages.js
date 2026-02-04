/**
 * WPShadow Admin Scripts
 * Common JavaScript functionality for all admin pages
 * Consolidates inline scripts from various PHP modules
 *
 * @package WPShadow
 */

(function($) {
    'use strict';

    /**
     * WPShadow Admin Module
     */
    const WPShadowAdmin = {

        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initModals();
            this.initFormHandlers();
            this.initAjaxHandlers();
            this.initToggles();
            this.initToolLinks();
            this.initProgressOverlay();
            this.initGlobalAjaxProgress();
        },

        /**
         * Modal Management
         */
        initModals: function() {
            const self = this;

            // Open modal handler
            $(document).on('click', '[data-modal-trigger]', function(e) {
                e.preventDefault();
                const modalId = $(this).data('modal-trigger');
                self.openModal(modalId);
            });

            // Close modal button
            $(document).on('click', '.wps-modal-close', function(e) {
                e.preventDefault();
                const modal = $(this).closest('.wps-modal');
                self.closeModal(modal);
            });

            // Close modal on background click
            $(document).on('click', '.wps-modal', function(e) {
                if ($(e.target).hasClass('wps-modal')) {
                    self.closeModal($(this));
                }
            });

            // Close modal on Escape key
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) {
                    self.closeModal($('.wps-modal.active'));
                }
            });
        },

        /**
         * Open a modal by ID
         */
        openModal: function(modalId) {
            const modal = $('#' + modalId);
            if (modal.length) {
                modal.addClass('active');
                $('body').css('overflow', 'hidden');
            }
        },

        /**
         * Close a modal
         */
        closeModal: function(modal) {
            if (modal && modal.length) {
                modal.removeClass('active');
                $('body').css('overflow', '');
            }
        },

        /**
         * Form Handlers
         */
        initFormHandlers: function() {
            const self = this;

            // Generic form submit with AJAX
            $(document).on('submit', '[data-ajax-form]', function(e) {
                e.preventDefault();
                self.handleFormSubmit($(this));
            });

            // Inline edit handlers
            $(document).on('click', '[data-edit-field]', function(e) {
                e.preventDefault();
                self.enableFieldEdit($(this));
            });
        },

        /**
         * Handle form submission with AJAX
         */
        handleFormSubmit: function(form) {
            const self = this;
            const formData = new FormData(form[0]);
            const button = form.find('button[type="submit"]');
            const originalText = button.text();

            // Show loading state
            button.prop('disabled', true).text(wpshadowAdmin.i18n.saving || 'Saving...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message || wpshadowAdmin.i18n.saved);
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    } else {
                        self.showNotice('error', response.message || wpshadowAdmin.i18n.error);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                    console.error('AJAX error:', error);
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Enable inline field editing
         */
        enableFieldEdit: function(trigger) {
            const fieldName = trigger.data('edit-field');
            const currentValue = trigger.data('value') || trigger.text();
            const input = $('<input type="text" value="' + currentValue + '" class="wps-inline-edit" />');

            trigger.replaceWith(input);
            input.focus().select();

            const saveEdit = (newValue) => {
                if (newValue && newValue !== currentValue) {
                    this.sendFieldUpdate(fieldName, newValue);
                } else {
                    input.replaceWith(trigger);
                }
            };

            input.on('blur', function() {
                saveEdit($(this).val());
            }).on('keydown', function(e) {
                if (e.keyCode === 13) { // Enter
                    saveEdit($(this).val());
                } else if (e.keyCode === 27) { // Escape
                    input.replaceWith(trigger);
                }
            });
        },

        /**
         * Send field update via AJAX
         */
        sendFieldUpdate: function(fieldName, newValue) {
            const self = this;

            $.ajax({
                url: wpshadowAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wps_update_field',
                    field: fieldName,
                    value: newValue,
                    nonce: wpshadowAdmin.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message);
                    } else {
                        self.showNotice('error', response.message);
                    }
                },
                error: function() {
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                }
            });
        },

        /**
         * AJAX Handlers
         */
        initAjaxHandlers: function() {
            const self = this;

            // Generic AJAX action buttons
            $(document).on('click', '[data-action]', function(e) {
                e.preventDefault();
                const action = $(this).data('action');
                const confirm = $(this).data('confirm');

                const triggerAction = function() {
                    self.performAction(action, $(this));
                }.bind(this);

                if (confirm && window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
                    window.WPShadowDesign.confirm(confirm, triggerAction);
                    return;
                }

                if (confirm) {
                    WPShadowModal.confirm({
                        title: 'Confirm',
                        message: confirm,
                        onConfirm: triggerAction,
                        onCancel: function() {
                            return;
                        }
                    });
                    return;
                }

                triggerAction();
            });
        },

        /**
         * Perform AJAX action
         */
        performAction: function(action, button) {
            const self = this;
            const originalText = button.text();

            button.prop('disabled', true);

            $.ajax({
                url: wpshadowAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: action,
                    nonce: wpshadowAdmin.nonce,
                    id: button.data('id') || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message);
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else if (button.data('refresh')) {
                            location.reload();
                        }
                    } else {
                        self.showNotice('error', response.message);
                    }
                },
                error: function() {
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Toggle Handlers
         */
        initToggles: function() {
            const self = this;

            // Toggle switch handler
            $(document).on('change', '[data-toggle-action]', function() {
                const action = $(this).data('toggle-action');
                const enabled = $(this).is(':checked');
                const toggleSwitch = $(this).closest('.wps-status-badge');

                self.performToggle(action, enabled, toggleSwitch);
            });
        },

        /**
         * Handle toggle switch state
         */
        performToggle: function(action, enabled, toggleElement) {
            const self = this;

            $.ajax({
                url: wpshadowAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: action,
                    enabled: enabled ? 1 : 0,
                    nonce: wpshadowAdmin.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message);
                    } else {
                        // Revert toggle on error
                        toggleElement.find('input[type="checkbox"]').prop('checked', !enabled);
                        self.showNotice('error', response.message);
                    }
                },
                error: function() {
                    // Revert toggle on error
                    toggleElement.find('input[type="checkbox"]').prop('checked', !enabled);
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                }
            });
        },

        /**
         * Tool/Help Link Handlers
         */
        initToolLinks: function() {
            $(document).on('click', '.wps-tool-link', function(e) {
                const href = $(this).attr('href');
                if (href === '#') {
                    e.preventDefault();
                }
            });

            $(document).on('click', '.wps-card[data-utility-url]', function(e) {
                if ($(e.target).closest('a, button, input, select, textarea, label').length) {
                    return;
                }

                const url = $(this).data('utility-url');
                if (url) {
                    window.location.href = url;
                }
            });

            $(document).on('keydown', '.wps-card[data-utility-url]', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const url = $(this).data('utility-url');
                    if (url) {
                        window.location.href = url;
                    }
                }
            });
        },

        /**
         * Progress Overlay
         */
        initProgressOverlay: function() {
            if ($('#wpshadow-progress-overlay').length) {
                return;
            }

            const titleText = (wpshadowAdmin.i18n && wpshadowAdmin.i18n.working) ? wpshadowAdmin.i18n.working : 'Working on it...';
            const detailText = (wpshadowAdmin.i18n && wpshadowAdmin.i18n.workingDetails) ? wpshadowAdmin.i18n.workingDetails : 'This can take a few minutes.';
            const cancelText = (wpshadowAdmin.i18n && wpshadowAdmin.i18n.cancel) ? wpshadowAdmin.i18n.cancel : 'Cancel';

            const overlay = $(
                '<div id="wpshadow-progress-overlay" class="wps-scan-overlay" aria-busy="false" role="status" aria-live="polite">' +
                    '<div class="wps-scan-overlay-content">' +
                        '<h2 class="wps-progress-title">' + titleText + '</h2>' +
                        '<div class="wps-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
                            '<div class="wps-progress-fill is-indeterminate"></div>' +
                        '</div>' +
                        '<div class="wps-progress-text">' + titleText + '</div>' +
                        '<div class="wps-progress-details">' + detailText + '</div>' +
                        '<button type="button" class="wps-btn-primary wps-progress-cancel" style="display:none;">' + cancelText + '</button>' +
                    '</div>' +
                '</div>'
            );

            $('body').append(overlay);
        },

        showProgress: function(options) {
            const overlay = $('#wpshadow-progress-overlay');
            if (!overlay.length) {
                return;
            }

            const titleText = options && options.title ? options.title : (wpshadowAdmin.i18n && wpshadowAdmin.i18n.working ? wpshadowAdmin.i18n.working : 'Working on it...');
            const detailText = options && options.details ? options.details : (wpshadowAdmin.i18n && wpshadowAdmin.i18n.workingDetails ? wpshadowAdmin.i18n.workingDetails : 'This can take a few minutes.');

            overlay.find('.wps-progress-title').text(titleText);
            overlay.find('.wps-progress-text').text(titleText);
            overlay.find('.wps-progress-details').text(detailText);

            const progressFill = overlay.find('.wps-progress-fill');
            progressFill.addClass('is-indeterminate').css('width', '30%');
            overlay.find('.wps-progress-bar').attr('aria-valuenow', '0');

            const cancelButton = overlay.find('.wps-progress-cancel');
            if (options && typeof options.onCancel === 'function') {
                cancelButton.show().off('click').on('click', function(e) {
                    e.preventDefault();
                    options.onCancel();
                });
            } else {
                cancelButton.hide().off('click');
            }

            overlay.attr('aria-busy', 'true');
        },

        updateProgress: function(percent, message, details) {
            const overlay = $('#wpshadow-progress-overlay');
            if (!overlay.length) {
                return;
            }

            const progressFill = overlay.find('.wps-progress-fill');
            progressFill.removeClass('is-indeterminate').css('width', percent + '%');
            overlay.find('.wps-progress-bar').attr('aria-valuenow', percent);

            if (message) {
                overlay.find('.wps-progress-text').text(message);
            }

            if (details) {
                overlay.find('.wps-progress-details').text(details);
            }
        },

        hideProgress: function() {
            const overlay = $('#wpshadow-progress-overlay');
            if (!overlay.length) {
                return;
            }

            overlay.attr('aria-busy', 'false');
            overlay.find('.wps-progress-cancel').hide().off('click');
        },

        initGlobalAjaxProgress: function() {
            const self = this;
            this.longAjaxCount = 0;
            this.longAjaxTimer = null;

            $(document).ajaxSend(function(event, xhr, settings) {
                const action = self.getAjaxAction(settings);
                if (!action || !self.isLongOperation(action)) {
                    return;
                }

                self.longAjaxCount++;
                if (self.longAjaxTimer) {
                    clearTimeout(self.longAjaxTimer);
                }

                self.longAjaxTimer = setTimeout(function() {
                    if (self.longAjaxCount > 0) {
                        self.showProgress(self.getLongOperationMessage(action));
                    }
                }, 800);
            });

            $(document).ajaxComplete(function(event, xhr, settings) {
                const action = self.getAjaxAction(settings);
                if (!action || !self.isLongOperation(action)) {
                    return;
                }

                self.longAjaxCount = Math.max(0, self.longAjaxCount - 1);
                if (self.longAjaxCount === 0) {
                    if (self.longAjaxTimer) {
                        clearTimeout(self.longAjaxTimer);
                        self.longAjaxTimer = null;
                    }
                    self.hideProgress();
                }
            });
        },

        getAjaxAction: function(settings) {
            if (!settings || !settings.data) {
                return '';
            }

            if (typeof settings.data.get === 'function') {
                return settings.data.get('action') || '';
            }

            if (typeof settings.data === 'string') {
                try {
                    const params = new URLSearchParams(settings.data);
                    return params.get('action') || '';
                } catch (e) {
                    return '';
                }
            }

            if (typeof settings.data === 'object' && settings.data.action) {
                return settings.data.action;
            }

            return '';
        },

        isLongOperation: function(action) {
            const longOperations = this.getLongOperationMessages();
            return Object.prototype.hasOwnProperty.call(longOperations, action);
        },

        getLongOperationMessage: function(action) {
            const longOperations = this.getLongOperationMessages();
            return longOperations[action] || {};
        },

        getLongOperationMessages: function() {
            return {
                wpshadow_vault_create_backup: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.creatingBackup ? wpshadowAdmin.i18n.creatingBackup : 'Creating backup...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.backupDetails ? wpshadowAdmin.i18n.backupDetails : 'This can take a few minutes.'
                },
                wpshadow_vault_restore_backup: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.restoringBackup ? wpshadowAdmin.i18n.restoringBackup : 'Restoring backup...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.restoreDetails ? wpshadowAdmin.i18n.restoreDetails : 'Please keep this tab open while we restore your site.'
                },
                wpshadow_vault_delete_backup: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.deletingBackup ? wpshadowAdmin.i18n.deletingBackup : 'Deleting backup...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.deleteDetails ? wpshadowAdmin.i18n.deleteDetails : 'This should only take a moment.'
                },
                wpshadow_bulk_find_replace: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.findReplaceRunning ? wpshadowAdmin.i18n.findReplaceRunning : 'Running find and replace...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.findReplaceDetails ? wpshadowAdmin.i18n.findReplaceDetails : 'We are updating your content safely.'
                },
                wpshadow_run_family_diagnostics: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.runningDiagnostics ? wpshadowAdmin.i18n.runningDiagnostics : 'Running diagnostics...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.diagnosticsDetails ? wpshadowAdmin.i18n.diagnosticsDetails : 'This can take a few minutes.'
                },
                wpshadow_guardian_scan: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.runningScan ? wpshadowAdmin.i18n.runningScan : 'Running a scan...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.scanDetails ? wpshadowAdmin.i18n.scanDetails : 'We will update you when the scan is done.'
                },
                wpshadow_generate_dna: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.generatingDna ? wpshadowAdmin.i18n.generatingDna : 'Generating site DNA...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.dnaDetails ? wpshadowAdmin.i18n.dnaDetails : 'We are gathering site details.'
                },
                wpshadow_download_report: {
                    title: wpshadowAdmin.i18n && wpshadowAdmin.i18n.preparingReport ? wpshadowAdmin.i18n.preparingReport : 'Preparing report...',
                    details: wpshadowAdmin.i18n && wpshadowAdmin.i18n.reportDetails ? wpshadowAdmin.i18n.reportDetails : 'This can take a few minutes.'
                }
            };
        },

        /**
         * Notification Management
         */
        showNotice: function(type, message) {
            const noticeClass = 'notice notice-' + type;
            const notice = $('<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');

            $('.wp-header-end').before(notice);

            // Auto-dismiss success notices after 3 seconds
            if (type === 'success') {
                setTimeout(() => {
                    notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            // Dismiss button handler
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Spinner utilities
         */
        showSpinner: function(container) {
            const spinner = $('<div class="wps-spinner dashicons dashicons-update"></div>');
            $(container).append(spinner);
            return spinner;
        },

        removeSpinner: function(spinner) {
            $(spinner).remove();
        },

        /**
         * Helper: Format date
         */
        formatDate: function(date) {
            if (typeof date === 'string') {
                date = new Date(date);
            }
            return date.toLocaleDateString(wpshadowAdmin.locale || 'en-US');
        },

        /**
         * Helper: Format number
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        WPShadowAdmin.init();
        // Make available globally for other scripts
        window.WPShadowAdmin = WPShadowAdmin;
    });

})(jQuery);
