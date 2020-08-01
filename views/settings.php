
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
<!--
            <li class="{ tab=='relations' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="relations">{ App.i18n.get('Relation Manager') }</a></li>
-->
            <li class="{ tab=='other' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="other">{ App.i18n.get('Other') }</a></li>

        </ul>
        
    </div>

    <div class="uk-grid">

        <div class="uk-width-medium-1-1" show="{tab == 'auth'}">

            <div class="uk-panel uk-panel-box uk-panel-space uk-panel-card uk-margin" if="{ !Object.keys(acl_groups).length }">
                @lang('No user groups found')
            </div>

                <div class="uk-width-1-1" if="{ Object.keys(hardcoded).length }">

                    <div class="uk-panel uk-panel-box uk-panel-space uk-panel-card uk-margin">
                        <i class="uk-icon uk-icon-warning"></i>
                        <span>@lang('Settings with a warning icon are hardcoded via config file and cannot be overwritten via graphical user interface.')</span>
                    </div>
                </div>

            <div class="uk-grid" data-uk-grid-margin>

                <div class="uk-width-medium-1-2 uk-width-large-1-3" each="{acl, acl_group in acl_groups}" if="{ Object.keys(acl_groups).length }">

                    <div class="uk-panel uk-panel-box uk-panel-space uk-panel-card uk-margin">

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
                                        <i class="uk-icon uk-icon-warning" if="{ typeof hardcoded[acl_group][action] != 'undefined' }"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="uk-width-1-1 uk-grid" show="{tab == 'general'}">

            <div class="uk-width-medium-1-2 uk-width-xlarge-2-5">

                <div class="uk-panel uk-panel-box uk-panel-card uk-panel-box-secondary">

                    <h2 class="uk-panel-title">@lang('Problems')</h2>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{ !diff && !Object.keys(wrongRelations).length && !Object.keys(missingRelations).length }">
                        <span>@lang('Everything is fine.')</span>
                    </div>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{diff}">

                        <h3 class="">@lang('New or missing tables in the database'):</h3>
                        <span>@lang('Some database tables don\'t exist in the storage, yet.')</span>

                        <div class="uk-width-1-1">

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

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{ missingTables.length }">

                        <h4 class="">@lang('Wrong tables in storage'):</h4>
                        <span>@lang('Some stored tables don\'t exist in the database.')</span>

                        <div class="uk-margin">
                            <a class="uk-badge" onclick="{ removeMissingTables }" title="@lang('Remove missing tables from storage')" data-uk-tooltip>
                                <span>@lang('Fix it')</span>
                            </a>
                        </div>

                        <div class="uk-width-1-1">

                            <div class="uk-width-1-1 uk-margin-small" each="{ table in missingTables }">

                                <div class="uk-width-1-1 uk-margin-small" if="{ !tables[origTable] }">
                                    
                                    <div class="uk-panel uk-panel-box uk-panel-card">
                                        {table}

                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{ Object.keys(missingRelations).length }">

                        <h3 class="">@lang('Missing relations')</h3>

                        <div>
                            <div class="uk-margin">
                                <a class="uk-button uk-button-large uk-button-primary" onclick="{ fixWrongRelations }">@lang('Fix wrong relations')</a>
                            </div>
                            <ul>
                                <li class="" each="{ table,idx in missingRelations }">
                                    <strong>{idx}</strong>
                                    <ul>
                                        <li class="" each="{ field, idy in table }">
                                          {idy}
                                          <ul>
                                              <li class="" each="{ reference, idz in field }">
                                                {idz}
                                                <ul if="{idz == 'references'}">
                                                    <li class="" each="{ vxx, idxx in reference }">
                                                      <code>{idxx}: {vxx}</code>
                                                    </li>
                                                </ul>
                                                <ul if="{idz == 'is_referenced_by'}" each="{ idyy in reference }">
                                                    <li class="" each="{ vxx,idxx in idyy }">
                                                      <code>{idxx}: {vxx}</code>
                                                    </li>
                                                </ul>
                                              </li>
                                          </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                    </div>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{ Object.keys(wrongRelations).length }">

                        <h3 class="">@lang('Wrong relations')</h3>

                        <div>
                            <div class="uk-margin">
                                <a class="uk-button uk-button-large uk-button-primary" onclick="{ fixWrongRelations }">@lang('Fix wrong relations')</a>
                            </div>
                            <ul>
                                <li class="" each="{ table,idx in wrongRelations }">
                                    <strong>{idx}</strong>
                                    <ul>
                                        <li class="" each="{ field, idy in table }">
                                          {idy}
                                          <ul>
                                              <li class="" each="{ reference, idz in field }">
                                                {idz}
                                                <ul if="{idz == 'references'}">
                                                    <li class="" each="{ vxx, idxx in reference }">
                                                      <code>{idxx}: {vxx}</code>
                                                    </li>
                                                </ul>
                                                <ul if="{idz == 'is_referenced_by'}" each="{ idyy in reference }">
                                                    <li class="" each="{ vxx,idxx in idyy }">
                                                      <code>{idxx}: {vxx}</code>
                                                    </li>
                                                </ul>
                                              </li>
                                          </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                    </div>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{ true }">

                        <h3 class="">@lang('Schema comparison')</h3>

                        <p>to do...</p>

                        <div>
                            <ul>
                                <li class="" each="{ schema,idx in wrongSchemas }">
                                    <strong>{idx}</strong>
                                    <a class="uk-button uk-button-small uk-button-primary" onclick="{ cleanStoredDatabaseSchema }">@lang('Fix it')</a>
                                    <pre>{ JSON.stringify(schema) }</pre>
                                </li>
                            </ul>
                        </div>

                    </div>

                </div>
            </div>

            <div class="uk-width-medium-1-2 uk-width-xlarge-3-5">
                <div class="uk-panel uk-panel-box uk-panel-box-secondary uk-panel-card">

                    <h2 class="uk-panel-title">@lang('Tables')</h2>

                    <div class="uk-grid uk-grid-small uk-grid-gutter uk-grid-match">

                        <div class="uk-width-xlarge-1-2" each="{ group in groups }">
                            <div class="uk-panel uk-panel-box uk-panel-card">
                                <span class="uk-text-uppercase">{ group }</span>

                                <ul>
                                    <li each="{ table, idx in tables}" if="{ table.group && table.group == group }">
                                        <a href="@route('/tables/table/'){table.name}">
                                        <img class="uk-margin-small-right uk-svg-adjust" src="{ table.icon ? App.base('/assets/app/media/icons/'+table.icon) : App.base('/addons/Tables/icon.svg') }" width="16px" alt="icon" style="color:{table.color || ''}" data-uk-svg>
                                        { table.label ? table.label : table.name }
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
<!--
        <div class="uk-width-1-1 uk-grid" show="{tab == 'relations'}">

            <div class="uk-width-1-3">
                
            </div>

        </div>
-->
        <div class="uk-width-1-1 uk-grid" show="{tab == 'other'}">

            <div class="uk-width-medium-1-3">

                <div class="uk-panel uk-panel-box uk-panel-card uk-margin">
                    <span>@lang('Reset all table schemas to database defaults')</span>
                    <a class="uk-badge uk-badge-danger uk-margin-top" onclick="{ initFieldSchema }" title="@lang('')" data-uk-tooltip>
                        <i class="uk-icon-warning uk-margin-small-right"></i><span>@lang('Reset all table schemas')</span>
                    </a>
                </div>
                
            </div>

        </div>

    </div>

    <cp-actionbar>
        <div class="uk-container uk-container-center">
            <a class="uk-button uk-button-large uk-button-primary" onclick="{ saveAcl }" if="{tab == 'auth'}">@lang('Save')</a>
            <a class="uk-button uk-button-link" href="@route('/settings')">@lang('Close')</a>
        </div>
    </cp-actionbar>

    <script type="view/script">

        var $this = this;

        riot.util.bind(this);

        this.tables     = {{ json_encode($tables) }};
        this.origTables = {{ json_encode($origTables) }};

        this.missingRelations = {{ json_encode($missingRelations) }};
        this.wrongRelations   = {{ json_encode($wrongRelations) }};

        this.origSchemas   = {{ json_encode($origSchemas) }};
        this.wrongSchemas   = {{ json_encode($wrongSchemas) }};

        this.groups = [];
        this.diff   = false;
        this.missingTables = [];
        this.storedTables  = [];

        this.tab = 'general';

        this.acl_groups = {{ json_encode($acl_groups) }};
        this.acls       = {{ json_encode($acls) }};
        this.hardcoded  = {{ json_encode($hardcoded) }};

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {

            this.missingTables = [];

            Object.keys(this.tables).forEach(function(table) {
                if ($this.tables[table].group) {
                    $this.groups.push($this.tables[table].group);
                }

                if ($this.origTables.indexOf($this.tables[table]._id) == -1) {
                    // table schema is stored, but it doesn't
                    // exist anymore in the database
                    $this.missingTables.push(table);
                }
            });

            this.origTables.forEach(function(table) {
                if (!$this.tables[table.replace(/\//, '__')]) {
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

        fixWrongRelations(e) {

            App.ui.confirm("Are you sure?", function() {

                App.request('/tables/settings/fixWrongRelations').then(function(data){
                    App.ui.notify("Reinitialization of relations finished", "success");
                    $this.missingRelations = {};
                    $this.wrongRelations   = {};
                    $this.update();
                }).catch(function(){
                    App.ui.notify("Reinitialization of relations failed", "danger");
                });;

            });

        }

        listTables() {

            App.request('/tables/settings/listTables').then(function(data){
                if (data) {
                    $this.missingTables = [];
                    $this.origTables = data;
                    $this.update();
                }
            });

        }

        getTables() {

            App.request('/tables/settings/getTables').then(function(data){
                if (data) {
                    $this.tables = data;
                    $this.update();
                }
            });

        }

        removeMissingTables(e) {

            App.ui.confirm("Are you sure?", function() {

                App.request('/tables/settings/removeMissingTables').then(function(data){

                    if (data && !data.error) {
                        App.ui.notify(data.message || "Removed missing tables" , "success");
                        $this.getTables();
                        $this.listTables();
                        // $this.update();
                    } else {
                        App.ui.notify(data.error || "Removing failed", "danger");
                    }

                });

            });

        }

        cleanStoredDatabaseSchema(e) {

            if (e) e.preventDefault();

            var name = e.item.idx;

            App.ui.confirm("Are you sure?", function() {

                App.request('/tables/settings/cleanStoredDatabaseSchema/'+name).then(function(data){

                    App.ui.notify("Cleaned stored database schema" , "success");
                    $this.tables[name] = data;
                    delete $this.wrongSchemas[name];
                    $this.update();

                }).catch(function(e) {
                    App.ui.notify("Cleaning failed", "danger");
                });

            });

        }

    </script>

</div>
