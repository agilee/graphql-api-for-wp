/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Card, CardHeader, CardBody, ToggleControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	SchemaModeControl,
	LinkableInfoTooltip,
	getEditableOnFocusComponentClass,
} from '../../../packages/components/src';

const SchemaConfigOptionsCard = ( props ) => {
	const { isSelected, className, setAttributes, attributes: { useNamespacing } } = props;
	const componentClassName = `${ className } ${ getEditableOnFocusComponentClass(isSelected) }`;
	return (
		<div className={ componentClassName }>
			<Card { ...props }>
				<CardHeader isShady>
					{ __('Options', 'graphql-api') }
					<LinkableInfoTooltip
						text={ __('Select the default behavior of the Schema', 'graphql-api') }
						href="https://graphql-api.com/documentation/#schema-config-options"
					/ >
				</CardHeader>
				<CardBody>
					<div className={ `${ className }__schema_mode` }>
						<strong>{ __('Default Schema Mode:', 'graphql-api') }</strong>
						<LinkableInfoTooltip
							text={ __('Public: field/directives are always visible. Private: field/directives are hidden unless rules are satisfied.', 'graphql-api') }
							href="https://graphql-api.com/documentation/#schema-mode"
						/ >
						<SchemaModeControl
							{ ...props }
							attributeName="defaultSchemaMode"
							addDefault={ false }
						/>
					</div>
					<div className={ `${ className }__namespacing` }>
						<strong>{ __('Namespace Types and Interfaces?', 'graphql-api') }</strong>
						<LinkableInfoTooltip
							text={ __('Prepend types and interfaces using the PHP package\'s owner and name', 'graphql-api') }
							href="https://graphql-api.com/documentation/#namespacing"
						/ >
						<ToggleControl
							{ ...props }
							label={ useNamespacing ? __('Namespacing enabled', 'graphql-api') : __('Namespacing disabled', 'graphql-api') }
							checked={ useNamespacing }
							onChange={ newValue => setAttributes( {
								useNamespacing: newValue,
							} ) }
						/>
					</div>
				</CardBody>
			</Card>
		</div>
	);
}

export default SchemaConfigOptionsCard;
