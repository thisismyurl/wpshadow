<?php
/**
 * Help Page Module for WPShadow
 *
 * Help page rendering.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get help cards from modular card definitions.
 *
 * @return array Help cards.
 */
function wpshadow_get_help_cards() {
	$cards = array();
	$base  = WPSHADOW_PATH . 'includes/ui/help';
	$dirs  = glob( $base . '/*', GLOB_ONLYDIR );

	if ( ! empty( $dirs ) ) {
		foreach ( $dirs as $dir ) {
			$card_file = $dir . '/card.php';
			if ( ! file_exists( $card_file ) ) {
				continue;
			}

			$card = require $card_file;
			if ( is_array( $card ) ) {
				$cards[] = $card;
			}
		}
	}

	usort(
		$cards,
		static function ( $left, $right ) {
			return ( $left['order'] ?? 0 ) <=> ( $right['order'] ?? 0 );
		}
	);

	return $cards;
}

/**
 * Render help page.
 *
 * @return void
 */
function wpshadow_render_help() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$cloud_api_key = get_option( 'wpshadow_cloud_api_key', '' );
	$recent_learning_items = array();
	$recent_learning_error = '';

	if ( ! empty( $cloud_api_key ) ) {
		$feed_url = apply_filters( 'wpshadow_help_activity_feed_url', '' );

		if ( ! empty( $feed_url ) ) {
			$feed_response = wp_remote_get(
				$feed_url,
				array(
					'timeout' => 8,
					'headers' => array(
						'Authorization' => 'Bearer ' . $cloud_api_key,
					),
				)
			);

			if ( is_wp_error( $feed_response ) ) {
				$recent_learning_error = $feed_response->get_error_message();
			} else {
				$body = wp_remote_retrieve_body( $feed_response );
				$decoded = json_decode( $body, true );
				if ( is_array( $decoded ) && ! empty( $decoded['items'] ) && is_array( $decoded['items'] ) ) {
					$recent_learning_items = $decoded['items'];
				}
			}
		}
	}

	$cards = wpshadow_get_help_cards();
	$resource_cards = array_filter(
		$cards,
		static function ( $card ) {
			return ( $card['section'] ?? 'resources' ) === 'resources';
		}
	);
	$support_cards = array_filter(
		$cards,
		static function ( $card ) {
			return ( $card['section'] ?? '' ) === 'support';
		}
	);

	?>
	<div class="wrap wps-page-container">
		<!-- Page Header -->
		<?php wpshadow_render_page_header(
			__( 'WPShadow Help', 'wpshadow' ),
			__( 'Explore tutorials, guides, and resources to get the most out of WPShadow.', 'wpshadow' ),
			'dashicons-editor-help'
		); ?>

		<!-- Help Resources Grid -->
		<div class="wps-grid wps-grid-auto-320">
			<?php foreach ( $resource_cards as $item ) : ?>
				<?php
				$width_class = '';
				$card_width  = $item['width'] ?? '';
				if ( 'full' === $card_width ) {
					$width_class = 'wps-grid-span-full';
				} elseif ( 'half' === $card_width ) {
					$width_class = 'wps-grid-span-half';
				}

				wpshadow_render_card(
					array(
						'title'       => $item['title'] ?? '',
						'title_url'   => $item['url'] ?? '',
						'description' => $item['description'] ?? '',
						'icon'        => $item['icon'] ?? '',
						'icon_class'  => 'wps-text-primary',
						'card_class'  => $width_class,
						'body'        => function() use ( $item ) {
							?>
							<div class="wps-flex wps-gap-2">
								<?php if ( ! empty( $item['url'] ) ) : ?>
									<a href="<?php echo esc_url( $item['url'] ); ?>"
										target="_blank" rel="noopener noreferrer"
										class="wps-btn wps-btn--secondary">
										<span class="dashicons dashicons-external"></span>
										<?php esc_html_e( 'Read Article', 'wpshadow' ); ?>
									</a>
								<?php endif; ?>
								<?php if ( ! empty( $item['video'] ) ) : ?>
									<a href="<?php echo esc_url( $item['video'] ); ?>"
										target="_blank" rel="noopener noreferrer"
										class="wps-btn wps-btn--secondary">
										<span class="dashicons dashicons-video-alt2"></span>
										<?php esc_html_e( 'Watch Video', 'wpshadow' ); ?>
									</a>
								<?php endif; ?>
							</div>
							<?php
						},
					)
				);
				?>
			<?php endforeach; ?>
		</div>

		<!-- Contact Support & Resources -->
		<?php foreach ( $support_cards as $card ) : ?>
			<?php
			$body_callback = $card['body_callback'] ?? '';
			$width_class   = '';
			$card_width    = $card['width'] ?? '';
			if ( 'full' === $card_width ) {
				$width_class = 'wps-grid-span-full';
			} elseif ( 'half' === $card_width ) {
				$width_class = 'wps-grid-span-half';
			}
			$card_class = trim( ( $card['card_class'] ?? 'wps-mt-8' ) . ' ' . $width_class );
			wpshadow_render_card(
				array(
					'title'       => $card['title'] ?? '',
					'title_tag'   => $card['title_tag'] ?? 'h2',
					'description' => $card['description'] ?? '',
					'icon'        => $card['icon'] ?? '',
					'card_class'  => $card_class,
					'body'        => function() use ( $body_callback ) {
						if ( $body_callback && function_exists( $body_callback ) ) {
							call_user_func( $body_callback );
						}
					},
				)
			);
			?>
		<?php endforeach; ?>

		<?php if ( ! empty( $cloud_api_key ) ) : ?>
			<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Recent Learning Activity', 'wpshadow' ),
						'description' => __( 'See the last guides and videos you opened inside WPShadow.', 'wpshadow' ),
						'icon'        => 'dashicons-welcome-learn-more',
						'card_class'  => 'wps-mt-8',
						'body'        => function() use ( $recent_learning_items, $recent_learning_error ) {
							if ( ! empty( $recent_learning_items ) ) {
								?>
								<ul class="wps-list-disc wps-ml-5">
									<?php foreach ( $recent_learning_items as $item ) : ?>
										<?php
											$title = isset( $item['title'] ) ? (string) $item['title'] : '';
											$type  = isset( $item['type'] ) ? (string) $item['type'] : '';
											$url   = isset( $item['url'] ) ? (string) $item['url'] : '';
											$viewed_at = isset( $item['viewed_at'] ) ? (string) $item['viewed_at'] : '';
											$meta_bits = array();
											if ( $type ) {
												$meta_bits[] = $type;
											}
											if ( $viewed_at ) {
												$meta_bits[] = $viewed_at;
											}
											$meta_text = $meta_bits ? implode( ' • ', $meta_bits ) : '';
										?>
										<li class="wps-mb-2">
											<?php if ( $url ) : ?>
												<a class="wps-link" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
													<?php echo esc_html( $title ); ?>
												</a>
											<?php else : ?>
												<?php echo esc_html( $title ); ?>
											<?php endif; ?>
											<?php if ( $meta_text ) : ?>
												<span class="wps-text-xs wps-text-muted">
													<?php echo esc_html( $meta_text ); ?>
												</span>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php
								return;
							}
							?>
							<p class="wps-text-sm wps-text-muted">
								<?php esc_html_e( 'Your recent reading and viewing history will appear here once the WPShadow feed is connected.', 'wpshadow' ); ?>
							</p>
							<?php if ( $recent_learning_error ) : ?>
								<p class="wps-text-xs wps-text-muted">
									<?php echo esc_html( $recent_learning_error ); ?>
								</p>
							<?php endif; ?>
							<?php
						},
					)
				);
			?>
		<?php endif; ?>
	</div>
	<?php
}
