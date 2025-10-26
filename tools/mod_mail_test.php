<?php
declare(strict_types=1);

require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';
require_once ROOT_PATH . '/app/helpers/send-email.php';

$postId   = 9999;
$title    = 'Moderations-Test';
$reviewer = 'debug-user';

$ok = send_admin_mail([
  'type'     => 'moderation',
  'post_id'  => $postId,
  'title'    => $title,
  'reviewer' => $reviewer,
  'note'     => 'Nur ein Test via tools/mod_mail_test.php'
]);

var_dump($ok);
echo '<p>Moderations-Testmail gesendet? Siehe Mailpit.</p>';
