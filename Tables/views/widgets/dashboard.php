<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex">
            <strong class="uk-panel-box-header-title uk-flex-item-1">
                @lang('Tables')
<!--
                @hasaccess?('tables', 'create')
                <a href="@route('/tables/table')" class="uk-icon-plus uk-margin-small-left" title="@lang('Create Table')" data-uk-tooltip></a>
                @end
-->
            </strong>
            @if(count($tables))
            <span class="uk-badge uk-flex uk-flex-middle"><span>{{ count($tables) }}</span></span>
            @endif
        </div>

        @if(count($tables))

            <div class="uk-margin">

                <ul class="uk-list uk-list-space uk-margin-top">
                    @foreach(array_slice($tables, 0, count($tables) > 5 ? 5: count($tables)) as $col)
                    <li>
                        <div class="uk-grid uk-grid-small">
                            <div class="uk-flex-item-1 uk-text-truncate">
                                <a href="@route('/tables/entries/'.$col['name'])">

                                    <img class="uk-margin-small-right uk-svg-adjust" src="@url(isset($col['icon']) && $col['icon'] ? 'assets:app/media/icons/'.$col['icon']:'tables:icon.svg')" width="18px" alt="icon" data-uk-svg>

                                    {{ htmlspecialchars(@$col['label'] ? $col['label'] : $col['name']) }}
                                </a>
                            </div>
                            <div>
                                @if($app->module('tables')->hasaccess($col['name'], 'entries_create'))
                                <a class="uk-text-muted" href="@route('/tables/entry')/{{ $col['name'] }}" title="@lang('Add entry')" data-uk-tooltip="pos:'right'">
                                    <img src="@url('assets:app/media/icons/plus-circle.svg')" width="1.2em" data-uk-svg />
                                </a>
                                @endif
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>

            </div>

            @if(count($tables) > 5)
            <div class="uk-panel-box-footer uk-text-center">
                <a class="uk-button uk-button-small uk-button-link" href="@route('/tables')">@lang('Show all')</a>
            </div>
            @endif

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="@url('tables:icon.svg')" width="30" height="30" alt="tables" data-uk-svg />
                </p>

                @lang('No tables')
            </div>

        @endif

    </div>

</div>
