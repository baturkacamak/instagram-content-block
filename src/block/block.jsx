/**
 * BLOCK: tera-instagram-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';
import attributes from './attributes';
import InstagramPost from './instagram-post';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { InspectorControls } = wp.editor;
const { PanelBody, TextControl, RangeControl } = wp.components;
const { apiFetch } = wp;
const { Component, Fragment } = wp.element;

/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType('cgb/block-tera-instagram-block', {
	// Block name. Block names must be string that contains namespace prefix. Example:
	// my-plugin/my-custom-block.
	title: __('Instagram Feed (Tera)'), // Block title.
	icon: 'instagram', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'embed', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__('tera-instagram-block'),
		__('Instagram Feed'),
	],
	attributes,
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 *
	 * @param {Object} props Props.
	 * @returns {Mixed} JSX Component.
	 */
	edit: class extends Component {
		constructor(props) {
			super();

			this.props = props;
			this.state = {};
		}

		fetchRemoteData(username) {
			if ('' === username) {
				return;
			}
			this.props.setAttributes({ message: `<div><a target="_blank" href="https://instagram.com/${ username }"><span class="dashicons dashicons-instagram"></span>${ username }</a>'s feed is loading...</div>` });
			apiFetch({
				url: ajaxurl,
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
				},
				credentials: 'same-origin',
				body: `action=get_instagram_feed&username=${ username }`,
			}).then((posts) => {
				if (posts) {
					this.props.setAttributes({ posts, message: false });
				} else {
					this.props.setAttributes({
						message: `<div><a target="_blank" href="https://instagram.com/${ username }"><span class="dashicons dashicons-instagram"></span>${ username }</a> is not a public profile or not found. Please try something else.</div>`,
					});
				}
			});
		}

		changeUsername(username) {
			this.props.setAttributes({ username });
			if ('' !== username) {
				// // Immediately update the state
				clearTimeout(this.props.attributes.timer);
				this.props.setAttributes({
					timer: setTimeout(() => {
						this.fetchRemoteData(username);
					}, 2000),
				});
			}
		}

		componentDidMount() {
			if (!document.querySelector('#instagram-embed-script')) {
				// insert instagram embed
				const script = document.createElement('script');
				script.id = 'instagram-embed-script';
				script.src = '//instagram.com/embed.js';
				script.async = true;
				document.body.appendChild(script);
			}

			if (!this.props.attributes.posts.length && this.props.attributes.username) {
				this.fetchRemoteData(this.props.attributes.username);
			}
		}

		componentDidUpdate() {
			if ('' === this.props.attributes.username) {
				this.props.setAttributes({
					posts: false,
					message: '<div>Please write an <span class="dashicons dashicons-instagram"></span> Instagram username</div>',
				});
			} else if (this.props.attributes.posts.length) {
				window.instgrm.Embeds.process();
			}
		}

		componentWillUnmount() {
		}

		postsClassNames() {
			const names = ['tera-instagram-posts'];

			if (!this.props.attributes.posts.length) {
				names.push('is-initial');
			} else {
				names.push('is-initialized');
			}
			return names.join(' ');
		}

		render() {
			return ([
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
							default={ 3 }
							value={ this.props.attributes.columnCount }
							onChange={ (columnCount) => this.props.setAttributes({ columnCount }) }
							min={ 1 }
							max={ 6 }
						/>
					</PanelBody>
				</InspectorControls>,
				<div
					style={ { '--column-count': this.props.attributes.columnCount } }
					key="instagram-posts"
					className={ this.postsClassNames() }>
					<InstagramPost data={ this.props.attributes.posts }
						message={ this.props.attributes.message }></InstagramPost>
				</div>,
			]);
		}
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 *
	 * @param {Object} props Props.
	 * @returns {Mixed} JSX Frontend HTML.
	 */
	save({ attributes, className }) {
		return null;
	},
});
