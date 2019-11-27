
<style>
p img {
    display: block;
    margin: 0 auto;
    padding: 2px;
    border: 1px solid #ccc;
    border-radius: 2px;
}
</style>

<div class="uk-grid">
    <div class="uk-width-medium-1-4 uk-push-3-4">

        <ul>
            <li><a href="@base('/tables/help')">Tables README</a></li>
            @foreach($toc as $filename)
            <li><a href="@base('/tables/help/docs/'.$filename)">{{ substr($filename, 0, -3) }}</a></li>
            @endforeach
        </ul>
        
    </div>

    <div class="uk-width-medium-3-4 uk-pull-1-4">
    {{ $content }}
    </div>
</div>
