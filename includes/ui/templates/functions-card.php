<?php
/**
 * Card Rendering Helper Functions
 *
 * Provides consistent card markup for admin pages.
 *
 * @package    WPShadow
 * @subpackage Views
 * @since 1.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a consistent card layout.
 *
 * Supports optional header (title, description, icon), body content,
 * footer content, and action buttons.
 *
 * @since 1.6093.1200
 * @param  array $args {
 *     Card configuration.
 *
 *     @type string          $title            Card title.
 *     @type string          $title_url        Optional title link URL.
 *     @type string          $title_tag        Title tag. Default 'h3'.
 *     @type string          $title_class      Title class. Default 'wps-card-title wps-m-0'.
 *     @type string          $description      Card description.
 *     @type string          $description_class Description class. Default 'wps-card-description wps-m-0'.
 *     @type string          $icon             Dashicons class (without prefix).
 *     @type string          $icon_class       Additional icon class.
 *     @type string          $icon_color       Optional icon color.
 *     @type string          $card_class       Additional card classes.
 *     @type string          $header_class     Header class. Default 'wps-card-header wps-pb-3 wps-border-bottom'.
 *     @type string          $body_class       Body class. Default 'wps-card-body'.
 *     @type string          $footer_class     Footer class. Default 'wps-card-footer'.
 *     @type array           $badge            Optional badge array with 'label' and 'class'.
 *     @type array           $actions          Action button definitions.
 *     @type array           $header_actions   Header action button definitions.
 *     @type callable|string $body             Body content callback or HTML string.
 *     @type callable|string $footer           Footer content callback or HTML string.
 *     @type array           $attrs            HTML attributes for card wrapper.
 * }
 * @return void
 */
function wpshadow_render_card( array $args = array() ) {
	$defaults = array(
		'title'             => '',
		'title_url'         => '',
		'title_tag'         => 'h3',
		'title_class'       => 'wps-card-title wps-m-0 wps-pl-2',
		'description'       => '',
		'description_class' => 'wps-card-description wps-m-0 wps-pl-1',
		'icon'              => '',
		'icon_class'        => '',
		'icon_color'        => '',
		'card_class'        => '',
		'header_class'      => 'wps-card-header wps-pb-3 wps-border-bottom',
		'body_class'        => 'wps-card-body',
		'footer_class'      => 'wps-card-footer',
		'badge'             => array(),
		'actions'           => array(),
		'header_actions'    => array(),
		'body'              => null,
		'footer'            => null,
		'attrs'             => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	$card_class = trim( 'wps-card ' . $args['card_class'] );
	$attrs      = wpshadow_build_html_attributes( $args['attrs'] );

	$has_header = ! empty( $args['title'] ) || ! empty( $args['description'] ) || ! empty( $args['icon'] ) || ! empty( $args['header_actions'] ) || ! empty( $args['badge'] );

	// Capture body content to check if it's empty
	$body_content = '';
	if ( ! empty( $args['body'] ) ) {
		ob_start();
		if ( is_callable( $args['body'] ) ) {
			call_user_func( $args['body'] );
		} elseif ( is_string( $args['body'] ) ) {
			echo wp_kses_post( $args['body'] );
		}
		$body_content = ob_get_clean();
	}

	// Capture footer content to check if it's empty
	$footer_content = '';
	if ( ! empty( $args['footer'] ) ) {
		ob_start();
		if ( is_callable( $args['footer'] ) ) {
			call_user_func( $args['footer'] );
		} elseif ( is_string( $args['footer'] ) ) {
			echo wp_kses_post( $args['footer'] );
		}
		$footer_content = ob_get_clean();
	}

	// Check if we have body content or actions to display
	$has_body   = ( ! empty( trim( $body_content ) ) || ! empty( $args['actions'] ) );
	$has_footer = ! empty( trim( $footer_content ) );

	?>
	<div class="<?php echo esc_attr( $card_class ); ?>"<?php echo $attrs; ?>>
		<?php if ( $has_header ) : ?>
			<div class="<?php echo esc_attr( $args['header_class'] ); ?>">
				<div class="wps-flex wps-items-start wps-justify-between wps-gap-4">
					<div class="wps-flex wps-gap-3 wps-items-start">
						<?php if ( ! empty( $args['icon'] ) ) : ?>
							<span class="dashicons <?php echo esc_attr( $args['icon'] ); ?> <?php echo esc_attr( $args['icon_class'] ); ?> wps-text-3xl"<?php echo ! empty( $args['icon_color'] ) ? ' style="color: ' . esc_attr( $args['icon_color'] ) . ';"' : ''; ?>></span>
						<?php endif; ?>
						<div>
							<?php
							$title_tag = tag_escape( $args['title_tag'] );
							?>
							<<?php echo $title_tag; ?> class="<?php echo esc_attr( $args['title_class'] ); ?>">
								<?php if ( ! empty( $args['title_url'] ) ) : ?>
									<a href="<?php echo esc_url( $args['title_url'] ); ?>" style="color: inherit; text-decoration: none;">
										<?php echo esc_html( $args['title'] ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $args['title'] ); ?>
								<?php endif; ?>
								<?php if ( ! empty( $args['badge']['label'] ) ) : ?>
									<span class="wps-card-badge <?php echo esc_attr( $args['badge']['class'] ?? '' ); ?>">
										<?php echo esc_html( $args['badge']['label'] ); ?>
									</span>
								<?php endif; ?>
							</<?php echo $title_tag; ?>>
							<?php if ( ! empty( $args['description'] ) ) : ?>
								<p class="<?php echo esc_attr( $args['description_class'] ); ?>">
									<?php echo esc_html( $args['description'] ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( ! empty( $args['header_actions'] ) ) : ?>
						<div class="wps-flex wps-gap-2 wps-items-center">
							<?php wpshadow_render_card_actions( $args['header_actions'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $has_body ) : ?>
			<div class="<?php echo esc_attr( $args['body_class'] ); ?>">
				<?php
				// Output the captured body content
				echo $body_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is already escaped by callback or wp_kses_post
				?>
				<?php if ( ! empty( $args['actions'] ) ) : ?>
					<div class="wps-flex wps-gap-2 wps-flex-wrap">
						<?php wpshadow_render_card_actions( $args['actions'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $has_footer ) : ?>
			<div class="<?php echo esc_attr( $args['footer_class'] ); ?>">
				<?php
				// Output the captured footer content
				echo $footer_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is already escaped by callback or wp_kses_post
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render action buttons for a card.
 *
 * @since 1.6093.1200
 * @param  array $actions Action definitions.
 * @return void
 */
function wpshadow_render_card_actions( array $actions = array() ) {
	foreach ( $actions as $action ) {
		$label      = isset( $action['label'] ) ? $action['label'] : '';
		$url        = isset( $action['url'] ) ? $action['url'] : '';
		$class      = isset( $action['class'] ) ? $action['class'] : 'wps-btn wps-btn--secondary';
		$icon       = isset( $action['icon'] ) ? $action['icon'] : '';
		$attrs      = isset( $action['attrs'] ) ? $action['attrs'] : array();
		$aria_label = isset( $action['aria_label'] ) ? $action['aria_label'] : '';
		$target     = isset( $action['target'] ) ? $action['target'] : '';
		$rel        = isset( $action['rel'] ) ? $action['rel'] : '';

		if ( empty( $label ) ) {
			continue;
		}

		if ( ! empty( $aria_label ) ) {
			$attrs['aria-label'] = $aria_label;
		}

		if ( ! empty( $target ) ) {
			$attrs['target'] = $target;
		}

		if ( ! empty( $rel ) ) {
			$attrs['rel'] = $rel;
		}

		$attr_string = wpshadow_build_html_attributes( $attrs );

		if ( ! empty( $url ) ) {
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>"<?php echo $attr_string; ?>>
				<?php if ( ! empty( $icon ) ) : ?>
					<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
				<?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</a>
			<?php
		} else {
			?>
			<button type="button" class="<?php echo esc_attr( $class ); ?>"<?php echo $attr_string; ?>>
				<?php if ( ! empty( $icon ) ) : ?>
					<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
				<?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</button>
			<?php
		}
	}
}

/**
 * Build an HTML attribute string from a key/value array.
 *
 * @since 1.6093.1200
 * @param  array $attrs Attributes.
 * @return string
 */
function wpshadow_build_html_attributes( array $attrs = array() ) {
	$parts = array();
	foreach ( $attrs as $key => $value ) {
		if ( '' === $value || null === $value ) {
			continue;
		}
		$parts[] = sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	return implode( '', $parts );
}
