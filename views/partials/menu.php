<ul class="uk-nav uk-nav-side uk-nav-dropdown uk-margin-top">

    <li class="uk-nav-header">@lang('Tables')</li>

    @foreach($tables as $table)
    <li>
        <a class="uk-flex uk-flex-middle" href="@route('/collections/entries/'.$table['name'])">
            <i class="uk-icon-justify"><img class="uk-svg-adjust" src="@base('tables:icon.svg')" width="20" height="20" data-uk-svg></i> {{ htmlspecialchars($table['label'] ? $table['label'] : $table['name']) }}
        </a>
    </li>
    @endforeach
</ul>
