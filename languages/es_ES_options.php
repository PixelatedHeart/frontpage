<?php
// This file contains the default options values
$newsletter_default_options['from_email'] = get_option('admin_email');
$newsletter_default_options['from_name'] = get_option('blogname');



// Subscription page introductory text (befor the subscription form)
$newsletter_default_options['subscription_text'] =
"<p>Para recibir autom‡ticamente mensajes de correo electr—nico con los titulares de nuestras noticias, escriba la direcci—n donde desea que se los enviemos y pulse el bot—n "Suscribir".</p>";

// Message show after a subbscription request has made.
$newsletter_default_options['subscribed_text'] =
"<p>Gracias por suscribirse a nuestro Servicio de Env’o de Titulares.</p>
<p>Su direcci—n de correo electr—nico, {email}, ha sido registrada y se le ha enviado a la misma un mensaje con instrucciones para activar su suscripci—n.</p>
<p>Por favor, consulte su buz—n de correo electr—nico.</p>";

// Confirmation email subject (double opt-in)
$newsletter_default_options['confirmation_subject'] =
"Solicitud de confirmaci—n de suscripci—n al Servicio de Env’o de Titulares de Infolatam";

// Confirmation email body (double opt-in)
$newsletter_default_options['confirmation_message'] =
"<p>Estimado usuario:
<p>Recibe usted este mensaje porque su direcci—n de correo electr—nico, {email} ha sido registrada en nuestro Servicio de Env’o de Titulares.</p>
<p>Si no ten’a usted intenci—n de suscribirse a nuestro Servicio, le rogamos que ignore este mensaje y le pedimos disculpas.</p>
<p>Si desea recibir mensajes con los titulares de la noticias publicadas en Infolatam, debe usted activar su registro de suscripci—n visitando la p‡gina web:</p>

<p>{subscription_confirm_url}</p>

<p>Atentamente</p>
--
<p>Departamento de Contenidos de Infolatam.com</p>";


// Subscription confirmed text (after a user clicked the confirmation link
// on the email he received
$newsletter_default_options['confirmed_text'] =
"<p>Gracias por suscribirse a nuestro Servicio de Env’o de Titulares.</p>
<p>Su registro ha sido activado y le enviaremos peri—dicamente a la direcci—n {email} mensajes de correo electr—nico con los titulares de nuestras noticias.</p>";

$newsletter_default_options['confirmed_subject'] =
"Suscripci—n a las noticias de Infolatam activada";

$newsletter_default_options['confirmed_message'] =
"<p>Gracias por suscribirse a nuestro Servicio de Env’o de Titulares.</p>
<p>Su registro ha sido activado y le enviaremos peri—dicamente a la direcci—n {email} mensajes de correo electr—nico con los titulares de nuestras noticias.</p>
<p>Si en algœn momento no desea recibir m‡s mensajes con los titulares de la noticias publicadas en Infolatam, debe usted desactivar su registro de suscripci—n visitando la p‡gina web:</p>
<p>{unsubscription_url}</p>";

// Unsubscription request introductory text
$newsletter_default_options['unsubscription_text'] =
"<p>Por favor, confirme que desea desactivar su registro de suscripcion haciendo click <a href='http://devel.mecus.es/infolatam/{unsubscription_confirm_url}'>aqu&iacute;</a>.</p>";

// When you finally loosed your subscriber
$newsletter_default_options['unsubscribed_text'] =
"<p>Su suscripci—n est‡ cancelada y por tanto no recibir‡ nuestros titulares.</p>";

$newsletter_default_options['unsubscribed_subject'] =
"<p>Suscripci—n cancelada a los titulares de Infolatam</p>";

$newsletter_default_options['unsubscribed_message'] =
"<p>Este email le confirma que su suscripci&oacute;n est&aacute; cancelada y por tanto no recibir&aacute; nuestros  titulares.</p>
<p>Atentamente<br /> --<br /> Departamento de Contenidos de Infolatam.com</p>";

$newsletter_default_options['subscription_form'] =
'<form method="post" action="" style="text-align: center">
<input type="hidden" name="na" value="s"/>
<table cellspacing="3" cellpadding="3" border="0" width="50%">
<tr><td>Your&nbsp;name</td><td><input type="text" name="nn" size="30"/></td></tr>
<tr><td>Your&nbsp;email</td><td><input type="text" name="ne" size="30"/></td></tr>
<tr><td colspan="2" style="text-align: center"><input type="submit" value="Subscribe me"/></td></tr>
</table>
</form>';
?>
