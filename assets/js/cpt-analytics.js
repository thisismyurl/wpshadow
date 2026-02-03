/**
 * CPT Analytics Dashboard
 *
 * Handles analytics dashboard functionality for custom post types.
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      1.6034.1200
 */

(function($) {
    'use strict';

    let currentChart = null;

    /**
     * Initialize analytics dashboard
     */
    function initAnalytics() {
        if (!$('.wpshadow-analytics-page').length) {
            return;
        }

        bindEvents();
        loadAnalytics();
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Filter changes
        $(document).on('change', '#wpshadow-analytics-post-type, #wpshadow-analytics-period', function() {
            loadAnalytics();
        });

        // Export button
        $(document).on('click', '.wpshadow-export-analytics', handleExport);

        // Refresh button
        $(document).on('click', '.wpshadow-refresh-analytics', function(e) {
            e.preventDefault();
            loadAnalytics();
        });
    }

    /**
     * Load analytics data
     */
    function loadAnalytics() {
        const postType = $('#wpshadow-analytics-post-type').val();
        const period = $('#wpshadow-analytics-period').val();

        showLoadingIndicator();

        $.ajax({
            url: wpShadowAnalytics.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wpshadow_get_cpt_analytics',
                nonce: wpShadowAnalytics.nonce,
                post_type: postType,
                period: period
            },
            success: function(response) {
                if (response.success) {
                    renderAnalytics(response.data);
                } else {
                    showError(response.data.message || wpShadowAnalytics.i18n.loadFailed);
                }
            },
            error: function() {
                showError(wpShadowAnalytics.i18n.loadFailed);
            },
            complete: function() {
                hideLoadingIndicator();
            }
        });
    }

    /**
     * Render analytics data
     */
    function renderAnalytics(data) {
        renderSummaryCards(data.summary);
        renderTopPosts(data.top_posts);
        renderChart(data.daily_views);
    }

    /**
     * Render summary cards
     */
    function renderSummaryCards(summary) {
        $('.wpshadow-stat-total-views .wpshadow-stat-value').text(formatNumber(summary.total_views));
        $('.wpshadow-stat-avg-views .wpshadow-stat-value').text(formatNumber(summary.average_views));
        $('.wpshadow-stat-top-post .wpshadow-stat-value').text(summary.top_post_views);
        $('.wpshadow-stat-total-posts .wpshadow-stat-value').text(formatNumber(summary.total_posts));
    }

    /**
     * Render top posts table
     */
    function renderTopPosts(posts) {
        const $tbody = $('.wpshadow-top-posts tbody');
        $tbody.empty();

        if (!posts || posts.length === 0) {
            $tbody.html('<tr><td colspan="3" class="wpshadow-no-data">' + 
                       wpShadowAnalytics.i18n.noPosts + '</td></tr>');
            return;
        }

        posts.forEach(function(post, index) {
            const $row = $('<tr>')
                .append($('<td>').text(index + 1))
                .append($('<td>').html('<a href="' + post.edit_url + '">' + 
                                      escapeHtml(post.title) + '</a>'))
                .append($('<td>').text(formatNumber(post.views)));
            
            $tbody.append($row);
        });
    }

    /**
     * Render views chart
     */
    function renderChart(dailyViews) {
        const canvas = document.getElementById('wpshadow-views-chart');
        
        if (!canvas) {
            return;
        }

        const ctx = canvas.getContext('2d');

        // Destroy existing chart
        if (currentChart) {
            currentChart.destroy();
        }

        const labels = Object.keys(dailyViews);
        const data = Object.values(dailyViews);

        currentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: wpShadowAnalytics.i18n.views,
                    data: data,
                    borderColor: '#2271b1',
                    backgroundColor: 'rgba(34, 113, 177, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    /**
     * Handle export
     */
    function handleExport(e) {
        e.preventDefault();

        const postType = $('#wpshadow-analytics-post-type').val();
        const period = $('#wpshadow-analytics-period').val();

        const url = wpShadowAnalytics.ajaxUrl + 
                   '?action=wpshadow_export_analytics' +
                   '&nonce=' + wpShadowAnalytics.nonce +
                   '&post_type=' + encodeURIComponent(postType) +
                   '&period=' + encodeURIComponent(period);

        window.location.href = url;
    }

    /**
     * Show loading indicator
     */
    function showLoadingIndicator() {
        $('.wpshadow-analytics-content').addClass('loading');
    }

    /**
     * Hide loading indicator
     */
    function hideLoadingIndicator() {
        $('.wpshadow-analytics-content').removeClass('loading');
    }

    /**
     * Show error message
     */
    function showError(message) {
        const $error = $('<div class="notice notice-error is-dismissible">' +
                       '<p>' + message + '</p>' +
                       '</div>');
        $('.wpshadow-analytics-filters').after($error);
    }

    /**
     * Format number with commas
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initAnalytics();
    });

})(jQuery);
