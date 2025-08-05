class proShotClock {
  constructor( block, data ) {
    this.data   = data;
    this.$block = typeof block == 'string' ? document.querySelector(`[data-block="${block}"]`) : block;
    
    if ( !this.$block ) return;
    
    if ( !this.data.seconds ) this.data.seconds = new Date('1970-01-01T' + this.data.timer + 'Z').getTime() / 1000;
    
    this.$onFinish = document.querySelectorAll(`.${this.data.on_finish}`);
    
    let interval  = null,
        timer     = this.timer(this.$block, this.data.seconds);
        
    interval = setInterval(() => {
      timer = this.timer(this.$block, timer);
      
      if ( timer < 0 ) {
        clearInterval(interval);
        this.$block.classList.add('shotclock--expired');
        
        this.$onFinish.forEach((el) => {
          setTimeout(() => {
            var classNew = `${this.data.on_finish}--show`;
            
            el.classList.add('shotclock--show');
            
            // el.classList.remove('visible');
  //           el.classList.remove('onShotClockFinish');
  //           setTimeout(function() {
  //             el.classList.add('visible');
  //           }, 500)
          }, 500)
        });
      }
    }, 1000);
  }
  timer( $shotclock, timer ) {
    let minutes = parseInt(timer / 60, 10),
        seconds = parseInt(timer % 60, 10);
        
    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;
    
    $shotclock.querySelector('.minutes').innerHTML = minutes;
    $shotclock.querySelector('.seconds').innerHTML = seconds;
    
    //$shotclock.querySelector('.shotclock__progress').style.width = ( timer / shotclock.dataset.seconds * 100 )+"%";
    
    --timer
    
    return timer;
  }
}

var proShotClockFn = function( block, data ) {
  if ( data.field_64dc1d6a83725 ) {
    data = {
      'timer': data.field_64dc1d6a83725,
      'on_finish': data.field_673e43d15d32f
    };
  }
  
  return new proShotClock(block, data);
}

let proShotClockBlocks = {};

document.addEventListener('DOMContentLoaded', function() {
  if ( window.acf ) {
    window.acf.addAction('render_block_preview/type=pluspro/pro-shotclock', (el, fields) => {
			console.log('fired');
      proShotClockFn(el[0], fields.data);
    });
  }
});
