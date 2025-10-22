<article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
  <header class="entry-header">
    <?php if ( is_singular() ) : the_title('<h1 class="entry-title">','</h1>'); else: ?>
      <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php endif; ?>
  </header>
  <div class="entry-content">
    <?php is_singular() ? the_content() : the_excerpt(); ?>
  </div>
</article>
