/**
 * WPShadow Content Review Pre-Publish Panel
 *
 * Adds a content health gauge to the block editor pre-publish panel
 * and lets users open the content review wizard.
 *
 * @since 1.7035.1410
 */

(function () {
	'use strict';

	if (!window.wp || !wp.editPost || !wp.plugins) {
		return;
	}

	const dataStore = wp.data;
	const element = wp.element;
	const components = wp.components;
	const editPost = wp.editPost;
	const i18n = wp.i18n;

	const panelData = window.wpShadowContentReviewPanel || window.wpShadowReview || {};
	const ajaxUrl = panelData.ajax_url || (window.ajaxurl || '');
	const nonce = panelData.nonce || '';

	const severityWeights = {
		critical: 30,
		high: 20,
		medium: 10,
		low: 5
	};

	function calculateScore(diagnostics) {
		let totalWeight = 0;
		let issueCount = 0;
		let fixableCount = 0;

		Object.keys(diagnostics || {}).forEach(function (family) {
			const items = diagnostics[family] || [];
			items.forEach(function (item) {
				issueCount += 1;
				const severity = (item.severity || 'medium').toLowerCase();
				totalWeight += severityWeights[severity] || severityWeights.medium;
				if (item.finding && item.finding.auto_fixable) {
					fixableCount += 1;
				}
			});
		});

		const score = Math.max(0, Math.min(100, 100 - totalWeight));
		return {
			score: Math.round(score),
			issues: issueCount,
			fixable: fixableCount
		};
	}

	function getScoreLabel(score) {
		if (score >= 90) {
			return i18n.__('Excellent', 'wpshadow');
		}
		if (score >= 75) {
			return i18n.__('Good', 'wpshadow');
		}
		if (score >= 60) {
			return i18n.__('Needs Work', 'wpshadow');
		}
		return i18n.__('Needs Attention', 'wpshadow');
	}

	function getScoreClass(score) {
		if (score >= 90) {
			return 'is-excellent';
		}
		if (score >= 75) {
			return 'is-good';
		}
		if (score >= 60) {
			return 'is-warning';
		}
		return 'is-danger';
	}

	function Gauge(props) {
		const score = props.score || 0;
		const radius = 40;
		const circumference = Math.PI * radius;
		const dash = (score / 100) * circumference;
		const gap = circumference - dash;

		return element.createElement(
			'div',
			{ className: 'wpshadow-prepublish-gauge ' + getScoreClass(score) },
			element.createElement(
				'svg',
				{ viewBox: '0 0 100 60', role: 'img', 'aria-label': i18n.__('Content score gauge', 'wpshadow') },
				element.createElement('path', {
					d: 'M10 50 A40 40 0 0 1 90 50',
					className: 'wpshadow-gauge-track'
				}),
				element.createElement('path', {
					d: 'M10 50 A40 40 0 0 1 90 50',
					className: 'wpshadow-gauge-fill',
					style: { strokeDasharray: dash + ' ' + gap }
				})
			),
			element.createElement(
				'div',
				{ className: 'wpshadow-gauge-score' },
				element.createElement('span', { className: 'wpshadow-gauge-value' }, score),
				element.createElement('span', { className: 'wpshadow-gauge-label' }, getScoreLabel(score))
			)
		);
	}

	function ContentReviewPanel() {
		const postId = dataStore.useSelect(function (select) {
			return select('core/editor').getCurrentPostId();
		}, []);

		const [state, setState] = element.useState({
			loading: true,
			score: 0,
			issues: 0,
			fixable: 0,
			error: ''
		});

		element.useEffect(function () {
			if (!postId || !ajaxUrl || !nonce) {
				return;
			}

			setState({ loading: true, score: 0, issues: 0, fixable: 0, error: '' });

			wp.util.sendJsonRequest({
				url: ajaxUrl,
				method: 'POST',
				data: {
					action: 'wpshadow_content_review_get_data',
					post_id: postId,
					nonce: nonce
				}
			}).done(function (response) {
				if (!response || !response.success) {
					setState({ loading: false, score: 0, issues: 0, fixable: 0, error: i18n.__('Unable to load content checks.', 'wpshadow') });
					return;
				}

				const results = calculateScore(response.data.diagnostics || {});
				setState({
					loading: false,
					score: results.score,
					issues: results.issues,
					fixable: results.fixable,
					error: ''
				});
			}).fail(function () {
				setState({ loading: false, score: 0, issues: 0, fixable: 0, error: i18n.__('Unable to load content checks.', 'wpshadow') });
			});
		}, [postId]);

		function openWizard() {
			if (window.wpShadowContentReview && typeof window.wpShadowContentReview.openWizardForPost === 'function') {
				window.wpShadowContentReview.openWizardForPost(postId);
				return;
			}

			if (window.jQuery) {
				const button = window.jQuery('.wpshadow-review-button').first();
				if (button.length) {
					button.trigger('click');
				}
			}
		}

		return element.createElement(
			editPost.PluginPrePublishPanel,
			{
				name: 'wpshadow-content-review-panel',
				title: i18n.__('Content Check', 'wpshadow')
			},
			element.createElement(
				'div',
				{ className: 'wpshadow-prepublish-panel' },
				state.loading
					? element.createElement(components.Spinner, null)
					: element.createElement(Gauge, { score: state.score }),
				state.error
					? element.createElement('p', { className: 'wpshadow-prepublish-error' }, state.error)
					: element.createElement(
						'div',
						{ className: 'wpshadow-prepublish-summary' },
						element.createElement(
							'p',
							{ className: 'wpshadow-prepublish-meta' },
							i18n.sprintf(
								i18n._n('%d issue found', '%d issues found', state.issues, 'wpshadow'),
								state.issues
							)
							),
						element.createElement(
							'p',
							{ className: 'wpshadow-prepublish-meta' },
							i18n.sprintf(
								i18n._n('%d auto-fix available', '%d auto-fixes available', state.fixable, 'wpshadow'),
								state.fixable
							)
							)
						),
				element.createElement(
					components.Button,
					{ className: 'wpshadow-prepublish-button', isSecondary: true, onClick: openWizard },
					panelData.wizard_text || i18n.__('Review issues and fixes', 'wpshadow')
					)
			)
		);
	}

	wp.plugins.registerPlugin('wpshadow-content-review-panel', {
		render: ContentReviewPanel
	});
})();
