<?php
get_header();

if ( have_posts() ) : while ( have_posts() ) : the_post();

$title=get_post_meta(get_the_ID(),'vibe_title',true);
if(vibe_validate($title) || empty($title)){
?>

<?php
}

    $v_add_content = get_post_meta( $post->ID, '_add_content', true );
 
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
        <div class="row">
            <div class="col-md-12">

                <div class="<?php echo $v_add_content;?> content">
                    <?php
                        the_content();
                     ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
endwhile;
endif;
?>
<?php
get_footer();
?>