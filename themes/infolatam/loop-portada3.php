<?php 
	query_posts('cat=78&order=DESC&showposts=1');
	
	
	global $post;
	
	if ( ! have_posts() ) : ?>
	<div class="notportada_der">
	  	<div class="notportada_titular">
			<h1 class="entry-title"><?php _e( 'No encontrado', 'twentyten' ); ?></h1>
			<p><?php _e( 'Lo sentimos, pero la página que estás buscando no se ha encontrado. Quizás la búsqueda le pueda ayudar.', 'twentyten' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .notportada_titular -->
	</div><!-- #notportada_der -->
	<?php endif;
	
	while ( have_posts() ) : the_post(); ?>
		<div class="notportada_der">
	  	  <div class="notportada_titular"> 
			<?php $asociado = loop_interno($post); ?>

			<br /> <!-- centrado de imágenes del pie -->
			<div class="icono_pie bot_derecha1">
					 <a href="<?php comments_link(); ?>" id="link-comments">
					 <img src="<?php bloginfo('home'); ?>/wp-content/uploads/2010/05/boton-comenta.png" alt="Comentarios" class="icono_coment"></a>
			</div>
			<div class="icono_pie bot_derecha2">
					 <?php if( function_exists('ADDTOANY_SHARE_SAVE_KIT') ) { ADDTOANY_SHARE_SAVE_KIT(); } ?>
			</div><!-- icono_pie bot_derecha2 -->
			
			
			<div class="antifloat">&nbsp;</div>
			</div><!-- #notportada_titular-<?php the_ID(); ?> -->
			<?php loop_interno2(); ?>
			
			<?php if($asociado != ''):?>
				<div class="notportada_analisis">						
				   	<?php mostrar_analisis_portada($asociado);?>
				</div><!-- #notportada_analisis-<?php the_ID(); ?> -->
			<?php endif; ?>
		
		</div><!-- #notportada_der-<?php the_ID(); ?> -->

		<?php comments_template( '', true ); ?>
			
	<?php endwhile;

	
		
	//Reset Query
	wp_reset_query();
	
?>