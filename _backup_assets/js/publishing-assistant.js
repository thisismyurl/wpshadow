/**
 * WPShadow Publishing Assistant - Block Editor Integration
 *
 * Integrates content review and validation features into the WordPress block editor.
 * Allows multiple features to provide pre-publish checks for content quality.
 *
 * @package WPSHADOW
 */

(function() {
	'use strict';

	const { registerPlugin } = wp.plugins;
	const { PluginPrePublishPanel } = wp.editPost;
	const { Fragment, useState, useEffect } = wp.element;
	const { Button, Panel, PanelBody, Notice, Spinner } = wp.components;
	const { useSelect, useDispatch } = wp.data;
	const apiFetch = wp.apiFetch;

	/**
	 * Publishing Assistant Panel Component
	 */
	const PublishingAssistantPanel = () => {
		const [isOpen, setIsOpen] = useState(false);
		const [isRunning, setIsRunning] = useState(false);
		const [reviews, setReviews] = useState({});

		const postId = useSelect((select) => select('core/editor').getCurrentPostId(), []);
		const postType = useSelect((select) => select('core/editor').getCurrentPostType(), []);

		// Auto-run reviews when pre-publish panel opens
		const handleOpen = async () => {
			setIsOpen(true);
			if (Object.keys(reviews).length === 0) {
				runReviews();
			}
		};

		const runReviews = async () => {
			setIsRunning(true);
			setReviews({});

			try {
				const response = await apiFetch({
					path: '/wp/v2/wpshadow-publishing-assistant/review',
					method: 'POST',
					data: {
						post_id: postId,
						post_type: postType,
					},
				});

				if (response && response.reviews) {
					setReviews(response.reviews);
				}
			} catch (error) {
				console.error('Publishing Assistant Error:', error);
			} finally {
				setIsRunning(false);
			}
		};

		const handleReviewAction = async (reviewerId, actionType) => {
			try {
				await apiFetch({
					path: '/wp/v2/wpshadow-publishing-assistant/callback',
					method: 'POST',
					data: {
						post_id: postId,
						reviewer_id: reviewerId,
						action_type: actionType,
					},
				});

				// Refresh reviews after action
				runReviews();
			} catch (error) {
				console.error('Review Action Error:', error);
			}
		};

		const getReviewIcon = (status) => {
			switch (status) {
				case 'success':
					return '✓';
				case 'warning':
					return '⚠';
				case 'error':
					return '✕';
				default:
					return 'ℹ';
			}
		};

		const getReviewColor = (status) => {
			switch (status) {
				case 'success':
					return '#28a745';
				case 'warning':
					return '#ffc107';
				case 'error':
					return '#dc3545';
				default:
					return '#17a2b8';
			}
		};

		if (!wpsPublishingAssistant?.reviewers?.length) {
			return null;
		}

		return (
			<PluginPrePublishPanel
				title={wp.i18n.__('Content Review', 'wpshadow')}
				initialOpen={false}
			>
				<div className="wps-publishing-assistant">
					<p style={{ marginTop: 0, fontSize: '13px' }}>
						{wp.i18n.__('Review your content before publishing', 'wpshadow')}
					</p>

					{isRunning && (
						<div style={{ textAlign: 'center', padding: '20px' }}>
							<Spinner />
							<p>{wp.i18n.__('Running content review...', 'wpshadow')}</p>
						</div>
					)}

					{!isRunning && Object.keys(reviews).length > 0 && (
						<div className="wps-review-results">
							{Object.entries(reviews).map(([reviewerId, review]) => (
								<div
									key={reviewerId}
									style={{
										border: `1px solid ${getReviewColor(review.status)}`,
										borderRadius: '4px',
										padding: '12px',
										marginBottom: '12px',
										backgroundColor: `${getReviewColor(review.status)}15`,
									}}
								>
									<div
										style={{
											display: 'flex',
											alignItems: 'center',
											marginBottom: '8px',
										}}
									>
										<span
											style={{
												display: 'inline-flex',
												alignItems: 'center',
												justifyContent: 'center',
												width: '24px',
												height: '24px',
												borderRadius: '50%',
												backgroundColor: getReviewColor(review.status),
												color: 'white',
												marginRight: '8px',
												fontSize: '14px',
												fontWeight: 'bold',
											}}
										>
											{getReviewIcon(review.status)}
										</span>
										<strong style={{ color: getReviewColor(review.status) }}>
											{review.name}
										</strong>
									</div>

									<p
										style={{
											margin: '0 0 8px 32px',
											fontSize: '13px',
											color: '#333',
										}}
									>
										{review.message}
									</p>

									{review.items && review.items.length > 0 && (
										<div
											style={{
												marginLeft: '32px',
												marginBottom: '8px',
												fontSize: '12px',
											}}
										>
											<details>
												<summary style={{ cursor: 'pointer', marginBottom: '4px' }}>
													{wp.i18n.sprintf(
														wp.i18n._n(
															'%d issue',
															'%d issues',
															review.items.length,
															'wpshadow'
														),
														review.items.length
													)}
												</summary>
												<ul style={{ margin: '4px 0 0 16px', paddingLeft: 0 }}>
													{review.items.slice(0, 5).map((item, idx) => (
														<li key={idx} style={{ listStyle: 'disc' }}>
															{item.url && (
																<>
																	<a
																		href={item.url}
																		target="_blank"
																		rel="noopener noreferrer"
																		style={{
																			color: getReviewColor(
																				review.status
																			),
																		}}
																	>
																		{item.url}
																	</a>{' '}
																	({item.code})
																</>
															)}
														</li>
													))}
													{review.items.length > 5 && (
														<li style={{ listStyle: 'disc', marginTop: '4px' }}>
															... and {review.items.length - 5} more
														</li>
													)}
												</ul>
											</details>
										</div>
									)}

									{review.action_url && (
										<div style={{ marginLeft: '32px', marginTop: '8px' }}>
											<Button
												variant="secondary"
												size="small"
												href={review.action_url}
												target="_blank"
											>
												{review.action_text ||
													wp.i18n.__('Learn More', 'wpshadow')}
											</Button>
										</div>
									)}
								</div>
							))}
						</div>
					)}

					{!isRunning && Object.keys(reviews).length === 0 && (
						<Button
							variant="secondary"
							onClick={() => runReviews()}
							style={{ marginTop: '8px' }}
						>
							{wp.i18n.__('Run Review', 'wpshadow')}
						</Button>
					)}
				</div>
			</PluginPrePublishPanel>
		);
	};

	// Register the publishing assistant plugin
	if (typeof wpsPublishingAssistant !== 'undefined') {
		registerPlugin('wps-publishing-assistant', {
			render: PublishingAssistantPanel,
		});
	}
})();
