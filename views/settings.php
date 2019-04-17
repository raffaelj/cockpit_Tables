
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Tables')</span></li>
    </ul>
</div>

<div riot-view>
    
    <div class="uk-grid">
        
        <div class="uk-width-medium-3-4">
            
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
        
        <div class="uk-width-medium-1-4">
    
            <div class="uk-form-row">
                            
                <a class="uk-badge uk-badge-danger uk-form-row" onclick="{ initFieldSchema }" title="@lang('')" data-uk-tooltip>
                    <span>@lang('Reset all table schemas to database defaults')</span>
                </a>

            </div>
            
            <div class="uk-form-row">
                
                <strong>@lang('New or missing tables'):</strong><br />
                
                <span if="{!diff}">@lang('no missing tables found')</span>
                
                <div class="uk-width-1-1" if="{diff}">
                
                    <div class="uk-width-1-1 uk-margin-small" each="{ origTable in origTables }">
                    
                        <div class="uk-width-1-1 uk-margin-small" if="{ !tables[origTable] }">
                            
                            <div class="uk-panel uk-panel-box uk-panel-card">
                                {origTable}
                                
                                <a class="uk-badge uk-form-row" onclick="{ resetFieldSchema }" title="@lang('')" data-uk-tooltip>
                                    <span>@lang('init')</span>
                                </a>
                        
                            </div>
                        
                        </div>

                    </div>
                </div>
                
            </div>
    
        </div>
        
    </div>

    <script type="view/script">

        var $this = this;

        this.tables = {{ json_encode($tables) }};
        this.origTables = {{ json_encode($origTables) }};

        this.groups = [];
        this.diff = false;

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

        initFieldSchema() {
            App.request('/tables/init_schema/init_all').then(function() {
                App.reroute('/settings/tables');
            });
        }

        resetFieldSchema(e) {

            App.request('/tables/init_schema/'+e.item.origTable).then(function(data){
                App.ui.notify("Field schema resetted", "success");

                $this.tables[data.name] = data;

                $this.update();
            });

        }

    </script>

</div>
