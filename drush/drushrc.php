<?php

$ignore = array('devel', 'kint', 'devel_generate', 'webprofiler', 'views_ui', 'page_manager_ui');
$command_specific['config-export']['skip-modules'] = $ignore;
$command_specific['config-import']['skip-modules'] = $ignore;
