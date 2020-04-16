<?php

/**
 * @file
 * Contains drush commands.
 */

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Database\Database;
use GuzzleHttp\Exception\ClientException;

/**
 * Implements hook_drush_command().
 */
function stage_file_proxy_drush_command() {
  $items['stage-file-proxy-dl'] = [
    'description' => 'Download all managed files from the origin.',
  ];

  return $items;
}

/**
 * Download all managed files from the origin.
 */
function drush_stage_file_proxy_dl() {
  $server = Drupal::config('stage_file_proxy.settings')->get('origin');
  $options = [
    'verify' => \Drupal::config('stage_file_proxy.settings')->get('verify'),
  ];
  if (empty($server)) {
    \Drupal::messenger()->addError(
      dt('Configure stage_file_proxy.settings.origin in your settings.php (see INSTALL.txt).')
    );

    return;
  }

  $query = Database::getConnection('default')->select('file_managed', 'fm');
  $query->fields('fm', ['uri']);
  $query->orderBy('fm.fid', 'DESC');
  $results = $query->execute()->fetchCol();

  $fetch_manager = Drupal::service('stage_file_proxy.fetch_manager');
  $logger = Drupal::service('logger.channel.stage_file_proxy');
  $translation = Drupal::translation();

  $file_dir = $fetch_manager->filePublicPath();
  $remote_file_dir = trim(\Drupal::config('stage_file_proxy.settings')->get('origin_dir'));
  if (!$remote_file_dir) {
    $remote_file_dir = $file_dir;
  }

  $got_files_number = 0;
  $error_files_number = 0;
  $not_public_files_number = 0;

  foreach ($results as $uri) {
    if (strpos($uri, 'public://') !== 0) {
      $not_public_files_number++;
      continue;
    }

    $relative_path = mb_substr($uri, mb_strlen('public://'));

    if (file_exists(DRUPAL_ROOT . '/' . $file_dir . '/' . $relative_path)) {
      continue;
    }

    try {
      if ($fetch_manager->fetch($server, $remote_file_dir, $relative_path, $options)) {
        $got_files_number++;
      }
      else {
        $error_files_number++;
        $logger->error(
          'Stage File Proxy encountered an unknown error by retrieving file @file',
          ['@file' => $server . '/' . UrlHelper::encodePath($remote_file_dir . '/' . $relative_path)]
        );
      }
    }
    catch (ClientException $e) {
      $error_files_number++;
      $logger->error($e->getMessage());
    }
  }

  \Drupal::messenger()->addMessage(
    $translation->translate(
      '@got_files_number downladed files.',
      ['@got_files_number' => $got_files_number]
    )
  );

  if ($error_files_number) {
    \Drupal::messenger()->addError(
      $translation->formatPlural(
        $error_files_number,
        '@count file having an error, see log.',
        '@count files having an error, see log.'
      )
    );
  }

  if ($not_public_files_number) {
    \Drupal::messenger()->addError(
      $translation->formatPlural(
        $not_public_files_number,
        '@count file not in public directory.',
        '@count files not in public directory.'
      )
    );
  }
}
