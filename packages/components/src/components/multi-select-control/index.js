/**
 * External dependencies
 */
import { filter, uniq } from 'lodash';
import { search as searchIcon } from '@wordpress/icons';
/**
 * WordPress dependencies
 */
import { compose, withState } from '@wordpress/compose';
import { TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Addition by Leo
import './style.scss';

/**
 * Internal dependencies
 */
import MultiSelectControlGroup from './group';
import withErrorMessage from '../loading/with-error-message';
import withSpinner from '../loading/with-spinner';

function MultiSelectControl( props ) {
	const { setState, showSearch, search, items } = props;
	// Filtering occurs here (as opposed to `withSelect`) to avoid wasted
	// wasted renders by consequence of `Array#filter` producing a new
	// value reference on each call.
	// If the type matches the search, return all fields. Otherwise, return all fields that match the search
	const filteredItems = items.filter(
		( item ) => !search || item.group.toLowerCase().includes(search.toLowerCase()) || item.title.toLowerCase().includes(search.toLowerCase())
	);
	const groups = uniq(filteredItems.map(
		( item ) => item.group
	))
	return (
		<div className="multi-select-control__content">
			<div className="multi-select-control__content_search">
				<Button
					isSmall
					icon={ searchIcon }
					onClick={
						() => setState( {
							showSearch: !showSearch
						} )
					}
				>
					{ showSearch ? __( 'Hide search' ) : __( 'Show search' ) }
				</Button>
			</div>
			{ showSearch &&
				<TextControl
					type="search"
					label={ __( 'Search' ) }
					value={ search }
					onChange={ ( nextSearch ) =>
						setState( {
							search: nextSearch,
						} )
					}
					className="multi-select-control__search"
				/>
			}
			<div
				tabIndex="0"
				role="region"
				aria-label={ __( 'Available items' ) }
				className="multi-select-control__results"
			>
				{ filteredItems.length === 0 && (
					<p className="multi-select-control__no-results">
						{ __( 'No items found.' ) }
					</p>
				) }
				{ groups.map( ( group ) => (
					<MultiSelectControlGroup
						{ ...props }
						key={ group }
						group={ group }
						items={ filter( filteredItems, {
							group: group,
						} ) }
					/>
				) ) }
			</div>
		</div>
	);
}

export default compose( [
	withState( { search: '', showSearch: false } ),
	withSpinner(),
	withErrorMessage(),
] )( MultiSelectControl );
