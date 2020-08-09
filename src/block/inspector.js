const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, TextControl, RangeControl } = wp.components;

class Inspector extends Component {
	render() {
		return (
			<InspectorControls
				key="instagram">
				<PanelBody
					title={ 'Instagram Settings' }>
					<TextControl
						placeholder={ 'Instagram Username' }
						onChange={ this.changeUsername.bind(this) }
						value={ this.props.attributes.username }
					>
					</TextControl>
					<RangeControl
						beforeIcon="arrow-left-alt2"
						afterIcon="arrow-right-alt2"
						label={ 'Column Count' }
						value={ this.props.attributes.columnCount }
						onChange={ (columnCount) => this.props.setAttributes({ columnCount }) }
						min={ 1 }
						max={ 6 }
					/>
				</PanelBody>
			</InspectorControls>
		);
	}
}

export default Inspector;
