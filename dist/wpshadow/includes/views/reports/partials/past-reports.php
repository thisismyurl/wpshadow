<?php
/**
 * Past Reports Card Partial
 *
 * Shared rendering for report history cards across report views.
 *
 * @package WPShadow
 * @subpackage Reports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpshadow_render_past_reports_card' ) ) {
	/**
	 * Render a Past Reports card.
	 *
	 * @param array $args Card configuration.
	 * @return void
	 */
	function wpshadow_render_past_reports_card( array $args ) {
		$defaults = array(
			'title'         => __( 'Past Reports', 'wpshadow' ),
			'description'   => '',
			'empty_message' => __( 'No past reports yet.', 'wpshadow' ),
			'items'         => array(),
			'pagination'    => array(),
			'delete_action' => array(),
		);

		$args = wp_parse_args( $args, $defaults );
		$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
		$pagination = isset( $args['pagination'] ) && is_array( $args['pagination'] ) ? $args['pagination'] : array();
		$delete_action = isset( $args['delete_action'] ) && is_array( $args['delete_action'] ) ? $args['delete_action'] : array();
		$current_page = isset( $pagination['current'] ) ? max( 1, (int) $pagination['current'] ) : 1;
		$total_pages  = isset( $pagination['total'] ) ? max( 1, (int) $pagination['total'] ) : 1;
		$page_param   = isset( $pagination['param'] ) ? (string) $pagination['param'] : 'past_page';
		$base_url     = isset( $pagination['base_url'] ) ? (string) $pagination['base_url'] : '';
		$confirm_text = isset( $delete_action['confirm'] ) ? (string) $delete_action['confirm'] : __( 'Delete all past reports? This cannot be undone.', 'wpshadow' );
		?>
		<div class="wps-card wps-mt-4">
			<div class="wps-card-body">
				<h3 class="wps-text-lg wps-mb-2">
					<?php echo esc_html( $args['title'] ); ?>
				</h3>
				<?php if ( ! empty( $args['description'] ) ) : ?>
					<p class="wps-text-sm wps-text-muted wps-mb-3">
						<?php echo esc_html( $args['description'] ); ?>
					</p>
				<?php endif; ?>

				<?php if ( empty( $items ) ) : ?>
					<p class="wps-text-sm wps-text-muted">
						<?php echo esc_html( $args['empty_message'] ); ?>
					</p>
				<?php else : ?>
					<ul class="wps-list-disc wps-ml-5">
						<?php foreach ( $items as $item ) : ?>
							<?php
							$time = isset( $item['time'] ) ? $item['time'] : 0;
							if ( is_string( $time ) ) {
								$time = strtotime( $time );
							}
							$time_label = '';
							if ( $time ) {
								$time_label = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $time );
							}
							$type_label = isset( $item['type'] ) && '' !== $item['type']
								? strtoupper( (string) $item['type'] )
								: '';
							$actions = isset( $item['actions'] ) && is_array( $item['actions'] ) ? $item['actions'] : array();
							?>
							<li class="wps-mb-2 wps-flex wps-items-start wps-justify-between wps-gap-3">
								<div>
									<?php if ( ! empty( $item['url'] ) ) : ?>
										<a href="<?php echo esc_url( $item['url'] ); ?>" class="wps-link" target="_blank" rel="noopener">
											<?php echo esc_html( $item['title'] ?? '' ); ?>
										</a>
									<?php else : ?>
										<span class="wps-font-medium">
											<?php echo esc_html( $item['title'] ?? '' ); ?>
										</span>
									<?php endif; ?>
									<?php if ( $time_label || $type_label ) : ?>
										<span class="wps-text-xs wps-text-muted">
											<?php echo esc_html( $time_label ); ?>
											<?php if ( $type_label ) : ?>
												<?php echo esc_html( '• ' . $type_label ); ?>
											<?php endif; ?>
										</span>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $actions ) ) : ?>
									<div class="wps-flex wps-gap-3">
										<?php foreach ( $actions as $action ) : ?>
											<?php if ( empty( $action['url'] ) || empty( $action['label'] ) ) : ?>
												<?php continue; ?>
											<?php endif; ?>
											<a href="<?php echo esc_url( $action['url'] ); ?>" class="wps-link">
												<?php echo esc_html( $action['label'] ); ?>
											</a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $total_pages > 1 && $base_url ) : ?>
					<div class="wps-flex wps-gap-2 wps-items-center wps-mt-3">
						<?php
						$prev_page = max( 1, $current_page - 1 );
						$next_page = min( $total_pages, $current_page + 1 );
						?>
						<a class="wps-btn wps-btn--secondary wps-btn-sm" href="<?php echo esc_url( add_query_arg( $page_param, $prev_page, $base_url ) ); ?>" <?php echo $current_page === 1 ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
							<?php esc_html_e( 'Previous', 'wpshadow' ); ?>
						</a>
						<span class="wps-text-xs wps-text-muted">
							<?php
							printf(
								/* translators: 1: current page, 2: total pages */
								esc_html__( 'Page %1$d of %2$d', 'wpshadow' ),
								$current_page,
								$total_pages
							);
							?>
						</span>
						<a class="wps-btn wps-btn--secondary wps-btn-sm" href="<?php echo esc_url( add_query_arg( $page_param, $next_page, $base_url ) ); ?>" <?php echo $current_page === $total_pages ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
							<?php esc_html_e( 'Next', 'wpshadow' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $delete_action['action_url'] ) ) : ?>
					<form method="post" action="<?php echo esc_url( $delete_action['action_url'] ); ?>" class="wps-mt-4 wps-delete-reports-form">
						<?php
						wp_nonce_field(
							$delete_action['nonce_action'] ?? 'wpshadow_delete_reports',
							$delete_action['nonce_name'] ?? 'wpshadow_delete_reports_nonce'
						);
						$fields = isset( $delete_action['fields'] ) && is_array( $delete_action['fields'] ) ? $delete_action['fields'] : array();
						foreach ( $fields as $name => $value ) {
							printf(
								'<input type="hidden" name="%s" value="%s" />',
								esc_attr( $name ),
								esc_attr( (string) $value )
							);
						}
						?>
						<button
							type="submit"
							class="wps-btn wps-btn--danger wps-delete-reports-trigger"
							data-confirm-title="<?php echo esc_attr__( 'Delete past reports?', 'wpshadow' ); ?>"
							data-confirm-message="<?php echo esc_attr( $confirm_text ); ?>"
							data-confirm-button="<?php echo esc_attr( $delete_action['label'] ?? __( 'Delete All Reports', 'wpshadow' ) ); ?>"
							data-cancel-button="<?php echo esc_attr__( 'Cancel', 'wpshadow' ); ?>"
						>
							<?php echo esc_html( $delete_action['label'] ?? __( 'Delete All Reports', 'wpshadow' ) ); ?>
						</button>
					</form>
					<script>
						(function($) {
							'use strict';

							if (window.wpshadowDeleteReportsModalBound) {
								return;
							}
							window.wpshadowDeleteReportsModalBound = true;

							$(document).on('submit', '.wps-delete-reports-form', function(e) {
								const $form = $(this);
								if ($form.data('wpshadowConfirmed')) {
									return;
								}

								const $trigger = $form.find('.wps-delete-reports-trigger').first();
								if (!$trigger.length || typeof window.WPShadowModal === 'undefined') {
									return;
								}

								e.preventDefault();

								window.WPShadowModal.confirm({
									title: $trigger.data('confirmTitle') || 'Confirm',
									message: $trigger.data('confirmMessage') || '',
									confirmText: $trigger.data('confirmButton') || 'Confirm',
									cancelText: $trigger.data('cancelButton') || 'Cancel',
									type: 'danger',
									onConfirm: function() {
										$form.data('wpshadowConfirmed', true);
										$form.trigger('submit');
									}
								});
							});
						})(jQuery);
					</script>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
