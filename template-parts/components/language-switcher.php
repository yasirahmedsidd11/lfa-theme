<?php if ( ! defined( 'ABSPATH' ) ) exit;
$market = lfa_current_market();
$current = lfa_current_language_code();

function lfa_url_with_lang($code) {
  $uri = $_SERVER['REQUEST_URI'] ?? '/';
  $parts = wp_parse_url($uri);
  $qs = [];
  if (!empty($parts['query'])) parse_str($parts['query'], $qs);
  $qs['lang'] = $code;
  $new = ( $parts['path'] ?? '/' ) . '?' . http_build_query($qs);
  return esc_url($new);
}
?>
<div class="lfa-language-switcher">
  <label><?php esc_html_e('Language:', 'livingfitapparel'); ?></label>
  <ul>
    <?php foreach ($market['languages'] as $code): ?>
      <li class="<?php echo $current === $code ? 'active' : ''; ?>">
        <a href="<?php echo lfa_url_with_lang( $code ); ?>">
          <?php echo esc_html( strtoupper( explode('_',$code)[0] ) ); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
