(function($){
  // Quick View stub
  $(document).on('click', '.lfa-quick-view', function(e){
    e.preventDefault();
    alert('Quick View modal goes here.');
  });
})(jQuery);

// Mobile nav toggle
(function(){
  var nav = document.querySelector('.primary-nav');
  var btn = document.querySelector('.lfa-burger');
  if(!nav || !btn) return;
  btn.addEventListener('click', function(){
    nav.classList.toggle('open');
  });
})();

(function(){
  var trigger = document.querySelector('[data-mega="shop"]');
  var panel   = document.querySelector('[data-mega-panel="shop"]');
  if(!trigger || !panel) return;
  var hideT;
  function open(){ panel.hidden = false; panel.classList.add('is-open'); trigger.setAttribute('aria-expanded','true'); }
  function close(){ panel.classList.remove('is-open'); trigger.setAttribute('aria-expanded','false'); hideT = setTimeout(function(){ panel.hidden = true; }, 120); }
  function cancel(){ if(hideT){ clearTimeout(hideT); hideT=undefined; } }
  trigger.addEventListener('mouseenter', open);
  trigger.addEventListener('focus', open);
  trigger.addEventListener('mouseleave', function(){ hideT = setTimeout(close, 150); });
  panel.addEventListener('mouseenter', cancel);
  panel.addEventListener('mouseleave', close);
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });
})();

(function(){
  var openBtn = document.querySelector('.js-open-search');
  var drawer  = document.querySelector('[data-search-drawer]');
  var dim     = document.querySelector('[data-search-dim]');
  var input   = document.querySelector('[data-search-input]');
  var results = document.querySelector('[data-search-results]');
  var titleEl = document.querySelector('[data-search-title]');
  var moreBtn = document.querySelector('[data-search-more]');
  var closeBtn= document.querySelector('[data-search-close]');
  if(!drawer || !dim || !openBtn || !input || !results) return;

  var state = { q: '', page: 1, next: false, busy: false };

  function openDrawer(){
    drawer.hidden = false; dim.hidden = false;
    requestAnimationFrame(function(){
      drawer.classList.add('is-open');
      dim.classList.add('is-on');
      setTimeout(function(){ input.focus(); input.select(); }, 120);
    });
    // Load trending on first open
    if(results.childElementCount === 0) fetchResults('');
  }
  function closeDrawer(){
    drawer.classList.remove('is-open'); dim.classList.remove('is-on');
    setTimeout(function(){ drawer.hidden = true; dim.hidden = true; }, 180);
  }

  openBtn.addEventListener('click', function(e){ e.preventDefault(); openDrawer(); });
  dim.addEventListener('click', closeDrawer);
  if(closeBtn) closeBtn.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && !drawer.hidden) closeDrawer(); });

  function setTitle(q){
    titleEl.textContent = (q && q.trim().length) ? LFA.strSearchingFor ? LFA.strSearchingFor.replace('%s', q) : 'Results for “'+q+'”' : (LFA.strTrending || 'TRENDING PRODUCTS');
  }

  function render(html, append){
    if(!append) results.innerHTML = '';
    var frag = document.createElement('div');
    frag.innerHTML = html;
    while(frag.firstChild) results.appendChild(frag.firstChild);
    moreBtn.hidden = !state.next;
  }

  function fetchResults(q, append){
    if(state.busy) return;
    state.busy = true;
    if(!append){ state.page = 1; }
    var params = new URLSearchParams({ action:'lfa_search', q:q, page: state.page, nonce: (LFA && LFA.nonce) ? LFA.nonce : '' });
    fetch(LFA.ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, body: params.toString() })
      .then(r=>r.json())
      .then(function(data){
        if(!data || !data.success) throw new Error('Search failed');
        state.next = !!data.data.next;
        render(data.data.html, append);
      })
      .catch(function(){ render('<div class="lfa-sr-empty">Error loading results</div>'); })
      .finally(function(){ state.busy = false; });
  }

  // Debounced input
  var t;
  input.addEventListener('input', function(){
    var q = input.value.trim();
    setTitle(q);
    state.q = q;
    state.page = 1;
    clearTimeout(t);
    t = setTimeout(function(){ fetchResults(q, false); }, 300);
  });

  // Load more
  moreBtn.addEventListener('click', function(){
    if(!state.next) return;
    state.page += 1;
    fetchResults(state.q, true);
  });

  // Optional: infinite scroll inside drawer
  var body = document.querySelector('[data-search-body]');
  if(body){
    body.addEventListener('scroll', function(){
      if(state.busy || !state.next) return;
      var nearBottom = body.scrollTop + body.clientHeight >= body.scrollHeight - 200;
      if(nearBottom){ state.page += 1; fetchResults(state.q, true); }
    });
  }

  // Localized strings fallback
  if(!window.LFA){ window.LFA = {}; }
  if(!LFA.strTrending) LFA.strTrending = 'TRENDING PRODUCTS';
  if(!LFA.strSearchingFor) LFA.strSearchingFor = 'Results for “%s”';
})();

