<?php
get_header();
if ( have_posts() ) : while ( have_posts() ) : the_post();


$title=get_post_meta(get_the_ID(),'vibe_title',true);

if(!isset($title) || !$title || (vibe_validate($title))){

?>

<?php
}

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

<div class="container">
        
        <div class="row">
            <div class="col-md-9">
                <div class="content">
                <div class="row">
                    <div class="col-md-4"><?php the_post_thumbnail('full'); ?></div>
                    <div class="col-md-8 les-inleiding"><p><?php echo get_post_meta( $post->ID, 'beschrijving', true ); ?></p></div>
                </div>
                <div class="les-inhoud">
                    <h4>Inleiding</h4>
                    <p><?php echo get_post_meta( $post->ID, 'inleiding', true ); ?></p>
                    <h4>Lesdoelen</h4>
                    <p><?php echo get_post_meta( $post->ID, 'lesdoelen', true ); ?></p>
                    <h4>Voorbereiding</h4>
                    <p><?php echo get_post_meta( $post->ID, 'voorbereiding', true ); ?></p>
                    <h4>Workflow</h4>
                    <p><?php echo get_post_meta( $post->ID, 'workflow', true ); ?></p>
                    <h4>Huiswerk/vervolg</h4>
                    <p><?php echo get_post_meta( $post->ID, 'huiswerk_vervolg', true ); ?></p>
                </div>
                
                <?php
                    $author = getPostMeta($post->ID,'vibe_author',true);
                    if(vibe_validate($author)){ ?>
                    <div class="postauthor">
                        <div class="auth_image">
                            <?php
                                echo get_avatar( get_the_author_meta('email'), '160');
                                $instructing_courses=apply_filters('wplms_instructing_courses_endpoint','instructing-courses');
                             ?>
                        </div>
                        <div class="author_info">
                            <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" class="readmore link"><?php _e('Posts','vibe'); ?></a><a class="readmore">&nbsp;|&nbsp;</a><a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ).$instructing_courses; ?>" class="readmore link"><?php _e('Courses','vibe'); ?></a>
                            <h6><?php the_author_meta( 'display_name' ); ?></h6>
                            <div class="author_desc">
                                <p>
                                    <?php  the_author_meta( 'description' );?>
                                </p>
                                <p class="website"><?php _e('Website','vibe');?> : <a href="<?php  the_author_meta( 'url' );?>" target="_blank"><?php  the_author_meta( 'url' );?></a></p>
                                <?php
                                    $author_id=  get_the_author_meta('ID');
                                    vibe_author_social_icons($author_id);
                                ?>  
                            </div>     
                        </div>    
                    </div>
                    <?php
                    } 
                
                comments_template();
                endwhile;
                endif;
                ?>

            </div>
        </div>
            
            <div class="col-md-3 col-sm-3">
                <div class="sidebar">
                    <div>
                        <h3>Informatie</h3>
                        <p>Competentie: <span class="meta-les"><?php echo get_post_meta( $post->ID, 'competentie', true ); ?></span></p>
                        <p>Duur: <span class="meta-les"><?php echo get_post_meta( $post->ID, 'duur', true ); ?></span></p>
                        <p>Thema: <span class="meta-les"><?php echo get_post_meta( $post->ID, 'thema', true ); ?></span></p>
                    </div>
                    <div>
                        <h3>Materialen</h3>
                        <p>Bijlagen: <span class="meta-les"><?php echo get_post_meta( $post->ID, 'bijlagen', true ); ?></span></p>
                        <?php $full_url = wp_get_attachment_url( get_post_meta( $post->ID, ‘bijlagen’, true ) ); ?>
                        <a href="<?php echo $full_url; ?>">Link</a>   
                        <?php $files = get_post_meta( $post->ID, ‘bijlage’ );

                        if ($files) {
                        foreach ($files as $attachment_id) {
                        $full_url = wp_get_attachment_url( $attachment_id );
                        }
                        }

                        ?>
                    </div>
                    <div>
                        <h3>Auteur</h3>
                         <?php the_author(); ?> 
                        <?php $author = get_the_author(); ?>
                        
                    </div>
                </div>
            </div>
            
        </div>
    </div>

<?php
get_footer();
?>