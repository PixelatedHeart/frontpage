<?php
global $post;

$texts['footer'] = '<p>To unsubscribe <a href="{unsubscription_url}">click here</a>.</p>';
$texts['header'] = '<p>Hi {name},</p>
<p>here the lastest news:</p>';

@include(dirname(__FILE__) . '/' . WPLANG . '.php');

query_posts('showposts=' . nt_option('posts', 10) . '&post_status=publish');
$empty_image = get_option('blogurl') . '/wp-content/plugins/newsletter/themes/with-picture/empty.gif';
?>

<div style="background-color: #444444;">

<table width="550" cellpadding="10" align="center" style="margin-top: 30px; background-color: #ffffff; border: 5px solid #3B3B3B; ">
<tr><td>
<?php echo $texts['header']; ?>

<table cellspacing="10">
<?php while (have_posts()) { the_post(); ?>
    <tr>
        <td valign="top">
            <a href="<?php echo get_permalink(); ?>"><img src="<?php echo nt_post_image($post->ID, 'thumbnail', $empty_image); ?>" width="100"/></a>
        </td>
        <td valign="top" align="left">
            <a href="<?php echo get_permalink(); ?>"><strong><?php the_title(); ?></strong></a><br />
            <?php echo the_excerpt(); ?>
        </td>
    </tr>
<?php } ?>
</table>

<?php echo $texts['footer']; ?>
    </td></tr>
</table>
</div>

<?php wp_reset_query(); ?>