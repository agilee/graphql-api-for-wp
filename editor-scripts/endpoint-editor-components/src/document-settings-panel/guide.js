/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Guide, GuidePage } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { getMarkdownContentOrUseDefault } from '../markdown-loader';

const EndpointGuide = ( props ) => {
	const [ pages, setPages ] = useState([]);
	const lang = 'fr'
	const defaultLang = 'en'
	const markdownPageFilenames = [
		'welcome-guide',
		'schema-config-options',
	]
	useEffect(() => {
		const importPromises = markdownPageFilenames.map(
			fileName => getMarkdownContentOrUseDefault( lang, defaultLang, fileName )
		)
		Promise.all(importPromises).then( values => {
			setPages( values )
		});
	}, []);

	return (
		<Guide
			{ ...props }
			contentLabel={ __('Endpoint guide', 'graphql-api') } 
		>
			{ pages.map( page => (
				<GuidePage
					{ ...props }
					dangerouslySetInnerHTML={ { __html: page } } 
				/>
			) ) }
		</Guide>
	)
}
const EndpointGuideButton = ( props ) => {
	const [ isOpen, setOpen ] = useState( false );
	return (
		<>
			<Button isSecondary onClick={ () => setOpen( true ) }>
				{ __('Open tutorial guide', 'graphql-api') }
			</Button>
			{ isOpen && (
				<EndpointGuide 
					{ ...props }
					onFinish={ () => setOpen( false ) }
				/>
			) }
		</>
	);
};
export default EndpointGuideButton;
