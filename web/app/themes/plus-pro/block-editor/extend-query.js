const { addFilter } = window.myThemeEditor.hooks;

// Register new attributes *before* the editor loads the block
addFilter(
	'blocks.registerBlockType',
	'mytheme/query/manual-selection-attributes',
	(settings, name) => {
		if (name !== 'core/query') return settings;

		settings.attributes = {
			...settings.attributes,
			manualPostIds: {
				type: 'array',
				default: [],
				items: {
					type: 'number',
				},
			},
		};

		return settings;
	}
);

wp.domReady(() => {
	const { createHigherOrderComponent } = window.myThemeEditor.compose;
	const { InspectorControls } = window.myThemeEditor.blockEditor;
	const {
		PanelBody,
		FormTokenField,
		Spinner,
	} = window.myThemeEditor.components;
	const {
		Fragment,
		createElement,
		useState,
		useEffect,
	} = window.myThemeEditor.element;
	const { apiFetch } = window.myThemeEditor;

	// 1. Add manualPostIds attribute
	const addManualSelectionAttributes = (settings, name) => {
		if (name !== 'core/query') return settings;

		settings.attributes = {
			...settings.attributes,
			manualPostIds: {
				type: 'array',
				default: [],
				items: {
					type: 'number',
				},
			},
		};

		return settings;
	};

	addFilter(
		'blocks.registerBlockType',
		'mytheme/query/manual-selection-attributes',
		addManualSelectionAttributes
	);

	// 2. UI: Inject into block editor
	const withManualSelectionControls = createHigherOrderComponent(
		(BlockEdit) => {
			return function (props) {
				if (props.name !== 'core/query') {
					return createElement(BlockEdit, props);
				}

				const { attributes, setAttributes } = props;
				const { query, manualPostIds } = attributes;
				const postTypeRaw = query?.postType;
				const postType = Array.isArray(postTypeRaw) ? postTypeRaw[0] : postTypeRaw || 'post';

				const [postList, setPostList] = useState([]);
				const [loading, setLoading] = useState(false);

				useEffect(() => {
				  if (!postType || typeof postType !== 'string') return;

				  setLoading(true);
					console.log('Post type for fetch:', postType);
					console.log('Attempting to fetch from:', `/wp/v2/${postType}?per_page=100&_fields=id,title`);

				  apiFetch({
				    path: `/wp/v2/${postType}?per_page=100&_fields=id,title`,
				  })
				    .then((results) => {
							// Ensure all selected manualPostIds are present in the list
							const existingIds = results.map(p => p.id);
							const missingIds = (Array.isArray(manualPostIds) ? manualPostIds : []).filter(id => !existingIds.includes(id));
							console.log('Existing IDs from API:', existingIds);
							console.log('Currently selected manualPostIds:', manualPostIds);
							console.log('Missing IDs:', missingIds);
							if (missingIds.length) {
							  apiFetch({
							    path: `/wp/v2/${postType}?include=${missingIds.join(',')}&_fields=id,title`,
							  })
							    .then((extras) => {
										console.log('Fetched missing posts:', extras);
							      setPostList([...results, ...extras]);
							    })
							    .catch(() => {
							      // fallback: show what we have
							      setPostList(results);
							    });
							} else {
							  setPostList(results);
							}
				    })
				    .catch((error) => {
				      console.warn('Manual post picker error:', error);
				      setPostList([]);
				    })
				    .finally(() => setLoading(false));
				}, [postType, manualPostIds]);

				
				return createElement(
					Fragment,
					null,
					createElement(BlockEdit, props),
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: 'Post Selection', initialOpen: false },
							loading
								? createElement(Spinner, null)
								: (function () {
									const safePosts = Array.isArray(postList) ? postList : [];

									const titleToIdMap = {};
									const uniqueTitles = [];

									safePosts.forEach((post) => {
										const title = post.title?.rendered?.trim();
										if (title && !titleToIdMap[title.toLowerCase()]) {
											titleToIdMap[title.toLowerCase()] = post.id;
											uniqueTitles.push(title);
										}
									});

									const selectedTitles = Array.isArray(manualPostIds)
									  ? manualPostIds.map((id) => {
									      const match = safePosts.find((p) => p.id === id);
									      if (match?.title?.rendered?.trim()) {
									        return match.title.rendered.trim();
									      }
									      // Fallback title for unknown IDs
									      return `[Post #${id}]`;
									    })
									  : [];

									return createElement(FormTokenField, {
										label: `Select ${postType} items`,
										value: selectedTitles,
										suggestions: uniqueTitles,
										onChange: (tokens) => {
										  const ids = tokens
										    .map((title) => titleToIdMap[title.toLowerCase().trim()])
										    .filter(Boolean);

										  const existingQuery = attributes.query || {};

										  // Toggle offset to force re-fetch
										  const toggledOffset = existingQuery.offset === 1 ? 0 : 1;

										  setAttributes({
										    manualPostIds: ids,
										    query: {
										      ...existingQuery,
										      offset: toggledOffset,
										      manualPostIds: ids, // include manualPostIds inside query to pass through REST
										    },
										  });
										}
									});
								})()
						)
					)
				);
			};
		},
		'withManualSelectionControls'
	);

	addFilter(
		'editor.BlockEdit',
		'mytheme/query/manual-selection-controls',
		withManualSelectionControls
	);
	
});
