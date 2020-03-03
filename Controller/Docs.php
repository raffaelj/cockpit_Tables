<?php

namespace Tables\Controller;

class Docs extends \Cockpit\AuthController {

    public $help_url = '/help/addons/tables';
    public $docs_dir = '/docs';
    public $img_dir  = 'tables:docs';

    public function before() {

        $this->app->helpers['admin']->addAssets([
            'tables:assets/lib/highlight/highlight.pack.js',
            'tables:assets/lib/highlight/styles/default.css',
            'tables:assets/lib/highlight/styles/github.css'
        ]);

    } // end of before()

    public function index() {

        // redirect to .../docs
        $this->app->reroute($this->help_url . $this->docs_dir);

    } // end of index()

    public function readme() {

        $path = $this->app->path('tables:README.md');

        $content = $this->getParsedContent($path);

        if ($this->app->req_is('ajax')) {
            return ['content' => $content];
        }

        return $this->render('tables:views/docs.php', ['content' => $content, 'help_url' => $this->help_url]);

    } // end of readme()

    public function license() {

        $path = $this->app->path('tables:LICENSE');

        $content = $this->getParsedContent($path);

        if ($this->app->req_is('ajax')) {
            return ['content' => $content];
        }

        return $this->render('tables:views/docs.php', ['content' => $content, 'help_url' => $this->help_url]);

    } // end of license()

    public function docs($file = 'README') {

        if (strtolower(substr($file, -3)) == '.md') {
            $file = substr($file, 0, -3);
        }

        $path = $this->app->path('tables:docs/'.$file.'.md');

        if (!$path) return false;

        $content = $this->getParsedContent($path);

        if ($this->app->req_is('ajax')) {
            return ['content' => $content];
        }

        return $this->render('tables:views/docs.php', ['content' => $content, 'help_url' => $this->help_url]);

    } // end of docs()

    public function img($file) {

        if ($path = $this->app->path('tables:docs/img/'.$file)) {
            $url = $this->app->pathToUrl($path, true);

            $this->app->reroute($url);
        }

        return false;

    } // end of img()

    protected function getParsedContent($path, $base = null, $base_img = null) {

        $base_url       = $base ?? $this->app['base_url'] . $this->help_url
                          . rtrim($this->docs_dir, '/') . '/';

        $image_base_url = $base_img ?? $this->pathToUrl(rtrim($this->img_dir, '/') . '/');

        $content = file_get_contents($path);
        $content = $this->app->module('cockpit')->markdown($content);
        $content = $this->fixRelativeUrls($content, $base_url, $image_base_url);

        return $content;

    } // end of getParsedContent()

    /**
     * modfied from /cockpit/lib/Lime/Helper/Utils.php
     * @param $content
     * @param string $base
     * @param string $base_img
     * @return mixed
     */
    protected function fixRelativeUrls($content, $base = '/', $base_img = '/') {

        // links
        $protocols = '[a-zA-Z0-9\-]+:';
        $regex     = '#\s+(href)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';

        \preg_match_all($regex, $content, $matches);

        if (isset($matches[0])) {
            foreach ($matches[0] as $i => $match) {
                if (\trim($matches[2][$i])) {
                    $content = \str_replace($match, " {$matches[1][$i]}=\"{$base}{$matches[2][$i]}\"", $content);
                }
            }
        }

        // images
        $regex     = '#\s+(src|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';

        \preg_match_all($regex, $content, $matches);

        if (isset($matches[0])) {
            foreach ($matches[0] as $i => $match) {
                if (\trim($matches[2][$i])) {
                    $content = \str_replace($match, " {$matches[1][$i]}=\"{$base_img}{$matches[2][$i]}\"", $content);
                }
            }
        }

        // Background image.
        $regex     = '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|' . $protocols . '|\#)([^\)\'\"]+)[\'\"]?\)#m';
        $content   = \preg_replace($regex, 'style="$1: url(\'' . $base_img . '$2$3\')', $content);

        return $content;

    } // end of fixRelativeUrls()

}
