App.Utils.renderer['relation'] = function(v, meta) {

    if (v === null) return '';

    // comma separated string of values
    if (typeof v === 'string' && meta.options.multiple) {
        v = v.split(meta.options.separator ? meta.options.separator : ',');
        return App.Utils.renderer.tags(v);
    }

    if (typeof v[0] === 'string') {
        return App.Utils.renderer.tags(v);
    }

    if (typeof v[0]  === 'object') {

        if (v.length > 5) {
            // don't render too much output
            return App.Utils.renderer.repeater(v);
        }

        var out = '';
        for (k in v) {
            var tags = [];
            for (val in v[k]) {
                if (typeof v[k][val] !== 'string') {
                    // don't render nested output
                    return App.Utils.renderer.repeater(v);
                }
                tags.push(v[k][val]);
            }
            out += App.Utils.renderer.tags(tags) + (k < v.length ? ' ' : '');
        }
        return out;
    }
};

<field-relation>

    <div class="uk-grid uk-grid-gutter uk-position-relative">

        <div class="uk-width-medium-1-1 uk-grid uk-grid-gutter" if="{ field_type == 'select' }">

            <div class="uk-width-medium-1-{ columns }" each="{options,idx in groups}">

                <label class="uk-margin" if="{ idx !== 'main' }"><span class="uk-text-bold">{idx}</span></label>

                <div class="uk-grid uk-grid-small uk-flex-middle uk-margin" data-uk-grid-margin="observe:true" if="{ options.length > 6 && !opts.split }">
                  <span if="{ selected.length }">{ App.i18n.get('Selected') }:</span>
                    <div class="uk-text-primary" each="{ option in options }" show="{ id(option.value, parent.selected) !==-1 }">
                        <span class="field-tag">
                        <i class="uk-icon-tag"></i> { option.label }
                        <i class="uk-icon-info uk-margin-small-right" title="{ option.info }" data-uk-tooltip if="{ option.info }"></i>
                        <a onclick="{ parent.toggle }"><i class="uk-icon-close"></i></a>
                        </span>
                    </div>
                </div>

                <div class="{ options.length > 10 ? 'uk-scrollable-box':'' }">
                    <div class="uk-margin-small-top" each="{option in options}">
                        <a class="{ id(option.value, parent.selected) !==-1 || id(option.value_orig, parent.selected) !==-1 ? 'uk-text-primary':'uk-text-muted' }" onclick="{ parent.toggle }">
                            <i class="uk-icon-{ id(option.value, parent.selected) !==-1 || id(option.value_orig, parent.selected) !==-1 ? 'circle':'circle-o' } uk-margin-small-right"></i>
                            <span>{ option.label }</span>
                            <i class="uk-icon-info uk-margin-small-right" title="{ option.info }" data-uk-tooltip if="{ option.info }"></i>
                            <i class="uk-icon-warning uk-margin-small-right" title="{ option.warning }" data-uk-tooltip if="{ option.warning }"></i>
                        </a>
                        <a class="uk-margin-small-left uk-text-muted" if="{ edit_entry }" onclick="{ showDialog }" title="{ App.i18n.get('Edit entry') }" data-uk-tooltip><i class="uk-icon-pencil"></i></a>
                    </div>
                </div>
                <span class="uk-text-small uk-text-muted" if="{ options.length > 10 && !opts.split }">{selected.length} { App.i18n.get('selected') }</span>
            </div>

            <span class="uk-text-small uk-text-muted" if="{ options_length > 10  && opts.split }">{selected.length} { App.i18n.get('selected') }</span>

        </div>

        <div class="uk-width-medium-1-1" if="{ field_type == 'edit-content' }">

            <div class="uk-width-medium-1-{ columns }" each="{options,idx in groups}">

                <label class="uk-margin" if="{ idx !== 'main' }"><span class="uk-text-bold">{idx}</span></label>

                <div class="{ options.length > 10 ? 'uk-scrollable-box':'' }">
                    <div class="uk-margin-small-top" each="{option in options}">

                        <a class="{ id(option.value, parent.selected) !==-1 || id(option.value_orig, parent.selected) !==-1 ? 'uk-text-primary':'uk-text-muted' }" onclick="{ parent.toggle }">

                            <i class="uk-icon-{ id(option.value, parent.selected) !==-1 || id(option.value_orig, parent.selected) !==-1 ? 'circle':'circle-o' } uk-margin-small-right"></i>
                            <span class="uk-text-muted">{ option.label }</span>
                            <i class="uk-icon-info uk-margin-small-left uk-text-muted" title="{ option.info }" data-uk-tooltip if="{ option.info }"></i>
                            <i class="uk-icon-warning uk-margin-small-left" title="{ option.warning }" data-uk-tooltip if="{ option.warning }"></i>
                            <a class="uk-margin-left uk-text-muted" if="{ edit_entry }" onclick="{ showDialog }" title="{ App.i18n.get('Edit entry') }" data-uk-tooltip><i class="uk-icon-pencil"></i></a>

                        </a>

                    </div>

                </div>

            </div>

        </div>

        <div class="uk-position-top-right uk-margin-remove">

            <span class="uk-text-small uk-text-muted" if="{ error_message }">{ error_message }</span>

            <a class="uk-margin-small-right uk-text-muted" if="{ new_entry }" onclick="{ showDialog }" title="{ App.i18n.get('New entry') }" data-uk-tooltip><i class="uk-icon-plus-circle"></i></a>

            <a class="uk-margin-small-right uk-text-muted" onclick="{ loadOptions }" title="{ App.i18n.get('Reload Options') }" data-uk-tooltip><i class="uk-icon-refresh"></i></a>

            <a class="uk-margin-small-right uk-text-muted" if="{ open_entries }" href="{ App.base('/tables/entries/' + source_table) }" target="_blank" title="{ App.i18n.get('Open table in new tab') }" data-uk-tooltip><i class="uk-icon-link"></i></a>

        </div>

    </div>
    
    

    <div class="uk-modal">

        <div class="uk-modal-dialog uk-modal-dialog-large" if="{!related_allowed}">
            <p>{App.i18n.get('Sorry, but you are not authorized.')}</p>
            <a href="" class="uk-modal-close uk-button uk-button-link">{ App.i18n.get('Close') }</a>
        </div>

        <div class="uk-modal-dialog uk-modal-dialog-large" if="{related_allowed}">
            <a href="" class="uk-modal-close uk-close"></a>

            <h3 class="uk-flex uk-flex-middle uk-text-bold">
                <img class="uk-margin-small-right" src="{App.route(related_table.icon ? 'assets:app/media/icons/'+related_table.icon : '/addons/tables/icon.svg')}" width="25" alt="icon">
                { App.i18n.get('Add Entry') }
            </h3>
        
            <div class="uk-grid uk-grid-match uk-grid-gutter">

                <div class="uk-width-medium-{field.width}" each="{field,idx in related_table.fields}" no-reorder>

                    <cp-fieldcontainer if="{ field.name != related_table.primary_key }">

                        <label>
                            <span class="uk-text-bold"><i class="uk-icon-pencil-square uk-margin-small-right"></i>{ field.label || field.name }</span>
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

            <div class="uk-margin-top uk-grid uk-grid-small uk-flex">
                <div>
                    <a class="uk-button uk-button-large uk-button-primary" onclick="{ saveRelatedEntry }">{ App.i18n.get('Save') }</a>
                    <a href="" class="uk-modal-close uk-button uk-button-link">{ App.i18n.get('Cancel') }</a>
                </div>

                <div class="">
                    <a class="uk-button uk-button-large uk-text-muted" title="{ App.i18n.get('Reload related entry and lock status') }" data-uk-tooltip onclick="{ getRelatedEntry }"><i class="uk-icon-refresh uk-margin-small-right"></i>Reload</a>
                </div>

                <table-lockstatus meta="{related_meta}" table="{related_table}" id="{ related_id }" locked="{ related_locked }" bind="related_locked"></table-lockstatus>
            </div>

        </div>

    </div>


    <script>

        var $this = this,
            modal;

        this.selected = [];
        this.groups   = {};
        this.error_message = null;
        this.columns = 1;
        this.options_length = 0;
        this.field_type = 'select';
        
        this.edit_entry = false;
        this.new_entry = true;
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

        riot.util.bind(this); // This line is important to enable binds in modal!

        this.on('mount', function() {
            
            this.field_type = opts.display && opts.display.type ? opts.display.type : 'select';
            this.source_table = opts.source.table;

            this.edit_entry = opts.edit_entry ? opts.edit_entry : (opts.display && opts.display.type && opts.display.type == 'edit-content' ? true : false);
            this.new_entry = opts.new_entry || true;
            this.open_entries = opts.open_entries || true;

            modal = UIkit.modal(App.$('.uk-modal', this.root), {modal:false});

            // build the request
            this.request = '/' + opts.source.module + '/find';
            
            // get singular from module name to work with collections, too
            var table = opts.source.module.slice(0, -1);
            
            var fields = {};
            if (opts.source.identifier)
                fields[opts.source.identifier] = true;
            // if (opts.source.display_field)
                // fields[opts.source.display_field] = true;
            if (opts.split && opts.split.identifier)
                fields[opts.split.identifier] = true;
            if (opts.display && opts.display.info)
                fields[opts.display.info] = true;
            
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
            if (opts.split && opts.split.identifier)
                sort[opts.split.identifier] = 1;      // sort by keyword category
            if (opts.sort)
                sort[opts.sort] = 1;                  // sort by user defined field
            if (opts.source.display_field)
                sort[opts.source.display_field] = 1;  // and then sort by keyword

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

            this.related_id = e.item.option && e.item.option.value ? e.item.option.value : null;

            this.related_allowed = false;

            this.getRelatedEntry();

            modal.show();

        }

        getRelatedEntry() {

            App.request('/' + opts.source.module + '/edit_entry/' + opts.source.table, {_id:$this.related_id}).then(function(data){

                $this.related_allowed = true;

                var table = data.table;

                $this.related_table = table;
                $this.related_locked = data.locked;
                $this.related_meta = data.meta;

                for (var val in table.fields) {
                    $this.related_value[table.fields[val].name] = data.values[table.fields[val].name] || null;
                }

                $this.update();

            });

        }

        saveRelatedEntry() {

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
                if (opts.select && opts.select == 'related' && is_new_entry) {
                        $this.loadOptions(entry);
                    }

                else {
                    $this.loadOptions();
                }

                setTimeout(function(){
                    modal.hide();
                }, 50);

            });

        }

        loadOptions(new_item) {

            $this.req_options.options.filter = $this.req_options.options.filter || {};
            if (opts.select && opts.select == 'related') {

                if (!this.parent.parent.entry[this.parent.parent._id]) { // new entry
                    $this.req_options.options.filter[opts.target.identifier] = '-1';
                }
                else {
                    $this.req_options.options.filter[opts.target.identifier] = this.parent.parent.entry[this.parent.parent._id];
                }

                $this.req_options.options.fields = {}; // quick fix to make the filter work - to do: fix filterToQuery function

            }
            
            App.request($this.request, $this.req_options).then(function(data){

                // add new item to data, because the request is filtered and
                // the relation doesn't exist, yet
                if (opts.select && opts.select == 'related'
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

    </script>

</field-relation>
