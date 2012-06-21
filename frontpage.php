<?php
global $wpdb;

@include_once 'commons.php';

//Las opciones frontpage_view guardan las opciones de la portada que se está previsualizando, no la real
$options = get_option('frontpage_view');
$options_frontpage = get_option('frontpage');

//Las opciones frontpage_type guardan las opciones de la portada real, no las que se están previsualizando
$real_options = get_option('frontpage_type');

$the_positions = array(0 => 'positionM', 1 => 'positionM2', 2 => 'positionL1', 3 => 'positionL2', 4 => 'positionL3', 5 => 'positionL4', 6 => 'positionR1', 7 => 'positionR2', 8 => 'positionR3', 9 => 'positionR4', 10 => 'positionL5', 11 => 'positionL6', 12 => 'positionR5', 13 => 'positionR6');

$the_photos = array(0 => 'fotoM', 1 => 'fotoM2', 2 => 'fotoL1', 3 => 'fotoL2', 4 => 'fotoL3', 5 => 'fotoL4', 6 => 'fotoR1', 7 => 'fotoR2', 8 => 'fotoR3', 9 => 'fotoR4', 10 => 'fotoL5', 11 => 'fotoL6', 12 => 'fotoR5', 13 => 'fotoR6');

//Aquí se entra si han pulsado el botón 'Save'
//Hacemos todas las comprobaciones antes de grabar en la BD la nueva portada
if (isset($_POST['save']) && check_admin_referer()) {
    $options = stripslashes_deep($_POST['options']);
    
    //Miramos cuál es el último id de portada, para que la nueva portada tenga el id siguiente (no es autoincrement, ya que son dos claves primarias: idPortada y idEntrada)
    $id_ultima_portada = $wpdb->get_var("SELECT idPortada FROM wp_frontpage ORDER BY idPortada DESC LIMIT 1");
    $id_nueva_portada = $id_ultima_portada + 1;
    $i = 0;
    $hay_articulos_no_publicados = false;
    
    $the_estados = array(0 => 'estadoM', 1 => 'estadoM2', 2 => 'estadoL1', 3 => 'estadoL2', 4 => 'estadoL3', 5 => 'estadoL4', 6 => 'estadoR1', 7 => 'estadoR2', 8 => 'estadoR3', 9 => 'estadoR4', 10 => 'estadoL5', 11 => 'estadoL6', 12 => 'estadoR5', 13 => 'estadoR6');
    
    //Comprobamos antes de nada, que no hay en la portada ningún artículo repetido
    //En caso de haberlo deberá saltar un mensaje de error.
    $cont1 = 0;
    $hay_articulos_repetidos = false;
    while(($cont1 <= 13) && (!$hay_articulos_repetidos)):
    	$the_post = $options[$the_positions[$cont1]];
    	$cont2 = $cont1 + 1;
    	
    	while($cont2 <= 13):
    		$aux = $options[$the_positions[$cont2]];
    		if($the_post != ''):
    			if($the_post == $aux):
    				$hay_articulos_repetidos = true;
    			endif;
    		endif;
    		$cont2++;
    	endwhile;
    	$cont1++;
    endwhile;
    
    if(!$hay_articulos_repetidos):
    	
    	//Comprobamos todos los artículos antes de añadirlos:
   		//Con que haya un artículo como borrador, etc, la portada no deberá guradarse en la BD
    	$cont = 0;
    	while(($cont <= 13) && (!$hay_articulos_no_publicados)):
    		
    		$status = $options[$the_estados[$cont]];
    		
    		if(($status != 'Publicado') && ($status != 'Sin definir')):
    			$hay_articulos_no_publicados = true;
    		endif;
    		$cont++;
    	
    	endwhile;//while(($cont <= 9) && (!$hay_articulos_no_publicados)):
    	
    	//Antes de publicar la portada tenemos que comprobar que ninguno de los artículos
    	//es un borrador o privado, etc.
    	if(!$hay_articulos_no_publicados):
    		while($i <= 13):
    			$the_position = $the_positions[$i];
    			$the_status = $the_estados[$i];
    	
    			$the_post = $options[$the_position];
    			$status = $options[$the_status];
    			$fecha = date('Y-m-d H:i:s');
    	
    			//También guardamos en la BD el tipo de portada: Normal o Especial
    			if($real_options['theme'] == 'infolatam-portada-normal'):
    				$esPortadaEspecial = 'N';
    			else:
    				$esPortadaEspecial = 'Y';
    			endif;
    	
    			if(($status == 'Publicado') || ($status == 'Sin definir')):
    				//Si en esa posición han puesto un artículo, entonces, lo insertamos en la BD junto al id de portada, fecha, id del post y posición en la portada
    				if($the_post != ''):
    		
    					$wpdb->query( $wpdb->prepare( "INSERT INTO wp_frontpage (idPortada, idEntrada, fecha, orden, portadaEspecial) VALUES ( %d, %d, %s, %d, %s )", $id_nueva_portada, $the_post, $fecha, $i+1, $esPortadaEspecial) );
    					//update_option('frontpage_view', $options);

    				endif;
    			endif;
		
    			$i++;
    	
  			endwhile;
  		endif;
  		
  		//Si le ha dado a salvar una portada que tiene algún borrador, etc. saldrá un mensaje de error.
  		//Recargamos la página de portada en caso de que se haya rechazado la portada porque hubiera borradores o privados
  		if($hay_articulos_no_publicados): ?>
  			<script language=Javascript>
				alert('No puedes publicar esta portada porque alguno de los artículos es un borrador, privado, etc. Antes debes publicarlos y después crear la portada.');
				window.location = "<?php bloginfo('home'); ?>/wp-admin/admin.php?page=frontpage/frontpage.php";
			</script>
			<?php
	
		else: 
			//Si todos los artículos están publicados y la portada es válida, entones es cuando se graba el tipo de portada que aparecerá en la página principal
			//La portada de Infolatam mostrará el tipo de portada que indique frontpage_type, ya que si miráramos frontpage_view cambiaría la portada cada vez que hacemos Auto compose
    		update_option('frontpage_type', $options);
		
  		endif;
    
    //En el caso de que haya artículos repetidos mostraremos un mensaje de error
    else: 
    	//Recargamos la página de portada en caso de que se haya rechazado la portada porque hubiera artículos repetidos. ?> 
    	<script language=Javascript>
			alert('No puedes publicar esta portada porque algún artículo está repetido.');
			window.location = "<?php bloginfo('home'); ?>/wp-admin/admin.php?page=frontpage/frontpage.php";
		</script>
		<?php	
    endif;//if(!$hay_articulos_repetidos)
  	
}

// Auto composition
if (isset($_POST['auto']) && isset($_POST['options']) && check_admin_referer()) {
// Load the theme
    
     
    $ids[0] = stripslashes_deep($_POST['idM']);
    $ids[1] = stripslashes_deep($_POST['idM2']);
    $ids[2] = stripslashes_deep($_POST['idL1']);
    $ids[3] = stripslashes_deep($_POST['idL2']);
    $ids[4] = stripslashes_deep($_POST['idL3']);
    $ids[5] = stripslashes_deep($_POST['idL4']);
    $ids[6] = stripslashes_deep($_POST['idR1']);
    $ids[7] = stripslashes_deep($_POST['idR2']);
    $ids[8] = stripslashes_deep($_POST['idR3']);
    $ids[9] = stripslashes_deep($_POST['idR4']);
    $ids[10] = stripslashes_deep($_POST['idL5']);
    $ids[11] = stripslashes_deep($_POST['idL6']);
    $ids[12] = stripslashes_deep($_POST['idR5']);
    $ids[13] = stripslashes_deep($_POST['idR6']);
    
    update_option('frontpage_postsids', $ids);
    
    //Antes de grabar las opciones, si han metido alguna de las noticias por ID, hay que cargarlo en su posición en el array options antes de grabarlo en la BD
    $options = stripslashes_deep($_POST['options']);
    
    $i = 0;
    while($i <= 13){
    	//Si han seleccionado la opción: 'Select a post ID' y han escrito algo en la caja para el ID, entonces que lo guarde en el array de options
    	if($options[$the_positions[$i]] == 'ID'){
    		if($ids[$i] != '-ID-'){
    			$options[$the_positions[$i]] = $ids[$i];
    		}
    	}
    	$i++;
    }
    
    //Ya podemos grabar las opciones en la BD, ya cada posición tiene el id del post que hayan puesto, seleccionandolo con el select o poniéndolo a mano.
	update_option('frontpage_view', $options);
	
    $file = frontpage_get_theme_dir($options['theme']) . '/theme.php';
    
    // Execute the theme file and get the content generated
    ob_start();
    @include($file);
    $options['message'] = ob_get_contents();
    ob_end_clean();

    if ($options['novisual']) {
        $options['message'] = "<html>\n<head>\n<style type=\"text/css\">\n" . frontpage_get_theme_css($options_email['theme']) .
            "\n</style>\n</head>\n<body>\n" . $options['message'] . "\n</body>\n</html>";
    }
}

/* Modificación Mecus ?>
// Reset the batch
if (isset($_POST['reset']) && check_admin_referer()) {
    frontpage_delete_batch_file();
    wp_clear_scheduled_hook('frontpage_cron_hook');
    delete_option('frontpage_batch', array());
}

if (isset($_POST['scheduled_simulate']) && check_admin_referer()) {
    $options = stripslashes_deep($_POST['options']);
    update_option('frontpage_view', $options);
    frontpage_send_scheduledsdafsd(0, true);
}

if (isset($_POST['scheduled_send']) && check_admin_referer()) {
    $options = stripslashes_deep($_POST['options']);
    update_option('frontpage_view', $options);
    frontpage_send_scheduleddsfadsa(0, false);
}


if (isset($_POST['restore']) && check_admin_referer()) {
    $batch = frontpage_load_batch_file();
    update_option('frontpage_batch', $batch);
    frontpage_delete_batch_file();
}
<?php } */

// Theme style

$css_url = null;
$theme_dir = frontpage_get_theme_dir($options['theme']);
if (file_exists($theme_dir . '/style.css')) {
    $css_url = frontpage_get_theme_url($options['theme']) . '/style.css';
}

$nc = new frontpage_frontpageControls($options, 'composer');

?>
<?php if (!isset($options['novisual'])) { ?>
<script type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/frontpage/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        mode : "textareas",
        theme : "advanced",
        plugins: "table,fullscreen",
        theme_advanced_disable : "styleselect",
        theme_advanced_buttons1_add: "forecolor,blockquote,code",
        theme_advanced_buttons3 : "tablecontrols,fullscreen",
        relative_urls : false,
        remove_script_host : false,
        theme_advanced_toolbar_location : "top",
        document_base_url : "<?php echo get_option('home'); ?>/"
    <?php
    if ($css_url != null) {
        echo ', content_css: "' . $css_url . '?' . time() . '"';
    }
    ?>
        });
</script>
<?php } ?>

<div class="wrap">

    <h2><?php echo __('Frontpage Composer', 'frontpage'); ?></h2>
    
    <?php if (!touch(dirname(__FILE__) . '/test.tmp')) { ?>
    <div class="error fade" style="background-color:red;"><p><strong><?php echo __("It seems that frontpage plugin folder is not writable. Make it writable to let
                frontpage write logs and save date when errors occour.", 'frontpage'); ?></strong></p>
    </div>
    <?php } ?>

    <?php //require_once 'header.php'; ?>

    <form method="post" action="">
        <?php wp_nonce_field(); ?>
		
		<?php /* Modificación Mecus ?>
        <?php if (isset($_POST['restart']) && check_admin_referer()) { ?>

        <h3><?php echo __('Continuing with previous batch', 'frontpage'); ?></h3>
        <div class="form-table">
                <?php
                $options = stripslashes_deep($_POST['options']);
                update_option('frontpage_view', $options);
                $batch = get_option('frontpage_batch');

                if (defined('frontpage_EXTRAS') && $batch['scheduled']) {
                	frontpage_cron_task();
                }
                else {
                    frontpage_send_batch();
                }
                ?>
        </div>

        <?php } ?>


        <?php if (isset($_POST['simulate']) && check_admin_referer()) { ?>

        <h3>Simulation</h3>
        <div class="form-table">
                <?php
                $options = stripslashes_deep($_POST['options']);
                update_option('frontpage_view', $options);
                $batch = array();
                $batch['id'] = 0;
                $batch['list'] = 0;
                $batch['scheduled'] = false;
                $batch['simulate'] = true;

                update_option('frontpage_batch', $batch);

                frontpage_send_batch();
                ?>
        </div>

        <?php } ?>



        <?php if (isset($_REQUEST['send']) && check_admin_referer()) { ?>

        <h3>Sending</h3>
        <div class="form-table">
                <?php
                $options = stripslashes_deep($_POST['options']);
                update_option('frontpage_view', $options);
                $batch = array();
                $batch['id'] = 0;
                $batch['list'] = 0;
                $batch['scheduled'] = false;
                $batch['simulate'] = false;

                update_option('frontpage_batch', $batch);

                frontpage_send_batch();
                ?>
        </div>

        <?php } ?>



        <?php  if (isset($_POST['test']) && check_admin_referer()) { ?>

        <h3><?php echo __('Testing the front page','frontpage'); ?></h3>
        <div class="form-table">
                <?php
                $options = stripslashes_deep($_POST['options']);
                update_option('frontpage_view', $options);
                /*$subscribers = array();
                for ($i=1; $i<=10; $i++) {
                    if (!$options['test_email_' . $i]) continue;
                    $subscribers[$i-1]->name = $options['test_name_' . $i];
                    $subscribers[$i-1]->email = $options['test_email_' . $i];
                    $subscribers[$i-1]->token = 'FAKETOKEN';
                }
                //frontpage_send_test($subscribers);
                ?>
       </div>

        <?php }*/ ?>


		
        <?php /* Modificación Mecus
        $batch_file = frontpage_load_batch_file();
        if ($batch_file != null) {
            ?>
        <h3><?php echo __('Warning!!!', 'frontpage'); ?></h3>
        <p><?php echo __('There is a batch saved to disk. That means an error occurred while sending.
            Would you try to restore
            that batch?', 'frontpage'); ?><br />
            <input class="button" type="submit" name="restore" value="Restore batch data"  onclick="return confirm('Restore batch data?')"/>
        </p>
        <?php } ?>

        
        <h3>Batch info</h3>

        <?php $batch = get_option('frontpage_batch'); ?>
        <?php if (!is_array($batch) || empty($batch)) { ?>

        <p><strong>No batch info found, it's ok!</strong></p>

        <?php } else { ?>

        <table class="form-table">
            <tr>
                <th>Status</th>
                <td>
                        <?php
                        if ($batch['scheduled']) {

                            if ($batch['completed']) echo 'Completed';
                            else {
                                $time = wp_next_scheduled('frontpage_cron_hook');
                                if ($time == 0) {
                                    echo 'Not completed but no next run found (errors?)';
                                }
                                else {
                                    echo 'Not completed, next run on ' . date('j/m/Y h:i', $time);
                                    echo ' (' . ((int)(($time-time())/60)) . ' minutes left)';
                                }
                            }
                        }
                        else {
                            if ($batch['completed']) echo 'Completed';
                            else echo 'Not completed (you should restart it)';
                        }
                        ?>
                    <br />
                    <?php echo $batch['message']; ?>
                </td>
            </tr>
            <tr>
                <th>Emails sent/total</th>
                <td><?php echo $batch['sent']; ?>/<?php echo $batch['total']; ?> (last id: <?php echo $batch['id']; ?>)</td>
            </tr>
            <!--
            <tr>
                <td>List</td>
                <td><?php echo $batch['list']; ?></td>
            </tr>
            -->
            <tr>
                <th>Sending type</th>
                <td><?php echo $batch['simulate']?"Simluation":"Real"; ?>/<?php echo $batch['scheduled']?"Scheduled":"Not scheduled"; ?></td>
            </tr>
        </table>

        <p class="submit">
                <?php if (!$batch['completed']) { ?>
            <input class="button" type="submit" name="restart" value="Restart batch"  onclick="return confirm('Continue with this batch?')"/>
                <?php } ?>
            <input class="button" type="submit" name="reset" value="Reset batch"  onclick="return confirm('Reset the batch status?')"/>
        </p>

        <?php } ?>
		<?php */ ?>


        <h3><?php echo __('Frontpage Preview', 'frontpage'); ?></h3>

        <table class="form-table">
            
            <?php 
            global $wpdb;
	
			$frontpage_posts = new WP_Query("cat=-222&orderby=date&order=DESC&showposts=100");
            
            /* Modificación Mecus ?>
            <tr valign="top">
                <th>frontpage name and tracking</th>
            <?php if (defined('frontpage_EXTRAS')) { ?>
                <td>
                    <input name="options[name]" type="text" size="25" value="<?php echo htmlspecialchars($options['name'])?>"/>
                    <input name="options[track]" value="1" type="checkbox" <?php echo $options['track']?'checked':''; ?>/>
                    Track link clicks
                    <br />
                    When this option is enabled, each link in the email text will be rewritten and clicks
                    on them intercepted.
                    The symbolic name will be used to track the link clicks and associate them to a specific frontpage.
                    Keep the name compact and significative.
                </td>

            <?php } else { ?>
                <td>Tracking options available with frontpage Extras package</td>
            <?php } ?>
            </tr>
            

            <tr valign="top">
                <th><?php echo __('Subject', 'frontpage'); ?></th>
                <td>
                    <?php $nc->frontpage_text('subject', 70); ?>
                    <br />
                    <?php _e('Tags: <strong>{name}</strong> receiver name.', 'frontpage'); ?>
                </td>
            </tr>
            <?php */ ?>
            
            <br />
            <tr valign="top">
            	<th><?php echo __('Main new', 'frontpage'); ?></th>
            	<td>
            		<select id="options[positionM]" name="options[positionM]" tabindex="2" onChange="if(this.value == 'ID'){ this.form.idM.disabled = false; }else{ this.form.idM.disabled = true;}">
						<option value="<?php if($options['positionM'] == ''): echo ''; else: echo $options['positionM']; endif; ?>"><?php if($options['positionM'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionM = get_post($options['positionM']); echo $post_positionM->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} 
						?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idM" id="idM" type="text" value="-ID-" size="6" disabled="disabled" tabindex="3" />
					
					<select id="options[fotoM]" name="options[fotoM]" tabindex="4">
						<option value="con-foto" <?php if($options['fotoM'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoM'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idM = $options['positionM'];
						$post_typeM = get_post_status($post_type_idM);
						
						if($post_type_idM){
							$post_typeM = get_post_status($post_type_idM);
						}else{
							$post_typeM = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeM){
							case 'auto-draft':
								$estadoM = 'Auto-borrador';
								break;
							case 'draft':
								$estadoM = 'Borrador';
								break;
							case 'inherit':
								$estadoM = 'Adjunto';
								break;
							case 'publish':
								$estadoM = 'Publicado';
								break;
							case 'trash':
								$estadoM = 'En la papelera';
								break;
							default:
								$estadoM = 'Sin definir';
								break;
						}
						?><strong <?php if($estadoM == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoM == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoM.'</strong>';
					?>
					<input name="options[estadoM]" type="hidden" value="<?php echo $estadoM; ?>"/>
            	</td>
            </tr>
            
            <!-- 
            	En la portada especial sólo se muestran 1 noticia principal, por lo tanto la siguiente sólo es para la portada normal
            -->
            <?php if($options['theme'] == 'infolatam-portada-normal'): ?>
            
            <tr valign="top">
            	<th><?php echo __('Main new #2', 'frontpage'); ?></th>
            	<td>
            		<select id="options[positionM2]" name="options[positionM2]" tabindex="5" onChange="if(this.value == 'ID'){ this.form.idM2.disabled = false; }else{ this.form.idM2.disabled = true;}">
						<option value="<?php if($options['positionM2'] == ''): echo ''; else: echo $options['positionM2']; endif; ?>"><?php if($options['positionM2'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionM2 = get_post($options['positionM2']); echo $post_positionM2->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} 
						?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idM2" id="idM2" type="text" value="-ID-" size="6" disabled="disabled" tabindex="6" />
					
					<select id="options[fotoM2]" name="options[fotoM2]" tabindex="7">
						<option value="con-foto" <?php if($options['fotoM2'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoM2'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idM2 = $options['positionM2'];
						$post_typeM2 = get_post_status($post_type_idM2);
						
						if($post_type_idM2){
							$post_typeM2 = get_post_status($post_type_idM2);
						}else{
							$post_typeM2 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeM2){
							case 'auto-draft':
								$estadoM2 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoM2 = 'Borrador';
								break;
							case 'inherit':
								$estadoM2 = 'Adjunto';
								break;
							case 'publish':
								$estadoM2 = 'Publicado';
								break;
							case 'trash':
								$estadoM2 = 'En la papelera';
								break;
							default:
								$estadoM2 = 'Sin definir';
								break;
						}
						?><strong <?php if($estadoM2 == 'Publicado'): echo 'style="background-color:#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoM2 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoM2.'</strong>';
					?>
					<input name="options[estadoM2]" type="hidden" value="<?php echo $estadoM2; ?>"/>
            	</td>
            </tr>
            
            <?php else: ?>
            		<input name="options[estadoM2]" type="hidden" value="Publicado"/>           
            <?php endif; ?>
            
            <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Left column, new #1', 'frontpage'); else: echo __('Análisis principal', 'frontpage'); endif; ?></th>
            	<td>
            		<select id="options[positionL1]" name="options[positionL1]" tabindex="8" onChange="if(this.value == 'ID'){ this.form.idL1.disabled = false; }else{ this.form.idL1.disabled = true;}">
						<option value="<?php if($options['positionL1'] == ''): echo ''; else: echo $options['positionL1']; endif; ?>"><?php if($options['positionL1'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionL1 = get_post($options['positionL1']); echo $post_positionL1->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idL1" id="idL1" type="text" value="-ID-" size="6" disabled="disabled" tabindex="9" />
					
					<select id="options[fotoL1]" name="options[fotoL1]" tabindex="10">
						<option value="con-foto" <?php if($options['fotoL1'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoL1'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idL1 = $options['positionL1'];
						$post_typeL1 = get_post_status($post_type_idL1);
						
						if($post_type_idL1){
							$post_typeL1 = get_post_status($post_type_idL1);
						}else{
							$post_typeL1 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeL1){
							case 'auto-draft':
								$estadoL1 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoL1 = 'Borrador';
								break;
							case 'inherit':
								$estadoL1 = 'Adjunto';
								break;
							case 'publish':
								$estadoL1 = 'Publicado';
								break;
							case 'trash':
								$estadoL1 = 'En la papelera';
								break;
							default:
								$estadoL1 = 'Sin definir';
								break;
						}
						?><strong <?php if($estadoL1 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoL1 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoL1.'</strong>';
					?>
					<input name="options[estadoL1]" type="hidden" value="<?php echo $estadoL1; ?>"/>
            	</td>
            </tr>
            
            <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Left column, new #2', 'frontpage'); else: echo __('New #1', 'frontpage'); endif;?></th>
            	<td>
            		<select id="options[positionL2]" name="options[positionL2]" tabindex="11" onChange="if(this.value == 'ID'){ this.form.idL2.disabled = false; }else{ this.form.idL2.disabled = true;}">
						<option value="<?php if($options['positionL2'] == ''): echo ''; else: echo $options['positionL2']; endif; ?>"><?php if($options['positionL2'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionL2 = get_post($options['positionL2']); echo $post_positionL2->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idL2" id="idL2" type="text" value="-ID-" size="6" disabled="disabled" tabindex="12" />
					
					<select id="options[fotoL2]" name="options[fotoL2]" tabindex="13">
						<option value="con-foto" <?php if($options['fotoL2'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoL2'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idL2 = $options['positionL2'];
						$post_typeL2 = get_post_status($post_type_idL2);
						
						if($post_type_idL2){
							$post_typeL2 = get_post_status($post_type_idL2);
						}else{
							$post_typeL2 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeL2){
							case 'auto-draft':
								$estadoL2 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoL2 = 'Borrador';
								break;
							case 'inherit':
								$estadoL2 = 'Adjunto';
								break;
							case 'publish':
								$estadoL2 = 'Publicado';
								break;
							case 'trash':
								$estadoL2 = 'En la papelera';
								break;
							default:
								$estadoL2 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoL2 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoL2 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoL2.'</strong>';
					?>
					<input name="options[estadoL2]" type="hidden" value="<?php echo $estadoL2; ?>"/>
            	</td>
            </tr>
            
            <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Left column, new #3', 'frontpage'); else: echo __('New #2', 'frontpage'); endif;?></th>
            	<td>
            		<select id="options[positionL3]" name="options[positionL3]" tabindex="14" onChange="if(this.value == 'ID'){ this.form.idL3.disabled = false; }else{ this.form.idL3.disabled = true;}">
						<option value="<?php if($options['positionL3'] == ''): echo ''; else: echo $options['positionL3']; endif; ?>"><?php if($options['positionL3'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionL3 = get_post($options['positionL3']); echo $post_positionL3->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idL3" id="idL3" type="text" value="-ID-" size="6" disabled="disabled" tabindex="15" />
					
					<select id="options[fotoL3]" name="options[fotoL3]" tabindex="16">
						<option value="con-foto" <?php if($options['fotoL3'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoL3'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idL3 = $options['positionL3'];
						$post_typeL3 = get_post_status($post_type_idL3);
						
						if($post_type_idL3){
							$post_typeL3 = get_post_status($post_type_idL3);
						}else{
							$post_typeL3 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeL3){
							case 'auto-draft':
								$estadoL3 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoL3 = 'Borrador';
								break;
							case 'inherit':
								$estadoL3 = 'Adjunto';
								break;
							case 'publish':
								$estadoL3 = 'Publicado';
								break;
							case 'trash':
								$estadoL3 = 'En la papelera';
								break;
							default:
								$estadoL3 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoL3 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoL3 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoL3.'</strong>';
					?>
					<input name="options[estadoL3]" type="hidden" value="<?php echo $estadoL3; ?>"/>
            	</td>
            </tr>
            
            <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Left column, new #4', 'frontpage'); else: echo __('New #3', 'frontpage'); endif;?></th>
            	<td>
            		<select id="options[positionL4]" name="options[positionL4]" tabindex="17" onChange="if(this.value == 'ID'){ this.form.idL4.disabled = false; }else{ this.form.idL4.disabled = true;}">
						<option value="<?php if($options['positionL4'] == ''): echo ''; else: echo $options['positionL4']; endif; ?>"><?php if($options['positionL4'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionL4 = get_post($options['positionL4']); echo $post_positionL4->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idL4" id="idL4" type="text" value="-ID-" size="6" disabled="disabled" tabindex="18" />
					
					<select id="options[fotoL4]" name="options[fotoL4]" tabindex="19">
						<option value="con-foto" <?php if($options['fotoL4'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoL4'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idL4 = $options['positionL4'];
						$post_typeL4 = get_post_status($post_type_idL4);
						
						if($post_type_idL4){
							$post_typeL4 = get_post_status($post_type_idL4);
						}else{
							$post_typeL4 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeL4){
							case 'auto-draft':
								$estadoL4 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoL4 = 'Borrador';
								break;
							case 'inherit':
								$estadoL4 = 'Adjunto';
								break;
							case 'publish':
								$estadoL4 = 'Publicado';
								break;
							case 'trash':
								$estadoL4 = 'En la papelera';
								break;
							default:
								$estadoL4 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoL4 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoL4 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoL4.'</strong>';
					?>
					<input name="options[estadoL4]" type="hidden" value="<?php echo $estadoL4; ?>"/>
            	</td>
            </tr>
            
             <!-- 
            	En la portada especial sólo se muestran 5 noticias, por lo tanto las siguientes sólo son para la portada normal
            -->
            <?php if($options['theme'] == 'infolatam-portada-normal'): ?>
             <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Left column, new #5', 'frontpage'); else: echo __('New #5', 'frontpage'); endif;?></th>
            	<td>
            		<select id="options[positionL5]" name="options[positionL5]" tabindex="20" onChange="if(this.value == 'ID'){ this.form.idL5.disabled = false; }else{ this.form.idL5.disabled = true;}">
						<option value="<?php if($options['positionL5'] == ''): echo ''; else: echo $options['positionL5']; endif; ?>"><?php if($options['positionL5'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionL5 = get_post($options['positionL5']); echo $post_positionL5->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idL5" id="idL5" type="text" value="-ID-" size="6" disabled="disabled" tabindex="21" />
					
					<select id="options[fotoL5]" name="options[fotoL5]" tabindex="22">
						<option value="con-foto" <?php if($options['fotoL5'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoL5'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idL5 = $options['positionL5'];
						$post_typeL5 = get_post_status($post_type_idL5);
						
						if($post_type_idL5){
							$post_typeL5 = get_post_status($post_type_idL5);
						}else{
							$post_typeL5 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeL5){
							case 'auto-draft':
								$estadoL5 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoL5 = 'Borrador';
								break;
							case 'inherit':
								$estadoL5 = 'Adjunto';
								break;
							case 'publish':
								$estadoL5 = 'Publicado';
								break;
							case 'trash':
								$estadoL5 = 'En la papelera';
								break;
							default:
								$estadoL5 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoL5 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoL5 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoL5.'</strong>';
					?>
					<input name="options[estadoL5]" type="hidden" value="<?php echo $estadoL5; ?>"/>
            	</td>
            </tr>
            
             <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Left column, new #6', 'frontpage'); else: echo __('New #6', 'frontpage'); endif;?></th>
            	<td>
            		<select id="options[positionL6]" name="options[positionL6]" tabindex="23" onChange="if(this.value == 'ID'){ this.form.idL6.disabled = false; }else{ this.form.idL6.disabled = true;}">
						<option value="<?php if($options['positionL6'] == ''): echo ''; else: echo $options['positionL6']; endif; ?>"><?php if($options['positionL6'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionL6 = get_post($options['positionL6']); echo $post_positionL6->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idL6" id="idL6" type="text" value="-ID-" size="6" disabled="disabled" tabindex="24" />
					
					<select id="options[fotoL6]" name="options[fotoL6]" tabindex="25">
						<option value="con-foto" <?php if($options['fotoL6'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoL6'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idL6 = $options['positionL6'];
						$post_typeL6 = get_post_status($post_type_idL6);
						
						if($post_type_idL6){
							$post_typeL6 = get_post_status($post_type_idL6);
						}else{
							$post_typeL6 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeL6){
							case 'auto-draft':
								$estadoL6 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoL6 = 'Borrador';
								break;
							case 'inherit':
								$estadoL6 = 'Adjunto';
								break;
							case 'publish':
								$estadoL6 = 'Publicado';
								break;
							case 'trash':
								$estadoL6 = 'En la papelera';
								break;
							default:
								$estadoL6 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoL6 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoL6 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoL6.'</strong>';
					?>
					<input name="options[estadoL6]" type="hidden" value="<?php echo $estadoL6; ?>"/>
            	</td>
            </tr>

            
           
             <tr valign="top">
            	<th><?php echo __('Right column, new #1', 'frontpage'); ?></th>
            	<td>
            		<select id="options[positionR1]" name="options[positionR1]" tabindex="30" onChange="if(this.value == 'ID'){ this.form.idR1.disabled = false; }else{ this.form.idM.disabled = true;}">
						<option value="<?php if($options['positionR1'] == ''): echo ''; else: echo $options['positionR1']; endif; ?>"><?php if($options['positionR1'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionR1 = get_post($options['positionR1']); echo $post_positionR1->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idR1" id="idR1" type="text" value="-ID-" size="6" disabled="disabled" tabindex="31" />
					
					<select id="options[fotoR1]" name="options[fotoR1]" tabindex="32">
						<option value="con-foto" <?php if($options['fotoR1'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoR1'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idR1 = $options['positionR1'];
						$post_typeR1 = get_post_status($post_type_idR1);
						
						if($post_type_idR1){
							$post_typeR1 = get_post_status($post_type_idR1);
						}else{
							$post_typeR1 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeR1){
							case 'auto-draft':
								$estadoR1 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoR1 = 'Borrador';
								break;
							case 'inherit':
								$estadoR1 = 'Adjunto';
								break;
							case 'publish':
								$estadoR1 = 'Publicado';
								break;
							case 'trash':
								$estadoR1 = 'En la papelera';
								break;
							default:
								$estadoR1 = 'Sin definir';
								break;
						}
						?><strong <?php if($estadoR1 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoR1 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoR1.'</strong>';
					?>
					<input name="options[estadoR1]" type="hidden" value="<?php echo $estadoR1; ?>"/>
            	</td>
            </tr>
            
            <tr valign="top">
            	<th><?php echo __('Right column, new #2', 'frontpage'); ?></th>
            	<td>
            		<select id="options[positionR2]" name="options[positionR2]" tabindex="33" onChange="if(this.value == 'ID'){ this.form.idR2.disabled = false; }else{ this.form.idR2.disabled = true;}">
						<option value="<?php if($options['positionR2'] == ''): echo ''; else: echo $options['positionR2']; endif; ?>"><?php if($options['positionR2'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionR2 = get_post($options['positionR2']); echo $post_positionR2->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idR2" id="idR2" type="text" value="-ID-" size="6" disabled="disabled" tabindex="34" />
					
					<select id="options[fotoR2]" name="options[fotoR2]" tabindex="35">
						<option value="con-foto" <?php if($options['fotoR2'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoR2'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idR2 = $options['positionR2'];
						$post_typeR2 = get_post_status($post_type_idR2);
						
						if($post_type_idR2){
							$post_typeR2 = get_post_status($post_type_idR2);
						}else{
							$post_typeR2 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeR2){
							case 'auto-draft':
								$estadoR2 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoR2 = 'Borrador';
								break;
							case 'inherit':
								$estadoR2 = 'Adjunto';
								break;
							case 'publish':
								$estadoR2 = 'Publicado';
								break;
							case 'trash':
								$estadoR2 = 'En la papelera';
								break;
							default:
								$estadoR2 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoR2 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoR2 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoR2.'</strong>';
					?>
					<input name="options[estadoR2]" type="hidden" value="<?php echo $estadoR2; ?>"/>
            	</td>
            </tr>
            
            <tr valign="top">
            	<th><?php echo __('Right column, new #3', 'frontpage'); ?></th>
            	<td>
            		<select id="options[positionR3]" name="options[positionR3]" tabindex="36" onChange="if(this.value == 'ID'){ this.form.idR3.disabled = false; }else{ this.form.idR3.disabled = true;}">
						<option value="<?php if($options['positionR3'] == ''): echo ''; else: echo $options['positionR3']; endif; ?>"><?php if($options['positionR3'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionR3 = get_post($options['positionR3']); echo $post_positionR3->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idR3" id="idR3" type="text" value="-ID-" size="6" disabled="disabled" tabindex="37" />
					
					<select id="options[fotoR3]" name="options[fotoR3]" tabindex="38">
						<option value="con-foto" <?php if($options['fotoR3'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoR3'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idR3 = $options['positionR3'];
						$post_typeR3 = get_post_status($post_type_idR3);
						
						if($post_type_idR3){
							$post_typeR3 = get_post_status($post_type_idR3);
						}else{
							$post_typeR3 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeR3){
							case 'auto-draft':
								$estadoR3 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoR3 = 'Borrador';
								break;
							case 'inherit':
								$estadoR3 = 'Adjunto';
								break;
							case 'publish':
								$estadoR3 = 'Publicado';
								break;
							case 'trash':
								$estadoR3 = 'En la papelera';
								break;
							default:
								$estadoR3 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoR3 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoR3 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoR3.'</strong>';
					?>
					<input name="options[estadoR3]" type="hidden" value="<?php echo $estadoR3; ?>"/>
            	</td>
            </tr>
            
            <tr valign="top">
            	<th><?php echo __('Right column, new #4', 'frontpage'); ?></th>
            	<td>
            		<select id="options[positionR4]" name="options[positionR4]" tabindex="39" onChange="if(this.value == 'ID'){ this.form.idR4.disabled = false; }else{ this.form.idR4.disabled = true;}">
						<option value="<?php if($options['positionR4'] == ''): echo ''; else: echo $options['positionR4']; endif; ?>"><?php if($options['positionR4'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionR4 = get_post($options['positionR4']); echo $post_positionR4->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idR4" id="idR4" type="text" value="-ID-" size="6" disabled="disabled" tabindex="40" />
					
					<select id="options[fotoR4]" name="options[fotoR4]" tabindex="41">
						<option value="con-foto" <?php if($options['fotoR4'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoR4'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idR4 = $options['positionR4'];
						
						if($post_type_idR4){
							$post_typeR4 = get_post_status($post_type_idR4);
						}else{
							$post_typeR4 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						
						switch ($post_typeR4){
							case 'auto-draft':
								$estadoR4 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoR4 = 'Borrador';
								break;
							case 'inherit':
								$estadoR4 = 'Adjunto';
								break;
							case 'publish':
								$estadoR4 = 'Publicado';
								break;
							case 'trash':
								$estadoR4 = 'En la papelera';
								break;
							default:
								$estadoR4 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoR4 == 'Publicado'): echo 'style="background-color:#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoR4 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoR4.'</strong>';
					?>
					<input name="options[estadoR4]" type="hidden" value="<?php echo $estadoR4; ?>"/>
            	</td>
            </tr>
            
             <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Right column, new #5', 'frontpage'); else: echo __('New #5', 'frontpage'); endif;?></th>
            	<td>
            		<select id="options[positionR5]" name="options[positionR5]" tabindex="42" onChange="if(this.value == 'ID'){ this.form.idR5.disabled = false; }else{ this.form.idR5.disabled = true;}">
						<option value="<?php if($options['positionR5'] == ''): echo ''; else: echo $options['positionR5']; endif; ?>"><?php if($options['positionR5'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionR5 = get_post($options['positionR5']); echo $post_positionR5->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idR5" id="idR5" type="text" value="-ID-" size="6" disabled="disabled" tabindex="43" />
					
					<select id="options[fotoR5]" name="options[fotoR5]" tabindex="44">
						<option value="con-foto" <?php if($options['fotoR5'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoR5'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idR5 = $options['positionR5'];
						$post_typeR5 = get_post_status($post_type_idR5);
						
						if($post_type_idR5){
							$post_typeR5 = get_post_status($post_type_idR5);
						}else{
							$post_typeR5 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeR5){
							case 'auto-draft':
								$estadoR5 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoR5 = 'Borrador';
								break;
							case 'inherit':
								$estadoR5 = 'Adjunto';
								break;
							case 'publish':
								$estadoR5 = 'Publicado';
								break;
							case 'trash':
								$estadoR5 = 'En la papelera';
								break;
							default:
								$estadoR5 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoR5 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoR5 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoR5.'</strong>';
					?>
					<input name="options[estadoR5]" type="hidden" value="<?php echo $estadoR5; ?>"/>
            	</td>
            </tr>

			 <tr valign="top">
            	<th><?php if($options['theme'] == 'infolatam-portada-normal'): echo __('Right column, new #6', 'frontpage'); else: echo __('New #6', 'frontpage'); endif;?></th>
            	<td>
            		<select id="options[positionR6]" name="options[positionR6]" tabindex="45" onChange="if(this.value == 'ID'){ this.form.idR6.disabled = false; }else{ this.form.idR6.disabled = true;}">
						<option value="<?php if($options['positionR6'] == ''): echo ''; else: echo $options['positionR6']; endif; ?>"><?php if($options['positionR6'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_positionR6 = get_post($options['positionR6']); echo $post_positionR6->post_title; endif; ?></option>
						<option value="">No post</option>
						<?php while ( $frontpage_posts->have_posts() )
						{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
						} ?>
						<option value="ID">Select a post ID</option>
					</select>
					
					<input name="idR6" id="idR6" type="text" value="-ID-" size="6" disabled="disabled" tabindex="46" />
					
					<select id="options[fotoR6]" name="options[fotoR6]" tabindex="47">
						<option value="con-foto" <?php if($options['fotoR6'] == 'con-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Con foto</option>
						<option value="sin-foto" <?php if($options['fotoR6'] == 'sin-foto'): echo 'selected="selected"'; else: echo ''; endif; ?>>Sin foto</option>
					</select>
					<?php 
						$post_type_idR6 = $options['positionR6'];
						$post_typeR6 = get_post_status($post_type_idR6);
						
						if($post_type_idR6){
							$post_typeR6 = get_post_status($post_type_idR6);
						}else{
							$post_typeR6 = 'None';
						}
						
						echo __('Post status: ', 'frontpage');
						switch ($post_typeR6){
							case 'auto-draft':
								$estadoR6 = 'Auto-borrador';
								break;
							case 'draft':
								$estadoR6 = 'Borrador';
								break;
							case 'inherit':
								$estadoR6 = 'Adjunto';
								break;
							case 'publish':
								$estadoR6 = 'Publicado';
								break;
							case 'trash':
								$estadoR6 = 'En la papelera';
								break;
							default:
								$estadoR6 = 'Sin definir';	
								break;
						}
						?><strong <?php if($estadoR6 == 'Publicado'): echo 'style="background-color:	#32CD32;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; elseif($estadoR6 == 'Sin definir'): echo ''; else: echo 'style="background-color:#DC143C;padding:5px;border-radius:5px;-moz-border-radius:5px;"'; endif; ?>><?php echo $estadoR6.'</strong>';
					?>
					<input name="options[estadoR6]" type="hidden" value="<?php echo $estadoR6; ?>"/>
            	</td>
            </tr>


            <!-- En la portada especial sólo se muestran 5 noticias, por lo que los que se quedan sin poner las ponemos a publicadas, para que no salte el validador -->
            <?php else: ?>
            		<input name="options[estadoL5]" type="hidden" value="Publicado"/>
            		<input name="options[estadoL6]" type="hidden" value="Publicado"/>
            		<input name="options[estadoR1]" type="hidden" value="Publicado"/>
            		<input name="options[estadoR2]" type="hidden" value="Publicado"/>
            		<input name="options[estadoR3]" type="hidden" value="Publicado"/>
            		<input name="options[estadoR4]" type="hidden" value="Publicado"/>
            		<input name="options[estadoR5]" type="hidden" value="Publicado"/>
            		<input name="options[estadoR6]" type="hidden" value="Publicado"/>
            
            <?php endif; ?>
            

            <tr valign="top">
                <th><?php echo __('Preview', 'frontpage'); ?></th>
                <td>
                    <?php $nc->frontpage_checkbox('novisual', 'disable the visual editor'); ?>
                    <?php echo __('(save to apply and be sure to <a href="http://mecus.es">read here</a>)', 'frontpage'); ?>
                    <br />
                    <?php /*?><textarea name="options[message]" wrap="off" rows="10" style="font-family: monospace; width: 100%"><?php echo htmlspecialchars($options['message'])?></textarea><?php */ ?>
                    <br />
                    <?php if('infolatam-portada-normal'==$options['theme']): ?>
                    	<iframe width="100%" height="500" src="<?php echo bloginfo('home'); ?>/previsualizacion-de-la-portada/" style="border: 1px solid #ccc"></iframe>
                    <?php else: ?>
                    	<iframe width="100%" height="500" src="<?php echo bloginfo('home'); ?>/previsualizacion-de-la-portada-especial/" style="border: 1px solid #ccc"></iframe>
                    <?php endif; ?>
                    <?php //Modificación Mecus _e('Tags: <strong>{name}</strong> receiver name;<strong>{unsubscription_url}</strong> unsubscription URL;<strong>{token}</strong> the subscriber token.', 'frontpage'); ?>
                </td>
            </tr>
            
            <tr valign="top">
                <th><?php echo __('Theme', 'frontpage'); ?></th>
                <td>
                    <select name="options[theme]">
                       
                            <option <?php echo ('infolatam-portada-normal'==$options['theme'])?'selected':''; ?> value="infolatam-portada-normal">Infolatam Portada Normal</option>
                            <option <?php echo ('infolatam-portada-especial'==$options['theme'])?'selected':''; ?> value="infolatam-portada-especial">Infolatam Portada Especial</option> 
                        
                        <?php /* Modificación Mecus ?>
                        <optgroup label="Extras themes">
                            <?php
                            $themes = frontpage_get_extras_themes();

                            foreach ($themes as $theme) {
                                echo '<option ' .  (('$'.$theme)==$options['theme']?'selected':'') . ' value="$' . $theme . '">' . $theme . '</option>';
                            }
                            ?>
                        </optgroup>
                        <optgroup label="Custom themes">
                            <?php
                            $themes = frontpage_get_themes();

                            foreach ($themes as $theme) {
                                echo '<option ' .  (('*'.$theme)==$options['theme']?'selected':'') . ' value="*' . $theme . '">' . $theme . '</option>';
                            }
                            ?>
                        </optgroup>
                        <?php */ ?>
                    </select>
                    <input class="button" type="submit" name="auto" value="Auto compose"/>
                    
                    <?php if (isset($_POST['auto']) && isset($_POST['options']) && check_admin_referer()): ?>
                    	<?php if('infolatam-portada-normal'==$options['theme']): ?>
                    		<a target="_blank" href="<?php echo bloginfo('home'); ?>/previsualizacion-de-la-portada/"><?php echo __('Full Screen', 'frontpage'); ?></a>
                    	<?php else: ?>
                    		<a target="_blank" href="<?php echo bloginfo('home'); ?>/previsualizacion-de-la-portada-especial/"><?php echo __('Full Screen', 'frontpage'); ?></a>
                    	<?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input class="button" type="submit" name="save" value="Save" />
             <?php /* Modificación Mecus ?>
            <input class="button" type="submit" name="test" value="Test"/>
            <input class="button" type="submit" name="simulate" value="Simulate"  onclick="return confirm('Simulate? The test address will receive all emails!')"/>
            <input class="button" type="submit" name="send" value="Send" onclick="return confirm('Start a real frontpage sending batch?')"/>
            <?php if (defined('frontpage_EXTRAS')) { ?>
            <input class="button" type="submit" name="scheduled_simulate" value="Scheduled simulation" onclick="return confirm('Start a scheduled simulation?')"/>
            <input class="button" type="submit" name="scheduled_send" value="Scheduled send" onclick="return confirm('Start a scheduled real send?')"/>
            <?php }*/ ?>
        </p>
		
		<?php /* Modificación Mecus ?>
        <h3><?php echo __('Theme parameters', 'frontpage'); ?></h3>
        <p><?php echo __('Themes may not use such parameters!', 'frontpage'); ?></p>
        <table class="form-table">
            <tr valign="top">
                <th><?php echo __('Number of posts on theme', 'frontpage'); ?></th>
                <td>
                    <input name="options[theme_posts]" type="text" size="5" value="<?php echo htmlspecialchars($options['theme_posts'])?>"/>
                </td>
            </tr>
        </table>
		<br /><br />


        <!--
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        List:
        <select name="options[list]">
            <option value="0">General</option>
        <?php for ($i=1; $i<=10; $i++) { ?>
            <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($options_frontpage['list_' . $i]); ?></option>
        <?php } ?>
        </select>
        -->

		
        <h3>Sending options</h3>
        <table class="form-table">
            <tr valign="top">
                <th>Max emails in a single batch</th>
                <td>
                    <?php $nc->frontpage_text('max', 5); ?>
                </td>
            </tr>
            <tr valign="top">
                <th>Receiver address for simulation</th>
                <td>
                    <?php $nc->frontpage_text('simulate_email', 50); ?>
                    <br />
                    <?php _e('When you simulate a sending process, emails are really sent but all to this
email address. That helps to test out problems with mail server.', 'frontpage'); ?>
                </td>
            </tr>
            <tr valign="top">
                <th>Return path</th>
                <td>
                    <?php $nc->frontpage_text('return_path', 50); ?>
                    <br />
                    <?php _e('Force the return path to this email address. Return path is used from mail server to
send back messages with delivery errors.', 'frontpage'); ?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input class="button" type="submit" name="save" value="<?php _e('Save', 'frontpage'); ?>"/>
        </p>
        <!--
        <tr valign="top">
            <td>
                Filter<br />
                <input name="options[filter]" type="text" size="30" value="<?php echo htmlspecialchars($options['filter'])?>"/>
            </td>
        </tr>
        -->

        
        <h3>Sending options for scheduler</h3>
        
        <?php if (defined('frontpage_EXTRAS')) { ?>
        <p>Scheduler is described <a href="http://mecus.es">here</a>.</p>
        <table class="form-table">
            <tr valign="top">
                <th>Interval between sending tasks</th>
                <td>
                    <?php $nc->frontpage_text('scheduler_interval', 5); ?>
                    (minutes, minimum value is 1)
                </td>
            </tr>
            <tr valign="top">
                <th>Max number of emails per task</th>
                <td>
                    <?php $nc->frontpage_text('scheduler_max', 5); ?>
                    (good value is 20 to 50)
                </td>
            </tr>
        </table>
        <p class="submit">
            <input class="button" type="submit" name="save" value="Save"/>
        </p>
        <?php } else { ?>
        <p><strong>Available only with <a href="http://mecus.es">frontpage Extras</a> package</strong></p>
        

		
        <h3><?php echo __('Test subscribers', 'frontpage'); ?></h3>
        <p>
            Define more test subscriber to see how your email looks on different clients:
            GMail, Outlook, Thunderbird, Hotmail, ...
        </p>

        <table class="form-table">
            <?php for ($i=1; $i<=10; $i++) { ?>
            <tr valign="top">
                <th>Subscriber <?php echo $i; ?></th>
                <td>
                    name: <input name="options[test_name_<?php echo $i; ?>]" type="text" size="30" value="<?php echo htmlspecialchars($options['test_name_' . $i])?>"/>
                    &nbsp;&nbsp;&nbsp;
                    email:<input name="options[test_email_<?php echo $i; ?>]" type="text" size="30" value="<?php echo htmlspecialchars($options['test_email_' . $i])?>"/>
                </td>
            </tr>
            <?php } ?>
        </table>
        <p class="submit">
            <input class="button" type="submit" name="save" value="Save"/>
        </p>
        <?php } */ ?>

    </form>
</div>
