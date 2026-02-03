/**
 * CPT Block Presets
 *
 * Handles saving and loading block configurations as presets.
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      1.6034.1200
 */

(function(wp) {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { PanelBody, Button, TextControl, SelectControl, Notice } = wp.components;
    const { Component } = wp.element;
    const { select, dispatch } = wp.data;
    const { __ } = wp.i18n;

    /**
     * Block Presets Sidebar Component
     */
    class BlockPresetsSidebar extends Component {
        constructor() {
            super(...arguments);
            this.state = {
                presets: [],
                presetName: '',
                selectedBlock: null,
                saving: false,
                loading: false,
                message: null
            };

            this.loadPresets = this.loadPresets.bind(this);
            this.savePreset = this.savePreset.bind(this);
            this.loadPreset = this.loadPreset.bind(this);
            this.deletePreset = this.deletePreset.bind(this);
        }

        componentDidMount() {
            this.loadPresets();
            
            // Subscribe to block selection changes
            wp.data.subscribe(() => {
                const selectedBlock = select('core/block-editor').getSelectedBlock();
                if (selectedBlock && selectedBlock.clientId !== (this.state.selectedBlock || {}).clientId) {
                    this.setState({ selectedBlock });
                }
            });
        }

        /**
         * Load presets from server
         */
        loadPresets() {
            this.setState({ loading: true });

            wp.apiFetch({
                path: '/wp/v2/wps_block_preset?per_page=100',
                method: 'GET'
            }).then((presets) => {
                this.setState({ 
                    presets: presets || [],
                    loading: false 
                });
            }).catch(() => {
                this.setState({ 
                    loading: false,
                    message: { type: 'error', text: __('Failed to load presets', 'wpshadow') }
                });
            });
        }

        /**
         * Save current block as preset
         */
        savePreset() {
            const { selectedBlock, presetName } = this.state;

            if (!selectedBlock) {
                this.setState({
                    message: { type: 'error', text: __('Please select a block first', 'wpshadow') }
                });
                return;
            }

            if (!presetName.trim()) {
                this.setState({
                    message: { type: 'error', text: __('Please enter a preset name', 'wpshadow') }
                });
                return;
            }

            this.setState({ saving: true });

            wp.ajax.post('wpshadow_save_block_preset', {
                nonce: wpShadowBlockPresets.nonce,
                name: presetName,
                block_name: selectedBlock.name,
                attributes: JSON.stringify(selectedBlock.attributes)
            }).done((response) => {
                this.setState({
                    saving: false,
                    presetName: '',
                    message: { type: 'success', text: __('Preset saved successfully', 'wpshadow') }
                });
                this.loadPresets();
            }).fail(() => {
                this.setState({
                    saving: false,
                    message: { type: 'error', text: __('Failed to save preset', 'wpshadow') }
                });
            });
        }

        /**
         * Load preset into editor
         */
        loadPreset(presetId) {
            this.setState({ loading: true });

            wp.ajax.post('wpshadow_load_block_preset', {
                nonce: wpShadowBlockPresets.nonce,
                preset_id: presetId
            }).done((response) => {
                const { block_name, attributes } = response.data;
                
                // Create new block with preset attributes
                const newBlock = wp.blocks.createBlock(block_name, JSON.parse(attributes));
                
                // Insert block
                dispatch('core/block-editor').insertBlocks(newBlock);
                
                this.setState({
                    loading: false,
                    message: { type: 'success', text: __('Preset loaded successfully', 'wpshadow') }
                });
            }).fail(() => {
                this.setState({
                    loading: false,
                    message: { type: 'error', text: __('Failed to load preset', 'wpshadow') }
                });
            });
        }

        /**
         * Delete preset
         */
        deletePreset(presetId) {
            if (!confirm(__('Are you sure you want to delete this preset?', 'wpshadow'))) {
                return;
            }

            this.setState({ loading: true });

            wp.ajax.post('wpshadow_delete_block_preset', {
                nonce: wpShadowBlockPresets.nonce,
                preset_id: presetId
            }).done(() => {
                this.setState({
                    loading: false,
                    message: { type: 'success', text: __('Preset deleted successfully', 'wpshadow') }
                });
                this.loadPresets();
            }).fail(() => {
                this.setState({
                    loading: false,
                    message: { type: 'error', text: __('Failed to delete preset', 'wpshadow') }
                });
            });
        }

        render() {
            const { presets, presetName, selectedBlock, saving, loading, message } = this.state;

            return (
                <>
                    <PanelBody title={__('Save Current Block', 'wpshadow')} initialOpen={true}>
                        {message && (
                            <Notice 
                                status={message.type} 
                                isDismissible={true}
                                onRemove={() => this.setState({ message: null })}
                            >
                                {message.text}
                            </Notice>
                        )}
                        
                        {selectedBlock && (
                            <p style={{ fontSize: '13px', color: '#646970', marginBottom: '12px' }}>
                                {__('Selected:', 'wpshadow')} <strong>{selectedBlock.name}</strong>
                            </p>
                        )}
                        
                        <TextControl
                            label={__('Preset Name', 'wpshadow')}
                            value={presetName}
                            onChange={(value) => this.setState({ presetName: value })}
                            placeholder={__('e.g., "My Button Style"', 'wpshadow')}
                            disabled={saving || !selectedBlock}
                        />
                        
                        <Button
                            isPrimary
                            isBusy={saving}
                            disabled={saving || !selectedBlock || !presetName.trim()}
                            onClick={this.savePreset}
                        >
                            {saving ? __('Saving...', 'wpshadow') : __('Save Preset', 'wpshadow')}
                        </Button>
                    </PanelBody>

                    <PanelBody title={__('Saved Presets', 'wpshadow')} initialOpen={true}>
                        {loading && <p>{__('Loading...', 'wpshadow')}</p>}
                        
                        {!loading && presets.length === 0 && (
                            <p style={{ color: '#646970' }}>
                                {__('No saved presets yet.', 'wpshadow')}
                            </p>
                        )}
                        
                        {!loading && presets.length > 0 && (
                            <div className="wpshadow-presets-list">
                                {presets.map((preset) => (
                                    <div key={preset.id} className="wpshadow-preset-item">
                                        <div className="wpshadow-preset-name">
                                            {preset.title.rendered}
                                        </div>
                                        <div className="wpshadow-preset-actions">
                                            <Button
                                                isSmall
                                                onClick={() => this.loadPreset(preset.id)}
                                            >
                                                {__('Load', 'wpshadow')}
                                            </Button>
                                            <Button
                                                isSmall
                                                isDestructive
                                                onClick={() => this.deletePreset(preset.id)}
                                            >
                                                {__('Delete', 'wpshadow')}
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </PanelBody>
                </>
            );
        }
    }

    /**
     * Register plugin sidebar
     */
    registerPlugin('wpshadow-block-presets', {
        render: () => {
            return (
                <>
                    <PluginSidebarMoreMenuItem target="wpshadow-block-presets-sidebar">
                        {__('Block Presets', 'wpshadow')}
                    </PluginSidebarMoreMenuItem>
                    <PluginSidebar
                        name="wpshadow-block-presets-sidebar"
                        title={__('Block Presets', 'wpshadow')}
                    >
                        <BlockPresetsSidebar />
                    </PluginSidebar>
                </>
            );
        }
    });

})(window.wp);
