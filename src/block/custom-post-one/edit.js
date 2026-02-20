/**
 * External dependencies
 */
import dayjs from "dayjs";
import classnames from "classnames";
import axios from "axios";
var HtmlToReactParser = require("html-to-react").Parser;

const { Fragment, useState, useEffect, useCallback, useRef } = wp.element;

const { __ } = wp.i18n;

const { decodeEntities } = wp.htmlEntities;

const {
	PanelBody,
	Placeholder,
	RangeControl,
	SelectControl,
	Spinner,
	TextControl,
	TextareaControl,
	ToggleControl,
	ToolbarGroup,
} = wp.components;

const {
	MediaUpload,
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls,
	PanelColorSettings,
	useBlockProps,
} = wp.blockEditor;

const MAX_POSTS_COLUMNS = 6;

function PTAM_Custom_PostsEdit( props ) {
	const { attributes, setAttributes } = props;
	const attributesRef = useRef( attributes );
	attributesRef.current = attributes;

	const [ loading, setLoading ] = useState( true );
	const [ latestPosts, setLatestPosts ] = useState( [] );
	const [ postTypeList, setPostTypeList ] = useState( [] );
	const [ taxonomyList, setTaxonomyList ] = useState( [] );
	const [ termsList, setTermsList ] = useState( [] );
	const [ imageSizes, setImageSizes ] = useState( [] );
	const [ fonts, setFonts ] = useState( [] );
	const [ userTaxonomies, setUserTaxonomies ] = useState( [] );
	const [ userTerms, setUserTerms ] = useState( [] );

	const blockProps = useBlockProps( {
		className: classnames( "ptam-block-post-grid" ),
	} );

	const getLatestPosts = useCallback( ( object = {} ) => {
		setLoading( true );
		const merged = jQuery.extend( {}, attributes, object );
		let {
			postType,
			order,
			orderBy,
			taxonomy,
			avatarSize,
			imageType,
			imageTypeSize,
			term,
			postsToShow,
			imageCrop,
			linkColor,
			fallbackImg,
			wpmlLanguage,
		} = merged;
		linkColor = ( linkColor || "" ).replace( "#", "" );
		axios
			.post(
				ptam_globals.rest_url + "ptam/v2/get_posts",
				{
					post_type: postType,
					order: order,
					orderby: orderBy,
					taxonomy: taxonomy,
					term: term,
					posts_per_page: postsToShow,
					image_size: imageCrop,
					avatar_size: avatarSize,
					image_type: imageType,
					image_size: imageTypeSize,
					link_color: linkColor,
					default_image: fallbackImg,
					language: wpmlLanguage,
				},
				{
					headers: {
						"X-WP-Nonce": ptam_globals.rest_nonce,
					},
				}
			)
			.then( ( response ) => {
				setLoading( false );
				setLatestPosts( response.data.posts );
				setImageSizes( response.data.image_sizes );
				setFonts( response.data.fonts );
				setUserTaxonomies( response.data.taxonomies || [] );
				setUserTerms( response.data.terms || [] );
			} );
	}, [ attributes ] );

	const getTermList = useCallback( ( object = {} ) => {
		const merged = jQuery.extend( {}, attributes, object );
		const { postType, taxonomy } = merged;
		axios
			.post(
				ptam_globals.rest_url + "ptam/v2/get_terms",
				{
					taxonomy: taxonomy,
					post_type: postType,
				},
				{
					headers: {
						"X-WP-Nonce": ptam_globals.rest_nonce,
					},
				}
			)
			.then( ( response ) => {
				const list = [];
				if ( Object.keys( response.data ).length > 0 ) {
					list.push( {
						value: 0,
						label: __( "All", "post-type-archive-mapping" ),
					} );
					jQuery.each( response.data, function ( key, value ) {
						list.push( { value: value.term_id, label: value.name } );
					} );
				}
				setLoading( false );
				setTermsList( list );
			} );
	}, [ attributes ] );

	const getLatestData = useCallback( ( object = {} ) => {
		setLoading( true );
		const merged = jQuery.extend( {}, attributes, object );
		let {
			postType,
			order,
			orderBy,
			avatarSize,
			imageType,
			imageTypeSize,
			taxonomy,
			term,
			postsToShow,
			imageCrop,
			linkColor,
			fallbackImg,
			wpmlLanguage,
		} = merged;

		linkColor = ( linkColor || "" ).replace( "#", "" );

		axios
			.post(
				ptam_globals.rest_url + "ptam/v2/get_posts",
				{
					post_type: postType,
					order: order,
					orderby: orderBy,
					taxonomy: taxonomy,
					term: term,
					posts_per_page: postsToShow,
					image_size: imageCrop,
					avatar_size: avatarSize,
					image_type: imageType,
					image_size: imageTypeSize,
					link_color: linkColor,
					default_image: fallbackImg,
					language: wpmlLanguage,
				},
				{
					headers: {
						"X-WP-Nonce": ptam_globals.rest_nonce,
					},
				}
			)
			.then( ( response ) => {
				const posts = response.data.posts;
				const sizes = response.data.image_sizes;
				const taxonomies = response.data.taxonomies;
				const fontList = response.data.fonts;

				axios.get( ptam_globals.rest_url + "wp/v2/types" ).then( ( typesRes ) => {
					const typesList = [];
					jQuery.each( typesRes.data, function ( key, value ) {
						if ( "attachment" !== key && "wp_block" !== key ) {
							typesList.push( { value: key, label: value.name } );
						}
					} );

					axios
						.post(
							ptam_globals.rest_url + "ptam/v2/get_terms",
							{ taxonomy: taxonomy, post_type: postType },
							{
								headers: {
									"X-WP-Nonce": ptam_globals.rest_nonce,
								},
							}
						)
						.then( ( termsRes ) => {
							const termList = [];
							if ( Object.keys( termsRes.data ).length > 0 ) {
								termList.push( {
									value: 0,
									label: __( "All", "post-type-archive-mapping" ),
								} );
								jQuery.each( termsRes.data, function ( key, value ) {
									termList.push( {
										value: value.term_id,
										label: value.name,
									} );
								} );
							}

							axios
								.post(
									ptam_globals.rest_url + "ptam/v2/get_taxonomies",
									{ post_type: postType },
									{
										headers: {
											"X-WP-Nonce": ptam_globals.rest_nonce,
										},
									}
								)
								.then( ( taxRes ) => {
									const taxList = [];
									if ( Object.keys( taxRes.data ).length > 0 ) {
										taxList.push( {
											value: "none",
											label: __(
												"Select a Taxonomy",
												"post-type-archive-mapping"
											),
										} );
										jQuery.each( taxRes.data, function ( key, value ) {
											taxList.push( {
												value: key,
												label: value.label,
											} );
										} );
									}
									setLoading( false );
									setImageSizes( sizes );
									setFonts( fontList );
									setLatestPosts( posts );
									setPostTypeList( typesList );
									setTaxonomyList( taxList );
									setTermsList( termList );
									setUserTaxonomies( taxonomies || [] );
									setUserTerms( [] );
								} );
						} );
				} );
			} );
	}, [ attributes ] );

	const toggleDisplayTitle = () =>
		setAttributes( { displayTitle: ! attributes.displayTitle } );
	const toggleDisplayTitleLink = () =>
		setAttributes( { displayTitleLink: ! attributes.displayTitleLink } );
	const toggleDisplayCustomFields = () =>
		setAttributes( { displayCustomFields: ! attributes.displayCustomFields } );
	const toggleDisplayPostDate = () =>
		setAttributes( { displayPostDate: ! attributes.displayPostDate } );
	const toggleDisplayPostExcerpt = () =>
		setAttributes( { displayPostExcerpt: ! attributes.displayPostExcerpt } );
	const toggleDisplayPostAuthor = () =>
		setAttributes( { displayPostAuthor: ! attributes.displayPostAuthor } );
	const toggleDisplayPostImage = () =>
		setAttributes( { displayPostImage: ! attributes.displayPostImage } );
	const toggleDisplayPostLink = () =>
		setAttributes( { displayPostLink: ! attributes.displayPostLink } );
	const toggleDisplayPagination = () =>
		setAttributes( { pagination: ! attributes.pagination } );
	const toggleCapitilization = () =>
		setAttributes( { changeCapitilization: ! attributes.changeCapitilization } );
	const toggleTaxonomyDisplay = () =>
		setAttributes( { displayTaxonomies: ! attributes.displayTaxonomies } );
	const toggleRemoveStyles = () =>
		setAttributes( { removeStyles: ! attributes.removeStyles } );
	const toggleFullPostContent = () => {
		const { displayPostContent, displayPostExcerpt } = attributes;
		setAttributes( {
			displayPostExcerpt:
				displayPostExcerpt && false === displayPostContent
					? false
					: displayPostExcerpt,
			displayPostContent: ! displayPostContent,
		} );
	};
	const onTrimWordsChange = ( value ) => setAttributes( { trimWords: value } );
	const onChangeLocation = ( value ) =>
		setAttributes( { imageLocation: value } );
	const onChangeTaxonomyLocation = ( value ) =>
		setAttributes( { taxonomyLocation: value } );

	const onImageTypeChange = ( imageType ) => {
		setLoading( true );
		const atts = attributesRef.current;
		let linkColor = ( atts.linkColor || "" ).replace( "#", "" );
		axios
			.post(
				ptam_globals.rest_url + "ptam/v2/get_images",
				{
					post_type: atts.postType,
					order: atts.order,
					orderby: atts.orderBy,
					taxonomy: atts.taxonomy,
					term: atts.term,
					posts_per_page: atts.postsToShow,
					image_type: atts.imageCrop,
					avatar_size: atts.avatarSize,
					image_type: imageType,
					image_size: atts.imageTypeSize,
					link_color: linkColor,
					default_image: atts.fallbackImg,
					language: atts.wpmlLanguage,
				},
				{
					headers: { "X-WP-Nonce": ptam_globals.rest_nonce },
				}
			)
			.then( ( response ) => {
				setLoading( false );
				setLatestPosts( response.data.posts );
				setImageSizes( response.data.image_sizes );
			} );
	};

	const onImageSizeChange = ( value ) => {
		setLoading( true );
		const atts = attributesRef.current;
		let linkColor = ( atts.linkColor || "" ).replace( "#", "" );
		axios
			.post(
				ptam_globals.rest_url + "ptam/v2/get_images",
				{
					post_type: atts.postType,
					order: atts.order,
					orderby: atts.orderBy,
					taxonomy: atts.taxonomy,
					term: atts.term,
					posts_per_page: atts.postsToShow,
					image_crop: atts.imageCrop,
					avatar_size: atts.avatarSize,
					image_type: "regular",
					image_size: value,
					link_color: linkColor,
					default_image: atts.fallbackImg,
					language: atts.wpmlLanguage,
				},
				{
					headers: { "X-WP-Nonce": ptam_globals.rest_nonce },
				}
			)
			.then( ( response ) => {
				setLoading( false );
				setLatestPosts( response.data.posts );
				setImageSizes( response.data.image_sizes );
			} );
	};

	const excerptParse = ( excerpt ) => {
		const parser = new HtmlToReactParser();
		const { trimWords: words } = attributes;
		const trimmed = excerpt.split( " " ).slice( 0, words ).join( " " );
		return parser.parse( trimmed );
	};

	const onAvatarSizeChange = ( value ) => {
		setLoading( true );
		setAttributes( { avatarSize: value } );
		setTimeout( function () {
			const atts = attributesRef.current;
			let linkColor = ( atts.linkColor || "" ).replace( "#", "" );
			axios
				.post(
					ptam_globals.rest_url + "ptam/v2/get_images",
					{
						post_type: atts.postType,
						order: atts.order,
						orderby: atts.orderBy,
						taxonomy: atts.taxonomy,
						term: atts.term,
						posts_per_page: atts.postsToShow,
						image_type: atts.imageCrop,
						avatar_size: value,
						image_type: atts.imageType,
						image_size: atts.imageTypeSize,
						link_color: linkColor,
						default_image: atts.fallbackImg,
						language: atts.wpmlLanguage,
					},
					{
						headers: { "X-WP-Nonce": ptam_globals.rest_nonce },
					}
				)
				.then( ( response ) => {
					setLoading( false );
					setLatestPosts( response.data.posts );
					setImageSizes( response.data.image_sizes );
				} );
		}, 3000 );
	};

	const onFallbackImgChange = ( imgObject ) => {
		setLoading( true );
		setAttributes( { fallbackImg: imgObject } );
		setTimeout( function () {
			const atts = attributesRef.current;
			let linkColor = ( atts.linkColor || "" ).replace( "#", "" );
			axios
				.post(
					ptam_globals.rest_url + "ptam/v2/get_images",
					{
						post_type: atts.postType,
						order: atts.order,
						orderby: atts.orderBy,
						taxonomy: atts.taxonomy,
						term: atts.term,
						posts_per_page: atts.postsToShow,
						image_type: atts.imageCrop,
						avatar_size: atts.avatarSize,
						image_type: atts.imageType,
						image_size: atts.imageTypeSize,
						link_color: linkColor,
						default_image: imgObject,
						language: atts.wpmlLanguage,
					},
					{
						headers: { "X-WP-Nonce": ptam_globals.rest_nonce },
					}
				)
				.then( ( response ) => {
					setLoading( false );
					setLatestPosts( response.data.posts );
					setImageSizes( response.data.image_sizes );
				} );
		}, 3000 );
	};

	const onChangeBorderColor = ( value ) =>
		setAttributes( { borderColor: value } );
	const onChangeBackgroundColor = ( value ) =>
		setAttributes( { backgroundColor: value } );
	const onChangeTitleColor = ( value ) =>
		setAttributes( { titleColor: value } );
	const onChangeCustomFieldsColor = ( value ) =>
		setAttributes( { customFieldsColor: value } );
	const onChangeContentColor = ( value ) =>
		setAttributes( { contentColor: value } );
	const onChangeLinkColor = ( value ) => {
		setAttributes( { linkColor: value } );
		getLatestPosts( { linkColor: value } );
	};
	const onChangeContinueReadingColor = ( value ) =>
		setAttributes( { continueReadingColor: value } );

	// Load post types, taxonomies, and posts on mount (same as previous componentDidMount).
	useEffect( () => {
		getLatestData();
	}, [] );

	if ( attributes.preview ) {
		return (
			<Fragment>
				<img src={ ptam_globals.custom_posts_block_preview } />
			</Fragment>
		);
	}

	const htmlToReactParser = new HtmlToReactParser();
	const {
			postType,
			term,
			taxonomy,
			displayPostDate,
			displayPostExcerpt,
			displayPostContent,
			displayPostAuthor,
			displayPostImage,
			displayPostLink,
			displayTitleLink,
			align,
			postLayout,
			columns,
			order,
			pagination,
			orderBy,
			postsToShow,
			readMoreText,
			imageLocation,
			taxonomyLocation,
			imageType,
			imageTypeSize,
			avatarSize,
			changeCapitilization,
			displayTaxonomies,
			trimWords,
			titleAlignment,
			customFieldAlignment,
			imageAlignment,
			metaAlignment,
			contentAlignment,
			padding,
			border,
			borderRounded,
			borderColor,
			backgroundColor,
			titleColor,
			customFieldsColor,
			linkColor,
			contentColor,
			continueReadingColor,
			titleFont,
			customFieldsFont,
			metaFont,
			contentFont,
			continueReadingFont,
			displayTitle,
			displayCustomFields,
			customFields,
			removeStyles,
			titleHeadingTag,
			fallbackImg,
			wpmlLanguage,
		} = attributes;

	let userTaxonomiesArray = [];
		for ( var key in userTaxonomies ) {
			userTaxonomiesArray.push( {
				value: key,
				label: userTaxonomies[ key ].label,
			} );
		}

		// Thumbnail options
		const imageLocationOptions = [
			{
				value: "regular",
				label: __("Regular placement", "post-type-archive-mapping")
			},
			{
				value: "below_title",
				label: __("Image Below Title", "post-type-archive-mapping")
			},
			{
				value: "below_title_and_meta",
				label: __("Below title and post meta", "post-type-archive-mapping")
			},
			{
				value: "bottom",
				label: __("Image on bottom", "post-type-archive-mapping")
			}
		];
		let imageSizeOptions = [];
		for ( var key in imageSizes ) {
			imageSizeOptions.push({ value: key, label: key });
		}

		// Title Heading Options
		const titleHeadingOptions = [
			{ value: "h1", label: __("H1", "post-type-archive-mapping") },
			{ value: "h2", label: __("H2", "post-type-archive-mapping") },
			{ value: "h3", label: __("H3", "post-type-archive-mapping") },
			{ value: "h4", label: __("H4", "post-type-archive-mapping") },
			{ value: "h5", label: __("H5", "post-type-archive-mapping") },
			{ value: "H6", label: __("H6", "post-type-archive-mapping") }
		];

		const wpmlInstalled = ptam_globals.wpml_installed;
		const wpmlLanguages = ptam_globals.wpml_languages;

		// Fonts
		let fontOptions = [];
		for ( var key in fonts ) {
			fontOptions.push({ value: key, label: fonts[key] });
		}

		let imageDisplayOptionsTypes = [];
		imageDisplayOptionsTypes.push({
			label: __("Gravatar", "post-type-archive-mapping"),
			value: "gravatar"
		});
		imageDisplayOptionsTypes.push({
			label: __("Featured Image", "post-type-archive-mapping"),
			value: "regular"
		});

		const capitilization = changeCapitilization ? "ptam-text-lower-case" : "";

		const taxonomyLocationOptions = [
			{
				value: "regular",
				label: __("Regular placement", "post-type-archive-mapping")
			},
			{
				value: "below_content",
				label: __("Below Content", "post-type-archive-mapping")
			}
		];

		const alignmentOptions = [
			{ value: "left", label: __("Left", "post-type-archive-mapping") },
			{ value: "center", label: __("Center", "post-type-archive-mapping") },
			{ value: "right", label: __("Right", "post-type-archive-mapping") }
		];

		const orderOptions = [
			{ value: "ASC", label: __("ASC", "post-type-archive-mapping") },
			{ value: "DESC", label: __("DESC", "post-type-archive-mapping") }
		];

		const orderByOptions = [
			{ value: "ID", label: __("ID", "post-type-archive-mapping") },
			{
				value: "menu_order",
				label: __("Menu Order", "post-type-archive-mapping")
			},
			{
				value: "author",
				label: __("Post Author", "post-type-archive-mapping")
			},
			{ value: "date", label: __("Date", "post-type-archive-mapping") },
			{
				value: "modified",
				label: __("Date Modified", "post-type-archive-mapping")
			},
			{ value: "name", label: __("Post Slug", "post-type-archive-mapping") },
			{ value: "title", label: __("Title", "post-type-archive-mapping") },
			{ value: "rand", label: __("Random", "post-type-archive-mapping") }
		];

		const borderPaddingStyles = {
			padding: padding + "px",
			border: border + "px solid " + borderColor,
			borderRadius: borderRounded + "px",
			backgroundColor: backgroundColor
		};

		const hasPosts = Array.isArray(latestPosts) && latestPosts.length;

		const inspectorControls = (
			<InspectorControls>
				<PanelBody
					title={__("Query", "post-type-archive-mapping")}
					initialOpen={false}
				>
					<SelectControl
						label={__("Post Type", "post-type-archive-mapping")}
						options={postTypeList}
						value={postType}
						onChange={value => {
							setAttributes({
								postType: value,
								taxonomy: "none",
								term: 0
							});
							getLatestData({
								postType: value,
								taxonomy: "none",
								term: 0
							});
						}}
					/>
					<SelectControl
						label={__("Taxonomy", "post-type-archive-mapping")}
						options={taxonomyList}
						value={taxonomy}
						onChange={value => {
							setAttributes({ taxonomy: value });
							getTermList({ taxonomy: value });
							getLatestPosts({ taxonomy: value });
						}}
					/>
					<SelectControl
						mutltiple
						label={__("Terms", "post-type-archive-mapping")}
						options={termsList}
						value={term}
						onChange={value => {
							setAttributes({ term: value });
							getLatestPosts({ term: value });
						}}
					/>
					{wpmlInstalled &&
						<SelectControl
							label={__("Language", "post-type-archive-mapping")}
							options={wpmlLanguages}
							value={wpmlLanguage}
							onChange={value => {
								setAttributes({ wpmlLanguage: value });
								getLatestPosts({ wpmlLanguage: value });
							}}
						/>
					}
					<SelectControl
						label={__("Order", "post-type-archive-mapping")}
						options={orderOptions}
						value={order}
						onChange={value => {
							setAttributes({ order: value });
							getLatestPosts({ order: value });
						}}
					/>
					<SelectControl
						label={__("Order By", "post-type-archive-mapping")}
						options={orderByOptions}
						value={orderBy}
						onChange={value => {
							setAttributes({ orderBy: value });
							getLatestPosts({ orderBy: value });
						}}
					/>
					<RangeControl
						label={__("Number of Items", "post-type-archive-mapping")}
						value={postsToShow}
						onChange={value => {
							setAttributes({ postsToShow: value });
							getLatestPosts({ postsToShow: value });
						}}
						min={1}
						max={100}
					/>
					{postLayout === "grid" && (
						<RangeControl
							label={__("Columns", "post-type-archive-mapping")}
							value={columns}
							onChange={ ( value ) => setAttributes( { columns: value } ) }
							min={1}
							max={
								!hasPosts
									? MAX_POSTS_COLUMNS
									: Math.min(MAX_POSTS_COLUMNS, latestPosts.length)
							}
						/>
					)}
				</PanelBody>
				<PanelBody
					title={__("Title", "post-type-archive-mapping")}
					initialOpen={false}
				>
					<ToggleControl
						label={__("Display Title", "post-type-archive-mapping")}
						checked={displayTitle}
						onChange={ toggleDisplayTitle }
					/>
					<ToggleControl
						label={__("Enable Title Link", "post-type-archive-mapping")}
						checked={displayTitleLink}
						onChange={ toggleDisplayTitleLink }
					/>
					<SelectControl
						label={__("Title Heading Tag", "post-type-archive-mapping")}
						options={titleHeadingOptions}
						value={titleHeadingTag}
						onChange={value => {
							setAttributes({ titleHeadingTag: value });
						}}
					/>
				</PanelBody>
				<PanelBody
					title={__("Custom Fields", "post-type-archive-mapping")}
					initialOpen={false}
				>
					<ToggleControl
						label={__("Display Custom Fields", "post-type-archive-mapping")}
						checked={displayCustomFields}
						onChange={ toggleDisplayCustomFields }
					/>
					{displayCustomFields && (
						<TextareaControl
							label={__(
								"Enter Custom Field Markup",
								"post-type-archive-mapping"
							)}
							help={__(
								"HTML allowed. Place custom fields inbetween {}: {custom_field_1}",
								"post-type-archive-mapping"
							)}
							value={customFields}
							onChange={value => {
								setAttributes({ customFields: value });
							}}
						/>
					)}
				</PanelBody>
				<PanelBody title={__("Featured Image", "post-type-archive-mapping")}>
					<ToggleControl
						label={__("Display Featured Image", "post-type-archive-mapping")}
						checked={displayPostImage}
						onChange={ toggleDisplayPostImage }
					/>
					{displayPostImage && (
						<Fragment>
							<SelectControl
								label={__("Image Type", "post-type-archive-mapping")}
								options={imageDisplayOptionsTypes}
								value={imageType}
								onChange={value => {
									setAttributes({ imageType: value });
									onImageTypeChange( value );
								}}
							/>
							{"gravatar" === imageType ? (
								<div>
									<RangeControl
										label={__("Avatar Size", "post-type-archive-mapping")}
										value={avatarSize}
										onChange={value => {
											setAttributes({ avatarSize: value });
											onAvatarSizeChange( value );
										}}
										min={16}
										max={512}
									/>
								</div>
							) : (
								""
							)}
							{"gravatar" !== imageType && (
								<Fragment>
									<MediaUpload
										onSelect={imageObject => {
											setAttributes( { fallbackImg: imageObject } );
											onFallbackImgChange( imageObject );
										}}
										type="image"
										value={fallbackImg.url}
										render={({ open }) => (
											<Fragment>
												<button
													className="ptam-media-alt-upload components-button is-button is-secondary"
													onClick={open}
												>
													{__(
														"Fallback Featured Image",
														"post-type-archive-mapping"
													)}
												</button>
												{fallbackImg && (
													<Fragment>
														<div>
															<img
																src={fallbackImg.url}
																alt={__(
																	"Featured Image",
																	"post-type-archive-mapping"
																)}
																width="250"
																height="250"
															/>
														</div>
														<div>
															<button
																className="ptam-media-alt-reset components-button is-button is-secondary"
																onClick={event => {
																	setAttributes( { fallbackImg: "" } );
																	onFallbackImgChange( 0 );
																}}
															>
																{__("Reset Image", "post-type-archive-mapping")}
															</button>
														</div>
													</Fragment>
												)}
											</Fragment>
										)}
									/>
									<SelectControl
										label={__(
											"Featured Image Size",
											"post-type-archive-mapping"
										)}
										options={imageSizeOptions}
										value={imageTypeSize}
										onChange={value => {
											setAttributes({ imageTypeSize: value });
											onImageSizeChange( value );
										}}
									/>
								</Fragment>
							)}
							<SelectControl
								label={__("Image Location", "post-type-archive-mapping")}
								options={imageLocationOptions}
								value={attributes.imageLocation}
								onChange={value => {
									setAttributes({ imageLocation: value });
									onChangeLocation( value );
								}}
							/>
						</Fragment>
					)}
				</PanelBody>
				<PanelBody title={__("Options", "post-type-archive-mapping")}>
					<ToggleControl
						label={__("Display Taxonomies", "post-type-archive-mapping")}
						checked={displayTaxonomies}
						onChange={ toggleTaxonomyDisplay }
					/>
					{displayTaxonomies && (
						<SelectControl
							label={__("Taxonomy Location", "post-type-archive-mapping")}
							options={taxonomyLocationOptions}
							value={attributes.taxonomyLocation}
							onChange={value => {
								onChangeTaxonomyLocation( value );
								setAttributes({ taxonomyLocation: value });
							}}
						/>
					)}
					<ToggleControl
						label={__("Display Post Author", "post-type-archive-mapping")}
						checked={displayPostAuthor}
						onChange={ toggleDisplayPostAuthor }
					/>
					<ToggleControl
						label={__("Display Post Date", "post-type-archive-mapping")}
						checked={displayPostDate}
						onChange={ toggleDisplayPostDate }
					/>
					{'full_content' !== postLayout &&
						<Fragment>
							<ToggleControl
								label={__("Display Post Excerpt", "post-type-archive-mapping")}
								checked={displayPostExcerpt}
								onChange={ toggleDisplayPostExcerpt }
							/>
							{displayPostExcerpt && (
								<TextControl
									label={__(
										"Maximum Word Length of Excerpt",
										"post-type-archive-mapping"
									)}
									type="number"
									value={trimWords}
									onChange={ ( value ) => onTrimWordsChange( value ) }
								/>
							)}
						</Fragment>
					}
					<ToggleControl
						label={__("Display Pagination", "post-type-archive-mapping")}
						checked={pagination}
						onChange={ toggleDisplayPagination }
					/>
					<ToggleControl
						label={__("Change Capitilization", "post-type-archive-mapping")}
						checked={changeCapitilization}
						onChange={ toggleCapitilization }
					/>
					<ToggleControl
						label={__(
							"Display Continue Reading Link",
							"post-type-archive-mapping"
						)}
						checked={displayPostLink}
						onChange={ toggleDisplayPostLink }
					/>
					{displayPostLink && (
						<TextControl
							label={__(
								"Customize Read More Link",
								"post-type-archive-mapping"
							)}
							type="text"
							value={readMoreText}
							onChange={value =>
								setAttributes({ readMoreText: value })
							}
						/>
					)}
					<ToggleControl
						label={__("Overwrite styles", "post-type-archive-mapping")}
						checked={removeStyles}
						onChange={ toggleRemoveStyles }
					/>
				</PanelBody>
				{postLayout === "grid" && !removeStyles && (
					<PanelBody
						title={__("Alignment", "post-type-archive-mapping")}
						initialOpen={false}
					>
						<SelectControl
							label={__("Title Alignment", "post-type-archive-mapping")}
							options={alignmentOptions}
							value={titleAlignment}
							onChange={value => {
								setAttributes({ titleAlignment: value });
							}}
						/>
						<SelectControl
							label={__("Custom Fields Alignment", "post-type-archive-mapping")}
							options={alignmentOptions}
							value={customFieldAlignment}
							onChange={value => {
								setAttributes({ customFieldAlignment: value });
							}}
						/>
						<SelectControl
							label={__("Image Alignment", "post-type-archive-mapping")}
							options={alignmentOptions}
							value={imageAlignment}
							onChange={value => {
								setAttributes({ imageAlignment: value });
							}}
						/>
						<SelectControl
							label={__("Meta Alignment", "post-type-archive-mapping")}
							options={alignmentOptions}
							value={metaAlignment}
							onChange={value => {
								setAttributes({ metaAlignment: value });
							}}
						/>
						<SelectControl
							label={__("Content Alignment", "post-type-archive-mapping")}
							options={alignmentOptions}
							value={contentAlignment}
							onChange={value => {
								setAttributes({ contentAlignment: value });
							}}
						/>
					</PanelBody>
				)}
				{!removeStyles && (
					<Fragment>
						<PanelBody
							title={__("Borders and Padding", "post-type-archive-mapping")}
							initialOpen={false}
						>
							<RangeControl
								label={__("Padding", "post-type-archive-mapping")}
								value={padding}
								onChange={value => setAttributes({ padding: value })}
								min={0}
								max={60}
								step={1}
							/>
							<RangeControl
								label={__("Border", "post-type-archive-mapping")}
								value={border}
								onChange={value => setAttributes({ border: value })}
								min={0}
								max={10}
								step={1}
							/>
							<PanelColorSettings
								title={__("Border Color", "post-type-archive-mapping")}
								initialOpen={true}
								colorSettings={[
									{
										value: borderColor,
										onChange: onChangeBorderColor,
										label: __("Border Color", "post-type-archive-mapping")
									}
								]}
							></PanelColorSettings>
							<RangeControl
								label={__("Border Rounded", "post-type-archive-mapping")}
								value={borderRounded}
								onChange={value =>
									setAttributes({ borderRounded: value })
								}
								min={0}
								max={10}
								step={1}
							/>
						</PanelBody>
						<PanelBody
							title={__("Background and Colors", "post-type-archive-mapping")}
							initialOpen={false}
						>
							<PanelColorSettings
								title={__("Background Color", "post-type-archive-mapping")}
								initialOpen={true}
								colorSettings={[
									{
										value: backgroundColor,
										onChange: onChangeBackgroundColor,
										label: __("Background Color", "post-type-archive-mapping")
									}
								]}
							></PanelColorSettings>
							<PanelColorSettings
								title={__("Title Color", "post-type-archive-mapping")}
								initialOpen={true}
								colorSettings={[
									{
										value: titleColor,
										onChange: onChangeTitleColor,
										label: __("Title Color", "post-type-archive-mapping")
									}
								]}
							></PanelColorSettings>
							<PanelColorSettings
								title={__("Custom Fields Color", "post-type-archive-mapping")}
								initialOpen={true}
								colorSettings={[
									{
										value: customFieldsColor,
										onChange: onChangeCustomFieldsColor,
										label: __(
											"Custom Fields Color",
											"post-type-archive-mapping"
										)
									}
								]}
							></PanelColorSettings>
							<PanelColorSettings
								title={__("Content Color", "post-type-archive-mapping")}
								initialOpen={true}
								colorSettings={[
									{
										value: contentColor,
										onChange: onChangeContentColor,
										label: __("Content Color", "post-type-archive-mapping")
									}
								]}
							></PanelColorSettings>
							<PanelColorSettings
								title={__("Link Color", "post-type-archive-mapping")}
								initialOpen={true}
								colorSettings={[
									{
										value: linkColor,
										onChange: onChangeLinkColor,
										label: __("Link Color", "post-type-archive-mapping")
									}
								]}
							></PanelColorSettings>
							<PanelColorSettings
								title={__(
									"Continue Reading Color",
									"post-type-archive-mapping"
								)}
								initialOpen={true}
								colorSettings={[
									{
										value: continueReadingColor,
										onChange: onChangeContinueReadingColor,
										label: __(
											"Continue Reading Color",
											"post-type-archive-mapping"
										)
									}
								]}
							></PanelColorSettings>
						</PanelBody>
						<PanelBody
							title={__("Fonts", "post-type-archive-mapping")}
							initialOpen={false}
						>
							<SelectControl
								label={__("Title Font", "post-type-archive-mapping")}
								options={fontOptions}
								value={titleFont}
								onChange={value => {
									setAttributes({ titleFont: value });
								}}
							/>
							<SelectControl
								label={__("Custom Fields Font", "post-type-archive-mapping")}
								options={fontOptions}
								value={customFieldsFont}
								onChange={value => {
									setAttributes({ customFieldsFont: value });
								}}
							/>
							<SelectControl
								label={__("Meta Font", "post-type-archive-mapping")}
								options={fontOptions}
								value={metaFont}
								onChange={value => {
									setAttributes({ metaFont: value });
								}}
							/>
							<SelectControl
								label={__("Content Font", "post-type-archive-mapping")}
								options={fontOptions}
								value={contentFont}
								onChange={value => {
									setAttributes({ contentFont: value });
								}}
							/>
							<SelectControl
								label={__("Continue Reading Font", "post-type-archive-mapping")}
								options={fontOptions}
								value={continueReadingFont}
								onChange={value => {
									setAttributes({ continueReadingFont: value });
								}}
							/>
						</PanelBody>
					</Fragment>
				)}
			</InspectorControls>
		);
		if (loading) {
			return (
				<Fragment>
					{inspectorControls}
					<Placeholder>
						<div className="ptam-loading">
							<h1>
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 315.23 341.25" width="42" height="42"><polygon points="315.23 204.75 315.23 68.25 197.02 0 197.02 136.5 315.23 204.75" style={{ fill: "#ffdd01", opacity: 0.8 }} /><polygon points="0 204.75 0 68.25 118.21 0 118.21 136.5 0 204.75" style={{ fill: "#2e3192", opacity: 0.8 }} /><polygon points="157.62 159.25 275.83 91 157.62 22.75 39.4 91 157.62 159.25" style={{ fill: "#86cedc", opacity: 0.8 }} /><polygon points="157.62 341.25 275.83 273 157.62 204.75 39.4 273 157.62 341.25" style={{ fill: "#f07f3b", opacity: 0.8 }} /><polygon points="177.32 170.62 295.53 102.37 295.53 238.87 177.32 307.12 177.32 170.62" style={{ fill: "#c10a26", opacity: 0.8 }} /><polygon points="137.91 170.62 19.7 102.37 19.7 238.87 137.91 307.12 137.91 170.62" style={{ fill: "#662583", opacity: 0.8 }} /></svg>{" "}
								{__("Custom Posts", "post-type-archive-mapping")}
							</h1>
							<h2>
								{__("Finding items...", "post-type-archive-mapping")}{" "}
								<Spinner />
							</h2>
						</div>
					</Placeholder>
				</Fragment>
			);
		}
		if (!hasPosts) {
			return (
				<Fragment>
					{inspectorControls}
					<Placeholder
						icon="admin-post"
						label={__("Custom Posts", "post-type-archive-mapping")}
					>
						{!Array.isArray(latestPosts) ? (
							<Spinner />
						) : (
							__("No posts found.", "post-type-archive-mapping")
						)}
					</Placeholder>
				</Fragment>
			);
		}

		// Removing posts from display should be instant.
		const displayPosts =
			latestPosts.length > postsToShow
				? latestPosts.slice(0, postsToShow)
				: latestPosts;

		const layoutControls = [
			{
				icon: "grid-view",
				title: __("Grid View", "post-type-archive-mapping"),
				onClick: () => setAttributes({ postLayout: "grid", displayPostContent: false }),
				isActive: postLayout === "grid"
			},
			{
				icon: "list-view",
				title: __("List View", "post-type-archive-mapping"),
				onClick: () => setAttributes({ postLayout: "list", displayPostContent: false }),
				isActive: postLayout === "list"
			},
			{
				icon: "admin-page",
				title: __("Full Content View", "post-type-archive-mapping"),
				onClick: () => setAttributes({ postLayout: "full_content", displayPostContent: true }),
				isActive: postLayout === "full_content"
			}
		];

		// Styles
		let titleStyles =
			postLayout === "grid" ? { textAlign: titleAlignment } : {};
		titleStyles.fontFamily = titleFont;
		let customFieldsStyles = {
			fontFamily: customFieldsFont,
			color: customFieldsColor,
			textAlign: customFieldAlignment
		};
		let imageAlignmentStyles =
			postLayout === "grid" ? { textAlign: imageAlignment } : {};
		let metaStyles =
			postLayout === "grid"
				? { textAlign: metaAlignment, color: contentColor }
				: { color: contentColor };
		metaStyles.fontFamily = metaFont;
		let contentStyles =
			postLayout === "grid"
				? { textAlign: contentAlignment, color: contentColor }
				: { color: contentColor };
		contentStyles.fontFamily = contentFont;
		let continueReadingStyles = {
			color: continueReadingColor,
			fontFamily: continueReadingFont
		};

		// Color Styles
		let titleColorStyles = { color: titleColor };
		let linkColorStyles = { color: linkColor };

		// Title tag
		const Titletag = titleHeadingTag;

		return (
			<Fragment>
				{inspectorControls}
				<BlockControls>
					<BlockAlignmentToolbar
						value={align}
						onChange={value => {
							if (undefined == value) {
								value = "wide";
							}
							setAttributes({ align: value });
						}}
						controls={["center", "wide"]}
					/>
					<ToolbarGroup controls={layoutControls} />
				</BlockControls>
				<div
					{ ...blockProps }
				>
					<div
						className={classnames({
							"is-custom": imageLocation !== "regular",
							"is-grid": postLayout === "grid",
							"is-list": postLayout === "list",
							[`columns-${columns}`]: postLayout === "grid",
							"ptam-post-grid-items": "ptam-post-grid-items"
						})}
					>
						{displayPosts.map((post, i) => (
							<article
								key={i}
								className={classnames(
									post.featured_image_src && displayPostImage
										? "has-thumb"
										: "no-thumb"
								)}
								style={!removeStyles ? borderPaddingStyles : {}}
							>
								{displayPostImage &&
									post.featured_image_src !== undefined &&
									post.featured_image_src &&
									"regular" === attributes.imageLocation ? (
									<div
										className="ptam-block-post-grid-image"
										style={!removeStyles ? imageAlignmentStyles : {}}
									>
										<a href={post.link} target="_blank" rel="bookmark">
											{htmlToReactParser.parse(post.featured_image_src)}
										</a>
									</div>
								) : null}

								<div className="ptam-block-post-grid-text">
									{displayTitle && (
										<Titletag
											className="ptam-block-post-grid-title"
											style={!removeStyles ? titleStyles : {}}
										>
											{displayTitleLink &&
												<a
													href={post.link}
													target="_blank"
													rel="bookmark"
													style={!removeStyles ? titleColorStyles : {}}
												>
													{decodeEntities(post.post_title.trim()) ||
														__("(Untitled)", "post-type-archive-mapping")}
												</a>
											}
											{false === displayTitleLink &&
												<Fragment>
													{decodeEntities(post.post_title.trim()) ||
														__("(Untitled)", "post-type-archive-mapping")}
												</Fragment>
											}
										</Titletag>
									)}
									{displayPostImage &&
										post.featured_image_src !== undefined &&
										post.featured_image_src &&
										"below_title" === attributes.imageLocation ? (
										<div
											className="ptam-block-post-grid-image"
											style={!removeStyles ? imageAlignmentStyles : {}}
										>
											<a href={post.link} target="_blank" rel="bookmark">
												{htmlToReactParser.parse(post.featured_image_src)}
											</a>
										</div>
									) : null}

									{displayCustomFields && (
										<div
											className="ptam-block-post-custom-fields"
											style={!removeStyles ? customFieldsStyles : {}}
										>
											{htmlToReactParser.parse(customFields)}
										</div>
									)}

									<div
										className={`ptam-block-post-grid-byline ${capitilization}`}
										style={!removeStyles ? metaStyles : {}}
									>
										{displayPostAuthor &&
											post.author_info.display_name !== "undefined" &&
											post.author_info.display_name && (
												<div className="ptam-block-post-grid-author">
													<a
														className="ptam-text-link"
														target="_blank"
														href={post.author_info.author_link}
														style={!removeStyles ? linkColorStyles : {}}
													>
														{post.author_info.display_name}
													</a>
												</div>
											)}

										{displayPostDate && post.post_date_gmt && (
											<time
												dateTime={dayjs(post.post_date_gmt).format()}
												className={"ptam-block-post-grid-date"}
											>
												{dayjs(post.post_date_gmt).format("MMMM DD, YYYY")}
											</time>
										)}
										{userTaxonomiesArray.length > 0 &&
											displayTaxonomies &&
											"regular" === taxonomyLocation && (
												<div>
													{userTaxonomiesArray.map(key => {
														if (post.terms[key.value] !== false) {
															return (
																<div className="ptam-terms" key={key.value}>
																	<span className="ptam-term-label">
																		{key.label}:{" "}
																	</span>
																	<span
																		className="ptam-term-values"
																		style={linkColorStyles}
																	>
																		{htmlToReactParser.parse(
																			post.terms[key.value]
																		)}
																	</span>
																</div>
															);
														}
													})}
												</div>
											)}
										{displayPostImage &&
											post.featured_image_src !== undefined &&
											post.featured_image_src &&
											"below_title_and_meta" === attributes.imageLocation ? (
											<div
												className="ptam-block-post-grid-image"
												style={!removeStyles ? imageAlignmentStyles : {}}
											>
												<a href={post.link} target="_blank" rel="bookmark">
													{htmlToReactParser.parse(post.featured_image_src)}
												</a>
											</div>
										) : null}
									</div>

									<div
										className="ptam-block-post-grid-excerpt"
										style={!removeStyles ? contentStyles : {}}
									>
										{displayPostExcerpt && "" !== post.post_excerpt && 'full_content' !== postLayout && (
											<Fragment>
												{ excerptParse( post.post_excerpt ) }
											</Fragment>
										)}
										{displayPostContent && 'full_content' === postLayout &&
											<Fragment>
												{htmlToReactParser.parse(post.post_content)}
											</Fragment>
										}

										{displayPostLink && (
											<p>
												<a
													className="ptam-block-post-grid-link ptam-text-link"
													href={post.link}
													target="_blank"
													rel="bookmark"
													style={!removeStyles ? continueReadingStyles : {}}
												>
													{readMoreText}
												</a>
											</p>
										)}
										{displayPostImage &&
											post.featured_image_src !== undefined &&
											post.featured_image_src &&
											"bottom" === attributes.imageLocation ? (
											<div
												className="ptam-block-post-grid-image"
												style={!removeStyles ? imageAlignmentStyles : {}}
											>
												<a href={post.link} target="_blank" rel="bookmark">
													{htmlToReactParser.parse(post.featured_image_src)}
												</a>
											</div>
										) : null}
									</div>
									{userTaxonomiesArray.length > 0 &&
										displayTaxonomies &&
										"below_content" === taxonomyLocation && (
											<div style={metaStyles}>
												{userTaxonomiesArray.map(key => {
													if (post.terms[key.value] !== false) {
														return (
															<div className="ptam-terms">
																<span className="ptam-term-label">
																	{key.label}:{" "}
																</span>
																<span
																	className="ptam-term-values"
																	style={!removeStyles ? linkColorStyles : {}}
																>
																	{htmlToReactParser.parse(
																		post.terms[key.value]
																	)}
																</span>
															</div>
														);
													}
												})}
											</div>
										)}
								</div>
							</article>
						))}
					</div>
				</div>
			</Fragment>
		);
}

export default PTAM_Custom_PostsEdit;
