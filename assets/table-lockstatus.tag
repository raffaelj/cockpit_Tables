
<table-lockstatus>
  <div class="uk-grid uk-grid-small uk-visible-toggle" if="{locked}">
    <div class="">
        <span><i class="uk-icon-lock uk-margin-small-right" title="{ App.i18n.get('Locked by') }" data-uk-tooltip></i>{ meta.user.name ? meta.user.name : meta.user.user }</span><br />
        <span class="uk-text-muted uk-hidden-hover">{ meta.user.email }</span>
    </div>
    
    <div class="">

        <span title="{ App.i18n.get('Last lock') }" data-uk-tooltip>
            <i class="uk-icon-clock-o uk-margin-small-right uk-text-muted"></i> { App.Utils.dateformat( new Date( 1000 * meta.time ), 'H:mm') }
        </span><br />
        <a class="uk-margin-small-right uk-text-muted" onclick="{ isResourceLocked }"><i class="uk-icon-refresh" title="{ App.i18n.get('Reload lock status') }" data-uk-tooltip></i></a>
        <span title="{ App.i18n.get('By default an entry is locked for 5 minutes. While editing, the status resets every two minutes.') }" data-uk-tooltip>
            { App.Utils.dateformat( new Date( 1000 * meta.time - Date.now() + 300000 ), 'm:ss') }
        </span>
        <a class="uk-margin-small-right uk-text-muted" onclick="{ kickFromResourceId }"><i class="uk-icon-unlock" title="{ App.i18n.get('Unlock - the other user\'s lock status will be reset.') }" data-uk-tooltip></i></a>

    </div>
  </div>

    <script>

        var $this = this;

        riot.util.bind(this);

        this.locked = opts.locked;
        this.meta = opts.meta;
        this.table = opts.table;
        this._id = opts.id;

        this.on('mount', function(){
            this.update();
        });

        this.on('update', function() {

            this.locked = opts.locked;
            this.meta = opts.meta;
            this.table = opts.table;
            this._id = opts.id;

        });

        isResourceLocked() {

            if (!$this._id) return;

            App.request('/tables/isResourceLocked/tables.'+$this.table._id+'.'+$this._id, {}).then(function(data) {

                $this.locked = data.user && data.user._id == App.$data.user._id ? false : data.locked;

                $this.meta = data;

                $this.update();

            });
        }

        kickFromResourceId() {

            App.request('/tables/kickFromResourceId/tables.'+$this.table._id+'.'+$this._id, {});

            $this.locked = false;

            $this.update();

            $this.$setValue(false);

        }

    </script>

</table-lockstatus>