<?php
/*
Template Name: css一覧
*/
get_header();

/**
 * =========================================================
 * 設定
 * =========================================================
 */
$post_type    = 'css';
$tax_category = 'css-category';
$tax_tag      = 'css-tag';
$ppp          = 24;

/**
 * =========================================================
 * URLクエリ取得（[]あり/なし両対応 + カンマ区切りも吸収）
 * 例：
 *  /css/?css-category[]=visual-effects
 *  /css/?css-category=visual-effects
 *  /css/?css-tag=button,card
 * =========================================================
 */
function kumonosu_get_query_values($key) {
  $vals = [];

  // ?key[]=a&key[]=b
  if (isset($_GET[$key])) {
    $v = $_GET[$key];

    // ?key=a,b も来る可能性を吸収
    if (is_string($v)) {
      $vals = array_merge($vals, explode(',', $v));
    } elseif (is_array($v)) {
      $vals = array_merge($vals, $v);
    }
  }

  // 念のため：?key[]=... の形を明示的に拾いたい場合にも対応
  // （PHPでは通常 $_GET[$key] に統合されますが、環境差対策）
  $bracket_key = $key . '[]';
  if (isset($_GET[$bracket_key])) {
    $v = $_GET[$bracket_key];
    if (is_string($v)) {
      $vals = array_merge($vals, explode(',', $v));
    } elseif (is_array($v)) {
      $vals = array_merge($vals, $v);
    }
  }

  // sanitize + 空削除 + 重複削除
  $vals = array_map('sanitize_text_field', $vals);
  $vals = array_values(array_filter($vals));
  $vals = array_values(array_unique($vals));

  return $vals;
}

$cats = kumonosu_get_query_values($tax_category);
$tags = kumonosu_get_query_values($tax_tag);
$keyword = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

/**
 * =========================================================
 * tax_query（カテゴリ×タグはAND、各tax内はIN）
 * =========================================================
 */
/**
 * =========================================================
 * tax_query（カテゴリ or タグ）
 * - 各tax内は IN（複数選択はOR扱い）
 * - tax間は OR
 * =========================================================
 */
$tax_query = ['relation' => 'OR'];

if (!empty($cats)) {
  $tax_query[] = [
    'taxonomy' => $tax_category,
    'field'    => 'slug',
    'terms'    => $cats,
    'operator' => 'IN',
  ];
}

if (!empty($tags)) {
  $tax_query[] = [
    'taxonomy' => $tax_tag,
    'field'    => 'slug',
    'terms'    => $tags,
    'operator' => 'IN',
  ];
}

/**
 * $args に入れるときは「条件が1個以上ある時だけ」
 */
if (count($tax_query) > 1) {
  $args['tax_query'] = $tax_query;
}

/**
 * =========================================================
 * 初期表示：paged
 * - 今は「MORE」で無限ロードなので通常は1でOK
 * - ただしURLに paged が付いたら拾って表示できるようにする
 * =========================================================
 */
$paged = get_query_var('paged');
if (!$paged) {
  $paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
}
$paged = max(1, (int) $paged);

$args = [
  'post_type'      => $post_type,
  'post_status'    => 'publish',
  'posts_per_page' => $ppp,
  'paged'          => $paged,
  'orderby'        => 'date',
  'order'          => 'DESC',
  's'              => $keyword, // ★ここを追加！
];

if (!empty($tax_query)) {
  $args['tax_query'] = $tax_query;
}

$q = new WP_Query($args);

/**
 * =========================================================
 * 初期表示：新着順
 * =========================================================
 */
$args = [
  'post_type'      => $post_type,
  'post_status'    => 'publish',
  'posts_per_page' => $ppp,
  'paged'          => $paged,
  'orderby'        => 'date',
  'order'          => 'DESC',
];

if (count($tax_query) > 1) {
  $args['tax_query'] = $tax_query;
}

$q = new WP_Query($args);
$max_pages = (int) ($q->max_num_pages ?? 1);

/**
 * =========================================================
 * AJAX用
 * =========================================================
 */
$ajax_url = admin_url('admin-ajax.php');
$nonce    = wp_create_nonce('kumonosu_css_filter_nonce');
?>

<section class="l-section l-section--new">
  <h1 class="c-section-title">
    <span class="c-section-title-main" data-text="CSS">CSS</span>
    <span class="c-section-title-sub" data-text="CSS一覧">CSS一覧</span>
  </h1>

  <div class="c-sort"
    data-kumonosu-list
    data-ajax-url="<?php echo esc_url($ajax_url); ?>"
    data-nonce="<?php echo esc_attr($nonce); ?>"
    data-post-type="<?php echo esc_attr($post_type); ?>"
    data-sort="new"
    data-paged="<?php echo esc_attr($paged); ?>"
    data-ppp="<?php echo esc_attr($ppp); ?>"
    data-max="<?php echo esc_attr($max_pages); ?>"
    data-filter-cats='<?php echo esc_attr( wp_json_encode($cats, JSON_UNESCAPED_UNICODE) ); ?>'
    data-filter-tags='<?php echo esc_attr( wp_json_encode($tags, JSON_UNESCAPED_UNICODE) ); ?>'
  >

    <div class="c-section-option">
      <div class="c-active-filters js-active-filters" hidden>
        <ul class="c-active-filters__list js-active-filters-list"></ul>
        <div class="c-active-filters__head">
          <button type="button" class="c-active-filters__clear js-active-filters-clear">すべてクリア</button>
        </div>
      </div>

        <select class="c-sort-select" data-kumonosu-sort-select aria-label="並び替え順を選択">
          <option value="new" selected>新着順</option>
          <option value="popular">人気順</option>
          <option value="old">古い順</option>
        </select>
    </div>

    <div class="l-card-grid" data-kumonosu-grid>
      <?php if ($q->have_posts()) : ?>
        <?php while ($q->have_posts()) : $q->the_post(); ?>
          <?php get_template_part('templates/parts/c-card', 'css'); ?>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
      <?php else : ?>
        <p class="c-empty">該当するCSSがありません。</p>
      <?php endif; ?>
    </div>

    <?php if ($max_pages > $paged) : ?>
      <button type="button" class="gradient-btn" data-kumonosu-more>
        <span class="gradient-btn__text">MORE</span>
        <span class="gradient-btn__icon">»</span>
      </button>
    <?php endif; ?>

  </div>
</section>

<?php get_footer(); ?>
