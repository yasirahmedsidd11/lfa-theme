<?php
/*
 * Template Name: Find Your Fit
 */
get_header();

// Get tabs from meta box (repeater field)
$tabs = get_post_meta(get_the_ID(), '_lfa_fyf_tabs', true);
$tabs = is_array($tabs) && !empty($tabs) ? $tabs : array();

// Preload all tab data (images and products) for instant switching
$preloaded_tab_data = array();
if (!empty($tabs) && class_exists('WooCommerce')) {
    foreach ($tabs as $index => $tab) {
        $tab_data = array(
            'image' => '',
            'products' => '',
        );

        // Preload image
        if (!empty($tab['image_id'])) {
            $tab_data['image'] = wp_get_attachment_image($tab['image_id'], 'large');
        }

        // Preload products
        if (!empty($tab['product_ids']) && is_array($tab['product_ids'])) {
            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'post__in' => array_map('intval', $tab['product_ids']),
                'posts_per_page' => -1,
                'orderby' => 'post__in',
            );
            $products_query = new WP_Query($args);

            if ($products_query->have_posts()) {
                ob_start();
                echo '<ul class="products">';
                global $post;
                while ($products_query->have_posts()) {
                    $products_query->the_post();
                    wc_get_template_part('content', 'product');
                }
                echo '</ul>';
                $tab_data['products'] = ob_get_clean();
                wp_reset_postdata();
            } else {
                $tab_data['products'] = '<p>No products found.</p>';
            }
        } else {
            $tab_data['products'] = '<p>No products found.</p>';
        }

        $preloaded_tab_data[$index] = $tab_data;
    }
}

// Get header image (featured image or custom field)
$header_image_id = get_post_thumbnail_id();
if (!$header_image_id) {
    // Try to get from custom field if featured image not set
    $header_image_id = get_post_meta(get_the_ID(), 'header_image_id', true);
}

// Get first tab for initial display
$active_tab = !empty($tabs) ? $tabs[0] : null;
$active_tab_index = 0;

// Get products for active tab
$products = array();
if ($active_tab && !empty($active_tab['product_ids']) && class_exists('WooCommerce')) {
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'post__in' => array_map('intval', $active_tab['product_ids']),
        'posts_per_page' => -1, // Get all specified products
        'orderby' => 'post__in', // Maintain the order specified in the array
    );
    $products_query = new WP_Query($args);
    if ($products_query->have_posts()) {
        $products = $products_query->posts;
    }
    wp_reset_postdata();
}
?>

<main>
    <?php if ($header_image_id): ?>
        <section style="margin: 0;">
            <div class="fyf-header-img-container">
                <?php echo wp_get_attachment_image($header_image_id, 'full'); ?>
            </div>
        </section>
    <?php endif; ?>

    <section>
        <div class="fyf-container">
            <div class="left-section">
                <?php if (!empty($active_tab) && !empty($active_tab['image_id'])): ?>
                    <?php if (!empty($tabs)): ?>
                        <div>
                            <?php foreach ($tabs as $index => $tab): ?>
                                <button class="<?php echo ($index === 0) ? 'is-active' : ''; ?>"
                                    data-tab-index="<?php echo esc_attr($index); ?>">
                                    <?php echo esc_html($tab['name']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="lfa-fyf-sticky-image">
                        <?php echo wp_get_attachment_image($active_tab['image_id'], 'large'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="right-section">
                <div class="lfa-fyf-description" data-tab-index="<?php echo esc_attr($active_tab_index); ?>">
                    <?php
                    if ($active_tab && !empty($active_tab['description'])) {
                        echo wp_kses_post($active_tab['description']);
                    } else {
                        echo '<p>Select a tab above to view products.</p>';
                    }
                    ?>
                </div>

                <div class="lfa-fyf-products" data-tab-index="<?php echo esc_attr($active_tab_index); ?>">
                    <?php if (!empty($products)): ?>
                        <ul class="products">
                            <?php
                            global $post;
                            foreach ($products as $product_post):
                                $post = $product_post;
                                setup_postdata($post);
                                wc_get_template_part('content', 'product');
                            endforeach;
                            wp_reset_postdata();
                            ?>
                        </ul>
                    <?php else: ?>
                        <p>No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    (function () {
        // Preloaded tab data from PHP
        const preloadedTabData = <?php echo json_encode($preloaded_tab_data); ?>;
        const tabsData = <?php echo json_encode(array_map(function ($tab) {
            return array(
                'name' => $tab['name'] ?? '',
                'description' => $tab['description'] ?? '',
                'image_id' => $tab['image_id'] ?? 0,
                'product_ids' => $tab['product_ids'] ?? array(),
            );
        }, $tabs)); ?>;

        const tabs = document.querySelectorAll('button[data-tab-index]');
        const descriptionContainer = document.querySelector('.lfa-fyf-description');
        const productsContainer = document.querySelector('.lfa-fyf-products');
        const stickyImageContainer = document.querySelector('.lfa-fyf-sticky-image');
        const stickyWrapper = stickyImageContainer ? stickyImageContainer.parentElement : null;

        if (!tabs.length || !productsContainer) return;

        // Function to switch tab content instantly
        function switchTab(tabIndex) {
            const tabIndexInt = parseInt(tabIndex);
            const tabData = tabsData[tabIndexInt];
            const preloadedData = preloadedTabData[tabIndexInt];

            if (!tabData) return;

            // Update description immediately
            if (descriptionContainer && tabData.description) {
                descriptionContainer.innerHTML = tabData.description;
                descriptionContainer.setAttribute('data-tab-index', tabIndex);
            } else if (descriptionContainer) {
                descriptionContainer.innerHTML = '<p>Select a tab above to view products.</p>';
                descriptionContainer.setAttribute('data-tab-index', tabIndex);
            }

            // Update image immediately from preloaded data
            if (stickyImageContainer && preloadedData && preloadedData.image) {
                stickyImageContainer.innerHTML = preloadedData.image;
            } else if (stickyImageContainer) {
                stickyImageContainer.innerHTML = '';
            }

            // Show/hide wrapper based on image availability
            if (stickyWrapper && stickyImageContainer) {
                if (preloadedData && preloadedData.image) {
                    stickyWrapper.style.display = 'block';
                } else {
                    stickyWrapper.style.display = 'none';
                }
            }

            // Update products immediately from preloaded data
            if (productsContainer && preloadedData && preloadedData.products) {
                productsContainer.innerHTML = preloadedData.products;
                productsContainer.setAttribute('data-tab-index', tabIndex);
            } else if (productsContainer) {
                productsContainer.innerHTML = '<p>No products found.</p>';
                productsContainer.setAttribute('data-tab-index', tabIndex);
            }
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.stopPropagation(); // Prevent event bubbling
                const tabIndex = this.getAttribute('data-tab-index');

                // Update active tab
                tabs.forEach(function (t) {
                    t.classList.remove('is-active');
                });
                this.classList.add('is-active');

                // Switch tab content instantly using preloaded data
                switchTab(tabIndex);

                // Scroll to description so it's visible when tab changes
                if (descriptionContainer) {
                    setTimeout(function() {
                        descriptionContainer.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'nearest' });
                    }, 100);
                }
            });
        });
    })();
</script>

<?php get_footer(); ?>