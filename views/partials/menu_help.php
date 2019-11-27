<li class="uk-grid-margin">
    <a class="uk-display-block uk-panel-box uk-panel-card-hover uk-panel-space {{ (strpos($app['route'],'/tables/help')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="@route('/tables/help')">
        <div class="uk-svg-adjust">
            <img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/info.svg')" width="40" height="40" data-uk-svg alt="assets" /> 
        </div>
        <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang('Tables help')</div>
    </a>
</li>