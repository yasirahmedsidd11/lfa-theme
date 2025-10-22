<?php if ( ! defined( 'ABSPATH' ) ) exit;
$markets = lfa_markets_registry();
$current = lfa_current_market_key();
?>
<div class="lfa-market-switcher">
  <label><?php esc_html_e('Ship to:', 'livingfitapparel'); ?></label>
  <ul>
    <?php foreach ($markets as $key => $m): ?>
      <li class="<?php echo $current === $key ? 'active' : ''; ?>">
        <a href="<?php echo esc_url( lfa_market_url($key) ); ?>">
          <?php echo esc_html($m['label']); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
