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
    <div class="uk-grid uk-grid-gutter">

        <div class="uk-width-medium-1-{ Object.keys(groups).length == 1 ? 1 : (Object.keys(groups).length <= 4 ? Object.keys(groups).length : 3) }" each="{options,idx in groups}">

            <label class="uk-margin" if="{ idx !== 'main' }"><span class="uk-text-bold">{idx}</span></label>

            <div class="uk-grid uk-grid-small uk-flex-middle uk-margin" data-uk-grid-margin="observe:true" if="{ options.length > 6 && !opts.split }">
              <span>{ App.i18n.get('Selected') }:</span>
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
                </div>
            </div>
            <span class="uk-text-small uk-text-muted" if="{ options.length > 10 && !opts.split }">{selected.length} { App.i18n.get('selected') }</span>
        </div>

        <span class="uk-text-small uk-text-muted" if="{ error_message }">{ error_message }</span>

    </div>

    <script>

        var $this = this;

        this.selected = [];
        this.groups   = {};
        this.error_message = null;

        this.on('mount', function() {

            // build the request
            var request = '/' + opts.source.module + '/find';
            
            // get singular from module name to work with collections, too
            var table = opts.source.module.slice(0, -1);
            
            var fields = {};
            if (opts.source.identifier)
                fields[opts.source.identifier] = true;
            if (opts.source.display_field)
                fields[opts.source.display_field] = true;
            if (opts.split && opts.split.identifier)
                fields[opts.split.identifier] = true;
            if (opts.info)
                fields[opts.info] = true;
            
            var sort = {}
            if (opts.split && opts.split.identifier)
                sort[opts.split.identifier] = 1;      // sort by keyword category
            if (opts.sort)
                sort[opts.sort] = 1;                  // sort by user defined field
            if (opts.source.display_field)
                sort[opts.source.display_field] = 1;  // and then sort by keyword

            var req_options = {
                [table] : opts.source[table],
                options : {
                    fields   : fields,
                    sort     : sort,
                    populate : 1,   // resolve 1:m related content
                }
            };

            App.request(request, req_options).then(function(data){

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

                        var label = opts.label && data[k].hasOwnProperty(opts.label)
                                      ? data[k][opts.label].toString().trim()
                                      : data[k][opts.value].toString().trim();

                        var info = opts.info && data[k].hasOwnProperty(opts.info)
                                      ? data[k][opts.info].toString().trim()
                                      : false;

                        $this.groups[category].push({
                            value : value,
                            label : label,
                            info  : info
                        });

                    }

                } else {
                    displayError(data);
                }

                $this.update();
            });

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

            console.log('something went wrong...: App.request(\'' + opts.request + (opts.options ? '\', ' + JSON.stringify(opts.options) : '') + ')\r\n', data);
        }

    </script>

</field-relation>
