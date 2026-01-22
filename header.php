<!DOCTYPE html>
<html lang="ja">
	<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">

		<!--meta-->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<?php if ( is_front_page() || is_home() ) : ?>
		<title><?php bloginfo('name'); ?></title>
		<?php elseif ( is_page() || is_singular('css') || is_single() ) : ?>
		<title><?php the_title(); ?>【KUMONOSU】</title>
		<?php elseif ( is_post_type_archive('css') ) : ?>
		<title>CSS一覧【KUMONOSU】</title>
		<?php elseif ( is_post_type_archive('blog') ) : ?>
		<title>特集&まとめ【KUMONOSU】</title>
		<?php elseif ( is_404() ) : ?>
		<title>404【KUMONOSU】</title>
		<?php endif; ?>
		<?php if ( is_front_page() || is_home() ) : ?>
		<meta name="description" content="<?php bloginfo('description'); ?>" />
		<?php elseif ( is_page() || is_singular('css') || is_single() ) : ?>
		<meta name="description" content="<?php the_kumonosu_description(); ?>" />
		<?php elseif ( is_post_type_archive('css') ) : ?>
		<meta name="description" content="CSSやJavaScriptを使ったアニメーションを一覧で紹介しています。動きやパーツ別にアイデア探しに活用できます。" />
		<?php elseif ( is_post_type_archive('blog') ) : ?>
		<meta name="description" content="CSS・JavaScriptについての特集やまとめの記事を一覧で紹介しています。アイデア探しや参考に活用できます。" />
		<?php endif; ?>
		<meta name="referrer" content="unsafe-url">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
		<link rel="canonical" href="<?= (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"]. $_SERVER["REQUEST_URI"]; ?>" />

		<!-- OGP設定 -->
		<?php get_template_part( 'templates/parts/c-meta_ogp' ); ?>

		<!--favicon ico-->
		<link rel="apple-touch-icon" sizes="180x180" href="<?= esc_url(get_template_directory_uri()); ?>/assets/img/favicon/apple-touch-icon.png">
		<link rel="icon" href="<?= esc_url(get_template_directory_uri()); ?>/assets/img/favicon/favicon.ico">
		<link rel="icon" sizes="32x32" type="image/png" href="<?= esc_url(get_template_directory_uri()); ?>/assets/img/favicon/favicon-32x32.png">

		<!--css-->
		<link href="<?= esc_url( get_template_directory_uri() ); ?>/assets/css/dist/common.css" media="all" rel="stylesheet">
		<?php print_dynamic_page_css(); //page固有CSSを読み込み（func-dynamic-css.php）?>

		<!--構造化データ-->
		<?php get_template_part( 'templates/parts/c-meta_schema' ); ?>

		<!-- wp_head -->
		<?php wp_head(); ?>
		<!-- End wp_head -->
	</head>

	<?php
	$page_type = '';

	if ( is_front_page() ) {
	$page_type = 'front';
	} elseif ( is_singular('css') ) {
	$page_type = 'single-css';
	} elseif ( is_singular('blog') ) {
	$page_type = 'single-blog';
	} elseif ( is_post_type_archive('css') ) {
	$page_type = 'archive-css';
	} elseif ( is_post_type_archive('blog') ) {
	$page_type = 'archive-blog';
	}
	?>

	<body <?php body_class();?><?php if( is_404() ): ?>class="bg-black text-white"<?php endif; ?> data-page="<?php echo esc_attr($page_type); ?>">
		<?php
		// PV計測用：css / blog の single 記事ページのみ
		if ( is_singular(['css','blog']) ) : ?>
		<div data-kumonosu-post-id="<?php echo (int) get_the_ID(); ?>"></div>
		<?php endif; ?>

		<!-- Loader Container -->
		<div id="loader-container">
			<canvas id="c"></canvas>
		</div>
		<div id="js-loading">
		<header id="header" class="l-header">
			<div class="l-navi -closed js-navi">
				<div class="l-navi-overlay js-overlay"></div>
				<div class="l-navi-bg js-navi-bg">
					<div class="l-navi-area js-navi-area">
						<!-- Left -->
						<div class="l-navi-left">
							<a class="c-logo" href="<?php echo esc_url(home_url('/')); ?>">
								<img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/logo.svg" alt="<?php bloginfo(); ?>" loading="lazy" width="130" height="auto">
							</a>
						</div>
						<!-- Right -->
						<div class="l-navi-right">
							<!-- PC Nav -->
							<nav class="c-nav c-pc-w600">
								<ul>
									<li><a href="/">Home</a></li>
									<li><a href="/css/">CSS</a></li>
									<li><a href="/blog/">Blog</a></li>
									<li><a href="/about/">About</a></li>
									<li><a href="/contact/">Contact</a></li>
								</ul>
							</nav>
							<!-- Search Button -->
							<button class="c-navi-searchbtn js-searchbtn">
								<div class="c-icon">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="search-svg">
										<g id="レイヤー_2" data-name="レイヤー 2">
											<g id="Rectangle">
												<rect fill="none" width="48" height="48"/>
											</g>
											<g id="icon_data">
												<!-- 1. 下地になるグレーの線 -->
												<g class="base-line">
													<path d="M25.66,35.52A14.24,14.24,0,0,1,22,36a14,14,0,1,1,9.9-4.1"/>
													<line x1="40" y1="40" x2="31.9" y2="31.9"/>
												</g>
												<!-- 2. 上に乗るアニメーション用の線 -->
												<g class="trace-line">
													<path d="M25.66,35.52A14.24,14.24,0,0,1,22,36a14,14,0,1,1,9.9-4.1"/>
													<line x1="40" y1="40" x2="31.9" y2="31.9"/>
												</g>
											</g>
										</g>
									</svg>
								</div>
								<div class="c-icon-close" aria-hidden="true"><i></i><i></i><i></i></div>
							</button>
							<!-- Hamburger Button -->
							<button class="c-navi-menubtn c-sp-w600 js-menubtn">
								<div class="c-icon"><i></i><i></i><i></i></div>
							</button>
						</div>
					</div>
					<!-- Hamburger Menu -->
					<div class="l-navi-menu js-navi-menu">
						<div class="l-footer-block">
							<nav class="c-nav c-sp-w600">
								<ul>
									<li><a href="/">Home</a></li>
									<li><a href="/css/">CSS</a></li>
									<li><a href="/blog/">BLOG</a></li>
									<li><a href="/contact/">CONTACT</a></li>
									<li><a href="/about/">ABOUT</a></li>
									<li><a href="/privacy-policy/">PRIVACY POLICY</a></li>
									<li><a href="/sitemap/">SITEMAP</a></li>
								</ul>
							</nav>
						</div>
					</div>
					<!-- Search Menu -->
					<?php
					$tax_category = 'css-category';
					$tax_tag      = 'css-tag';
					$archive_url  = get_post_type_archive_link('css');

					// 現在の選択状態（配列）
					$current_cats = isset($_GET[$tax_category]) ? (array) $_GET[$tax_category] : [];
					$current_tags = isset($_GET[$tax_tag])      ? (array) $_GET[$tax_tag]      : [];

					$current_cats = array_values(array_filter(array_map('sanitize_text_field', $current_cats)));
					$current_tags = array_values(array_filter(array_map('sanitize_text_field', $current_tags)));

					$cat_terms = get_terms([
					'taxonomy'   => $tax_category,
					'hide_empty' => false,
					'orderby'    => 'id',
					'order'      => 'ASC',
					]);

					$tag_terms = get_terms([
					'taxonomy'   => $tax_tag,
					'hide_empty' => false,
					'orderby'    => 'id',
					'order'      => 'ASC',
					]);

					$nonce = wp_create_nonce('kumonosu_css_filter_nonce');
					?>

					<div class="l-navi-search js-navi-search">

						<form class="c-filter-form js-css-filter-form" action="<?php echo esc_url($archive_url); ?>" method="get"
								data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
								data-nonce="<?php echo esc_attr($nonce); ?>">

							<div class="l-footer-block l-footer-main">
								<div class="l-footer-block-wrap">
									<?php get_template_part('searchform'); ?>

									<!-- Category -->
									<div class="c-search-item">
										<span class="c-search-title">CSS Category</span>
										<ul class="c-filter-list c-category-content">
										<?php if (!empty($cat_terms) && !is_wp_error($cat_terms)) : ?>
											<?php foreach ($cat_terms as $term) :
											$checked = in_array($term->slug, $current_cats, true);
											?>
											<li>
												<label class="c-filter-check">
												<input
													type="checkbox"
													name="<?php echo esc_attr($tax_category); ?>[]"
													value="<?php echo esc_attr($term->slug); ?>"
													<?php checked($checked); ?>
												>
												<span><?php echo esc_html($term->name); ?></span>
												</label>
											</li>
											<?php endforeach; ?>
										<?php endif; ?>
										</ul>
									</div>

									<!-- Parts -->
									<div class="c-search-item">
										<span class="c-search-title">CSS Parts</span>
										<ul class="c-filter-list c-parts-content">
										<?php if (!empty($tag_terms) && !is_wp_error($tag_terms)) : ?>
											<?php foreach ($tag_terms as $term) :
											$checked = in_array($term->slug, $current_tags, true);
											?>
											<li class="c-filter-listitem">
												<label class="c-filter-check">
												<input
													type="checkbox"
													name="<?php echo esc_attr($tax_tag); ?>[]"
													value="<?php echo esc_attr($term->slug); ?>"
													<?php checked($checked); ?>
												>
												<span>#<?php echo esc_html($term->name); ?></span>
												</label>
											</li>
											<?php endforeach; ?>
										<?php endif; ?>
										</ul>
									</div>
								</div>

								<!-- 上部：件数 + ボタン -->
								<div class="c-filter-actions">
									<div class="c-filter-count">
										<span class="c-filter-count-label">該当</span>
										<span class="c-filter-count-num js-filter-count">-</span>
										<span class="c-filter-count-unit">件</span>
									</div>

									<button type="submit" class="gradient-btn c-filter-submit js-filter-submit" disabled>
										<span class="gradient-btn__text">この条件で探す</span>
										<span class="gradient-btn__icon">»</span>
									</button>
									<button type="button" class="c-filter-clear js-filter-clear">すべての選択を解除</button>
								</div>
							</div>
						</form>

					</div>

				</div>
			</div>
		</header>

		<main class="l-main">
			<?php if ( is_home() || is_front_page() ) : ?>
			<div id="canvas-container" class="l-hero-canvas">
				<div id="canvas-wrapper">
					<canvas id="liquidCanvas"></canvas>
				</div>
			</div>
			<?php endif; ?>
			<?php if ( !is_home() && !is_front_page() && !is_404() ) : ?>
			<div id="canvas-wrapper" class="l-hero-canvas">
				<canvas id="liquidCanvas"></canvas>
			</div>
			<?php endif; ?>