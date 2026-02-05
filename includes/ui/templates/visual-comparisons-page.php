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
		<div class="wrap wps-page-container">
			<?php wpshadow_render_page_header(
				__( 'Visual Comparisons', 'wpshadow' ),
				__( 'Visual comparison captures screenshots before and after treatments are applied, helping you ensure changes don\'t break your site.', 'wpshadow' )
			); ?>

			<div class="wpshadow-visual-comparisons">
				<!-- Statistics Cards -->
				<div class="wpshadow-statistics-cards wps-flex wps-gap-5 wps-my-5">
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
									<th class="wps-w-40"><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
									<th><?php esc_html_e( 'Finding', 'wpshadow' ); ?></th>
									<th><?php esc_html_e( 'Page', 'wpshadow' ); ?></th>
									<th class="wps-w-50"><?php esc_html_e( 'Before', 'wpshadow' ); ?></th>
									<th class="wps-w-50"><?php esc_html_e( 'After', 'wpshadow' ); ?></th>
									<th class="wps-w-25"><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
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
														class="wps-max-w-150 wps-h-auto wps-border wps-border-gray-200"
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
														class="wps-max-w-150 wps-h-auto wps-border wps-border-gray-200"
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
		<div id="wpshadow-comparison-modal" class="wpshadow-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="wpshadow-comparison-title" aria-hidden="true" data-wpshadow-modal="static" data-overlay-close="true" data-esc-close="true">
			<div class="wpshadow-modal wpshadow-modal--wide" role="document">
				<button type="button" class="wpshadow-modal-close" aria-label="<?php echo esc_attr__( 'Close dialog', 'wpshadow' ); ?>" data-wpshadow-modal-close="wpshadow-comparison-modal">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="wpshadow-modal-header">
					<h2 id="wpshadow-comparison-title" class="wpshadow-modal-title"><?php esc_html_e( 'Visual Comparison', 'wpshadow' ); ?></h2>
				</div>
				<div class="wpshadow-modal-body comparison-content" id="wpshadow-comparison-content"></div>
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
				if (window.WPShadowModal && typeof window.WPShadowModal.openStatic === 'function') {
					window.WPShadowModal.openStatic('wpshadow-comparison-modal', { returnFocus: this });
				} else {
					$('#wpshadow-comparison-modal').addClass('wpshadow-modal-show');
				}
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
			$('.wpshadow-modal-close').on('click', function() {
				if (window.WPShadowModal && typeof window.WPShadowModal.closeStatic === 'function') {
					window.WPShadowModal.closeStatic('wpshadow-comparison-modal');
				} else {
					$('#wpshadow-comparison-modal').removeClass('wpshadow-modal-show');
				}
			});
		});
		</script>
		<?php
	}
}
