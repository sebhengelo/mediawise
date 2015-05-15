<?php
/**
 * Search & Filter Pro 
 *
 * Sample Results Template
 * 
 * @package   Search_Filter
 * @author    Ross Morsali
 * @link      http://www.designsandcode.com/
 * @copyright 2014 Designs & Code
 * 
 * Note: these templates are not full page templates, rather 
 * just an encaspulation of the your results loop which should
 * be inserted in to other pages by using a shortcode - think 
 * of it as a template part
 * 
 * This template is an absolute base example showing you what
 * you can do, for more customisation see the WordPress docs 
 * and using template tags - 
 * 
 * http://codex.wordpress.org/Template_Tags
 *
 */

if ( $query->have_posts() )
{
	?>
	
	<div class="lessen-aantal">
		<?php echo $query->found_posts; ?> resultaten (Pagina <?php echo $query->query['paged']; ?> van <?php echo $query->max_num_pages; ?>)
	</div>
	
	
	<?php
	while ($query->have_posts())
	{
		$query->the_post();
		
		?>
		<div class="col-md-4">
				<div>
					<? $thumb_id = get_post_thumbnail_id(); ?>
					<? $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'thumbnail-size', true); ?>
					<? $thumb_url = $thumb_url_array[0];?>
					<div class="thumbtitle group">
						<a href="#"><div class="thumbnail-les" style="background-image: url(<? echo $thumb_url; ?>);">
							<div class="lessen-meta-img">
								Begrip
							</div>
						</div></a>
						<div class="lessen-content">
							<h6><a href="<?php the_permalink(); ?>">
								<?php the_title(); ?></a>
							</h6>

							<p><?php the_field('beschrijving'); ?></p>
							<p class="meta-werkvorm"><?php the_field ('type'); ?></p>
						</div>
					</div>
					
				</div>

			</div>

		<?php
	}
	?>
	<div class="lessen-aantal">
		Pagina <?php echo $query->query['paged']; ?> van <?php echo $query->max_num_pages; ?><br />
	</div>
	<div class="pagination">
		
		<div class="nav-previous"><?php next_posts_link( 'Older posts', $query->max_num_pages ); ?></div>
		<div class="nav-next"><?php previous_posts_link( 'Newer posts' ); ?></div>
		<?php
			/* example code for using the wp_pagenavi plugin */
			if (function_exists('wp_pagenavi'))
			{
				echo "<br />";
				wp_pagenavi( array( 'query' => $query ) );
			}
		?>
	</div>
	<?php
}
else
{
	echo "No Results Found";
}
?>