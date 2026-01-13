<article class="c-card">
    <a href="<?php the_permalink(); ?>" class="c-card-link">
        <div class="c-card-media">
            <div class="c-card-image">
                <?php the_post_thumbnail('', [
                    'alt'   => get_the_title(),
                    'loading' => 'lazy',
                    'decoding' => 'async',
                ]); ?>
            </div>
        </div>
        <div class="c-card-body">
            <?php
            // データの取得
            $kumo_data = get_kumonosu_page_data();

            // 1. 技術スタックの表示
            if ( ! empty( $kumo_data['tech_stack'] ) ) {
                echo '<div class="c-card-tag">';

                // 最初の項目かどうかを判定する変数を定義
                $is_first = true;

                foreach ( $kumo_data['tech_stack'] as $tech ) {
                    // 2つ目以降のループであれば、直前に区切り文字を表示
                    if ( ! $is_first ) {
                        echo ' / ';
                    }

                    // バッジを表示
                    echo '<span class="badge">' . strtoupper( $tech ) . '</span>';

                    // 最初のループが終わったので false にする
                    $is_first = false;
                }

                echo '</div>';
            }
            ?>

            <h3 class="c-card-title"><?php the_title(); ?></h3>

            <?php
            // 1. カスタム投稿タイプ（標準の post, page 以外）をすべて取得
            // '_builtin' => false は「標準機能ではない（＝カスタム）」という意味です
            $custom_post_types = get_post_types( array( '_builtin' => false ) );

            // 2. 「カスタム投稿タイプの詳細ページではない」場合のみ表示
            if ( ! is_singular( $custom_post_types ) ) :
            ?>
            <div class="c-card-footer">
                <div class="c-card-category">
                    <span class="c-card-category-label">Category</span>
                    <?php
                    $terms = get_the_terms(get_the_ID(), 'css-category');

                    if (!empty($terms) && !is_wp_error($terms)) :
                        foreach ($terms as $term) :
                            ?>
                            <p class="c-card-category-name"><?php echo esc_html($term->name); ?></p>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <span class="c-card-btn">More</span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        // 1. カスタム投稿タイプ（標準の post, page 以外）をすべて取得
        // '_builtin' => false は「標準機能ではない（＝カスタム）」という意味です
        $custom_post_types = get_post_types( array( '_builtin' => false ) );

        // 2. 「カスタム投稿タイプの詳細ページではない」場合のみ表示
        if ( ! is_singular( $custom_post_types ) ) :
        ?>
        <span class="glow"></span>
        <?php endif; ?>
    </a>
</article>