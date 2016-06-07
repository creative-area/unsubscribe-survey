<?php

$default_config = array(

	"page_title" => "We're sorry to see you go.",
	"page_description" => "You've already been unsubscribed from our mailing list, however before you go, we'd love to know why you're leaving us.",

	"success_msg" => "Thank you! Looking forward to having you with us again.",

	"email_to" => "",
	"email_subject" => "Unsubscribing",
	"email_template" => "Hi,<br /><br />Someone unsubscribe from your list.<br /><br />Reason: {{reason}}<br /><br />{{message}}",

	"form_reasons_label" => "I'm unsubscibing because:",
	"form_message_label" => "My other reason is:",
	"form_button_label" => "Send",

	"reasons" => array(
		"relevancy" => "Your emails are not relevant to me",
		"frequency" => "Your emails are too frequent",
		"error" => "I don't remember signing up for this",
		"other" => "I've got another reason"
	),

	"stats_token" => "show"

);
