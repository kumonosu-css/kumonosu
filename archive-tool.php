<?php
/**
 * archive-tool.php
 * ツール一覧（全件表示 / 区切り「/」）
 */
get_header();

$q = new WP_Query([
  'post_type'      => 'tool',
  'post_status'    => 'publish',
  'posts_per_page' => -1,
  'orderby'        => 'date',
  'order'          => 'DESC',
]);
?>

<section class="l-section l-section--new">
  <div class="c-section-title">
    <span class="c-section-title-main" aria-hidden="true" data-text="Tool">Tool</span>
    <h1 class="c-section-title-sub" data-text="ツール一覧">ツール一覧</h1>
  </div>

  <div class="c-tool-list">
    <?php if ($q->have_posts()) : ?>
      <?php $i = 0; ?>
      <?php while ($q->have_posts()) : $q->the_post(); ?>

        <?php if ($i > 0) : ?>
          <span class="c-tool-separator"> / </span>
        <?php endif; ?>

        <a href="<?php the_permalink(); ?>" class="c-tool-link">
          <?php the_title(); ?>
        </a>

        <?php $i++; ?>
      <?php endwhile; ?>
      <?php wp_reset_postdata(); ?>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>