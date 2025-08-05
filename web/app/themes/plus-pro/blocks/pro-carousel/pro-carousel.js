class proCarousel {
  constructor( block, data ) {
    this.data   = data;
    this.swiper = false;
    this.editor = undefined !== window.acf;
    
    this.$block   = typeof block == 'string' ? document.querySelector(`[data-block="${block}"]`) : block;
    this.$wrapper = this.$block.querySelector('.swiper-wrapper');
    
    if ( this.editor ) {
      this.$block.addEventListener('click', (e) => {
        if ( !(e.target.classList.contains('swiper-button-next') || e.target.classList.contains('swiper-button-prev')) ) {
          this.$wrapper.classList.remove('swiper-wrapper');
          this.swiper.destroy();
        }
        
        if ( this.swiper.destroyed && (e.target.classList.contains('swiper-button-next') || e.target.classList.contains('swiper-button-prev')) ) {
          this.$wrapper.classList.add('swiper-wrapper');
          
          this.init();
        }
      });
    }
    
    this.init();
  }
  init() {
    if ( this.editor ) {
      setTimeout(() => {
        this.slides();
        this.swiperInit();
      }, 500);
    }
    else {
      this.slides();
      this.swiperInit();
    }
  }
  slides() {
    this.$slides = [...this.$wrapper.querySelectorAll(':scope > *:not(.block-list-appender)')];
    
    this.$slides.forEach((slide, i) => {
      slide.classList.add('swiper-slide');
      
      if ( !this.editor && this.data.shuffle ) {
        const j = Math.floor(Math.random() * (i + 1));
        [this.$slides[i], this.$slides[j]] = [this.$slides[j], this.$slides[i]];
      }
    });
    
    if ( !this.editor && this.data.shuffle ) {
      this.$slides.forEach((slide) => {
        this.$wrapper.appendChild(slide);
      });
    }
  }
  swiperInit() {
    this.swiperArgs = {
      navigation: {
        nextEl: this.$block.querySelector('.swiper-button-next'),
        prevEl: this.$block.querySelector('.swiper-button-prev')
      },
      pagination: {
        el: this.$block.querySelector('.swiper-pagination'),
        type: this.data.paging ? this.data.paging : false
      },
      centeredSlides: this.data.centered ? true : false,
      effect: this.data.effect ? this.data.effect : false,
      loop: this.data.loop ? true : false,
      slidesPerView: this.data.slides_per_view_mobile ? this.data.slides_per_view_mobile : 1 ,
      spaceBetween: this.data.slide_gap ? this.data.slide_gap : 0 ,
      watchSlidesProgress: true,
	  breakpoints: {
        600: {
          slidesPerView: this.data.slides_per_view_tablet ? this.data.slides_per_view_tablet : 1 ,
        },
        980: {
          slidesPerView: this.data.slides_per_view ? this.data.slides_per_view : 1 ,
        },
      }
    };
    
    if ( this.data.delay ) {
      this.swiperArgs.autoplay       = [];
      this.swiperArgs.autoplay.delay = this.data.delay*1000;
    }
    
    if ( this.$slides.length > 8 ) {
       /* might be able to remove the conditional and just code them directly into swiperArgs */
      this.swiperArgs.pagination.dynamicBullets     = true;
      this.swiperArgs.pagination.dynamicMainBullets = 5;
    }
    
    let $swiper = this.editor ? this.$block.querySelector('.swiper') : this.$block;
    
    this.swiper = new Swiper($swiper, this.swiperArgs);
  }
}

var proCarouselFn = function( block, data ) {
  return new proCarousel(block, data);
}

let proCarouselBlocks = {};

document.addEventListener('DOMContentLoaded', function() {
  if ( window.acf ) {
    window.acf.addAction('render_block_preview/type=pluspro/pro-carousel', (el, fields) => {
      let id = el[0].id;
      
      if ( proCarouselBlocks[id] ) {
        proCarouselBlocks[id].$wrapper.classList.add('swiper-wrapper');
        
        proCarouselBlocks[id].init();
      }
      else if ( !proCarouselBlocks[id] ) proCarouselBlocks[id] = proCarouselFn(el[0], fields.data);
    });
  }
});
