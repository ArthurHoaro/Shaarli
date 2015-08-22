<?php

// don't raise unnecessary warnings
if (is_file(PluginManager::$PLUGINS_PATH . '/isso/config.php')) {
    include PluginManager::$PLUGINS_PATH . '/isso/config.php';
}

if (!isset($GLOBALS['plugins']['ISSO_SERVER'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Isso plugin error: '. PHP_EOL;
    echo '  Please copy "plugins/isso/config.php.dist" to config.php and configure your isso server URL.'. PHP_EOL;
    echo '  You can also define "$GLOBALS[\'plugins\'][\'ISSO_SERVER\']" in your global Shaarli config.php file.';
    exit;
}

function hook_isso_render_linklist($data)
{
    // Only execute when linklist is rendered.
    if (!empty($data['search_type']) && $data['search_type'] == 'permalink'
        && count($data['links']) == 1) {

        $isso_html = file_get_contents(PluginManager::$PLUGINS_PATH . '/isso/isso.html');

        // data-isso-id is broken. This plugin is useless until it's fixed.
        // https://github.com/posativ/isso/issues/27 (2013!)
        $link = reset($data['links']);
        $isso = sprintf(
            $isso_html,
            $GLOBALS['plugins']['ISSO_SERVER'],
            $link['linkdate'],
            $GLOBALS['plugins']['ISSO_SERVER']
        );
        $data['plugin_end_zone'][] = $isso;
    }

    return $data;
}
