wp.domReady(() => {
	const { addFilter } = wp.hooks;
	const { InspectorControls, ColorPalette } = wp.blockEditor;
	const {
		ToolsPanel,
		ToolsPanelItem,
		__experimentalToolsPanel,
		__experimentalToolsPanelItem,
		UnitControl,
		__experimentalUnitControl,
		Flex,
		FlexItem,
		__experimentalHStack,
		__experimentalZStack,
		Button,
		Dropdown,
		ColorIndicator,
	} = wp.components;

	const ToolsPanelComponent = ToolsPanel || __experimentalToolsPanel;
	const ToolsPanelItemComponent = ToolsPanelItem || __experimentalToolsPanelItem;
	const UnitControlComponent = UnitControl || __experimentalUnitControl;
	const HStack = __experimentalHStack || Flex;
	const ZStack = __experimentalZStack || Flex;

	const { createHigherOrderComponent } = wp.compose;
	const { Fragment, createElement } = wp.element;

	const SUPPORTED_BLOCKS = [ 'core/heading', 'core/paragraph', 'core/list' ];

	// --- Intended Defaults ---
	const SHADOW_DEFAULTS = {
		textShadowColor: '',
		textShadowOffsetX: '0.2em',
		textShadowOffsetY: '0.2em',
		textShadowBlur:    '0.2em'
	};

	// Register attributes with NO DEFAULTS!
	const addTextShadowAttributes = ( settings, name ) => {
		if ( ! SUPPORTED_BLOCKS.includes( name ) ) return settings;
		settings.attributes = {
			...settings.attributes,
			textShadowColor:   { type: 'string' },
			textShadowOffsetX: { type: 'string' },
			textShadowOffsetY: { type: 'string' },
			textShadowBlur:    { type: 'string' }
		};
		return settings;
	};
	addFilter(
		'blocks.registerBlockType',
		'mytheme/text-shadow/attributes',
		addTextShadowAttributes
	);

	// Helper: Only "active" if user has set a value (attribute is defined and differs from default)
	function isAttrChanged(attributes, key, defaultValue) {
		return (
			Object.prototype.hasOwnProperty.call(attributes, key) &&
			attributes[key] !== defaultValue
		);
	}

	const hasShadowValue = ( attrs ) =>
		isAttrChanged(attrs, 'textShadowColor', SHADOW_DEFAULTS.textShadowColor) ||
		isAttrChanged(attrs, 'textShadowBlur', SHADOW_DEFAULTS.textShadowBlur);

	const hasOffsetValue = ( attrs ) =>
		isAttrChanged(attrs, 'textShadowOffsetX', SHADOW_DEFAULTS.textShadowOffsetX) ||
		isAttrChanged(attrs, 'textShadowOffsetY', SHADOW_DEFAULTS.textShadowOffsetY);

	const hasAnyValue = ( attrs ) =>
		hasShadowValue(attrs) || hasOffsetValue(attrs);

	const generateTextShadowCSS = ( attrs ) => {
		const offsetX = typeof attrs.textShadowOffsetX !== "undefined" ? attrs.textShadowOffsetX : SHADOW_DEFAULTS.textShadowOffsetX;
		const offsetY = typeof attrs.textShadowOffsetY !== "undefined" ? attrs.textShadowOffsetY : SHADOW_DEFAULTS.textShadowOffsetY;
		const blur    = typeof attrs.textShadowBlur    !== "undefined" ? attrs.textShadowBlur    : SHADOW_DEFAULTS.textShadowBlur;
		const color   = typeof attrs.textShadowColor   !== "undefined" ? attrs.textShadowColor   : SHADOW_DEFAULTS.textShadowColor;
		if ( !color ) return '';
		return `${ offsetX } ${ offsetY } ${ blur } ${ color }`;
	};

	// ---- Native-style Color Dropdown (matches 'Text' color control) ----
	function NativeColorDropdown({ value, onChange, label }) {
		const settings = wp.data.select("core/block-editor").getSettings?.() || {};
		const colors = settings.colors || undefined;
		const buttonRef = wp.element.useRef();

		return createElement(
			Dropdown,
			{
				className: "components-dropdown block-editor-panel-color-gradient-settings__dropdown",
				contentClassName: "components-dropdown__content",
				popoverProps: {
					placement: 'left-start',
					anchorRef: buttonRef,
				},
				renderToggle: ({ isOpen, onToggle }) =>
					createElement(
						Button,
						{
							"aria-expanded": isOpen,
							className:
								"components-button block-editor-panel-color-gradient-settings__dropdown is-next-40px-default-size",
							onClick: onToggle,
							style: {
								width: "100%",
								border: "1px solid #ddd",
								borderRadius: "2px",
								background: "#fff"
							},
							ref: buttonRef
						},
						createElement(
							HStack,
							{
								className: "components-flex components-h-stack",
								style: { justifyContent: "flex-start", alignItems: "center", width: "100%" }
							},
							createElement(
								ZStack,
								{ className: "components-z-stack", style: { lineHeight: "0" } },
								createElement(
									"div",
									{ className: "components-flex" },
									createElement(ColorIndicator, {
										colorValue: value || undefined,
										className: "component-color-indicator",
									})
								)
							),
							createElement(
								FlexItem,
								{
									className:
										"components-flex-item block-editor-panel-color-gradient-settings__color-name",
								},
								label
							)
						)
					),
				renderContent: () =>
					createElement(ColorPalette, {
						value: value || "",
						onChange: onChange,
						disableCustomColors: false,
						clearable: true,
						colors: colors,
						enableAlpha: true
					}),
			}
		);
	}
	// ------------------------------------------------------------

	// UI
	const withTextShadowControls = createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			if ( ! SUPPORTED_BLOCKS.includes( props.name ) ) {
				return createElement( BlockEdit, props );
			}
			const { attributes, setAttributes } = props;

			const shadowColor   = typeof attributes.textShadowColor !== "undefined" ? attributes.textShadowColor : SHADOW_DEFAULTS.textShadowColor;
			const offsetX       = typeof attributes.textShadowOffsetX !== "undefined" ? attributes.textShadowOffsetX : SHADOW_DEFAULTS.textShadowOffsetX;
			const offsetY       = typeof attributes.textShadowOffsetY !== "undefined" ? attributes.textShadowOffsetY : SHADOW_DEFAULTS.textShadowOffsetY;
			const blur          = typeof attributes.textShadowBlur    !== "undefined" ? attributes.textShadowBlur    : SHADOW_DEFAULTS.textShadowBlur;

			// Only set the attribute the user interacted with!
			const onShadowColorChange = ( color ) => setAttributes({ textShadowColor: color });
			const onBlurChange = ( value ) => setAttributes({ textShadowBlur: value });
			const onOffsetXChange = ( value ) => setAttributes({ textShadowOffsetX: value });
			const onOffsetYChange = ( value ) => setAttributes({ textShadowOffsetY: value });

			const resetShadow = () => setAttributes({
				textShadowColor: undefined,
				textShadowBlur: undefined
			});
			const resetOffset = () => setAttributes({
				textShadowOffsetX: undefined,
				textShadowOffsetY: undefined
			});
			const resetAll = () => setAttributes({
				textShadowColor: undefined,
				textShadowBlur: undefined,
				textShadowOffsetX: undefined,
				textShadowOffsetY: undefined
			});

			// Shadow/color/blur
			const shadowControl = createElement(
				ToolsPanelItemComponent,
				{
					hasValue: () => hasShadowValue( attributes ),
					label: 'Shadow',
					onDeselect: resetShadow,
					isShownByDefault: false,
					panelId: 'shadow-settings',
				},
				createElement(
					Flex,
					{
						direction: 'column',
						gap: '16px'
					},
					createElement(
						NativeColorDropdown,
						{
							label: 'Color',
							value: shadowColor,
							onChange: onShadowColorChange,
						}
					),
					createElement(
						UnitControlComponent,
						{
							label: 'Blur',
							value: blur,
							onChange: onBlurChange,
							units: [
								{ value: 'px', label: 'px' },
								{ value: 'em', label: 'em' },
								{ value: 'rem', label: 'rem' }
							],
							min: 0,
							max: 20,
							step: 0.1,
							className: 'patch-unit-control-component'
						}
					)
				),
			);

			// Offset X/Y
			const offsetControl = createElement(
				ToolsPanelItemComponent,
				{
					hasValue: () => hasOffsetValue( attributes ),
					label: 'Offset',
					onDeselect: resetOffset,
					isShownByDefault: false,
					panelId: 'shadow-settings',
				},
				createElement(
					HStack,
					{ justify: 'flex-start' },
					createElement(
						UnitControlComponent,
						{
							label: 'X',
							labelPosition: 'top',
							value: offsetX,
							onChange: onOffsetXChange,
							units: [
								{ value: 'px', label: 'px' },
								{ value: 'em', label: 'em' },
								{ value: 'rem', label: 'rem' }
							],
							min: -20,
							max: 20,
							step: 0.1,
							className: 'patch-unit-control-component'
						}
					),
					createElement(
						UnitControlComponent,
						{
							label: 'Y',
							labelPosition: 'top',
							value: offsetY,
							onChange: onOffsetYChange,
							units: [
								{ value: 'px', label: 'px' },
								{ value: 'em', label: 'em' },
								{ value: 'rem', label: 'rem' }
							],
							min: -20,
							max: 20,
							step: 0.1,
							className: 'patch-unit-control-component'
						}
					)
				)
			);

			return createElement(
				Fragment,
				null,
				createElement( BlockEdit, props ),
				createElement(
					InspectorControls,
					{ group: 'styles' },
					createElement(
						ToolsPanelComponent,
						{
							label: 'Shadow',
							resetAll: resetAll,
							panelId: 'shadow-settings',
							hasActiveControls: hasAnyValue(attributes)
						},
						shadowControl,
						offsetControl
					)
				)
			);
		};
	}, 'withTextShadowControls' );

	addFilter(
		'editor.BlockEdit',
		'mytheme/text-shadow/controls',
		withTextShadowControls
	);

	// Editor preview
	const withEditorTextShadowStyles = createHigherOrderComponent( ( BlockListBlock ) => {
		return ( props ) => {
			if ( ! SUPPORTED_BLOCKS.includes( props.name ) ) {
				return createElement( BlockListBlock, props );
			}
			const attrs = props.attributes;
			const color = typeof attrs.textShadowColor !== "undefined" ? attrs.textShadowColor : SHADOW_DEFAULTS.textShadowColor;
			if ( ! color ) {
				return createElement( BlockListBlock, props );
			}
			const shadowCSS = generateTextShadowCSS( attrs );
			return createElement( BlockListBlock, {
				...props,
				wrapperProps: {
					...( props.wrapperProps || {} ),
					style: {
						...( props.wrapperProps?.style || {} ),
						textShadow: shadowCSS,
					},
				},
			} );
		};
	}, 'withEditorTextShadowStyles' );

	addFilter(
		'editor.BlockListBlock',
		'mytheme/text-shadow/editor-styles',
		withEditorTextShadowStyles
	);

	// Frontend
	addFilter(
		'blocks.getSaveElement',
		'mytheme/text-shadow/save-element',
		( element, blockType, attributes ) => {
			if ( ! SUPPORTED_BLOCKS.includes( blockType.name ) ) return element;
			const color = typeof attributes.textShadowColor !== "undefined" ? attributes.textShadowColor : SHADOW_DEFAULTS.textShadowColor;
			if ( ! color ) return element;
			const shadowCSS = generateTextShadowCSS( attributes );
			return wp.element.cloneElement( element, {
				style: {
					...( element.props.style || {} ),
					textShadow: shadowCSS,
				},
			} );
		}
	);
});
