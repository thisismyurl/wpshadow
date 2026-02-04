<?php
/**
 * FAQ Accordion Block
 *
 * Displays FAQ items with expandable/collapsible functionality.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1520
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ_Accordion_Block Class
 *
 * Creates accessible FAQ accordions with schema markup for SEO.
 *
 * @since 1.6034.1520
 */
class FAQ_Accordion_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6034.1520
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/faq-accordion',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since  1.6034.1520
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'items'         => array(
				'type'    => 'array',
				'default' => array(
					array(
						'question' => __( 'What is WPShadow?', 'wpshadow' ),
						'answer'   => __( 'WPShadow is a comprehensive WordPress health and optimization plugin that helps you maintain a secure, fast, and reliable website.', 'wpshadow' ),
					),
					array(
						'question' => __( 'Is WPShadow free?', 'wpshadow' ),
						'answer'   => __( 'Yes! WPShadow core features are completely free. We also offer premium add-ons for advanced features.', 'wpshadow' ),
					),
				),
			),
			'allowMultiple' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'defaultOpen'   => array(
				'type'    => 'number',
				'default' => -1, // -1 = none, 0+ = index.
			),
			'showSchema'    => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'iconStyle'     => array(
				'type'    => 'string',
				'default' => 'chevron', // chevron, plus, arrow.
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since  1.6034.1520
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$items          = $attributes['items'] ?? array();
		$allow_multiple = ! empty( $attributes['allowMultiple'] );
		$default_open   = absint( $attributes['defaultOpen'] ?? -1 );
		$show_schema    = ! empty( $attributes['showSchema'] );
		$icon_style     = sanitize_text_field( $attributes['iconStyle'] ?? 'chevron' );

		if ( empty( $items ) ) {
			return '';
		}

		// Generate schema markup if enabled.
		$schema = '';
		if ( $show_schema ) {
			$schema_items = array();
			foreach ( $items as $item ) {
				$schema_items[] = array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $item['question'] ?? '' ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $item['answer'] ?? '' ),
					),
				);
			}

			$schema_data = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $schema_items,
			);

			$schema = sprintf(
				'<script type="application/ld+json">%s</script>',
				wp_json_encode( $schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
			);
		}

		ob_start();
		?>
		<?php echo $schema; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="wpshadow-faq-accordion wpshadow-icon-<?php echo esc_attr( $icon_style ); ?>" data-allow-multiple="<?php echo esc_attr( $allow_multiple ? '1' : '0' ); ?>">
			<?php foreach ( $items as $index => $item ) : ?>
				<?php
				$question = wp_kses_post( $item['question'] ?? '' );
				$answer   = wp_kses_post( $item['answer'] ?? '' );
				$is_open  = ( $default_open === $index );
				?>
				<div class="wpshadow-faq-item<?php echo $is_open ? ' wpshadow-open' : ''; ?>">
					<button 
						type="button"
						class="wpshadow-faq-question"
						aria-expanded="<?php echo esc_attr( $is_open ? 'true' : 'false' ); ?>"
						aria-controls="wpshadow-faq-answer-<?php echo esc_attr( $index ); ?>"
					>
						<span class="wpshadow-faq-question-text"><?php echo $question; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="wpshadow-faq-icon" aria-hidden="true">
							<?php if ( 'chevron' === $icon_style ) : ?>
								<span class="dashicons dashicons-arrow-down-alt2"></span>
							<?php elseif ( 'plus' === $icon_style ) : ?>
								<span class="dashicons dashicons-plus-alt2"></span>
							<?php else : ?>
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							<?php endif; ?>
						</span>
					</button>
					<div 
						id="wpshadow-faq-answer-<?php echo esc_attr( $index ); ?>"
						class="wpshadow-faq-answer"
						role="region"
						<?php echo ! $is_open ? 'hidden' : ''; ?>
					>
						<div class="wpshadow-faq-answer-content">
							<?php echo $answer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
