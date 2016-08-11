<?php


class PluginAdminController extends AuthenticatedController
{
    public function render()
    {
        $pluginMeta = $this->pluginManager->getPluginsMeta();

        // Split plugins into 2 arrays: ordered enabled plugins and disabled.
        $enabledPlugins = array_filter($pluginMeta, function($v) { return $v['order'] !== false; });
        // Load parameters.
        $enabledPlugins = load_plugin_parameter_values($enabledPlugins, $this->conf->get('plugins', array()));
        uasort(
            $enabledPlugins,
            function($a, $b) { return $a['order'] - $b['order']; }
        );
        $disabledPlugins = array_filter($pluginMeta, function($v) { return $v['order'] === false; });

        $this->tpl->assign('enabledPlugins', $enabledPlugins);
        $this->tpl->assign('disabledPlugins', $disabledPlugins);
        $this->tpl->renderPage('pluginsadmin');
    }
}
