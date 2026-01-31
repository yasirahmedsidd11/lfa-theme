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

    // Get selected color from product card if available
    var $product = $(this).closest('.lfa-product');
    var selectedColorSlug = '';
    if ($product.length) {
      var $activeSwatch = $product.find('.lfa-color-swatch.is-active');
      if ($activeSwatch.length) {
        selectedColorSlug = $activeSwatch.data('color-slug') || '';
      } else {
        // Check if stored in product data
        selectedColorSlug = $product.data('selected-color-slug') || '';
      }
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
    var ajaxData = {
      action: 'lfa_get_quick_view',
      product_id: productId,
      nonce: LFA.nonce || ''
    };

    // Add selected color if available
    if (selectedColorSlug) {
      ajaxData.selected_color = selectedColorSlug;
    }

    $.ajax({
      url: LFA.ajaxUrl || '/wp-admin/admin-ajax.php',
      type: 'POST',
      data: ajaxData,
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
          setTimeout(function () {
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

// Mobile mega menu toggle (icon click toggles dropdown)
(function () {
  function initMobileMegaMenu() {
    // Only on mobile
    if (window.innerWidth > 980) return;

    var toggleIcons = document.querySelectorAll('.primary-nav li.mega-menu .lfa-mega-toggle');
    if (!toggleIcons.length) return;

    toggleIcons.forEach(function (icon) {
      // Check if already has listener
      if (icon.hasAttribute('data-mega-mobile-bound')) return;
      icon.setAttribute('data-mega-mobile-bound', 'true');

      icon.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var parentLi = icon.closest('li.mega-menu');
        if (parentLi) {
          parentLi.classList.toggle('open');
        }
      });
    });
  }

  // Initialize on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileMegaMenu);
  } else {
    initMobileMegaMenu();
  }

  // Re-initialize on resize and after menu toggle
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth <= 980) {
        initMobileMegaMenu();
      }
    }, 250);
  });

  // Also re-initialize when mobile menu opens
  var nav = document.querySelector('.primary-nav');
  if (nav) {
    var observer = new MutationObserver(function (mutations) {
      if (nav.classList.contains('open')) {
        setTimeout(initMobileMegaMenu, 100);
      }
    });
    observer.observe(nav, { attributes: true, attributeFilter: ['class'] });
  }
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

// Mega menu for general menu items with children
(function () {
  var trigger = document.querySelector('[data-mega="menu"]');
  var panel = document.querySelector('[data-mega-panel="menu"]');
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

  var state = { q: '', page: 1, next: false, busy: false, totalProducts: 0, displayedProducts: 0 };

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

  function showLoading() {
    results.innerHTML = '<div class="lfa-sr-loading" style="text-align: center; padding: 40px; color: #666;">Loading...</div>';
    // Hide load more button during loading
    moreBtn.hidden = true;
    moreBtn.style.display = 'none';
    moreBtn.classList.add('hidden');
  }

  function render(html, append) {
    if (!append) {
      results.innerHTML = '';
      state.displayedProducts = 0;
    }
    
    var frag = document.createElement('div');
    frag.innerHTML = html;
    while (frag.firstChild) results.appendChild(frag.firstChild);
    
    // Count total products displayed in results container
    state.displayedProducts = results.querySelectorAll('.lfa-sr-item').length;
    
    // Determine if load more button should be shown
    var showButton = false;
    
    // Show button only if:
    // 1. We know the total count AND there are more products to show
    // 2. OR if we don't know total yet, use the next flag
    if (state.totalProducts > 0) {
      // We have total count - show button only if displayed < total
      if (state.displayedProducts < state.totalProducts) {
        showButton = true;
      } else {
        // All products are displayed
        showButton = false;
      }
      
      // If total is less than 9, never show button
      if (state.totalProducts < 9) {
        showButton = false;
      }
    } else {
      // Fallback: use next flag
      // Only show if there are more pages AND we have at least 9 products displayed
      if (state.next && state.displayedProducts >= 9) {
        showButton = true;
      } else {
        showButton = false;
      }
    }
    
    // Double-check: if displayed equals or exceeds total, hide button
    if (state.totalProducts > 0 && state.displayedProducts >= state.totalProducts) {
      showButton = false;
    }
    
    // Final check: if we have total and displayed >= total, definitely hide
    if (state.totalProducts > 0) {
      if (state.displayedProducts >= state.totalProducts) {
        showButton = false;
      }
      if (state.totalProducts < 9) {
        showButton = false;
      }
    }
    
    // Don't update button visibility if we're loading more (let fetchResults handle it)
    if (!state.busy || !append) {
      // Use both hidden attribute and inline style to ensure button is hidden
    if (!showButton) {
      moreBtn.hidden = true;
      moreBtn.style.display = 'none';
      moreBtn.classList.add('hidden');
    } else {
      moreBtn.hidden = false;
      moreBtn.style.display = '';
      moreBtn.classList.remove('hidden');
    }
    }
  }

  function fetchResults(q, append) {
    if (state.busy) return;
    state.busy = true;
    
    // Update button text to "Loading..." and hide it during initial load
    if (!append) {
      moreBtn.hidden = true;
      moreBtn.style.display = 'none';
      moreBtn.classList.add('hidden');
      state.page = 1; 
      state.next = false;
      state.totalProducts = 0;
      state.displayedProducts = 0;
      showLoading(); // Show loading state
    } else {
      // When loading more, show button but change text to "Loading..."
      moreBtn.textContent = 'Loading...';
      moreBtn.disabled = true;
    }
    
    var params = new URLSearchParams({ action: 'lfa_search', q: q, page: state.page, nonce: (LFA && LFA.nonce) ? LFA.nonce : '' });
    fetch(LFA.ajaxUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: params.toString() })
      .then(r => r.json())
      .then(function (data) {
        if (!data || !data.success) throw new Error('Search failed');
        
        // Update state from server response
        state.next = !!(data.data && data.data.next);
        
        // Get total products count (only on first page)
        if (data.data && data.data.total && state.page === 1) {
          state.totalProducts = parseInt(data.data.total) || 0;
        }
        
        render(data.data.html, append);
      })
      .catch(function () { 
        results.innerHTML = '<div class="lfa-sr-empty">Error loading results</div>';
        moreBtn.hidden = true;
        moreBtn.style.display = 'none';
        moreBtn.classList.add('hidden');
        moreBtn.textContent = 'Load more';
        moreBtn.disabled = false;
        state.totalProducts = 0;
        state.displayedProducts = 0;
      })
      .finally(function () { 
        state.busy = false;
        // Reset button text
        moreBtn.textContent = 'Load more';
        moreBtn.disabled = false;
        
        // Re-check button visibility after loading completes
        var displayed = results.querySelectorAll('.lfa-sr-item').length;
        state.displayedProducts = displayed;
        
        // Update button visibility based on current state
        var showButton = false;
        if (state.totalProducts > 0) {
          // We have total count - show only if displayed < total AND total >= 9
          if (state.displayedProducts < state.totalProducts && state.totalProducts >= 9) {
            showButton = true;
          } else {
            // All products shown or total < 9 - hide button
            showButton = false;
          }
        } else if (state.next && state.displayedProducts >= 9) {
          // Fallback: use next flag
          showButton = true;
        } else {
          // No more products or less than 9 - hide button
          showButton = false;
        }
        // Use both hidden attribute and inline style to ensure button is hidden
    if (!showButton) {
      moreBtn.hidden = true;
      moreBtn.style.display = 'none';
      moreBtn.classList.add('hidden');
    } else {
      moreBtn.hidden = false;
      moreBtn.style.display = '';
      moreBtn.classList.remove('hidden');
    }
      });
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
    if (!state.next || state.busy) return;
    state.page += 1;
    fetchResults(state.q, true);
  });

  // Disable infinite scroll - we only want load more button
  // var body = document.querySelector('[data-search-body]');
  // if (body) {
  //   body.addEventListener('scroll', function () {
  //     if (state.busy || !state.next) return;
  //     var nearBottom = body.scrollTop + body.clientHeight >= body.scrollHeight - 200;
  //     if (nearBottom) { state.page += 1; fetchResults(state.q, true); }
  //   });
  // }

  // Localized strings fallback
  if (!window.LFA) { window.LFA = {}; }
  if (!LFA.strTrending) LFA.strTrending = 'TRENDING PRODUCTS';
  if (!LFA.strSearchingFor) LFA.strSearchingFor = 'Results for “%s”';
})();

// Cart Drawer
(function () {
  var openBtn = document.querySelector('.js-open-cart-drawer');
  var drawer = document.querySelector('[data-cart-drawer]');
  var dim = document.querySelector('[data-cart-dim]');
  var content = document.querySelector('[data-cart-drawer-content]');
  var closeBtn = document.querySelector('[data-cart-drawer-close]');

  if (!drawer || !dim || !openBtn || !content) return;

  var isOpen = false;
  var isLoading = false;

  function openDrawer() {
    if (isOpen) {
      return;
    }

    isOpen = true;
    drawer.hidden = false;
    dim.hidden = false;
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(function () {
      drawer.classList.add('is-open');
      dim.classList.add('is-on');

      // Load cart content after drawer is visually open
      setTimeout(function () {
        loadCartContent();
      }, 100);
    });
  }

  function closeDrawer() {
    if (!isOpen) return;
    isOpen = false;
    drawer.classList.remove('is-open');
    dim.classList.remove('is-on');
    document.body.style.overflow = '';

    setTimeout(function () {
      drawer.hidden = true;
      dim.hidden = true;
    }, 180);
  }

  // Make loadCartContent globally accessible
  window.loadCartContent = function () {
    // Check if drawer exists and is open
    var drawerEl = document.querySelector('[data-cart-drawer]');
    if (!drawerEl) {
      return;
    }
    if (!drawerEl.classList.contains('is-open')) {
      return; // Don't load if drawer isn't open
    }

    var contentEl = document.querySelector('[data-cart-drawer-content]');
    if (!contentEl) {
      return;
    }

    if (isLoading) {
      return; // Don't retry - prevents infinite loop
    }

    // Check if LFA object exists
    if (typeof LFA === 'undefined') {
      contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Error: LFA not defined</span></div>';
      return;
    }
    if (!LFA.ajaxUrl) {
      contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Error: ajaxUrl missing</span></div>';
      return;
    }

    isLoading = true;

    // Show loading
    contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Loading cart...</span></div>';

    // Load cart via AJAX
    var params = new URLSearchParams({
      action: 'lfa_get_cart_drawer',
      nonce: (LFA && LFA.nonce) ? LFA.nonce : '',
      _ajax_nonce: (LFA && LFA.nonce) ? LFA.nonce : ''
    });

    // Add timeout to prevent hanging
    var timeoutId = setTimeout(function () {
      isLoading = false;
      if (contentEl) {
        contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Request timeout. Please try again.</span></div>';
      }
    }, 10000); // 10 second timeout

    // Use jQuery AJAX as it's more reliable with WordPress
    if (typeof jQuery !== 'undefined' && jQuery.ajax) {
      var ajaxData = {
        action: 'lfa_get_cart_drawer',
        nonce: (LFA && LFA.nonce) ? LFA.nonce : '',
        _ajax_nonce: (LFA && LFA.nonce) ? LFA.nonce : ''
      };

      var ajaxStartTime = Date.now();

      jQuery.ajax({
        url: LFA.ajaxUrl,
        type: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 10000,
        success: function (data) {
          clearTimeout(timeoutId);
          isLoading = false;

          if (data && data.success && data.data && data.data.html) {
            if (contentEl) {
              try {
                contentEl.innerHTML = data.data.html;
              } catch (e) {
                // Error setting innerHTML
              }
            }

            // Update header badge after drawer content loads
            // This ensures the badge count matches the drawer content
            if (typeof window.updateCartBadge === 'function') {
              setTimeout(function () {
                window.updateCartBadge();
              }, 100);
            }

            // Initialize shipping accordion (closed by default in drawer)
            setTimeout(function () {
              var $accordionToggles = jQuery('.lfa-cart-drawer .lfa-shipping-accordion-toggle');
              $accordionToggles.attr('aria-expanded', 'false');
              var $accordionContent = jQuery('.lfa-cart-drawer .lfa-shipping-accordion-content');
              $accordionContent.css({
                'max-height': '0',
                'padding-top': '0',
                'padding-bottom': '0',
                'overflow': 'hidden'
              });

              // Attach click handlers to accordion toggles
              $accordionToggles.off('click.lfa-accordion').on('click.lfa-accordion', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $toggle = jQuery(this);
                var $content = $toggle.next('.lfa-shipping-accordion-content');
                var isExpanded = $toggle.attr('aria-expanded') === 'true';

                // Toggle aria-expanded
                var newState = !isExpanded;
                $toggle.attr('aria-expanded', newState);

                // Force a reflow to ensure CSS transition works
                if (newState) {
                  $content.css('display', 'block');
                  // Trigger reflow
                  $content[0].offsetHeight;
                }
              });
            }, 100);

            // Initialize cart drawer featured products slider
            if (typeof window.initializeCartDrawerSlider === 'function') {
              window.initializeCartDrawerSlider();
            }
            
            // Store reference to loadCartDrawer for use in slider
            if (typeof loadCartDrawer === 'function') {
              window.loadCartDrawer = loadCartDrawer;
            }
          } else {
            var errorMsg = (data && data.data && data.data.message) ? data.data.message : 'Error loading cart';
            if (contentEl) {
              contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>' + errorMsg + '</span></div>';
            }
          }
        },
        error: function (xhr, status, error) {
          clearTimeout(timeoutId);
          isLoading = false;
          if (contentEl) {
            var errorMsg = error || status || 'Network error';
            contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Error: ' + errorMsg + '</span></div>';
          }
        },
        complete: function () {
          clearTimeout(timeoutId);
          isLoading = false;
        }
      })
        .fail(function (xhr, status, error) {
          if (status === 'timeout') {
            clearTimeout(timeoutId);
            isLoading = false;
            if (contentEl) {
              contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Request timed out. Please check your connection.</span></div>';
            }
          }
        })
        .always(function () {
          clearTimeout(timeoutId);
          isLoading = false;
        });

      return; // Exit early, jQuery handles it
    } else {
      isLoading = false;
      if (contentEl) {
        contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Error: jQuery not available</span></div>';
      }
    }

    // Fallback to fetch if jQuery not available
    fetch(LFA.ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: params.toString()
    })
      .then(function (r) {
        if (!r.ok) {
          throw new Error('HTTP error! status: ' + r.status);
        }
        return r.text().then(function (text) {
          try {
            return JSON.parse(text);
          } catch (e) {
            throw new Error('Invalid JSON response');
          }
        });
      })
      .then(function (data) {
        clearTimeout(timeoutId);
        if (data && data.success && data.data && data.data.html) {
          contentEl.innerHTML = data.data.html;
          // Re-initialize cart JS if needed
          if (typeof jQuery !== 'undefined' && jQuery.fn.ready) {
            jQuery(document.body).trigger('wc_fragment_refresh');
            // Initialize shipping accordion (closed by default in drawer)
            var $accordionToggles = jQuery('.lfa-cart-drawer .lfa-shipping-accordion-toggle');
            $accordionToggles.attr('aria-expanded', 'false');
            var $accordionContent = jQuery('.lfa-cart-drawer .lfa-shipping-accordion-content');
            $accordionContent.css({
              'max-height': '0',
              'padding-top': '0',
              'padding-bottom': '0',
              'overflow': 'hidden'
            });
          }
        } else {
          var errorMsg = (data && data.data && data.data.message) ? data.data.message : 'Error loading cart';
          contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>' + errorMsg + '</span></div>';
        }
      })
      .catch(function (error) {
        clearTimeout(timeoutId);
        if (contentEl) {
          var errorMsg = error && error.message ? error.message : 'Network error';
          contentEl.innerHTML = '<div class="lfa-cart-drawer-loading"><span>Error: ' + errorMsg + '</span></div>';
        }
      })
      .finally(function () {
        clearTimeout(timeoutId);
        isLoading = false;
      });
  }

  // Event listeners
  if (openBtn) {
    openBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      openDrawer();
    });
  }

  if (dim) {
    dim.addEventListener('click', closeDrawer);
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeDrawer);
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && isOpen) {
      closeDrawer();
    }
  });

  // Refresh cart when updated (WooCommerce fragments)
  if (typeof jQuery !== 'undefined') {
    jQuery(document.body).on('wc_fragment_refresh updated_wc_div', function (event, fragments) {
      // Check if drawer is actually open
      var drawerEl = document.querySelector('[data-cart-drawer]');
      var drawerIsOpen = drawerEl && drawerEl.classList.contains('is-open');

      // DON'T reload cart content on fragment refresh - it causes infinite loop
      // The cart content is already loaded when drawer opens
      // Only update the badge, not the entire drawer content
      if (drawerIsOpen && typeof window.loadCartContent === 'function' && !isLoading) {
        // window.loadCartContent(); // Commented out to prevent loop
      }

      // Always update badge on fragment refresh
      if (typeof window.updateCartBadge === 'function') {
        // Pass fragments if available, otherwise fetch fresh
        if (fragments && typeof fragments === 'object') {
          window.updateCartBadge(fragments);
        } else {
          window.updateCartBadge();
        }
      }
    });
  }

  // Make updateCartBadge globally accessible
  window.updateCartBadge = function (fragments) {
    if (typeof jQuery === 'undefined') {
      return;
    }

    var $badge = jQuery('.hdr-cart-badge');
    if (!$badge.length) {
      return;
    }

    // First, try to use provided fragments (from added_to_cart event)
    if (fragments && typeof fragments === 'object') {
      // Check if WooCommerce provided the badge fragment directly (key: '.hdr-cart-badge')
      if (fragments['.hdr-cart-badge']) {
        var $newBadge = jQuery(fragments['.hdr-cart-badge']);
        var newCount = $newBadge.text().trim();
        $badge.text(newCount);
        return; // Success, exit early
      }

      var foundCount = false;
      jQuery.each(fragments, function (key, value) {
        // Check if this fragment contains the badge
        var $fragment = jQuery(value);
        var $badgeInFragment = $fragment.find('.hdr-cart-badge');
        if ($badgeInFragment.length) {
          var newCount = $badgeInFragment.text().trim();
          $badge.text(newCount);
          foundCount = true;
          return false; // Break loop
        }

        // Also check if the fragment itself is the badge element
        if ($fragment.hasClass('hdr-cart-badge')) {
          var newCount = $fragment.text().trim();
          $badge.text(newCount);
          foundCount = true;
          return false; // Break loop
        }
      });

      if (foundCount) {
        return; // Success, exit early
      }
    }

    // Fallback: Fetch fragments via AJAX
    if (typeof wc_add_to_cart_params !== 'undefined') {
      jQuery.get(wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'), function (response) {
        if (response && response.fragments) {
          // Check if WooCommerce provided the badge fragment directly (key: '.hdr-cart-badge')
          if (response.fragments['.hdr-cart-badge']) {
            var $newBadge = jQuery(response.fragments['.hdr-cart-badge']);
            var newCount = $newBadge.text().trim();
            $badge.text(newCount);
            return; // Success
          }

          var foundInResponse = false;
          jQuery.each(response.fragments, function (key, value) {
            var $fragment = jQuery(value);
            var $badgeInFragment = $fragment.find('.hdr-cart-badge');
            if ($badgeInFragment.length) {
              var newCount = $badgeInFragment.text().trim();
              $badge.text(newCount);
              foundInResponse = true;
              return false; // Break loop
            }

            // Also check if the fragment itself is the badge
            if ($fragment.hasClass('hdr-cart-badge')) {
              var newCount = $fragment.text().trim();
              $badge.text(newCount);
              foundInResponse = true;
              return false; // Break loop
            }
          });

          if (foundInResponse) {
            return; // Success
          }
        }

        // Last resort: Try to calculate from cart object or increment current count
        var currentCount = parseInt($badge.text()) || 0;
        var newCount = currentCount + 1; // Increment by 1 if we can't find the actual count
        $badge.text(newCount);
      }).fail(function (xhr, status, error) {
        // Last resort: increment current count
        var currentCount = parseInt($badge.text()) || 0;
        $badge.text(currentCount + 1);
      });
    } else {
      var currentCount = parseInt($badge.text()) || 0;
      $badge.text(currentCount + 1);
    }
  };

  // Also listen for WooCommerce add to cart events
  if (typeof jQuery !== 'undefined') {
    jQuery(document.body).on('added_to_cart', function (fragments, cart_hash, $button) {
      if (typeof window.updateCartBadge === 'function') {
        // Pass fragments directly to the function
        window.updateCartBadge(fragments);
      }
    });
  }
})();

