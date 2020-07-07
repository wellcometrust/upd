<?php

/**
 * @file
 * Include default settings for the master/dev environment.
 */

// Config sync directory.
$settings['config_sync_directory'] = '../config/sync';

// Enable the 'prod' config_split and force other splits to be disabled.
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.master']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = TRUE;
