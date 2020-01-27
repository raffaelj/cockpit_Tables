
<field-relation>

<style>
.uk-modal.uk-open {
    background-color: rgba(0,0,0,.1);
}
.uk-modal.uk-open .uk-modal.uk-open > .uk-modal-dialog {
    width: 1070px;
    margin: 70px auto;
}
.uk-modal.uk-open.parent-modal {
    overflow-y: visible !important;
    -webkit-transform: unset;
    transform: unset;
}
.uk-modal.uk-open.parent-modal > .uk-modal-dialog {
    -webkit-transform: unset;
    transform: unset;
}
.field-tag {
    display: inline-block;
    border: 1px solid;
    padding: .1em .4em;
    border-radius: .1em;
    margin: 0 .2em .2em 0;
}
</style>

    <div class="uk-width-medium-1-1 { opts.split && 'uk-grid uk-grid-gutter' }" if="{ field_type == 'select' }">

        <div class="uk-width-medium-1-{ columns }" each="{options,idx in groups}">

            <label class="uk-margin" if="{ idx !== 'main' }"><span class="uk-text-bold">{idx}</span></label>

            <div class="uk-margin-small" if="{ options.length > 6 && !opts.split }">

                <span if="{ selected.length }">{ App.i18n.get('Selected') }:</span>

                <span class="field-tag uk-text-primary" each="{ option in options }" if="{ id(option.value, parent.selected) !==-1 }">
                    <span title="{ option.label.length > 30 && option.label }" data-uk-tooltip>
                        <i class="uk-icon-tag"></i> { truncate(option.label) }
                    </span>
                    <i class="uk-icon-info uk-text-muted uk-margin-small-right" title="{ option.info }" data-uk-tooltip if="{ option.info }"></i>
                    <a onclick="{ parent.toggle }"><i class="uk-icon-close uk-icon-hover"></i></a>
                </span>

            </div>

            <div class="{ options.length > 6 ? 'uk-scrollable-box':'' }">
                <div class="uk-margin-small-top" each="{option in options}">
                    <a class="{ id(option.value, parent.selected) !==-1 || id(option.value_orig, parent.selected) !==-1 ? 'uk-text-primary':'uk-text-muted' }" onclick="{ parent.toggle }">
                        <i class="uk-icon-{ id(option.value, parent.selected) !==-1 || id(option.value_orig, parent.selected) !==-1 ? 'circle':'circle-o' } uk-margin-small-right"></i>
                        <span>{ option.label }</span>
                        <i class="uk-icon-info uk-margin-small-right" title="{ option.info }" data-uk-tooltip if="{ option.info }"></i>
                        <i class="uk-icon-warning uk-margin-small-right" title="{ option.warning }" data-uk-tooltip if="{ option.warning }"></i>
                    </a>
                    <a href="{ App.route('/tables/entry/') + source_table + '/' + option.value }" class="uk-margin-small-left uk-text-muted" if="{ edit_entry }" onclick="{ showDialog }" title="{ App.i18n.get('Edit entry') }" data-uk-tooltip><i class="uk-icon-pencil"></i></a>
                </div>
            </div>
            <span class="uk-text-small uk-text-muted" if="{ options.length > 6 && !opts.split }">{selected.length} { App.i18n.get('selected') }</span>
        </div>

        <span class="uk-text-small uk-text-muted" if="{ options_length > 6  && opts.split }">{selected.length} { App.i18n.get('selected') }</span>

    </div>

    <div class="uk-width-medium-1-1" if="{ field_type == 'edit-content' }">

        <div class="uk-width-medium-1-{ columns }" each="{options,idx in groups}">

            <label class="uk-margin" if="{ idx !== 'main' }"><span class="uk-text-bold">{idx}</span></label>

            <div class="uk-text-center" if="{ !options.length }">

                <a class="uk-margin-small-right uk-text-muted" if="{ new_entry && (relation_type != 'many-to-one' || (relation_type == 'many-to-one' && tables_entry_id)) && parent_id == tables_entry_id }" onclick="{ showDialog }" title="{ App.i18n.get('New entry') }" data-uk-tooltip><i class="uk-icon-plus-circle uk-icon-small"></i></a>

                <span class="uk-text-warning" if="{ new_entry && !((relation_type != 'many-to-one' || (relation_type == 'many-to-one' && tables_entry_id)) && parent_id == tables_entry_id) }">{ App.i18n.get('Field is not available for unsaved entries') }</span>
            </div>

            <div class="{ options.length > 10 ? 'uk-scrollable-box':'' }" if="options.length">

                <div class="uk-margin-small-top" each="{option in options}">

                    <div class="uk-text-muted">

                        <i class="uk-icon-circle-o uk-margin-small-right"></i>
                        <span class="uk-text-muted">{ option.label }</span>
                        <i class="uk-icon-info uk-margin-small-left uk-text-muted" title="{ option.info }" data-uk-tooltip if="{ option.info }"></i>
                        <i class="uk-icon-warning uk-margin-small-left" title="{ option.warning }" data-uk-tooltip if="{ option.warning }"></i>
                        <a href="{ App.route('/tables/entry/') + source_table + '/' + option.value }" class="uk-margin-left uk-text-muted" if="{ edit_entry }" onclick="{ showDialog }" title="{ App.i18n.get('Edit entry') }" data-uk-tooltip><i class="uk-icon-pencil"></i></a>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="uk-width-medium-1-1" if="{ field_type == 'display-content' }">

        <div class="uk-width-medium-1-{ columns }" each="{options,idx in groups}">

            <label class="uk-margin" if="{ idx !== 'main' }"><span class="uk-text-bold">{idx}</span></label>

            <div class="{ options.length > 10 ? 'uk-scrollable-box':'' }">
                <div class="uk-margin-small-top" each="{ option in options }">

                    <div class="uk-text-primary" if="{ id(option.value, parent.selected) !==-1 }">

                        <i class="uk-icon-circle uk-margin-small-right"></i>
                        <span class="uk-text-muted">{ option.label }</span>
                        <i class="uk-icon-info uk-margin-small-left uk-text-muted" title="{ option.info }" data-uk-tooltip if="{ option.info }"></i>
                        <i class="uk-icon-warning uk-margin-small-left" title="{ option.warning }" data-uk-tooltip if="{ option.warning }"></i>
                        <a href="{ App.route('/tables/entry/') + source_table + '/' + option.value }" class="uk-margin-left uk-text-muted" if="{ edit_entry }" onclick="{ showDialog }" title="{ App.i18n.get('Edit entry') }" data-uk-tooltip><i class="uk-icon-pencil"></i></a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="uk-position-top-right uk-margin-top uk-margin-right">

        <span class="uk-text-small uk-text-muted" if="{ error_message }">{ error_message }</span>

        <a href="{ App.route('/tables/entry/' + source_table) }" target="_blank"  class="uk-margin-small-right uk-text-muted" if="{ new_entry && (relation_type != 'many-to-one' || (relation_type == 'many-to-one' && tables_entry_id)) && parent_id == tables_entry_id }" onclick="{ showDialog }" title="{ App.i18n.get('New entry') }" data-uk-tooltip><i class="uk-icon-plus-circle uk-icon-small"></i></a>

        <a class="uk-margin-small-right uk-text-muted" if="{ reload_entries }" onclick="{ loadOptions }" title="{ App.i18n.get('Reload Options') }" data-uk-tooltip><i class="uk-icon-refresh"></i></a>

        <a href="{ App.route('/tables/entries/' + source_table) }" target="_blank" class="uk-margin-small-right uk-text-muted" if="{ open_entries }" title="{ App.i18n.get('Open table in new tab') }" data-uk-tooltip><i class="uk-icon-link"></i></a>

    </div>


    <div class="uk-modal">

        <div class="uk-modal-dialog uk-modal-dialog-large" if="{ loading }">
            <div class="uk-text-center">
                <i class="uk-icon-spinner uk-icon-spin"></i>
            </div>
        </div>

        <div class="uk-modal-dialog uk-modal-dialog-large" if="{ !loading && !related_allowed }">
            <p>{ App.i18n.get('Sorry, but you are not authorized.') }</p>
            <a href="" class="uk-modal-close uk-button uk-button-link">{ App.i18n.get('Close') }</a>
        </div>

        <div class="uk-modal-dialog uk-modal-dialog-large" if="{ !loading && related_allowed }">
            <a href="" class="uk-modal-close uk-close uk-icon-hover"></a>

            <h3 class="uk-flex uk-flex-middle uk-text-bold">
                <img class="uk-margin-small-right" src="{ App.base(related_table.icon ? '/assets/app/media/icons/'+related_table.icon : '/addons/tables/icon.svg') }" width="25" alt="icon">
                { related_id ? App.i18n.get('Edit Entry') : App.i18n.get('Add Entry') } - { related_table.label || related_table.name }
            </h3>
        
            <div class="uk-grid uk-grid-match uk-grid-gutter">

                <div class="uk-width-medium-{field.width}" each="{field,idx in related_table.fields}" no-reorder>

                    <cp-fieldcontainer if="{ field.name != related_table.primary_key }" class="uk-position-relative { field.required && 'tables-required' }">

                        <label>
                            <span class="uk-text-bold"><i class="uk-icon-pencil-square uk-margin-small-right"></i>{ field.label || field.name }</span>

                            <span class="uk-text-bold" if="{ field.required }" title="{ App.i18n.get('Required') }" data-uk-tooltip>*</span>
                        </label>

                        <div class="uk-margin uk-text-small uk-text-muted">
                            { field.info || ' ' }
                        </div>

                        <div class="uk-margin">
                            <cp-field type="{field.type || 'text'}" bind="related_value.{field.name}" opts="{ field.options || {} }"></cp-field>
                        </div>

                    </cp-fieldcontainer>

                </div>

            </div>

            <div class="uk-modal-footer">
                <div class="uk-grid uk-grid-small uk-flex">
                    <div class="uk-flex-item-1">
                        <a href="#" class="uk-button uk-button-large uk-button-primary" onclick="{ saveRelatedEntry }" if="{ !related_locked }">{ App.i18n.get('Save') }</a>
                        <a href="#" class="uk-modal-close uk-button  { !related_locked ? 'uk-button-link' : 'uk-button-large' }">{ App.i18n.get('Cancel') }</a>
                    </div>

                    <div class="uk-grid" if="{ related_value[related_table.primary_key] }">

                        <table-lockstatus meta="{related_meta}" table="{related_table}" id="{ related_id }" locked="{ related_locked }" bind="related_locked" class="uk-margin-right"></table-lockstatus>

                        <a href="#" class="uk-button uk-button-large uk-text-muted" title="{ App.i18n.get('Reload related entry and lock status') }" data-uk-tooltip onclick="{ getRelatedEntry }"><i class="uk-icon-refresh uk-margin-small-right"></i>{ App.i18n.get('Reload') }</a>
                    </div>
                </div>
            </div>

        </div>

    </div>


    <script>

        var $this = this;

        this.modal;

        this.selected = [];
        this.groups   = {};
        this.error_message = null;
        this.columns = 1;
        this.options_length = 0;
        this.field_type = 'select';
        this.relation_type = null;
        
        this.edit_entry   = false;
        this.new_entry    = true;
        this.open_entries = true;

        this.source_table = '';

        this.related_table = {};
        this.related_value = {};
        this.related_locked = false;
        this.related_meta = {};
        this.related_id = null;
        this.related_allowed = false; // helper to detect if related table_create is allowed
        
        this.request = '';
        this.req_options = {};

        this.parent__id = this.parent.parent._id || this.parent.parent.related_table.primary_key || null;
        this.parent_id  = this.parent__id ? (this.parent.parent.entry || this.parent.parent.related_value)[this.parent__id] : null;

        riot.util.bind(this); // This line is important to enable binds in modal!

        this.on('mount', function() {

            var $this = this;
            
            this.field_type   = opts.display && opts.display.type
                                ? opts.display.type : 'select';

            this.relation_type = opts.type;

            this.source_table = opts.source.table;

            this.new_entry      = typeof opts.new_entry      != 'undefined' ? opts.new_entry      : true;
            this.open_entries   = typeof opts.open_entries   != 'undefined' ? opts.open_entries   : true;
            this.reload_entries = typeof opts.reload_entries != 'undefined' ? opts.reload_entries : true;
            this.edit_entry     = typeof opts.edit_entry     != 'undefined' ? opts.edit_entry
                                  : (opts.display && opts.display.type
                                      && opts.display.type == 'edit-content'
                                      ? true : false);

            this.show_modal     = typeof opts.show_modal     != 'undefined' ? opts.show_modal
                                  : (this.edit_entry || this.new_entry);
            
            
            if (this.show_modal) {

                // allow stackable modals with {modal:false}
                this.modal = UIkit.modal(App.$('.uk-modal', this.root), {modal:false});

                this.modal.on({
                    'show.uk.modal': function() {

                        if ($this.parent.parent.modal) {
                            App.$($this.parent.parent.modal.element).addClass('parent-modal');
                        }

                        // close (all stacked) modal(s) on esc key
                        // default doesn't work with stackable modals
                        $this.modal.UIkit.$html.on('keydown.modal.uikit', function (e) {
                            if (e.keyCode === 27 && $this.modal.options.keyboard) { // ESC
                                e.preventDefault();
                                // App.$('.uk-modal').removeClass('parent-modal');
                                $this.modal.hide();
                            }
                        });
                    },
                    'hide.uk.modal': function() {
                        if ($this.parent.parent.modal) {
                            App.$($this.parent.parent.modal.element).removeClass('parent-modal');
                        }
                    }
                });
            }

            // build the request
            this.request = '/' + opts.source.module + '/find';

            // get singular from module name to work with collections, too
            var table = opts.source.module.slice(0, -1);

            var fields = {};
            if (opts.source.identifier) {
                fields[opts.source.identifier] = true;
            }
            // if (opts.source.display_field)
                // fields[opts.source.display_field] = true;
            if (opts.split && opts.split.identifier) {
                fields[opts.split.identifier] = true;
            }

            if (opts.display && opts.display.info) {
                fields[opts.display.info] = true;
            }

            // add fields to field list, if label uses templating style
            if (opts.display && opts.display.label) {

                if (opts.display.label.indexOf('{') == -1) {
                    fields[opts.display.label] = true;
                }
                else {
                    var regex = /{([^}]*)}/g;
                    while (i = regex.exec(opts.display.label)) {
                        fields[i[1]] = true;
                    }
                }
            }

            var sort = {}
            if (opts.split && opts.split.identifier) {
                sort[opts.split.identifier] = 1;      // sort by keyword category
            }
            if (opts.sort) {
                sort[opts.sort] = 1;                  // sort by user defined field
            }
            if (opts.source.display_field && opts.source.display_field.indexOf('{') == -1) {
                sort[opts.source.display_field] = 1;  // and then sort by keyword
            }

            // var filter = {};
            // if (opts.filter) {
                // filter = opts.filter;
            // }

            this.req_options = {
                [table] : opts.source[table],
                options : {
                    fields   : fields,
                    sort     : sort,
                    // filter   : filter,
                    populate : 1,   // resolve 1:m related content
                }
            };

            this.loadOptions();

        });

        this.on('update', function() {

            // check for parent/entry id to enable disabled m:1 field
            // after creating new entry
            this.parent__id = this.parent.parent._id || this.parent.parent.related_table.primary_key || null;
            this.parent_id  = this.parent__id ? (this.parent.parent.entry || this.parent.parent.related_value)[this.parent__id] : null;

        });

        this.$updateValue = function(value) {

            if (value == null) {
                value = [];
            }

            else if (!Array.isArray(value)) {
                value = [value];
            }

            if (JSON.stringify(this.selected) != JSON.stringify(value)) {
                this.selected = value;
            }

        }.bind(this);

        toggle(e) {

            var option = e.item.option.value || e.item.option.value_orig,
                index  = this.id(option, this.selected);

            if (opts.multiple) {
                if (index == -1) {
                    this.selected.push(option);
                } else {
                    this.selected.splice(index, 1);
                }
            } else {
                this.selected = index == -1 ? [option] : [];
            }

            this.$setValue(this.selected);

        }

        this.id = function(needle, haystack) {
            if (typeof needle  === 'string') {
                return haystack.indexOf(needle);
            }
            for (k in haystack) {
                if (JSON.stringify(needle) == JSON.stringify(haystack[k])) {
                    return parseInt(k);
                }
            }
            return -1;
        }

        function displayError(data) {
            $this.error_message = App.i18n.get('No option available');
        }

        showDialog(e) {

            if (!this.show_modal) return;

            if (e) e.preventDefault();

            this.related_id = e.item.option && e.item.option.value
                                  ? e.item.option.value : null;

            this.loading = true;
            this.related_allowed = false;

            this.getRelatedEntry();

            this.modal.show();

        }

        getRelatedEntry(e) {

            if (e) e.preventDefault();

            App.request('/' + opts.source.module + '/edit_entry/' + opts.source.table, {_id:$this.related_id}).then(function(data){

                $this.loading = false;
                $this.related_allowed = true;

                var table = data.table;

                $this.related_value  = {};

                $this.related_table  = table;
                $this.related_locked = data.locked;
                $this.related_meta   = data.meta;

                for (var key in table.fields) {

                    var v = table.fields[key];

                    $this.related_value[v.name] = data.values[v.name] || null;

                    // pre-select parent id
                    if (v.type == 'relation'
                        && $this.parent_id
                        && (v.options.type == 'one-to-many' || v.options.type == 'many-to-many')
                        && v.options.source
                        && $this.parent.parent
                        && $this.parent.parent.table
                        && $this.parent.parent.entry
                        && v.options.source.table == $this.parent.parent.table.name) {

                        if (!v.options.multiple) {
                            $this.related_value[v.name] = $this.parent.parent.entry[$this.parent__id];
                        } else {
                            if (!Array.isArray($this.related_value[v.name])) {
                                $this.related_value[v.name] = [];
                            }
                            $this.related_value[v.name].push($this.parent.parent.entry[$this.parent__id]);
                        }

                        // force relation field in modal to current parent_id
                        // --> could cause overwrite with missing older content
                        // v.options.display.type = 'display-content';

                        // disable icon to edit (parent) entry
                        v.options.edit_entry = false;
                        v.options.reload_entries = false;
                    }
                }

                $this.update();

            }).catch(function(e){
console.log(e);
                $this.loading = false;
                $this.related_allowed = false;
                $this.update();
            });

        }

        saveRelatedEntry(e) {
            
            var $this = this;

            if (e) e.preventDefault();

            var required = [];

            this.related_table.fields.forEach(function(field){

                if (field.required && !$this.related_value[field.name] && field.name != $this.related_table.primary_key) {
                    
                    if (!($this.related_value[field.name]===false || $this.related_value[field.name]===0)) {
                        required.push(field.label || field.name);
                    }
                }
            });

            if (required.length) {
                App.ui.notify([
                    App.i18n.get('Fill in these required fields before saving:'),
                    '<div class="uk-margin-small-top"><ul><li>'+required.join('</li><li>')+'</li></ul></div>'
                ].join(''), 'danger');
                return;
            }

            App.request('/' + opts.source.module + '/save_entry/' + opts.source.table, {entry:$this.related_value}).then(function(entry){

                if (!entry) {
                    App.ui.notify("Saving failed.", "danger");
                    return;
                }

                if (entry && entry.error) {
                    App.ui.notify(entry.error, "danger");
                    return;
                }

                App.ui.notify("Saving to related table successful", "success");

                // auto select new created entry
                var is_new_entry = false;
                if ($this.selected.indexOf(entry[opts.source.identifier]) == -1) {

                    is_new_entry = true;

                    $this.selected.push(entry[opts.source.identifier]);
                    $this.$setValue($this.selected);
                }

                // add new entry to options
                if (opts.only_related && is_new_entry && opts.type == 'many-to-many') {
                    $this.loadOptions(entry);
                }

                else {
                    $this.loadOptions();
                }

                setTimeout(function(){
                    $this.modal.hide();
                }, 50);

            });

        }

        loadOptions(new_item) {

            $this.req_options.options.filter = $this.req_options.options.filter || {};

            // query only items with parent id
            if (opts.display && opts.display.type == 'display-content') {

                if (opts.type == 'many-to-many') {
                    $this.req_options.options.filter[this.parent__id] = tables_entry_id;
                }

                if (opts.type == 'one-to-many') {
                    $this.req_options.options.filter[this.parent__id] =
                        Array.isArray(this.selected) && this.selected.length
                            ? this.selected[0] : this.selected;
                }

            }

            if (opts.type == 'many-to-one' && opts.only_related) {

                $this.req_options.options.filter[opts.source.related_identifier] = this.parent_id || -1;

            }

            App.request($this.request, $this.req_options).then(function(data){

                // add new item to data, because the request is filtered and
                // the relation doesn't exist, yet
                if (opts.only_related
                    && typeof new_item === 'object'
                    && new_item.type !== 'click' // prevent adding item when clicking reload
                    ) {
                    if (data !== null && data.entries)
                        data.entries.push(new_item);
                }

                if (data === null) {
                    displayError(data);
                    data = [];
                }

                // grab only the entries and ignore count+page, that `/find` returned
                data = data.entries ? data.entries : [];

                if (Array.isArray(data)) {

                    var category = 'main';
                    var categories = [];

                    if (!opts.split) {
                        $this.groups = {main:[]};
                    }

                    for (var k in data) {

                        if (opts.split && opts.split.identifier) {

                            if (data[k].hasOwnProperty(opts.split.identifier)) {
                                category = data[k][opts.split.identifier];
                            }

                            if (categories.indexOf(category) === -1) {
                                categories.push(category);
                                $this.groups[category] = [];
                            }

                        }

                        var value = data[k].hasOwnProperty(opts.value)
                                      ? data[k][opts.value]
                                      : '';

                        var label = '';
                        if (opts.display && opts.display.label && (opts.display.label.indexOf('{') > -1)) {
                            var str = opts.display.label;
                            for (var v in data[k]) {
                                str = str.replace('{'+v+'}', data[k][v]);
                            }
                            label = str;
                        }
                        else {
                            label = opts.display && opts.display.label && data[k].hasOwnProperty(opts.display.label)
                                      ? data[k][opts.display.label].toString().trim()
                                      : value.toString().trim();
                        }

                        var info = opts.display && opts.display.info && data[k].hasOwnProperty(opts.display.info) && data[k][opts.display.info]
                                      ? data[k][opts.display.info].toString().trim()
                                      : false;

                        $this.groups[category].push({
                            value : value,
                            label : label,
                            info  : info
                        });

                        $this.options_length++; // counting options.length doesn't work anymore with grouped options

                        if (opts.split && opts.split.columns) {
                            $this.columns = opts.split.columns;
                        } else {
                            $this.columns = Object.keys($this.groups).length == 1 ? 1 : (Object.keys($this.groups).length <= 4 ? Object.keys($this.groups).length : 4);
                        }

                    }

                } else {
                    displayError(data);
                }

                $this.update();
            });

        }

        truncate(str = '', length = 30) {
            return str.length > 30 ? str.substr(0,30) + '...' : str;
        }

    </script>

</field-relation>
