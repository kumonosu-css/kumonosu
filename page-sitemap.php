<?php
get_header();
?>

<?php
$queried = get_queried_object();
$slug = is_a($queried, 'WP_Post') ? $queried->post_name : '';
$templates = array();
if ($slug) {
  $templates[] = 'components/pages/page-' . $slug . '.php';
}
$templates[] = 'components/pages/page.php';
$template_path = locate_template($templates, false, false);
if ($template_path) {
  include $template_path;
} else {
  get_template_part('components/pages/page');
}
?>

<section class="l-section l-section--about">
  <h1 class="c-section-title">
    <span class="c-section-title-main" data-text="<?php
      global $post;
      $slug = $post->post_name;

      // ucfirst() で先頭だけ大文字にする
      echo ucfirst( $slug );
    ?>">
    <?php
      global $post;
      $slug = $post->post_name;

      // ucfirst() で先頭だけ大文字にする
      echo ucfirst( $slug );
    ?>
    </span>
    <span class="c-section-title-sub"><?php the_title(); ?></span>
  </h1>
  <div class="c-section-content">
    <nav class="c-sitemap-group" aria-labelledby="sitemap-main">
      <h2 class="c-sitemap-heading" id="sitemap-main">KUMONOSU</h2>
      <ul class="c-sitemap-list">
        <li><a href="/">トップ</a></li>
        <li><a href="/css/">CSS</a></li>
        <li><a href="/blog/">特集・まとめ</a></li>
        <li><a href="/about/">About</a></li>
        <li><a href="/contact/">Contact</a></li>
      </ul>
    </nav>

    <nav class="c-sitemap-group" aria-labelledby="sitemap-other">
      <h2 class="c-sitemap-heading" id="sitemap-other">その他</h2>
      <ul class="c-sitemap-list">
        <li><a href="/privacy-policy/">Privacy Policy</a></li>
        <li><span aria-current="page">Site Map</span></li>
      </ul>
    </nav>

    <nav class="c-sitemap-group" aria-labelledby="sitemap-external">
      <h2 class="c-sitemap-heading" id="sitemap-external">外部リンク</h2>
      <ul class="c-sitemap-list">
        <li><a href="https://x.com/xxxx" target="_blank" rel="noopener">X</a></li>
        <li><a href="https://github.com/xxxx" target="_blank" rel="noopener">GitHub</a></li>
      </ul>
    </nav>
  </div>
</section>

<?php
get_footer();
?>

