<?php if (!defined('ABSPATH'))
  exit; ?>
</main>
<footer class="site-footer">
  <?php
  $ns = lfa_get('footer.newsletter_sc', lfa_get('home.footer.newsletter_sc', ''));
  $menu_names = ['Footer 1', 'Footer 2', 'Footer 3'];
  $ig = lfa_get('footer.socials.instagram', lfa_get('home.footer.socials.instagram'));
  $fb = lfa_get('footer.socials.facebook', lfa_get('home.footer.socials.facebook'));
  $tk = lfa_get('footer.socials.tiktok', lfa_get('home.footer.socials.tiktok'));
  $big = lfa_get('footer.big_text', lfa_get('home.footer.big_text', 'LIVINGFIT APPAREL'));
  $cpy = lfa_get('footer.copyright', lfa_get('home.footer.copyright', '© ' . date('Y') . ' ' . get_bloginfo('name') . '. All Rights Reserved'));
  $pid = intval(lfa_get('footer.payment_image', lfa_get('home.footer.payment_image')));
  ?>
  <section class="lfa-footer-block">
    <div class="container lfa-footer-top">
      <!-- Left Column: Newsletter (30%) -->
      <div class="lfa-footer-left">
        <h3><?php _e('Sign up to be the first to hear about all things LFA.', 'livingfitapparel'); ?></h3>
        <div class="lfa-newsletter"><?php echo do_shortcode($ns); ?></div>
      </div>

      <!-- Right Column: Menus + Social Icons (70%) -->
      <div class="lfa-footer-right">
        <!-- Menu Column 1 -->
        <div class="lfa-footer-menu-col">
          <?php wp_nav_menu(['menu' => $menu_names[0], 'container' => false, 'menu_class' => 'lfa-footer-nav']); ?>
        </div>

        <!-- Menu Column 2 -->
        <div class="lfa-footer-menu-col">
          <?php wp_nav_menu(['menu' => $menu_names[1], 'container' => false, 'menu_class' => 'lfa-footer-nav']); ?>
        </div>

        <!-- Menu Column 3 -->
        <div class="lfa-footer-menu-col">
          <?php wp_nav_menu(['menu' => $menu_names[2], 'container' => false, 'menu_class' => 'lfa-footer-nav']); ?>
        </div>

        <!-- Social Icons Column -->
        <div class="lfa-footer-socials-col">
          <?php if ($fb): ?><a href="<?php echo esc_url($fb); ?>" class="lfa-social-icon lfa-social-facebook"
              aria-label="<?php esc_attr_e('Facebook', 'livingfitapparel'); ?>" target="_blank" rel="noopener">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12c0-5.523-4.477-10-10-10z"
                  fill="#111" />
              </svg>
            </a><?php endif; ?>
          <?php if ($ig): ?><a href="<?php echo esc_url($ig); ?>" class="lfa-social-icon lfa-social-instagram"
              aria-label="<?php esc_attr_e('Instagram', 'livingfitapparel'); ?>" target="_blank" rel="noopener">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                  fill="#111" />
              </svg>
            </a><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="lfa-footer-bigtext"><img style="width:100%; padding-inline:10px; box-sizing:border-box;" src="/lfapk/wp-content/uploads/2026/01/LFA-footer-logo.webp" /></div>
    <div class="container lfa-footer-bottom">
      <div class="lfa-copy"><?php echo esc_html($cpy); ?></div>
      <div class="lfa-pay"><?php if ($pid):
        echo wp_get_attachment_image($pid, 'medium');
      endif; ?></div>
    </div>
  </section>
  <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
  <script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>
  <script>
    // gsap.registerPlugin(ScrollTrigger);

    // function initFooterAnimation() {
    //   const footer = document.querySelector(".lfa-footer-bigtext");
    //   if (!footer) return;

    //   const footerTimeline = gsap.timeline({
    //     scrollTrigger: {
    //       trigger: footer,
    //       start: "top bottom",
    //       end: "top center",
    //       scrub: true,
    //       invalidateOnRefresh: true
    //     }
    //   });

    //   footerTimeline
    //     .fromTo(".top-text", {
    //       xPercent: 100
    //     }, {
    //       xPercent: 0
    //     }, 0)
    //     .fromTo(".bottom-text", {
    //       xPercent: -100
    //     }, {
    //       xPercent: 0
    //     }, 0);

    //   ScrollTrigger.refresh();
    // }

    // // Wait until everything is loaded
    // window.addEventListener("load", () => {
    //   setTimeout(() => {
    //     initFooterAnimation();
    //   }, 1000); // 1 second delay to ensure all elements are loaded
    // });
  </script>



</footer>
<?php
// Include quick view modal
wc_get_template('quick-view-modal.php', array(), '', get_template_directory() . '/woocommerce/');
?>
<?php wp_footer(); ?>
</body>

</html>