/* globals List */
/* exported proAccordion */

class proAccordion {
  constructor( block, data ) {
    this.data       = data;
    this.$block     = typeof block == 'string' ? document.querySelector(`[data-block="${block}"]`) : block;
    this.$accordion = this.$block.querySelector('.pro-accordion');
    this.$items     = this.$block.querySelectorAll('.pro-accordion-item');
    
    this.$block.id = this.$block.dataset.block;
    
    this.build();
    this.list();
    this.navbar();
    this.search();
    
    this.listeners();
  }
  build() {
    // this.$items.forEach(function(item, i){
    //   console.log(item.querySelector('.pro-accordion-item__heading'));
    // });
  }
  list() {
    this.options = {
      searchClass: 'accordion-search__input',
      listClass: 'pro-accordion',
      valueNames: [
        'pro-accordion-item__heading',
        'wp-block-group',
        { data: ['types'] }
      ]
    };
    
    this.list = new List(this.$block.id, this.options);
  }
  listeners() {
    this.link = window.location.href.split('#');
    
    if ( this.link[1] ) {
      this.$item = document.getElementById(link[1]);
      this.toggle(this.$item, false);
    }
    
    this.$accordion.addEventListener('click', (e) => {
      let target = e.target.classList.contains('pro-accordion-item__heading') ? e.target : e.target.closest('.pro-accordion-item__heading');
      
      if ( target ) this.toggle(target);
    }, true);
  }
  navbar() {
    this.$navbar = document.querySelector('.navbar');
    
    if ( this.$navbar ) this.$.style.setProperty('--offset', this.$navbar.offsetHeight + 18 + 'px');
  }
  search() {
    this.$search = document.querySelector(`.${this.options.searchClass}`);
    
    if ( this.$search && this.$search.value ) {
      this.list.on('searchComplete', () => {
        this.list.visibleItems[0].elm.classList.add('open');
      });
      
      this.list.search(this.$search.value);
    }
  }
  toggle( target ) {
    let item = target.parentElement;
    
    if ( item.classList.contains('pro-accordion-item--open') ) item.classList.remove('pro-accordion-item--open');
    else {
      if ( this.$accordion.classList.contains('pro-accordion--automatic') ) {
        this.$accordion.querySelectorAll('.pro-accordion-item--open').forEach(function(el) {
          el.classList.remove('pro-accordion-item--open');
        });
      }
      
      item.classList.add('pro-accordion-item--open');
    }
  }
}
