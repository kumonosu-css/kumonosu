<article class="c-blog-card">
    <a href="<?php the_permalink(); ?>" class="c-blog-card-link">
        <div class="c-blog-card-image">
            <?php echo wp_get_attachment_image(
                get_post_thumbnail_id(),
                '',
                false,
                [
                'alt'      => get_the_title(),
                'loading'  => 'lazy',
                'decoding' => 'async',
                ]
            ); ?>
        </div>
        <div class="c-blog-card-body">
            <div class="c-blog-card-category">
                <?php
                $terms = get_the_terms(get_the_ID(), 'blog-category');

                if (!empty($terms) && !is_wp_error($terms)) :
                    foreach ($terms as $term) :
                        ?>
                        <p class="c-blog-card-category-name"><?php echo esc_html($term->name); ?></p>
                        <?php
                    endforeach;
                endif;
                ?>
            </div>
            <h3 class="c-blog-card-title"><?php the_title(); ?></h3>
        </div>
    </a>
</article>