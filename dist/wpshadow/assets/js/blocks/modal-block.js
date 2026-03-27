/**
 * WPShadow Modal Block - Editor
 *
 * Gutenberg block for creating modal popups.
 *
 * @package WPShadow
 * @since   1.6034.1530
 */

(function () {
	const { registerBlockType }           = wp.blocks;
	const { InspectorControls, RichText } = wp.blockEditor;
	const { PanelBody, SelectControl, TextControl, ToggleControl, RangeControl } = wp.components;
	const { __ }       = wp.i18n;
	const { Fragment } = wp.element;

	registerBlockType(
		'wpshadow/modal',
		{
			title: __( 'Modal Popup', 'wpshadow' ),
			description: __( 'Create a modal popup that appears when users scroll to this block or click a trigger.', 'wpshadow' ),
			category: 'wpshadow',
			icon: 'welcome-view-site',
			keywords: [__( 'modal', 'wpshadow' ), __( 'popup', 'wpshadow' ), __( 'lightbox', 'wpshadow' )],
			attributes: {
				modalType: {
					type: 'string',
					default: 'inline'
				},
				modalId: {
					type: 'number',
					default: 0
				},
				title: {
					type: 'string',
					default: __( 'Modal Title', 'wpshadow' )
				},
				content: {
					type: 'string',
					default: __( 'Your modal content goes here...', 'wpshadow' )
				},
				width: {
					type: 'number',
					default: 600
				},
				animation: {
					type: 'string',
					default: 'fade'
				},
				triggerText: {
					type: 'string',
					default: __( 'Click to Open', 'wpshadow' )
				},
				showTrigger: {
					type: 'boolean',
					default: false
				},
				overlayClose: {
					type: 'boolean',
					default: true
				},
				showCloseBtn: {
					type: 'boolean',
					default: true
				},
				closeOnEsc: {
					type: 'boolean',
					default: true
				}
			},

			edit: function (props) {
				const { attributes, setAttributes, className } = props;
				const {
					modalType,
					title,
					content,
					width,
					animation,
					triggerText,
					showTrigger,
					overlayClose,
					showCloseBtn,
					closeOnEsc
				} = attributes;

				return (
				< Fragment >
					< InspectorControls >
						< PanelBody title = {__( 'Modal Settings', 'wpshadow' )} initialOpen = {true} >
							< SelectControl
								label     = {__( 'Modal Type', 'wpshadow' )}
								value     = {modalType}
								options   = {[
									{ label: __( 'Inline Content', 'wpshadow' ), value: 'inline' },
									{ label: __( 'Reference Modal CPT', 'wpshadow' ), value: 'cpt' }
									]}
								onChange  = {(value) => setAttributes( { modalType: value } )}
								help      = {__( 'Inline creates a new modal, CPT references an existing modal post.', 'wpshadow' )}
							/ >

							< RangeControl
								label    = {__( 'Width (px)', 'wpshadow' )}
								value    = {width}
								onChange = {(value) => setAttributes( { width: value } )}
								min      = {300}
								max      = {1200}
								step     = {50}
							/ >

							< SelectControl
								label    = {__( 'Animation', 'wpshadow' )}
								value    = {animation}
								options  = {[
									{ label: __( 'Fade', 'wpshadow' ), value: 'fade' },
									{ label: __( 'Slide Up', 'wpshadow' ), value: 'slide-up' },
									{ label: __( 'Slide Down', 'wpshadow' ), value: 'slide-down' },
									{ label: __( 'Zoom', 'wpshadow' ), value: 'zoom' }
									]}
								onChange = {(value) => setAttributes( { animation: value } )}
							/ >
						< / PanelBody >

						< PanelBody title = {__( 'Trigger Settings', 'wpshadow' )} initialOpen = {false} >
							< ToggleControl
								label     = {__( 'Show Visible Trigger Button', 'wpshadow' )}
								checked   = {showTrigger}
								onChange  = {(value) => setAttributes( { showTrigger: value } )}
								help      = {__( 'If off, modal triggers when user scrolls to this block (invisible trigger).', 'wpshadow' )}
							/ >

							{showTrigger && (
								< TextControl
									label    = {__( 'Trigger Button Text', 'wpshadow' )}
									value    = {triggerText}
									onChange = {(value) => setAttributes( { triggerText: value } )}
								/ >
							)}
						< / PanelBody >

						< PanelBody title = {__( 'Close Behavior', 'wpshadow' )} initialOpen = {false} >
							< ToggleControl
								label     = {__( 'Close on Overlay Click', 'wpshadow' )}
								checked   = {overlayClose}
								onChange  = {(value) => setAttributes( { overlayClose: value } )}
							/ >

							< ToggleControl
								label    = {__( 'Show Close Button', 'wpshadow' )}
								checked  = {showCloseBtn}
								onChange = {(value) => setAttributes( { showCloseBtn: value } )}
							/ >

							< ToggleControl
								label    = {__( 'Close on ESC Key', 'wpshadow' )}
								checked  = {closeOnEsc}
								onChange = {(value) => setAttributes( { closeOnEsc: value } )}
							/ >
						< / PanelBody >
					< / InspectorControls >

					< div className      = {className + ' wpshadow-modal-block-editor'} style = {{
						padding: '30px',
						background: '#f0f0f1',
						border: '2px dashed #007cba',
						borderRadius: '8px',
						textAlign: 'center'
						}} >
						< div style      = {{
							background: '#fff',
							padding: '20px',
							borderRadius: '4px',
							marginBottom: '15px'
							}} >
							< span style = {{
								display: 'inline-block',
								padding: '5px 15px',
								background: '#007cba',
								color: '#fff',
								borderRadius: '4px',
								fontSize: '12px',
								marginBottom: '15px'
								}} >
								{__( 'Modal Popup', 'wpshadow' )}
							< / span >

							< RichText
								tagName     = "h3"
								value       = {title}
								onChange    = {(value) => setAttributes( { title: value } )}
								placeholder = {__( 'Modal Title', 'wpshadow' )}
								style       = {{
									margin: '0 0 15px 0',
									fontSize: '20px',
									fontWeight: '600'
									}}
							/ >

							< RichText
								tagName     = "div"
								multiline   = "p"
								value       = {content}
								onChange    = {(value) => setAttributes( { content: value } )}
								placeholder = {__( 'Modal content...', 'wpshadow' )}
								style       = {{
									textAlign: 'left',
									color: '#666'
									}}
							/ >
						< / div >

						< div style = {{
							fontSize: '13px',
							color: '#666',
							marginTop: '10px'
							}} >
							{showTrigger ? (
								< span >
									{__( '🎯 Trigger: ', 'wpshadow' )}
									< strong > {__( 'Visible Button', 'wpshadow' )} < / strong >
									{' "' + triggerText + '"'}
								< / span >
							) : (
								< span >
									{__( '🎯 Trigger: ', 'wpshadow' )}
									< strong > {__( 'Scroll to This Block', 'wpshadow' )} < / strong >
									{' (' + __( 'invisible', 'wpshadow' ) + ')'}
								< / span >
							)}
							< br / >
							{__( '✨ Animation: ', 'wpshadow' )} < strong > {animation} < / strong > |
							{__( ' Width: ', 'wpshadow' )} < strong > {width}px < / strong >
						< / div >
					< / div >
				< / Fragment >
				);
			},

			save: function () {
				// Dynamic block - rendered server-side
				return null;
			}
		}
	);
})();
