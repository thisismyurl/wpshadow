<?php
/**
 * Glossary Post Type
 *
 * Registers custom post type and taxonomy for glossary terms.
 *
 * @package WPShadow
 * @subpackage Glossary
 */

declare(strict_types=1);

namespace WPShadow\Glossary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Glossary_Post_Type class.
 */
class Glossary_Post_Type {
	/**
	 * Initialize the post type.
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_action( 'init', [ __CLASS__, 'register_taxonomy' ] );
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'save_post_wpshadow_glossary', [ __CLASS__, 'save_meta' ] );
	}

	/**
	 * Register glossary post type.
	 */
	public static function register_post_type(): void {
		register_post_type(
			'wpshadow_glossary',
			[
				'labels'              => [
					'name'          => __( 'Glossary Terms', 'wpshadow' ),
					'singular_name' => __( 'Glossary Term', 'wpshadow' ),
					'add_new_item'  => __( 'Add New Term', 'wpshadow' ),
					'edit_item'     => __( 'Edit Term', 'wpshadow' ),
					'view_item'     => __( 'View Term', 'wpshadow' ),
				],
				'public'              => true,
				'has_archive'         => true,
				'rewrite'             => [ 'slug' => 'glossary' ],
				'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
				'show_in_rest'        => true,
				'rest_base'           => 'glossary-terms',
				'menu_icon'           => 'dashicons-book',
				'capability_type'     => 'post',
			]
		);
	}

	/**
	 * Register glossary category taxonomy.
	 */
	public static function register_taxonomy(): void {
		register_taxonomy(
			'glossary_category',
			'wpshadow_glossary',
			[
				'labels'            => [
					'name'          => __( 'Glossary Categories', 'wpshadow' ),
					'singular_name' => __( 'Category', 'wpshadow' ),
				],
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'rest_base'         => 'glossary-categories',
			]
		);
	}

	/**
	 * Add meta boxes for glossary term.
	 */
	public static function add_meta_boxes(): void {
		add_meta_box(
			'wpshadow_glossary_settings',
			__( 'Glossary Settings', 'wpshadow' ),
			[ __CLASS__, 'render_meta_box' ],
			'wpshadow_glossary',
			'normal',
			'default'
		);
	}

	/**
	 * Render glossary settings meta box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function render_meta_box( $post ): void {
		wp_nonce_field( 'wpshadow_glossary_nonce', 'wpshadow_glossary_nonce' );

		$term_variations = get_post_meta( $post->ID, 'wpshadow_glossary_variations', true );
		if ( ! is_array( $term_variations ) ) {
			$term_variations = [ get_the_title( $post->ID ) ];
		}

		$case_sensitive = get_post_meta( $post->ID, 'wpshadow_glossary_case_sensitive', true );
		$tooltip_enabled = get_post_meta( $post->ID, 'wpshadow_glossary_tooltip_enabled', true );
		if ( '' === $tooltip_enabled ) {
			$tooltip_enabled = '1';
		}

		?>
		<div style="margin: 15px 0;">
			<label><strong><?php esc_html_e( 'Term Variations', 'wpshadow' ); ?></strong></label>
			<p style="font-size: 12px; color: #666; margin: 5px 0;">
				<?php esc_html_e( 'Enter all variations of this term to match (one per line). E.g., SMTP, smtp, Simple Mail Transfer Protocol', 'wpshadow' ); ?>
			</p>
			<textarea name="wpshadow_glossary_variations" rows="4" style="width: 100%; font-family: monospace;">
<?php echo esc_textarea( implode( "\n", $term_variations ) ); ?></textarea>
		</div>

		<div style="margin: 15px 0;">
			<label>
				<input type="checkbox" name="wpshadow_glossary_case_sensitive" value="1" <?php checked( $case_sensitive, '1' ); ?> />
				<?php esc_html_e( 'Case Sensitive Matching', 'wpshadow' ); ?>
			</label>
			<p style="font-size: 12px; color: #666; margin: 5px 0;">
				<?php esc_html_e( 'If checked, only exact case matches will trigger the tooltip.', 'wpshadow' ); ?>
			</p>
		</div>

		<div style="margin: 15px 0;">
			<label>
				<input type="checkbox" name="wpshadow_glossary_tooltip_enabled" value="1" <?php checked( $tooltip_enabled, '1' ); ?> />
				<?php esc_html_e( 'Enable Tooltip in Content', 'wpshadow' ); ?>
			</label>
			<p style="font-size: 12px; color: #666; margin: 5px 0;">
				<?php esc_html_e( 'If checked, this term will automatically display a tooltip when found in article content.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save glossary term metadata.
	 *
	 * @param int $post_id The post ID.
	 */
	public static function save_meta( $post_id ): void {
		if ( ! isset( $_POST['wpshadow_glossary_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpshadow_glossary_nonce'] ) ), 'wpshadow_glossary_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save term variations
		if ( isset( $_POST['wpshadow_glossary_variations'] ) ) {
			$variations = array_filter( array_map( 'trim', explode( "\n", sanitize_text_field( wp_unslash( $_POST['wpshadow_glossary_variations'] ) ) ) ) );
			update_post_meta( $post_id, 'wpshadow_glossary_variations', $variations );
		}

		// Save case sensitivity
		$case_sensitive = isset( $_POST['wpshadow_glossary_case_sensitive'] ) ? '1' : '0';
		update_post_meta( $post_id, 'wpshadow_glossary_case_sensitive', $case_sensitive );

		// Save tooltip enabled
		$tooltip_enabled = isset( $_POST['wpshadow_glossary_tooltip_enabled'] ) ? '1' : '0';
		update_post_meta( $post_id, 'wpshadow_glossary_tooltip_enabled', $tooltip_enabled );
	}
}
