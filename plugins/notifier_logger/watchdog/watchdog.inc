<?php

$plugin = array(
  'title' => t('Watchdog'),
  'description' => t('Logs failed message notifications to the watchdog.'),
  'class' => 'MessageNotifierLoggerWatchdog',
  'options' => array(
    'log on fail' => TRUE,
    'log on success' => FALSE,
  ),
);
