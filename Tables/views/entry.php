
<script type="riot/tag" src="@base('tables:assets/table-entrypreview.tag')"></script>
<script type="riot/tag" src="@base('tables:assets/field-relation.tag')"></script>

@if(isset($table['color']) && $table['color'])
<style>
    .app-header { border-top: 8px {{ $table['color'] }} solid; }
</style>
@endif

@render('tables:views/partials/breadcrumbs.php', compact('table'))

<div class="uk-margin-top-large" riot-view>

    <div class="uk-alert" if="{ !fields.length }">
        @lang('No fields defined'). <a href="@route('/tables/table')/{ table.name }">@lang('Define table fields').</a>
    </div>

    <h3 class="uk-flex uk-flex-middle uk-text-bold">
        <img class="uk-margin-small-right" src="@url($table['icon'] ? 'assets:app/media/icons/'.$table['icon']:'tables:icon.svg')" width="25" alt="icon">
        { App.i18n.get(entry[_id] ? 'Edit Entry':'Add Entry') }

        <a class="uk-text-large uk-margin-small-left" onclick="{showPreview}" if="{ table.contentpreview && table.contentpreview.enabled }" title="@lang('Preview')"><i class="uk-icon-eye"></i></a>
    </h3>

    <div class="uk-grid">

        <div class="uk-grid-margin uk-width-medium-3-4">

            <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                <ul class="uk-tab uk-margin-large-bottom uk-flex uk-flex-center uk-noselect" show="{ App.Utils.count(groups) > 1 }">
                    <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                    <li class="{ group==parent.group && 'uk-active'}" each="{items,group in groups}" show="{ items.length }"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
                </ul>

                <div class="uk-grid uk-grid-match uk-grid-gutter" if="{ !preview }">

                    <div class="uk-width-medium-{field.width}" each="{field,idx in fields}" show="{checkVisibilityRule(field) && (!group || (group == field.group)) }" if="{ hasFieldAccess(field.name) }" no-reorder>

                        <div class="uk-panel">

                            <label>

                                <span class="uk-text-bold">{ field.label || field.name }</span>

                                <span if="{ field.localize }" data-uk-dropdown="mode:'click'">
                                    <a class="uk-icon-globe" title="@lang('Localized field')" data-uk-tooltip="pos:'right'"></a>
                                    <div class="uk-dropdown uk-dropdown-close">
                                        <ul class="uk-nav uk-nav-dropdown">
                                            <li class="uk-nav-header">@lang('Copy content from:')</li>
                                            <li show="{parent.lang}"><a onclick="{parent.copyLocalizedValue}" lang="" field="{field.name}">@lang('Default')</a></li>
                                            <li show="{parent.lang != language.code}" each="{language,idx in languages}" value="{language.code}"><a onclick="{parent.parent.copyLocalizedValue}" lang="{language.code}" field="{field.name}">{language.label}</a></li>
                                        </ul>
                                    </div>
                                </span>

                            </label>

                            <div class="uk-margin uk-text-small uk-text-muted">
                                { field.info || ' ' }
                            </div>

                            <div class="uk-margin">
                                <cp-field type="{field.type || 'text'}" bind="entry.{ field.localize && parent.lang ? (field.name+'_'+parent.lang):field.name }" opts="{ field.options || {} }"></cp-field>
                            </div>

                        </div>

                    </div>

                </div>

                <cp-actionbar>
                    <div class="uk-container uk-container-center">
                        <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                        <a class="uk-button uk-button-link" href="@route('/tables/entries/'.$table['name'])">
                            <span show="{ !entry[_id] }">@lang('Cancel')</span>
                            <span show="{ entry[_id] }">@lang('Close')</span>
                        </a>
                    </div>
                </cp-actionbar>

            </form>

        </div>

        <div class="uk-grid-margin uk-width-medium-1-4 uk-flex-order-first uk-flex-order-last-medium">

            <div class="uk-margin uk-form" if="{ languages.length }">

                <div class="uk-width-1-1 uk-form-select">

                    <label class="uk-text-small">@lang('Language')</label>
                    <div class="uk-margin-small-top"><span class="uk-badge uk-badge-outline {lang ? 'uk-text-primary' : 'uk-text-muted'}">{ lang ? _.find(languages,{code:lang}).label:App.$data.languageDefaultLabel }</span></div>

                    <select bind="lang" onchange="{persistLanguage}">
                        <option value="">{App.$data.languageDefaultLabel}</option>
                        <option each="{language,idx in languages}" value="{language.code}">{language.label}</option>
                    </select>
                </div>

            </div>

            <div class="uk-margin">
                <label class="uk-text-small">@lang('Last Modified')</label>
                <div class="uk-margin-small-top uk-text-muted" if="{entry._id}">
                    <i class="uk-icon-calendar uk-margin-small-right"></i> {  App.Utils.dateformat( new Date( 1000 * entry._modified )) }
                </div>
                <div class="uk-margin-small-top uk-text-muted" if="{!entry._id}">@lang('Not saved yet')</div>
            </div>

            <div class="uk-margin" if="{entry._id}">
                <label class="uk-text-small">@lang('Revisions')</label>
                <div class="uk-margin-small-top">
                    <span class="uk-position-relative">
                        <cp-revisions-info class="uk-badge uk-text-large" rid="{entry._id}"></cp-revisions-info>
                        <a class="uk-position-cover" href="@route('/tables/revisions/'.$table['name'])/{entry._id}"></a>
                    </span>
                </div>
            </div>

            <div class="uk-margin" if="{entry._id && entry._mby}">
                <label class="uk-text-small">@lang('Last update by')</label>
                <div class="uk-margin-small-top">
                    <!--<cp-account account="{entry._mby}"></cp-account>-->
                </div>
            </div>

            @trigger('tables.entry.aside')

        </div>

    </div>

    <table-entrypreview table="{table}" entry="{entry}" groups="{ groups }" fields="{ fields }" fieldsidx="{ fieldsidx }" languages="{ languages }" settings="{ table.contentpreview }" if="{ preview }"></table-entrypreview>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.table   = {{ json_encode($table) }};
        this._id = this.table.primary_key;
        this.fields       = this.table.fields;
        this.fieldsidx    = {};
        // this.excludeFields = {{ json_encode($excludeFields) }};

        this.entry        = {{ json_encode($entry) }};

        this.languages    = App.$data.languages;
        this.groups       = {Main:[]};
        this.group        = '';

        if (this.languages.length) {
            this.lang = App.session.get('tables.entry.'+this.table._id+'.lang', '');
        }

        // fill with default values
        this.fields.forEach(function(field) {

            $this.fieldsidx[field.name] = field;

            if ($this.entry[field.name] === undefined) {
                $this.entry[field.name] = field.options && field.options.default || null;
            }

            if (field.localize && $this.languages.length) {

                $this.languages.forEach(function(lang) {

                    var key = field.name+'_'+lang.code;

                    if ($this.entry[key] === undefined) {

                        if (field.options && field.options['default_'+lang.code] === null) {
                            return;
                        }

                        $this.entry[key] = field.options && field.options.default || null;
                        $this.entry[key] = field.options && field.options['default_'+lang.code] || $this.entry[key];
                    }
                });
            }

            if (field.type == 'password') {
                $this.entry[field.name] = '';
            }

            // if ($this.excludeFields.indexOf(field.name) > -1) {
                // return;
            // }

            if (field.group && !$this.groups[field.group]) {
                $this.groups[field.group] = [];
            } else if (!field.group) {
                field.group = 'Main';
            }

            $this.groups[field.group || 'Main'].push(field);
        });

        this.on('mount', function(){

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                if (App.$('.uk-modal.uk-open').length) {
                    return;
                }

                $this.submit(e);
                return false;
            });

            // wysiwyg cmd + save hack
            App.$(this.root).on('submit', function(e, component) {
                if (component) $this.submit(e);
            });
        });

        toggleGroup(e) {
            this.group = e.item && e.item.group || false;
        }

        submit(e) {

            if (e) {
                e.preventDefault();
            }

            var required = [];

            this.fields.forEach(function(field){

                if (field.required && !$this.entry[field.name]) {

                    if (!($this.entry[field.name]===false || $this.entry[field.name]===0)) {
                        required.push(field.label || field.name);
                    }
                }
            });

            if (required.length) {
                App.ui.notify([
                    App.i18n.get('Fill in these required fields before saving:'),
                    '<div class="uk-margin-small-top">'+required.join(',')+'</div>'
                ].join(''), 'danger');
                return;
            }

            App.request('/tables/save_entry/'+this.table.name, {entry:this.entry}).then(function(entry) {

                if (entry) {

                    App.ui.notify("Saving successful", "success");

                    _.extend($this.entry, entry);

                    $this.fields.forEach(function(field){

                        if (field.type == 'password') {
                            $this.entry[field.name] = '';
                        }
                    });

                    if ($this.tags['cp-revisions-info']) {
                        $this.tags['cp-revisions-info'].sync();
                    }

                    $this.update();

                } else {
                    App.ui.notify("Saving failed.", "danger");
                }
            }, function(res) {
                App.ui.notify(res && (res.message || res.error) ? (res.message || res.error) : "Saving failed.", "danger");
            });

            return false;
        }

        showPreview() {
            this.preview = true;
        }

        hasFieldAccess(field) {

            var acl = this.fieldsidx[field] && this.fieldsidx[field].acl || [];

            // if (this.excludeFields.indexOf(field) > -1) {
                // return false;
            // }

            if (field == '_modified' ||
                App.$data.user.group == 'admin' ||
                !acl ||
                (Array.isArray(acl) && !acl.length) ||
                acl.indexOf(App.$data.user.group) > -1 ||
                acl.indexOf(App.$data.user._id) > -1
            ) {
                return true;
            }

            return false;
        }

        persistLanguage(e) {
            App.session.set('tables.entry.'+this.table._id+'.lang', e.target.value);
        }

        copyLocalizedValue(e) {

            var field = e.target.getAttribute('field'),
                lang = e.target.getAttribute('lang'),
                val = JSON.stringify(this.entry[field+(lang ? '_':'')+lang]);

            this.entry[field+(this.lang ? '_':'')+this.lang] = JSON.parse(val);
        }

        checkVisibilityRule(field) {

            if (field.options && field.options['@visibility']) {

                try {
                    return (new Function('$', 'v','return ('+field.options['@visibility']+')'))(this.entry, function(key) {
                        var f = this.fieldsidx[key] || {};
                        return this.entry[(f.localize && this.lang ? (f.name+'_'+this.lang):f.name)];
                    }.bind(this));
                } catch(e) {
                    return false;
                }

                return this.data.check;
            }

            return true;
        }

    </script>

</div>
