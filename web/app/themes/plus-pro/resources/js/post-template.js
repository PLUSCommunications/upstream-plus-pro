/* For post-template blocks */
document.addEventListener('DOMContentLoaded', function() {
  
  // Check if we have any carousel-style post templates
  const carouselPostTemplates = document.querySelectorAll('.wp-block-post-template.is-style-carousel');
  
  if (carouselPostTemplates.length > 0) {
    // Dynamically load Swiper if it's not already loaded
    if (typeof Swiper === 'undefined') {
      // Check if Swiper script is already in the page
      if (!document.querySelector('script[src*="swiper"]')) {
        const script = document.createElement('script');
        script.src = '/wp-content/themes/plus-pro/resources/js/swiper-bundle.min.js';
        script.onload = function() {
          initializeCarousels();
        };
        document.head.appendChild(script);
      } else {
        // Swiper script exists but may not be loaded yet
        const checkSwiper = setInterval(() => {
          if (typeof Swiper !== 'undefined') {
            clearInterval(checkSwiper);
            initializeCarousels();
          }
        }, 100);
      }
    } else {
      // Swiper is already available
      initializeCarousels();
    }
  }
  
  function initializeCarousels() {
    
    carouselPostTemplates.forEach(function(carousel, index) {      
      // Ensure we have a valid DOM element
      if (!carousel || !carousel.nodeType) {
        console.error('Invalid carousel element at index', index);
        return;
      }
      
      // Get computed styles BEFORE we modify the DOM
      const computedStyle = window.getComputedStyle(carousel);
      const display = computedStyle.display;
      const gridTemplateColumns = computedStyle.gridTemplateColumns;
      const gap = computedStyle.gap || computedStyle.gridGap || '30px'; // fallback to 30px
      
      // First, we need to structure the DOM for Swiper
      // Wrap the list items in a swiper-wrapper
      const listItems = Array.from(carousel.children);
      
      // Add swiper classes
      carousel.classList.add('swiper');
      
      // Create swiper-wrapper and move all li elements into it
      const swiperWrapper = document.createElement('div');
      swiperWrapper.classList.add('swiper-wrapper');
      
      // Move all li elements to swiper-wrapper and add swiper-slide class
      listItems.forEach(function(li) {
        li.classList.add('swiper-slide');
        swiperWrapper.appendChild(li);
      });
      
      // Add wrapper to carousel
      carousel.appendChild(swiperWrapper);
      
      // Add navigation and pagination elements
      const swiperPagination = document.createElement('div');
      swiperPagination.classList.add('swiper-pagination');
      carousel.appendChild(swiperPagination);
      
      const swiperButtonNext = document.createElement('div');
      swiperButtonNext.classList.add('swiper-button-next');
      carousel.appendChild(swiperButtonNext);
      
      const swiperButtonPrev = document.createElement('div');
      swiperButtonPrev.classList.add('swiper-button-prev');
      carousel.appendChild(swiperButtonPrev);
      
      // Convert gap to number (remove 'px' and parse)
      const gapValue = parseInt(gap.replace('px', '')) || 30;
      
      let swiperConfig = {
        spaceBetween: gapValue,
        watchSlidesProgress: true,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        }
      };
      
      // Determine slides per view based on the original layout
      if (display === 'grid' && gridTemplateColumns && gridTemplateColumns !== 'none') {
        // Check if it's manual columns (repeat pattern) or auto columns (minmax pattern)
        if (gridTemplateColumns.includes('repeat(')) {
          // Manual columns - extract the number
          const repeatMatch = gridTemplateColumns.match(/repeat\((\d+)/);
          if (repeatMatch) {
            const columnCount = parseInt(repeatMatch[1]);
            swiperConfig.slidesPerView = columnCount;
          } else {
            swiperConfig.slidesPerView = 1; // fallback
          }
        } else if (gridTemplateColumns.includes('minmax(')) {
          // Auto columns with minimum width - extract the min width
          const minmaxMatch = gridTemplateColumns.match(/minmax\(([^,]+)/);
          if (minmaxMatch) {
            const minWidth = minmaxMatch[1].trim();
            swiperConfig.slidesPerView = 'auto';
            // Set slide width based on minimum column width
            carousel.style.setProperty('--swiper-slide-width', minWidth);
            carousel.classList.add('swiper-auto');
          } else {
            swiperConfig.slidesPerView = 'auto';
            carousel.classList.add('swiper-auto');
          }
        } else {
          // Other grid configurations - count the columns
          const columns = gridTemplateColumns.split(' ').filter(col => col.trim() !== '');
          swiperConfig.slidesPerView = Math.max(1, columns.length);
        }
      } else {
        // Non-grid layouts (list, flex, etc.) - default to 1 slide per view
        swiperConfig.slidesPerView = 1;
      }
      
      // Add responsive breakpoints - decrement slides per view at smaller screens
      if (typeof swiperConfig.slidesPerView === 'number' && swiperConfig.slidesPerView > 1) {
        const baseSlidesPerView = swiperConfig.slidesPerView;
        
        swiperConfig.breakpoints = {
          // 1200px and up: use the original slidesPerView
          1200: {
            slidesPerView: baseSlidesPerView
          },
          // 980px to 1199px: decrement by 1 (minimum 1)
          980: {
            slidesPerView: Math.max(1, baseSlidesPerView - 1)
          },
          // 600px to 979px: decrement by 2 (minimum 1)  
          600: {
            slidesPerView: Math.max(1, baseSlidesPerView - 2)
          },
          // Under 600px: always 1 slide
          0: {
            slidesPerView: 1
          }
        };
      }
      
      try {
        const swiper = new Swiper(carousel, swiperConfig);
      } catch (error) {
        console.error('Error initializing Swiper:', error);
      }
    });
  }
	
});

