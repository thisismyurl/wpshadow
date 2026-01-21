/**
 * KB Cloud Integration Block - Editor Component
 * 
 * Provides Block Editor UI for the cloud integration block.
 * Shows live preview of cloud status data.
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, TextControl, ColorPalette } = wp.components;
const { Fragment } = wp.element;
const { __ } = wp.i18n;

registerBlockType('wpshadow/kb-cloud-integration', {
	title: __('Cloud Integration Status', 'wpshadow'),
	description: __('Show site backup status and cloud integration for connected users', 'wpshadow'),
	category: 'wpshadow',
	icon: '☁️',
	attributes: {
		title: {
			type: 'string',
			default: 'Your Site Status',
		},
		showLastBackup: {
			type: 'boolean',
			default: true,
		},
		showBackupButton: {
			type: 'boolean',
			default: true,
		},
		backgroundColor: {
			type: 'string',
			default: '#f5f5f5',
		},
	},

	edit({ attributes, setAttributes }) {
		const { title, showLastBackup, showBackupButton, backgroundColor } = attributes;

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={__('Block Settings', 'wpshadow')} initialOpen={true}>
						<TextControl
							label={__('Block Title', 'wpshadow')}
							value={title}
							onChange={(value) => setAttributes({ title: value })}
						/>
						<ToggleControl
							label={__('Show Last Backup Time', 'wpshadow')}
							checked={showLastBackup}
							onChange={(value) => setAttributes({ showLastBackup: value })}
						/>
						<ToggleControl
							label={__('Show Backup Button', 'wpshadow')}
							checked={showBackupButton}
							onChange={(value) => setAttributes({ showBackupButton: value })}
						/>
						<div style={{ marginTop: '15px' }}>
							<label>{__('Background Color', 'wpshadow')}</label>
							<input
								type="color"
								value={backgroundColor}
								onChange={(e) => setAttributes({ backgroundColor: e.target.value })}
								style={{ marginTop: '5px', width: '100%', height: '40px', cursor: 'pointer' }}
							/>
						</div>
					</PanelBody>
				</InspectorControls>

				<div
					style={{
						backgroundColor: backgroundColor,
						padding: '20px',
						borderRadius: '8px',
						marginTop: '15px',
						marginBottom: '15px',
						border: '1px solid #ddd',
					}}
				>
					<h3 style={{ marginTop: 0 }}>{title}</h3>
					<p>
						<em>
							{__('This block shows:', 'wpshadow')}
						</em>
					</p>
					<ul>
						<li>
							{showLastBackup ? '✓' : '○'} {__('Last backup timestamp', 'wpshadow')}
						</li>
						<li>
							{showBackupButton ? '✓' : '○'} {__('One-click backup button', 'wpshadow')}
						</li>
						<li>✓ {__('Cloud connection status', 'wpshadow')}</li>
						<li>✓ {__('Link to dashboard', 'wpshadow')}</li>
					</ul>
					<p style={{ fontSize: '0.9em', color: '#666' }}>
						{__('(For logged-in, connected users only)', 'wpshadow')}
					</p>
				</div>
			</Fragment>
		);
	},

	save() {
		// All rendering is done server-side
		return null;
	},
});
