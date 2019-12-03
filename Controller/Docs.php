<?php

namespace Tables\Controller;

class Docs extends \Cockpit\AuthController {

    public function before() {

        $this->app->helpers['admin']->addAssets('tables:assets/lib/highlight/highlight.pack.js');
        $this->app->helpers['admin']->addAssets('tables:assets/lib/highlight/styles/default.css');
        $this->app->helpers['admin']->addAssets('tables:assets/lib/highlight/styles/github.css');

    } // end of before()

    public function index() {

        // links with relative paths in /docs must point to correct route
        $this->app->reroute('/tables/help/docs/README');

    } // end of index()

    public function readme() {

        $path = $this->app->path('tables:README.md');

        $content = file_get_contents($path);

        if ($this->app->req_is('ajax')) {
            return ['content' => $this->app->module('cockpit')->markdown($content)];
        }

        $content = $this->app->module('cockpit')->markdown($content);

        return $this->render('tables:views/docs.php', compact('content'));

    } // end of readme()

    public function license() {

        $path = $this->app->path('tables:LICENSE');

        $content = file_get_contents($path);

        if ($this->app->req_is('ajax')) {
            return ['content' => $this->app->module('cockpit')->markdown($content)];
        }

        $content = $this->app->module('cockpit')->markdown($content);

        return $this->render('tables:views/docs.php', compact('content'));

    } // end of license()

    public function docs($file = 'README') {

        if ($file == 'img') { // '/docs/img/file_name.png'
            $args = func_get_args();
            if (isset($args[1])) {
                return $this->img($args[1]);
            }
            return false;
        }

        if (strtolower(substr($file, -3)) == '.md') {
            $file = substr($file, 0, -3);
            $this->app->reroute('/tables/help/docs/'.$file);
        }

        $path = $this->app->path('tables:docs/'.$file.'.md');

        if (!$path) return false;

        $content = file_get_contents($path);

        if ($this->app->req_is('ajax')) {
            return ['content' => $this->app->module('cockpit')->markdown($content)];
        }

        $content = $this->app->module('cockpit')->markdown($content);

        return $this->render('tables:views/docs.php', compact('content'));

    } // end of docs()

    public function img($file) {

        if ($path = $this->app->path('tables:docs/img/'.$file)) {
            $url = $this->app->pathToUrl($path, true);

            $this->app->reroute($url);
        }

        return false;

    } // end of img()

}
