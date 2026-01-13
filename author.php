<?php
/*
Template Name: Archives
*/

get_header();

$paged = get_query_var('page');

$userId = get_query_var('author');
$user = get_userdata($userId);
?>

  <?php
  if ( get_query_var('paged') ) {
    $paged = get_query_var('paged');
  } elseif ( get_query_var('page') ) {
    $paged = get_query_var('page');
  } else {
    $paged = 1;
  }
  $args = array(
    'author' => $userId,
    'posts_per_page' => 20,
    'paged' => $paged,
    'orderby' => 'post_date',
    'order' => 'DESC',
    'post_type' => 'post',
    'post_status' => 'publish',
    /*'post_type' => 'news',*/
  );
  $wp_query = new WP_Query($args);
  $counter = 0;
  if ( $wp_query->have_posts() ) :
  while ( $wp_query->have_posts() ) :
  $wp_query->the_post();
  $image_id = get_post_thumbnail_id();
  $image_url = wp_get_attachment_image_src ($image_id, true);
  $counter++;
  ?>

    <article>

    </article>

  <?php endwhile; endif; ?>

<?php
get_footer();
