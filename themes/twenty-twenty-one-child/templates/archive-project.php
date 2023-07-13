<?php
/* Template Name: archive project */
get_header();
?>

<main id="main" class="site-main">
    <section class="project-archive">
        <div class="container">
            <?php
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = array(
                'post_type'      => 'project',
                'posts_per_page' => 6,
                'paged'          => $paged
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    ?>
                    <article class="project">
                        <h2><?php the_title(); ?></h2>
                        <div class="project-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                    <?php
                }
                ?>
                <div class="pagination">
                    <?php
                    echo paginate_links(array(
                        'total'     => $query->max_num_pages,
                        'current'   => max(1, $paged),
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;'
                    ));
                    ?>
                </div>
                <?php
                wp_reset_postdata();
            } else {
                ?>
                <p>No projects found.</p>
                <?php
            }
            ?>
        </div>
    </section>
</main>
<?php
$coffee_link = hs_give_me_coffee();

if ($coffee_link) {
    echo '<a href="' . esc_url($coffee_link) . '">Get your coffee here!</a>';
} else {
    echo 'No coffee available.';
}
?>

<?php
get_footer();
