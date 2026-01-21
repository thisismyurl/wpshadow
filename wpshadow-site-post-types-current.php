<?php
/**
 * Custom Post Types for WPShadow.com
 *
 * @package WPShadow_Site
 */

namespace WPShadow_Site;

class Post_Types {
    
    /**
     * Initialize post types
     */
    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_product' ] );
        add_action( 'init', [ __CLASS__, 'register_kb_article' ] );
        add_action( 'init', [ __CLASS__, 'register_success_story' ] );
        add_action( 'init', [ __CLASS__, 'register_changelog' ] );
        
        // Ensure excerpt is shown in block editor for KB articles
        add_filter( 'rest_request_after_callbacks', [ __CLASS__, 'ensure_excerpt_visibility' ], 10, 3 );
    }
    
    /**
     * Ensure excerpt field is visible in block editor
     */
    public static function ensure_excerpt_visibility( $response, $server, $request ) {
        if ( 'kb-articles' === $request->get_param( 'rest_base' ) || false !== strpos( $request->get_route(), 'kb-articles' ) ) {
            if ( isset( $response->data['excerpt'] ) ) {
                $response->data['excerpt'] = $response->data['excerpt'];
            }
        }
        return $response;
    }
    
    /**
     * Register Product CPT (Guardian, Vault, Academy, Pro)
     */
    public static function register_product() {
        $labels = [
            'name'               => __( 'Products', 'wpshadow-site' ),
            'singular_name'      => __( 'Product', 'wpshadow-site' ),
            'menu_name'          => __( 'Products', 'wpshadow-site' ),
            'add_new'            => __( 'Add New', 'wpshadow-site' ),
            'add_new_item'       => __( 'Add New Product', 'wpshadow-site' ),
            'edit_item'          => __( 'Edit Product', 'wpshadow-site' ),
            'new_item'           => __( 'New Product', 'wpshadow-site' ),
            'view_item'          => __( 'View Product', 'wpshadow-site' ),
            'search_items'       => __( 'Search Products', 'wpshadow-site' ),
            'not_found'          => __( 'No products found', 'wpshadow-site' ),
            'not_found_in_trash' => __( 'No products found in trash', 'wpshadow-site' ),
        ];
        
        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => [ 'slug' => 'products' ],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-cart',
            'show_in_rest'        => true,
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ],
        ];
        
        register_post_type( 'wpshadow_product', $args );
    }
    
    /**
     * Register KB Article CPT
     */
    public static function register_kb_article() {
        $labels = [
            'name'               => __( 'KB Articles', 'wpshadow-site' ),
            'singular_name'      => __( 'KB Article', 'wpshadow-site' ),
            'menu_name'          => __( 'Knowledge Base', 'wpshadow-site' ),
            'add_new'            => __( 'Add New', 'wpshadow-site' ),
            'add_new_item'       => __( 'Add New Article', 'wpshadow-site' ),
            'edit_item'          => __( 'Edit Article', 'wpshadow-site' ),
            'new_item'           => __( 'New Article', 'wpshadow-site' ),
            'view_item'          => __( 'View Article', 'wpshadow-site' ),
            'search_items'       => __( 'Search Articles', 'wpshadow-site' ),
            'not_found'          => __( 'No articles found', 'wpshadow-site' ),
            'not_found_in_trash' => __( 'No articles found in trash', 'wpshadow-site' ),
        ];
        
        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => [ 'slug' => 'kb' ],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => true,
            'menu_position'       => 21,
            'menu_icon'           => 'dashicons-book',
            'show_in_rest'        => [
                'show_in_rest'       => true,
                'rest_base'          => 'kb-articles',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
            ],
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'comments', 'custom-fields' ],
        ];
        
        register_post_type( 'wpshadow_kb', $args );
    }
    
    /**
     * Register Success Story CPT
     */
    public static function register_success_story() {
        $labels = [
            'name'               => __( 'Success Stories', 'wpshadow-site' ),
            'singular_name'      => __( 'Success Story', 'wpshadow-site' ),
            'menu_name'          => __( 'Success Stories', 'wpshadow-site' ),
            'add_new'            => __( 'Add New', 'wpshadow-site' ),
            'add_new_item'       => __( 'Add New Story', 'wpshadow-site' ),
            'edit_item'          => __( 'Edit Story', 'wpshadow-site' ),
            'new_item'           => __( 'New Story', 'wpshadow-site' ),
            'view_item'          => __( 'View Story', 'wpshadow-site' ),
            'search_items'       => __( 'Search Stories', 'wpshadow-site' ),
            'not_found'          => __( 'No stories found', 'wpshadow-site' ),
            'not_found_in_trash' => __( 'No stories found in trash', 'wpshadow-site' ),
        ];
        
        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => [ 'slug' => 'success-stories' ],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 22,
            'menu_icon'           => 'dashicons-awards',
            'show_in_rest'        => true,
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
        ];
        
        register_post_type( 'wpshadow_story', $args );
    }
    
    /**
     * Register Changelog CPT
     */
    public static function register_changelog() {
        $labels = [
            'name'               => __( 'Changelogs', 'wpshadow-site' ),
            'singular_name'      => __( 'Changelog', 'wpshadow-site' ),
            'menu_name'          => __( 'Changelog', 'wpshadow-site' ),
            'add_new'            => __( 'Add New', 'wpshadow-site' ),
            'add_new_item'       => __( 'Add New Changelog', 'wpshadow-site' ),
            'edit_item'          => __( 'Edit Changelog', 'wpshadow-site' ),
            'new_item'           => __( 'New Changelog', 'wpshadow-site' ),
            'view_item'          => __( 'View Changelog', 'wpshadow-site' ),
            'search_items'       => __( 'Search Changelogs', 'wpshadow-site' ),
            'not_found'          => __( 'No changelogs found', 'wpshadow-site' ),
            'not_found_in_trash' => __( 'No changelogs found in trash', 'wpshadow-site' ),
        ];
        
        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => [ 'slug' => 'changelog' ],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 23,
            'menu_icon'           => 'dashicons-list-view',
            'show_in_rest'        => true,
            'supports'            => [ 'title', 'editor', 'revisions', 'page-attributes' ],
        ];
        
        register_post_type( 'wpshadow_changelog', $args );
    }
}
