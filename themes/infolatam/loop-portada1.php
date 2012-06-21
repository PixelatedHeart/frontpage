<?php 
	query_posts('cat=3&order=DESC&showposts=1');
	
	
	global $post; 
	if ( ! have_posts() ) : ?>
	<div class="notportada_cen">
		<div class="notportada_titular">
			<h1 class="entry-title"><?php _e( 'No encontrado', 'twentyten' ); ?></h1>
			<p><?php _e( 'Lo sentimos, pero la página que estás buscando no se ha encontrado. Quizás la búsqueda le pueda ayudar.', 'twentyten' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .notportada_titular -->
	</div><!-- #notportada_cen -->
	<?php else : 
	
	while ( have_posts() ) : the_post(); ?>
		<div class="notportada_cen">
			<div class="notportada_titular">
			
			<?php 
				$post_id = $post->ID;
				$asociado = get_post_meta($post_id, 'asociado' , true);
				$asociado2 = get_post_meta($post_id, 'asociado2' , true);
				$asociado3 = get_post_meta($post_id, 'asociado3' , true);
				$asociado4 = get_post_meta($post_id, 'asociado4' , true);
				$video_destacado = get_post_meta($post->ID, 'video_destacado' , true);
			?>
			
			<div class="tipo_articulo">
			<?php if ( in_category( 'el-analisis-de-infolatam' )): ?>
					EL ANÁLISIS DE INFOLATAM
			<?php elseif ( in_category( 'analisis' )): ?>
					EL ANÁLISIS
			<?php elseif ( in_category( 'la-noticia' )): ?>
					LA NOTICIA
			<?php elseif ( in_category( 'el-informe' )): ?>
					EL INFORME
			<?php elseif(in_category( 'biografia' )):?>
					LA BIOGRAFÍA
			<?php elseif(in_category( 'entrevista' )):?>
					LA ENTREVISTA
			<?php elseif(in_category( 'documento' )):?>
					EL DOCUMENTO
			<?php else:?>
			<?php endif; ?>
			</div><!-- tipo_articulo -->
						
			<?php /* ?><span class="pais"><?php echo get_the_term_list( $post->ID, 'pais', '', ' ', '' ); ?></span><?php */ ?>
			<h1>
				<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h1>
			
			
			<?php //Si el excerpt estÃ¡ rellenado, lo mostramos, en otro caso mostraremos the_content, porque asÃ­ se puede controlar lo qu se muestra con la etiqueta <!--more-->, con excerpt no se puede.	
				if(has_excerpt($post_id)): ?>
					<p>
						<?php 
									//IMAGEN O VÍDEO DESTACADO
									//Si hay vídeo destacado, lo mostramos, sino la imagen destacada. En otro caso, nada.
									if(($video_destacado != '') || (has_post_thumbnail()) ): ?>
									<?php
											if($video_destacado != ''):
													echo mostrar_video_destacado($video_destacado);
											else:
													the_post_thumbnail('large');
											endif;?>
									<?php
									endif;
						?>
						
						<?php
						//Si lo hay, mostramos el excerpt
						$text = get_the_excerpt();
						$text = strip_tags($text, '<strong><a><i><em><p><img>[caption]');
						$text = apply_filters('the_content', $text);
						
						echo $text;
						 ?>
					</p>
				<?php else: ?>
					<p>
						<?php if(has_post_thumbnail()):
						//Si la hay, mostramos la imagen destacada
							the_post_thumbnail('large');
						endif;					
						//Si no hayexcerpt, mostramos el content
						the_content(); ?>
					</p>
			<?php endif; ?>
				
			
			
			<!--<span class="edit-home"><?php edit_post_link( __( 'Edit', 'twentyten' ), "<span class=\"edit-link\">", "</span>" ); ?></span>-->
			<br /> <!-- centrado de im‡genes del pie -->
			<div class="icono_pie bot_derecha1">
					 <a href="<?php comments_link(); ?>" id="link-comments">
					 	<img src="<?php bloginfo('home'); ?>/wp-content/uploads/2010/05/boton-comenta.png" alt="Comentarios" class="icono_coment"></a>
			</div><!-- icono_pie bot_derecha1 -->
			
			<div class="icono_pie bot_derecha2">
					 <?php if( function_exists('ADDTOANY_SHARE_SAVE_KIT') ) { ADDTOANY_SHARE_SAVE_KIT(); } ?>
			</div><!-- icono_pie bot_derecha2 -->
			
			
			<div class="antifloat">&nbsp;</div>
			</div><!-- #notportada_titular-<?php the_ID(); ?> -->

			<?php loop_interno2(); ?> <!-- dibuja las claves -->
			
			
			
			<?php if($asociado != ''):?>
				<div class="notportada_analisis">						
				   	<?php mostrar_analisis_portada($asociado);?>
				</div><!-- #notportada_analisis-<?php the_ID(); ?> -->
			<?php endif; ?>
			<?php if($asociado2 != ''):?>
				<div class="notportada_analisis">						
				   	<?php mostrar_analisis_portada($asociado2);?>
				</div><!-- #notportada_analisis-<?php the_ID(); ?> -->
			<?php endif; ?>
			<?php if($asociado3 != ''):?>
				<div class="notportada_analisis">						
				   	<?php mostrar_analisis_portada($asociado3);?>
				</div><!-- #notportada_analisis-<?php the_ID(); ?> -->
			<?php endif; ?>
			<?php if($asociado4 != ''):?>
				<div class="notportada_analisis">						
				   	<?php mostrar_analisis_portada($asociado4);?>
				</div><!-- #notportada_analisis-<?php the_ID(); ?> -->
			<?php endif; ?>
			
	</div><!-- #notportada_cen-<?php the_ID(); ?> -->
		<?php comments_template( '', true ); ?>
			
	<?php endwhile;
	endif;
	
	

	
	//Reset Query
	wp_reset_query();
	
?>