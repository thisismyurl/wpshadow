(function($) {
	'use strict';

	const WPShadowActivityUpdater = {
		containers: [],
		refreshTimers: {},

		init: function() {
			this.containers = document.querySelectorAll('.wps-activity-ajax-container');
			this.containers.forEach((container) => this.setupContainer(container));
			this.initHeartbeatListener();
		},

		/**
		 * Listen for WordPress heartbeat responses and refresh immediately when
		 * the Guardian executor reports that diagnostics were run.
		 */
		initHeartbeatListener: function() {
			const self = this;
			$(document).on('heartbeat-tick', function(event, data) {
				if (!data || !data.wpshadow_guardian) {
					return;
				}
				const g = data.wpshadow_guardian;
				const ranDiagnostics = g.executed ||
					(Array.isArray(g.diagnostics_run) && g.diagnostics_run.length > 0);
				if (!ranDiagnostics) {
					return;
				}
				// Diagnostics ran — refresh every activity panel immediately
				self.containers.forEach(function(container) {
					const context = container.getAttribute('data-context');
					const limit   = parseInt(container.getAttribute('data-limit'), 10) || 10;
					const page    = parseInt(container.getAttribute('data-current-page'), 10) || 0;
					self.loadActivities(container, context, limit, page * limit, false);

					// Pulse the heartbeat dot in this card's header
					const card = container.closest('.wps-card');
					const dot  = card ? card.querySelector('.wps-heartbeat-dot') : null;
					if (dot) {
						dot.classList.remove('is-pulsing');
						// Force reflow so re-adding the class restarts the animation
						void dot.offsetWidth;
						dot.classList.add('is-pulsing');
						window.setTimeout(function() {
							dot.classList.remove('is-pulsing');
						}, 2800); // 3 pulses × 0.8s each + small buffer
					}
				});
			});
		},

		setupContainer: function(container) {
			const context = container.getAttribute('data-context');
			const limit = parseInt(container.getAttribute('data-limit'), 10) || 10;
			const interval = parseInt(container.getAttribute('data-refresh-interval'), 10) || 3000;
			const paginationDiv = container.parentElement.querySelector('.wps-activity-pagination');
			const prevBtn = paginationDiv ? paginationDiv.querySelector('.wps-activity-pagination-prev') : null;
			const nextBtn = paginationDiv ? paginationDiv.querySelector('.wps-activity-pagination-next') : null;

			this.loadActivities(container, context, limit, 0, true);

			if (prevBtn) {
				prevBtn.addEventListener('click', (e) => {
					e.preventDefault();
					const currentPage = parseInt(container.getAttribute('data-current-page'), 10) || 0;
					if (currentPage > 0) {
						this.loadActivities(container, context, limit, (currentPage - 1) * limit, true);
					}
				});
			}

			if (nextBtn) {
				nextBtn.addEventListener('click', (e) => {
					e.preventDefault();
					const currentPage = parseInt(container.getAttribute('data-current-page'), 10) || 0;
					this.loadActivities(container, context, limit, (currentPage + 1) * limit, true);
				});
			}

			const timer = setInterval(() => {
				const currentPage = parseInt(container.getAttribute('data-current-page'), 10) || 0;
				this.loadActivities(container, context, limit, currentPage * limit, false);
			}, interval);

			this.refreshTimers[context] = timer;

			document.addEventListener('wpshadow_activity_logged', () => {
				const currentPage = parseInt(container.getAttribute('data-current-page'), 10) || 0;
				this.loadActivities(container, context, limit, currentPage * limit, false);
			});

			document.addEventListener('wpshadow_dashboard_refresh', () => {
				const currentPage = parseInt(container.getAttribute('data-current-page'), 10) || 0;
				this.loadActivities(container, context, limit, currentPage * limit, false);
			});
		},

		loadActivities: function(container, context, limit, offset = 0, forceFull = false) {
			const nonce = container.getAttribute('data-nonce');
			const report = container.getAttribute('data-report') || '';
			const currentTimestamp = Math.floor(Date.now() / 1000);
			const sinceTimestamp = forceFull ? 0 : this.getLastTimestamp(context);

			$.post(ajaxurl, {
				action: 'wpshadow_get_activities',
				nonce: nonce,
				context: context,
				report: report,
				limit: limit,
				offset: offset,
				since: sinceTimestamp
			}, (response) => {
				if (response.success && response.data.activities) {
					if (response.data.activities.length > 0 || sinceTimestamp === 0 || forceFull) {
						this.renderActivities(container, response.data.activities, response.data);
						container.dataset.wpsActivityLoaded = '1';
						this.setLastTimestamp(context, currentTimestamp);
					} else if (!container.dataset.wpsActivityLoaded) {
						// First paint safety: replace loading state even when no newer entries exist.
						this.loadActivities(container, context, limit, offset, true);
					}
				} else if (!container.dataset.wpsActivityLoaded) {
					container.innerHTML = '<div class="wps-activity-empty"><p>' + this.escapeHtml((window.wpshadow_i18n && window.wpshadow_i18n.no_activities) || 'No activities yet') + '</p></div>';
					container.dataset.wpsActivityLoaded = '1';
				}
			}).fail(() => {
				if (!container.dataset.wpsActivityLoaded) {
					container.innerHTML = '<div class="wps-activity-empty"><p>' + this.escapeHtml((window.wpshadow_i18n && window.wpshadow_i18n.no_activities) || 'No activities yet') + '</p></div>';
					container.dataset.wpsActivityLoaded = '1';
				}
			});
		},

		renderActivities: function(container, activities, responseData = {}) {
			const i18n = window.wpshadow_i18n || {};
			const paginationDiv = container.parentElement.querySelector('.wps-activity-pagination');
			const prevBtn = paginationDiv ? paginationDiv.querySelector('.wps-activity-pagination-prev') : null;
			const nextBtn = paginationDiv ? paginationDiv.querySelector('.wps-activity-pagination-next') : null;
			const infoSpan = paginationDiv ? paginationDiv.querySelector('.wps-activity-pagination-info') : null;

			const limit = responseData.limit || 5;
			const offset = responseData.offset || 0;
			const total = responseData.total || 0;
			const currentPage = Math.floor(offset / limit);
			const totalPages = Math.ceil(total / limit);

			container.setAttribute('data-current-page', currentPage);

			if (paginationDiv) {
				if (totalPages > 1) {
					paginationDiv.style.display = 'block';
					if (infoSpan) {
						const pageText = i18n.page_label || 'Page';
						const ofText = i18n.of_label || 'of';
						infoSpan.textContent = pageText + ' ' + (currentPage + 1) + ' ' + ofText + ' ' + totalPages;
					}
					if (prevBtn) {
						prevBtn.disabled = currentPage === 0;
					}
					if (nextBtn) {
						nextBtn.disabled = currentPage >= totalPages - 1;
					}
				} else {
					paginationDiv.style.display = 'none';
				}
			}

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
				container.innerHTML = '<div class="wps-activity-empty"><p>' + this.escapeHtml(i18n.no_activities || 'No activities yet') + '</p></div>';
				return;
			}

			let html = '';
			activities.forEach((activity) => {
				const iconClass = this.getIconClass(activity.action);
				const iconColor = this.getIconColor(activity.action);
				const reportLink = activity.report_url && activity.report_label
					? '<a href="' + this.escapeAttr(activity.report_url) + '" class="wps-link">' + this.escapeHtml(activity.report_label) + '</a>'
					: '';
				const detailsLine = reportLink
					? '<div class="wps-activity-details">' + this.escapeHtml(i18n.report_label || 'Report') + ': ' + reportLink + '</div>'
					: (activity.details ? '<div class="wps-activity-details">' + this.escapeHtml(activity.details) + '</div>' : '');

				html += '<div class="wps-activity-item" role="listitem">' +
					'<div class="wps-activity-icon-wrapper" style="background: ' + iconColor + '20; border-color: ' + iconColor + ';">' +
					'<span class="dashicons ' + iconClass + '" style="color: ' + iconColor + ';" aria-hidden="true"></span>' +
					'</div>' +
					'<div class="wps-activity-content">' +
					'<div class="wps-activity-text">' + this.escapeHtml(activity.action) + '</div>' +
					detailsLine +
					'<time class="wps-activity-time" datetime="' + new Date(activity.timestamp * 1000).toISOString() + '">' +
					this.escapeHtml(activity.time_ago) + ' • ' + this.escapeHtml(activity.user_name) +
					'</time>' +
					'</div>' +
					'</div>';
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
				'diagnostic_run':     'dashicons-search',
				'diagnostic_finding': 'dashicons-flag',
				'diagnostic_failed':  'dashicons-warning',
				'treatment_applied':  'dashicons-admin-tools',
				'finding_fixed':      'dashicons-yes-alt',
				'workflow_executed':  'dashicons-controls-play',
				'guardian_executed':  'dashicons-shield-alt',
				'guardian_execution': 'dashicons-shield-alt',
				'report_generated':   'dashicons-chart-area',
				'cache_cleared':      'dashicons-update',
				'Settings Updated':   'dashicons-admin-settings'
			};
			return iconMap[action] || 'dashicons-admin-generic';
		},

		getIconColor: function(action) {
			if (action.includes('applied') || action.includes('fixed') || action.includes('completed')) {
				return '#10b981';
			} else if (action.includes('failed') || action.includes('error')) {
				return '#ef4444';
			} else if (action.includes('finding')) {
				return '#f59e0b'; // amber — issue detected
			} else if (action.includes('scan') || action.includes('run')) {
				return '#3b82f6';
			} else if (action.includes('workflow') || action.includes('executed')) {
				return '#8b5cf6';
			}
			return '#6b7280';
		},

		getLastTimestamp: function(context) {
			return parseInt(sessionStorage.getItem('wpshadow_activity_ts_' + context), 10) || 0;
		},

		setLastTimestamp: function(context, timestamp) {
			sessionStorage.setItem('wpshadow_activity_ts_' + context, timestamp);
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

	$(document).ready(function() {
		WPShadowActivityUpdater.init();
	});

	$(window).on('beforeunload', function() {
		WPShadowActivityUpdater.destroy();
	});
})(jQuery);
