<?php
/**
 * The sidebar containing the main shop widget area
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;
?>

<aside id="secondary" class="widget-area shop-sidebar lfa-shop-filters" role="complementary">
	<!-- Sort By Filter -->
	<div class="lfa-filter-section" data-filter="sort">
		<button type="button" class="lfa-filter-toggle" aria-expanded="false">
			<span class="lfa-filter-label"><?php esc_html_e('SORT BY', 'livingfitapparel'); ?><span class="lfa-filter-count-badge" data-count="0"></span></span>
			<span class="lfa-filter-icon">+</span>
		</button>
		<div class="lfa-filter-content" style="display: none;">
			<?php
			// Sort options
			$sort_options = array(
				'featured' => __('Featured', 'livingfitapparel'),
				'price' => __('Price (low to high)', 'livingfitapparel'),
				'price-desc' => __('Price (high to low)', 'livingfitapparel'),
				'alphabetically-asc' => __('Alphabetically (A-Z)', 'livingfitapparel'),
				'alphabetically-desc' => __('Alphabetically (Z-A)', 'livingfitapparel'),
				'new-in-oldest' => __('New In (Oldest first)', 'livingfitapparel'),
				'new-in' => __('New In', 'livingfitapparel'),
				'most-viewed' => __('Most Viewed', 'livingfitapparel'),
				'best-selling' => __('Best Selling', 'livingfitapparel'),
			);
			?>
			<ul class="lfa-filter-list">
				<?php foreach ($sort_options as $value => $label): ?>
					<li>
						<label>
							<input type="radio" name="sort_by" value="<?php echo esc_attr($value); ?>">
							<?php echo esc_html($label); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<!-- Categories Filter -->
	<div class="lfa-filter-section" data-filter="categories">
		<button type="button" class="lfa-filter-toggle" aria-expanded="false">
			<span class="lfa-filter-label"><?php esc_html_e('CATEGORIES', 'livingfitapparel'); ?><span class="lfa-filter-count-badge" data-count="0"></span></span>
			<span class="lfa-filter-icon">+</span>
		</button>
		<div class="lfa-filter-content" style="display: none;">
			<?php
			$categories = get_terms(array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
			));
			?>
			<ul class="lfa-filter-list">
				<?php if (!empty($categories) && !is_wp_error($categories)): ?>
					<?php foreach ($categories as $category): ?>
						<li>
							<label>
								<input type="checkbox" name="category" value="<?php echo esc_attr($category->term_id); ?>">
								<?php echo esc_html($category->name); ?>
								<span class="lfa-filter-count">(<?php echo esc_html($category->count); ?>)</span>
							</label>
						</li>
					<?php endforeach; ?>
				<?php else: ?>
					<li><?php esc_html_e('No categories found', 'livingfitapparel'); ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>

	<!-- Colors Filter -->
	<div class="lfa-filter-section" data-filter="colors">
		<button type="button" class="lfa-filter-toggle" aria-expanded="false">
			<span class="lfa-filter-label"><?php esc_html_e('COLORS', 'livingfitapparel'); ?><span class="lfa-filter-count-badge" data-count="0"></span></span>
			<span class="lfa-filter-icon">+</span>
		</button>
		<div class="lfa-filter-content" style="display: none;">
			<?php
			$colors = get_terms(array(
				'taxonomy' => 'pa_color',
				'hide_empty' => false,
			));
			?>
			<ul class="lfa-filter-list lfa-filter-colors">
				<?php if (!empty($colors) && !is_wp_error($colors)): ?>
					<?php foreach ($colors as $color): 
						// Get color hex code from term meta
						$color_hex = get_term_meta($color->term_id, 'color', true);
						if (empty($color_hex)) {
							$color_hex = get_term_meta($color->term_id, 'product_attribute_color', true);
						}
						if (empty($color_hex)) {
							$color_hex = get_term_meta($color->term_id, 'pa_color', true);
						}
						// Fallback to helper function
						if (empty($color_hex) && function_exists('lfa_get_color_hex_from_name')) {
							$color_hex = lfa_get_color_hex_from_name($color->name);
						}
						if (empty($color_hex)) {
							$color_hex = '#cccccc';
						}
						
						$has_products = $color->count > 0;
						$item_class = $has_products ? 'lfa-color-item' : 'lfa-color-item lfa-color-item-disabled';
					?>
						<li class="<?php echo esc_attr($item_class); ?>">
							<label>
								<input type="radio" name="color" value="<?php echo esc_attr($color->term_id); ?>" <?php echo $has_products ? '' : 'disabled'; ?>>
								<span class="lfa-color-swatch-filter" style="background-color: <?php echo esc_attr($color_hex); ?>;"></span>
								<span class="lfa-color-name"><?php echo esc_html($color->name); ?></span>
								<span class="lfa-filter-count">(<?php echo esc_html($color->count); ?>)</span>
							</label>
						</li>
					<?php endforeach; ?>
				<?php else: ?>
					<li><?php esc_html_e('No colors found', 'livingfitapparel'); ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>

	<!-- Sizes Filter -->
	<div class="lfa-filter-section" data-filter="sizes">
		<button type="button" class="lfa-filter-toggle" aria-expanded="false">
			<span class="lfa-filter-label"><?php esc_html_e('SIZES', 'livingfitapparel'); ?><span class="lfa-filter-count-badge" data-count="0"></span></span>
			<span class="lfa-filter-icon">+</span>
		</button>
		<div class="lfa-filter-content" style="display: none;">
			<?php
			$sizes = get_terms(array(
				'taxonomy' => 'pa_size',
				'hide_empty' => false,
			));
			?>
			<ul class="lfa-filter-list">
				<?php if (!empty($sizes) && !is_wp_error($sizes)): ?>
					<?php foreach ($sizes as $size): ?>
						<li>
							<label>
								<input type="checkbox" name="size" value="<?php echo esc_attr($size->term_id); ?>">
								<?php echo esc_html($size->name); ?>
								<span class="lfa-filter-count">(<?php echo esc_html($size->count); ?>)</span>
							</label>
						</li>
					<?php endforeach; ?>
				<?php else: ?>
					<li><?php esc_html_e('No sizes found', 'livingfitapparel'); ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	
	<!-- Clear All Button -->
	<button type="button" class="lfa-clear-all-filters" id="lfa-clear-all-filters">
		<?php esc_html_e('CLEAR ALL', 'livingfitapparel'); ?>
	</button>
</aside>

<script>
(function() {
	// Define runFilterCode first so it's available when called
	function runFilterCode() {
		// Shop filters accordion functionality with localStorage
		const filterSections = document.querySelectorAll('.lfa-filter-section');
		let currentlyOpen = null;
		
		// Function to close a section with animation
		function closeSection(section) {
			const toggle = section.querySelector('.lfa-filter-toggle');
			const content = section.querySelector('.lfa-filter-content');
			const icon = section.querySelector('.lfa-filter-icon');
			const filterName = section.getAttribute('data-filter');
			
			// Remove open class to trigger close animation
			content.classList.remove('is-open');
			toggle.setAttribute('aria-expanded', 'false');
			icon.textContent = '+';
			localStorage.setItem('lfa_filter_' + filterName, 'closed');
			
			// Set display to none after animation completes
			setTimeout(function() {
				if (!content.classList.contains('is-open')) {
					content.style.display = 'none';
				}
			}, 400);
		}
		
		// Function to open a section with animation
		function openSection(section) {
			const toggle = section.querySelector('.lfa-filter-toggle');
			const content = section.querySelector('.lfa-filter-content');
			const icon = section.querySelector('.lfa-filter-icon');
			const filterName = section.getAttribute('data-filter');
			
			// Set display first, then add open class to trigger animation
			content.style.display = 'block';
			// Force reflow to ensure display is set before animation
			content.offsetHeight;
			content.classList.add('is-open');
			toggle.setAttribute('aria-expanded', 'true');
			icon.textContent = '−';
			localStorage.setItem('lfa_filter_' + filterName, 'open');
			localStorage.setItem('lfa_filter_last_open', filterName);
		}
		
		// Load saved state from localStorage - only open the last opened one
		const lastOpen = localStorage.getItem('lfa_filter_last_open');
		filterSections.forEach(function(section) {
			const filterName = section.getAttribute('data-filter');
			const savedState = localStorage.getItem('lfa_filter_' + filterName);
			const content = section.querySelector('.lfa-filter-content');
			
			// Set initial state without animation on page load
			if (savedState === 'open' && filterName === lastOpen) {
				content.style.display = 'block';
				content.classList.add('is-open');
				section.querySelector('.lfa-filter-toggle').setAttribute('aria-expanded', 'true');
				section.querySelector('.lfa-filter-icon').textContent = '−';
				currentlyOpen = section;
			} else {
				content.style.display = 'none';
				content.classList.remove('is-open');
				section.querySelector('.lfa-filter-toggle').setAttribute('aria-expanded', 'false');
				section.querySelector('.lfa-filter-icon').textContent = '+';
			}
		});
		
		// Accordion toggle functionality
		filterSections.forEach(function(section) {
			const toggle = section.querySelector('.lfa-filter-toggle');
			
			toggle.addEventListener('click', function() {
				const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
				
				if (isExpanded) {
					// Close this section
					closeSection(section);
					currentlyOpen = null;
				} else {
					// Close previously open section (if any)
					if (currentlyOpen && currentlyOpen !== section) {
						closeSection(currentlyOpen);
					}
					// Open this section
					openSection(section);
					currentlyOpen = section;
				}
			});
		});
		
		// AJAX Filter Functionality
		let filterTimeout;
		// Removed unused variables for debug mode
		// const productsContainer = document.querySelector('.woocommerce-products');
		// const paginationContainer = document.querySelector('.woocommerce-pagination');
		
		function getFilterValues() {
			const filters = {
				orderby: '',
				categories: [],
				colors: [],
				sizes: []
			};
			
			// Get sort by value
			const sortRadio = document.querySelector('input[name="sort_by"]:checked');
			if (sortRadio) {
				filters.orderby = sortRadio.value;
			}
			
			// Get selected categories
			const categoryCheckboxes = document.querySelectorAll('input[name="category"]:checked');
			categoryCheckboxes.forEach(function(cb) {
				filters.categories.push(parseInt(cb.value));
			});
			
			// Get selected color (radio - single selection)
			const colorRadio = document.querySelector('input[name="color"]:checked');
			if (colorRadio) {
				filters.colors.push(parseInt(colorRadio.value));
			}
			
			// Get selected sizes
			const sizeCheckboxes = document.querySelectorAll('input[name="size"]:checked');
			sizeCheckboxes.forEach(function(cb) {
				filters.sizes.push(parseInt(cb.value));
			});
			
			return filters;
		}
		
		function updateFilterOptions(categories, callback) {
			if (typeof LFA === 'undefined' || !LFA.ajaxUrl) {
				if (callback) callback();
				return;
			}
			
			// Get available colors and sizes for selected categories
			const formData = new FormData();
			formData.append('action', 'lfa_get_filter_options');
			formData.append('nonce', LFA.nonce);
			formData.append('categories', JSON.stringify(categories));
			
			fetch(LFA.ajaxUrl, {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success && data.data) {
					const availableColors = data.data.available_colors || [];
					const availableSizes = data.data.available_sizes || [];
					
					// Update color filters
					const colorInputs = document.querySelectorAll('input[name="color"]');
					colorInputs.forEach(function(input) {
						const colorId = parseInt(input.value);
						const colorItem = input.closest('.lfa-color-item');
						
						// If no categories selected, all colors are available
						// Otherwise, check if color is in available list
						if (categories.length === 0 || availableColors.includes(colorId)) {
							input.disabled = false;
							if (colorItem) {
								colorItem.classList.remove('lfa-color-item-disabled');
							}
						} else {
							input.disabled = true;
							if (input.checked) {
								input.checked = false;
								// Trigger filter update after unchecking
								setTimeout(applyFilters, 100);
							}
							if (colorItem) {
								colorItem.classList.add('lfa-color-item-disabled');
							}
						}
					});
					
					// Update size filters
					const sizeInputs = document.querySelectorAll('input[name="size"]');
					sizeInputs.forEach(function(input) {
						const sizeId = parseInt(input.value);
						const sizeItem = input.closest('li');
						
						// If no categories selected, all sizes are available
						// Otherwise, check if size is in available list
						if (categories.length === 0 || availableSizes.includes(sizeId)) {
							input.disabled = false;
							if (sizeItem) {
								sizeItem.classList.remove('lfa-filter-disabled');
							}
						} else {
							input.disabled = true;
							if (input.checked) {
								input.checked = false;
								// Trigger filter update after unchecking
								setTimeout(applyFilters, 100);
							}
							if (sizeItem) {
								sizeItem.classList.add('lfa-filter-disabled');
							}
						}
					});
				}
				
				// Call callback if provided
				if (callback) callback();
			})
			.catch(error => {
				console.error('Failed to update filter options:', error);
				// Call callback even on error
				if (callback) callback();
			});
		}
		
		function applyFilters() {
			if (typeof LFA === 'undefined' || !LFA.ajaxUrl) {
				console.error('LFA object not defined');
				return;
			}
			
			const filters = getFilterValues();
			const productsWrapper = document.querySelector('.woocommerce-content-wrapper');
			const productsContainer = productsWrapper ? productsWrapper.querySelector('.woocommerce-products') : null;
			
			if (!productsWrapper) {
				return;
			}
			
			// Show skeleton loading
			productsWrapper.classList.add('lfa-loading');
			
			// Hide "no products found" messages
			const noProductsMessages = productsWrapper.querySelectorAll('.woocommerce-info, .woocommerce-no-products-found');
			noProductsMessages.forEach(function(msg) {
				msg.style.display = 'none';
			});
			
			// Hide pagination during loading (don't remove it, just hide)
			const existingPagination = productsWrapper.querySelector('.woocommerce-pagination');
			if (existingPagination) {
				existingPagination.style.display = 'none';
			}
			
			// Create or show skeleton loaders
			let container = productsContainer;
			if (!container) {
				container = productsWrapper.querySelector('.woocommerce-products');
			}
			if (!container) {
				container = document.createElement('div');
				container.className = 'woocommerce-products';
				productsWrapper.appendChild(container);
			}
			
			// Generate skeleton HTML for 9 products (3 rows of 3 columns)
			const skeletonHTML = '<ul class="products lfa-grid lfa-grid-3 lfa-skeleton-loading">' +
				Array(9).fill(0).map(() => 
					'<li class="product type-product status-publish"><div class="lfa-skeleton-product">' +
					'<div class="lfa-skeleton-image"></div>' +
					'<div class="lfa-skeleton-content">' +
					'<div class="lfa-skeleton-title"></div>' +
					'<div class="lfa-skeleton-title short"></div>' +
					'<div class="lfa-skeleton-price"></div>' +
					'</div></div></li>'
				).join('') +
				'</ul>';
			
			container.innerHTML = skeletonHTML;
			
			// Create promises for both APIs
			const promises = [];
			
			// Promise 1: Get filter options (if categories are selected)
			let filterOptionsPromise = null;
			if (filters.categories.length > 0) {
				const filterFormData = new FormData();
				filterFormData.append('action', 'lfa_get_filter_options');
				filterFormData.append('nonce', LFA.nonce);
				filterFormData.append('categories', JSON.stringify(filters.categories));
				
				filterOptionsPromise = fetch(LFA.ajaxUrl, {
					method: 'POST',
					body: filterFormData
				})
				.then(response => response.json())
				.then(data => {
					if (data.success && data.data) {
						const availableColors = data.data.available_colors || [];
						const availableSizes = data.data.available_sizes || [];
						
						// Update color filters
						const colorInputs = document.querySelectorAll('input[name="color"]');
						colorInputs.forEach(function(input) {
							const colorId = parseInt(input.value);
							const colorItem = input.closest('.lfa-color-item');
							
							if (availableColors.includes(colorId)) {
								input.disabled = false;
								if (colorItem) {
									colorItem.classList.remove('lfa-color-item-disabled');
								}
							} else {
								input.disabled = true;
								if (input.checked) {
									input.checked = false;
								}
								if (colorItem) {
									colorItem.classList.add('lfa-color-item-disabled');
								}
							}
						});
						
						// Update size filters
						const sizeInputs = document.querySelectorAll('input[name="size"]');
						sizeInputs.forEach(function(input) {
							const sizeId = parseInt(input.value);
							const sizeItem = input.closest('li');
							
							if (availableSizes.includes(sizeId)) {
								input.disabled = false;
								if (sizeItem) {
									sizeItem.classList.remove('lfa-filter-disabled');
								}
							} else {
								input.disabled = true;
								if (input.checked) {
									input.checked = false;
								}
								if (sizeItem) {
									sizeItem.classList.add('lfa-filter-disabled');
								}
							}
						});
					}
					return data;
				});
				promises.push(filterOptionsPromise);
			} else {
				// No categories selected, enable all filters
				const colorInputs = document.querySelectorAll('input[name="color"]');
				colorInputs.forEach(function(input) {
					input.disabled = false;
					const colorItem = input.closest('.lfa-color-item');
					if (colorItem) {
						colorItem.classList.remove('lfa-color-item-disabled');
					}
				});
				
				const sizeInputs = document.querySelectorAll('input[name="size"]');
				sizeInputs.forEach(function(input) {
					input.disabled = false;
					const sizeItem = input.closest('li');
					if (sizeItem) {
						sizeItem.classList.remove('lfa-filter-disabled');
					}
				});
			}
			
			// Promise 2: Get filtered products
			const productsFormData = new FormData();
			productsFormData.append('action', 'lfa_filter_products');
			productsFormData.append('nonce', LFA.nonce);
			productsFormData.append('orderby', filters.orderby);
			productsFormData.append('categories', JSON.stringify(filters.categories));
			productsFormData.append('colors', JSON.stringify(filters.colors));
			productsFormData.append('sizes', JSON.stringify(filters.sizes));
			productsFormData.append('paged', 1);
			
			const productsPromise = fetch(LFA.ajaxUrl, {
				method: 'POST',
				body: productsFormData
			})
			.then(response => response.json());
			
			promises.push(productsPromise);
			
			// Execute both APIs in parallel using Promise.all
			Promise.all(promises)
			.then(results => {
				// results[0] = filter options (if categories selected), results[1] = products
				// OR results[0] = products (if no categories)
				const productsData = filters.categories.length > 0 ? results[1] : results[0];
				const filterOptionsData = filters.categories.length > 0 ? results[0] : null;
				
				// Hide skeleton loading
				productsWrapper.classList.remove('lfa-loading');
				
				// Update products
				if (productsData && productsData.success && productsData.data && productsData.data.products) {
					// Find or create products container
					let container = productsContainer;
					if (!container) {
						container = productsWrapper.querySelector('.woocommerce-products');
					}
					if (!container) {
						container = document.createElement('div');
						container.className = 'woocommerce-products';
						productsWrapper.appendChild(container);
					}
					
					// Replace products
					const existingUl = container.querySelector('ul.products');
					if (existingUl) {
						existingUl.outerHTML = productsData.data.products;
					} else {
						container.innerHTML = productsData.data.products;
					}
					
					// Update pagination - search within productsWrapper
					if (productsData.data.pagination) {
						let paginationContainer = productsWrapper.querySelector('.woocommerce-pagination');
						if (!paginationContainer) {
							// Create pagination container after products container
							paginationContainer = document.createElement('nav');
							paginationContainer.className = 'woocommerce-pagination';
							// Insert after products container
							if (container && container.parentNode) {
								container.parentNode.insertBefore(paginationContainer, container.nextSibling);
							} else {
								productsWrapper.appendChild(paginationContainer);
							}
						}
						paginationContainer.innerHTML = productsData.data.pagination;
						paginationContainer.style.display = '';
					} else {
						// Hide pagination if no products (don't remove, just hide)
						const paginationContainer = productsWrapper.querySelector('.woocommerce-pagination');
						if (paginationContainer) {
							paginationContainer.style.display = 'none';
						}
					}
					
					// Trigger re-initialization events
					window.dispatchEvent(new CustomEvent('lfa-products-updated'));
				} else {
					// No products found - show message
					let container = productsContainer;
					if (!container) {
						container = productsWrapper.querySelector('.woocommerce-products');
					}
					if (!container) {
						container = document.createElement('div');
						container.className = 'woocommerce-products';
						productsWrapper.appendChild(container);
					}
					
					// Show WooCommerce no products found message (use the HTML from AJAX response if available)
					if (productsData && productsData.data && productsData.data.products) {
						container.innerHTML = productsData.data.products;
					} else {
						const message = (productsData && productsData.data && productsData.data.message) 
							? productsData.data.message 
							: 'No products were found matching your selection.';
						container.innerHTML = '<p class="woocommerce-info">' + message + '</p>';
					}
					
					// Hide pagination (don't remove, just hide)
					const paginationContainer = productsWrapper.querySelector('.woocommerce-pagination');
					if (paginationContainer) {
						paginationContainer.style.display = 'none';
					}
				}
				
				// Update URL without reload
				const url = new URL(window.location.href);
				if (filters.orderby && filters.orderby !== 'default') {
					url.searchParams.set('orderby', filters.orderby);
				} else {
					url.searchParams.delete('orderby');
				}
				if (filters.categories.length) {
					url.searchParams.set('categories', filters.categories.join(','));
				} else {
					url.searchParams.delete('categories');
				}
				if (filters.colors.length) {
					url.searchParams.set('colors', filters.colors.join(','));
				} else {
					url.searchParams.delete('colors');
				}
				if (filters.sizes.length) {
					url.searchParams.set('sizes', filters.sizes.join(','));
				} else {
					url.searchParams.delete('sizes');
				}
				window.history.pushState({}, '', url);
			})
			.catch(error => {
				console.error('AJAX Error:', error);
				
				// Hide skeleton loading
				productsWrapper.classList.remove('lfa-loading');
				
				// Show error message
				let container = productsContainer;
				if (!container) {
					container = productsWrapper.querySelector('.woocommerce-products');
				}
				if (container) {
					container.innerHTML = '<p class="woocommerce-info">An error occurred while loading products. Please try again.</p>';
				}
			});
		}
		
		// Function to update filter count badges
		function updateFilterCounts() {
			const filters = getFilterValues();
			
			// Update sort by count (0 or 1)
			const sortBadge = document.querySelector('[data-filter="sort"] .lfa-filter-count-badge');
			if (sortBadge) {
				const sortCount = filters.orderby ? 1 : 0;
				sortBadge.setAttribute('data-count', sortCount);
				sortBadge.textContent = sortCount > 0 ? '(' + sortCount + ')' : '';
			}
			
			// Update categories count
			const categoriesBadge = document.querySelector('[data-filter="categories"] .lfa-filter-count-badge');
			if (categoriesBadge) {
				const categoriesCount = filters.categories.length;
				categoriesBadge.setAttribute('data-count', categoriesCount);
				categoriesBadge.textContent = categoriesCount > 0 ? '(' + categoriesCount + ')' : '';
			}
			
			// Update colors count (0 or 1 since it's radio)
			const colorsBadge = document.querySelector('[data-filter="colors"] .lfa-filter-count-badge');
			if (colorsBadge) {
				const colorsCount = filters.colors.length;
				colorsBadge.setAttribute('data-count', colorsCount);
				colorsBadge.textContent = colorsCount > 0 ? '(' + colorsCount + ')' : '';
			}
			
			// Update sizes count
			const sizesBadge = document.querySelector('[data-filter="sizes"] .lfa-filter-count-badge');
			if (sizesBadge) {
				const sizesCount = filters.sizes.length;
				sizesBadge.setAttribute('data-count', sizesCount);
				sizesBadge.textContent = sizesCount > 0 ? '(' + sizesCount + ')' : '';
			}
		}
		
		// Function to set filters from URL parameters
		function setFiltersFromURL() {
			const urlParams = new URLSearchParams(window.location.search);
			
			// Set sort by
			const orderby = urlParams.get('orderby');
			if (orderby) {
				const sortInput = document.querySelector('input[name="sort_by"][value="' + orderby + '"]');
				if (sortInput) {
					sortInput.checked = true;
				}
			}
			
			// Set categories
			const categoriesParam = urlParams.get('categories');
			if (categoriesParam) {
				const categoryIds = categoriesParam.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
				categoryIds.forEach(function(catId) {
					const catInput = document.querySelector('input[name="category"][value="' + catId + '"]');
					if (catInput) {
						catInput.checked = true;
					}
				});
			}
			
			// Set color (single selection)
			const colorsParam = urlParams.get('colors');
			if (colorsParam) {
				const colorIds = colorsParam.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
				if (colorIds.length > 0) {
					const colorInput = document.querySelector('input[name="color"][value="' + colorIds[0] + '"]');
					if (colorInput) {
						colorInput.checked = true;
					}
				}
			}
			
			// Set sizes
			const sizesParam = urlParams.get('sizes');
			if (sizesParam) {
				const sizeIds = sizesParam.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
				sizeIds.forEach(function(sizeId) {
					const sizeInput = document.querySelector('input[name="size"][value="' + sizeId + '"]');
					if (sizeInput) {
						sizeInput.checked = true;
					}
				});
			}
		}
		
		// Set filters from URL on page load
		setFiltersFromURL();
		
		// Check if URL has filter parameters and auto-apply on page load
		const urlParams = new URLSearchParams(window.location.search);
		const hasUrlFilters = urlParams.has('orderby') || urlParams.has('categories') || urlParams.has('colors') || urlParams.has('sizes');
		
		if (hasUrlFilters) {
			// Apply filters on page load (now handles filter options update internally via Promise.all)
			setTimeout(function() {
				applyFilters();
			}, 200);
		}
		
		// Update counts on page load
		updateFilterCounts();
		
		// Handle color radio button click to allow unselecting
		// Store the checked state before click
		const colorClickState = new Map();
		
		document.addEventListener('mousedown', function(e) {
			let colorInput = null;
			
			// Check if clicking on a color input or its label
			if (e.target.matches('input[name="color"]')) {
				colorInput = e.target;
			} else if (e.target.closest('label')) {
				const label = e.target.closest('label');
				colorInput = label.querySelector('input[name="color"]');
			}
			
			if (colorInput && !colorInput.disabled) {
				// Store the checked state before the click
				colorClickState.set(colorInput, colorInput.checked);
			}
		}, true);
		
		document.addEventListener('click', function(e) {
			let colorInput = null;
			
			// Check if clicking directly on the input
			if (e.target.matches('input[name="color"]')) {
				colorInput = e.target;
			}
			// Check if clicking on the label or color swatch
			else if (e.target.closest('label')) {
				const label = e.target.closest('label');
				colorInput = label.querySelector('input[name="color"]');
			}
			
			if (colorInput && !colorInput.disabled) {
				// Get the state before the click
				const wasChecked = colorClickState.get(colorInput) || false;
				
				// Use setTimeout to check after the default radio button behavior
				setTimeout(function() {
					// If it was already checked and is still checked, uncheck it
					if (wasChecked && colorInput.checked) {
						colorInput.checked = false;
						// Update counts and apply filters
						updateFilterCounts();
						clearTimeout(filterTimeout);
						filterTimeout = setTimeout(applyFilters, 300);
					}
					// Clean up
					colorClickState.delete(colorInput);
				}, 10);
			}
		});
		
		// Clear All Filters functionality
		const clearAllButton = document.getElementById('lfa-clear-all-filters');
		if (clearAllButton) {
			clearAllButton.addEventListener('click', function() {
				// Uncheck all filter inputs
				document.querySelectorAll('input[name="sort_by"]:checked').forEach(function(input) {
					input.checked = false;
				});
				
				document.querySelectorAll('input[name="category"]:checked').forEach(function(input) {
					input.checked = false;
				});
				
				document.querySelectorAll('input[name="color"]:checked').forEach(function(input) {
					input.checked = false;
				});
				
				document.querySelectorAll('input[name="size"]:checked').forEach(function(input) {
					input.checked = false;
				});
				
				// Update filter counts
				updateFilterCounts();
				
				// Apply filters (will enable all options and show all products via Promise.all)
				applyFilters();
			});
		}
		
		// Listen for filter changes with debounce
		document.addEventListener('change', function(e) {
			if (e.target.matches('input[name="sort_by"], input[name="category"], input[name="color"], input[name="size"]')) {
				// Update counts immediately
				updateFilterCounts();
				
				// Apply filters (now handles filter options update internally via Promise.all)
				clearTimeout(filterTimeout);
				filterTimeout = setTimeout(applyFilters, 300); // Debounce 300ms
			}
		});
	}
	
	// Wait for LFA object and DOM to be ready
	function initShopFilters() {
		if (typeof LFA === 'undefined' || !LFA.ajaxUrl) {
			// Retry after a short delay if LFA is not yet loaded
			setTimeout(initShopFilters, 50);
			return;
		}
		
		// Check if DOM is already loaded
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', runFilterCode);
		} else {
			// DOM is already loaded, run immediately
			runFilterCode();
		}
	}
	
	// Start initialization - wait for LFA object
	initShopFilters();
})();
</script>

