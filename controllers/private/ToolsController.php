<?php

class ToolsController extends AuthenticatedController
{
    public function render()
    {
        $data = array(
            'pageabsaddr' => index_url($this->server),
        );
        $this->pluginManager->executeHooks('render_tools', $data);

        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }

        $this->tpl->renderPage('tools');
    }
}