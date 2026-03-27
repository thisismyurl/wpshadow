/**
 * Register Block Styles for WPShadow CPT Blocks
 *
 * Provides alternative visual styles for each block that users can toggle.
 *
 * @package WPShadow
 * @since   1.6034.1200
 */

(function () {
	'use strict';

	const { registerBlockStyle } = wp.blocks;
	const { __ }                 = wp.i18n;

	// Wait for DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener( 'DOMContentLoaded', registerAllBlockStyles );
	} else {
		registerAllBlockStyles();
	}

	function registerAllBlockStyles() {
		// Testimonials Block Styles
		registerBlockStyle(
			'wpshadow/testimonials',
			{
				name: 'card',
				label: __( 'Card Style', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/testimonials',
			{
				name: 'minimal',
				label: __( 'Minimal', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/testimonials',
			{
				name: 'quote-bubble',
				label: __( 'Quote Bubbles', 'wpshadow' )
			}
		);

		// Team Members Block Styles
		registerBlockStyle(
			'wpshadow/team-members',
			{
				name: 'card',
				label: __( 'Card Style', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/team-members',
			{
				name: 'overlay',
				label: __( 'Image Overlay', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/team-members',
			{
				name: 'circle',
				label: __( 'Circular Photos', 'wpshadow' )
			}
		);

		// Portfolio Block Styles
		registerBlockStyle(
			'wpshadow/portfolio',
			{
				name: 'grid',
				label: __( 'Grid Style', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/portfolio',
			{
				name: 'masonry',
				label: __( 'Masonry', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/portfolio',
			{
				name: 'hover-zoom',
				label: __( 'Hover Zoom', 'wpshadow' )
			}
		);

		// Events Block Styles
		registerBlockStyle(
			'wpshadow/events',
			{
				name: 'list',
				label: __( 'List Style', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/events',
			{
				name: 'timeline',
				label: __( 'Timeline', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/events',
			{
				name: 'calendar',
				label: __( 'Calendar Style', 'wpshadow' )
			}
		);

		// Resources Block Styles
		registerBlockStyle(
			'wpshadow/resources',
			{
				name: 'card',
				label: __( 'Card Style', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/resources',
			{
				name: 'compact',
				label: __( 'Compact List', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/resources',
			{
				name: 'featured',
				label: __( 'Featured', 'wpshadow' )
			}
		);

		// Case Studies Block Styles
		registerBlockStyle(
			'wpshadow/case-studies',
			{
				name: 'default',
				label: __( 'Default', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/case-studies',
			{
				name: 'metrics-focused',
				label: __( 'Metrics Focused', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/case-studies',
			{
				name: 'split-layout',
				label: __( 'Split Layout', 'wpshadow' )
			}
		);

		// Services Block Styles
		registerBlockStyle(
			'wpshadow/services',
			{
				name: 'card',
				label: __( 'Card Style', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/services',
			{
				name: 'pricing-table',
				label: __( 'Pricing Table', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/services',
			{
				name: 'icon-boxes',
				label: __( 'Icon Boxes', 'wpshadow' )
			}
		);

		// Locations Block Styles
		registerBlockStyle(
			'wpshadow/locations',
			{
				name: 'list',
				label: __( 'List Style', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/locations',
			{
				name: 'card',
				label: __( 'Card Style', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/locations',
			{
				name: 'map-view',
				label: __( 'Map View', 'wpshadow' )
			}
		);

		// Documentation Block Styles
		registerBlockStyle(
			'wpshadow/documentation',
			{
				name: 'default',
				label: __( 'Default', 'wpshadow' ),
				isDefault: true
			}
		);

		registerBlockStyle(
			'wpshadow/documentation',
			{
				name: 'sidebar-toc',
				label: __( 'With Sidebar TOC', 'wpshadow' )
			}
		);

		registerBlockStyle(
			'wpshadow/documentation',
			{
				name: 'accordion',
				label: __( 'Accordion Style', 'wpshadow' )
			}
		);
	}
})();