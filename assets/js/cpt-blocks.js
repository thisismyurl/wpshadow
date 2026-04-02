/**
 * WPShadow Custom Post Type Blocks
 *
 * Gutenberg blocks for all WPShadow custom post types.
 *
 * @package WPShadow
 * @since   1.6033.1600
 */

(function(wp) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { TextControl, SelectControl, ToggleControl, RangeControl, PanelBody } = wp.components;
	const { InspectorControls } = wp.blockEditor || wp.editor;
	const { ServerSideRender } = wp.serverSideRender || wp.components;
	const { createElement: el, Fragment } = wp.element;
	const { __ } = wp.i18n;

	// Block category
	const blockCategory = 'wpshadow-cpt';

	// Helper function to create taxonomy options
	function getTaxonomyOptions(taxonomyKey) {
		const taxonomies = wpshadowCPTBlocks.taxonomies || {};
		const taxonomy = taxonomies[taxonomyKey];
		
		if (!taxonomy || !taxonomy.terms) {
			return [{ label: __('All', 'wpshadow'), value: '' }];
		}

		const options = [{ label: __('All', 'wpshadow'), value: '' }];
		taxonomy.terms.forEach(term => {
			options.push({ label: term.label, value: term.value });
		});

		return options;
	}

	// Layout options (reusable)
	const layoutOptions = [
		{ label: __('Grid', 'wpshadow'), value: 'grid' },
		{ label: __('List', 'wpshadow'), value: 'list' }
	];

	/**
	 * Testimonials Block
	 */
	registerBlockType('wpshadow/testimonials', {
		title: __('Testimonials', 'wpshadow'),
		description: __('Display customer testimonials and reviews', 'wpshadow'),
		icon: 'testimonial',
		category: blockCategory,
		keywords: [__('testimonials', 'wpshadow'), __('reviews', 'wpshadow'), __('feedback', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 3 },
			category: { type: 'string', default: '' },
			rating: { type: 'string', default: '' },
			layout: { type: 'string', default: 'grid' },
			showExcerpt: { type: 'boolean', default: true },
			showRating: { type: 'boolean', default: true }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Testimonial Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Testimonials', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 12
						}),
						el(SelectControl, {
							label: __('Category', 'wpshadow'),
							value: attributes.category,
							options: getTaxonomyOptions('wps_testimonial_category'),
							onChange: (val) => setAttributes({ category: val })
						}),
						el(SelectControl, {
							label: __('Rating', 'wpshadow'),
							value: attributes.rating,
							options: getTaxonomyOptions('wps_rating'),
							onChange: (val) => setAttributes({ rating: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(ToggleControl, {
							label: __('Show Excerpt', 'wpshadow'),
							checked: attributes.showExcerpt,
							onChange: (val) => setAttributes({ showExcerpt: val })
						}),
						el(ToggleControl, {
							label: __('Show Rating', 'wpshadow'),
							checked: attributes.showRating,
							onChange: (val) => setAttributes({ showRating: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/testimonials',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Team Members Block
	 */
	registerBlockType('wpshadow/team-members', {
		title: __('Team Members', 'wpshadow'),
		description: __('Display team member profiles', 'wpshadow'),
		icon: 'groups',
		category: blockCategory,
		keywords: [__('team', 'wpshadow'), __('staff', 'wpshadow'), __('employees', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 6 },
			department: { type: 'string', default: '' },
			role: { type: 'string', default: '' },
			layout: { type: 'string', default: 'grid' },
			columns: { type: 'number', default: 3 },
			showExcerpt: { type: 'boolean', default: true }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Team Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Members', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 24
						}),
						el(SelectControl, {
							label: __('Department', 'wpshadow'),
							value: attributes.department,
							options: getTaxonomyOptions('wps_department'),
							onChange: (val) => setAttributes({ department: val })
						}),
						el(SelectControl, {
							label: __('Role', 'wpshadow'),
							value: attributes.role,
							options: getTaxonomyOptions('wps_team_role'),
							onChange: (val) => setAttributes({ role: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(RangeControl, {
							label: __('Columns', 'wpshadow'),
							value: attributes.columns,
							onChange: (val) => setAttributes({ columns: val }),
							min: 1,
							max: 6
						}),
						el(ToggleControl, {
							label: __('Show Bio Excerpt', 'wpshadow'),
							checked: attributes.showExcerpt,
							onChange: (val) => setAttributes({ showExcerpt: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/team-members',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Portfolio Block
	 */
	registerBlockType('wpshadow/portfolio', {
		title: __('Portfolio', 'wpshadow'),
		description: __('Display portfolio items and projects', 'wpshadow'),
		icon: 'portfolio',
		category: blockCategory,
		keywords: [__('portfolio', 'wpshadow'), __('projects', 'wpshadow'), __('work', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 6 },
			category: { type: 'string', default: '' },
			skill: { type: 'string', default: '' },
			layout: { type: 'string', default: 'grid' },
			columns: { type: 'number', default: 3 },
			showExcerpt: { type: 'boolean', default: false }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Portfolio Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Items', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 24
						}),
						el(SelectControl, {
							label: __('Category', 'wpshadow'),
							value: attributes.category,
							options: getTaxonomyOptions('wps_portfolio_category'),
							onChange: (val) => setAttributes({ category: val })
						}),
						el(SelectControl, {
							label: __('Skill', 'wpshadow'),
							value: attributes.skill,
							options: getTaxonomyOptions('wps_skill'),
							onChange: (val) => setAttributes({ skill: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(RangeControl, {
							label: __('Columns', 'wpshadow'),
							value: attributes.columns,
							onChange: (val) => setAttributes({ columns: val }),
							min: 1,
							max: 6
						}),
						el(ToggleControl, {
							label: __('Show Description', 'wpshadow'),
							checked: attributes.showExcerpt,
							onChange: (val) => setAttributes({ showExcerpt: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/portfolio',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Events Block
	 */
	registerBlockType('wpshadow/events', {
		title: __('Events', 'wpshadow'),
		description: __('Display upcoming events and webinars', 'wpshadow'),
		icon: 'calendar-alt',
		category: blockCategory,
		keywords: [__('events', 'wpshadow'), __('calendar', 'wpshadow'), __('webinar', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 5 },
			category: { type: 'string', default: '' },
			eventType: { type: 'string', default: '' },
			layout: { type: 'string', default: 'list' },
			showExcerpt: { type: 'boolean', default: true },
			upcoming: { type: 'boolean', default: true }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Event Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Events', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 20
						}),
						el(SelectControl, {
							label: __('Category', 'wpshadow'),
							value: attributes.category,
							options: getTaxonomyOptions('wps_event_category'),
							onChange: (val) => setAttributes({ category: val })
						}),
						el(SelectControl, {
							label: __('Event Type', 'wpshadow'),
							value: attributes.eventType,
							options: getTaxonomyOptions('wps_event_type'),
							onChange: (val) => setAttributes({ eventType: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(ToggleControl, {
							label: __('Show Description', 'wpshadow'),
							checked: attributes.showExcerpt,
							onChange: (val) => setAttributes({ showExcerpt: val })
						}),
						el(ToggleControl, {
							label: __('Upcoming Only', 'wpshadow'),
							checked: attributes.upcoming,
							onChange: (val) => setAttributes({ upcoming: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/events',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Resources Block
	 */
	registerBlockType('wpshadow/resources', {
		title: __('Resources', 'wpshadow'),
		description: __('Display downloadable resources', 'wpshadow'),
		icon: 'download',
		category: blockCategory,
		keywords: [__('resources', 'wpshadow'), __('downloads', 'wpshadow'), __('files', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 6 },
			type: { type: 'string', default: '' },
			category: { type: 'string', default: '' },
			layout: { type: 'string', default: 'list' },
			showExcerpt: { type: 'boolean', default: true }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Resource Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Resources', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 20
						}),
						el(SelectControl, {
							label: __('Type', 'wpshadow'),
							value: attributes.type,
							options: getTaxonomyOptions('wps_resource_type'),
							onChange: (val) => setAttributes({ type: val })
						}),
						el(SelectControl, {
							label: __('Category', 'wpshadow'),
							value: attributes.category,
							options: getTaxonomyOptions('wps_resource_category'),
							onChange: (val) => setAttributes({ category: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(ToggleControl, {
							label: __('Show Description', 'wpshadow'),
							checked: attributes.showExcerpt,
							onChange: (val) => setAttributes({ showExcerpt: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/resources',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Case Studies Block
	 */
	registerBlockType('wpshadow/case-studies', {
		title: __('Case Studies', 'wpshadow'),
		description: __('Display customer success stories', 'wpshadow'),
		icon: 'analytics',
		category: blockCategory,
		keywords: [__('case study', 'wpshadow'), __('success', 'wpshadow'), __('stories', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 3 },
			industry: { type: 'string', default: '' },
			solution: { type: 'string', default: '' },
			layout: { type: 'string', default: 'grid' },
			columns: { type: 'number', default: 2 },
			showExcerpt: { type: 'boolean', default: true }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Case Study Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Case Studies', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 12
						}),
						el(SelectControl, {
							label: __('Industry', 'wpshadow'),
							value: attributes.industry,
							options: getTaxonomyOptions('wps_industry'),
							onChange: (val) => setAttributes({ industry: val })
						}),
						el(SelectControl, {
							label: __('Solution', 'wpshadow'),
							value: attributes.solution,
							options: getTaxonomyOptions('wps_solution'),
							onChange: (val) => setAttributes({ solution: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(RangeControl, {
							label: __('Columns', 'wpshadow'),
							value: attributes.columns,
							onChange: (val) => setAttributes({ columns: val }),
							min: 1,
							max: 4
						}),
						el(ToggleControl, {
							label: __('Show Description', 'wpshadow'),
							checked: attributes.showExcerpt,
							onChange: (val) => setAttributes({ showExcerpt: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/case-studies',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Services Block
	 */
	registerBlockType('wpshadow/services', {
		title: __('Services', 'wpshadow'),
		description: __('Display business services', 'wpshadow'),
		icon: 'admin-tools',
		category: blockCategory,
		keywords: [__('services', 'wpshadow'), __('offerings', 'wpshadow'), __('products', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 6 },
			category: { type: 'string', default: '' },
			layout: { type: 'string', default: 'grid' },
			columns: { type: 'number', default: 3 },
			showExcerpt: { type: 'boolean', default: true }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Service Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Services', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 12
						}),
						el(SelectControl, {
							label: __('Category', 'wpshadow'),
							value: attributes.category,
							options: getTaxonomyOptions('wps_service_category'),
							onChange: (val) => setAttributes({ category: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(RangeControl, {
							label: __('Columns', 'wpshadow'),
							value: attributes.columns,
							onChange: (val) => setAttributes({ columns: val }),
							min: 1,
							max: 6
						}),
						el(ToggleControl, {
							label: __('Show Description', 'wpshadow'),
							checked: attributes.showExcerpt,
							onChange: (val) => setAttributes({ showExcerpt: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/services',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Locations Block
	 */
	registerBlockType('wpshadow/locations', {
		title: __('Locations', 'wpshadow'),
		description: __('Display business locations', 'wpshadow'),
		icon: 'location',
		category: blockCategory,
		keywords: [__('locations', 'wpshadow'), __('branches', 'wpshadow'), __('offices', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: -1 },
			locationType: { type: 'string', default: '' },
			layout: { type: 'string', default: 'list' }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Location Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Locations (-1 for all)', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: -1,
							max: 20
						}),
						el(SelectControl, {
							label: __('Location Type', 'wpshadow'),
							value: attributes.locationType,
							options: getTaxonomyOptions('wps_location_type'),
							onChange: (val) => setAttributes({ locationType: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/locations',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

	/**
	 * Documentation Block
	 */
	registerBlockType('wpshadow/documentation', {
		title: __('Documentation', 'wpshadow'),
		description: __('Display knowledge base articles', 'wpshadow'),
		icon: 'book',
		category: blockCategory,
		keywords: [__('documentation', 'wpshadow'), __('docs', 'wpshadow'), __('help', 'wpshadow')],
		attributes: {
			count: { type: 'number', default: 10 },
			category: { type: 'string', default: '' },
			version: { type: 'string', default: '' },
			layout: { type: 'string', default: 'list' },
			hierarchical: { type: 'boolean', default: false }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Documentation Settings', 'wpshadow'), initialOpen: true },
						el(RangeControl, {
							label: __('Number of Articles', 'wpshadow'),
							value: attributes.count,
							onChange: (val) => setAttributes({ count: val }),
							min: 1,
							max: 50
						}),
						el(SelectControl, {
							label: __('Category', 'wpshadow'),
							value: attributes.category,
							options: getTaxonomyOptions('wps_doc_category'),
							onChange: (val) => setAttributes({ category: val })
						}),
						el(SelectControl, {
							label: __('Version', 'wpshadow'),
							value: attributes.version,
							options: getTaxonomyOptions('wps_doc_version'),
							onChange: (val) => setAttributes({ version: val })
						}),
						el(SelectControl, {
							label: __('Layout', 'wpshadow'),
							value: attributes.layout,
							options: layoutOptions,
							onChange: (val) => setAttributes({ layout: val })
						}),
						el(ToggleControl, {
							label: __('Show Hierarchy', 'wpshadow'),
							checked: attributes.hierarchical,
							onChange: (val) => setAttributes({ hierarchical: val })
						})
					)
				),
				el('div', { className: 'wpshadow-block-preview' },
					el(ServerSideRender, {
						block: 'wpshadow/documentation',
						attributes: attributes
					})
				)
			);
		},
		save: () => null
	});

})(window.wp);
