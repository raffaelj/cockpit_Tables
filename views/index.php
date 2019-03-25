<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Tables')</span></li>
    </ul>
</div>

<div riot-view>

    <div>

        <div class="uk-margin uk-clearfix" if="{ App.Utils.count(tables) }">

            <div class="uk-form-icon uk-form uk-text-muted">

                <i class="uk-icon-filter"></i>
                <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter tables...')" onkeyup="{ updatefilter }">

            </div>
<!--
            @hasaccess?('tables', 'create')
            <div class="uk-float-right">
                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/tables/table')">@lang('Add Table')</a>
            </div>
            @end
-->
        </div>
<!--
        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(tables) }">

            <div class="uk-animation-scale">

                <p>
                    <img class="uk-svg-adjust uk-text-muted" src="@url('tables:icon.svg')" width="80" height="80" alt="Tables" data-uk-svg />
                </p>
                <hr>
                <span class="uk-text-large"><strong>@lang('No Tables').</strong>
                @hasaccess?('tables', 'create')
                <a href="@route('/tables/table')">@lang('Create one')</a></span>
                @end
            </div>

        </div>
-->
        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(tables) }">

            <div class="uk-animation-scale">

                <p>
                    <img class="uk-svg-adjust uk-text-muted" src="@url('tables:icon.svg')" width="80" height="80" alt="Tables" data-uk-svg />
                </p>
                <hr>
                <span class="uk-text-large"><strong>@lang('No Tables').</strong>
                @hasaccess?('tables', 'create')
                <a onclick="{ initFieldSchema }">@lang('Create tables from database schema')</a></span>
                @end
            </div>

        </div>

        <div class="uk-margin" if="{groups.length}">

            <ul class="uk-tab uk-flex uk-flex-center uk-noselect">
                <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize { group && 'uk-text-muted'}" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                <li class="{ group==parent.group && 'uk-active'}" each="{group in groups}"><a class="uk-text-capitalize { group!=parent.group && 'uk-text-muted'}" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
            </ul>
        </div>

        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-margin-top">

            <div each="{ table, idx in tables }" show="{ ingroup(table.meta) && infilter(table.meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card uk-panel-card-hover">

                    <div class="uk-panel-teaser uk-position-relative">
                        <canvas width="600" height="350"></canvas>
                        <a href="@route('/tables/entries')/{table.name}" class="uk-position-cover uk-flex uk-flex-middle uk-flex-center">
                            <div class="uk-width-1-4 uk-svg-adjust" style="color:{ (table.meta.color) }">
                                <img riot-src="{ table.meta.icon ? '@url('assets:app/media/icons/')'+table.meta.icon : '@url('tables:icon.svg')'}" alt="icon" data-uk-svg>
                            </div>
                        </a>
                    </div>

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown="delay:300">

                            <a class="uk-icon-cog" style="color:{ (table.meta.color) }" href="@route('/tables/table')/{ table.name }" if="{ table.meta.allowed.edit }"></a>
                            <a class="uk-icon-cog" style="color:{ (table.meta.color) }" if="{ !table.meta.allowed.edit }"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/tables/entries')/{table.name}">@lang('Entries')</a></li>
                                    <li><a href="@route('/tables/entry')/{table.name}" if="{ table.meta.allowed.entries_create }">@lang('Add entry')</a></li>
                                    <li if="{ table.meta.allowed.edit || table.meta.allowed.delete }" class="uk-nav-divider"></li>
                                    <li if="{ table.meta.allowed.edit }"><a href="@route('/tables/table')/{ table.name }">@lang('Edit')</a></li>

                                    @hasaccess?('tables', 'delete')
                                    <li class="uk-nav-item-danger" if="{ table.meta.allowed.delete }"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                                    @end
<!--
                                    <li class="uk-nav-divider" if="{ table.meta.allowed.edit }"></li>
                                    <li class="uk-text-truncate" if="{ table.meta.allowed.edit }"><a href="@route('/tables/export')/{ table.name }" download="{ table.meta.name }.table.json">@lang('Export entries')</a></li>
                                    <li class="uk-text-truncate" if="{ table.meta.allowed.edit }"><a href="@route('/tables/import/table')/{ table.name }">@lang('Import entries')</a></li>
-->
                                </ul>
                            </div>
                        </div>

                        <a class="uk-text-bold uk-flex-item-1 uk-text-center uk-link-muted" href="@route('/tables/entries')/{table.name}">{ table.label }</a>
                        <div>
                            <span class="uk-badge" riot-style="background-color:{ (table.meta.color) }">{ table.meta.itemsCount }</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.tables = {{ json_encode($tables) }};
        this.groups = [];

        this.tables.forEach(function(table) {

            if (table.meta.group) {
                $this.groups.push(table.meta.group);
            }
        });

        if (this.groups.length) {
            this.groups = _.uniq(this.groups.sort());

            this.group = this.groups[0]; // display first group instead of all by default
        }

        remove(e, table) {

            table = e.item.table;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('tables:removeTableSchema', table.name).then(function(data) {

                    App.ui.notify("Table removed", "success");

                    $this.tables.splice(e.item.idx, 1);

                    $this.groups = [];

                    $this.tables.forEach(function(table) {
                        if (table.meta.group) $this.groups.push(table.meta.group);
                    });

                    if ($this.groups.length) {
                        $this.groups = _.uniq($this.groups.sort());
                    }

                    $this.update();
                });
            });
        }

        toggleGroup(e) {
            this.group = e.item && e.item.group || false;
        }

        updatefilter(e) {

        }

        ingroup(singleton) {
            return this.group ? (this.group == singleton.group) : true;
        }

        infilter(table, value, name, label) {

            if (!this.refs.txtfilter.value) {
                return true;
            }

            value = this.refs.txtfilter.value.toLowerCase();
            name  = [table.name.toLowerCase(), table.label.toLowerCase()].join(' ');

            return name.indexOf(value) !== -1;
        }

        initFieldSchema() {
            App.request('/tables/init_schema/init_all').then(function() {
                App.reroute('/tables');
            });
        }

    </script>

</div>
