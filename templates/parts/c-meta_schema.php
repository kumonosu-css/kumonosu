<?php
// ==========================================================
// 1. 変数定義
// ==========================================================
$site_name = get_bloginfo('name');
$site_url  = home_url();
$logo_url  = $site_url . '/wp-content/themes/kumonosu/assets/img/favicon/logo.png';

// 最終的に出力するデータを格納する配列
$schema_graph = [];

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
        'item' => home_url(),
    ];

    if (is_single()) {
        $post_type = get_post_type();
        if ($post_type === 'css' || $post_type === 'blog') {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => get_post_type_object($post_type)->label,
                'item' => get_post_type_archive_link($post_type),
            ];
        } else {
            $categories = get_the_category();
            if (!empty($categories)) {
                $cat = $categories[0];
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $cat->name,
                    'item' => get_category_link($cat->term_id),
                ];
            }
        }
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_the_title(),
            'item' => get_permalink(),
        ];
    } // --- ここから追加：カスタム投稿アーカイブ用 ---
    elseif (is_post_type_archive(['css', 'blog'])) {
        $post_type = get_query_var('post_type');
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_post_type_object($post_type)->label,
            'item' => get_post_type_archive_link($post_type),
        ];
    } elseif (is_page() && !is_front_page()) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_the_title(),
            'item' => get_permalink(),
        ];
    } elseif (is_category()) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => single_cat_title('', false),
            'item' => get_category_link(get_queried_object_id()),
        ];
    }

    return (count($items) > 1) ? $items : null;
}

// ==========================================================
// 3. 各種データの構築
// ==========================================================

// --- (A) 記事ページ (BlogPosting / TechArticle) ---
if ( is_single() ) {
    $post_type = get_post_type();
    $schema_type = ($post_type === 'blog') ? 'BlogPosting' : (($post_type === 'css') ? 'TechArticle' : 'Article');

    $image_id = get_post_thumbnail_id();
    $image_meta = wp_get_attachment_metadata($image_id);
    ob_start(); // 記録開始
    the_kumonosu_description(); // 関数を実行
    $kumonosu_desc = ob_get_clean(); // 表示されるはずだった内容を変数に代入
    $description = wp_strip_all_tags($kumonosu_desc); // タグを除去して整理

    $article_data = [
        '@type' => $schema_type,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => get_permalink() . '#webpage'
        ],
        'headline' => get_the_title(),
        'image' => [
            '@type' => 'ImageObject',
            'url' => wp_get_attachment_image_url($image_id, 'full'),
            'width' => !empty($image_meta['width']) ? $image_meta['width'] : 1200,
            'height' => !empty($image_meta['height']) ? $image_meta['height'] : 675
        ],
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
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'KUMONOSU',
            'alternateName' => 'コピペで使えるCSS・JSのアニメーションデザイン',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logo_url
            ]
        ],
        'description' => $description
    ];
    $schema_graph[] = $article_data;
}

// --- (B) 固定ページ (WebPage) ---
if ( !is_page('contact') ) {
    ob_start();
    the_kumonosu_description();
    $kumonosu_desc = ob_get_clean();
    $description = wp_strip_all_tags($kumonosu_desc);

    $page_data = [
        '@type' => 'WebPage',
        '@id' => get_permalink() . '#webpage',
        'url' => get_permalink(),
        'name' => get_the_title(),
        'description' => $description,
        'inLanguage' => 'ja',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'KUMONOSU',
            'url' => home_url('/')
        ],
        // パンくずリストとこのページを紐付けるための設定
        'breadcrumb' => [
            '@id' => get_permalink() . '#breadcrumb'
        ]
    ];

    // publisher情報を追加（Organizationとしての信頼性）
    $page_data['publisher'] = [
        '@type' => 'Organization',
        'name' => 'KUMONOSU',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logo_url
        ]
    ];

    $schema_graph[] = $page_data;
}

// --- (B) コンタクトページ (WebPage) ---
if ( is_page('contact') ) {
    ob_start();
    the_kumonosu_description();
    $kumonosu_desc = ob_get_clean();
    $description = wp_strip_all_tags($kumonosu_desc);

    $page_data = [
        '@type' => 'WebPage',
        '@id' => get_permalink() . '#webpage',
        'url' => get_permalink(),
        'name' => get_the_title(),
        'description' => $description,
        'inLanguage' => 'ja',
        'isPartOf' => [
            '@type' => 'ContactPage',
            'name' => 'KUMONOSU',
            'url' => home_url('/')
        ],
        // パンくずリストとこのページを紐付けるための設定
        'breadcrumb' => [
            '@id' => get_permalink() . '#breadcrumb'
        ]
    ];

    // publisher情報を追加（Organizationとしての信頼性）
    $page_data['publisher'] = [
        '@type' => 'Organization',
        'name' => 'KUMONOSU',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logo_url
        ]
    ];

    $schema_graph[] = $page_data;
}

// --- (D) カスタム投稿アーカイブページ (CollectionPage) ---
if ( is_post_type_archive(['css', 'blog']) ) {
    $post_type = get_query_var('post_type');
    $post_type_obj = get_post_type_object($post_type);

    // アーカイブページの説明を取得（カスタム投稿の説明文など）
    $archive_title = post_type_archive_title('', false);
    $description = ($post_type === 'css')
        ? 'CSSやJavaScriptを使ったアニメーションを一覧で紹介しています。動きやパーツ別にアイデア探しに活用できます。'
        : 'CSS・JavaScriptについての特集やまとめの記事を一覧で紹介しています。アイデア探しや参考に活用できます。';

    $archive_data = [
        '@type' => 'CollectionPage', // 一覧ページであることを明示
        '@id' => get_post_type_archive_link($post_type) . '#webpage',
        'url' => get_post_type_archive_link($post_type),
        'name' => $archive_title,
        'description' => $description,
        'inLanguage' => 'ja',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'KUMONOSU',
            'url' => home_url('/')
        ],
        'breadcrumb' => [
            '@id' => get_post_type_archive_link($post_type) . '#breadcrumb'
        ]
    ];

    // publisher情報を追加
    $archive_data['publisher'] = [
        '@type' => 'Organization',
        'name' => 'KUMONOSU',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logo_url
        ]
    ];

    $schema_graph[] = $archive_data;
}

// --- (B) トップページ専用 (WebSite & Organization) ---
if ( is_front_page() ) {
    $schema_graph[] = [
        '@type' => 'WebSite',
        'name' => 'KUMONOSU',
        'alternateName' => 'コピペで使えるCSS・JSのアニメーションデザイン',
        'url' => $site_url,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $site_url . '/?s={search_term_string}',
            'query-input' => 'required name=search_term_string'
        ]
    ];

    $schema_graph[] = [
        '@type' => 'Organization',
        'name' => 'KUMONOSU',
        'url' => $site_url,
        'logo' => $logo_url
    ];
}

// --- (C) パンくずリスト ---
$breadcrumb_items = get_breadcrumb_array();
if ($breadcrumb_items) {
    // 現在表示しているページの正確なURLを取得
    $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // クエリパラメータ（?s=など）を除去してクリーンなURLにする
    $clean_url = strtok($current_url, '?');

    $schema_graph[] = [
        '@type' => 'BreadcrumbList',
        '@id' => $clean_url . '#breadcrumb', // これでアーカイブでも記事でも正しいURLになる
        'itemListElement' => $breadcrumb_items
    ];
}

// --- (D) サイトナビゲーション ---
$schema_graph[] = [
    '@type' => 'SiteNavigationElement',
    'name' => '主要ナビゲーション',
    'hasPart' => [
        ['@type' => 'WebPage', 'name' => 'トップ', 'url' => $site_url . '/', ],
        ['@type' => 'WebPage', 'name' => 'CSS', 'url' => $site_url . '/css/'],
        ['@type' => 'WebPage', 'name' => '特集&まとめ', 'url' => $site_url . '/blog/'],
        ['@type' => 'WebPage', 'name' => 'KUMONOSUについて', 'url' => $site_url . '/about/', ],
        ['@type' => 'WebPage', 'name' => 'お問い合わせ', 'url' => $site_url . '/contact/']
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