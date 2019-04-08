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
        dbname: database_name
        user: root
        password: superSecret
</pre>

<h2>Other options</h2>

<p>Or if you want to use a different config file, add the full path of your database config file. Store them as .php, .ini or .yaml.</p>

<code>config/config.yaml</code>:

<pre>
tables:
    db: /full/path/to/database_config_file.ini # or .php or .yaml
</pre>

<div class="uk-grid">
    <div class="uk-width-medium-1-3">

        <code>database_config_file.php</code>:

<pre>
&lt;?php return [
    'host'     => 'localhost',
    'dbname'   => 'database_name',
    'user'     => 'root',
    'password' => 'superSecret'
];
</pre>

    </div>
    <div class="uk-width-medium-1-3">
        <code>database_config_file.ini</code>:

<pre>
host=localhost
dbname=database_name
user=root
password=superSecret
</pre>

    </div>
    <div class="uk-width-medium-1-3">
        <code>database_config_file.yaml</code>:

<pre>
host: localhost
dbname: database_name
user: root
password: superSecret
</pre>

    </div>
</div>
