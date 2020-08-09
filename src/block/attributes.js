const attributes = {
	username: {
		type: 'string',
		default: '',
	},
	timer: {
		type: 'object',
		default: null,
	},
	init: {
		type: 'bool',
		default: true,
	},
	posts: {
		type: 'object',
		default: [],
	},
	message: {
		type: 'string',
		default: '<div>Please write an <span class="dashicons dashicons-instagram"></span> Instagram username</div>',
	},
	columnCount: {
		type: 'number',
		default: 3,
	},
};
export default attributes;
