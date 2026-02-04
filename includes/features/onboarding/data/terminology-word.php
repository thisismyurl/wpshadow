<?php
/**
 * Microsoft Word Terminology
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'platform'       => 'word',
	'label'          => __( 'Microsoft Word', 'wpshadow' ),
	'terms'          => array(
		'post'           => __( 'Document', 'wpshadow' ),
		'page'           => __( 'Page', 'wpshadow' ),
		'plugin'         => __( 'Add-in', 'wpshadow' ),
		'theme'          => __( 'Template', 'wpshadow' ),
		'widget'         => __( 'Widget', 'wpshadow' ),
		'menu'           => __( 'Navigation', 'wpshadow' ),
		'category'       => __( 'Folder', 'wpshadow' ),
		'tag'            => __( 'Tag', 'wpshadow' ),
		'media_library'  => __( 'Pictures', 'wpshadow' ),
		'customize'      => __( 'Design', 'wpshadow' ),
		'dashboard'      => __( 'Home', 'wpshadow' ),
		'settings'       => __( 'Options', 'wpshadow' ),
		'editor'         => __( 'Document Editor', 'wpshadow' ),
		'publish'        => __( 'Save & Share', 'wpshadow' ),
		'draft'          => __( 'Draft', 'wpshadow' ),
		'trash'          => __( 'Deleted Items', 'wpshadow' ),
		'permalink'      => __( 'Link', 'wpshadow' ),
		'excerpt'        => __( 'Summary', 'wpshadow' ),
		'featured_image' => __( 'Cover Image', 'wpshadow' ),
		'author'         => __( 'Author', 'wpshadow' ),
		'comment'        => __( 'Comment', 'wpshadow' ),
	),
	'kb_article'     => \WPShadow\Core\UTM_Link_Manager::kb_link( 'word-to-wordpress', 'onboarding' ),
	'training_video' => \WPShadow\Core\UTM_Link_Manager::academy_link( 'word-to-wordpress', 'onboarding' ),
);
