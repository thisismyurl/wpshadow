/**
 * WPShadow Settings Page JavaScript
 *
 * Handles:
 * - Auto-save of general settings (toggles, selects) via wpshadow_save_setting
 * - Scan config saves via wpshadow_save_scan_config
 * - Diagnostic toggle via wpshadow_toggle_diagnostic
 * - Diagnostic frequency override via wpshadow_save_diagnostic_frequency
 * - Diagnostics table filtering and searching
 *
 * @package WPShadow
 * @since   0.6093.1200
 */
/* global wpshadowSettingsData, jQuery */

( function ( $ ) {
	'use strict';

	var WPSSettings = {

		/**
		 * Pending debounce timers keyed by element ID.
		 */
		timers: {},

		/**
		 * Boot the settings page.
		 */
		init: function () {
			this.bindAutoSave();
			this.bindScanConfig();
			this.bindDiagnosticToggles();
			this.bindDiagnosticFrequency();
			this.bindDiagnosticFilters();
			this.syncScanTimeVisibility();
		},

		getErrorMessage: function ( response ) {
			if ( response && response.data && response.data.message ) {
				return response.data.message;
			}

			return wpshadowSettingsData.i18n.saveError;
		},

		sendRequest: function ( data, onSuccess, onError ) {
			$.ajax( {
				url:  wpshadowSettingsData.ajaxUrl,
				type: 'POST',
				data: data,
				success: function ( response ) {
					if ( response && response.success ) {
						if ( 'function' === typeof onSuccess ) {
							onSuccess( response );
						}
						return;
					}

					if ( 'function' === typeof onError ) {
						onError( WPSSettings.getErrorMessage( response ), response );
					}
				},
				error: function () {
					if ( 'function' === typeof onError ) {
						onError( wpshadowSettingsData.i18n.saveError );
					}
				}
			} );
		},

		// ────────────────────────────────────────────────────────────────────
		// Auto-save: generic wpshadow_* options
		// ────────────────────────────────────────────────────────────────────

		bindAutoSave: function () {
			var self = this;
			$( document ).on( 'change', '.wps-auto-save', function () {
				var $el = $( this );
				self.saveOption( $el );
			} );
		},

		saveOption: function ( $el ) {
			var option = $el.data( 'option' );
			var type   = $el.data( 'type' ) || 'string';
			var value;

			if ( 'bool' === type ) {
				value = $el.is( ':checked' ) ? '1' : '0';
			} else if ( 'integer' === type ) {
				value = parseInt( $el.val(), 10 );
			} else {
				value = $el.val();
			}

			var $status = $el.closest( '.wps-settings-row-control' ).find( '.wps-save-status' );
			this.setSaving( $status );

			this.sendRequest(
				{
					action: 'wpshadow_save_setting',
					nonce:  wpshadowSettingsData.adminNonce,
					option: option,
					value:  value
				},
				function () {
					WPSSettings.setSaved( $status );
				},
				function ( msg ) {
					WPSSettings.setError( $status, msg );
				}
			);
		},

		// ────────────────────────────────────────────────────────────────────
		// Scan config saves
		// ────────────────────────────────────────────────────────────────────

		bindScanConfig: function () {
			var self = this;

			$( document ).on( 'change', '.wps-save-scan-config', function () {
				var $el  = $( this );
				var type = $el.data( 'type' ) || 'string';
				var key  = $el.data( 'key' );
				var value;

				if ( 'bool' === type ) {
					value = $el.is( ':checked' ) ? '1' : '0';
				} else {
					value = $el.val();
				}

				var $status = $el.closest( '.wps-settings-row-control' ).find( '.wps-save-status' );
				self.setSaving( $status );

				this.sendRequest(
					{
						action: 'wpshadow_save_scan_config',
						nonce:  wpshadowSettingsData.adminNonce,
						key:    key,
						value:  value
					},
					function () {
						WPSSettings.setSaved( $status );

						// Show/hide the scan-time row when frequency changes.
						if ( 'frequency' === key ) {
							WPSSettings.syncScanTimeVisibility();
						}
					},
					function ( msg ) {
						WPSSettings.setError( $status, msg );
					}
				);
			} );
		},

		syncScanTimeVisibility: function () {
			var $freqSelect = $( '#wps-scan-frequency' );
			var $timeRow    = $( '#wps-scan-time-row' );

			if ( ! $freqSelect.length || ! $timeRow.length ) {
				return;
			}

			if ( 'manual' === $freqSelect.val() ) {
				$timeRow.hide();
			} else {
				$timeRow.show();
			}
		},

		// ────────────────────────────────────────────────────────────────────
		// Diagnostic enable/disable toggles
		// ────────────────────────────────────────────────────────────────────

		bindDiagnosticToggles: function () {
			$( document ).on( 'change', '.wps-diag-toggle', function () {
				var $cb        = $( this );
					var className  = $cb.data( 'class-name' );
				var enable     = $cb.is( ':checked' );
				var $td        = $cb.closest( 'td' );
				var $status    = $td.find( '.wps-save-status' );
				var $row       = $cb.closest( 'tr' );

				WPSSettings.setSaving( $status );

				WPSSettings.sendRequest(
					{
						action:     'wpshadow_toggle_diagnostic',
						nonce:      wpshadowSettingsData.scanSettingsNonce,
						class_name: className,
						enable:     enable ? '1' : '0'
					},
					function () {
						WPSSettings.setSaved( $status );
						if ( enable ) {
							$row.removeClass( 'wps-diag-row--disabled' );
							$row.attr( 'data-enabled', 'enabled' );
						} else {
							$row.addClass( 'wps-diag-row--disabled' );
							$row.attr( 'data-enabled', 'disabled' );
						}
					},
					function ( msg ) {
						$cb.prop( 'checked', ! enable );
						WPSSettings.setError( $status, msg );
					}
				);
			} );
		},

		// ────────────────────────────────────────────────────────────────────
		// Diagnostic frequency overrides
		// ────────────────────────────────────────────────────────────────────

		bindDiagnosticFrequency: function () {
			$( document ).on( 'change', '.wps-diag-freq-select', function () {
				var $sel      = $( this );
					var className = $sel.data( 'class-name' );
				var frequency = $sel.val();
				var $status   = $sel.closest( 'td' ).find( '.wps-save-status' );

				WPSSettings.setSaving( $status );

				WPSSettings.sendRequest(
					{
						action:     'wpshadow_save_diagnostic_frequency',
						nonce:      wpshadowSettingsData.scanSettingsNonce,
						class_name: className,
						frequency:  frequency
					},
					function () {
						WPSSettings.setSaved( $status );
					},
					function ( msg ) {
						WPSSettings.setError( $status, msg );
					}
				);
			} );
		},

		// ────────────────────────────────────────────────────────────────────
		// Diagnostics table filtering
		// ────────────────────────────────────────────────────────────────────

		bindDiagnosticFilters: function () {
			var self = this;

			$( '#wps-diag-search' ).on( 'input', function () {
				clearTimeout( self.timers.search );
				self.timers.search = setTimeout( function () {
					WPSSettings.filterDiagnostics();
				}, 200 );
			} );

			$( '#wps-diag-family-filter, #wps-diag-status-filter' ).on( 'change', function () {
				WPSSettings.filterDiagnostics();
			} );
		},

		filterDiagnostics: function () {
			var search   = ( $( '#wps-diag-search' ).val() || '' ).toLowerCase().trim();
			var family   = $( '#wps-diag-family-filter' ).val() || '';
			var status   = $( '#wps-diag-status-filter' ).val() || '';
			var $rows    = $( '#wps-diagnostics-table tbody .wps-diag-row' );
			var visible  = 0;

			$rows.each( function () {
				var $row       = $( this );
				var rowFamily  = $row.data( 'family' ) || '';
				var rowStatus  = $row.data( 'enabled' ) || '';
				var rowText    = $row.text().toLowerCase();

				var matchSearch = ! search  || rowText.indexOf( search )  > -1;
				var matchFamily = ! family  || rowFamily === family;
				var matchStatus = ! status  || rowStatus === status;

				if ( matchSearch && matchFamily && matchStatus ) {
					$row.show();
					visible++;
				} else {
					$row.hide();
				}
			} );

			$( '#wps-diag-no-results' ).prop( 'hidden', visible > 0 );
		},

		// ────────────────────────────────────────────────────────────────────
		// Status helpers
		// ────────────────────────────────────────────────────────────────────

		setSaving: function ( $status ) {
			$status
				.removeClass( 'is-saved is-error' )
				.addClass( 'is-saving' )
				.text( wpshadowSettingsData.i18n.saving );
		},

		setSaved: function ( $status ) {
			$status
				.removeClass( 'is-saving is-error' )
				.addClass( 'is-saved' )
				.text( wpshadowSettingsData.i18n.saved );

			clearTimeout( $status.data( 'clearTimer' ) );
			$status.data(
				'clearTimer',
				setTimeout( function () {
					$status.removeClass( 'is-saved' ).text( '' );
				}, 2500 )
			);
		},

		setError: function ( $status, message ) {
			$status
				.removeClass( 'is-saving is-saved' )
				.addClass( 'is-error' )
				.text( message || wpshadowSettingsData.i18n.saveError );
		}
	};

	$( function () {
		WPSSettings.init();
	} );

} )( jQuery );
