<?php
/**
 * Wix Terminology
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'platform'       => 'wix',
	'label'          => __( 'Wix', 'wpshadow' ),
	'terms'          => array(
		'post'           => __( 'Blog Post', 'wpshadow' ),
		'page'           => __( 'Site Page', 'wpshadow' ),
		'plugin'         => __( 'App', 'wpshadow' ),
		'theme'          => __( 'Template', 'wpshadow' ),
		'widget'         => __( 'Widget', 'wpshadow' ),
		'menu'           => __( 'Menu', 'wpshadow' ),
		'category'       => __( 'Category', 'wpshadow' ),
		'tag'            => __( 'Label', 'wpshadow' ),
		'media_library'  => __( 'Media Manager', 'wpshadow' ),
		'customize'      => __( 'Design', 'wpshadow' ),
		'dashboard'      => __( 'Dashboard', 'wpshadow' ),
		'settings'       => __( 'Settings', 'wpshadow' ),
		'editor'         => __( 'Editor', 'wpshadow' ),
		'publish'        => __( 'Publish', 'wpshadow' ),
		'draft'          => __( 'Draft', 'wpshadow' ),
		'trash'          => __( 'Trash', 'wpshadow' ),
		'permalink'      => __( 'Page URL', 'wpshadow' ),
		'excerpt'        => __( 'Summary', 'wpshadow' ),
		'featured_image' => __( 'Featured Image', 'wpshadow' ),
		'author'         => __( 'Author', 'wpshadow' ),
		'comment'        => __( 'Comment', 'wpshadow' ),
	),
	'kb_article'     => \WPShadow\Core\UTM_Link_Manager::kb_link( 'wix-to-wordpress', 'onboarding' ),
	'training_video' => \WPShadow\Core\UTM_Link_Manager::academy_link( 'wix-to-wordpress', 'onboarding' ),
);
