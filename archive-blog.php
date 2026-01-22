<?php
/*
Template Name: ブログ一覧
*/
get_header();

// 初期表示（新着順）
$q = new WP_Query([
  'post_type'      => 'blog',
  'post_status'    => 'publish',
  'posts_per_page' => 24,
  'paged'          => 1,
  'orderby'        => 'date',
  'order'          => 'DESC',
]);
$max_pages = (int) ($q->max_num_pages ?? 1);
?>
<?php
$featured = function_exists('kumonosu_get_featured_blogs')
  ? kumonosu_get_featured_blogs(5)
  : [];
?>

<?php if (!empty($featured)) : ?>
<section class="l-section l-section--featured">
  <div class="c-section-title">
    <span class="c-section-title-main" aria-hidden="true" data-text="Featured&nbsp;Blog">Featured&nbsp;Blog</span>
    <h2 class="c-section-title-sub" data-text="注目の特集">注目の特集</h2>
  </div>

  <div class="l-card-grid">
    <?php foreach ($featured as $post) : setup_postdata($post); ?>
      <?php get_template_part('templates/parts/c-blogcard', 'css'); ?>
    <?php endforeach; wp_reset_postdata(); ?>
  </div>

  <div class="c-blog-slider-nav">
    <button class="c-blog-slider-prev" aria-label="前へ">«</button>
    <div class="c-blog-slider-dots"></div>
    <button class="c-blog-slider-next" aria-label="次へ">»</button>
  </div>
</section>
<?php endif; ?>

<section class="l-section l-section--blog">
  <div class="c-section-title">
    <span class="c-section-title-main" aria-hidden="true" data-text="Blog">Blog</span>
    <h1 class="c-section-title-sub" data-text="特集&まとめ">特集&まとめ</h1>
  </div>

  <div class="c-blog-list"
    data-kumonosu-list
    data-post-type="blog"
    data-sort="new"
    data-paged="1"
    data-ppp="24"
    data-max="<?php echo esc_attr($max_pages); ?>">

    <div class="l-card-grid" data-kumonosu-grid>
      <?php
      if ($q->have_posts()) :
        while ($q->have_posts()) : $q->the_post();
          // blogカード（あなたのテンプレに合わせる）
          get_template_part('templates/parts/c-blogcard', 'css');
        endwhile;
        wp_reset_postdata();
      endif;
      ?>
    </div>

    <?php if ($max_pages > 1) : ?>
      <button type="button" class="gradient-btn" data-kumonosu-more>
        <span class="gradient-btn__text">MORE</span>
        <span class="gradient-btn__icon">»</span>
      </button>
    <?php endif; ?>

  </div>
</section>

<?php get_footer(); ?>
