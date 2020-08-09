import React from 'react';

const { __ } = wp.i18n; // Import __() from wp.i18n

const InstagramPost = ({ data, message }) => {
	if (!data.length && !message) {
		return '';
	}

	if (message) {
		return <div dangerouslySetInnerHTML={ { __html: message } }></div>;
	}

	return (
		data.map((post) => {
			const {
				time, link,
			} = post;
			return <div className="tera-instagram-post" key={ time }>
				<blockquote className="instagram-media"
					dataInstgrmPermalink={ link }
					dataInstgrmCaptioned
					dataInstgrmVersion="12"
				>
					<div>
						<a href={ link }
							target="_blank"></a>
					</div>
				</blockquote>
			</div>;
		})
	);
};

export default InstagramPost;
