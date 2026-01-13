<?php
get_header();

?>

  <div class="l-wrap">

  <div class="l-content p-page-404">
    <h2>404 Not Found</h2>
    <p class="below">（ページが見つかりませんでした）</p>
    <div class="p-page-404__content">
      <p>指定された以下のページは存在しないか、または移動した可能性があります。</p>
      <p class="text-errorUrl">URL ：<span><?= get_pagenum_link(); ?></span></p>
      <p class="text-guide">お探しのコンテンツに該当するキーワードを入力して下さい。</p>
      <?= get_search_form(); ?>
      <p class="p-page-404-link"><a href="<?= home_url(); ?>" class="c-btn-more">トップページへ</a></p>
    </div>
  </div>

<?php
get_footer();
?>