class proLogos {
  constructor( block ) {
    this.$block        = typeof block == 'string' ? document.querySelector(`[data-block="${block}"]`) : block;
    this.$wrapper = this.$block.querySelector('.pro-logos--swiper');
    this.swiper = false;
        
    if ( this.$wrapper ) {
      this.swiper = new Swiper(this.$block, {
        slidesPerView: 'auto',
        spaceBetween: this.$wrapper.dataset.gap,
        autoplay: {
          delay: 3000,
        },
				centeredSlides: true,
				loop: true,
        watchSlidesProgress: true,
        pagination: {
          el: this.$block.querySelector('.swiper-pagination'),
          type: 'bullets',
          dynamicBullets: true,
          dynamicMainBullets: 5
        }
      });
    }
    else this.swiper = 'missing';
  }
}

var proLogosFn = function( block ) {
  return new proLogos(block);
}

document.addEventListener('DOMContentLoaded', function() {

  if ( window.acf ) {
    let proLogos;
    
    window.acf.addAction('render_block_preview/type=pluspro/pro-logos', (el) => {
      var $wrapper = el[0].querySelector('.pro-logos--swiper');

      if ( $wrapper ) proLogos = proLogosFn(el[0]);
      else if ( proLogos && proLogos.swiper ) proLogos.swiper.destroy();
    });
  }
});
