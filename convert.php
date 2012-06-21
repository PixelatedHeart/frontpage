<?php
@include_once 'commons.php';
?>
<div class="wrap">
    <form method="post" action="">
        <h2><?php echo __('Conversion', 'frontpage'); ?></h2>
        <p><?php echo __("There are no avalaible updates for the moment."); ?>
        </p>
        
<?php
/*
if (isset($_POST['convert'])) {
    $query = "select id,profile from " . $wpdb->prefix . "frontpage";
    $recipients = $wpdb->get_results($query);
    foreach ($recipients as $s) {
        $profile = unserialize($s->profile);
        if ($profile) {
            foreach ($profile as $name=>$value) {
                @$wpdb->insert($wpdb->prefix . 'frontpage_profiles', array(
                    'frontpage_id'=>$s->id,
                    'name'=>$name,
                    'value'=>$value));
            }
            @$wpdb->query('update ' . $wpdb->prefix . 'frontpage set profile=null where id=' . $s->id);
        }
    }
    echo __('DONE!', 'frontpage');
}*/
?>


<!-- De moemnto, lo dejamos comentado, hasta que veamos como va el tema de las actualizaciones del plugin
        <p class="submit">
            <input class="button" type="submit" name="convert" value="Convert"/>
        </p>
-->
    </form>
</div>