/**
 * WPShadow Design System - Interactive Components
 * Version: 2.0 (2026 Redesign)
 * 
 * Handles modal dialogs, notifications, and interactive UI components.
 * 
 * @package WPShadow
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        WPShadowDesign.init();
    });

    /**
     * WPShadow Design System Object
     */
    window.WPShadowDesign = {
        
        /**
         * Initialize design system
         */
        init: function() {
            this.initModals();
            this.initNotifications();
            this.initTooltips();
            this.initAnimations();
            this.initRangeSliders();
        },

        /**
         * Modal System
         */
        initModals: function() {
            const self = this;

            // Close modal on backdrop click
            $(document).on('click', '.wps-modal-backdrop', function(e) {
                if (e.target === this) {
                    self.closeModal($(this));
                }
            });

            // Close modal on close button
            $(document).on('click', '.wps-modal-close', function() {
                self.closeModal($(this).closest('.wps-modal-backdrop'));
            });

            // Close modal on ESC key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    const $openModals = $('.wps-modal-backdrop');
                    if ($openModals.length) {
                        self.closeModal($openModals.last());
                    }
                }
            });
        },

        /**
         * Open a modal
         * @param {Object} options - Modal configuration
         */
        openModal: function(options) {
            const defaults = {
                title: 'Modal Title',
                content: '<p>Modal content goes here</p>',
                size: 'medium', // small, medium, large
                onConfirm: null,
                onCancel: null,
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                showCancel: true
            };

            options = $.extend({}, defaults, options);

            const modalHTML = `
                <div class="wps-modal-backdrop" style="display: none;">
                    <div class="wps-modal" data-size="${options.size}">
                        <div class="wps-modal-header">
                            <h3 class="wps-modal-title">${options.title}</h3>
                            <button class="wps-modal-close" aria-label="Close">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </div>
                        <div class="wps-modal-body">
                            ${options.content}
                        </div>
                        <div class="wps-modal-footer">
                            ${options.showCancel ? `<button class="wps-btn wps-btn-secondary wps-modal-cancel">${options.cancelText}</button>` : ''}
                            <button class="wps-btn wps-btn-primary wps-modal-confirm">${options.confirmText}</button>
                        </div>
                    </div>
                </div>
            `;

            const $modal = $(modalHTML);
            $('body').append($modal);

            // Bind confirm action
            if (options.onConfirm) {
                $modal.find('.wps-modal-confirm').on('click', function() {
                    options.onConfirm();
                    WPShadowDesign.closeModal($modal);
                });
            }

            // Bind cancel action
            if (options.onCancel) {
                $modal.find('.wps-modal-cancel').on('click', function() {
                    options.onCancel();
                    WPShadowDesign.closeModal($modal);
                });
            } else {
                $modal.find('.wps-modal-cancel').on('click', function() {
                    WPShadowDesign.closeModal($modal);
                });
            }

            // Show modal with animation
            $modal.fadeIn(200);
            $('body').css('overflow', 'hidden');
        },

        /**
         * Close a modal
         * @param {jQuery} $modal - Modal element to close
         */
        closeModal: function($modal) {
            $modal.fadeOut(200, function() {
                $modal.remove();
                if (!$('.wps-modal-backdrop').length) {
                    $('body').css('overflow', '');
                }
            });
        },

        /**
         * Notification System
         */
        initNotifications: function() {
            // Create notification container if it doesn't exist
            if (!$('#wps-notifications-container').length) {
                $('body').append('<div id="wps-notifications-container" style="position: fixed; top: 32px; right: 20px; z-index: 10000; width: 400px; max-width: 90%;"></div>');
            }
        },

        /**
         * Show a notification
         * @param {String} message - Notification message
         * @param {String} type - Notification type (success, error, warning, info)
         * @param {Number} duration - Auto-dismiss duration in ms (0 = no auto-dismiss)
         */
        notify: function(message, type = 'info', duration = 5000) {
            type = type || 'info';

            const icons = {
                success: 'dashicons-yes-alt',
                error: 'dashicons-dismiss',
                warning: 'dashicons-warning',
                info: 'dashicons-info'
            };

            const icon = icons[type] || icons.info;

            const notificationHTML = `
                <div class="wps-alert wps-alert-${type}" style="margin-bottom: 12px; animation: wps-slide-in-right 0.3s ease-out; cursor: pointer; box-shadow: var(--wps-shadow-lg);">
                    <span class="dashicons ${icon} wps-alert-icon"></span>
                    <div class="wps-alert-content">
                        <p class="wps-alert-message" style="margin: 0;">${message}</p>
                    </div>
                    <button class="wps-modal-close" style="margin-left: auto; background: none; border: none; padding: 4px; cursor: pointer; color: inherit; opacity: 0.6;">
                        <span class="dashicons dashicons-no" style="font-size: 16px;"></span>
                    </button>
                </div>
            `;

            const $notification = $(notificationHTML);
            $('#wps-notifications-container').prepend($notification);

            // Auto-dismiss
            if (duration > 0) {
                setTimeout(function() {
                    WPShadowDesign.dismissNotification($notification);
                }, duration);
            }

            // Manual dismiss
            $notification.find('.wps-modal-close').on('click', function(e) {
                e.stopPropagation();
                WPShadowDesign.dismissNotification($notification);
            });

            // Click to dismiss
            $notification.on('click', function() {
                WPShadowDesign.dismissNotification($notification);
            });
        },

        /**
         * Dismiss a notification
         * @param {jQuery} $notification - Notification element to dismiss
         */
        dismissNotification: function($notification) {
            $notification.fadeOut(200, function() {
                $notification.remove();
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Simple tooltip system using native title attributes
            // Can be enhanced with a library like Tippy.js if needed
            $('[data-wps-tooltip]').each(function() {
                const $el = $(this);
                const tooltipText = $el.data('wps-tooltip');
                
                $el.attr('title', tooltipText);
                
                // Add hover effect
                $el.css({
                    'cursor': 'help',
                    'text-decoration': 'underline',
                    'text-decoration-style': 'dotted'
                });
            });
        },

        /**
         * Initialize animations
         */
        initAnimations: function() {
            // Add CSS animation classes
            const style = document.createElement('style');
            style.textContent = `
                @keyframes wps-slide-in-right {
                    from {
                        opacity: 0;
                        transform: translateX(100px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
            `;
            document.head.appendChild(style);
        },

        /**
         * Confirm dialog helper
         * @param {String} message - Confirmation message
         * @param {Function} onConfirm - Callback on confirm
         * @param {Function} onCancel - Callback on cancel
         */
        confirm: function(message, onConfirm, onCancel) {
            this.openModal({
                title: 'Confirm Action',
                content: `<p>${message}</p>`,
                size: 'small',
                onConfirm: onConfirm,
                onCancel: onCancel,
                confirmText: 'Confirm',
                cancelText: 'Cancel'
            });
        },

        /**
         * Alert dialog helper
         * @param {String} title - Alert title
         * @param {String} message - Alert message
         * @param {String} type - Alert type (success, error, warning, info)
         */
        alert: function(title, message, type = 'info') {
            this.openModal({
                title: title,
                content: `<div class="wps-alert wps-alert-${type}">
                    <div class="wps-alert-content">
                        <p class="wps-alert-message">${message}</p>
                    </div>
                </div>`,
                size: 'small',
                showCancel: false,
                confirmText: 'OK'
            });
        },

        /**
         * Initialize range sliders with live value updates
         */
        initRangeSliders: function() {
            // Handle all range inputs with wps-range class
            document.querySelectorAll('.wps-range').forEach(function(range) {
                const display = document.getElementById(range.id + '_display');
                if (display) {
                    // Get suffix from data attribute or default to empty string
                    const suffix = range.dataset.suffix || '';
                    
                    // Update on input event (live updates)
                    range.addEventListener('input', function(e) {
                        const value = e.target.value;
                        display.textContent = value + suffix;
                        
                        // Update ARIA attributes for accessibility
                        e.target.setAttribute('aria-valuenow', value);
                        e.target.setAttribute('aria-valuetext', value + ' ' + suffix);
                    });
                    
                    // Initialize display value
                    const initialValue = range.value;
                    display.textContent = initialValue + suffix;
                    range.setAttribute('aria-valuenow', initialValue);
                    range.setAttribute('aria-valuetext', initialValue + ' ' + suffix);
                }
            });
        }
    };

})(jQuery);
