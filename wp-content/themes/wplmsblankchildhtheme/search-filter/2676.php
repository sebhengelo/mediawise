<?php
/**
 * Search & Filter Pro 
 *
 * Badges (test)
 * 
 * @package   Search_Filter
 * @author    Ross Morsali
 * @link      http://www.designsandcode.com/
 * @copyright 2014 Designs & Code
 * 
 */

if ( $query->have_posts() )
{
	?>
	
	<?php
	while ($query->have_posts())
	{
		$query->the_post();
		
		?>
		<div class="col-md-3 cursussen badges-overzicht">
			<div class="badge-item">
				<p class="badge-punten"><?php the_field('badgeos-punten'); ?></p> <!-- Punten toevoegen -->
				<div class="badge-icon">
					<? $thumb_id = get_post_thumbnail_id(); ?>
					<? $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'thumbnail-size', true); ?>
					<? $thumb_url = $thumb_url_array[0];?>
					<a href="<?php the_permalink(); ?>"><img class="thumbnail-badge" src="<? echo $thumb_url; ?>"</img></a>
				</div>
				<div class="badge-content">
					<div class="badge-content">
						<h6><a href="<?php the_permalink(); ?>">
							<?php the_title(); ?></a>
						</h6>
						<div class="badge-omschrijving">
							<p><?php the_excerpt(); ?></p>
						</div>
					</div>	
				</div>
			</div>
		</div>
		<?php
	}
	?>


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