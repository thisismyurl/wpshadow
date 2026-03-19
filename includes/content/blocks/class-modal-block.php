<?php
/**
 * Modal Block Registration
 *
 * Registers the Modal block for Gutenberg editor.
 * Supports two modes:
 * 1. Inline trigger - Place in content, modal shows when scrolled to
 * 2. CPT reference - Display a modal created via CPT
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modal_Block Class
 *
 * Handles modal block registration and rendering.
 *
 * @since 1.6093.1200
 */
class Modal_Block {

	/**
	 * Initialize the modal block.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	/**
	 * Register the modal block.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_block() {
		// Register block type.
		register_block_type(
			'wpshadow/modal',
			array(
				'api_version'     => 2,
				'title'           => __( 'Modal Popup', 'wpshadow' ),
				'description'     => __( 'Create a modal popup that appears when users scroll to this block.', 'wpshadow' ),
				'category'        => 'wpshadow',
				'icon'            => 'welcome-view-site',
				'keywords'        => array( 'modal', 'popup', 'lightbox', 'overlay' ),
				'supports'        => array(
					'align'  => array( 'wide', 'full' ),
					'anchor' => true,
				),
				'attributes'      => array(
					'modalType'      => array(
						'type'    => 'string',
						'default' => 'inline',
					),
					'modalId'        => array(
						'type'    => 'number',
						'default' => 0,
					),
					'content'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'title'          => array(
						'type'    => 'string',
						'default' => '',
					),
					'width'          => array(
						'type'    => 'number',
						'default' => 600,
					),
					'animation'      => array(
						'type'    => 'string',
						'default' => 'fade',
					),
					'triggerText'    => array(
						'type'    => 'string',
						'default' => 'Click to Open',
					),
					'showTrigger'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'overlayClose'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showCloseBtn'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'closeOnEsc'     => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
				'render_callback' => array( __CLASS__, 'render_block' ),
			)
		);
	}

	/**
	 * Render the modal block.
	 *
	 * @since 1.6093.1200
	 * @param  array  $attributes Block attributes.
	 * @param  string $content    Block content (inner blocks).
	 * @return string Rendered block HTML.
	 */
	public static function render_block( $attributes, $content ) {
		$modal_type    = $attributes['modalType'] ?? 'inline';
		$modal_id      = $attributes['modalId'] ?? 0;
		$title         = $attributes['title'] ?? '';
		$width         = $attributes['width'] ?? 600;
		$animation     = $attributes['animation'] ?? 'fade';
		$trigger_text  = $attributes['triggerText'] ?? 'Click to Open';
		$show_trigger  = $attributes['showTrigger'] ?? false;
		$overlay_close = $attributes['overlayClose'] ?? true;
		$show_close    = $attributes['showCloseBtn'] ?? true;
		$esc_close     = $attributes['closeOnEsc'] ?? true;

		// Generate unique ID for this modal instance.
		$unique_id = 'wpshadow-modal-block-' . wp_unique_id();

		// If CPT reference mode, get modal from CPT.
		if ( 'cpt' === $modal_type && $modal_id > 0 ) {
			$modal_post = get_post( $modal_id );
			if ( $modal_post && Modal_Post_Type::POST_TYPE === $modal_post->post_type ) {
				$title   = $modal_post->post_title;
				$content = apply_filters( 'the_content', $modal_post->post_content );
				
				// Override settings from CPT if available.
				$width         = get_post_meta( $modal_id, '_wpshadow_modal_width', true ) ?: $width;
				$animation     = get_post_meta( $modal_id, '_wpshadow_modal_animation', true ) ?: $animation;
				$overlay_close = get_post_meta( $modal_id, '_wpshadow_modal_overlay_close', true ) !== 'no';
				$show_close    = get_post_meta( $modal_id, '_wpshadow_modal_show_close', true ) !== 'no';
				$esc_close     = get_post_meta( $modal_id, '_wpshadow_modal_esc_close', true ) !== 'no';
			}
		} else {
			// Inline mode - use block content.
			$inline_content = $attributes['content'] ?? '';
			if ( ! empty( $inline_content ) ) {
				$content = wp_kses_post( $inline_content );
			}
		}

		$modal_classes = array(
			'wpshadow-modal',
			'wpshadow-modal-block',
			'wpshadow-modal-animation-' . esc_attr( $animation ),
		);

		$modal_data = array(
			'trigger'       => 'scroll',
			'overlay-close' => $overlay_close ? 'true' : 'false',
			'esc-close'     => $esc_close ? 'true' : 'false',
		);

		ob_start();
		?>
		<div class="wpshadow-modal-trigger-block" id="<?php echo esc_attr( $unique_id . '-trigger' ); ?>">
			<?php if ( $show_trigger ) : ?>
				<button 
					type="button" 
					class="wpshadow-modal-trigger-button" 
					data-modal-target="<?php echo esc_attr( $unique_id ); ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Open %s modal', 'wpshadow' ), $title ) ); ?>"
				>
					<?php echo esc_html( $trigger_text ); ?>
				</button>
			<?php else : ?>
				<!-- Modal trigger point (invisible) -->
				<span class="wpshadow-modal-scroll-trigger" data-modal-target="<?php echo esc_attr( $unique_id ); ?>" aria-hidden="true"></span>
			<?php endif; ?>
		</div>

		<div 
			id="<?php echo esc_attr( $unique_id ); ?>"
			class="<?php echo esc_attr( implode( ' ', $modal_classes ) ); ?>" 
			<?php
			foreach ( $modal_data as $key => $value ) {
				echo 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
			}
			?>
			style="display: none;"
			role="dialog"
			aria-modal="true"
			aria-labelledby="<?php echo esc_attr( $unique_id . '-title' ); ?>"
		>
			<div class="wpshadow-modal__overlay"></div>
			<div class="wpshadow-modal__container" style="max-width: <?php echo esc_attr( $width ); ?>px;">
				<?php if ( $show_close ) : ?>
					<button type="button" class="wpshadow-modal__close" aria-label="<?php esc_attr_e( 'Close modal', 'wpshadow' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				<?php endif; ?>
				<div class="wpshadow-modal__content">
					<?php if ( ! empty( $title ) ) : ?>
						<h2 id="<?php echo esc_attr( $unique_id . '-title' ); ?>" class="wpshadow-modal__title">
							<?php echo esc_html( $title ); ?>
						</h2>
					<?php endif; ?>
					<div class="wpshadow-modal__body">
						<?php echo wp_kses_post( $content ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get available modals for block editor.
	 *
	 * @since 1.6093.1200
	 * @return array Array of modals with id and title.
	 */
	public static function get_modal_options() {
		$modals = get_posts(
			array(
				'post_type'      => Modal_Post_Type::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$options = array(
			array(
				'label' => __( '-- Select Modal --', 'wpshadow' ),
				'value' => 0,
			),
		);

		foreach ( $modals as $modal ) {
			$options[] = array(
				'label' => $modal->post_title,
				'value' => $modal->ID,
			);
		}

		return $options;
	}
}

