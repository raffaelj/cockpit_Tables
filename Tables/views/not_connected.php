<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Tables')</span></li>
    </ul>
</div>

<p>
    <span class="uk-badge uk-badge-danger">Database connection failed</span>
</p>
<p>Please add your database credentials to <code>config/config.yaml</code> in a format like this:</p>

<pre>
tables:
  db:
    host: localhost
    database: database_name
    user: root
    password: *****
</pre>
