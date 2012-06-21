<?php
/*
Plugin Name: Frontpage
Plugin URI: http://mecus.es
Description: Frontpage is a cool plugin to view your own front page before to publish it. <strong>Before update give a look to <a href="http://mecus.es">this page</a> to know what's changed.</strong> Based on Satollo newsletter.
Version: 1.0
Author: _DorsVenabili
Author URI: http://mecus.es

*/

/*	
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define('frontpage', '1.0');

$frontpage_options_main = get_option('frontpage_main', array());

// Labels loading, after that $frontpage_labels is filled
$frontpage_labels = null;
@include_once(dirname(__FILE__) . '/languages/es_ES.php');
if (WPLANG != '') @include_once(dirname(__FILE__) . '/languages/' . WPLANG . '.php');
//@include_once(ABSPATH . 'wp-content/plugins/frontpage-custom/languages/es_ES.php');
//if (WPLANG != '') @include_once(ABSPATH . 'wp-content/plugins/frontpage-custom/languages/' . WPLANG . '.php');

// Don't try to hack that, frontpage will badly fail
//@include(ABSPATH . 'wp-content/plugins/frontpage-extras/frontpage-extras.php');

$frontpage_step = 'subscription';
$frontpage_subscriber;


function frontpage_init_labels() {
}

function frontpage_label($name, $default='') {
    global $frontpage_labels;

    if (isset($frontpage_labels[$name])) 
    	return $frontpage_labels[$name];
    return $default;
}

function frontpage_echo($name, $default) {
    echo frontpage_label($name, $default);
}

function frontpage_request($name, $default=null ) {
    if (!isset($_REQUEST[$name])) return $default;
    return stripslashes_deep($_REQUEST[$name]);
}

function frontpage_subscribers_count() {
    global $wpdb;

    return $wpdb->get_var("select count(*) from " . $wpdb->prefix . "frontpage where status='C'");
}

//Shows two selects boxes to choose year and month
function frontpage_get_year_and_month() {
	global $wpdb, $m, $monthnum, $year, $wp_locale;
	
	$options_search = get_option('frontpage_search');
	
	if ( isset($_GET['w']) )
		$w = ''.intval($_GET['w']);
	
	// Let's figure out when we are
	if ( !empty($monthnum) && !empty($year) ) {
		$thismonth = ''.zeroise(intval($monthnum), 2);
		$thisyear = ''.intval($year);
	} elseif ( !empty($w) ) {
		// We need to get the month from MySQL
		$thisyear = ''.intval(substr($m, 0, 4));
		$d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
		$thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('${thisyear}0101', INTERVAL $d DAY) ), '%m')");
	} elseif ( !empty($m) ) {
		$thisyear = ''.intval(substr($m, 0, 4));
		if ( strlen($m) < 6 )
				$thismonth = '01';
		else
				$thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
	} else {
		$thisyear = gmdate('Y', current_time('timestamp'));
		$thismonth = gmdate('m', current_time('timestamp'));
	}

	$unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear);
	
	$anyo = $thisyear;
	$mes = 1;
	?>
	<select id="options[frontpage_year]" name="options[frontpage_year]">
         <option <?php if ($_POST['a'] != 'search' && check_admin_referer()) echo 'selected'; ?> value=""><?php echo __('Select a year','frontpage'); ?></option>
         <?php while($anyo >= 2007): ?>
         	<option <?php if ($_POST['a'] == 'search' && check_admin_referer() && $anyo == $options_search['frontpage_year']){
         						echo 'selected';
         				}
         				else{
         						echo '';
         				}; ?> value="<?php echo $anyo; ?>" onclick="frontpage_select_year(<?php echo $anyo; ?>)"><?php echo $anyo; ?></option>
         	<?php $anyo--;
         endwhile; ?>
   </select>
   
   <select id="options[frontpage_month]" name="options[frontpage_month]" <?php if ($_POST['a'] != 'search' && check_admin_referer()) echo 'disabled="disabled"'; ?>>
         <option <?php if ($_POST['a'] != 'search' && check_admin_referer()) echo 'selected'; ?> value=""><?php echo __('Select a month','frontpage'); ?></option>
         <?php while($mes <= 12): 
         	$month_name = month_name($mes); ?>
         	<option <?php if ($_POST['a'] == 'search' && check_admin_referer() && $mes == $options_search['frontpage_month']){
         						echo 'selected';
         				}
         				else{
         						echo '';
         				}; ?> value="<?php echo $mes; ?>" onclick="frontpage_select_month(<?php echo $mes; ?>)"><?php echo $month_name; ?></option>
         	<?php $mes++;
         endwhile; ?>
   </select>
   
	<?php
}

//Shows a select box with the frontpages for the selected year and month
function frontpage_get_frontpages_for_date(){
	global $wpdb;
	
	$options_search = get_option('frontpage_search');
	$y = $options_search['frontpage_year'];
	$m = $options_search['frontpage_month'];
	
	$frontpages = $wpdb->get_results("SELECT * FROM wp_frontpage WHERE YEAR(fecha) = '$y' AND MONTH(fecha) = '$m' ORDER BY fecha DESC");
	$id_last_frontpage = 0;
	?>
	<select id="options[id_frontpage]" name="options[id_frontpage]" style="width:200px;" <?php if ($_POST['a'] != 'search' && check_admin_referer()) echo 'disabled="disabled"';?>>
         <option <?php if ($_POST['a'] == 'search' && check_admin_referer()) echo 'selected';; ?> value=""><?php echo __('Select a day','frontpage'); ?></option>
         <?php foreach($frontpages as $frontpage): 
         	$id_frontpage = $frontpage->idPortada; 
         	
         	if($id_last_frontpage != $id_frontpage): ?>
         		<option <?php if ($_POST['f'] == 'id_frontpage' && check_admin_referer() && $id_frontpage == $options_search['id_frontpage']) echo 'selected'; ?> value="<?php echo $id_frontpage; ?>" onclick="frontpage_select_id(<?php echo $id_frontpage; ?>)"><?php echo $frontpage->fecha; ?></option>
         	<?php endif;
         	$id_last_frontpage = $id_frontpage;
         	
         endforeach; ?>
   </select>
   <?php 
}

function month_name($en_month){
    switch($en_month){
      	case 1:
      		$es_month = __('January','frontpage');
      		break;
      	case 2:
      		$es_month = __('February','frontpage');
      		break;
      	case 3:
      		$es_month = __('March','frontpage');
      		break;
      	case 4:
      		$es_month = __('April','frontpage');
      		break;
      	case 5:
      		$es_month = __('May','frontpage');
      		break;
      	case 6:
      		$es_month = __('June','frontpage');
      		break;
      	case 7:
      		$es_month = __('July','frontpage');
      		break;
      	case 8:
      		$es_month = __('August','frontpage');
      		break;
      	case 9:
      		$es_month = __('September','frontpage');
      		break;
      	case 10:
      		$es_month = __('October','frontpage');
      		break;
      	case 11:
      		$es_month = __('November','frontpage');
      		break;
      	case 12:
      		$es_month = __('December','frontpage');
      		break;
      }
      return $es_month;
}


/* Modificaci—n Mecus
function frontpage_embed_form($form=0) {
    $options = get_option('frontpage');
    
    if (frontpage_has_extras('1.0.2') && $form>0) {
        echo str_replace('{frontpage_url}', $options['url'], frontpage_extras_get_form($form));
    }
    else {
        echo '<div class="frontpage-embed-form">';
        if (isset($options['noname'])) {
            echo str_replace('{frontpage_url}', $options['url'], frontpage_label('embedded_form_noname'));
        }
        else {
            echo str_replace('{frontpage_url}', $options['url'], frontpage_label('embedded_form'));
        }
        echo '</div>';
    }
}*/

/* Modificaci—n Mecus
if (!is_admin()) {
    add_shortcode('frontpage', 'frontpage_call');
    add_shortcode('frontpage_form', 'frontpage_form_call');
}

function frontpage_form_call($attrs, $content=null) {
    $options = get_option('frontpage');
    if (frontpage_has_extras('1.0.2') && isset($attrs['form'])) {
        $buffer = str_replace('{frontpage_url}', $options['url'], frontpage_extras_get_form($attrs['form']));
    }
    else {
        $buffer = '<div class="frontpage-embed-form">';
        if (!isset($options['noname'])) {
            $buffer .= str_replace('{frontpage_url}', $options['url'], frontpage_label('embedded_form'));
        }
        else {
            $buffer .= str_replace('{frontpage_url}', $options['url'], frontpage_label('embedded_form_noname'));
        }

        $buffer .= '</div>';
    }
    return $buffer;
}

/* Modificaci—n Mecus
function frontpage_call($attrs, $content=null) {
    global $frontpage_step, $frontpage_subscriber;

    $options = get_option('frontpage');

    $buffer = '';

    // When a user is starting the subscription process
    if ($frontpage_step == 'subscription') {
        $buffer .= $options['subscription_text'];

        if (frontpage_has_extras('1.0.2') && isset($attrs['form'])) {
            $buffer .= str_replace('{frontpage_url}', $options['url'], frontpage_extras_get_form($attrs['form']));
        }
        else {
            if (isset($options['noname'])) {
                $buffer .= frontpage_label('subscription_form_noname');
            }
            else {
                $buffer .= frontpage_label('subscription_form');
            }
            //if (!defined('frontpage_EXTRAS'))
            //    $buffer .=  '<div style="text-align:right;padding:0 10px;margin:0;"><a style="font-size:9px;color:#bbb;text-decoration:none" href="http://mecus.es">by satollo.net</a></div>';
        }

    }

    // When a user asked to subscribe and the connfirmation request has been sent
    if ($frontpage_step == 'subscribed') {
        $text = frontpage_replace($options['subscribed_text'], $frontpage_subscriber);
        $buffer .= $text;
    }

    if ($frontpage_step == 'confirmed') {
        $text = frontpage_replace($options['confirmed_text'], $frontpage_subscriber);
        $buffer .= $text;

        if (isset($options['confirmed_tracking'])) {
            ob_start();
            eval('?>' . $options['confirmed_tracking']);
            $buffer .= ob_get_clean();
        }
    }

    // Here we are when an unsubscription is requested. There are two kind of unsubscription: the
    // ones with email and token, so the user has only to confire and the ones without
    // data, so the user is requested to insert his email. In the latter case an email
    // will be sent to the user with alink to confirm the email removal.
    if ($frontpage_step == 'unsubscription' || $frontpage_step == 'unsubscription_error') {
        $frontpage_subscriber = frontpage_get_subscriber($_REQUEST['ni']);
        $buffer = frontpage_replace($options['unsubscription_text'], $frontpage_subscriber);
        $url = frontpage_add_qs($options['url'], 'na=uc&amp;ni=' . $frontpage_subscriber->id .
            '&amp;nt=' . $_REQUEST['nt']);
        $buffer = frontpage_replace_url($buffer, 'UNSUBSCRIPTION_CONFIRM_URL', $url);
    }

    // Last message shown to user to say good bye
    if ($frontpage_step == 'unsubscribed') {
        $text = $options['unsubscribed_text'];
        $text = frontpage_replace($text, $frontpage_subscriber);
        $buffer .= $text;
    }

    return '<div class="frontpage">' . $buffer . '</div>';
}

/* Modificaci—n Mecus
function frontpage_phpmailer_init($phpmailer) {
    $options_email = get_option('frontpage_view');
    $phpmailer->Sender = $options_email['return_path'];
}

/**
 * Sends out frontpages.
 *
 * I recipients is an array of subscribers, other parameters are ignored and a test
 * batch is started. This parameter has priority over all.
 *
 * If continue is true, the system try to continue a previous batch keeping its
 * configuration (eg. if it was a simulation or not).
 *
 * If continue is false, simulate indicates if the batch is a simulation and forces
 * the subscriber's email to a test one, as specified in the configuration.
 *
 * Return true if the batch is completed.
 *//* Modificaci—n Mecus
function frontpage_send_batch() {
    global $wpdb;

    frontpage_info(__FUNCTION__, 'Start');

    $options = get_option('frontpage');
    $options_email = get_option('frontpage_view');
    $batch = get_option('frontpage_batch');

    if ($batch == null || !is_array($batch)) {
        frontpage_error(__FUNCTION__, 'No batch found');
        return;
    }

    frontpage_debug(__FUNCTION__, "Batch:\n" . print_r($last, true));

    // Batch have to contain 'id' which is the starting id, 'simulate' boolean
    // to indicate if is a simulation or not, 'scheduled' if it's a scheduled
    // sending process. 'list' is the list number, required.
    // If 'id' = 0 it's a new seding process.

    if (!isset($batch['id'])) {
        frontpage_error(__FUNCTION__, 'Batch "id" parameter not present');
        return false;
    }

    if (!isset($batch['list'])) {
        frontpage_error(__FUNCTION__, 'Batch "list" parameter not present');
        return false;
    }

    if (!isset($batch['simulate'])) {
        frontpage_error(__FUNCTION__, 'Batch "simulate" parameter not present');
        return false;
    }

    if (!isset($batch['scheduled'])) {
        frontpage_error(__FUNCTION__, 'Batch "scheduled" parameter not present');
        return false;
    }

    $id = (int)$batch['id'];
    $list = (int)$batch['list'];
    $simulate = (bool)$batch['simulate'];
    $scheduled = (bool)$batch['scheduled']; // Used to avoid echo

    if ($scheduled) {
        $max = $options_email['scheduler_max'];
        if (!is_numeric($max)) $max = 10;
    }
    else {
        $max = $options_email['max'];
        if (!is_numeric($max)) $max = 0;
    }

    $query = "select * from " . $wpdb->prefix . "frontpage where status='C' and list=" . $list .
        " and id>" . $id . " order by id";
    if ($max > 0) {
        $query .= " limit " . $max;
    }

    $recipients = $wpdb->get_results($query);

    // For a new batch save some info
    if ($id == 0) {
        frontpage_delete_batch_file();
        wp_clear_scheduled_hook('frontpage_cron_hook');
        $batch['total'] = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "frontpage where status='C' and list=" . $list);
        $batch['sent'] = 0;
        $batch['completed'] = false;
        $batch['message'] = '';
    }

    // Not all hosting provider allow this...
    @set_time_limit(100000);

    $start_time = time();
    $max_time = (int)(ini_get('max_execution_time') * 0.8);
    $db_time = time();



    if (!$scheduled) {
        echo 'Sending to: <br />';
    }


    if (isset($options_email['novisual'])) {
        $message = $options_email['message'];
    }
    else {
        $message = '<html><head><style type="text/css">' . frontpage_get_theme_css($options_email['theme']) .
            '</style></head><body>' . $options_email['message'] . '</body></html>';
    }

    $idx = 0;

    add_action('phpmailer_init','frontpage_phpmailer_init');
    if (frontpage_has_extras('1.0.4')) frontpage_init_mail();
    foreach ($recipients as $r) {

        $url = frontpage_add_qs($options['url'],
            'na=u&amp;ni=' . $r->id . '&amp;nt=' . $r->token);

        $m = frontpage_replace_url($message, 'UNSUBSCRIPTION_URL', $url);
        $m = frontpage_replace($m, $r);

        if (defined('frontpage_EXTRAS') && isset($options_email['track']))
            $m = frontpage_relink($m, $r->id, $options_email['name']);

        $s = $options_email['subject'];
        $s = frontpage_replace($s, $r);

        if ($simulate) {
            $x = frontpage_mail($options_email['simulate_email'], $s, $m, true);
        }
        else {
            $x = frontpage_mail($r->email, $s, $m, true);
        }

        if (!$scheduled) {
            echo htmlspecialchars($r->name) . ' (' . $r->email . ') ';

            if ($x) {
                echo '[OK] - ';
                frontpage_debug(__FUNCTION__, 'Sent to ' . $r->id . ' success');
            } else {
                echo '[KO] - ';
                frontpage_debug(__FUNCTION__, 'Sent to ' . $r->id . ' failed');
            }
            flush();
        }

        $idx++;

        $batch['sent']++;
        $batch['id'] = $r->id;

        // Try to avoid database timeout
        if (time()-$db_time > 15) {
            //frontpage_debug(__FUNCTION__, 'Batch saving to avoid database timeout');
            $db_time = time();
            $batch['message'] = 'Temporary saved batch to avoid database timeout';
            if (!update_option('frontpage_batch', $batch)) {
                frontpage_error(__FUNCTION__, 'Unable to save to database, saving on file system');
                frontpage_error(__FUNCTION__, "Batch:\n" . print_r($batch, true));

                frontpage_save_batch_file($batch);
                remove_action('phpmailer_init','frontpage_phpmailer_init');
                if (frontpage_has_extras('1.0.4')) frontpage_close_mail();
                return false;
            }
        }

        // Check for the max emails per batch
        if ($max != 0 && $idx >= $max) {
            frontpage_info(__FUNCTION__, 'Batch saving due to max emails limit reached');
            $batch['message'] = 'Batch max emails limit reached (it is ok)';
            if (!update_option('frontpage_batch', $batch)) {
                frontpage_error(__FUNCTION__, 'Unable to save to database, saving on file system');
                frontpage_error(__FUNCTION__, "Batch:\n" . print_r($batch, true));

                frontpage_save_batch_file($batch);
                remove_action('phpmailer_init','frontpage_phpmailer_init');
                if (frontpage_has_extras('1.0.4')) frontpage_close_mail();

                return false;
            }

            remove_action('phpmailer_init','frontpage_phpmailer_init');
            if (frontpage_has_extras('1.0.4')) frontpage_close_mail();

            return true;
        }

        // Timeout check, max time is zero if set_time_limit works
        if (($max_time != 0 && (time()-$start_time) > $max_time)) {
            frontpage_info(__FUNCTION__, 'Batch saving due to max time limit reached');
            $batch['message'] = 'Batch max time limit reached (it is ok)';
            if (!update_option('frontpage_batch', $batch)) {
                frontpage_error(__FUNCTION__, 'Unable to save to database, saving on file system');
                frontpage_error(__FUNCTION__, "Batch:\n" . print_r($last, true));

                frontpage_save_batch_file($batch);
                remove_action('phpmailer_init','frontpage_phpmailer_init');
                if (frontpage_has_extras('1.0.4')) frontpage_close_mail();

                return false;
            }

            remove_action('phpmailer_init','frontpage_phpmailer_init');
            if (frontpage_has_extras('1.0.4')) frontpage_close_mail();
            return true;
        }
    }

    // All right (incredible!)
    frontpage_info(__FUNCTION__, 'Sending completed!');
    $batch['completed'] = true;
    $batch['message'] = '';
    if (!update_option('frontpage_batch', $batch)) {
        frontpage_error(__FUNCTION__, 'Unable to save to database, saving on file system');
        frontpage_error(__FUNCTION__, "Batch:\n" . print_r($last, true));

        frontpage_save_batch_file($batch);
        remove_action('phpmailer_init','frontpage_phpmailer_init');
        if (frontpage_has_extras('1.0.4')) frontpage_close_mail();

        return false;
    }

    remove_action('phpmailer_init','frontpage_phpmailer_init');
    if (frontpage_has_extras('1.0.4')) frontpage_close_mail();
    return true;
}

/**
 * Send a set of test emails to a list of recipents. The recipients are created
 * in the composer page using the test addresses.
 *//* Modificaci—n Mecus
function frontpage_send_test($recipients) {
    global $wpdb;

    //frontpage_info(__FUNCTION__, 'Start');

    $options = get_option('frontpage');
    $options_email = get_option('frontpage_view');

    @set_time_limit(100000);

    echo 'Sending to: <br />';

    if (isset($options_email['novisual'])) {
        $message = $options_email['message'];
    }
    else {
        $message = '<html><head><style type="text/css">' . frontpage_get_theme_css($options_email['theme']) .
            '</style></head><body>' . $options_email['message'] . '</body></html>';
    }

    //if (frontpage_has_extras('1.0.4')) frontpage_init_mail();
    foreach ($recipients as $r) {

        $url = frontpage_add_qs($options['url'],
            'na=u&amp;ni=' . $r->id . '&amp;nt=' . $r->token);

        $m = frontpage_replace_url($message, 'UNSUBSCRIPTION_URL', $url);
        $m = frontpage_replace($m, $r);
		
		
        if (defined('frontpage_EXTRAS') && isset($options_email['track']))
            $m = frontpage_relink($m, $r->id, $options_email['name']);
        

        $s = $options_email['subject'];
        $s = frontpage_replace($s, $r);

        $x = frontpage_mail($r->email, $s, $m, true);

        echo htmlspecialchars($r->name) . ' (' . $r->email . ') ';
        flush();
		
		
        if ($x) {
            echo '[OK] -- ';
            frontpage_debug(__FUNCTION__, 'Sent to ' . $r->id . ' success');
        } else {
            echo '[KO] -- ';
            frontpage_debug(__FUNCTION__, 'Sent to ' . $r->id . ' failed');
        }
    }
    //if (frontpage_has_extras('1.0.4')) frontpage_close_mail();

}*/


/*
function frontpage_add_qs($url, $qs, $amp=true) {
    if (strpos($url, '?') !== false) {
        if ($amp) return $url . '&amp;' . $qs;
        else return $url . '&' . $qs;
    }
    else return $url . '?' . $qs;
}*/

/**
 * Add a request of frontpage subscription into the database with status "S" (waiting
 * confirmation) and sends out the confirmation request email to the subscriber.
 * The email will contain an URL (or link) the user has to follow to complete the
 * subscription (double opt-in).
 *//* Modificaci—n Mecus
function frontpage_subscribe($email, $name='', $profile=null) {
    global $wpdb, $frontpage_subscriber;

    $options = get_option('frontpage');

    $email = frontpage_normalize_email($email);

    $name = frontpage_normalize_name($name);

    $list = 0;

    if ($profile == null) $profile = array();

    // Check if this email is already in our database: if so, just resend the
    // confirmation email.
    $frontpage_subscriber = frontpage_get_subscriber_by_email($email, $list);
    if (!$frontpage_subscriber) {
        $token = md5(rand());

        if (isset($options['noconfirmation'])) {
            $status = 'C';
        }
        else {
            $status = 'S';
        }
        @$wpdb->insert($wpdb->prefix . 'frontpage', array(
            'email'=>$email,
            'name'=>$name,
            'token'=>$token,
            'list'=>$list,
            'status'=>$status
            //'profile'=>serialize($profile)
        ));
        $id = $wpdb->insert_id;
        $frontpage_subscriber = frontpage_get_subscriber($id);

        // Profile saving
        foreach ($profile as $key=>$value) {
            @$wpdb->insert($wpdb->prefix . 'frontpage_profiles', array(
                'frontpage_id'=>$id,
                'name'=>$key,
                'value'=>$value));
        }

    }

    if (isset($options['noconfirmation'])) {
        frontpage_send_welcome($frontpage_subscriber);
    }
    else {
        frontpage_send_confirmation($frontpage_subscriber);
    }

    $message = 'There is a new subscriber to ' . get_option('blogname') . ' frontpage:' . "\n\n" .
        $name . ' <' . $email . '>' . "\n\n" .
        'Have a nice day,' . "\n" . 'your frontpage plugin.';

    $subject = '[' . get_option('blogname') . '] New subscription';
    frontpage_notify_admin($subject, $message);
}

/* Modificaci—n Mecus
function frontpage_frontpage_save($subscriber) {
    global $wpdb;

    $email = frontpage_normalize_email($email);
    $name = frontpage_normalize_name($name);
    $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "frontpage set email=%s, name=%s where id=%d",
        $subscriber['email'], $subscriber['name'], $subscriber['id']));
}


/**
 * Resends the confirmation message when asked by user manager panel.
 *//* Modificaci—n Mecus
function frontpage_send_confirmation($subscriber) {
    $options = get_option('frontpage');

    frontpage_debug(__FUNCTION__, "Confirmation request to:\n" . print_r($subscriber, true));

    $message = $options['confirmation_message'];
    $html = frontpage_get_theme_html($options['theme']);
    if ($html == null) $html = '{message}';

    $message = str_replace('{message}', $message, $html);

    // The full URL to the confirmation page
    $url = frontpage_add_qs($options['url'], 'na=c&amp;ni=' . $subscriber->id .
        '&amp;nt=' . $subscriber->token);
    $message = frontpage_replace_url($message, 'SUBSCRIPTION_CONFIRM_URL', $url);

    // URL to the unsubscription page (for test purpose)
    $url = frontpage_add_qs($options['url'], 'na=u&amp;ni=' . $subscriber->id .
        '&amp;nt=' . $subscriber->token);
    $message = frontpage_replace_url($message, 'UNSUBSCRIPTION_URL', $url);

    $message = frontpage_replace($message, $subscriber);

    $subject = frontpage_replace($options['confirmation_subject'], $subscriber);

    if (frontpage_has_extras('1.0.4')) frontpage_init_mail();
    frontpage_mail($subscriber->email, $subject, $message);
    if (frontpage_has_extras('1.0.4')) frontpage_close_mail();

}

/**
 * Return a subscriber by his email. The email will be sanitized and normalized
 * before issuing the query to the database.
 *//* Modificaci—n Mecus
function frontpage_get_subscriber($id) {
    global $wpdb;

    $recipients = $wpdb->get_results($wpdb->prepare("select * from " . $wpdb->prefix .
        "frontpage where id=%d", $id));
    if (!$recipients) return null;
    return $recipients[0];
}

function frontpage_get_subscriber_by_email($email, $list=0) {
    global $wpdb;

    $recipients = $wpdb->get_results($wpdb->prepare("select * from " . $wpdb->prefix .
        "frontpage where email=%s and list=%d", $email, $list));
    if (!$recipients) return null;
    return $recipients[0];
}*/
/*
function frontpage_get_all() {
    global $wpdb;

    $recipients = $wpdb->get_results("select * from " . $wpdb->prefix . "frontpage order by email");
    return $recipients;
}*/
/*
function frontpage_search($text, $status=null, $order='email') {
    global $wpdb;

    if ($order == 'id') $order = 'id desc';

    $query = "select * from " . $wpdb->prefix . "frontpage where 1=1";
    if ($status != null) {
        $query .= " and status='" . $wpdb->escape($status) . "'";
    }

    if ($text == '') {
        $recipients = $wpdb->get_results($query . " order by " . $order . ' limit 100');
    }
    else {
        $recipients = $wpdb->get_results($query . " and email like '%" .
            $wpdb->escape($text) . "%' or name like '%" . $wpdb->escape($text) . "%' order by " . $order . ' limit 100');
    }
    if (!$recipients) return null;
    return $recipients;
}*/
/* Modificaci—n Mecus
function frontpage_get_unconfirmed() {
    global $wpdb;

    $recipients = $wpdb->get_results("select * from " . $wpdb->prefix . "frontpage where status='S' order by email");
    return $recipients;
}


/**
 * Normalize an email address,making it lowercase and trimming spaces.
 *//* Modificaci—n Mecus
function frontpage_normalize_email($email) {
    return strtolower(trim($email));
}

function frontpage_normalize_name($name) {
    $name = str_replace(';', ' ', $name);
    $name = strip_tags($name);
    return $name;
}

add_action('init', 'frontpage_init');
/**
 * Intercept the request parameters which drive the subscription and unsubscription
 * process.
 */
 /*
function frontpage_init() {
    global $frontpage_step, $wpdb, $frontpage_subscriber;
    global $hyper_cache_stop;

    // "na" always is the action to be performed - stands for "frontpage action"
    $action = $_REQUEST['na'];
    if (!$action) return;

    $hyper_cache_stop = true;

    //if (defined('frontpage_EXTRAS')) frontpage_extra_init($action);

    $options = get_option('frontpage');

	/* Modificaci—n Mecus
    // Subscription request from a subscription form (in page), can be
    // a direct subscription with no confirmation
    if ($action == 's') {
        if (!frontpage_is_email($_REQUEST['ne'])) {
            die(frontpage_label('error_email'));
        }
        // If not set, the subscription form is not requesting the name, so we do not
        // raise errors.
        if (isset($_REQUEST['nn'])) {
            if (trim($_REQUEST['nn']) == '') {
                die(frontpage_label('error_name'));
            }
        }
        else {
            $_REQUEST['nn'] = '';
        }

        $profile1 = $_REQUEST['np'];
        if (!isset($profile1) || !is_array($profile1)) $profile1 = array();

        // keys starting with "_" are removed because used internally
        $profile = array();
        foreach ($profile1 as $k=>$v) {
            if ($k[0] == '_') continue;
            $profile[$k] = $v;
        }

        $profile['_ip'] = $_SERVER['REMOTE_ADDR'];
        $profile['_referrer'] = $_SERVER['HTTP_REFERER'];

        // Check if the group is good
        frontpage_subscribe($_REQUEST['ne'], $_REQUEST['nn'], $profile);

        if (isset($options['noconfirmation'])) {
            $frontpage_step = 'confirmed';
        }
        else {
            $frontpage_step = 'subscribed';
        }
        return;
    }

    // A request to confirm a subscription
    if ($action == 'c') {
        $id = $_REQUEST['ni'];
        frontpage_confirm($id, $_REQUEST['nt']);
        header('Location: ' . frontpage_add_qs($options['url'], 'na=cs&ni=' . $id . '&nt=' . $_REQUEST['nt'], false));
        die();
    }

    // Show the confirmed message after a redirection (to avoid mutiple email sending).
    // Redirect is sent by action "c".
    if ($action == 'cs') {
        $frontpage_subscriber = frontpage_get_subscriber($_REQUEST['ni']);
        if ($frontpage_subscriber->token != $_REQUEST['nt']) die('Ivalid token');
        $frontpage_step = 'confirmed';
    }

    // Unsubscription process has 2 options: if email and token are specified the user
    // will only be asked to confirm. If there is no infos of who remove (when
    // mass mail mode is used) the user will be asked to type the emailto be removed.
    if ($action == 'u') {
        $frontpage_step = 'unsubscription';
    }

    // User confirmed he want to unsubscribe clicking the link on unsubscription
    // page
    if ($action == 'uc') {
        frontpage_unsubscribe($_REQUEST['ni'], $_REQUEST['nt']);
        $frontpage_step = 'unsubscribed';
    }
}*/


/**
 * Deletes a subscription (no way back). Fills the global $frontpage_subscriber
 * with subscriber data to be used to build up messages.
 *//* Modificaci—n Mecus
function frontpage_unsubscribe($id, $token) {
    global $frontpage_subscriber, $wpdb;

    // Save the subscriber for good bye page
    $frontpage_subscriber = frontpage_get_subscriber($id);

    $wpdb->query($wpdb->prepare("delete from " . $wpdb->prefix . "frontpage where id=%d" .
        " and token=%s", $id, $token));

    $options = get_option('frontpage');
    
    $html = frontpage_get_theme_html($options['theme']);
    if ($html == null) $html = '{message}';
    $message = str_replace('{message}', $options['unsubscribed_message'], $html);

    $message = frontpage_replace($message, $frontpage_subscriber);

    // URL to the unsubscription page (for test purpose)
    //    $url = frontpage_add_qs($options['url'], 'na=u&amp;ni=' . $frontpage_subscriber->id .
    //        '&amp;nt=' . $frontpage_subscriber->token);
    //    $message = frontpage_replace_url($message, 'UNSUBSCRIPTION_URL', $url);

    $subject = frontpage_replace($options['unsubscribed_subject'], $frontpage_subscriber);

    if (frontpage_has_extras('1.0.4')) frontpage_init_mail();
    frontpage_mail($frontpage_subscriber->email, $subject, $message);
    if (frontpage_has_extras('1.0.4')) frontpage_close_mail();



    // Admin notification
    $message = 'There is an unsubscription to ' . get_option('blogname') . ' frontpage:' . "\n\n" .
        $frontpage_subscriber->name . ' <' . $frontpage_subscriber->email . '>' . "\n\n" .
        'Have a nice day,' . "\n" . 'your frontpage plugin.';

    $subject = '[' . get_option('blogname') . '] Unsubscription';
    frontpage_notify_admin($subject, $message);
}*/

/*
 * Deletes a specific subscription. Called only from the admin panel.
 *//* Modificaci—n Mecus
function frontpage_delete($id) {
    global $wpdb;

    $wpdb->query($wpdb->prepare("delete from " . $wpdb->prefix . "frontpage where id=%d", $id));
}

function frontpage_delete_all($status=null) {
    global $wpdb;

    if ($status == null) {
        $wpdb->query("delete from " . $wpdb->prefix . "frontpage");
    }
    else {
        $wpdb->query("delete from " . $wpdb->prefix . "frontpage where status='" . $wpdb->escape($status) . "'");
    }
}*/

/**
 * Confirms a subscription identified by id and token, changing it's status on
 * database. Fill the global $frontpage_subscriber with user data.
 * If the subscription id already confirmed, the welcome email is still sent to
 * the subscriber (the welcome email can contains somthing reserved to the user
 * and he may has lost it).
 * If id and token do not match, the function does nothing.
 *//* Modificaci—n Mecus
function frontpage_confirm($id, $token) {
    global $wpdb, $frontpage_subscriber;

    $options = get_option('frontpage');

    $frontpage_subscriber = frontpage_get_subscriber($id);

    frontpage_info(__FUNCTION__, "Starting confirmation of subscriber " . $id);

    if ($frontpage_subscriber == null) {
        frontpage_error(__FUNCTION__, "Subscriber not found");
        return;
    }

    if ($frontpage_subscriber->token != $token) {
        frontpage_error(__FUNCTION__, "Token not matching");
        return;
    }

    frontpage_debug(__FUNCTION__, "Confirming subscriber:\n" . print_r($frontpage_subscriber, true));

    $count = $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "frontpage set status='C' where id=%d", $id));

    frontpage_send_welcome($frontpage_subscriber);
}

function frontpage_send_welcome($subscriber) {
    $options = get_option('frontpage');

    frontpage_debug(__FUNCTION__, "Welcome message to:\n" . print_r($subscriber, true));

    $html = frontpage_get_theme_html($options['theme']);
    if ($html == null) $html = '{message}';
    $message = str_replace('{message}', $options['confirmed_message'], $html);

    $message = frontpage_replace($message, $subscriber);

    // URL to the unsubscription page (for test purpose)
    $url = frontpage_add_qs($options['url'], 'na=u&amp;ni=' . $subscriber->id .
        '&amp;nt=' . $subscriber->token);
    $message = frontpage_replace_url($message, 'UNSUBSCRIPTION_URL', $url);

    $subject = frontpage_replace($options['confirmed_subject'], $subscriber);
    if (frontpage_has_extras('1.0.4')) frontpage_init_mail();
    frontpage_mail($subscriber->email, $subject, $message);
    if (frontpage_has_extras('1.0.4')) frontpage_close_mail();
}

/*
 * Changes the status of a subscription identified by its id.
 *//* Modificaci—n Mecus
function frontpage_frontpage_set_status($id, $status) {
    global $wpdb;

    $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "frontpage set status=%s where id=%d", $status, $id));
}

/*
 * Sends a notification message to the blog admin.
 *//* Modificaci—n Mecus
function frontpage_notify_admin(&$subject, &$message) {
    $to = get_option('admin_email');
    $headers .= "Content-type: text/plain; charset=UTF-8\n";
    wp_mail($to, $subject, $message, $headers);
}

/**
 * Sends out an email (html or text). From email and name is retreived from
 * frontpage plugin options. Return false on error. If the subject is empty
 * no email is sent out without warning.
 * The function uses wp_mail() to really send the message.
 */
 /* Modificaci—n Mecus
function frontpage_mail($to, &$subject, &$message, $html=true) {
    global $frontpage_mailer, $frontpage_options_main;

    if ($subject == '') {
        frontpage_debug(__FUNCTION__, 'Subject empty, skipped');
        return true;
    }

    if (frontpage_has_extras('1.0.4')) {
        return frontpage_extra_mail($to, &$subject, &$message, $html);
    }

    $options = get_option('frontpage');
    
    $headers  = "MIME-Version: 1.0\n";
    if ($html) $headers .= "Content-type: text/html; charset=UTF-8\n";
    else $headers .= "Content-type: text/plain; charset=UTF-8\n";

    // Special character are manager by wp_mail()
    $headers .= 'From: "' . $options['from_name'] . '" <' . $options['from_email'] . ">\n";

    $r = wp_mail($to, $subject, $message, $headers);
    if (!$r) {
        frontpage_error(__FUNCTION__, "wp_mail() failed");
    }
    return $r;
}*/


add_action('activate_frontpage/plugin.php', 'frontpage_activate');
function frontpage_activate() {
    global $wpdb, $frontpage_options_main;

    $options = get_option('frontpage', array());

	/* Modificaci—n Mecus
    if (frontpage >= '1.5.2') {*/
        //if (isset($options['logs'])) $frontpage_options_main['logs'] = $options['logs'];
        if (isset($options['editor'])) $frontpage_options_main['editor'] = $options['editor'];
        if (isset($options['version'])) $frontpage_options_main['version'] = $options['version'];
        if (isset($options['no_translation'])) $frontpage_options_main['no_translation'] = $options['no_translation'];
    //}

    // Load the default options
    @include_once(dirname(__FILE__) . '/languages/es_ES_options.php');
    if (WPLANG != '') @include_once(dirname(__FILE__) . '/languages/' . WPLANG . '_options.php');
    //@include_once(ABSPATH . 'wp-content/frontpage/languages/custom_options.php');

    $options = array_merge($frontpage_default_options, $options);

    // SQL to create the table
    $sql = 'create table if not exists ' . $wpdb->prefix . 'frontpage (
        `idPortada` int not null primary key default 0,
        `idEntrada` int not null primary key default 0,
        `fecha` datetime not null default 0000-00-00 00:00:00,
        `orden` int not null default 0
        )';

    @$wpdb->query($sql);
	
	/* Modificaci—n Mecus
    if (!isset($frontpage_options_main['version']) || $frontpage_options_main['version'] < '1.4.0') {

        $sql = "alter table " . $wpdb->prefix . "frontpage drop primary key";
        @$wpdb->query($sql);

        $sql = "alter table " . $wpdb->prefix . "frontpage add column id int not null auto_increment primary key";
        @$wpdb->query($sql);

        $sql = "alter table " . $wpdb->prefix . "frontpage add column list int not null default 0";
        @$wpdb->query($sql);

        $sql = "alter table " . $wpdb->prefix . "frontpage drop key email_token";
        @$wpdb->query($sql);

        $sql = "alter table " . $wpdb->prefix . "frontpage add column profile text";
        @$wpdb->query($sql);

        $sql = "ALTER TABLE " . $wpdb->prefix . "frontpage ADD UNIQUE email_list (email, list)";
        @$wpdb->query($sql);
    }

    if (!isset($frontpage_options_main['version']) || $frontpage_options_main['version'] < '1.4.1') {
        $sql = "alter table " . $wpdb->prefix . "frontpage add column created timestamp not null default current_timestamp";
        @$wpdb->query($sql);
    }
	
    $sql = 'create table if not exists ' . $wpdb->prefix . 'frontpage_profiles (
        `frontpage_id` int not null,
        `name` varchar (100) not null default \'\',
        `value` text,
        primary key (frontpage_id, name)
        )';

    @$wpdb->query($sql);*/

    //frontpage_info(__FUNCTION__, 'Activated');

    $frontpage_options_main['version'] = frontpage;
    update_option('frontpage_main', $frontpage_options_main);
    update_option('frontpage', $options);

    //if (defined('frontpage_EXTRAS')) frontpage_extra_activate();
}

if (is_admin()) {
    add_action('admin_menu', 'frontpage_admin_menu');
    function frontpage_admin_menu() {
        global $frontpage_options_main;
        $options = get_option('frontpage');
        $level = ($frontpage_options_main['editor']==1)?7:10;

        if (function_exists('add_menu_page')) {
            add_menu_page('frontpage', 'Frontpage', $level, 'frontpage/main.php', '', '');
        }

        if (function_exists('add_submenu_page')) {
            add_submenu_page('frontpage/main.php', 'Configuration', 'Configuration', $level, 'frontpage/main.php');
            add_submenu_page('frontpage/main.php', 'Subscription', 'Advanced', $level, 'frontpage/options.php');
            add_submenu_page('frontpage/main.php', 'Composer', 'New Frontpage', $level, 'frontpage/frontpage.php');
            add_submenu_page('frontpage/main.php', 'Subscribers', 'Old Frontpages', $level, 'frontpage/manage.php');
            add_submenu_page('frontpage/main.php', 'Update', 'Update', $level, 'frontpage/convert.php');
        }
    }

    add_action('admin_head', 'frontpage_admin_head');
    function frontpage_admin_head() {
        if (strpos($_GET['page'], 'frontpage/') === 0) {
            echo '<link type="text/css" rel="stylesheet" href="' .
                get_option('siteurl') . '/wp-content/plugins/frontpage/style.css"/>';
        }
    }
}

/**
 * Fills a text with sunscriber data and blog data replacing some place holders.
 */
function frontpage_replace($text, $subscriber) {
    $text = str_replace('{home_url}', get_option('home'), $text);
    $text = str_replace('{blog_title}', get_option('blogname'), $text);
    $text = str_replace('{email}', $subscriber->email, $text);
    $text = str_replace('{id}', $subscriber->id, $text);
    $text = str_replace('{name}', $subscriber->name, $text);
    $text = str_replace('{token}', $subscriber->token, $text);
    $text = str_replace('%7Btoken%7D', $subscriber->token, $text);
    $text = str_replace('%7Bid%7D', $subscriber->id, $text);

    return $text;
}

/**
 * Replaces the URL placeholders. There are two kind of URL placeholders: the ones
 * lowercase and betweeb curly brakets and the ones all uppercase. The tag to be passed
 * is the one all uppercase but the lowercase one will also be replaced.
 *//* Modificaci—n Mecus
function frontpage_replace_url($text, $tag, $url) {
    $home = get_option('home') . '/';
    $tag_lower = strtolower($tag);
    $text = str_replace($home . '{' . $tag_lower . '}', $url, $text);
    $text = str_replace($home . '%7B' . $tag_lower . '%7D', $url, $text);
    $text = str_replace('{' . $tag_lower . '}', $url, $text);

    // for compatibility
    $text = str_replace($home . $tag, $url, $text);

    return $text;
}

/* Modificaci—n Mecus
function frontpage_is_email($email, $empty_ok=false) {
    $email = strtolower(trim($email));
    if ($empty_ok && $email == '') return true;

    if (eregi("^([a-z0-9_\.-])+@(([a-z0-9_-])+\\.)+[a-z]{2,6}$", trim($email))) {
        if (strpos($email, 'mailinator.com') !== false) return false;
        if (strpos($email, 'guerrillamailblock.com') !== false) return false;
        return true;
    }
    else
        return false;
}

function frontpage_delete_batch_file() {
    @unlink(dirname(__FILE__) . '/batch.dat');
}

function frontpage_save_batch_file($batch) {
    $file = @fopen(dirname(__FILE__) . '/batch.dat', 'w');
    if (!$file) return;
    @fwrite($file, serialize($batch));
    @fclose($file);
}

function frontpage_load_batch_file() {
    $content = @file_get_contents(dirname(__FILE__) . '/batch.dat');
    return @unserialize($content);
}

/**
 * Write a line of log in the log file if the logs are enabled or force is
 * set to true.
 *//* Modificaci—n Mecus
function frontpage_log($text) {
    $file = @fopen(dirname(__FILE__) . '/frontpage.log', 'a');
    if (!$file) return;
    @fwrite($file, date('Y-m-d h:i') . ' ' . $text . "\n");
    @fclose($file);
}

function frontpage_debug($fn, $text) {
    global $frontpage_options_main;
    if ($frontpage_options_main['logs'] < 2) return;
    frontpage_log('- DEBUG - ' . $fn . ' - ' . $text);
}

function frontpage_info($fn, $text) {
    if ($frontpage_options_main['logs'] < 1) return;
    frontpage_log('- INFO  - ' . $fn . ' - ' . $text);
}

function frontpage_error($fn, $text) {
    if ($frontpage_options_main['logs'] < 1) return;
    frontpage_log('- ERROR - ' . $fn . ' - ' . $text);
}

/**
 * Retrieves a list of custom themes located under wp-plugins/frontpage-custom/themes.
 * Return a list of theme names (which are folder names where the theme files are stored.
 *//* Modificaci—n Mecus
function frontpage_get_themes() {
    $handle = @opendir(ABSPATH . 'wp-content/plugins/frontpage-custom/themes');
    $list = array();
    if (!$handle) return $list;
    while ($file = readdir($handle)) {
        if ($file == '.' || $file == '..') continue;
        if (!is_dir(ABSPATH . 'wp-content/plugins/frontpage-custom/themes/' . $file)) continue;
        if (!is_file(ABSPATH . 'wp-content/plugins/frontpage-custom/themes/' . $file . '/theme.php')) continue;
        $list[] = $file;
    }
    closedir($handle);
    return $list;
}

function frontpage_get_extras_themes() {
    $handle = @opendir(ABSPATH . 'wp-content/plugins/frontpage-extras/themes');
    $list = array();
    if (!$handle) return $list;
    while ($file = readdir($handle)) {
        if ($file == '.' || $file == '..') continue;
        if (!is_dir(ABSPATH . 'wp-content/plugins/frontpage-extras/themes/' . $file)) continue;
        if (!is_file(ABSPATH . 'wp-content/plugins/frontpage-extras/themes/' . $file . '/theme.php')) continue;
        $list[] = $file;
    }
    closedir($handle);
    return $list;
}

/**
 * Resets the batch status.
 /* Modificaci—n Mecus
function frontpage_reset_batch() {

}

function frontpage_has_extras($version=null) {
    if (!defined('frontpage_EXTRAS')) return false;
    if ($version == null) return true;
    if ($version <= frontpage_EXTRAS) return true;
    return false;
}*/

/** 
 * Find an image for a post checking the media uploaded for the post and
 * choosing the first image found.
 */
function frontpage_nt_post_image($post_id, $size='thumbnail', $alternative=null) {

    $attachments = get_children(array('post_parent'=>$post_id, 'post_status'=>'inherit', 'post_type'=>'attachment', 'post_mime_type'=>'image', 'order'=>'ASC', 'orderby'=>'menu_order ID' ) );

    if (empty($attachments)) {
        return $alternative;
    }

    foreach ($attachments as $id=>$attachment) {
        $image = wp_get_attachment_image_src($id, $size);
        return $image[0];
    }
    return null;
}

function frontpage_nt_option($name, $def = null) {
//    if ($frontpage_is_feed && $name == 'posts') {
//        $options = get_option('frontpage_feed');
//        return $options['posts'];
//    }
    $options = get_option('frontpage_view');
    $option = $options['theme_' . $name];
    if (!isset($option)) return $def;
    else return $option;
}

/**
 * Retrieves the theme dir path.
 */
function frontpage_get_theme_dir($theme) {
    /* Modificaci—n Mecus
    if ($theme[0] == '*') {
        return ABSPATH . '/wp-content/plugins/frontpage-custom/themes/' . substr($theme, 1);
    }
    elseif ($theme[0] == '$') {
        return ABSPATH . '/wp-content/plugins/frontpage-extras/themes/' . substr($theme, 1);
    }
    else {*/
        return dirname(__FILE__) . '/themes/' . $theme;
    //}
}

/**
 * Retrieves the theme URL (pointing to theme dir).
 */
function frontpage_get_theme_url($theme) {
    /* Modificaci—n Mecus
    if ($theme[0] == '*') {
        return get_option('siteurl') . '/wp-content/plugins/frontpage-custom/themes/' . substr($theme, 1);
    }
    elseif ($theme[0] == '$') {
        return get_option('siteurl') . '/wp-content/plugins/frontpage-extras/themes/' . substr($theme, 1);
    }
    else {*/
        return get_option('siteurl') . '/wp-content/plugins/frontpage/themes/' . $theme;
    //}
}

/**
 * Loads the theme css content to be embedded in emails body.
 */
function frontpage_get_theme_css($theme) {
    if ($theme == 'blank') return '';
    return @file_get_contents(frontpage_get_theme_dir($theme) . '/style.css');
}

function frontpage_get_theme_html($theme) {
    if ($theme == 'blank') return '';
    $file = frontpage_get_theme_dir($theme) . '/theme.php';

    // Execute the theme file and get the content generated
    ob_start();
    @include($file);
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

?>
