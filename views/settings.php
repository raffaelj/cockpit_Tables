
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Tables')</span></li>
    </ul>
</div>

<div riot-view>

    <div class="">

        <ul class="uk-tab uk-margin-large-bottom">

            <li class="{ tab=='general' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="general">{ App.i18n.get('General') }</a></li>
            <li class="{ tab=='auth' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="auth">{ App.i18n.get('Access') }</a></li>
            <li class="{ tab=='relations' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="relations">{ App.i18n.get('Relation Manager') }</a></li>
            @if($app->module('cockpit')->isSuperAdmin())
            <li><a class="" onclick="{showTablesObject}">@lang('Show json')</a></li>
            @endif

        </ul>
        
    </div>

    <div class="uk-grid">

        <div class="uk-width-medium-1-1" show="{tab == 'auth'}">

            <div class="uk-panel uk-panel-box uk-panel-space uk-panel-card uk-margin" if="{!acl_groups.length}">
                @lang('No user groups found')
            </div>

            <div class="uk-panel uk-panel-box uk-panel-space uk-panel-card uk-margin" each="{acl, acl_group in acl_groups}" if="{acl_groups.length}">

                <div class="uk-grid">
                    <div class="uk-width-1-3 uk-flex uk-flex-middle uk-flex-center">
                        <div class="uk-text-center">
                            <p class="uk-text-uppercase uk-text-small uk-text-bold">{ acl_group }</p>
                            <img class="uk-text-primary uk-svg-adjust" src="@url('assets:app/media/icons/accounts.svg')" alt="icon" width="80" data-uk-svg>
                        </div>
                    </div>
                    <div class="uk-flex-item-1">
                        <div class="uk-margin uk-text-small">
                            <div class="uk-margin-top" each="{ action in acls }">
                                <field-boolean bind="acl_groups.{acl_group}.{action}" label="{ action }"></field-boolean>
                                <i class="uk-icon uk-icon-warning" title="@lang('Setting is hardcoded via config file.')" if="{ typeof hardcoded[acl_group][action] != 'undefined' }" data-uk-tooltip></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <cp-actionbar>
                <div class="uk-container uk-container-center">
                    <a class="uk-button uk-button-large uk-button-primary" onclick="{ saveAcl }">@lang('Save')</a>
                </div>
            </cp-actionbar>

        </div>

        <div class="uk-width-1-1 uk-grid" show="{tab == 'general'}">

            <div class="uk-width-large-3-4">
                <div class="uk-panel uk-panel-box uk-panel-box-secondary uk-panel-card">

                <p>more settings and info coming soon...</p>

                <div class="uk-grid uk-grid-small uk-grid-gutter">

                    <div class="uk-width-medium-1-3" each="{ group in groups }">
                        <div class="uk-panel uk-panel-box uk-panel-card">
                            <span class="uk-text-uppercase">{ group }</span>

                            <ul>
                                <li each="{ table, idx in tables}" if="{ table.group && table.group == group }">
                                    <a href="@route('/tables/table/'){table.name}">{ table.label ? table.label : table.name }</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
                </div>

            </div>

            <div class="uk-width-large-1-4">
                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-form-row">
                        <a class="uk-badge uk-badge-danger uk-form-row" onclick="{ initFieldSchema }" title="@lang('')" data-uk-tooltip>
                            <span>@lang('Reset all table schemas to database defaults')</span>
                        </a>
                    </div>

                    <div class="uk-form-row">

                        <div class="uk-margin">
                            <strong>@lang('New or missing tables'):</strong>
                        </div>

                        <span if="{!diff}">@lang('no missing tables found')</span>

                        <div class="uk-width-1-1" if="{diff}">

                            <div class="uk-width-1-1 uk-margin-small" each="{ origTable in origTables }">

                                <div class="uk-width-1-1 uk-margin-small" if="{ !tables[origTable] }">
                                    
                                    <div class="uk-panel uk-panel-box uk-panel-card">
                                        {origTable}
                                        
                                        <a class="uk-badge uk-float-right" onclick="{ resetFieldSchema }" title="@lang('')" data-uk-tooltip>
                                            <span>@lang('init')</span>
                                        </a>

                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="uk-width-1-1 uk-grid" show="{tab == 'relations'}">
<!--
            <div class="uk-width-1-3">
<pre>
{ JSON.stringify(relations, null, 2) }
</pre>
            </div>
            <div class="uk-width-1-3">
<pre>
{ JSON.stringify(storedRelations, null, 2) }
</pre>
            </div>
-->
            <div class="uk-width-1-3" if="{ !Object.keys(relationsDiff).length }">
                <p>@lang('everthing is fine')</p>
            </div>
            <div class="uk-width-1-3" if="{ Object.keys(relationsDiff).length }">
                <strong>@lang('Wrong relations')</strong>
                <a class="uk-button uk-button-large uk-button-primary" onclick="{ fixWrongRelations }">@lang('Fix wrong relations')</a>
                <ul>
                <li class="" each="{ table,idx in relationsDiff }">
                  {idx}
                </li>
                </ul>
<pre>
{ JSON.stringify(relationsDiff, null, 2) }
</pre>
                
            </div>
        </div>


    </div>

    <cp-inspectobject ref="inspect"></cp-inspectobject>

    <script type="view/script">

        var $this = this;
        
        riot.util.bind(this);

        this.tables = {{ json_encode($tables) }};
        this.origTables = {{ json_encode($origTables) }};

        this.relations = {{ json_encode($relations) }};
        this.storedRelations = {{ json_encode($storedRelations) }};
        this.relationsDiff = {{ json_encode($relationsDiff) }};

        this.groups = [];
        this.diff = false;
        
        this.tab = 'relations';
        // this.tab = 'general';
        this.acl_groups = {{ json_encode($acl_groups) }};
        this.acls = {{ json_encode($acls) }};
        this.hardcoded = {{ json_encode($hardcoded) }};

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {

            Object.keys(this.tables).forEach(function(table) {
                if ($this.tables[table].group) {
                    $this.groups.push($this.tables[table].group);
                }
            });

            this.origTables.forEach(function(table) {
                if (!$this.tables[table]) {
                    $this.diff = true;
                    return;
                }
            });

            if (this.groups.length) {
                this.groups = _.uniq(this.groups.sort());
            }

        });

        toggleTab(e) {
            this.tab = e.target.getAttribute('data-tab');
        }

        initFieldSchema() {

            App.ui.confirm("Are you sure?", function() {

                App.request('/tables/init_schema/init_all').then(function() {
                    App.reroute('/settings/tables');
                });

            });
        }

        resetFieldSchema(e) {

            App.ui.confirm("Are you sure?", function() {

                App.request('/tables/init_schema/'+e.item.origTable).then(function(data){
                    App.ui.notify("Field schema resetted", "success");

                    $this.tables[data.name] = data;

                    $this.update();
                });

            });

        }

        saveAcl() {

            App.request('/tables/settings/saveAcl', {acl:this.acl_groups}).then(function(data){
                App.ui.notify("Access Control List saved", "success");
            });

        }

        showTablesObject() {
            $this.refs.inspect.show($this.tables);
            $this.update();
        }

        fixWrongRelations(e) {

            App.ui.confirm("Are you sure?", function() {

                App.request('/tables/settings/fixWrongRelations').then(function(data){
                    App.ui.notify("Reinitialization of relations finished", "success");
                    $this.update();
                });

            });

        }

    </script>

</div>
