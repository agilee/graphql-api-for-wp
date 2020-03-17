import { __ } from '@wordpress/i18n';
import { TabPanel } from '@wordpress/components';
import FieldMultiSelectControl from './field-multi-select-control';

const FieldDirectiveTabPanel = ( props ) => {
	const { className, setAttributes, typeFields } = props;
	return (
		<TabPanel
			className={ className + '__tab_panel' }
			activeClass="active-tab"
			tabs={ [
				{
					name: 'tabFields',
					title: __('Fields', 'graphql-api'),
					className: 'tab tab-fields',
				},
				{
					name: 'tabDirectives',
					title: __('Directives', 'graphql-api'),
					className: 'tab tab-directives',
				},
			] }
		>
			{
				( tab ) => tab.name == 'tabFields' ?
					<FieldMultiSelectControl
						selectedItems={ typeFields }
						setAttributes={ setAttributes }
					/> :
					<p>
						Saraza
					</p>
			}
		</TabPanel>
	);
}

export default FieldDirectiveTabPanel;