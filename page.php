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
  <div class="c-section-title">
    <span class="c-section-title-main" aria-hidden="true" data-text="<?php
      global $post;
      $slug = $post->post_name;

      // ucfirst() で先頭だけ大文字にする
      echo ucfirst( $slug );
    ?>" aria-hidden="true">
    <?php
      global $post;
      $slug = $post->post_name;

      // ucfirst() で先頭だけ大文字にする
      echo ucfirst( $slug );
    ?>
    </span>
    <h1 class="c-section-title-sub" data-text="<?php the_title(); ?>"><?php the_title(); ?></h1>
  </div>
  <div class="c-section-content">
    <?php the_content();?>
  </div>
</section>

<?php
get_footer();
?>

