<?php
/*
Template Name: Testimonials
*/
?>

<?php get_header(); ?>

        <main id="main" class="site-main" role="main">

            <header class="entry-header">

                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

                <?php edit_post_link( __( 'Edit', 'wpzoom' ), '<span class="edit-link">', '</span>' ); ?>

            </header><!-- .entry-header -->


            <div class="entry-content">

                <?php while ( have_posts() ) : the_post(); ?>

                    <?php the_content(); ?>

                <?php endwhile; // end of the loop. ?>

            </div><!-- .entry-content -->


			<div class="wpzoom-testimonial">

    			<?php
        		   $loop = new WP_Query( array( 'post_type' => 'testimonial', 'posts_per_page' => 99, 'orderby' => 'date') );
            		while ( $loop->have_posts() ) : $loop->the_post();
            		$customFields = get_post_custom();
         			$testimonial_author = $customFields['wpzoom_testimonial_author'][0];
        			$testimonial_position = $customFields['wpzoom_testimonial_author_position'][0];
        			$testimonial_company = $customFields['wpzoom_testimonial_author_company'][0];
        			$testimonial_company_url = $customFields['wpzoom_testimonial_author_company_url'][0];
        		?>

				<div class="testimonial">

                    <blockquote><?php the_content(); ?></blockquote>

                    <div class="testimonial_footer">

                        <?php if ( has_post_thumbnail() ) : ?>

                            <div class="testimonial-thumb">
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'testimonial-widget-author-photo' ); ?></a>
                            </div>

                        <?php endif; ?>


                        <div class="testimonial_details">

                           <?php

                               if ($testimonial_author) echo "<h4>$testimonial_author</h4>";
                               if ($testimonial_company) {
                                   echo '<span class="company">';
                                   if ($testimonial_company_url) echo "<a href=\"$testimonial_company_url\">";
                                   echo $testimonial_company;
                                   if ($testimonial_company_url) echo '</a>';
                                   echo '</span>';
                               }
                               if ($testimonial_company & $testimonial_position) { echo ", "; }

                               if ($testimonial_position) echo "<span class=\"position\">$testimonial_position</span>";

                               ?>
                        </div>

                    </div>

    				<div class="cleaner">&nbsp;</div>

				</div><!-- / .testimonial -->

    			<?php
    			endwhile;

    			//Reset query_posts
    			wp_reset_query();

    			?>

            </div><!-- / .testimonial -->

        </main>

<?php get_footer(); ?>