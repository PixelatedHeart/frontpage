<?php

@include_once 'commons.php';

$options = get_option('frontpage_main');

if ($action == 'save') {
    $options = stripslashes_deep($_POST['options']);
    update_option('frontpage_main', $options);
}

$nc = new frontpage_frontpageControls($options);

?>

<div class="wrap">

    <h2><?php echo __('Frontpage Configuration', 'frontpage'); ?></h2>

    <?php require_once 'header.php'; ?>

    <p>
        <strong><?php echo __('Frontpage has an <a href="http://mecus.es">official page</a> where
        to find documentation on how it
        works and how to configure it. Version history and tips are located on
        <a href="http://mecus.es">this archive</a>. Questions can be submitted
        on <a href="http://mecus.es">this help page</a>.', 'frontpage'); ?></strong>
    </p>
    
    <form method="post" action="">
        <?php wp_nonce_field(); ?>
        <input type="hidden" value="<?php echo frontpage; ?>" name="options[version]"/>

        <h3><?php _e('General parameters', 'frontpage'); ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th><?php _e('Enable access to editors?', 'frontpage'); ?></th>
                <td>
                    <?php $nc->frontpage_yesno('editor'); ?>
                </td>
            </tr>
            <tr valign="top">
                <th><?php _e('Always show panels in english?', 'frontpage'); ?></th>
                <td>
                    <?php $nc->frontpage_yesno('no_translation'); ?>
                    <br />
                    <?php _e('The author does NOT maintain translations, so if you have a doubt about some texts, disable the translations', 'frontpage'); ?>
                </td>
            </tr>
            <?php /* Modificaci—n Mecus ?>
            <tr valign="top">
                <th><?php _e('Logging', 'frontpage'); ?></th>
                <td>
                    <?php $nc->frontpage_select('logs', array(0=>'None', 1=>'Normal', 2=>'Debug')); ?>
                    <br />
                    <?php _e('Debug level saves user data on file system, use only to debug problems.', 'frontpage'); ?>
                </td>
            </tr>
            <?php } */ ?>
        </table>
        <p class="submit">
            <?php $nc->frontpage_button('save', __('Save', 'frontpage')); ?>
        </p>
    </form>
</div>
