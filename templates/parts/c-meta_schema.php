<?php
// ==========================================================
// 1. 変数定義
// ==========================================================
$site_name = get_bloginfo('name');
$site_url  = home_url();
$site_url_slash = trailingslashit($site_url);
$logo_url  = $site_url_slash . 'wp-content/themes/kumonosu/assets/img/favicon/logo.png';

$schema_graph = [];

// ★ 全ページ共通：WebSite（1回だけ）
$schema_graph[] = [
  '@type' => 'WebSite',
  '@id'   => $site_url_slash . '#website',
  'name'  => 'KUMONOSU',
  'alternateName' => 'コピペで使えるCSS・JSのアニメーションデザイン',
  'url'   => $site_url_slash,
    'potentialAction' => [
    '@type' => 'SearchAction',
    'target' => [
        '@type' => 'EntryPoint',
        'urlTemplate' => $site_url_slash . '?s={search_term_string}',
    ],
    'query-input' => 'required name=search_term_string',
    ],
];

// ★ 全ページ共通：Organization（1回だけ）
$schema_graph[] = [
  '@type' => 'Organization',
  '@id'   => $site_url_slash . '#organization',
  'name'  => 'KUMONOSU',
  'url'   => $site_url_slash,
  'logo'  => [
    '@type' => 'ImageObject',
    'url' => $logo_url
  ]
];

// ==========================================================
// 2. パンくずリスト生成 (配列として取得)
// ==========================================================
function get_breadcrumb_array() {
  $items = [];
  $position = 1;

  $items[] = [
    '@type' => 'ListItem',
    'position' => $position++,
    'name' => 'トップ',
    'item' => trailingslashit(home_url()),
  ];

  if (is_single()) {
    $post_type = get_post_type();

    if ($post_type === 'css' || $post_type === 'blog') {
      $items[] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => get_post_type_object($post_type)->label,
        'item' => trailingslashit(get_post_type_archive_link($post_type)),
      ];
    } else {
      $categories = get_the_category();
      if (!empty($categories)) {
        $cat = $categories[0];
        $items[] = [
          '@type' => 'ListItem',
          'position' => $position++,
          'name' => $cat->name,
          'item' => trailingslashit(get_category_link($cat->term_id)),
        ];
      }
    }

    $items[] = [
      '@type' => 'ListItem',
      'position' => $position++,
      'name' => get_the_title(),
      'item' => get_permalink(),
    ];
  }
  elseif (is_post_type_archive(['css', 'blog'])) {
    $post_type = get_query_var('post_type');
    $items[] = [
      '@type' => 'ListItem',
      'position' => $position++,
      'name' => get_post_type_object($post_type)->label,
      'item' => trailingslashit(get_post_type_archive_link($post_type)),
    ];
  }
  elseif (is_page() && !is_front_page()) {
    $items[] = [
      '@type' => 'ListItem',
      'position' => $position++,
      'name' => get_the_title(),
      'item' => get_permalink(),
    ];
  }
  elseif (is_category()) {
    $items[] = [
      '@type' => 'ListItem',
      'position' => $position++,
      'name' => single_cat_title('', false),
      'item' => trailingslashit(get_category_link(get_queried_object_id())),
    ];
  }

  return (count($items) > 1) ? $items : null;
}

// ==========================================================
// 3. 各種データの構築
// ==========================================================

// --- (A-TOOL) ツールページ専用 (WebPage) ---
if (is_singular('tool')) {

  ob_start();
  the_kumonosu_description();
  $kumonosu_desc = ob_get_clean();
  $description = wp_strip_all_tags($kumonosu_desc);

  $schema_graph[] = [
    '@type' => 'WebPage',
    '@id'   => get_permalink() . '#toolpage',
    'url'   => get_permalink(),
    'name'  => get_the_title(),
    'description' => $description,
    'inLanguage'  => 'ja',

    'isPartOf' => [
      '@id' => $site_url_slash . '#website'
    ],

    'publisher' => [
      '@id' => $site_url_slash . '#organization'
    ],

    'breadcrumb' => [
      '@id' => get_permalink() . '#breadcrumb'
    ]
  ];
}

// --- (A) 記事ページ (BlogPosting / TechArticle) ---
if (is_single() && get_post_type() !== 'tool') {
  $post_type = get_post_type();
  $schema_type = ($post_type === 'blog') ? 'BlogPosting' : (($post_type === 'css') ? 'TechArticle' : 'Article');

  $image_id = get_post_thumbnail_id();
  $image_meta = $image_id ? wp_get_attachment_metadata($image_id) : null;

  ob_start();
  the_kumonosu_description();
  $kumonosu_desc = ob_get_clean();
  $description = wp_strip_all_tags($kumonosu_desc);

  $article_data = [
    '@type' => $schema_type,
    '@id' => get_permalink() . '#blogposting',
    'mainEntityOfPage' => get_permalink(),
    'headline' => get_the_title(),
    'image' => $image_id ? [
      '@type' => 'ImageObject',
      'url' => wp_get_attachment_image_url($image_id, 'full'),
      'width' => !empty($image_meta['width']) ? $image_meta['width'] : 1200,
      'height' => !empty($image_meta['height']) ? $image_meta['height'] : 675
    ] : null,
    'datePublished' => get_the_time('c'),
    'dateModified' => get_the_modified_time('c'),
    'author' => [
      '@type' => 'Person',
      'name' => 'KUMONOSU',
      'url' => esc_url(home_url('/about/')),
      'sameAs' => [
        'https://x.com/kumonosucss/'
      ]
    ],

    'isPartOf' => [
        '@id' => $site_url_slash . '#website'
    ],

    'publisher' => [
        '@id' => $site_url_slash . '#organization'
    ],
    'description' => $description
  ];

  // image が null の場合はキーごと落とす（検証ツールのノイズ削減）
  if ($article_data['image'] === null) unset($article_data['image']);

  $schema_graph[] = $article_data;
}

// --- (B) 固定ページ (WebPage) ---
if (is_page() && !is_front_page() && !is_page('contact')) {
  ob_start();
  the_kumonosu_description();
  $kumonosu_desc = ob_get_clean();
  $description = wp_strip_all_tags($kumonosu_desc);

  $schema_graph[] = [
    '@type' => 'WebPage',
    '@id' => get_permalink() . '#webpage',
    'url' => get_permalink(),
    'name' => get_the_title(),
    'description' => $description,
    'inLanguage' => 'ja',
    'isPartOf' => [
      '@id' => $site_url_slash . '#website'
    ],
    'publisher' => [
      '@id' => $site_url_slash . '#organization'
    ],
    'breadcrumb' => [
      '@id' => get_permalink() . '#breadcrumb'
    ]
  ];
}

// --- (B) コンタクトページ (ContactPage) ---
if (is_page('contact')) {
  ob_start();
  the_kumonosu_description();
  $kumonosu_desc = ob_get_clean();
  $description = wp_strip_all_tags($kumonosu_desc);

  $schema_graph[] = [
    '@type' => 'ContactPage',
    '@id' => get_permalink() . '#webpage',
    'url' => get_permalink(),
    'name' => get_the_title(),
    'description' => $description,
    'inLanguage' => 'ja',
    'isPartOf' => [
      '@id' => $site_url_slash . '#website'
    ],
    'publisher' => [
      '@id' => $site_url_slash . '#organization'
    ],
    'breadcrumb' => [
      '@id' => get_permalink() . '#breadcrumb'
    ]
  ];
}

// --- (D) カスタム投稿アーカイブページ (CollectionPage) ---
if (is_post_type_archive(['css', 'blog'])) {
  $post_type = get_query_var('post_type');
  $archive_url = trailingslashit(get_post_type_archive_link($post_type));

  $archive_title = post_type_archive_title('', false);
  $description = ($post_type === 'css')
    ? 'CSSやJavaScriptを使ったアニメーションを一覧で紹介しています。動きやパーツ別にアイデア探しに活用できます。'
    : 'CSS・JavaScriptについての特集やまとめの記事を一覧で紹介しています。アイデア探しや参考に活用できます。';

  $schema_graph[] = [
    '@type' => 'CollectionPage',
    '@id'   => $archive_url . '#webpage',
    'url'   => $archive_url,
    'name'  => $archive_title,
    'description' => $description,
    'inLanguage'  => 'ja',
    'isPartOf'    => [
      '@id' => $site_url_slash . '#website'
    ],
    'publisher'   => [
      '@id' => $site_url_slash . '#organization'
    ],
    'breadcrumb'  => [
      '@id' => $archive_url . '#breadcrumb'
    ]
  ];
}

// --- (TOP) トップページ専用（WebPageだけ追加）---
if (is_front_page() || is_home()) {
  $schema_graph[] = [
    '@type' => 'WebPage',
    '@id'   => $site_url_slash . '#webpage',
    'url'   => $site_url_slash,
    'name'  => 'KUMONOSU｜コピペで使えるCSS・JSのアニメーションデザイン',
    'inLanguage' => 'ja',
    'isPartOf' => [
      '@id' => $site_url_slash . '#website'
    ],
    'publisher' => [
      '@id' => $site_url_slash . '#organization'
    ]
  ];
}

// --- (C) パンくずリスト ---
$breadcrumb_items = get_breadcrumb_array();
if ($breadcrumb_items) {
  $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  $clean_url = strtok($current_url, '?');
  $clean_url = trailingslashit($clean_url);

  $schema_graph[] = [
    '@type' => 'BreadcrumbList',
    '@id' => $clean_url . '#breadcrumb',
    'itemListElement' => $breadcrumb_items
  ];
}

// --- (D) サイトナビゲーション ---
$schema_graph[] = [
  '@type' => 'SiteNavigationElement',
  'name' => '主要ナビゲーション',
  'hasPart' => [
    ['@type' => 'WebPage', 'name' => 'トップ', 'url' => $site_url_slash],
    ['@type' => 'WebPage', 'name' => 'CSS', 'url' => $site_url_slash . 'css/'],
    ['@type' => 'WebPage', 'name' => '特集&まとめ', 'url' => $site_url_slash . 'blog/'],
    ['@type' => 'WebPage', 'name' => 'KUMONOSUについて', 'url' => $site_url_slash . 'about/'],
    ['@type' => 'WebPage', 'name' => 'お問い合わせ', 'url' => $site_url_slash . 'contact/']
  ]
];

// ==========================================================
// 4. JSON-LDとして一括出力
// ==========================================================
if (!empty($schema_graph)) : ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": <?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
}
</script>
<?php endif; ?>