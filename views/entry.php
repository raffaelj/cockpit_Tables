
@if(isset($table['color']) && $table['color'])
<style>
    .app-header { border-top: 8px {{ $table['color'] }} solid; }
</style>
@endif

<script>
    window.__tableEntry = {{ json_encode($entry) }};
</script>

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

    <div class="uk-width-xlarge-5-6">

        <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

            <ul class="uk-tab uk-margin-large-bottom uk-flex uk-flex-center uk-noselect" show="{ App.Utils.count(groups) > 1 }">
                <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                <li class="{ group==parent.group && 'uk-active'}" each="{items,group in groups}" show="{ items.length }"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
            </ul>

            <div class="uk-grid uk-grid-match uk-grid-gutter" if="{ !preview }">

                <div class="uk-width-medium-{field.width}" each="{field,idx in fields}" show="{checkVisibilityRule(field) && (!group || (group == field.group)) }" if="{ hasFieldAccess(field.name) }" no-reorder>

                    <cp-fieldcontainer class="{ field.type == 'relation' && 'uk-position-relative' }">

                        <label>

                            <span class="uk-text-bold"><i class="uk-icon-pencil-square uk-margin-small-right"></i>{ field.label || field.name }</span>

                            <span class="uk-text-bold" if="{ field.required }" title="@lang('Required')" data-uk-tooltip>*</span>

                        </label>

                        <div class="uk-margin uk-text-small uk-text-muted">
                            { field.info || ' ' }
                        </div>

                        <div class="uk-margin">
                            <cp-field type="{field.type || 'text'}" bind="entry.{ field.localize && parent.lang ? (field.name+'_'+parent.lang):field.name }" opts="{ field.options || {} }"></cp-field>
                        </div>

                    </cp-fieldcontainer>

                </div>

            </div>

            <cp-actionbar>
                <div class="uk-container uk-container-center">
                    
                    <div class="uk-flex">

                        <div class="uk-flex-item-1">
                            @if($app->module('tables')->hasaccess($table['name'], 'entries_edit'))
                            <button class="uk-button uk-button-large uk-button-primary" if="{ !locked }">@lang('Save')</button>
                            @endif
                            <a class="uk-button { !locked ? 'uk-button-link' : 'uk-button-large' }" href="@route('/tables/entries/'.$table['name'])">
                                <span show="{ !entry[_id] }">@lang('Cancel')</span>
                                <span show="{ entry[_id] }">@lang('Close')</span>
                            </a>
                        </div>
                        
                        <table-lockstatus meta="{meta}" table="{table}" id="{ entry[_id] ? entry[_id] : null }" locked="{ locked }" bind="locked" if="{ canLock }"></table-lockstatus>

                        <div class="uk-margin-left">
                            <a class="uk-button uk-button-large uk-text-muted" title="@lang('Reload page and lock status')" data-uk-tooltip onclick="{ pageReload }"><i class="uk-icon-refresh uk-margin-small-right"></i>Reload</a>
                        </div>

                    </div>
                </div>
            </cp-actionbar>

        </form>

    </div>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.table        = {{ json_encode($table) }};
        this._id          = this.table.primary_key;
        this.fields       = this.table.fields;
        this.fieldsidx    = {};
        this.excludeFields = {{ json_encode($excludeFields) }};

        this.entry        = window.__tableEntry;

        this.languages    = App.$data.languages;
        this.groups       = {Main:[]};
        this.group        = '';

        this.locked       = {{ json_encode($locked) }};
        this.canLock      = {{ json_encode($canLock) }};
        this.meta         = {{ json_encode($meta) }};

        this.inactive     = false;
        
        var timeout;

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

            if ($this.excludeFields.indexOf(field.name) > -1) {
                return;
            }

            if (field.group && !$this.groups[field.group]) {
                $this.groups[field.group] = [];
            } else if (!field.group) {
                field.group = 'Main';
            }

            $this.groups[field.group || 'Main'].push(field);
        });

        this.on('mount', function(){

            // bind global command + save
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

            // lock resource
            if (this.canLock) {

                var idle = setInterval(function() {

                    if (!$this.entry[$this._id] || $this.inactive) return;

                    if (!$this.locked) {

                        App.request('/tables/lockResourceId/tables.'+$this.table._id+'.'+$this.entry[$this._id], {}).then(function(data) {

                            if (data && data.error) {
                                $this.isResourceLocked();
                            }

                        });
                    } else {
                        $this.isResourceLocked();
                    }

                }, (!this.locked ? 120000 : 30000));

                // unlock resource
                window.addEventListener("beforeunload", function (event) {

                    clearInterval(idle);

                    if (!$this.entry[$this._id]) return;

                    if (navigator.sendBeacon) {
                        navigator.sendBeacon(App.route('/cockpit/utils/unlockResourceId/tables.'+$this.table._id+'.'+$this.entry[$this._id]));
                    } else {
                        App.request('/cockpit/utils/unlockResourceId/tables.'+$this.table._id+'.'+$this.entry[$this._id], {});
                    }
                });
                
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        $this.inactive = true;
                    }
                    else {
                        resetTimer();
                    }
                }, false);

                // set inactive status to prevent resource locking when browser tab
                // is open without any activity
                window.addEventListener('click', resetTimer, false);
                window.addEventListener('mousemove', resetTimer, false);
                window.addEventListener('keydown', resetTimer, false);
                window.addEventListener('touchmove', resetTimer, false);
                // window.addEventListener('mouseenter', resetTimer, false);
                // window.addEventListener('scroll', resetTimer, false);
                // window.addEventListener('mousewheel', resetTimer, false);
                // window.addEventListener('touchstart', resetTimer, false);

            }

        });

        function startTimer() {
            timeout = setTimeout(function() {
                $this.inactive = true;
            }, 30000);
        }

        function resetTimer() {
            $this.inactive = false;
            clearTimeout(timeout);
            startTimer();
        }

        toggleGroup(e) {
            this.group = e.item && e.item.group || false;
        }

        submit(e) {

            if (e) {
                e.preventDefault();
            }

            var required = [];

            this.fields.forEach(function(field){

                if (field.required && !$this.entry[field.name] && field.name != $this._id) {
                    
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

                if (!entry) {
                    App.ui.notify("Saving failed.", "danger");
                    return;
                }

                if (entry && entry.error) {
                    App.ui.notify(entry.error, "danger");
                    return;
                }

                if (!$this.entry[$this._id] && entry[$this._id]){

                    // add current id to browser history
                    window.history.pushState(null,null,App.route('/tables/entry/' + $this.table.name + '/' + entry[$this._id]));

                    // lock resource
                    App.request('/tables/lockResourceId/tables.'+$this.table._id+'.'+entry[$this._id], {});
                }

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

            if (this.excludeFields.indexOf(field) > -1) {
                return false;
            }

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

        isResourceLocked() {

            if (!$this.entry[$this._id]) return;

            if (!this.canLock) return;

            App.request('/tables/isResourceLocked/tables.'+$this.table._id+'.'+$this.entry[$this._id], {}).then(function(data) {

                $this.locked = data.user && data.user._id == App.$data.user._id ? false : data.locked;

                $this.meta = data;

                $this.update();

            });
        }

        pageReload() {

            App.request('/tables/entry/'+$this.table.name+'/'+$this.entry[$this._id], {}).then(function(data) {

                $this.entry = data.entry;
                $this.table = data.table;
                $this.locked = data.locked;
                $this.canLock = data.canLock;
                $this.meta = data.meta;
                $this.excludeFields = data.excludeFields;

                $this.update();

            });

        }

    </script>

</div>
