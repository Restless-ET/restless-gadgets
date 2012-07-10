<?php
require_once './google-api-php-client/src/apiClient.php';
require_once './google-api-php-client/src/contrib/apiCalendarService.php';
session_start();

$client = new apiClient();
$client->setApplicationName("Google Calendar PHP Starter Application");

// Visit https://code.google.com/apis/console?api=calendar to generate your
// client id, client secret, and to register your redirect uri.
$client->setClientId('810150553784-t6brbp82u5fv3g81jt7fgn9qem7p5pn2.apps.googleusercontent.com');
$client->setClientSecret('GAq8vUss3S-3heu6CtXYp1mI');
$client->setRedirectUri('http://calendar.lh.ubiprism.pt/calendar_test.php');
$client->setDeveloperKey('AIzaSyDlcfeg6uPmtk2VkW34epjtvXXF1hBaVtE');

$cal = new apiCalendarService($client);

if (isset($_GET['logout'])) {
  unset($_SESSION['token']);
}

if (isset($_GET['code'])) {
  $client->authenticate();
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken()) {

  $calList = $cal->calendarList->listCalendarList();

  if (isset($_GET['create'])) {
    $new_cal = new Calendar();
    $new_cal->setSummary('Calendário teste '.count($calList['items']));
    $new_cal->setDescription('Calendário para teste de uso da API.');
    $new_cal->setLocation('Aveiro');
    $new_cal->setTimeZone('Europe/Lisbon');

    $cal->calendars->insert($new_cal);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);

  }
  else if (isset($_GET['delete'])) {
    $itemsList = $calList['items'];

    foreach ($itemsList as $item)
    {
      if (strpos($item['summary'], 'teste') !== false)
      {
        $cal->calendars->delete($item['id']);
      }
    }

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
  }

  print "<a class=create href='?create'>Create new calendar</a><br/><br/>";

  print "<a class=logout href='?logout'>Logout</a><br/><br/>";

  print "<a class=delete href='?delete'>Delete all the 'teste' calendars</a><br/><br/>";

  $calList = $cal->calendarList->listCalendarList();
  print "<h1>Calendar List</h1><pre>" . utf8_decode(print_r($calList, true)) . "</pre>";

  $_SESSION['token'] = $client->getAccessToken();

} else {
  $authUrl = $client->createAuthUrl();
  print "<a class='login' href='$authUrl'>Connect Me!</a>";
}
