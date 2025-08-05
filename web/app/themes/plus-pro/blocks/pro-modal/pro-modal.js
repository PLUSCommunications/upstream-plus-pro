/* exported proModal */

const proCookie = {
  create(name, value, days = false) {
    let date = new Date(), expires = '';
    
    if ( days ) {
      date.setTime(date.getTime()+(days*24*60*60*1000));
      expires = "; expires="+date.toGMTString();
    }
    else expires = "; expires=Tue, 19 Jan 2088 03:14:07 UTC";
    
    document.cookie = name+"="+value+expires+"; path=/";
    
    return true;
  },
  erase(name) {
    this.create(name,"",-1);
  },
  read(name) {
    let nameEQ = name + "=", ca = document.cookie.split(';');
    
    for(let i=0;i < ca.length;i++) {
      let c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    
    return false;
  }
};

class proModal {
  constructor( block, data ) {
    this.data     = data;
    this.hash     = false;
    this.modal    = typeof block == 'string' ? document.querySelector(`[data-block="${block}"]`) : block;
    this.closeBtn = this.modal.querySelector(".pro_modal__close");
    
    this.hashCheck();
    this.listeners();
    this.trigger();
  }
  hashCheck() {
    this.hash = window.location.hash.replace('#', '');
    
    if ( this.hash == this.data.id ) this.open();
    else if ( this.modal.open ) this.modal.close();
  }
  listeners() {
    jQuery(document).bind('gform_confirmation_loaded', () => {
      let id = `modal-${this.data.id}`;
      
      this.modal.close('submitted');
      
      proCookie.create(id, 'completed');
    });
    
    this.closeBtn.addEventListener('click', (e) => {
      if ( e.target.tagName == 'A' ) e.preventDefault();
      
      this.modal.close('closed');
      
      if ( e.target.tagName == 'A' ) return false;
    });
    
    this.modal.addEventListener('cancel', (e) => {
      this.modal.returnValue = 'cancelled';
    });
    
    this.modal.addEventListener('close', (e) => {
      if ( this.mobileAgent() ) window.scrollTo(0, this.startWindowScroll);
      
      if ( this.hash ) {
        this.hash = false;
        window.history.pushState("", document.title, window.location.pathname + window.location.search);
      }
      else if ( this.modal.returnValue !== 'submitted' ) this.snooze();
    });
    
    window.addEventListener('popstate', (e) => {
      this.hashCheck();
    });
  }
  mobileAgent() {
    return ( navigator.userAgent.match(/Android/i)
     || navigator.userAgent.match(/webOS/i)
     || navigator.userAgent.match(/iPhone/i)
     || navigator.userAgent.match(/iPad/i)
     || navigator.userAgent.match(/iPod/i)
     || navigator.userAgent.match(/BlackBerry/i)
     || navigator.userAgent.match(/Windows Phone/i)
     ? true : false
    )
  }
  open() {
    if ( this.mobileAgent() ) this.startWindowScroll = window.scrollY;
   
    if ( this.data.type == 'modal' ) this.modal.showModal();
    else this.modal.show();
  }
  trigger() {
    let _this     = this,
        completed = proCookie.read(`modal-${this.data.id}`),
        snooze    = this.data.snooze ? parseInt(this.data.snooze) : 0,
        snoozing  = proCookie.read(`modal-${this.data.id}-snooze`);
        
    if ( completed != 'completed' && ( snooze === 0 || snoozing === false || snoozing >= snooze ) ) {
      switch (this.data.trigger) {
        case 'leave':
          let leave = true;
          
          if ( !this.mobileAgent() ) {
            document.addEventListener('mouseleave', (e) => {
              if ( (e.toElement === null && e.relatedTarget === null) && leave && ! jQuery('.mfp-bg').length ) {
                leave = false;
                this.open();
              }
            }, false);
          }
        break;
        case 'scroll':
          if ( this.hash ) break;
          
          let pop            = true,
              distance       = this.mobileAgent() ? this.data.distance.mobile_distance : this.data.distance.desktop_distance,
              value          = distance.match(/([0-9]+)/g)[0],
              measurement    = distance.match(/([^0-9]+)/g)[0],
              viewportHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0),
              pageHeight     = document.documentElement.scrollHeight - viewportHeight;
              
          window.addEventListener('scroll', () => {
            if ( pop ) {
              let pos = (document.documentElement.scrollTop||document.body.scrollTop);
              
              if ( pos > 0 ) {
                
                switch (measurement) {
                  case '%':
                    let percentage = ( pos / pageHeight) * 100;
                    
                    if ( percentage >= value ) { this.open(); pop = false; }
                  break;
                  case 'px':
                    if ( pos >= value ) { this.open(); pop = false; }
                  break;
                  case 'vh':
                    let d = value/100, p = pos / viewportHeight;
                    
                    if ( p >= d ) { this.open(); pop = false; }
                  break;
                  default:
                }
              }
            }
          });
        break;
        case 'timer':
          if ( this.hash ) break;
          
          let timeout = this.mobileAgent() ? this.data.timer.mobile_delay : this.data.timer.desktop_delay;
          
          setTimeout(() => { this.open(); }, timeout * 1000);
        break;
        default:
      }
    }
    else if ( snoozing <= snooze ) this.snooze();
  }
  snooze() {
    if ( this.data.snooze !== '0' ) {
      let id   = 'modal-'+this.data.id+'-snooze',
          wait = proCookie.read(id);
          
      if ( wait ) {
        wait = parseInt(wait) + 1;
        if ( wait > this.data.snooze ) wait = 1;
      }
      else wait = 1
      
      proCookie.create(id, wait, 7);
    }
  }
}

let proModalFn = function( block, data ) {
  return new proModal(block, data);
}
