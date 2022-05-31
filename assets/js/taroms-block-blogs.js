/*!
 * Display blog blocks.
 *
 * @handle taroms-block-blogs
 * @deps wp-i18n, wp-components, wp-blocks, wp-block-editor, wp-server-side-render
 */

/* global TaroMsBlockBlogsVars:false */

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl } = wp.components;
const { serverSideRender: ServerSideRender } = wp;

registerBlockType( TaroMsBlockBlogsVars.name, {

	title: TaroMsBlockBlogsVars.label,

	icon: 'admin-site',

	category: 'widgets',

	keywords: [ 'site', __( 'Sites', 'taroms' ) ],

	attributes: TaroMsBlockBlogsVars.attributes,

	description: TaroMsBlockBlogsVars.description,

	edit( { attributes, setAttributes } ) {
		return (
			<>
				<InspectorControls>
					<PanelBody defaultOpen={ true } title={ __( 'Blogs List Setting', 'taroms' ) } >
						<TextControl label={ __( 'Number of Blogs', 'taroms' ) } value={ attributes.number }
							onChange={ ( number ) => setAttributes( { number: parseInt( number ) } ) }
							type="number"
							/>
						<SelectControl label={ __( 'Order By', 'taroms' ) } value={ attributes.orderby }
							onChange={ ( orderby ) => setAttributes( { orderby } ) }
							options={ [
								{
									value: 'last_updated',
									label: __( 'Last Updated', 'taroms' ),
								},
								{
									value: 'registered',
									label: __( 'Registered', 'taroms' ),
								}
							] } />
						<SelectControl label={ __( 'Order', 'taroms' ) } value={ attributes.order }
							onChange={ ( order ) => setAttributes( { order } ) }
							options={ [
								{
									value: 'DESC',
									label: __( 'Descendant', 'taroms' ),
								},
								{
									value: 'ASC',
									label: __( 'Ascendant', 'taroms' ),
								},
							] } />
						<SelectControl label={ __( 'This Site', 'taroms' ) } value={ attributes.exclude_self }
							onChange={ ( exclude_self ) => setAttributes( { exclude_self } ) }
							options={ [
								{
									value: true,
									label: __( 'Exclude this site', 'taroms' ),
								},
								{
									value: false,
									label: __( 'Include this site', 'taroms' ),
								}
							] } />
					</PanelBody>
				</InspectorControls>
				<div className="taroms-blogs" style={ { 'pointer-events': 'none' } }>
					<ServerSideRender block={ TaroMsBlockBlogsVars.name } attributes={ attributes } />
				</div>
			</>
		);
	},

	save() {
		return null;
	},
} );
