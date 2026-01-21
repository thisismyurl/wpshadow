(function(wp){
	const { registerBlockType } = wp.blocks;
	const { TextControl, ToggleControl, PanelBody } = wp.components;
	const { InspectorControls } = wp.blockEditor || wp.editor;
	const { ServerSideRender } = wp.serverSideRender || wp.components;
	const { createElement: el, Fragment } = wp.element;

	registerBlockType('wpshadow/course-list', {
		title: 'WPShadow Course List',
		icon: 'welcome-learn-more',
		category: 'widgets',
		attributes: {
			ids: { type: 'string', default: '' },
			category: { type: 'string', default: '' },
			showExcerpt: { type: 'boolean', default: true }
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;
			return (
				el(
					Fragment,
					null,
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: 'Course Settings', initialOpen: true },
							el(TextControl, {
								label: 'Course IDs (comma-separated)',
								help: 'Leave blank to pull by category or show latest.',
								value: attributes.ids,
								onChange: (val) => setAttributes({ ids: val })
							}),
							el(TextControl, {
								label: 'Category slug (optional)',
								help: 'Uses course_category taxonomy; ignored when IDs are set.',
								value: attributes.category,
								onChange: (val) => setAttributes({ category: val })
							}),
							el(ToggleControl, {
								label: 'Show excerpts',
								checked: !!attributes.showExcerpt,
								onChange: (val) => setAttributes({ showExcerpt: val })
							})
						)
					),
					el('div', { className: 'wpshadow-course-block-editor', style: { padding: '20px', border: '1px dashed #ccc', borderRadius: '4px', background: '#f9f9f9' } },
						el(ServerSideRender, {
							block: 'wpshadow/course-list',
							attributes: attributes
						})
					)
				)
			);
		},
		save: () => null
	});
})(window.wp);
