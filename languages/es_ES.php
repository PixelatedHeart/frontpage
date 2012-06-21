<?php

// Subscription form (traslate "your name", "your email" and the button "subscribe me")
$newsletter_labels['subscription_form'] =
'<form method="post" action="" style="text-align: center">
<input type="hidden" name="na" value="s"/>
<table cellspacing="3" cellpadding="3" border="0" width="50%">
<tr><td>Your&nbsp;name</td><td><input type="text" name="nn" size="30"/></td></tr>
<tr><td>Your&nbsp;email</td><td><input type="text" name="ne" size="30"/></td></tr>
<tr><td colspan="2" style="text-align: center"><input type="submit" value="Subscribe me"/></td></tr>
</table>
</form>';


$newsletter_labels['subscription_form_noname'] =
'<form method="post" action="" class="fondo_buscar">
	<input type="hidden" name="na" value="s"/>
	<label class="campo_label">Correo electrónico:</label>
	<input type="text" name="ne" size="20" class="campo_buscar_texto" value="" maxsize="80" />
	<input class="boton_busqueda_1" type="submit" value="SUSCRIBIR" />
</form>';

$newsletter_labels['widget_form'] =
'<form action="{newsletter_url}" method="post">
{text}
<p><input type="text" name="nn" value="Your name" onclick="if (this.defaultValue==this.value) this.value=\'\'" onblur="if (this.value==\'\') this.value=this.defaultValue"/></p>
<p><input type="text" name="ne" value="Your email" onclick="if (this.defaultValue==this.value) this.value=\'\'" onblur="if (this.value==\'\') this.value=this.defaultValue"/></p>
<p><input type="submit" value="Subscribe"/></p>
<input type="hidden" name="na" value="s"/>
</form>';


$newsletter_labels['widget_form_noname'] =
'<form action="{newsletter_url}" method="post">
{text}
<p><input type="text" name="ne" value="Your email" onclick="if (this.defaultValue==this.value) this.value=\'\'" onblur="if (this.value==\'\') this.value=this.defaultValue"/></p>
<p><input type="submit" value="Subscribe"/></p>
<input type="hidden" name="na" value="s"/>
</form>';



$newsletter_labels['embedded_form'] =
'<form action="{newsletter_url}" method="post">
<p><input type="text" name="ne" value="Your email" onclick="if (this.defaultValue==this.value) this.value=\'\'" onblur="if (this.value==\'\') this.value=this.defaultValue"/>
&nbsp;<input type="text" name="nn" value="Your name" onclick="if (this.defaultValue==this.value) this.value=\'\'" onblur="if (this.value==\'\') this.value=this.defaultValue"/>
<input type="submit" value="Subscribe"/>
<input type="hidden" name="na" value="s"/></p>
</form>';

// Example of embedded form without name

$newsletter_labels['embedded_form_noname'] =
'<form action="{newsletter_url}" method="post">
<p><input type="text" name="ne" value="Your email" onclick="if (this.defaultValue==this.value) this.value=\'\'" onblur="if (this.value==\'\') this.value=this.defaultValue"/>
<input type="submit" value="Subscribe"/>
<input type="hidden" name="na" value="s"/></p>
</form>';


// Errors on subscription
$newsletter_labels['error_email'] = 'Wrong email address. <a href="javascript:history.back()">Go back</a>.';
$newsletter_labels['error_name'] = 'The name cannot be empty. <a href="javascript:history.back()">Go back</a>.';

?>