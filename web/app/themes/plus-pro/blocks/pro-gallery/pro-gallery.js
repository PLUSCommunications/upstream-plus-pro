/* globals PhotoSwipe */
/* exported proGallery */
class proGallery {
  constructor(block, data) {
    this.data = data;
    this.$block = typeof block === 'string'
      ? document.querySelector(`[data-block="${block}"]`)
      : block;

    if (!this.$block) return;

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.init());
    } else {
      this.init();
    }
  }

  init() {
		
    const items = Array.from(this.$block.querySelectorAll('a[href]')).map(link => {
      const type = link.dataset.type || 'image';
      const caption = link.dataset.caption || '';
      const href = link.getAttribute('href');

      if (type === 'html') {
        const contentEl = document.querySelector(href);
        return {
          html: contentEl ? contentEl.innerHTML : '',
          caption
        };
      }

      const width = parseInt(link.dataset.pswpWidth, 10) || 1600;
      const height = parseInt(link.dataset.pswpHeight, 10) || 900;

      return {
        src: href,
        width,
        height,
        caption
      };
    });

    const lightbox = new PhotoSwipeLightbox({
      gallery: `[data-block="${this.$block.dataset.block}"]`,
      children: 'a[href]',
      pswpModule: PhotoSwipe,
      dataSource: items
    });
		
		console.log('Lightbox constructor exists:', typeof PhotoSwipeLightbox);
		console.log('addFilter function exists:', typeof lightbox.addFilter);
		

		lightbox.on('uiRegister', function() {
		  lightbox.pswp.ui.registerElement({
		    name: 'download-button',
		    order: 8,
		    isButton: true,
		    tagName: 'a',

		    // SVG with outline
		    html: {
		      isCustomSVG: true,
		      inner: '<path d="M20.5 14.3 17.1 18V10h-2.2v7.9l-3.4-3.6L10 16l6 6.1 6-6.1ZM23 23H9v2h14Z" id="pswp__icn-download"/>',
		      outlineID: 'pswp__icn-download'
		    },

		    onInit: (el, pswp) => {
		      el.setAttribute('download', '');
		      el.setAttribute('target', '_blank');
		      el.setAttribute('rel', 'noopener');

		      pswp.on('change', () => {
		        console.log('change');
		        el.href = pswp.currSlide.data.src;
		      });
		    }
		  });
		});
		
		new PhotoSwipeDynamicCaption(lightbox, {
		    type: 'auto', // 'auto', 'below', or 'aside'
		 });

    lightbox.init();
  }
}
