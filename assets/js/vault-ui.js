/**
 * WPShadow Vault UI JavaScript
 *
 * Handles AJAX interactions for backup creation, deletion, and restoration.
 *
 * @package WPShadow
 * @since   1.6030.1845
 */

(function($) {
    'use strict';

    const VaultUI = {
        /**
         * Initialize Vault UI
         */
        init: function() {
            this.bindEvents();
            this.initRegistrationFlow();
        },

        /**
         * Bind UI events
         */
        bindEvents: function() {
            // Create backup
            $('#wpshadow-vault-create-backup-btn').on('click', this.handleCreateBackup.bind(this));

            // Delete backup
            $(document).on('click', '.wpshadow-vault-delete-btn', this.handleDeleteBackup.bind(this));

            // Restore backup
            $(document).on('click', '.wpshadow-vault-restore-btn', this.handleRestoreBackup.bind(this));

            // Show registration form
            $('#wpshadow-vault-register-btn').on('click', function(e) {
                e.preventDefault();
                $('.wpshadow-vault-prompt').hide();
                $('.wpshadow-vault-register-form').slideDown();
            });

            // Register form submit
            $('#wpshadow-vault-register-form').on('submit', this.handleRegister.bind(this));

            // Connect form submit
            $('#wpshadow-vault-connect-form').on('submit', this.handleConnect.bind(this));
        },

        /**
         * Initialize registration flow
         */
        initRegistrationFlow: function() {
            // Handle registration links throughout UI
            $(document).on('click', '.wpshadow-vault-register-link', function(e) {
                e.preventDefault();
                window.location.href = wpShadowVault.vault_page_url || 'admin.php?page=wpshadow-vault';
            });
        },

        /**
         * Handle create backup
         */
        handleCreateBackup: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const originalText = $btn.text();

            // Prompt for backup label
			window.WPShadowModal.prompt({
				title: 'Create Backup',
				message: 'Enter a label for this backup (optional):',
				placeholder: 'Backup label',
				defaultValue: 'Manual Backup - ' + new Date().toLocaleDateString(),
				submitText: 'Create',
				cancelText: 'Cancel',
				onSubmit: function(label) {
					$btn.prop('disabled', true).text(wpShadowVault.strings.creating);
					VaultUI.createBackupWithLabel(label, $btn, originalText);
				}
			});
		},

		/**
		 * Create backup with label
		 */
		createBackupWithLabel: function(label, $btn, originalText) {
                data: {
                    action: 'wpshadow_vault_create_backup',
                    nonce: wpShadowVault.nonces.create_backup,
                    label: label
                },
                success: function(response) {
                    if (response.success) {
                        VaultUI.showNotice('success', response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        VaultUI.showNotice('error', response.data);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    VaultUI.showNotice('error', 'Failed to create backup. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle delete backup
         */
        handleDeleteBackup: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const backupId = $btn.data('backup-id');
            const originalText = $btn.text();

            window.WPShadowModal.confirm({
                title: 'Delete Backup',
                message: wpShadowVault.strings.confirm_delete,
                confirmText: 'Delete',
                cancelText: 'Cancel',
                type: 'danger',
                onConfirm: function() {
                    $btn.prop('disabled', true).text(wpShadowVault.strings.deleting);

					$.ajax({
						url: wpShadowVault.ajax_url,
						type: 'POST',
						data: {
							action: 'wpshadow_vault_delete_backup',
							nonce: wpShadowVault.nonces.delete_backup,
							backup_id: backupId
						},
						success: function(response) {
							if (response.success) {
								VaultUI.showNotice('success', response.data.message);
								$btn.closest('tr').fadeOut(300, function() {
									$(this).remove();
								});
							} else {
								VaultUI.showNotice('error', response.data);
								$btn.prop('disabled', false).text(originalText);
							}
						},
						error: function() {
							VaultUI.showNotice('error', 'Failed to delete backup. Please try again.');
							$btn.prop('disabled', false).text(originalText);
						}
					});
                }
            });
		},

        /**
         * Handle restore backup
         */
        handleRestoreBackup: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const backupId = $btn.data('backup-id');
            const originalText = $btn.text();

            window.WPShadowModal.confirm({
                title: 'Restore Backup',
                message: wpShadowVault.strings.confirm_restore,
                confirmText: 'Restore',
                cancelText: 'Cancel',
                type: 'warning',
                onConfirm: function() {
                    $btn.prop('disabled', true).text(wpShadowVault.strings.restoring);

					$.ajax({
						url: wpShadowVault.ajax_url,
						type: 'POST',
						data: {
							action: 'wpshadow_vault_restore_backup',
							nonce: wpShadowVault.nonces.restore_backup,
							backup_id: backupId
						},
						success: function(response) {
							if (response.success) {
								VaultUI.showNotice('success', response.data.message);
								setTimeout(function() {
									location.reload();
								}, 2000);
							} else {
								VaultUI.showNotice('error', response.data);
								$btn.prop('disabled', false).text(originalText);
							}
						},
						error: function() {
							VaultUI.showNotice('error', 'Failed to restore backup. Please try again.');
							$btn.prop('disabled', false).text(originalText);
						}
					});
                }
            });
        },

        /**
         * Handle registration
         */
        handleRegister: function(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $btn = $form.find('button[type="submit"]');
            const originalText = $btn.text();

            $btn.prop('disabled', true).text('Registering...');

            $.ajax({
                url: wpShadowVault.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpshadow_vault_register',
                    nonce: wpShadowVault.nonces.register,
                    email: $form.find('input[name="email"]').val(),
                    password: $form.find('input[name="password"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        VaultUI.showNotice('success', response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        VaultUI.showNotice('error', response.data);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    VaultUI.showNotice('error', 'Registration failed. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle connect with API key
         */
        handleConnect: function(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $btn = $form.find('button[type="submit"]');
            const originalText = $btn.text();

            $btn.prop('disabled', true).text('Connecting...');

            $.ajax({
                url: wpShadowVault.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpshadow_vault_connect',
                    nonce: wpShadowVault.nonces.connect,
                    api_key: $form.find('input[name="api_key"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        VaultUI.showNotice('success', response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        VaultUI.showNotice('error', response.data);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    VaultUI.showNotice('error', 'Connection failed. Please check your API key.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Show admin notice
         */
        showNotice: function(type, message) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + this.escapeHtml(message) + '</p></div>');

            $('.wpshadow-vault-page h1').after($notice);

            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        VaultUI.init();
    });

})(jQuery);
