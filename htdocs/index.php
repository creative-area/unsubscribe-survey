<?php
include __DIR__ . '/../config.default.php';

if (file_exists(__DIR__ . '/../config.custom.php')) {
	include __DIR__ . '/../config.custom.php';
	$config = array_merge($default_config, $custom_config);
} else {
	$config =& $default_config;
}

$statsfile = __DIR__ . '/../data/unsubscribe.json';
$logsfile = __DIR__ . '/../data/unsubscribe.log';

if (file_exists($statsfile)) {
	$json = file_get_contents($statsfile);
	$data = json_decode($json);
} else {
	$data = new stdClass;
	$data_keys = array_keys($config['reasons']);
	foreach ($data_keys as $reason_key) {
		$data->{$reason_key} = 0;
	}
}

$display_stats = false;
if (isset($_GET['stats']) && $_GET['stats'] === $config['stats_secret']) {
	$display_stats = true;
}

if (!empty($_POST['reason'])
	&& array_key_exists($_POST['reason'], $config['reasons'])
	&& isset($_SERVER['HTTP_X_REQUESTED_WITH'])
	&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {

	$reason = $_POST['reason'];
	$message = "";
	if (!empty($_POST['message'])) {
		$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
	}

	$data->{$reason} ++;
	$write_json = file_put_contents($statsfile, json_encode($data));

	$logger = fopen($logsfile, 'a+');
	$write_log = fputcsv($logger, array(date(DATE_RFC2822), $reason, str_replace(array("\n", "\t", "\r"), ' ', $message)));
	fclose($logger);

	if (!empty($config['email_to'])) {
		$email_body = str_replace(array('{{reason}}', '{{message}}'), array($config['reasons'][$reason], nl2br($message)), $config['email_template']);
		$email_headers = 'MIME-Version: 1.0' . "\r\n";
		$email_headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$send_mail = mail($config['email_to'], $config['email_subject'], $email_body, $email_headers);
	}

	$result = array(
		'result' => ($write_json && $send_mail && $write_log) ? "ok" : "ko",
		'message' => $config['success_msg']
	);

	echo json_encode($result);
	exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= $config['page_title'] ?></title>
		<?php if (!empty($config['page_description'])) { ?>
		<meta name="description" content="<?= $config['page_description'] ?>">
		<?php } ?>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/chartist/0.9.7/chartist.min.css">
		<style>
		.logo {
			padding-bottom: 20px;
		}
		.ct-chart-pie .ct-label {
			font-size: .95em;
			fill: rgba(0,0,0,.5);
			color: rgba(0,0,0,.5);
		}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-sm-12 page-header">
					<?php if (!empty($config['logo_path'])) { ?>
					<img src="<?= $config['logo_path'] ?>" class="logo img-responsive" alt="">
					<?php } ?>
					<h1><?= $config['page_title'] ?></h1>
					<?php if (!empty($config['page_description'])) { ?>
					<p class="lead"><?= $config['page_description'] ?></p>
				</div>
			</div>
			<?php } ?>
			<div class="row">
				<div class="col-md-6">
					<div id="unsubscribe-result" class="alert" style="display: none;"></div>
					<form id="unsubscribe-form">
						<div class="form-group" id="unsubscribe-field-reason">
							<label class="control-label"><?= $config['form_reasons_label'] ?></label>
							<?php foreach ($config['reasons'] as $reason_key => $reason_label) { ?>
							<div class="radio">
								<label><input type="radio" name="reason" value="<?= $reason_key ?>"> <?= $reason_label ?></label>
							</div>
							<?php } ?>
						</div>
						<div class="form-group" id="unsubscribe-field-message">
							<label class="control-label"><?= $config['form_message_label'] ?></label>
							<textarea class="form-control" name="message" rows="5"></textarea>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-primary"><?= $config['form_button_label'] ?></button>
						</div>
					</form>
				</div>
				<div class="col-md-6">
					<?php if ($display_stats) { ?>
					<div class="ct-chart ct-golden-section"></div>
					<script src="//cdnjs.cloudflare.com/ajax/libs/chartist/0.9.7/chartist.min.js"></script>
					<script>
					var chartData = {
						labels: [ <?php foreach ($data as $label => $counter) { echo '"' . $label . ' (' . $counter . ')",'; } ?> ],
						series: [ <?php foreach ($data as $counter) { echo $counter . ','; } ?>  ]
					};
					var chartOptions = {
						labelInterpolationFnc: function(value) {
							return value;
						}
					};
					var chartResponsiveOptions = [
						['screen and (min-width: 1024px)', {
							labelOffset: 80,
							chartPadding: 20,
							labelDirection: 'explode'
						}]
					];
					var chart = new Chartist.Pie('.ct-chart', chartData, chartOptions, chartResponsiveOptions);
					</script>
					<?php } ?>
				</div>
			</div>
		</div>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/3.51/jquery.form.min.js"></script>
		<script>
		$(function() {
			$unsubscribeForm = $("#unsubscribe-form");
			$reasonField = $("#unsubscribe-field-reason");
			$unsubscribeResult = $("#unsubscribe-result");
			$unsubscribeForm.ajaxForm({
				type: "POST",
				beforeSubmit: function(formData, jqForm) {
					var form = jqForm[0];
					if (!form.reason.value) {
						$reasonField.addClass("has-error");
						return false;
					} else {
						return true;
					}
				},
				success: function(response, status, xhr, $form) {
					var data = JSON.parse(response);
					$(".form-group", $form).removeClass("has-error");
					var message = data.message || "Désolé, nous rencontrons un problème.";
					$unsubscribeResult.html(message);
					if (data.result === "ok") {
						$unsubscribeResult.addClass("alert-success");
					} else if (data.result === "ko") {
						$unsubscribeResult.addClass("alert-warning");
					} else {
						$unsubscribeResult.addClass("alert-danger");
					}
					$unsubscribeForm.slideUp(400, function() {
						$unsubscribeResult.slideDown();
					});
				}
			});
		});
		</script>
	</body>
</html>
