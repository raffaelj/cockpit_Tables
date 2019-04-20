        <div class="uk-margin uk-flex uk-flex-middle" if="{ !loading && pages > 1 }">

            <ul class="uk-breadcrumb uk-margin-remove">
                <li class="uk-active"><span>{ page }</span></li>
                <li data-uk-dropdown="mode:'click'">

                    <a><i class="uk-icon-bars"></i> { pages }</a>

                    <div class="uk-dropdown">

                        <strong class="uk-text-small">@lang('Pages')</strong>

                        <div class="uk-margin-small-top { pages > 5 ? 'uk-scrollable-box':'' }">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-text-small" each="{k,v in new Array(pages)}"><a class="uk-dropdown-close" onclick="{ parent.loadpage.bind(parent, v+1) }">@lang('Page') {v + 1}</a></li>
                            </ul>
                        </div>
                    </div>

                </li>
            </ul>

            <div class="uk-button-group uk-margin-small-left">
                <a class="uk-button uk-button-small" onclick="{ loadpage.bind(this, page-1) }" if="{page-1 > 0}">@lang('Previous')</a>
                <a class="uk-button uk-button-small" onclick="{ loadpage.bind(this, page+1) }" if="{page+1 <= pages}">@lang('Next')</a>
            </div>

            <div class="uk-margin-small-right" data-uk-dropdown="mode:'click'">
                <a class="uk-button uk-button-link uk-button-small uk-text-muted">{limit}</a>
                <div class="uk-dropdown">
                    <ul class="uk-nav uk-nav-dropdown">
                        <li class="uk-nav-header">@lang('Show')</li>
                        <li><a onclick="{updateLimit.bind(this, 10)}">10</a></li>
                        <li><a onclick="{updateLimit.bind(this, 20)}">20</a></li>
                        <li><a onclick="{updateLimit.bind(this, 40)}">40</a></li>
                        <li><a onclick="{updateLimit.bind(this, 80)}">80</a></li>
                        <li><a onclick="{updateLimit.bind(this, 100)}">100</a></li>
                        <li class="uk-nav-divider"></li>
                        <li><a onclick="{updateLimit.bind(this, null)}">@lang('All')</a></li>
                    </ul>
                </div>
            </div>

        </div>