(function($){
  
  $(function(){
    
  // Visibility observer for animation triggering

    function observerInit(){
      
      var targetsA = [].slice.call(document.querySelectorAll('main > *:not(.entry-content)')),
          targetsB = [].slice.call(document.querySelectorAll('.entry-content > *')),
          targets = targetsA.concat(targetsB);
          config = {
            rootMargin: '0px',
            threshold: 0.1
          };
            
      if ( !('IntersectionObserver' in window) ) {
        targets.forEach(function(target) {
          if ( target.tagName !== 'FIGURE' ) target.classList.add('visible');
        });
      } else {
        let observer = new IntersectionObserver(onIntersection, config);
        targets.forEach(target => {
          observer.observe(target);
        });
      }
    }
    function onIntersection(entries) {
      entries.forEach(entry => {
        if ( entry.target.tagName !== 'FIGURE' ) {
          if ( entry.isIntersecting ) entry.target.classList.add('visible');
          else entry.target.classList.remove('visible');
        }
      });
    }
    observerInit();
    
    /* For scroll classes */
    var c, currentScrollTop = 0;

    function nav() {
      const header = document.querySelector('header.wp-block-template-part');
      
      if ( header ) {
        if (!header.firstElementChild.classList.contains("is-position-static")) { // Ensures we don't put scroll effects on static bars
          const pos              = document.documentElement.scrollTop || document.body.scrollTop,
                currentScrollTop = pos;
                
          if ( typeof c === 'undefined' ) window.c = 0; // Initialize c if not already defined
          
          if ( c < currentScrollTop && pos > 100 ) header.classList.add("scrolled--down");
          else if (c > currentScrollTop && !(pos <= 100)) header.classList.remove("scrolled--down");
  
          if ( pos > 10 ) header.classList.add("scrolled");
          else header.classList.remove("scrolled");
          
          c = currentScrollTop;
        }
      }
      
      return false;
    }
    
    nav();
    $(window).scroll(function() { nav(); });
    
    /* For parallax cover style */
    var rellaxInstances = []; // Store Rellax instances for cleanup
    
    function initParallax() {
      // Clean up existing instances
      rellaxInstances.forEach(instance => {
        if (instance && instance.destroy) {
          instance.destroy();
        }
      });
      rellaxInstances = [];
      
      var bgs = document.querySelectorAll('.is-style-parallax .wp-block-cover__image-background');
    
      bgs.forEach((bg) => {
        var bgh = bg.parentElement.offsetHeight;
        var vph = window.innerHeight;
        var off = (vph - bgh)*0.1;
        bg.style.top = '-'+off+'px';
        bg.style.bottom = '-'+off+'px';
        bg.style.height = (bgh + off * 2)+'px';
        var rellax = new Rellax(bg, {
          center:true
        });
        rellaxInstances.push(rellax);
      });
    }
    
    // Initialize parallax on load
    initParallax();
    
    // Reinitialize parallax on window resize
    $(window).resize(function() {
      initParallax();
    });
    
    /* For CountUp Format Items */
    var countUpItems = document.querySelectorAll('.pro-countup');
    countUpItems.forEach( (cuItem, i) => {
      
      cuItem.id = "#pro-countup-"+i;
      
      var cuItemVal = cuItem.innerHTML,
          cuNum = parseFloat(cuItemVal.replace(/,/g, '')),
          duration = 3;
      
      if ( cuItemVal.includes('.') ){
        var parts = cuItemVal.split('.');
        var decimals = parts[1].length;
      } else {
        var decimals = 0;
      }
      
      var countUp = new CountUp(cuItem.id, cuNum, {
        duration: duration,
        decimalPlaces: decimals,
        enableScrollSpy: true
      });
      countUp.start();
    });
    
  })
})(jQuery);
