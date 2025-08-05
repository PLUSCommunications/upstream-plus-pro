wp.domReady(() => {
  const { registerFormatType, toggleFormat } = wp.richText;
  const { RichTextToolbarButton } = wp.blockEditor; // Use RichTextToolbarButton for proper placement
  const { createElement } = wp.element;
  
  registerFormatType('pluspro/countup', {
    title: 'PRO Count Up',
    tagName: 'span', // The tag applied to the selected text
    className: 'pro-countup',
    scope: 'inline',
    edit({ isActive, value, onChange }) {
      return createElement(
        RichTextToolbarButton,
        {
          icon: 'plus',
          title: 'PRO Count Up',
          isActive: isActive,
          onClick: () => {
            onChange( toggleFormat(value, { type: 'pluspro/countup' }) );
          },
        }
      );
    }
  });
});
