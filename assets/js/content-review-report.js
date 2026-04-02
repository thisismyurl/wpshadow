/**
 * Content Review Report Page Script
 *
 * Handles the content quality report interface with filtering,
 * searching, and detailed post analysis.
 *
 * @since 1.6034.0000
 */

(function ($) {
	'use strict';

	const ContentReviewReport = {
		posts: [],
		filteredPosts: [],

		init() {
			this.bindEvents();
		},

		bindEvents() {
			$('#generate-report-btn').on('click', () => this.generateReport());
			$('#back-to-list').on('click', () => this.showListView());
			$('#post-type-filter, #severity-filter, #content-search').on('change keyup', () => this.filterPosts());
		},

		generateReport() {
			const postType = $('#post-type-filter').val() || '';
			const severity = $('#severity-filter').val() || '';
			const search = $('#content-search').val() || '';

			$('#posts-list').html('<p class="wpshadow-loading">Loading...</p>');

			wp.util.sendJsonRequest({
				url: wpShadowContentReview.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_get_post_review_data',
					post_type: postType,
					severity: severity,
					search: search,
					nonce: wpShadowContentReview.nonce,
				},
			}).done((response) => {
				if (response.success && response.data.posts) {
					this.posts = response.data.posts;
					this.filteredPosts = [...this.posts];
					this.renderPostsList();
				}
			}).fail(() => {
				$('#posts-list').html('<p class="wpshadow-error">Error loading posts. Please try again.</p>');
			});
		},

		filterPosts() {
			const search = $('#content-search').val().toLowerCase();
			const severity = $('#severity-filter').val();

			this.filteredPosts = this.posts.filter((post) => {
				let matchesSearch = !search || post.title.toLowerCase().includes(search);
				let matchesSeverity = true;

				if (severity === 'critical') {
					matchesSeverity = post.summary.critical > 0;
				} else if (severity === 'high') {
					matchesSeverity = post.summary.critical > 0 || post.summary.high > 0;
				}

				return matchesSearch && matchesSeverity;
			});

			this.renderPostsList();
		},

		renderPostsList() {
			if (this.filteredPosts.length === 0) {
				$('#posts-list').html('<p class="wpshadow-placeholder">No posts found matching your criteria.</p>');
				return;
			}

			let html = '<table class="wpshadow-posts-table">';
			html += '<thead>';
			html += '<tr>';
			html += '<th>Post Title</th>';
			html += '<th>Type</th>';
			html += '<th>Critical</th>';
			html += '<th>High</th>';
			html += '<th>Medium</th>';
			html += '<th>Low</th>';
			html += '<th>Total</th>';
			html += '<th>Action</th>';
			html += '</tr>';
			html += '</thead>';
			html += '<tbody>';

			this.filteredPosts.forEach((post) => {
				const total = post.summary.critical + post.summary.high + post.summary.medium + post.summary.low;
				const statusClass = total === 0 ? 'all-clear' : (post.summary.critical > 0 ? 'critical' : (post.summary.high > 0 ? 'high' : 'low-severity'));

				html += `<tr class="post-row ${statusClass}" data-post-id="${post.id}">`;
				html += `<td><strong>${this.escapeHtml(post.title)}</strong></td>`;
				html += `<td>${this.escapeHtml(post.type)}</td>`;
				html += `<td><span class="severity-badge critical">${post.summary.critical}</span></td>`;
				html += `<td><span class="severity-badge high">${post.summary.high}</span></td>`;
				html += `<td><span class="severity-badge medium">${post.summary.medium}</span></td>`;
				html += `<td><span class="severity-badge low">${post.summary.low}</span></td>`;
				html += `<td><strong>${total}</strong></td>`;
				html += `<td><button class="button button-small view-details-btn" data-post-id="${post.id}">View Details</button></td>`;
				html += '</tr>';
			});

			html += '</tbody>';
			html += '</table>';

			$('#posts-list').html(html);

			$('.view-details-btn').on('click', (e) => {
				const postId = $(e.target).data('post-id');
				this.showPostDetail(postId);
			});

			$('.post-row').on('click', function () {
				$(this).toggleClass('expanded');
			});
		},

		showPostDetail(postId) {
			const post = this.posts.find(p => p.id === postId);
			if (!post) return;

			$('#posts-list').hide();
			$('#post-detail').show();
			$('#post-title').text(post.title);

			let html = `
				<div class="wpshadow-post-info">
					<p><strong>URL:</strong> <a href="${post.url}" target="_blank">Edit Post →</a></p>
					<p><strong>Type:</strong> ${this.escapeHtml(post.type)}</p>
					<p><strong>Status:</strong> ${this.escapeHtml(post.status)}</p>
					<p><strong>Last Modified:</strong> ${this.escapeHtml(post.modified)}</p>
				</div>

				<div class="wpshadow-post-summary">
					<h4>Issue Summary</h4>
					<div class="wpshadow-severity-breakdown">
						<div class="severity-item critical">
							<span class="count">${post.summary.critical}</span>
							<span class="label">Critical</span>
						</div>
						<div class="severity-item high">
							<span class="count">${post.summary.high}</span>
							<span class="label">High</span>
						</div>
						<div class="severity-item medium">
							<span class="count">${post.summary.medium}</span>
							<span class="label">Medium</span>
						</div>
						<div class="severity-item low">
							<span class="count">${post.summary.low}</span>
							<span class="label">Low</span>
						</div>
					</div>
				</div>

				<div class="wpshadow-post-actions">
					<p>
						<a href="${post.url}" class="button button-primary" target="_blank">Edit Post</a>
						<button class="button" data-post-id="${postId}" onclick="window.location.href='${post.url}';">
							Review with Wizard
						</button>
					</p>
				</div>
			`;

			$('#detail-content').html(html);
		},

		showListView() {
			$('#post-detail').hide();
			$('#posts-list').show();
		},

		escapeHtml(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		},
	};

	$(document).ready(() => {
		ContentReviewReport.init();
	});

})(jQuery);
