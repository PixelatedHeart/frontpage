<?php
global $post;

$texts['footer'] = '<p>To unsubscribe <a href="{unsubscription_url}">click here</a>.</p>';
$texts['header'] = '<p>Hi {name},</p>
<p>here the lastest news:</p>';

//$posts = get_posts('numberposts=10');
query_posts('showposts=' . nt_option('posts', 10) . '&post_status=publish');
?>
<div style="font-family: sans-serif; font-size: 24px; color: #999"><?php echo get_option('blogname'); ?></div>

<?php echo $texts['header']; ?>

<?php
while (have_posts())
{
    the_post();

?>
<div><a style="font-size: 16px; text-decoration: none; color: #369" href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></div>
<div><?php echo the_excerpt(); ?></div>
<div style="height: 30px; clear: both"></div>
<?php
}
?>

<?php echo $texts['footer']; ?>

<?php wp_reset_query(); ?>