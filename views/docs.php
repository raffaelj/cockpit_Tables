
<style>
p img {
    display: block;
    margin: 1em auto;
    padding: 2px;
    border: 1px solid #ccc;
    border-radius: 2px;
}

kbd {
    display: inline-block;
    margin: 0 .1em;
    padding: .1em .6em;
    font-family: Arial,"Helvetica Neue",Helvetica,sans-serif;
    font-size: .8em;
    line-height: 1.4em;
    color: #242729;
    text-shadow: 0 1px 0 #FFF;
    background-color: #F7F7F7;
    border: 1px solid #adb3b9;
    border-radius: 3px;
    box-shadow: 0 1px 0 rgba(12,13,14,0.2),0 0 0 2px #FFF inset;
    white-space: nowrap;
}

#tables-docs-toc {
    transition: margin .1s ease-in-out;
}
#tables-docs-toc.sticky {
    margin-top: 75px;
    transition: margin .1s ease-in-out;
}
.hljs {
    background-color: #f5f5f5;
    color: #444;
}
pre .hljs {
    padding: 0;
}
</style>

<div id="tables-docs" class="uk-grid" data-uk-grid-margin>
    <div class="uk-width-medium-1-4 uk-push-3-4">

        <ul id="tables-docs-toc" class="uk-nav uk-nav-side" data-uk-sticky="{media:768}">

            <li class="uk-nav-header">@lang('General')</li>

            <li class="{{ strpos($app['route'], '/help/addons/tables/readme') === 0 ? 'uk-active' : '' }}">
                <a href="@base('/help/addons/tables/readme')">Readme</a>
            </li>
            <li class="{{ strpos($app['route'], '/help/addons/tables/license') === 0 ? 'uk-active' : '' }}">
                <a href="@base('/help/addons/tables/license')">@lang('License')</a>
            </li>

            <li class="uk-nav-header">@lang('Documentation')</li>

            <li class="{{ strpos($app['route'], '/help/addons/tables/docs/README') === 0 ? 'uk-active' : '' }}">
                <a href="@base('/help/addons/tables/docs/README')">@lang('Overview')</a>
            </li>
          @foreach($app->helpers['fs']->ls('*.md', 'tables:docs') as $file)
            {% $filename = $file->getBasename(); if ($filename == 'README.md') {continue;} %}
            <li class="{{ strpos($app['route'], '/help/addons/tables/docs/'. $filename) === 0 ? 'uk-active' : '' }}">
                <a href="@base('/help/addons/tables/docs/'.$filename)">{{ ucfirst(str_replace('_', ' ', substr($filename, 0, -3))) }}</a>
            </li>
          @endforeach
        </ul>

    </div>

    <div class="uk-width-medium-3-4 uk-pull-1-4">
        <div id="tables-docs-content">
        {{ $content }}
        </div>
    </div>
</div>

<script>

    // keep nav below app-header-panel
    App.$('.app-header').on({
        'active.uk.sticky': function(e) {
            App.$('#tables-docs-toc').addClass('sticky');
        },
        'inactive.uk.sticky': function(e) {
            App.$('#tables-docs-toc').removeClass('sticky');
        }
    });

    App.$(function($){

        var help_url = '{{ $help_url }}';

        App.$('document').ready(function() {
            addClickEvents();
            addLinkIcons();
            hljs.initHighlighting();
        });

        function loadData(url) {

            if (url.indexOf(App.route('/')) === 0) {
                url = url.substring(App.route('/').length -1, url.length);
            }

            App.request(url).then(function(data) {

                if (data && data.content) {

                    window.history.pushState(
                        null, null,
                        App.route(url)
                    );

                    App.$('#tables-docs a:not([href^="//"]):not([href^="http"])').unbind('click');

                    App.$('#tables-docs-content').html(data.content);

                    App.$('document').ready(function() {
                        addClickEvents();
                        addLinkIcons();
                        hljs.initHighlighting.called = false;;
                        hljs.initHighlighting();
                    });

                }

            }).catch(function(e) {
                console.log(url);
                console.log(e);
            });
            
        };

        function addClickEvents() {

            // load internal/relative links with ajax request - much faster
            App.$('#tables-docs a:not([href^="//"]):not([href^="http"])').click(function(e) {

                if (e) e.preventDefault();

                var href = e.target.getAttribute('href'), url = href;

                if (href == window.location.pathname) {
                    return;
                }

                App.$('#tables-docs-content').html('<div class="uk-icon-spinner uk-icon-spin"></div>');

                // update toc
                App.$('#tables-docs-toc .uk-active').removeClass('uk-active');

                var tocActive = App.$('#tables-docs-toc a[href="'+url+'"]').get(0);
                if (tocActive) tocActive.parentNode.classList.add('uk-active');

                loadData(url);

            });
            
        };

        function addLinkIcons() {

            App.$('#tables-docs a[href^="https://github.com"]')
                .attr('target', '_blank')
                .prepend('<i class="uk-icon-github uk-icon-hover"></i> ');

            App.$('#tables-docs a[href^="https"][href*="wikipedia.org"]')
                .attr('target', '_blank')
                .prepend('<i class="uk-icon-wikipedia-w uk-icon-hover"></i> ');

            App.$('#tables-docs a[href^="https"][href*="getcockpit.com"]')
                .attr('target', '_blank')
                .prepend('<i class="uk-icon"><img width="12px" height="12px" src="'+App.base('/assets/app/media/logo.svg')+'" data-uk-svg /></i> ');

            App.$('#tables-docs a[href^="//"], #tables-docs a[href^="http"]'
                + ':not([href^="https://github.com"])'
                + ':not([href*="wikipedia.org"])'
                + ':not([href*="getcockpit.com"])')
                .attr('target', '_blank')
                .prepend('<i class="uk-icon-external-link uk-icon-hover"></i> ');

        }

    });

</script>
