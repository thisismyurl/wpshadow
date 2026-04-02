<?php
/**
 * Content Tabs Block
 *
 * Displays tabbed content sections.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content_Tabs_Block Class
 *
 * Creates accessible tabbed content sections.
 *
 * @since 1.6093.1200
 */
class Content_Tabs_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/content-tabs',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since 1.6093.1200
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'tabs'         => array(
				'type'    => 'array',
				'default' => array(
					array(
						'title'   => __( 'Tab 1', 'wpshadow' ),
						'content' => __( 'Content for tab 1', 'wpshadow' ),
					),
					array(
						'title'   => __( 'Tab 2', 'wpshadow' ),
						'content' => __( 'Content for tab 2', 'wpshadow' ),
					),
				),
			),
			'orientation'  => array(
				'type'    => 'string',
				'default' => 'horizontal', // horizontal, vertical.
			),
			'defaultTab'   => array(
				'type'    => 'number',
				'default' => 0,
			),
			'accentColor'  => array(
				'type'    => 'string',
				'default' => '#1e40af',
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since 1.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$tabs         = $attributes['tabs'] ?? array();
		$orientation  = sanitize_text_field( $attributes['orientation'] ?? 'horizontal' );
		$default_tab  = absint( $attributes['defaultTab'] ?? 0 );
		$accent_color = sanitize_hex_color( $attributes['accentColor'] ?? '#1e40af' );

		if ( empty( $tabs ) ) {
			return '';
		}

		$unique_id = 'wpshadow-tabs-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div 
			class="wpshadow-content-tabs wpshadow-orientation-<?php echo esc_attr( $orientation ); ?>" 
			id="<?php echo esc_attr( $unique_id ); ?>"
			style="--tab-accent-color: <?php echo esc_attr( $accent_color ); ?>;"
		>
			<div class="wpshadow-tab-list" role="tablist" aria-label="<?php esc_attr_e( 'Content tabs', 'wpshadow' ); ?>">
				<?php foreach ( $tabs as $index => $tab ) : ?>
					<?php
					$title     = sanitize_text_field( $tab['title'] ?? '' );
					$is_active = ( $index === $default_tab );
					?>
					<button
						type="button"
						class="wpshadow-tab-button<?php echo $is_active ? ' wpshadow-active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo esc_attr( $is_active ? 'true' : 'false' ); ?>"
						aria-controls="<?php echo esc_attr( $unique_id . '-panel-' . $index ); ?>"
						id="<?php echo esc_attr( $unique_id . '-tab-' . $index ); ?>"
						tabindex="<?php echo esc_attr( $is_active ? '0' : '-1' ); ?>"
					>
						<?php echo esc_html( $title ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			
			<div class="wpshadow-tab-panels">
				<?php foreach ( $tabs as $index => $tab ) : ?>
					<?php
					$content   = wp_kses_post( $tab['content'] ?? '' );
					$is_active = ( $index === $default_tab );
					?>
					<div
						class="wpshadow-tab-panel<?php echo $is_active ? ' wpshadow-active' : ''; ?>"
						role="tabpanel"
						id="<?php echo esc_attr( $unique_id . '-panel-' . $index ); ?>"
						aria-labelledby="<?php echo esc_attr( $unique_id . '-tab-' . $index ); ?>"
						tabindex="0"
						<?php echo ! $is_active ? 'hidden' : ''; ?>
					>
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
