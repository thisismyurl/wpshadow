<?php
/**
 * Help card: Treatments
 *
 * @package WPShadow
 */

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'treatments',
	'order'       => 30,
	'section'     => 'resources',
	'width'       => 'half',
	'title'       => __( 'Applying Treatments', 'wpshadow' ),
	'description' => __( 'Learn how to safely apply fixes to your site with one-click treatments and undo support.', 'wpshadow' ),
	'icon'        => 'dashicons-admin-tools',
	'url'         => UTM_Link_Manager::kb_link( 'treatments', 'help_page' ),
	'video'       => 'https://wpshadow.com/academy/treatments?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_treatments',
);
