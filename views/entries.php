
<style>
@if($table['color'])
.app-header { border-top: 8px {{ $table['color'] }} solid; }
@endif
.uk-table th.tables-less-padding, .uk-table td.tables-less-padding {
    padding-left: 5px;
    padding-right: 5px;
}
.uk-table th.tables-less-padding .uk-checkbox, .uk-table td.tables-less-padding .uk-checkbox {
    margin: 0;
}
.tables-dropdown-checkbox {
    top: auto;
    margin-right: 5px;
}
.uk-dropdown-close {
    position: absolute;
    top: .5em;
    right: .5em;
    z-index: 1000;
}
/* fix for misplaced check icon */
.uk-checkbox:checked::after {
    transform: translate(20%, 20%);
}
th div {
    text-transform: none;
    letter-spacing: normal;
    font-weight: normal;
}

/* fix scroll bars in page dropdown */
.uk-breadcrumb .uk-dropdown .uk-scrollable-box {
    overflow-x: hidden;
}

body.fullscreen .uk-overflow-container {
    overflow: unset;
    -webkit-overflow-scrolling: unset;
}

body.fullscreen .table-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    max-width: 100%;
    padding: .2rem;
    height: calc(100vh);
    background-color: #fafafa;
    box-sizing: border-box;
    overflow: auto;
    -webkit-overflow-scrolling: touch;
    z-index: 11;
}

body.fullscreen #toggleFullscreen {
    position: fixed;
    top: 2px;
    right: 20px;
    z-index: 12;
}
</style>

<script>

function TableHasFieldAccess(field) {

    var acl = field.acl || [];

    if (field.name == '_modified' ||
        App.$data.user.group == 'admin' ||
        !acl ||
        (Array.isArray(acl) && !acl.length) ||
        acl.indexOf(App.$data.user.group) > -1 ||
        acl.indexOf(App.$data.user._id) > -1
    ) { return true; }

    return false;
}

</script>

<script type="riot/tag" src="@base('tables:assets/entries-batchedit.tag')"></script>

@render('tables:views/partials/breadcrumbs.php', compact('table'))

<div class="uk-margin-top" riot-view>

    <div class="uk-float-right" id="toggleFullscreen">
        <a class="uk-button {fullscreen ? 'uk-button-small' : ''}" onclick="{ toggleFullscreen }" title="@lang('Toggle fullscreen mode')" data-uk-tooltip><i class="uk-icon-arrows-alt"></i></a>
    </div>

    <div class="uk-margin uk-text-center uk-text-muted" show="{ (Array.isArray(entries) && entries.length) || filter}">

        <img class="uk-svg-adjust" src="@url($table['icon'] ? 'assets:app/media/icons/'.$table['icon']:'tables:icon.svg')" width="50" alt="icon" data-uk-svg>
        @if($table['description'])
        <div class="uk-container-center uk-margin-top uk-width-medium-1-2">
            {{ htmlspecialchars($table['description']) }}
        </div>
        @endif
    </div>

    <div class="table-container">

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ loading }">

            <div class="uk-animation-fade uk-text-center">

                <cp-preloader class="uk-container-center"></cp-preloader>

            </div>

        </div>

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ !loading && !entries.length && !filter }">

            <div class="uk-animation-scale">

                <img class="uk-svg-adjust" src="@url($table['icon'] ? 'assets:app/media/icons/'.$table['icon']:'tables:icon.svg')" width="50" alt="icon" data-uk-svg>
                @if($table['description'])
                <div class="uk-margin-top uk-text-small uk-text-muted">
                    {{ htmlspecialchars($table['description']) }}
                </div>
                @endif
                <hr>
                <span class="uk-text-large"><strong>@lang('No entries').</strong> <a href="@route('/tables/entry/'.$table['name'])">@lang('Create an entry').</a></span>

            </div>

        </div>

        <div class="uk-clearfix uk-margin-top uk-position-relative" show="{ !loading && (entries.length || filter) }">

            <div class="uk-float-left uk-margin-right">

                <div class="uk-button-group">
                    <button class="uk-button uk-button-large {!experimental && 'uk-text-muted'}" onclick="{ toggleExperimental }" title="@lang('experimental')" data-uk-tooltip><i class="uk-icon-filter"></i></button>
                </div>

            </div>

            <div class="uk-width-medium-1-2 uk-float-left">
                <div class="uk-child-width">

                    <div class="uk-form-icon uk-form uk-width-small-3-4 uk-text-muted">

                        <i class="uk-icon-search" title="@lang('Fulltext search')" data-uk-tooltip></i>
                        <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter items...')" onchange="{ updatefilter }">

                    </div>

                    <div class="uk-width-small-1-4 uk-form-icon uk-float-right uk-text-nowrap uk-margin-small-top uk-text-right" >
                        <a class="uk-button uk-button-small uk-text-muted" onclick="{ updatefilter }">
                            @lang('search')
                        </a>
                        <a class="" title="@lang('Clear search')" onclick="{ clearFilter }" data-uk-tooltip>
                            <i class="uk-icon-close"></i>
                        </a>
                    </div>

                </div>
            </div>

            <div class="uk-position-top-right">

                <div class="uk-display-inline-block uk-margin-small-right" data-uk-dropdown="mode:'click'" if="{ selected.length }">
                    <button class="uk-button uk-button-large uk-animation-fade">@lang('Batch Action') <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span></button>
                    <div class="uk-dropdown">
                        <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                            <li class="uk-nav-header">@lang('Actions')</li>
<!-- not tested yet
                            <li><a onclick="{ batchedit }">@lang('Edit')</a></li>
-->
                            @if($app->module('tables')->hasaccess($table['name'], 'entries_delete'))
                            <li class="uk-nav-item-danger"><a onclick="{ removeselected }">@lang('Delete')</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if($app->module('tables')->hasaccess($table['name'], 'entries_create'))
                <a class="uk-button uk-button-large uk-button-primary" href="@route('/tables/entry/'.$table['name'])">@lang('Add Entry')</a>
                @endif

            </div>

        </div>

        <div id="experimental-filter" class="uk-margin-small" if="{experimental}">

            <div class="uk-button-dropdown" data-uk-dropdown="mode:'click'">

                <button class="uk-button">@lang('Display fields')</button>

                <div class="uk-dropdown uk-dropdown-width-2">
                    <div class="uk-grid uk-dropdown-grid uk-dropdown-scrollable">

                        <a class="uk-dropdown-close uk-icon-close"></a>
                        <div class="uk-width-1-2">
                            <strong>@lang('Show')</strong>
                            <div class="" each="{field,idy in fields}">

                                <input class="uk-checkbox tables-dropdown-checkbox" type="checkbox" data-field="{ field.name }" onchange="{ toggleVisibleFields }" checked="{ visibleFields.indexOf(field.name) != -1 }" id="visible_fields_{ field.name }">
                                <label for="visible_fields_{ field.name }">{ field.label || field.name }</label>

                            </div>
                        </div>
                        <div class="uk-width-1-2">
                            <strong>@lang('Hide')</strong>
                            <div class="" each="{field,idy in fields}">

                                <input class="uk-checkbox tables-dropdown-checkbox" type="checkbox" data-field="{ field.name }" onchange="{ toggleHiddenFields }" checked="{ hiddenFields.indexOf(field.name) != -1 }" id="hidden_fields_{ field.name }">
                                <label for="hidden_fields_{ field.name }">{ field.label || field.name }</label>

                            </div>
                        </div>
                    </div>
                    <div class="uk-margin-small-top uk-button-group">
                        <button class="uk-button uk-button-primary" onclick="{ filterFields }">@lang('Apply')</button>
                        <button class="uk-button" onclick="{ resetFieldsFilter }">@lang('Reset')</button>
                    </div>
                </div>

            </div>

            <div class="uk-button-dropdown" data-uk-dropdown="mode:'click'">

                <button class="uk-button">@lang('Field equals')</button>

                <div class="uk-dropdown uk-dropdown-width-2">
                    <div class="uk-dropdown-scrollable">

                            <a class="uk-dropdown-close uk-icon-close"></a>

                            <div class="uk-grid uk-grid-collapse uk-grid-match" each="{field,idy in fields}">
                                <span class="uk-width-1-3 uk-flex-right uk-text-right { field.type == 'relation' && field.options.type != 'one-to-many' && 'uk-text-muted' }">{ field.label || field.name }</span>
                                <span class="uk-form-icon uk-width-2-3" if="{ field.type != 'relation' || field.type == 'relation' && field.options.type == 'one-to-many' }">
                                    <i class="uk-icon-search"></i>
                                    <input class="uk-form-blank" type="text" placeholder="@lang('Filter items...')" onchange="{ filterEquals }" bind="filter.{ field.type != 'relation' ? field.name : field.options.source.display_field }">
                                </span>
                                <span class="uk-margin-small-left uk-text-muted" if="{ field.type == 'relation' && field.options.type != 'one-to-many' }">n/a</span>
                            </div>

                    </div>
                    <div class="uk-margin-small-top uk-button-group">
                        <button class="uk-button uk-button-primary" onclick="{ filterEquals }">@lang('Apply')</button>
                        
                        <button class="uk-button" onclick="{ resetEqualsFilter }">@lang('Reset')</button>
                    </div>
                </div>

            </div>

            <div class="uk-button-dropdown" data-uk-dropdown="mode:'click'">

                <button class="uk-button">@lang('Export current view')</button>

                <div class="uk-dropdown uk-dropdown-small">

                    <form action="{ App.route('/tables/export/') + table.name }">

                        <div class="">

                            <input if="{sort}" each="{ v,idx in sort }" type="hidden" name="options[sort][{idx}]" value="{v}">
                            <input if="{typeof filter == 'object'}" each="{ v,idx in filter }" type="hidden" name="options[filter][{idx}]" value="{v}">
                            <input if="{typeof filter == 'string'}" type="hidden" name="options[filter]" value="{filter}">
                            <input if="{fieldsFilter}" each="{ v,idx in fieldsFilter }" type="hidden" name="options[fields][{idx}]" value="{v === true ? 1 : 0}">
                            <input if="{limit}" type="hidden" name="options[limit]" value="{limit}">
                            <input if="{limit && page}" type="hidden" name="options[skip]" value="{(page -1) * limit}">
                            <input type="hidden" name="options[populate]" value="2">

                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Actions')</li>
                                <li class="uk-text-truncate"><button name="type" value="ods" type="submit" class="uk-button uk-button-small uk-button-link">@lang('Export entries (ODS)')</button></li>
                                <li class="uk-text-truncate"><button name="type" value="xlsx" type="submit" class="uk-button uk-button-small uk-button-link">@lang('Export entries (XLSX)')</button></li>
                                <li class="uk-text-truncate"><button name="type" value="csv" type="submit" class="uk-button uk-button-small uk-button-link">@lang('Export entries (CSV)')</button></li>
                                <li class="uk-text-truncate"><button name="type" value="json" type="submit" class="uk-button uk-button-small uk-button-link">@lang('Export entries (JSON)')</button></li>
                            </ul>

                        </div>

                    </form>
                </div>

            </div>

        </div>


        <div class="uk-margin-top" show="{ !loading && (entries.length || filter) }">

        <div class="uk-text-xlarge uk-text-muted uk-viewport-height-1-3 uk-flex uk-flex-center uk-flex-middle" if="{ !entries.length && filter && !loading }">
            <div>@lang('No entries found')</div>
        </div>
     
        @render('tables:views/partials/pagination.php')

        <div class="uk-margin-top uk-overflow-container" if="{ entries.length && !loading }">
            <table class="uk-table uk-table-tabbed uk-table-striped">
                <thead>
                    <tr>
                        <th class="tables-less-padding"><input class="uk-checkbox tables-less-padding" type="checkbox" data-check="all"></th>

                        @if($app->module('tables')->hasaccess($table['name'], 'entries_edit'))
                        <th class="tables-less-padding"></th>
                        @endif

                        <th width="{field.name == '_modified' || field.name == '_created' ? '100':''}" class="uk-text-small" each="{field,idx in fields}" if="{ (!experimental && field.name != _id) || ( experimental && !visibleFields.length && !hiddenFields.length ) || ( experimental && visibleFields.length && visibleFields.indexOf(field.name) != -1 ) || ( experimental && hiddenFields.length && hiddenFields.indexOf(field.name) == -1 ) }">

                            <a class="uk-link-muted uk-noselect { (parent.sort[field.name] || parent.sort[field.name+'.display']) ? 'uk-text-primary':'' }" onclick="{ parent.updatesort }" data-sort="{ field.name }">

                                { field.label || field.name }

                                <span if="{(parent.sort[field.name] || parent.sort[field.name+'.display'])}" class="uk-icon-long-arrow-{ (parent.sort[field.name] == 1 || parent.sort[field.name+'.display']==1) ? 'up':'down'}"></span>
                            </a>
                        </th>
                        <th width="20"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr each="{entry,idx in entries}">
                        <td class="tables-less-padding"><input class="uk-checkbox" type="checkbox" data-check data-id="{ entry[_id] }"></td>

                        @if($app->module('tables')->hasaccess($table['name'], 'entries_edit'))
                        <td class="tables-less-padding"><a href="@route('/tables/entry/'.$table['name'])/{ entry[_id] }"><i class="uk-icon-pencil uk-icon-hover"></i></a></td>
                        @endif

                        <td class="uk-text-truncate" each="{field,idy in parent.fields}" if="{ (!experimental && field.name != _id) || ( experimental && !visibleFields.length && !hiddenFields.length ) || ( experimental && visibleFields.length && visibleFields.indexOf(field.name) != -1 ) || ( experimental && hiddenFields.length && hiddenFields.indexOf(field.name) == -1 ) }">
                            <a class="uk-link-muted" href="@route('/tables/entry/'.$table['name'])/{ parent.entry[_id] }" if="{!experimental}">
                                <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name], field) }" if="{parent.entry[field.name] !== undefined}"></raw>
                                <span class="uk-icon-eye-slash uk-text-muted" if="{parent.entry[field.name] === undefined}"></span>
                            </a>
                            <span class="uk-link-muted" if="{experimental}">
                                <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name], field) }" if="{parent.entry[field.name] !== undefined}"></raw>
                                <span class="uk-icon-eye-slash uk-text-muted" if="{parent.entry[field.name] === undefined}"></span>
                            </span>
                        </td>

                        <td>
                            <span data-uk-dropdown="mode:'click'">

                                <a class="uk-icon-bars"></a>

                                <div class="uk-dropdown uk-dropdown-flip">
                                    <ul class="uk-nav uk-nav-dropdown">
                                        <li class="uk-nav-header">@lang('Actions')</li>

                                        @if($app->module('tables')->hasaccess($table['name'], 'entries_edit'))
                                        <li><a href="@route('/tables/entry/'.$table['name'])/{ entry[_id] }">@lang('Edit')</a></li>

                                        @else
                                        <li><a href="@route('/tables/entry/'.$table['name'])/{ entry[_id] }">@lang('View')</a></li>
                                        @endif

                                        @if($app->module('tables')->hasaccess($table['name'], 'entries_delete'))
                                        <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                                        @endif

                                        @if($app->module('tables')->hasaccess($table['name'], 'entries_create'))
                                        <li class="uk-nav-divider"></li>
                                        <li><a class="uk-dropdown-close" onclick="{ parent.duplicateEntry }">@lang('Duplicate')</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @render('tables:views/partials/pagination.php')

        </div>

    </div>

    <entries-batchedit table="{table}" fields={fieldsidx}></entries-batchedit>


    <script type="view/script">

        var $this = this, $root = App.$(this.root);

        this.table = {{ json_encode($table) }};
        this._id = this.table.primary_key;
        this.loadmore   = false;
        this.loading    = true;
        this.count      = 0;
        this.page       = 1;
        this.limit      = 20;
        this.entries    = [];
        this.fieldsidx  = {};
        this.imageField = null;

        this.fields     = this.table.fields.filter(function(field){

            if (!TableHasFieldAccess(field)) return false;

            $this.fieldsidx[field.name] = field;

            if (!$this.imageField && (field.type=='image' || field.type=='asset')) {
                $this.imageField = field;
            }

            return field.lst;
        });

        this.sort     = {[this.table.primary_key]: -1};
        this.selected = [];

        this.fullscreen   = Boolean(App.session.get('tables.entries.'+this.table.name+'.fullscreen'));
        this.experimental = Boolean(App.session.get('tables.entries.'+this.table.name+'.experimental'));

        this.fieldsFilter = {};
        this.hide = [];

        this.loadOptions = {}; // needed for loading initial data and for populate comparison when duplicating data

        // experimental fields filter
        this.visibleFields = [];
        this.hiddenFields = [];

        riot.util.bind(this);

        this.on('mount', function(){

            Mousetrap.bindGlobal(['escape'], function(e) {

                if ($this.fullscreen) {
                    jQuery('body').removeClass('fullscreen');
                }

            });

            if (this.fullscreen) {
                jQuery('body').addClass('fullscreen');
            }

            $root.on('click', '[data-check]', function() {

                if (this.getAttribute('data-check') == 'all') {
                    $root.find('[data-check][data-id]').prop('checked', this.checked);
                }

                $this.checkselected();
                $this.update();
            });

            window.addEventListener('popstate', function(e) {
                $this.initState();
            });

            $this.initState();
        });

        initState() {

            var searchParams = new URLSearchParams(location.search);

            if (searchParams.has('q')) {

                try {

                    var q = JSON.parse(searchParams.get('q'));

                    if (q.sort) this.sort = q.sort;
                    if (q.page) this.page = q.page;
                    if (q.limit) this.limit = (parseInt(q.limit) || 20);
                    if (q.filter) {
                        this.filter = q.filter;
                        // this.refs.txtfilter.value = q.filter;
                        this.refs.txtfilter.value = typeof q.filter == 'string' ? q.filter : 'experimental item search';
                    }
                    if (q.fields) {
                        this.fieldsFilter = q.fields;
                        this.hideFields();
                    }

                } catch(e){}
            }

            this.load(true);
            this.update();
        }

        remove(e, entry, idx) {

            entry = e.item.entry
            idx   = e.item.idx;

            App.ui.confirm("Are you sure?", function() {

                App.request('/tables/delete_entries/'+$this.table.name, {filter: {[this._id]:entry[this._id]}}).then(function(data) {

                    if (!data || typeof data.error != 'undefined') {
                        
                        var error = data.error || "to do: descriptive error message";
                        
                        App.ui.notify(error, "danger");
                        
                        return;
                        
                    }
                    
                    App.ui.notify("Entry removed", "success");

                    $this.entries.splice(idx, 1);

                    if ($this.pages > 1 && !$this.entries.length) {
                        $this.page = $this.page == 1 ? 1 : $this.page - 1;
                        $this.load();
                        return;
                    }

                    $this.update();

                    $this.checkselected();
                });

            }.bind(this));
        }

        removeselected() {

            if (!this.selected.length) {
                return;
            }

            App.ui.confirm("Are you sure?", function() {

                var promises = [];

                this.entries = this.entries.filter(function(entry, yepp){

                    yepp = ($this.selected.indexOf(entry[$this._id]) === -1);

                    if (!yepp) {
                        promises.push(App.request('/tables/delete_entries/'+$this.table.name, {filter: {[$this._id]:entry[$this._id]}}));
                    }

                    return yepp;
                });

                Promise.all(promises).then(function(){

                    App.ui.notify(promises.length > 1 ? (promises.length + " entries removed") : "Entry removed", "success");

                    $this.loading = false;

                    if ($this.pages > 1 && !$this.entries.length) {
                        $this.page = $this.page == 1 ? 1 : $this.page - 1;
                        $this.load();
                    } else {
                        $this.update();
                    }

                });

                this.loading = true;
                this.update();
                this.checkselected(true);

            }.bind(this));

        }

        load(initial) {

            this.loadOptions = { sort:this.sort };

            if (this.filter) {
                this.loadOptions.filter = this.filter;
            }

            if (this.fieldsFilter) {
                this.loadOptions.fields = this.fieldsFilter;
            }

            if (this.limit) {
                this.loadOptions.limit = this.limit;
            }

            this.loadOptions.skip  = (this.page - 1) * this.limit;

            // trigger auto-join
            // 1: one-to-many
            // 2: many-to-many
            this.loadOptions.populate = 2;

            this.loading = true;

            if (!initial) {
                this.pushHistoryState();
            }

            App.request('/tables/find', {table:this.table.name, options:this.loadOptions}).then(function(data){

                window.scrollTo(0, 0);

                this.entries = data.entries;
                this.pages   = data.pages;
                this.page    = data.page;
                this.count   = data.count;

                this.loadmore = data.entries.length && data.entries.length == this.limit;

                this.checkselected();
                this.loading = false;
                this.update();

            }.bind(this));

        }

        pushHistoryState() {
            window.history.pushState(
                null, null,
                App.route(['/tables/entries/', this.table.name, '?q=', JSON.stringify({
                    page: this.page || null,
                    filter: this.filter || null,
                    sort: this.sort || null,
                    limit: this.limit,
                    fields: this.fieldsFilter || null
                })].join(''))
            );
        }

        loadpage(page) {
            this.page = page > this.pages ? this.pages : page;
            this.load();
        }

        updatesort(e, field) {

            e.preventDefault();

            field = e.target.getAttribute('data-sort');

            if (!field) {
                return;
            }

            var col = field;

            switch (this.fieldsidx[field].type) {
                case 'tablelink':
                    col = field+'.display';
                    break;
                case 'location':
                    col = field+'.address';
                    break;
                default:
                    col = field;
            }

            if (e.metaKey || e.ctrlKey) {
                // multi select
            } else {

                var sort = {};

                if (this.sort[col]) {
                    sort[col] = this.sort[col];
                }

                this.sort = sort;
            }

            if (!this.sort[col]) {
                this.sort[col] = 1;
            } else {
                this.sort[col] = this.sort[col] == 1 ? -1 : 1;
            }

            this.entries = [];
            this.load();
        }

        checkselected(update) {

            var checkboxes = $root.find('[data-check][data-id]'),
                selected   = checkboxes.filter(':checked');

            this.selected = [];

            if (selected.length) {

                selected.each(function(){
                    $this.selected.push(App.$(this).attr('data-id'));
                });
            }

            $root.find('[data-check="all"]').prop('checked', checkboxes.length && checkboxes.length === selected.length);

            if (update) {
                this.update();
            }
        }

        clearFilter() {

            this.refs.txtfilter.value = null;
            
            $this.updatefilter();
        }

        updatefilter() {

            var load = this.filter ? true:false;

            this.filter = this.refs.txtfilter.value || null;

            if (this.filter || load) {
                this.entries = [];
                this.loading = true;
                this.page = 1;
                this.load();
            }
        }

        filterEquals(e) {

            var load = this.filter ? true:false;

            // fix filtering on empty strings
            if (this.filter && typeof this.filter != 'string') {
                for (var k in this.filter) {
                    if (this.filter[k] === '') delete this.filter[k];
                }
            }

            if (this.filter || load) {
                this.entries = [];
                this.loading = true;
                this.page = 1;
                this.refs.txtfilter.value = 'experimental item search';
                this.load();
            }
        }

        resetEqualsFilter() {
            this.filter = null;
            this.entries = [];
            this.loading = true;
            this.page = 1;
            this.refs.txtfilter.value = '';
            this.load();
        }

        toggleHiddenFields(e) {

            var field = e.target.dataset.field,
                index = this.hiddenFields.indexOf(field);

            if (e.target.checked && index == -1) {
                this.hiddenFields.push(field);
            } else {
                this.hiddenFields.splice(index, 1);
            }

            if (this.hiddenFields.length > 0) {
                this.visibleFields = [];
            }

        }

        toggleVisibleFields(e) {

            var field = e.target.dataset.field,
                index = this.visibleFields.indexOf(field);

            if (e.target.checked && index == -1) {
                this.visibleFields.push(field);
            } else {
                this.visibleFields.splice(index, 1);
            }

            if (this.visibleFields.length > 0) {
                this.hiddenFields = [];
            }

        }

        applyFieldsFilters() {

            // positive projection
            if (this.visibleFields.length) {
                this.fieldsFilter = {};
                this.visibleFields.map(function(e) {
                    $this.fieldsFilter[e] = true;
                });
            }

            // negative projection
            else if (this.hiddenFields.length) {
                this.fieldsFilter = {};
                this.hiddenFields.map(function(e) {
                    $this.fieldsFilter[e] = false;
                });
            }

        }

        filterFields(e) {

            if (e) e.preventDefault();
            
            this.applyFieldsFilters();

            if (Object.keys(this.fieldsFilter).length) {

                this.entries = [];
                this.loading = true;
                this.page = 1;
                // this.refs.txtfilter.value = 'experimental item search';
                this.load();
            }

        }

        hideFields() {

            if (Object.keys(this.fieldsFilter).length == 0) return;

            for (var k in this.fieldsFilter) {
                if (this.fieldsFilter[k] == true) {
                    this.visibleFields.push(k);
                }
                if (this.fieldsFilter[k] == false) {
                    this.hiddenFields.push(k);
                }
            }

            // positive projection
            if (this.visibleFields.length) {
                this.hiddenFields = [];
            }

            // negative projection
            else if (this.hiddenFields.length) {
                this.visibleFields = [];
            }

        }

        resetFieldsFilter(e) {
            if (e) e.preventDefault();
            
            this.hiddenFields = [];
            this.visibleFields = [];
            this.fieldsFilter = {};
            this.entries = [];
            this.loading = true;
            this.page = 1;
            // this.refs.txtfilter.value = 'experimental item search';
            this.load();
        }

        updateLimit(limit) {
            this.limit = limit;
            this.page = 1;
            this.load();
        }

        duplicateEntry(e, table, entry, idx) {

            table = this.table.name;
            entry = App.$.extend({}, e.item.entry);
            idx   = e.item.idx;

            if (!this.loadOptions.populate) {

                delete entry[this._id];

                App.request('/tables/save_entry/'+this.table.name, {"entry": entry}).then(function(entry) {

                    if (entry) {
                        $this.entries.unshift(entry);
                        App.ui.notify("Entry duplicated", "success");
                        $this.update();
                    }
                });

                return;
            }

            // workaround to duplicate entry with populated data

            var options = this.loadOptions;
            delete options.populate;
            options.filter = {[this._id]:entry[this._id]};

            App.request('/tables/find', {table:this.table.name, options:options}).then(function(data){

                if (data && data.entries && data.entries[0]) {

                    entry = data.entries[0];
                    delete entry[this._id];

                    App.request('/tables/save_entry/'+this.table.name, {"entry": entry}).then(function(entry) {

                        if (entry) {
                            $this.entries.unshift(entry);
                            App.ui.notify("Entry duplicated", "success");
                            $this.update();
                        }

                    });

                }

            }.bind(this));

        }

        toggleExperimental() {

            this.experimental = !this.experimental;

            App.session.set('tables.entries.'+this.table.name+'.experimental', this.experimental);
        }
        
        toggleFullscreen() {

            if (!this.fullscreen) {
                jQuery('body').addClass('fullscreen');
                this.fullscreen = true;
                App.session.set('tables.entries.'+this.table.name+'.fullscreen', this.fullscreen);
            } else {
                jQuery('body').removeClass('fullscreen');
                this.fullscreen = false;
                App.session.set('tables.entries.'+this.table.name+'.fullscreen', this.fullscreen);
            }

        }

        batchedit() {
            this.tags['entries-batchedit'].open(this.entries, this.selected)
        }

    </script>

</div>
