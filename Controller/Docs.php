<?php

namespace Tables\Controller;

class Docs extends \Cockpit\AuthController {

    public function index() {

        $path = $this->app->path('tables:README.md');

        $content = file_get_contents($path);

        if ($this->app->req_is('ajax')) {
            return $this->app->module('cockpit')->markdown($content);
        }

        $content = $this->app->module('cockpit')->markdown($content);

        $toc = [];
        foreach($this->app->helpers['fs']->ls('*.md', 'tables:docs') as $file) {
            $toc[] = $file->getBasename();
        }

        return $this->render('tables:views/docs.php', compact('content', 'toc'));

    } // end of index()

    public function docs($file = 'README.md') {

        if ($file == 'img') { // /docs/img/file_name.png
            $args = func_get_args();
            if (isset($args[1])) {
                return $this->img($args[1]);
            }
            return false;
        }

        $path = $this->app->path('tables:docs/'.$file);

        if (!$path) return false;

        $content = file_get_contents($path);

        if ($this->app->req_is('ajax')) {
            return $this->app->module('cockpit')->markdown($content);
        }

        $content = $this->app->module('cockpit')->markdown($content);

        $toc = [];
        foreach($this->app->helpers['fs']->ls('*.md', 'tables:docs') as $file) {
            $toc[] = $file->getBasename();
        }

        return $this->render('tables:views/docs.php', compact('content', 'toc'));

    }

    public function img($file) {

        if ($path = $this->app->path('tables:docs/img/'.$file)) {
            $url = $this->app->pathToUrl($path, true);

            $this->app->reroute($url);
        }

        return false;

    }

}
