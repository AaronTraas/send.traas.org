<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../config.inc.php';

function message_builder($message, $success) {
  $alert_type = ($success == true) ? 'success' : 'danger';
  return '<div class="row"><div class="col alert alert-' . $alert_type . '" role="alert">' . $message . '</div></div>';
}

if (ENABLED != true) {
  die();
}

$message = '';
if( $_POST && isset($_POST['bodyText']) ) {
  require '../vendor/autoload.php';

  $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

  $email = new \SendGrid\Mail\Mail();
  $email->setFrom("admin@send.traas.org", "send.traas.org");
  $email->setSubject("send.traas.org - $ip");
  $email->addTo("aaron@traas.org", "Aaron Traas");
  $email->addContent("text/plain", $_POST['bodyText']);
  $sendgrid = new \SendGrid(SENDGRID_API_KEY);
  try {
      $response = $sendgrid->send($email);
      if ($response->statusCode() >= 200 && $response->statusCode() < 300 ) {
        $message = message_builder('Message sent successfully.' , true);
      } else {
        $message = message_builder('Message failed (status code ' . $response->statusCode() . ').<br>' . $response->body(), false);
      }
  } catch (Exception $e) {
      $message = message_builder('Caught exception: ' . $e->getMessage(), false);
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="css/bootstrap.min.css">

    <title>send.traas.org</title>
  </head>
  <body>
    <main role="main">

      <div class="jumbotron">
        <div class="container">
          <h1 class="display-3">send.traas.org</h1>
        </div>
      </div>

      <div class="container">
        <?php echo $message; ?>
        <div class="row">
          <form class="col" method="POST">
            <div class="form-group">
              <label for="exampleInputEmail1">Content</label>
              <textarea class="form-control" name="bodyText" id="bodyText" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
          </form>
        </div>
      </div>

    </main>
  </body>
</html>
