<?php
$posts = new WP_Query();
$posts->query(array('showposts'=>10, 'post_status'=>'publish'));
?>

<h1><?php echo get_option('blogname'); ?></h1>

<?php
while ($posts->have_posts())
{
    $posts->the_post();
    $image = nt_post_image(get_the_ID());
?>
<h2><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php if ($image != null) { ?>
        <img src="<?php echo $image; ?>" alt="COOL PHOTO" align="left" width="100" hspace="10"/>
    <?php } ?>
    <?php the_excerpt(); ?>
    <br clear="both"/>
<?php
}
?>

<p><small>To unsubscribe this newsletter, <a href="{unsubscription_url}">click here</a>.</small></p>
