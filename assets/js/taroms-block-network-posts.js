/*!
 * Display posts network wide.
 *
 * @handle taroms-block-network-posts
 * @deps wp-i18n, wp-components, wp-blocks, wp-block-editor, wp-server-side-render
 */

/* global TaroMsBlockNetworkPostsVars:false */

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl } = wp.components;
const { serverSideRender: ServerSideRender } = wp;

registerBlockType( TaroMsBlockNetworkPostsVars.name, {

	title: TaroMsBlockNetworkPostsVars.label,

	icon: 'edit-page',

	category: 'widgets',

	keywords: [ 'network', __( 'Network Posts', 'taroms' ) ],

	attributes: TaroMsBlockNetworkPostsVars.attributes,

	description: TaroMsBlockNetworkPostsVars.description,

	edit( { attributes, setAttributes } ) {
		return (
			<>
				<InspectorControls>
					<PanelBody defaultOpen={ true } title={ __( 'Network Posts Setting', 'taroms' ) } >
						<TextControl label={ __( 'Number of Posts', 'taroms' ) } value={ attributes.posts_per_page }
							onChange={ ( posts_per_page ) => setAttributes( { posts_per_page: parseInt( posts_per_page ) } ) }
							type="number"
							/>
						<SelectControl label={ __( 'Main Site', 'taroms' ) } value={ attributes.exclude_parent }
							onChange={ ( exclude_parent ) => setAttributes( { exclude_parent } ) }
							options={ [
								{
									value: true,
									label: __( 'Exclude main site', 'taroms' ),
								},
								{
									value: false,
									label: __( 'Include main site', 'taroms' ),
								}
							] } />
						<SelectControl label={ __( 'Group By', 'taroms' ) } value={ attributes.group_by }
							onChange={ ( group_by ) => setAttributes( { group_by } ) }
							options={ [
								{
									value: true,
									label: __( 'Group by site', 'taroms' ),
								},
								{
									value: false,
									label: __( 'Allow overlap', 'taroms' ),
								}
							] } />
						<TextControl label={ __( 'Blog IDs', 'taroms' ) } value={ attributes.blog_ids }
							onChange={ ( blog_ids ) => setAttributes( { blog_ids } ) }
							type="text"
							help={ __( 'Input blog ids in CSV format. Only specified blogs will be in the list.', 'taroms' ) }
						/>
					</PanelBody>
				</InspectorControls>
				<div className="taroms-blogs" style={ { 'pointer-events': 'none' } }>
					<ServerSideRender block={ TaroMsBlockNetworkPostsVars.name } attributes={ attributes } />
				</div>
			</>
		);
	},

	save() {
		return null;
	},
} );
