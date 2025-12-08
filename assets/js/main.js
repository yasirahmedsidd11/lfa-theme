(function ($) {
  // Quick View Modal - initialize after DOM ready
  var $quickViewModal;

  $(document).ready(function () {
    $quickViewModal = $('#lfa-quick-view-modal');
  });

  // Open quick view modal
  $(document).on('click', '.lfa-quick-view-btn', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var productId = $(this).data('product-id');
    if (!productId) {
      return;
    }

    // Get modal if not already cached
    if (!$quickViewModal || $quickViewModal.length === 0) {
      $quickViewModal = $('#lfa-quick-view-modal');
    }

    if ($quickViewModal.length === 0) {
      return;
    }

    // Show modal
    $quickViewModal.show();
    $('body').css('overflow', 'hidden');

    // Show loading
    var $inner = $quickViewModal.find('.lfa-quick-view-inner');
    $inner.html('<div class="lfa-quick-view-loading"><span>Loading...</span></div>');

    // Load product data via AJAX
    $.ajax({
      url: LFA.ajaxUrl || '/wp-admin/admin-ajax.php',
      type: 'POST',
      data: {
        action: 'lfa_get_quick_view',
        product_id: productId,
        nonce: LFA.nonce || ''
      },
      success: function (response) {
        if (response.success && response.data) {
          // WordPress wp_send_json_success wraps data, so access response.data.data
          var html = response.data.data || response.data;
          var $inner = $quickViewModal.find('.lfa-quick-view-inner');
          
          // Destroy any existing slider before inserting new content
          var $existingSlider = $inner.find('.lfa-quick-view-slider');
          if ($existingSlider.length && $existingSlider.hasClass('slick-initialized')) {
            $existingSlider.slick('unslick');
          }
          
          $inner.html(html);
          
          // Initialize slider after content is loaded
          setTimeout(function() {
            if (typeof window.initializeQuickViewSlider === 'function') {
              window.initializeQuickViewSlider($inner.find('.lfa-quick-view-wrapper'));
            }
          }, 200);
        } else {
          var $inner = $quickViewModal.find('.lfa-quick-view-inner');
          var errorMsg = (response.data && response.data.message) ? response.data.message : 'Unknown error';
          $inner.html('<div class="lfa-quick-view-loading"><span>Error: ' + errorMsg + '</span></div>');
        }
      },
      error: function (xhr, status, error) {
        var $inner = $quickViewModal.find('.lfa-quick-view-inner');
        $inner.html('<div class="lfa-quick-view-loading"><span>Error loading product details.</span></div>');
      }
    });
  });

  // Close quick view modal
  $(document).on('click', '.lfa-quick-view-close, .lfa-quick-view-overlay', function (e) {
    e.preventDefault();
    if (!$quickViewModal || $quickViewModal.length === 0) {
      $quickViewModal = $('#lfa-quick-view-modal');
    }
    if ($quickViewModal.length > 0) {
      $quickViewModal.hide();
      $('body').css('overflow', '');
    }
  });

  // Close on Escape key
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
      if (!$quickViewModal || $quickViewModal.length === 0) {
        $quickViewModal = $('#lfa-quick-view-modal');
      }
      if ($quickViewModal && $quickViewModal.is(':visible')) {
        $quickViewModal.hide();
        $('body').css('overflow', '');
      }
    }
  });

  // Function to apply swatch selection (updates image and active state)
  function applySwatchSelection($swatch, $product, productId, animate) {
    animate = animate !== false; // Default to true

    var imageUrl = $swatch.data('image-url');
    var imageSrcset = $swatch.data('image-srcset') || '';
    var imageSizes = $swatch.data('image-sizes') || '';
    var imageWidth = $swatch.data('image-width') || '';
    var imageHeight = $swatch.data('image-height') || '';
    var colorSlug = $swatch.data('color-slug');

    if (!imageUrl) {
      return false; // No image to switch to
    }

    // Find the product image
    var $productImage = $product.find('#lfa-product-image-' + productId);
    if ($productImage.length === 0) {
      $productImage = $product.find('.lfa-product-main-image');
    }

    if ($productImage.length === 0) {
      return false; // Image not found
    }

    // Update active swatch
    $product.find('.lfa-color-swatch').removeClass('is-active');
    $swatch.addClass('is-active');

    // Store selected color slug in product data
    if (colorSlug) {
      $product.data('selected-color-slug', colorSlug);
    }

    // Function to update all image attributes
    function updateImageAttributes($img) {
      $img.attr('src', imageUrl);

      // Update srcset if available
      if (imageSrcset) {
        $img.attr('srcset', imageSrcset);
      } else {
        $img.removeAttr('srcset');
      }

      // Update sizes if available
      if (imageSizes) {
        $img.attr('sizes', imageSizes);
      } else {
        $img.removeAttr('sizes');
      }

      // Update width if available
      if (imageWidth) {
        $img.attr('width', imageWidth);
      } else {
        $img.removeAttr('width');
      }

      // Update height if available
      if (imageHeight) {
        $img.attr('height', imageHeight);
      } else {
        $img.removeAttr('height');
      }
    }

    // If it's an img tag, update all attributes
    if ($productImage.is('img')) {
      var $img = $productImage;
      // Store original attributes if not already stored
      if (!$img.data('original-src')) {
        $img.data('original-src', $img.attr('src'));
        $img.data('original-srcset', $img.attr('srcset') || '');
        $img.data('original-sizes', $img.attr('sizes') || '');
        $img.data('original-width', $img.attr('width') || '');
        $img.data('original-height', $img.attr('height') || '');
      }

      if (animate) {
        // Update image with fade effect
        $img.fadeOut(200, function () {
          updateImageAttributes($img);
          $img.fadeIn(200);
        });
      } else {
        // Update immediately without animation
        updateImageAttributes($img);
      }
    } else {
      // If it's a wrapper, find img inside or replace content
      var $img = $productImage.find('img');
      if ($img.length > 0) {
        if (animate) {
          $img.fadeOut(200, function () {
            updateImageAttributes($img);
            $img.fadeIn(200);
          });
        } else {
          updateImageAttributes($img);
        }
      } else {
        // Replace content with new image
        var imgHtml = '<img src="' + imageUrl + '" alt="" class="attachment-woocommerce_single size-woocommerce_single lfa-product-main-image"';
        if (imageSrcset) {
          imgHtml += ' srcset="' + imageSrcset + '"';
        }
        if (imageSizes) {
          imgHtml += ' sizes="' + imageSizes + '"';
        }
        if (imageWidth) {
          imgHtml += ' width="' + imageWidth + '"';
        }
        if (imageHeight) {
          imgHtml += ' height="' + imageHeight + '"';
        }
        imgHtml += '>';

        if (animate) {
          $productImage.fadeOut(200, function () {
            $productImage.html(imgHtml);
            $productImage.fadeIn(200);
          });
        } else {
          $productImage.html(imgHtml);
        }
      }
    }

    return true; // Successfully applied
  }

  // Color swatch image switching
  $(document).on('click', '.lfa-color-swatch', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var $swatch = $(this);
    var $product = $swatch.closest('.lfa-product');
    var productId = $swatch.closest('.lfa-color-swatches').data('product-id');

    if (!productId) {
      return;
    }

    // Apply the swatch selection with animation
    applySwatchSelection($swatch, $product, productId, true);
  });

  // Restore selected swatch on product hover
  $(document).on('mouseenter', '.lfa-product', function () {
    var $product = $(this);
    var productId = $product.find('.lfa-color-swatches').data('product-id');

    if (!productId) {
      return;
    }

    // Check if there's a stored selection
    var selectedColorSlug = $product.data('selected-color-slug');
    if (selectedColorSlug) {
      // Find the swatch with matching color slug
      var $selectedSwatch = $product.find('.lfa-color-swatch[data-color-slug="' + selectedColorSlug + '"]');
      if ($selectedSwatch.length > 0) {
        // Restore the selection without animation (instant)
        applySwatchSelection($selectedSwatch, $product, productId, false);
      }
    }
  });

  // Set first swatch as active on page load if no swatch is active
  $(document).ready(function () {
    $('.lfa-color-swatches').each(function () {
      var $swatches = $(this).find('.lfa-color-swatch');
      if ($swatches.length > 0 && $swatches.filter('.is-active').length === 0) {
        $swatches.first().addClass('is-active');
      }
    });

    // Initialize swatch navigation
    initSwatchNavigation();
  });

  // Initialize swatch navigation arrows
  function initSwatchNavigation() {
    $('.lfa-color-swatches-wrapper').each(function () {
      var $wrapper = $(this);
      var $swatches = $wrapper.find('.lfa-color-swatches');
      var $prevBtn = $wrapper.find('.lfa-color-swatches-prev');
      var $nextBtn = $wrapper.find('.lfa-color-swatches-next');

      function checkOverflow() {
        var hasOverflow = $swatches[0].scrollWidth > $swatches[0].clientWidth;
        if (hasOverflow) {
          $prevBtn.show();
          $nextBtn.show();
          updateArrowStates();
        } else {
          $prevBtn.hide();
          $nextBtn.hide();
        }
      }

      function updateArrowStates() {
        var scrollLeft = $swatches.scrollLeft();
        var maxScroll = $swatches[0].scrollWidth - $swatches[0].clientWidth;

        // Show/hide prev button
        if (scrollLeft <= 0) {
          $prevBtn.css('opacity', '0.5').prop('disabled', true);
        } else {
          $prevBtn.css('opacity', '1').prop('disabled', false);
        }

        // Show/hide next button
        if (scrollLeft >= maxScroll - 1) { // -1 for rounding issues
          $nextBtn.css('opacity', '0.5').prop('disabled', true);
        } else {
          $nextBtn.css('opacity', '1').prop('disabled', false);
        }
      }

      // Check on load and resize
      checkOverflow();
      $(window).on('resize', checkOverflow);

      // Check on hover (when swatches become visible)
      $wrapper.closest('.lfa-product').on('mouseenter', function () {
        setTimeout(checkOverflow, 50); // Small delay to ensure visibility
      });

      // Previous button click
      $prevBtn.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!$prevBtn.prop('disabled')) {
          $swatches.animate({
            scrollLeft: '-=100'
          }, 300, function () {
            updateArrowStates();
          });
        }
      });

      // Next button click
      $nextBtn.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!$nextBtn.prop('disabled')) {
          $swatches.animate({
            scrollLeft: '+=100'
          }, 300, function () {
            updateArrowStates();
          });
        }
      });

      // Update arrow states on scroll
      $swatches.on('scroll', updateArrowStates);
    });
  }
})(jQuery);

// Mobile nav toggle
(function () {
  var nav = document.querySelector('.primary-nav');
  var btn = document.querySelector('.lfa-burger');
  if (!nav || !btn) return;
  btn.addEventListener('click', function () {
    nav.classList.toggle('open');
  });
})();

(function () {
  var trigger = document.querySelector('[data-mega="shop"]');
  var panel = document.querySelector('[data-mega-panel="shop"]');
  if (!trigger || !panel) return;
  var hideT;
  function open() { panel.hidden = false; panel.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
  function close() { panel.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); hideT = setTimeout(function () { panel.hidden = true; }, 120); }
  function cancel() { if (hideT) { clearTimeout(hideT); hideT = undefined; } }
  trigger.addEventListener('mouseenter', open);
  trigger.addEventListener('focus', open);
  trigger.addEventListener('mouseleave', function () { hideT = setTimeout(close, 150); });
  panel.addEventListener('mouseenter', cancel);
  panel.addEventListener('mouseleave', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();

(function () {
  var openBtn = document.querySelector('.js-open-search');
  var drawer = document.querySelector('[data-search-drawer]');
  var dim = document.querySelector('[data-search-dim]');
  var input = document.querySelector('[data-search-input]');
  var results = document.querySelector('[data-search-results]');
  var titleEl = document.querySelector('[data-search-title]');
  var moreBtn = document.querySelector('[data-search-more]');
  var closeBtn = document.querySelector('[data-search-close]');
  if (!drawer || !dim || !openBtn || !input || !results) return;

  var state = { q: '', page: 1, next: false, busy: false };

  function openDrawer() {
    drawer.hidden = false; dim.hidden = false;
    requestAnimationFrame(function () {
      drawer.classList.add('is-open');
      dim.classList.add('is-on');
      setTimeout(function () { input.focus(); input.select(); }, 120);
    });
    // Load trending on first open
    if (results.childElementCount === 0) fetchResults('');
  }
  function closeDrawer() {
    drawer.classList.remove('is-open'); dim.classList.remove('is-on');
    setTimeout(function () { drawer.hidden = true; dim.hidden = true; }, 180);
  }

  openBtn.addEventListener('click', function (e) { e.preventDefault(); openDrawer(); });
  dim.addEventListener('click', closeDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !drawer.hidden) closeDrawer(); });

  function setTitle(q) {
    titleEl.textContent = (q && q.trim().length) ? LFA.strSearchingFor ? LFA.strSearchingFor.replace('%s', q) : 'Results for “' + q + '”' : (LFA.strTrending || 'TRENDING PRODUCTS');
  }

  function render(html, append) {
    if (!append) results.innerHTML = '';
    var frag = document.createElement('div');
    frag.innerHTML = html;
    while (frag.firstChild) results.appendChild(frag.firstChild);
    moreBtn.hidden = !state.next;
  }

  function fetchResults(q, append) {
    if (state.busy) return;
    state.busy = true;
    if (!append) { state.page = 1; }
    var params = new URLSearchParams({ action: 'lfa_search', q: q, page: state.page, nonce: (LFA && LFA.nonce) ? LFA.nonce : '' });
    fetch(LFA.ajaxUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: params.toString() })
      .then(r => r.json())
      .then(function (data) {
        if (!data || !data.success) throw new Error('Search failed');
        state.next = !!data.data.next;
        render(data.data.html, append);
      })
      .catch(function () { render('<div class="lfa-sr-empty">Error loading results</div>'); })
      .finally(function () { state.busy = false; });
  }

  // Debounced input
  var t;
  input.addEventListener('input', function () {
    var q = input.value.trim();
    setTitle(q);
    state.q = q;
    state.page = 1;
    clearTimeout(t);
    t = setTimeout(function () { fetchResults(q, false); }, 300);
  });

  // Load more
  moreBtn.addEventListener('click', function () {
    if (!state.next) return;
    state.page += 1;
    fetchResults(state.q, true);
  });

  // Optional: infinite scroll inside drawer
  var body = document.querySelector('[data-search-body]');
  if (body) {
    body.addEventListener('scroll', function () {
      if (state.busy || !state.next) return;
      var nearBottom = body.scrollTop + body.clientHeight >= body.scrollHeight - 200;
      if (nearBottom) { state.page += 1; fetchResults(state.q, true); }
    });
  }

  // Localized strings fallback
  if (!window.LFA) { window.LFA = {}; }
  if (!LFA.strTrending) LFA.strTrending = 'TRENDING PRODUCTS';
  if (!LFA.strSearchingFor) LFA.strSearchingFor = 'Results for “%s”';
})();

