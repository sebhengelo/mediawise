<?php
get_header();
?>
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
<section id="content">
	<div class="container">
		<div class="col-md-9 col-sm-8">
			<div class="content">
				<?php
                        the_content();
                     ?>
			</div>
		</div>
		<div class="col-md-3 col-sm-4">
			<div class="sidebar">
				<?php 
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('mainsidebar') ) : ?>
                <?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
?>