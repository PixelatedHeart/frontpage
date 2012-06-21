<?php
global $post;

$fecha = date('d').' de '.es_month().' de '.date('Y');

$texts['footer'] = '<p>Recibe usted este mensaje porque su dirección de correo electrónico, está registrada en nuestro Servicio de Envío de Titulares. Si desea cancelar su suscripción introduzca su e-mail <a href="{unsubscription_url}">aquí</a>.</p>
					<p>Departamento de Contenidos de <a href="http://infolatam.com">Infolatam.com</a></p>
					<p>INFOLATAM Todos los derechos reservados 2010</p>';
					
$texts['header'] = '<div>
						<div>'.$fecha.'</div>
						<img src="http://www.infolatam.com/img/header2.jpg" alt="Infolatam" align="left" border="0" height="90" width="733">
					</div>
					<div>
					<div>Los titulares de hoy en Infolatam</div>
					<p>Estimado usuario:</p>
					<p>Gracias por leer nuestros titulares.</p>
					<div>&nbsp;</div>';

//$posts = get_posts('numberposts=10');
query_posts('showposts=' . nt_option('posts', 10) . '&post_status=publish');
?>

<?php echo $texts['header']; ?>

<?php
while (have_posts())
{
    the_post();?>
	
	<a href="<?php the_permalink(); ?>" style="float:right;clear:right;"><?php the_post_thumbnail('analisis-thumb'); ?></a>
	<h1>
   		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h1>

	<?php echo the_excerpt(); ?>
	
	<?php 	
        $post_id = get_the_ID();
		$el_analisis = get_post_meta($post_id, 'el_analisis' , true);
		if($el_analisis):
				mostrar_analisis_newsletter($el_analisis);
		endif;
	?>
	
	<div>&nbsp;</div><?php
}
?>

<?php echo $texts['footer']; ?>

<?php wp_reset_query(); ?>