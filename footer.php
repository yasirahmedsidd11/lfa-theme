<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
</main>
<footer class="site-footer">
  <?php
    $ns = lfa_get('footer.newsletter_sc', lfa_get('home.footer.newsletter_sc',''));
    $mid = intval(lfa_get('footer.menu_id', lfa_get('home.footer.menu_id')));
    $ig  = lfa_get('footer.socials.instagram', lfa_get('home.footer.socials.instagram'));
    $fb  = lfa_get('footer.socials.facebook', lfa_get('home.footer.socials.facebook'));
    $tk  = lfa_get('footer.socials.tiktok', lfa_get('home.footer.socials.tiktok'));
    $big = lfa_get('footer.big_text', lfa_get('home.footer.big_text','LIVINGFIT APPAREL'));
    $cpy = lfa_get('footer.copyright', lfa_get('home.footer.copyright','© '.date('Y').' '.get_bloginfo('name').'. All Rights Reserved'));
    $pid = intval(lfa_get('footer.payment_image', lfa_get('home.footer.payment_image')));
  ?>
  <section class="lfa-footer-block">
    <div class="container lfa-footer-top">
      <div class="lfa-newsletter"><?php echo do_shortcode($ns); ?></div>
      <div class="lfa-footer-right">
        <?php if ($mid): wp_nav_menu(['menu'=>$mid,'container'=>false,'menu_class'=>'lfa-links']); endif; ?>
        <div class="lfa-socials">
          <?php if ($ig): ?><a href="<?php echo esc_url($ig); ?>" aria-label="Instagram">Instagram</a><?php endif; ?>
          <?php if ($fb): ?><a href="<?php echo esc_url($fb); ?>" aria-label="Facebook">Facebook</a><?php endif; ?>
          <?php if ($tk): ?><a href="<?php echo esc_url($tk); ?>" aria-label="TikTok">TikTok</a><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="lfa-footer-bigtext"><?php echo esc_html($big); ?></div>
    <div class="container lfa-footer-bottom">
      <div class="lfa-copy"><?php echo esc_html($cpy); ?></div>
      <div class="lfa-pay"><?php if ($pid): echo wp_get_attachment_image($pid,'medium'); endif; ?></div>
    </div>
  </section>
</footer>
<?php wp_footer(); ?>
</body></html>
