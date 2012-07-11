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

$calService = new apiCalendarService($client);

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

  $calList = $calService->calendarList->listCalendarList();

  if (isset($_GET['create']))
  {
    $new_cal = new Calendar();
    $new_cal->setSummary('Calendário teste '.count($calList['items']));
    $new_cal->setDescription('Calendário para teste de uso da API.');
    $new_cal->setLocation('Aveiro');
    $new_cal->setTimeZone('Europe/Lisbon');

    $calService->calendars->insert($new_cal);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
  }
  else if (isset($_GET['delete']))
  {
    $itemsList = $calList['items'];

    foreach ($itemsList as $item)
    {
      if (strpos($item['summary'], 'teste') !== false)
      {
        //Delete the events associated with this calendar first
        $eventList = $calService->events->listEvents($item['id']);
        //print "<h1>Event List</h1><pre>".utf8_decode(print_r($eventList, true))."</pre>";

        $calEvents = $eventList['items'];
        foreach ($calEvents as $event)
        {
          $calService->events->delete($item['id'], $event['id'], array('sendNotifications' => false));
        }
        //Then delete the calendar
        $calService->calendars->delete($item['id']);
      }
    }
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
  }
  else if (isset($_GET['associate']))
  {
    $itemsList = $calList['items'];

    foreach ($itemsList as $item)
    {
      if (strpos($item['summary'], 'teste') !== false)
      {
        $tiago = new AclRuleScope();
        $tiago->setType('user');
        $tiago->setValue('tiago.brito@beubi.com');
        $hugo = new AclRuleScope();
        $hugo->setType('user');
        $hugo->setValue('hugo.fonseca@beubi.com');

        $rule = new AclRule();
        $rule->setRole('reader');

        $rule->setScope($tiago);
        $createdRule = $calService->acl->insert($item['id'], $rule);

        $rule->setScope($hugo);
        $createdRule = $calService->acl->insert($item['id'], $rule);
        //echo $createdRule->getId();
      }
    }
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
  }
  else if (isset($_GET['event']))
  {
    $itemsList = $calList['items'];

    foreach ($itemsList as $item)
    {
      if (strpos($item['summary'], 'teste') !== false)
      {
        $event = new Event();
        $event->setSummary('Almoço');
        $event->setLocation('Num restaurante perto de si...');

        $start = new EventDateTime();
        $start->setDateTime(date('Y-m-d').'T12:30:00.000+00:00');
        $event->setStart($start);
        $end = new EventDateTime();
        $end->setDateTime(date('Y-m-d').'T13:45:00.000+00:00');
        $event->setEnd($end);

        $attendee1 = new EventAttendee();
        $attendee1->setDisplayName('Tiago "Geek Retro" Brito');
        $attendee1->setEmail('tiago.brito@beubi.com');
        $attendee2 = new EventAttendee();
        $attendee2->setDisplayName('Hugo "Perigoso" Fonseca');
        $attendee2->setEmail('hugo.fonseca@beubi.com');
        $attendee3 = new EventAttendee();
        $attendee3->setDisplayName('Artur Melo');
        $attendee3->setEmail('artur.melo@beubi.com');

        $attendees = array(
            $attendee1,
            $attendee2,
            $attendee3,
        );
        $event->attendees = $attendees;
        $createdEvent = $calService->events->insert($item['id'], $event, array('sendNotifications' => false));
        //echo $createdEvent->getId();
      }
    }
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
  }

  print "<a class=create href='?create'>Create new calendar</a><br/><br/>";

  print "<a class=logout href='?logout'>Logout</a><br/><br/>";

  print "<a class=delete href='?associate'>Associate 'teste' calendars with Be.Ubi users</a><br/><br/>";

  print "<a class=delete href='?event'>Add example event on all 'teste' calendars</a><br/><br/>";

  print "<a class=delete href='?delete'>Delete all 'teste' calendars</a><br/><br/>";

  $calList = $calService->calendarList->listCalendarList();
  print "<h1>Calendar List</h1><pre>" . utf8_decode(print_r($calList, true)) . "</pre>";

  $_SESSION['token'] = $client->getAccessToken();

} else {
  $authUrl = $client->createAuthUrl();
  print "<a class='login' href='$authUrl'>Connect Me!</a>";
}
