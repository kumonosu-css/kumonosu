<?php

$addClass_LayoutSide = get_query_var('addClass_LayoutSide');
?>
<aside class="l-side <?= $addClass_LayoutSide; ?>">
  <dl class="l-side__item">
    <dt class="c-sec__title">
      <div class="txt">人気記事ランキング</div>
    </dt>
    <!--c-tabs-->
    <dd class="c-tabs">
      <!--1-->
      <input id="day" type="radio" name="tab_item" checked="checked">
      <label class="tab_item" for="day">本日</label>
      <!--2-->
      <input id="week" type="radio" name="tab_item">
      <label class="tab_item" for="week">週間</label>
      <!--3-->
      <input id="month" type="radio" name="tab_item">
      <label class="tab_item" for="month">月間</label>
      <!--content-1-->
      <div class="tab_content" id="day_content">
        <?php set_query_var('rank', 'pv_count'); ?>
        <?php get_template_part( 'components/article', 'ranking' ); ?>
      </div>
      <!--content-2-->
      <div class="tab_content" id="week_content">
        <?php set_query_var('rank', 'pv_count_week'); ?>
        <?php get_template_part( 'components/article', 'ranking' ); ?>
      </div>
      <!--content-3-->
      <div class="tab_content" id="month_content">
        <?php set_query_var('rank', 'pv_count_month'); ?>
        <?php get_template_part( 'components/article', 'ranking' ); ?>
      </div>
    </dd>
  </dl>
  <dl class="l-side__item">
    <dt class="c-sec__title">
      <div class="txt">話題のタグ</div>
    </dt>
     <?php /*
    <!--<ul class="tagSide">
        $args = array(
        'orderby' => 'count',  //記事件数で並び替える
        'order'   => 'DESC',   //大きい順
        'number' => 15,
        'format' => 'array',
      );
      $ar_tags = wp_tag_cloud($args);
      foreach ($ar_tags as $tag) {
        echo "<li>" . "{$tag}" . "</li>";
      };
    </ul>-->
    */?>
    <dd>
      <ul class="tagSide">
          <?php
          $args = array(
              'orderby' => 'name',
              'number' => 12,
          );
          $tags = get_tags( $args );
          foreach ( $tags as $tag ) :
              $tag_link = get_tag_link($tag->term_id);
              $str = 'tag/';
              $tag_link = str_replace($str, '', $tag_link);
              ?>
            <li>
              <a href="<?= $tag_link ; ?>" class="tag-cloud-link">
                <span><?= $tag->name ; ?></span>
              </a>
            </li>
          <?php endforeach; wp_reset_postdata(); ?>
          <li>
            <a href="https://cocoa-job.jp/" class="tag-cloud-link" target="_blank">
              <span>風俗求人・高収入バイト検索</span>
            </a>
          </li>
      </ul>
    </dd>
  </dl>
  <!--bannerSide-->
  <?php if(is_front_page() && is_home()): ?>
    <?php set_query_var('addClass_BnrSide', ''); ?>
  <?php else: ?>
    <?php set_query_var('addClass_BnrSide', 'is-fixed-flag'); ?>
  <?php endif; ?>
  <?php get_template_part( 'components/bnr', 'side' ); ?>
</aside>