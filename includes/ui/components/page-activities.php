<?php
/**
 * Page Activity Display Component
 *
 * Reusable component for displaying page-specific activities with real-time AJAX updates.
 * Displays filtered activities based on current page context (tools, reports, guardian, etc.)
 *
 * @package WPShadow
 * @subpackage Views
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render page-specific activity display
 *
 * @param string $context Page context (tools, reports, guardian, workflows, settings, security, performance)
 * @param int    $limit Maximum activities to display (default: 10)
 * @param string $report_slug Optional report slug for report-specific filtering
 * @return void
 */
function wpshadow_render_page_activities( string $context = '', int $limit = 10, string $report_slug = '' ): void {
	if ( empty( $context ) ) {
		return;
	}

	$report_slug = sanitize_key( $report_slug );

	$nonce = wp_create_nonce( 'wpshadow_get_activities' );
	?>
	<div class="wps-card wps-mt-8">
		<div class="wps-card-header">
			<h3 class="wps-card-title wps-m-0">
				<span class="dashicons dashicons-clock wps-icon-mr-2" aria-hidden="true"></span>
				<?php esc_html_e( 'Recent Activity', 'wpshadow' ); ?>
			</h3>
		</div>
		<div class="wps-card-body">
			<div class="wps-activity-timeline wps-activity-ajax-container" 
				 role="list" 
				 aria-label="<?php esc_attr_e( 'Recent page activity', 'wpshadow' ); ?>"
				 data-context="<?php echo esc_attr( $context ); ?>"
				 data-limit="<?php echo esc_attr( (string) $limit ); ?>"
				 data-nonce="<?php echo esc_attr( $nonce ); ?>"
					 data-refresh-interval="3000"
					 <?php if ( ! empty( $report_slug ) ) : ?>data-report="<?php echo esc_attr( $report_slug ); ?>"<?php endif; ?>>
				<div class="wps-activity-loading" style="text-align: center; padding: 20px;">
					<span class="spinner" style="float: none; margin: 0;"></span>
					<p><?php esc_html_e( 'Loading activity...', 'wpshadow' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<script>
	(function($) {
		'use strict';

		// Activity refresh controller
		const WPShadowActivityUpdater = {
			containers: [],
			refreshTimers: {},

			init: function() {
				this.containers = document.querySelectorAll('.wps-activity-ajax-container');
				this.containers.forEach((container) => this.setupContainer(container));
			},

			setupContainer: function(container) {
				const context = container.getAttribute('data-context');
				const limit = parseInt(container.getAttribute('data-limit')) || 10;
				const interval = parseInt(container.getAttribute('data-refresh-interval')) || 3000;

				// Load initial activities
				this.loadActivities(container, context, limit);

				// Setup auto-refresh
				const timer = setInterval(() => {
					this.loadActivities(container, context, limit);
				}, interval);

				this.refreshTimers[context] = timer;

				// Listen for activity logged events
				document.addEventListener('wpshadow_activity_logged', (e) => {
					// Refresh this container when activity is logged
					this.loadActivities(container, context, limit);
				});
			},

			loadActivities: function(container, context, limit) {
				const nonce = container.getAttribute('data-nonce');
				const report = container.getAttribute('data-report') || '';
				const currentTimestamp = Math.floor(Date.now() / 1000);
				const sinceTimestamp = this.getLastTimestamp(context);

				$.post(ajaxurl, {
					action: 'wpshadow_get_activities',
					nonce: nonce,
					context: context,
					report: report,
					limit: limit,
					offset: 0,
					since: sinceTimestamp
				}, (response) => {
					if (response.success && response.data.activities) {
						if (response.data.activities.length > 0 || sinceTimestamp === 0) {
							this.renderActivities(container, response.data.activities);
							this.setLastTimestamp(context, currentTimestamp);
						}
					}
				});
			},

				renderActivities: function(container, activities) {
					const i18n = window.wpshadow_i18n || {};
				let highlightNew = false;
				if (activities.length > 0) {
					const latestId = activities[0].id || '';
					if (latestId && container.dataset.latestActivityId === latestId) {
						return;
					}
					highlightNew = Boolean(container.dataset.latestActivityId);
					container.dataset.latestActivityId = latestId;
				}

				if (activities.length === 0) {
					container.innerHTML = '<div class="wps-activity-empty"><p>' + 
						this.escapeHtml(i18n.no_activities || 'No activities yet') + '</p></div>';
					return;
				}

				let html = '';
					activities.forEach((activity) => {
					const iconClass = this.getIconClass(activity.action);
					const iconColor = this.getIconColor(activity.action);
						const reportLink = activity.report_url && activity.report_label
							? `<a href="${this.escapeAttr(activity.report_url)}" class="wps-link">${this.escapeHtml(activity.report_label)}</a>`
							: '';
						const detailsLine = reportLink
							? `
								<div class="wps-activity-details">
									${this.escapeHtml(i18n.report_label || 'Report')}: ${reportLink}
								</div>
							`
							: (activity.details ? `<div class="wps-activity-details">${this.escapeHtml(activity.details)}</div>` : '');

					html += `
						<div class="wps-activity-item" role="listitem">
							<div class="wps-activity-icon-wrapper" style="background: ${iconColor}20; border-color: ${iconColor};">
								<span class="dashicons ${iconClass}" style="color: ${iconColor};" aria-hidden="true"></span>
							</div>
							<div class="wps-activity-content">
								<div class="wps-activity-text">${this.escapeHtml(activity.action)}</div>
									${detailsLine}
								<time class="wps-activity-time" datetime="${new Date(activity.timestamp * 1000).toISOString()}">
									${this.escapeHtml(activity.time_ago)} • ${this.escapeHtml(activity.user_name)}
								</time>
							</div>
						</div>
					`;
				});

				container.innerHTML = html;
				if (highlightNew) {
					const firstItem = container.querySelector('.wps-activity-item');
					if (firstItem) {
						firstItem.classList.add('wps-activity-item--new');
						window.setTimeout(() => {
							firstItem.classList.remove('wps-activity-item--new');
						}, 1200);
					}
				}
			},

			getIconClass: function(action) {
				const iconMap = {
					'diagnostic_run': 'dashicons-search',
					'treatment_applied': 'dashicons-admin-tools',
					'finding_fixed': 'dashicons-yes-alt',
					'workflow_executed': 'dashicons-controls-play',
					'guardian_executed': 'dashicons-shield-alt',
					'report_generated': 'dashicons-chart-area',
					'cache_cleared': 'dashicons-update',
					'Settings Updated': 'dashicons-admin-settings',
				};
				return iconMap[action] || 'dashicons-admin-generic';
			},

			getIconColor: function(action) {
				if (action.includes('applied') || action.includes('fixed') || action.includes('completed')) {
					return '#10b981'; // Green
				} else if (action.includes('failed') || action.includes('error')) {
					return '#ef4444'; // Red
				} else if (action.includes('scan') || action.includes('run')) {
					return '#3b82f6'; // Blue
				} else if (action.includes('workflow') || action.includes('executed')) {
					return '#8b5cf6'; // Purple
				}
				return '#6b7280'; // Gray
			},

			getLastTimestamp: function(context) {
				return parseInt(sessionStorage.getItem(`wpshadow_activity_ts_${context}`)) || 0;
			},

			setLastTimestamp: function(context, timestamp) {
				sessionStorage.setItem(`wpshadow_activity_ts_${context}`, timestamp);
			},

			escapeHtml: function(text) {
				const div = document.createElement('div');
				div.textContent = text;
				return div.innerHTML;
			},

				escapeAttr: function(text) {
					return this.escapeHtml(text).replace(/"/g, '&quot;');
				},

			destroy: function() {
				Object.values(this.refreshTimers).forEach((timer) => clearInterval(timer));
				this.refreshTimers = {};
			}
		};

		// Initialize on ready
		$(document).ready(function() {
			WPShadowActivityUpdater.init();
		});

		// Cleanup on page unload
		$(window).on('beforeunload', function() {
			WPShadowActivityUpdater.destroy();
		});

	})(jQuery);
	</script>

	<style>
	.wps-activity-ajax-container .wps-activity-item {
		transition: opacity 0.2s ease-in;
	}

	.wps-activity-item--new {
		background: rgba(34, 113, 177, 0.08);
		box-shadow: inset 0 0 0 1px rgba(34, 113, 177, 0.12);
		border-radius: 6px;
		transition: background 0.6s ease, box-shadow 0.6s ease;
	}

	.wps-activity-details {
		font-size: 13px;
		color: #6b7280;
		margin-top: 4px;
		margin-bottom: 4px;
	}

	.wps-activity-empty {
		text-align: center;
		padding: 40px 20px;
		color: #9ca3af;
	}

	.wps-activity-empty p {
		margin: 0;
	}

	.wps-activity-loading {
		text-align: center;
		padding: 20px;
	}
	</style>
	<?php
}

/**
 * Emit activity logged event (can be called from PHP when activity is logged)
 *
 * @param array $activity Activity entry
 * @return void
 */
function wpshadow_emit_activity_logged_event( array $activity ): void {
	?>
	<script>
	(function() {
		const event = new CustomEvent('wpshadow_activity_logged', {
			detail: <?php echo wp_json_encode( $activity ); ?>
		});
		document.dispatchEvent(event);
	})();
	</script>
	<?php
}

/**
 * Localization data for activity display
 */
function wpshadow_activity_display_localization(): void {
	wp_localize_script( 'jquery', 'wpshadow_i18n', array(
		'no_activities' => __( 'No activities yet', 'wpshadow' ),
		'loading'       => __( 'Loading activity...', 'wpshadow' ),
		'report_label'  => __( 'Report', 'wpshadow' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'wpshadow_activity_display_localization' );
add_action( 'admin_enqueue_scripts', 'wpshadow_activity_display_localization' );
