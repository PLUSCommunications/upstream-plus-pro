wp.domReady(() => {
	const { addFilter } = window.myThemeEditor.hooks;
	const { InspectorControls } = window.myThemeEditor.blockEditor;
	const { ToggleControl, PanelBody } = window.myThemeEditor.components;
	const { createHigherOrderComponent } = window.myThemeEditor.compose;
	const { Fragment, useEffect, createElement } = window.myThemeEditor.element;
	
	const videoAttributes = {
		autoplayVideo: { label: 'Autoplay', default: false },
		loopVideo: { label: 'Loop', default: false },
		mutedVideo: { label: 'Muted', default: false },
		controlsVideo: { label: 'Controls', default: true },
		playsinlineVideo: { label: 'Plays Inline', default: true },
	};
	
	// Method 1: Try the original filter approach
	const addVideoAttributes = (settings, name) => {
		if (name !== 'core/media-text') return settings;
	
		settings.attributes = {
			...(settings.attributes || {}),
			...Object.fromEntries(
				Object.entries(videoAttributes).map(([key, config]) => [
					key,
					{ type: 'boolean', default: config.default },
				])
			),
		};
	
		return settings;
	};
	
	addFilter(
		'blocks.registerBlockType',
		'mytheme/media-text/video-attributes',
		addVideoAttributes
	);
	
	// Method 2: Direct approach - modify the block type after registration
	setTimeout(() => {
		const mediaTextBlock = wp.blocks.getBlockType('core/media-text');
		if (mediaTextBlock) {
			// Add our attributes directly
			Object.entries(videoAttributes).forEach(([key, config]) => {
				if (!mediaTextBlock.attributes[key]) {
					mediaTextBlock.attributes[key] = {
						type: 'boolean',
						default: config.default
					};
				}
			});
		}
	}, 100);
	
	const withVideoControls = createHigherOrderComponent((BlockEdit) => {
		return (props) => {
			if (props.name !== 'core/media-text') {
				return createElement(BlockEdit, props);
			}

			const { attributes, setAttributes, clientId } = props;
			const { mediaType } = attributes;

			if (mediaType !== 'video') {
				return createElement(BlockEdit, props);
			}
	
			useEffect(() => {
				const block = document.querySelector(`[data-block="${clientId}"]`);
				if (!block) return;
	
				const video = block.querySelector('video');
				if (!video) return;
	
				// Apply attributes
				Object.entries(videoAttributes).forEach(([key, { default: defaultVal }]) => {
					const htmlAttr = key.replace('Video', '');
					const enabled = attributes[key];
	
					if (htmlAttr === 'muted') {
						video.muted = !!enabled;
					} else {
						if (enabled) {
							video.setAttribute(htmlAttr, '');
						} else {
							video.removeAttribute(htmlAttr);
						}
					}
				});
	
				// Handle autoplay ON
				if (attributes.autoplayVideo && attributes.mutedVideo) {
					video
						.play()
						.catch((err) => console.warn('Autoplay failed in editor preview:', err));
				}
	
				// Handle autoplay OFF
				if (!attributes.autoplayVideo) {
					video.pause();
					video.currentTime = 0; // Optional: reset video
				}
			}, [attributes, clientId]);
			
	
			return createElement(
				Fragment,
				null,
				createElement(BlockEdit, props),
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: 'Pro Video Options', initialOpen: true },
						Object.entries(videoAttributes).map(([key, config]) =>
							createElement(ToggleControl, {
								key,
								label: config.label,
								checked: !!attributes[key],
								onChange: (val) => {
									if (key === 'autoplayVideo' && val && !attributes.mutedVideo) {
										setAttributes({ mutedVideo: true });
									}
									setAttributes({ [key]: val });
								},
							})
						)
					)
				)
			);
		};
	}, 'withVideoControls');
	
	addFilter(
		'editor.BlockEdit',
		'mytheme/media-text/video-controls',
		withVideoControls
	);
});