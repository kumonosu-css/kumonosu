<?php
/**
 * Template Name: front-page
 *
 * @package WordPress
 * @subpackage  Original Theme
 */
get_header();

//テンプレート読み込みのサンプル用に
//get_template_part( 'components/article', 'pickup' );
?>

<!-- HERO CONTENT -->
<section class="l-hero">
	<h1 class="c-hero-title"><?php echo esc_html( get_bloginfo('name') ); ?></h1>
	<div class="c-hero-copy">
		<p class="c-hero-copy-main">Friendly&nbsp;<br class="c-sp-w480">Neighborhood</p>
		<p class="c-hero-copy-sub">Cascading&nbsp;Style&nbsp;Sheets&nbsp;&&nbsp;Animations</p>
	</div>
	<p class="c-hero-lead subtitle-jp">モーションやレイアウトテクニック、グラデーション、インタラクティブなアニメーションなど<br class="c-pc-w600">コピペするだけで使えるCSS / JSのコードを分かりやすく紹介します。</p>
</section>

<?php
// 本日PV上位2件（css固定）
$top_css_today = function_exists('kumonosu_get_top_css_today')
  ? kumonosu_get_top_css_today(2)
  : [];
?>

<!-- POPULAR CONTENT -->
<section class="l-section l-section--popular">
  <h2 class="c-section-title">
    <span class="c-section-title-main" data-text="Popular&nbsp;CSS">Popular&nbsp;CSS</span>
    <span class="c-section-title-sub" data-text="よく見られているCSS">よく見られているCSS</span>
  </h2>

  <div class="l-card-grid">
    <?php if ( ! empty($top_css_today) ) : ?>

      <?php
      // 本日PV上位の投稿ID（順位順を保持）
      $ids = array_map(function($p) {
        return (int) $p->ID;
      }, $top_css_today);

      $args = array(
        'post_type'      => 'css',
        'posts_per_page' => 2,
        'post__in'       => $ids,
        'orderby'        => 'post__in', // ← PV順位順
      );

      $css_query = new WP_Query($args);

      if ($css_query->have_posts()) :
        while ($css_query->have_posts()) :
          $css_query->the_post();
          get_template_part('templates/parts/c-card');
        endwhile;
        wp_reset_postdata();
      endif;
      ?>

    <?php else : ?>
      <?php
      // フォールバック：まだ本日のPVが無い場合は新着2件
      $args = array(
        'post_type'      => 'css',
        'posts_per_page' => 2,
        'orderby'        => 'date',
        'order'          => 'DESC',
      );

      $css_query = new WP_Query($args);

      if ($css_query->have_posts()) :
        while ($css_query->have_posts()) :
          $css_query->the_post();
          get_template_part('templates/parts/c-card');
        endwhile;
        wp_reset_postdata();
      endif;
      ?>
    <?php endif; ?>
  </div>
</section>

<!-- NEW CONTENT -->
<section class="l-section l-section--new">
	<h2 class="c-section-title">
    <span class="c-section-title-main" data-text="New&nbsp;CSS">New&nbsp;CSS</span>
    <span class="c-section-title-sub" data-text="最新のCSS">最新のCSS</span>
  </h2>
	<div class="l-card-grid">
        <?php
        $args = array(
            'post_type'      => 'css',
            'posts_per_page' => 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $css_query = new WP_Query($args);

        if ($css_query->have_posts()) :
            while ($css_query->have_posts()) :
                $css_query->the_post();
                ?>
                <?php get_template_part('templates/parts/c-card', 'css'); ?>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
	</div>
  <a href="/css/" class="gradient-btn">
      <span class="gradient-btn__text">CSS一覧</span>
      <span class="gradient-btn__icon">»</span>
  </a>
</section>

<!-- MARQUEE CONTENT -->
<section class="l-section l-section--marquee">
    <div class="c-marquee">
        <!-- 文字を動かすトラック -->
        <div class="c-marquee-track">
            <!-- 表示用 -->
            <p class="c-marquee-text">FRIENDLY NEIGHBORHOOD FRIENDLY NEIGHBORHOOD FRIENDLY NEIG</p>
            <!-- ループ用（スクリーンリーダー非表示） -->
            <p class="c-marquee-text" aria-hidden="true">FRIENDLY NEIGHBORHOOD FRIENDLY NEIGHBORHOOD FRIENDLY NEIG</p>
        </div>
    </div>

    <!-- 逆方向（右へ流れる） -->
    <div class="c-marquee c-marquee--reverse">
        <div class="c-marquee-track">
            <p class="c-marquee-text">With great power comes great responsibility With great pow</p>
            <p class="c-marquee-text" aria-hidden="true">With great power comes great responsibility With great pow</p>
        </div>
    </div>
</section>

<!-- SEARCH CONTENT -->
<section class="l-section l-section--search">
    <div class="l-search-block">
        <div class="l-search-visual l-search-visual--glow-red">
            <span class="glow"></span>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/front/serch-category.jpg" alt="Search Category Visual" loading="lazy" width="818" height="472">
        </div>
        <div class="c-search-content">
            <h2 class="c-section-title">
                <span class="c-section-title-main" data-text="Category&nbsp;CSS">Category&nbsp;CSS</span>
                <span class="c-section-title-sub" data-text="CCSをカテゴリから探す">CCSをカテゴリから探す</span>
            </h2>
            <p class="c-search-text"> KUMONOSUではCSSアニメーションやスクロールエフェクト、ホバー演出などをカテゴリ別に分類しています。目的に合わせて必要なCSS/JSをすぐに探せます。</p>
            <ul class="c-search-grid">
                <?php
                $taxonomy = 'css-category';
                $terms = get_terms([
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'orderby' => 'id',
                    'order' => 'ASC',
                ]);

                if ( ! empty($terms) && ! is_wp_error($terms) ) :
                $archive_url = get_post_type_archive_link('css');

                foreach ($terms as $term) :
                $link = add_query_arg([$taxonomy => $term->slug], $archive_url);
                ?>
                <li><a href="<?php echo esc_url($link); ?>" class="c-search-link"><?php echo esc_html($term->name); ?></a></li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
    <div class="l-search-block l-search-block--reverse">
        <div class="l-search-visual l-search-visual--glow-blue">
            <span class="glow"></span>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/front/serch-tag.jpg" alt="Search Parts Visual" loading="lazy" width="818" height="472">
        </div>
        <div class="c-search-content">
            <h2 class="c-section-title">
                <span class="c-section-title-main" data-text="Parts&nbsp;CSS">Parts&nbsp;CSS</span>
                <span class="c-section-title-sub" data-text="CCSをパーツから探す">CCSをパーツから探す</span>
            </h2>
            <p class="c-search-text">パーツ検索では、ボタン、カード、ナビゲーション、メインビジュアルなど、実際に使うUIパーツごとにCSS/JSのデモを探せます。</p>
            <ul class="c-search-grid">
                <?php
                $taxonomy = 'css-tag';
                $terms = get_terms([
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => true,      // 投稿があるものだけ
                    'orderby'    => 'count',   // 件数順
                    'order'      => 'DESC',    // 多い順
                    'number'     => 6,         // 上位6件
                ]);

                if ( ! empty($terms) && ! is_wp_error($terms) ) :
                $archive_url = get_post_type_archive_link('css');

                foreach ($terms as $term) :
                $link = add_query_arg([$taxonomy => $term->slug], $archive_url);
                ?>
                <li><a href="<?php echo esc_url($link); ?>" class="c-search-link"><?php echo esc_html($term->name); ?></a></li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</section>

<?php
$movie_query = new WP_Query([
  'post_type'      => 'css',
  'posts_per_page' => 6,
  'post_status'    => 'publish',
  'orderby'        => 'date',
  'order'          => 'DESC',
  'meta_query'     => [
    [
      'key'     => '_kumonosu_youtube_iframe',
      'value'   => '',
      'compare' => '!=',
    ],
  ],
]);

if ( $movie_query->have_posts() ) :
?>
<!-- MOVIE CONTENT -->
<section class="l-section l-section--movie">
  <h2 class="c-section-title">
    <span class="c-section-title-main" data-text="Shot&nbsp;Movie">Shot&nbsp;Movie</span>
    <span class="c-section-title-sub" data-text="ショート動画">ショート動画</span>
  </h2>

  <div class="l-movie-track">

    <!-- 表示用 -->
    <div class="l-movie-list">
      <?php while ( $movie_query->have_posts() ) :
        $movie_query->the_post();

        $iframe_raw = trim(
          (string) get_post_meta(get_the_ID(), '_kumonosu_youtube_iframe', true)
        );

        if ($iframe_raw === '') continue;
      ?>
        <div class="c-movie-item">
          <a href="<?php the_permalink(); ?>" class="c-movie-link">
            <?php echo $iframe_raw; ?>
          </a>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- ループ用（スクリーンリーダー非表示） -->
    <div class="l-movie-list" aria-hidden="true">
      <?php
      $movie_query->rewind_posts();
      while ( $movie_query->have_posts() ) :
        $movie_query->the_post();

        $iframe_raw = trim(
          (string) get_post_meta(get_the_ID(), '_kumonosu_youtube_iframe', true)
        );

        if ($iframe_raw === '') continue;
      ?>
        <div class="c-movie-item">
          <a href="<?php the_permalink(); ?>" class="c-movie-link">
            <?php echo $iframe_raw; ?>
          </a>
        </div>
      <?php endwhile; ?>
    </div>

  </div>
</section>
<?php
wp_reset_postdata();
endif;
?>

<!-- BLOG CONTENT -->
<section class="l-section l-section--blog">
	<h2 class="c-section-title">
        <span class="c-section-title-main" data-text="Blog">Blog</span>
        <span class="c-section-title-sub" data-text="特集&まとめ">特集&まとめ</span>
    </h2>
	<div class="l-card-grid">
        <?php
        $args = array(
            'post_type'      => 'blog',
            'posts_per_page' => 3,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $css_query = new WP_Query($args);

        if ($css_query->have_posts()) :
            while ($css_query->have_posts()) :
                $css_query->the_post();
                ?>
                <?php get_template_part('templates/parts/c-blogcard', 'css'); ?>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
	</div>
  <a href="/blog/" class="gradient-btn">
      <span class="gradient-btn__text">特集&まとめ</span>
      <span class="gradient-btn__icon">»</span>
  </a>
</section>

<?php
// 今日 / 累計
$pv_today = function_exists('kumonosu_get_site_pv_today') ? kumonosu_get_site_pv_today() : 0;
$pv_total = function_exists('kumonosu_get_site_pv_total') ? kumonosu_get_site_pv_total() : 0;
?>
<!-- REASONS CONTENT -->
<section class="l-section l-section--reasons">
    <div class="l-reasons-inner">
        <div class="l-reasons-wrap">
            <h2 class="c-section-title">
                <span class="c-section-title-main" data-text="Why&nbsp;KUMONOSU">Why<br class="c-sp-w600"><span class="c-pc-w600">&nbsp;</span>KUMONOSU</span>
                <span class="c-section-title-sub" data-text="なぜKUMONOSU？">なぜKUMONOSU？</span>
            </h2>

            <div class="c-section-text-box">
                <p class="c-section-text">KUMONOSUは<span class="c-sp-w600">、</span><br class="c-pc-w600">CSSやJavaScriptを使ったアニメーションやレイアウトデザインを<span class="c-pc-w1000">、</span><br class="c-sp-w1000 c-pc-w600">実際に試してみたサイトです。<br>
                <p class="c-section-text">「どんな見た目・どんな動きになるのか」を実際に確認でき、<span class="c-pc-w1000">、</span><br class="c-sp-w1000 c-pc-w600">デモとコード付きのためコピペ可能です。</p>
                <p class="c-section-text">海外の最新トレンドやモダンなCSSを中心に<span class="c-pc-w1000">、</span><br class="c-sp-w1000 c-pc-w600">Webデザイナーやフロントエンド制作者の初心者からベテランまで<span class="c-sp-w600">、</span><br class="c-pc-w600">制作の中で使えそうな表現を厳選して掲載しています。</p>
                <p class="c-section-text">また、カテゴリやパーツごとに整理しているため<span class="c-pc-w1000">、</span><br class="c-sp-w1000 c-pc-w600">アイデア探しやデザインの参考としても使いやすく<span class="c-sp-w1000">、</span><br class="c-pc-w1000 c-pc-w600">CSS・JSを探したいときに便利なサイトです。</p>
            </div>

            <!-- スクロールで動く部分（カードコンテナ） -->
            <div class="c-section-metrics">
                <!-- Card 1 -->
                <div class="c-metric-card-wrapper card-0">
                    <div class="c-section-metric">
                        <div class="c-count-title">
                            <span class="c-section-count-main">Articles</span>
                            <span class="c-section-count-sub">記事の数</span>
                        </div>
                        <div class="c-section-count-value">
                            <?php
                            $count = wp_count_posts('css')->publish;
                            echo $count;
                            ?>
                        </div>
                    </div>
                    <div class="c-section-count-text-box">
                        <p class="c-section-count-text">掲載している記事数は、KUMONOSUが扱う表現の幅を示す指標であり<span class="c-sp-w600">、</span><br class="c-pc-w600">
                        デザインの参考探しにも実装のヒント探しにも役立ちます。</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="c-metric-card-wrapper card-2">
                    <div class="c-section-metric">
                        <div class="c-count-title">
                            <span class="c-section-count-main">Today’s Access</span>
                            <span class="c-section-count-sub">本日のアクセス数</span>
                        </div>
                        <div class="c-section-count-value"><?php echo number_format($pv_today); ?></div>
                    </div>
                    <div class="c-section-count-text-box">
                        <p class="c-section-count-text">KUMONOSUの本日のアクセス数は、リアルタイムの指標です。<span class="c-sp-w600">、</span><br class="c-pc-w600">
                        デザイナーやフロントエジニアの新しいテクニックへの関心度がわかります。</p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="c-metric-card-wrapper card-3">
                    <div class="c-section-metric">
                        <div class="c-count-title">
                            <span class="c-section-count-main">Total Access</span>
                            <span class="c-section-count-sub">総アクセス数</span>
                        </div>
                        <div class="c-section-count-value"><?php echo number_format($pv_total); ?></div>
                    </div>
                    <div class="c-section-count-text-box">
                        <p class="c-section-count-text">KUMONOSUの総アクセス数は、信頼性の指標です。<span class="c-sp-w600">、</span><br class="c-pc-w600">
                        これまでの全てのアクセス数は、デザイナーやフロントエンジニアから継続的に支持されている証拠にもなります。</p>
                    </div>
                </div>
            </div>
        </div>
        <a href="/about/" class="gradient-btn">
            <span class="gradient-btn__text">ABOUT</span>
            <span class="gradient-btn__icon">»</span>
        </a>
    </div>
</section>

<?php
get_footer();
?>