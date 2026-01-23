<?php
/**
 * Links Post Type
 *
 * Registers custom post type for managed links.
 *
 * @package WPShadow
 * @subpackage Links
 */

declare(strict_types=1);

namespace WPShadow\Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Links_Post_Type class.
 */
class Links_Post_Type {
	/**
	 * Initialize the post type.
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_action( 'init', [ __CLASS__, 'register_taxonomy' ] );
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'save_post_wpshadow_link', [ __CLASS__, 'save_meta' ] );
	}

	/**
	 * Register links post type.
	 */
	public static function register_post_type(): void {
		register_post_type(
			'wpshadow_link',
			[
				'labels'              => [
					'name'          => __( 'Managed Links', 'wpshadow' ),
					'singular_name' => __( 'Managed Link', 'wpshadow' ),
					'add_new_item'  => __( 'Add New Link', 'wpshadow' ),
					'edit_item'     => __( 'Edit Link', 'wpshadow' ),
					'view_item'     => __( 'View Link', 'wpshadow' ),
				],
				'public'              => false,
				'has_archive'         => false,
				'supports'            => [ 'title', 'editor', 'revisions' ],
				'show_in_rest'        => true,
				'show_ui'             => true,
				'show_in_menu'        => 'wpshadow',
				'rest_base'           => 'managed-links',
				'menu_icon'           => 'dashicons-admin-links',
				'capability_type'     => 'post',
			]
		);
	}

	/**
	 * Register link category taxonomy.
	 */
	public static function register_taxonomy(): void {
		register_taxonomy(
			'link_category',
			'wpshadow_link',
			[
				'labels'            => [
					'name'          => __( 'Link Categories', 'wpshadow' ),
					'singular_name' => __( 'Category', 'wpshadow' ),
				],
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'rest_base'         => 'link-categories',
			]
		);
	}

	/**
	 * Add meta boxes for link.
	 */
	public static function add_meta_boxes(): void {
		add_meta_box(
			'wpshadow_link_settings',
			__( 'Link Settings', 'wpshadow' ),
			[ __CLASS__, 'render_meta_box' ],
			'wpshadow_link',
			'normal',
			'default'
		);
	}

	/**
	 * Render link settings meta box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function render_meta_box( $post ): void {
		wp_nonce_field( 'wpshadow_link_nonce', 'wpshadow_link_nonce' );

		$link_url           = get_post_meta( $post->ID, 'wpshadow_link_url', true );
		$link_text          = get_post_meta( $post->ID, 'wpshadow_link_text', true );
		$is_affiliate       = get_post_meta( $post->ID, 'wpshadow_link_is_affiliate', true );
		$open_blank         = get_post_meta( $post->ID, 'wpshadow_link_open_blank', true );
		$nofollow           = get_post_meta( $post->ID, 'wpshadow_link_nofollow', true );
		$affiliate_text     = get_post_meta( $post->ID, 'wpshadow_link_affiliate_text', true );
		$link_enabled       = get_post_meta( $post->ID, 'wpshadow_link_enabled', true );
		if ( '' === $link_enabled ) {
			$link_enabled = '1';
		}

		?>
		<div class="wps-m-15">
			<label><strong><?php esc_html_e( 'Link URL', 'wpshadow' ); ?></strong></label>
			<input type="url" name="wpshadow_link_url" value="<?php echo esc_attr( $link_url ); ?>" class="wps-p-8" placeholder="https://example.com" />
		</div>

		<div class="wps-m-15">
			<label><strong><?php esc_html_e( 'Link Display Text', 'wpshadow' ); ?></strong></label>
			<p class="wps-m-5">
				<?php esc_html_e( 'The text that appears in articles (exact match required)', 'wpshadow' ); ?>
			</p>
			<input type="text" name="wpshadow_link_text" value="<?php echo esc_attr( $link_text ); ?>" class="wps-p-8" placeholder="e.g., WPShadow" />
		</div>

		<div class="wps-m-15">
			<label><strong><?php esc_html_e( 'Link Behavior', 'wpshadow' ); ?></strong></label>
			<div style="margin-top: 8px;">
				<label>
					<input type="checkbox" name="wpshadow_link_open_blank" value="1" <?php checked( $open_blank, '1' ); ?> />
					<?php esc_html_e( 'Open in new tab (_blank)', 'wpshadow' ); ?>
				</label>
			</div>
			<div style="margin-top: 6px;">
				<label>
					<input type="checkbox" name="wpshadow_link_nofollow" value="1" <?php checked( $nofollow, '1' ); ?> />
					<?php esc_html_e( 'Add rel="nofollow" (no SEO credit)', 'wpshadow' ); ?>
				</label>
			</div>
		</div>

		<div class="wps-m-15-p-12-rounded-4">
			<label>
				<input type="checkbox" name="wpshadow_link_is_affiliate" value="1" <?php checked( $is_affiliate, '1' ); ?> />
				<strong><?php esc_html_e( 'This is an affiliate link', 'wpshadow' ); ?></strong>
			</label>
			<p class="wps-m-8">
				<?php esc_html_e( 'Checking this will automatically add affiliate disclosure notice to the page', 'wpshadow' ); ?>
			</p>
		</div>

		<div class="wps-m-15" id="wpshadow-affiliate-text-box" class="wps-none">
			<label><strong><?php esc_html_e( 'Custom Affiliate Disclosure', 'wpshadow' ); ?></strong></label>
			<p class="wps-m-5">
				<?php esc_html_e( 'Leave empty to use default disclosure. Include "##URL##" to show the URL.', 'wpshadow' ); ?>
			</p>
			<textarea name="wpshadow_link_affiliate_text" rows="3" style="width: 100%; font-family: monospace;"><?php echo esc_textarea( $affiliate_text ); ?></textarea>
		</div>

		<div class="wps-m-15">
			<label>
				<input type="checkbox" name="wpshadow_link_enabled" value="1" <?php checked( $link_enabled, '1' ); ?> />
				<?php esc_html_e( 'Enable this link in content', 'wpshadow' ); ?>
			</label>
			<p class="wps-m-5">
				<?php esc_html_e( 'If checked, this link will be automatically applied when found in article text.', 'wpshadow' ); ?>
			</p>
		</div>

		<script>
		jQuery(function($) {
			const $affiliateCheckbox = $('input[name="wpshadow_link_is_affiliate"]');
			const $affiliateTextBox = $('#wpshadow-affiliate-text-box');

			function toggleAffiliateText() {
				if ($affiliateCheckbox.is(':checked')) {
					$affiliateTextBox.show();
				} else {
					$affiliateTextBox.hide();
				}
			}

			toggleAffiliateText();
			$affiliateCheckbox.on('change', toggleAffiliateText);
		});
		</script>
		<?php
	}

	/**
	 * Save link metadata.
	 *
	 * @param int $post_id The post ID.
	 */
	public static function save_meta( $post_id ): void {
		if ( ! isset( $_POST['wpshadow_link_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpshadow_link_nonce'] ) ), 'wpshadow_link_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save link URL
		if ( isset( $_POST['wpshadow_link_url'] ) ) {
			update_post_meta( $post_id, 'wpshadow_link_url', esc_url_raw( wp_unslash( $_POST['wpshadow_link_url'] ) ) );
		}

		// Save link display text
		if ( isset( $_POST['wpshadow_link_text'] ) ) {
			update_post_meta( $post_id, 'wpshadow_link_text', sanitize_text_field( wp_unslash( $_POST['wpshadow_link_text'] ) ) );
		}

		// Save link options
		update_post_meta( $post_id, 'wpshadow_link_open_blank', isset( $_POST['wpshadow_link_open_blank'] ) ? '1' : '0' );
		update_post_meta( $post_id, 'wpshadow_link_nofollow', isset( $_POST['wpshadow_link_nofollow'] ) ? '1' : '0' );
		update_post_meta( $post_id, 'wpshadow_link_is_affiliate', isset( $_POST['wpshadow_link_is_affiliate'] ) ? '1' : '0' );

		// Save affiliate text
		if ( isset( $_POST['wpshadow_link_affiliate_text'] ) ) {
			update_post_meta( $post_id, 'wpshadow_link_affiliate_text', sanitize_textarea_field( wp_unslash( $_POST['wpshadow_link_affiliate_text'] ) ) );
		}

		// Save link enabled state
		update_post_meta( $post_id, 'wpshadow_link_enabled', isset( $_POST['wpshadow_link_enabled'] ) ? '1' : '0' );

		// Clear cache when link changes
		wp_cache_delete( 'wpshadow_links_cache' );
	}
}
