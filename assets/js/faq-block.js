(function(wp){
	const { registerBlockType } = wp.blocks;
	const { TextControl, ToggleControl, PanelBody } = wp.components;
	const { InspectorControls } = wp.blockEditor || wp.editor;
	const { ServerSideRender } = wp.serverSideRender || wp.components;
	const { createElement: el, Fragment } = wp.element;

	registerBlockType('wpshadow/faq-list', {
		title: 'WPShadow FAQ List',
		icon: 'editor-help',
		category: 'widgets',
		attributes: {
			ids: { type: 'string', default: '' },
			topic: { type: 'string', default: '' },
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
							{ title: 'FAQ Settings', initialOpen: true },
							el(TextControl, {
								label: 'FAQ IDs (comma-separated)',
								help: 'Leave blank to pull by topic or show latest.',
								value: attributes.ids,
								onChange: (val) => setAttributes({ ids: val })
							}),
							el(TextControl, {
								label: 'Topic slug (optional)',
								help: 'Uses faq_topic taxonomy; ignored when IDs are set.',
								value: attributes.topic,
								onChange: (val) => setAttributes({ topic: val })
							}),
							el(ToggleControl, {
								label: 'Show excerpts',
								checked: !!attributes.showExcerpt,
								onChange: (val) => setAttributes({ showExcerpt: val })
							})
						)
					),
					el('div', { className: 'wpshadow-faq-block-editor', style: { padding: '20px', border: '1px dashed #ccc', borderRadius: '4px', background: '#f9f9f9' } },
						el(ServerSideRender, {
							block: 'wpshadow/faq-list',
							attributes: attributes
						})
					)
				)
			);
		},
		save: () => null
	});
})(window.wp);
