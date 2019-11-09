<div>

    <ul class="uk-breadcrumb">
        <li><a href="@route('/tables')">@lang('Tables')</a></li>
        <li class="uk-active" data-uk-dropdown="mode:'hover', delay:300">

            @if($app['route'] == '/tables/entries/'.$table['name'])
                <span><i class="uk-icon-bars"></i>
                {{ htmlspecialchars($table['label'] ?? $table['name']) }}</span>
            @else
            <a href="@route('/tables/entries/'.$table['name'])">
                <i class="uk-icon-bars"></i>
                {{ htmlspecialchars($table['label'] ?? $table['name']) }}
            </a>
            @endif

            @if($app->module('tables')->hasaccess($table['name'], 'table_edit'))
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/tables/table/'.$table['name'])">@lang('Edit')</a></li>

                    <li class="uk-nav-divider"></li>

                    <li class="uk-text-truncate"><a href="@route('/tables/export/'.$table['name'].'?type=ods')" download="{{ $table['name'] }}.table.ods">@lang('Export entries (ODS)')</a></li>
                    <li class="uk-text-truncate"><a href="@route('/tables/export/'.$table['name'].'?type=xlsx')" download="{{ $table['name'] }}.table.ods">@lang('Export entries (XLSX)')</a></li>
                    <li class="uk-text-truncate"><a href="@route('/tables/export/'.$table['name'])" download="{{ $table['name'] }}.table.json">@lang('Export entries (JSON)')</a></li>
                    <li class="uk-text-truncate"><a href="@route('/tables/export/'.$table['name'].'?type=csv')" download="{{ $table['name'] }}.table.csv">@lang('Export entries (CSV)')</a></li>
                </ul>
            </div>
            @endif

        </li>
    </ul>

</div>