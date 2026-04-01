<?php
/**
 * Register Custom Block Category
 *
 * Creates a dedicated block category for WPShadow CPT blocks.
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

/**
 * Register WPShadow CPT block category.
 *
 * @param  array  $categories Existing block categories.
 * @param  object $post       Current post object.
 * @return array Modified block categories.
 */
function wpshadow_register_cpt_block_category( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'wpshadow-cpt',
				'title' => __( 'WPShadow Content', 'wpshadow' ),
				'icon'  => 'shield-alt',
			),
		)
	);
}
add_filter( 'block_categories_all', 'wpshadow_register_cpt_block_category', 10, 2 );
