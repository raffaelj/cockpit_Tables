<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Tables')</span></li>
    </ul>
</div>

<div>
    <h2>Tables</h2>
    <ul>
    @foreach ($tables as $key => $table)
        <li>
            <a href="@route('/tables')/entries/{{ $table['name'] }}">{{ $table['label'] }}</a>
            <a href="@route('/tables')/table/{{ $table['name'] }}" class="uk-icon-cog" data-uk-tooltip title="@lang('Edit')"></a>
        </li>
    @endforeach
    </ul>
</div>

<div>
    <h2>Views</h2>
    <ul>
    @foreach ($views as $key => $table)
        <li>
            <a href="@route('/tables')/entries/{{ $table['name'] }}">{{ $table['label'] }}</a>
            <a href="@route('/tables')/table/{{ $table['name'] }}" class="uk-icon-cog" data-uk-tooltip title="@lang('Edit')"></a>
        </li>
    @endforeach
    </ul>
</div>
