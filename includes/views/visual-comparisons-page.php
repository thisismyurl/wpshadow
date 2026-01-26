<?php
/**
 * Visual Comparisons View
 *
 * Displays visual comparison history for treatments applied to the site.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Views;

use WPShadow\Core\Visual_Comparator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the visual comparisons page
 *
 * @return void
 */
function wpshadow_render_visual_comparisons() {
	Visual_Comparisons_Page::render();
}

/**
 * Visual Comparisons Page
 */
class Visual_Comparisons_Page {
	/**
	 * Render the visual comparisons page
	 *
	 * @return void
	 */
	public static function render() {
		// Check capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
		}

		// Get statistics
		$statistics = Visual_Comparator::get_statistics();

		// Get recent comparisons
		$comparisons = Visual_Comparator::get_comparisons( array( 'limit' => 20 ) );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Visual Comparisons', 'wpshadow' ); ?></h1>
			<p class="wps-version-tag">v<?php echo esc_html( WPSHADOW_VERSION ); ?></p>
			
			<p class="description">
				<?php esc_html_e( 'Visual comparison captures screenshots before and after treatments are applied, helping you ensure changes don\'t break your site.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-visual-comparisons">
				<!-- Statistics Cards -->
				<div class="wpshadow-statistics-cards" style="margin: 20px 0; display: flex; gap: 20px;">
					<div class="wpshadow-stat-card" style="flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
						<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
							<?php esc_html_e( 'Total Comparisons', 'wpshadow' ); ?>
						</h3>
						<p style="margin: 0; font-size: 32px; font-weight: bold; color: #2271b1;">
							<?php echo esc_html( (string) $statistics['total'] ); ?>
						</p>
					</div>

					<div class="wpshadow-stat-card" style="flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
						<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
							<?php esc_html_e( 'Last 30 Days', 'wpshadow' ); ?>
						</h3>
						<p style="margin: 0; font-size: 32px; font-weight: bold; color: #2271b1;">
							<?php echo esc_html( (string) $statistics['last_30_days'] ); ?>
						</p>
					</div>
				</div>

				<!-- Comparisons List -->
				<div class="wpshadow-comparisons-list">
					<h2><?php esc_html_e( 'Recent Comparisons', 'wpshadow' ); ?></h2>

					<?php if ( empty( $comparisons ) ) : ?>
						<div class="notice notice-info inline">
							<p>
								<?php esc_html_e( 'No visual comparisons yet. Apply a treatment to see before/after screenshots appear here.', 'wpshadow' ); ?>
							</p>
						</div>
					<?php else : ?>
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th style="width: 150px;"><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
									<th><?php esc_html_e( 'Finding', 'wpshadow' ); ?></th>
									<th><?php esc_html_e( 'Page', 'wpshadow' ); ?></th>
									<th style="width: 200px;"><?php esc_html_e( 'Before', 'wpshadow' ); ?></th>
									<th style="width: 200px;"><?php esc_html_e( 'After', 'wpshadow' ); ?></th>
									<th style="width: 100px;"><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $comparisons as $comparison ) : ?>
									<tr>
										<td>
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo esc_html( gmdate( 'Y-m-d H:i', strtotime( $comparison['created_at'] ) ) );
											?>
										</td>
										<td>
											<strong><?php echo esc_html( $comparison['finding_id'] ); ?></strong>
										</td>
										<td>
											<a href="<?php echo esc_url( $comparison['page_url'] ); ?>" target="_blank">
												<?php echo esc_html( $comparison['page_url'] ); ?>
											</a>
										</td>
										<td>
											<?php if ( ! empty( $comparison['before_url'] ) ) : ?>
												<a href="<?php echo esc_url( $comparison['before_url'] ); ?>" target="_blank">
													<img 
														src="<?php echo esc_url( $comparison['before_url'] ); ?>" 
														alt="<?php esc_attr_e( 'Before screenshot', 'wpshadow' ); ?>"
														style="max-width: 150px; height: auto; border: 1px solid #ddd;"
													/>
												</a>
											<?php endif; ?>
										</td>
										<td>
											<?php if ( ! empty( $comparison['after_url'] ) ) : ?>
												<a href="<?php echo esc_url( $comparison['after_url'] ); ?>" target="_blank">
													<img 
														src="<?php echo esc_url( $comparison['after_url'] ); ?>" 
														alt="<?php esc_attr_e( 'After screenshot', 'wpshadow' ); ?>"
														style="max-width: 150px; height: auto; border: 1px solid #ddd;"
													/>
												</a>
											<?php endif; ?>
										</td>
										<td>
											<button 
												type="button"
												class="button button-small wpshadow-view-comparison"
												data-comparison-id="<?php echo esc_attr( (string) $comparison['id'] ); ?>"
											>
												<?php esc_html_e( 'View', 'wpshadow' ); ?>
											</button>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Comparison Modal -->
		<div id="wpshadow-comparison-modal" style="display: none;">
			<div class="wpshadow-modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999;">
				<div class="wpshadow-modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 20px; max-width: 90%; max-height: 90%; overflow: auto; border-radius: 4px;">
					<button type="button" class="wpshadow-modal-close" style="float: right; border: none; background: none; font-size: 24px; cursor: pointer;">&times;</button>
					<h2><?php esc_html_e( 'Visual Comparison', 'wpshadow' ); ?></h2>
					<div id="wpshadow-comparison-content"></div>
				</div>
			</div>
		</div>

		<style>
			.wpshadow-visual-comparisons {
				margin-top: 20px;
			}
			.wpshadow-comparison-side-by-side {
				display: flex;
				gap: 20px;
				margin: 20px 0;
			}
			.wpshadow-comparison-side {
				flex: 1;
			}
			.wpshadow-comparison-side img {
				max-width: 100%;
				height: auto;
				border: 1px solid #ddd;
			}
		</style>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// View comparison button handler
			$('.wpshadow-view-comparison').on('click', function() {
				var comparisonId = $(this).data('comparison-id');
				
				// Show modal
				$('#wpshadow-comparison-modal').show();
				$('#wpshadow-comparison-content').html('<p><?php esc_html_e( 'Loading...', 'wpshadow' ); ?></p>');
				
				// Load comparison data
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_get_visual_comparison',
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_visual_comparison' ) ); ?>',
						id: comparisonId
					},
					success: function(response) {
						if (response.success && response.data.comparison) {
							var comparison = response.data.comparison;
							
							// Escape HTML entities for safe insertion
							function escapeHtml(text) {
								var div = document.createElement('div');
								div.textContent = text;
								return div.innerHTML;
							}
							
							var html = '<div class="wpshadow-comparison-side-by-side">';
							html += '<div class="wpshadow-comparison-side">';
							html += '<h3><?php esc_html_e( 'Before', 'wpshadow' ); ?></h3>';
							html += '<img src="' + escapeHtml(comparison.before_url) + '" alt="<?php esc_attr_e( 'Before', 'wpshadow' ); ?>" />';
							html += '</div>';
							html += '<div class="wpshadow-comparison-side">';
							html += '<h3><?php esc_html_e( 'After', 'wpshadow' ); ?></h3>';
							html += '<img src="' + escapeHtml(comparison.after_url) + '" alt="<?php esc_attr_e( 'After', 'wpshadow' ); ?>" />';
							html += '</div>';
							html += '</div>';
							html += '<p><strong><?php esc_html_e( 'Finding:', 'wpshadow' ); ?></strong> ' + escapeHtml(comparison.finding_id) + '</p>';
							html += '<p><strong><?php esc_html_e( 'Page:', 'wpshadow' ); ?></strong> <a href="' + escapeHtml(comparison.page_url) + '" target="_blank">' + escapeHtml(comparison.page_url) + '</a></p>';
							html += '<p><strong><?php esc_html_e( 'Date:', 'wpshadow' ); ?></strong> ' + escapeHtml(comparison.created_at) + '</p>';
							
							$('#wpshadow-comparison-content').html(html);
						} else {
							$('#wpshadow-comparison-content').html('<p><?php esc_html_e( 'Failed to load comparison.', 'wpshadow' ); ?></p>');
						}
					},
					error: function() {
						$('#wpshadow-comparison-content').html('<p><?php esc_html_e( 'Error loading comparison.', 'wpshadow' ); ?></p>');
					}
				});
			});
			
			// Close modal handler
			$('.wpshadow-modal-close, .wpshadow-modal-overlay').on('click', function(e) {
				if (e.target === this) {
					$('#wpshadow-comparison-modal').hide();
				}
			});
		});
		</script>
		<?php
	}
}
