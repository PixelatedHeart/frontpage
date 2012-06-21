<?php

@include_once 'commons.php';

global $wpdb;
$frontpage_posts = new WP_Query("orderby=id&order=DESC&showposts=30");

$options_frontpage = get_option('frontpage_view');
$options = get_option('frontpage');
$options_search = get_option('frontpage_search');




if(isset($_POST['a']) && check_admin_referer()){
	
	$options = stripslashes_deep($_POST['options']);
	update_option('frontpage_search', $options);
}

if(isset($_POST['f']) && check_admin_referer()){
	
	$options = stripslashes_deep($_POST['options']);
	update_option('frontpage_search', $options);
}

/* Modificación Mecus
if ($_POST['a'] == 'resend' && check_admin_referer()) {
    frontpage_send_confirmation(frontpage_get_subscriber(frontpage_request('id')));
    $_POST['a'] = 'search';
}

if ($_POST['a'] == 'remove' && check_admin_referer()) {
    frontpage_delete(frontpage_request('id'));
    $_POST['a'] = 'search';
}

if ($_POST['removeall'] && check_admin_referer()) {
    frontpage_delete_all();
}

if ($_POST['removeallunconfirmed'] && check_admin_referer()) {
    frontpage_delete_all('S');
}

if ($_POST['showallunconfirmed'] && check_admin_referer()) {
    $list = frontpage_get_unconfirmed();
}

if ($_POST['a'] == 'status' && check_admin_referer()) {
    frontpage_frontpage_set_status(frontpage_request('id'), frontpage_request('status'));
    $_POST['a'] = 'search';
}

if ($_POST['a'] == 'save' && check_admin_referer()) {
    frontpage_frontpage_save(stripslashes_deep($_POST['subscriber']));
    $_POST['a'] = 'search';
}

if ($_POST['a'] == 'search' && check_admin_referer()) {
    $status = isset($_POST['unconfirmed'])?'S':null;
    $order = $_POST['order'];
    $list = frontpage_search(frontpage_request('text'), $status, $order);
}*/

$options = null;
$nc = new frontpage_frontpageControls($options, 'manage');

?>
<script type="text/javascript">
    function frontpage_select_year(anyo){
    	if(anyo != ''){
    		document.getElementById("options[frontpage_month]").disabled=false;
    	}
    }
    
    function frontpage_select_month(mes){
    	if(mes != ''){
    		document.getElementById("view_button").disabled=false;
    	}
    }
    
    function frontpage_select_id(dia){
    	if(dia != ''){
    		document.getElementById("view2_button").disabled=false;
    	}
    }
    
    function frontpage_detail(id)
    {
        document.getElementById("action").value = "detail";
        document.getElementById("id").value = id;
        document.getElementById("channel").submit();
    }
    function frontpage_frontpage_edit(id)
    {
        document.getElementById("action").value = "edit";
        document.getElementById("id").value = id;
        document.getElementById("channel").submit();
    }
    function frontpage_frontpage_save()
    {
        document.getElementById("action").value = "save";
        document.getElementById("channel").submit();
    }
    function frontpage_frontpage_remove(id)
    {
        document.getElementById("action").value = "remove";
        document.getElementById("id").value = id;
        document.getElementById("channel").submit();
    }
    function frontpage_frontpage_set_status(id, status)
    {
        document.getElementById("action").value = "status";
        document.getElementById("id").value = id;
        document.getElementById("status").value = status;
        document.getElementById("channel").submit();
    }
    function frontpage_frontpage_resend(id)
    {
        if (!confirm("<?php _e('Resend the subscription confirmation email?', 'frontpage'); ?>")) return;
        document.getElementById("action").value = "resend";
        document.getElementById("id").value = id;
        document.getElementById("channel").submit();
    }

</script>

<div class="wrap">
    <h2><?php _e('Old Frontpages', 'frontpage'); ?></h2>

    <?php //require_once 'header.php'; ?>

    <form id="channel" method="post" action="">
        <?php wp_nonce_field(); ?>
        <input type="hidden" id="action" name="a" value="search"/>
        <input type="hidden" id="id" name="id" value=""/>
        <input type="hidden" id="status" name="status" value=""/>
        <?php if ($_POST['a'] == 'search' && check_admin_referer()){ ?> <input type="hidden" id="id_frontpage" name="f" value="id_frontpage"/> <?php }?>

        <div style="display: <?php if ($_POST['a'] == 'edit') echo 'none'; else echo 'block'; ?>">
            <table class="form-table">
               	<tr>
               		<th><?php echo __("Choose frontpage's year and month that you want to see:", 'frontpage'); ?></th>
               		<td>
               			<?php frontpage_get_year_and_month(); ?>
               			<input class="button" type="submit" id="view_button" name="view_button" value="Select" disabled="disabled"/>
               		</td>
               	</tr>
            	
            	<tr valign="top">
               		<th><?php echo __("Choose frontpage's day and hour that you want to see:", 'frontpage'); ?></th>
               		<td>
               			<?php frontpage_get_frontpages_for_date(); ?>
               			<input class="button" type="submit" id="view2_button" name="view2_button" value="Frontpage view" disabled="disabled"/>
               		</td>
               	</tr>
               	
               	<!-- We will show the frontpage when the user select the date -->
               	<?php if (($_POST['f'] == 'id_frontpage') && check_admin_referer()): ?>
               		
               		<?php 
               			$options_search = get_option('frontpage_search');
               			$id_frontpage = $options_search['id_frontpage'];
						$frontpages_data = $wpdb->get_results("SELECT * FROM wp_frontpage WHERE idPortada = $id_frontpage ORDER BY orden ASC LIMIT 9");
						
						foreach($frontpages_data as $frontpage_data):
	
							$position_number = $frontpage_data->orden;
							$position = 'position'.$position_number;
							$id_post_position = $frontpage_data->idEntrada;
							$portada_especial = $frontpage_data->portadaEspecial;
							?>
								<tr valign="top">
            						<th><?php echo __('Post title for position', 'frontpage').' '.$position_number; ?></th>
            						<td>
            							<input size="100" type="text" id="options[<?php echo $position.'input_text'; ?>]" name="options[<?php echo $position.'input_text'; ?>]" readonly="readonly" value="<?php $post_position = get_post($id_post_position); echo $post_position->post_title; ?>">
            							
            						</td>
            					</tr>
							<?php		
						endforeach;
							
							
	
						
               		/*?>
               	
               		<tr valign="top">
            			<th><?php echo __('Post title for position 1', 'frontpage'); ?></th>
            			<td>
            			<select id="options[position1]" name="options[position1]" tabindex="7" disabled="disabled">
							<option value="<?php if($options_frontpage['position1'] == ''): echo ''; else: echo $options_frontpage['position1']; endif; ?>"><?php if($options_frontpage['position1'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_position1 = get_post($options_frontpage['position1']); echo $post_position1->post_title; endif; ?></option>
							<option value="">No post</option>
							<?php while ( $frontpage_posts->have_posts() )
							{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
							} ?>
						</select>
            			</td>
            		</tr>
            
            	<tr valign="top">
            		<th><?php echo __('Post title for position 2', 'frontpage'); ?></th>
            		<td>
            			<select id="options[position2]" name="options[position2]" tabindex="7" disabled="disabled">
							<option value="<?php if($options_frontpage['position2'] == ''): echo ''; else: echo $options_frontpage['position2']; endif; ?>"><?php if($options_frontpage['position2'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_position2 = get_post($options_frontpage['position2']); echo $post_position2->post_title; endif; ?></option>
							<option value="">No post</option>
							<?php while ( $frontpage_posts->have_posts() )
							{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
							} ?>
						</select>
            		</td>
            	</tr>
            
            	<tr valign="top">
            		<th><?php echo __('Post title for position 3', 'frontpage'); ?></th>
            		<td>
            			<select id="options[position3]" name="options[position3]" tabindex="7" disabled="disabled">
							<option value="<?php if($options_frontpage['position3'] == ''): echo ''; else: echo $options_frontpage['position3']; endif; ?>"><?php if($options_frontpage['position3'] == ''): echo _e( '&mdash; Select &mdash;' ); else: $post_position3 = get_post($options_frontpage['position3']); echo $post_position3->post_title; endif; ?></option>
							<option value="">No post</option>
							<?php while ( $frontpage_posts->have_posts() )
							{
							$frontpage_posts->the_post();?>
							<?php echo "\n<option value='"; echo get_the_ID().''; echo "'>"; echo the_title(); echo "</option>";
							} ?>
						</select>
            		</td>
            	</tr><?php */ ?>
				
				<?php if (($_POST['f'] == 'id_frontpage') && check_admin_referer()): ?>
            	<tr valign="top">
                	<th><?php echo __('Frontpage Preview', 'frontpage'); ?></th>
                	<td>
                    	<?php if($portada_especial == 'N'): ?>
                    		<iframe width="100%" height="500" src="<?php echo bloginfo('home'); ?>/previsualizacion-de-la-portada/" style="border: 1px solid #ccc"></iframe>
                    	<?php else: ?>
                    		<iframe width="100%" height="500" src="<?php echo bloginfo('home'); ?>/previsualizacion-de-la-portada-especial/" style="border: 1px solid #ccc"></iframe>
                    	<?php endif; ?>
                	</td>
            	</tr>
            	<?php endif; ?>
            	
            <?php endif; ?>
            
            </table>
        </div>

        <?php /*
        if ($_POST['a'] == 'edit' && check_admin_referer()) {
            $subscriber = frontpage_get_subscriber($_POST['id']);
            ?>
        <input type="hidden" name="subscriber[id]" value="<?php echo $subscriber->id; ?>"/>
        <table class="form-table">
            <tr valign="top">
                <th>Name</th>
                <td><input type="text" name="subscriber[name]" size="40" value="<?php echo htmlspecialchars($subscriber->name); ?>"/></td>
            </tr>
            <tr valign="top">
                <th>Email</th>
                <td><input type="text" name="subscriber[email]" size="40" value="<?php echo htmlspecialchars($subscriber->email); ?>"/></td>
            </tr>
        </table>
        <p class="submit"><input type="button" value="Save" onclick="frontpage_frontpage_save()"/></p>

            <?php }*/ ?>

    </form>


    <?php if ($_POST['a'] == 'edit') { ?>
</div>
    <?php return; } ?>


<form method="post" action="">
    <?php wp_nonce_field(); ?>
    <p class="submit">
    <!--<input type="submit" value="Remove all" name="removeall" onclick="return confirm('Are your sure, really sure?')"/>-->
    <!--<input type="submit" value="<?php _e('Remove all unconfirmed', 'frontpage'); ?>" name="removeallunconfirmed" onclick="return confirm('<?php _e('Are your sure, really sure?', 'frontpage'); ?>')"/>-->
    </p>
</form>


<?php /*?>
<h3><?php _e('Subscriber\'s statistics', 'frontpage'); ?></h3>
<?php _e('Confirmed subscriber', 'frontpage'); ?>: <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "frontpage where status='C'"); ?>
<br />
<?php _e('Unconfirmed subscriber', 'frontpage'); ?>: <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "frontpage where status='S'"); ?>

<h3><?php _e('Results', 'frontpage'); ?></h3>

<?php
if ($list) {
    echo '<table class="bordered-table" border="1" cellspacing="5">';
    echo '<tr><th>Id</th><th>' . __('Email', 'frontpage') . '</th><th>' . __('Name', 'frontpage') . '</th><th>' . __('Status', 'frontpage') . '</th><th>' . __('Actions', 'frontpage') . '</th><th>' . __('Profile', 'frontpage') . '</th></tr>';
    foreach($list as $s) {
        echo '<tr>';
        echo '<td>' . $s->id . '</td>';
        echo '<td>' . $s->email . '</td>';
        echo '<td>' . $s->name . '</td>';
        echo '<td><small>' . ($s->status=='S'?'Not confirmed':'Confirmed') . '</small></td>';
        echo '<td><small>';
        echo '<a href="javascript:void(frontpage_frontpage_edit(' . $s->id . '))">' . __('edit', 'frontpage') . '</a>';
        echo ' | <a href="javascript:void(frontpage_frontpage_remove(' . $s->id . '))">' . __('remove', 'frontpage') . '</a>';
        echo ' | <a href="javascript:void(frontpage_frontpage_set_status(' . $s->id . ', \'C\'))">' . __('confirm', 'frontpage') . '</a>';
        echo ' | <a href="javascript:void(frontpage_frontpage_set_status(' . $s->id . ', \'S\'))">' . __('unconfirm', 'frontpage') . '</a>';
        echo ' | <a href="javascript:void(frontpage_frontpage_resend(' . $s->id . '))">' . __('resend confirmation', 'frontpage') . '</a>';
        echo '</small></td>';
        echo '<td><small>';
        $query = $wpdb->prepare("select name,value from " . $wpdb->prefix . "frontpage_profiles where frontpage_id=%d", $s->id);
        $profile = $wpdb->get_results($query);
        foreach ($profile as $field) {
            echo htmlspecialchars($field->name) . ': ' . htmlspecialchars($field->value) . '<br />';
        }
        echo 'Token: ' . $s->token;

        echo '</small></td>';

        echo '</tr>';
    }
    echo '</table>';
}*/
?>

</div>
