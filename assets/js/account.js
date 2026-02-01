/**
 * WPShadow Account Management
 *
 * JavaScript for unified account registration and management UI.
 *
 * @package WPShadow
 * @since   1.6032.0000
 */

(function($) {
    'use strict';

    const WPShadowAccount = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            
            if (wpShadowAccount.is_registered) {
                this.refreshStatus();
            }
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Registration form
            $('#wpshadow-account-register-form').on('submit', this.handleRegister.bind(this));
            
            // Connect form
            $('#wpshadow-account-connect-form').on('submit', this.handleConnect.bind(this));
            
            // Disconnect button
            $('#wpshadow-disconnect-account').on('click', this.handleDisconnect.bind(this));
            
            // Sync services button
            $('#wpshadow-sync-services').on('click', this.handleSyncServices.bind(this));
        },

        /**
         * Handle registration
         */
        handleRegister: function(e) {
            e.preventDefault();
            
            const $form = $(e.currentTarget);
            const $btn = $form.find('button[type="submit"]');
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text(wpShadowAccount.i18n.registering);
            
            $.ajax({
                url: wpShadowAccount.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpshadow_account_register',
                    nonce: wpShadowAccount.nonces.register,
                    email: $form.find('input[name="email"]').val(),
                    password: $form.find('input[name="password"]').val()
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('success', response.data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        this.showNotice('error', response.data);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: () => {
                    this.showNotice('error', 'Registration failed. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle connect
         */
        handleConnect: function(e) {
            e.preventDefault();
            
            const $form = $(e.currentTarget);
            const $btn = $form.find('button[type="submit"]');
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text(wpShadowAccount.i18n.connecting);
            
            $.ajax({
                url: wpShadowAccount.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpshadow_account_connect',
                    nonce: wpShadowAccount.nonces.connect,
                    api_key: $form.find('input[name="api_key"]').val()
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('success', response.data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        this.showNotice('error', response.data);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: () => {
                    this.showNotice('error', 'Connection failed. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle disconnect
         */
        handleDisconnect: function(e) {
            e.preventDefault();
            
            if (!confirm(wpShadowAccount.i18n.confirm_disconnect)) {
                return;
            }
            
            const $btn = $(e.currentTarget);
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text(wpShadowAccount.i18n.disconnecting);
            
            $.ajax({
                url: wpShadowAccount.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpshadow_account_disconnect',
                    nonce: wpShadowAccount.nonces.disconnect
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('success', response.data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        this.showNotice('error', response.data);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: () => {
                    this.showNotice('error', 'Disconnection failed. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle sync services
         */
        handleSyncServices: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text(wpShadowAccount.i18n.syncing);
            
            $.ajax({
                url: wpShadowAccount.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpshadow_account_sync_services',
                    nonce: wpShadowAccount.nonces.sync
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('success', response.data.message);
                        this.refreshStatus();
                    } else {
                        this.showNotice('error', response.data);
                    }
                    $btn.prop('disabled', false).text(originalText);
                },
                error: () => {
                    this.showNotice('error', 'Sync failed. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Refresh account status
         */
        refreshStatus: function() {
            $.ajax({
                url: wpShadowAccount.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpshadow_account_status',
                    nonce: wpShadowAccount.nonces.status
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStatusDisplay(response.data);
                    }
                }
            });
        },

        /**
         * Update status display
         */
        updateStatusDisplay: function(data) {
            // Update service usage bars
            if (data.services) {
                // Guardian tokens
                if (data.services.guardian) {
                    this.updateUsageBar('guardian', 
                        data.services.guardian.tokens_current || 0,
                        data.services.guardian.tokens_per_month || 100
                    );
                }
                
                // Vault storage
                if (data.services.vault) {
                    this.updateUsageBar('vault',
                        data.services.vault.storage_used || 0,
                        data.services.vault.storage_limit || 1
                    );
                }
            }
        },

        /**
         * Update usage bar
         */
        updateUsageBar: function(service, current, max) {
            const percentage = Math.min(100, (current / Math.max(max, 1)) * 100);
            $(`.wpshadow-service-card.${service} .usage-fill`).css('width', percentage + '%');
        },

        /**
         * Show notice
         */
        showNotice: function(type, message) {
            const $notices = $('#wpshadow-account-notices');
            const $notice = $('<div>')
                .addClass('notice notice-' + type + ' is-dismissible')
                .html('<p>' + message + '</p>');
            
            $notices.html($notice);
            
            // Scroll to notice
            $('html, body').animate({
                scrollTop: $notices.offset().top - 50
            }, 500);
            
            // Auto-dismiss success notices
            if (type === 'success') {
                setTimeout(() => {
                    $notice.fadeOut();
                }, 5000);
            }
            
            // Handle dismiss button
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut();
            });
        }
    };

    // Initialize when ready
    $(document).ready(function() {
        WPShadowAccount.init();
    });

})(jQuery);
