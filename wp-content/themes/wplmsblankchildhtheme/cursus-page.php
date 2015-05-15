<?php
/**
 * Template Name: Lessen
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package web2feel
 */

get_header(); ?>
<div class="pagetitle">
                <div class="container">
                    <h1 class="header-title"><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
            <div class="container">
                <div class="breadcrumbs">
                    <?php
                        $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
                        if(vibe_validate($breadcrumbs) || empty($breadcrumbs))
                            vibe_breadcrumbs(); 
                    ?>
                </div>
            </div>    
<div class="container container-lessen">	
	<div class="row row-lessen">
		<div class="col-md-12">
		<div class="col-md-3 col-md-3-lessen">
			<a href="/mediawise/lessen/nieuwe-les" class="button lessen-deel hvr-grow">Deel je les</a>
			<?php echo do_shortcode( '[searchandfilter id="2124"]' ); ?>
		</div>
		<div class="col-md-9 lessen-grid">

				<?php echo do_shortcode( '[searchandfilter id="2124" show="results"]'); ?>
				<?php while ( have_posts() ) : the_post(); ?>
					
					<?php
						// If comments are open or we have at least one comment, load up the comment template
						if ( comments_open() || '0' != get_comments_number() )
							comments_template();
					?>

				<?php endwhile; // end of the loop. ?>
				<?php $lessen = new WP_Query(array(
						'post_type' => 'les'
					)); ?>
				<?php while($lessen->have_posts()) : $lessen->the_post(); ?>

			
		<?php endwhile; ?>
		</div><!-- #primary -->
		

<?php //get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>
