<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>{{$title}}</title>
			<meta property="og:title" content="{{$title}}" />
			<meta name="twitter:title" content="{{$title}}" />
		@endcomponent
	</head>
	<body onload="MEME.getMEMEs('{{$role}}');MEME.loadTrends();">
		@component('components/header')
		@endcomponent
		<div class="slide">
			<div class="slide-left">
				<div class="slide-left-content">
					@if ( $role === 'dashboard' )
						<div id="dashboard-ordering">
							<p class="dashboard-slide-title dashboard-slide-title-no-margin common-text" scope="ordering">Ordering</p>
							<ul id="dashboard-ordering-list">
								<li class="dashboard-ordering-list-element">
									<a class="dashboard-ordering-list-element-link dashboard-ordering-link common-text" href="javascript:void(0);" onclick="MEME.setOrdering(event);" ord="popular" title="Popular" selected="true">Popular</a>
								</li>
								<li class="dashboard-ordering-list-element">
									<a class="dashboard-ordering-list-element-link dashboard-ordering-link common-text" href="javascript:void(0);" onclick="MEME.setOrdering(event);" ord="new" title="New">New</a>
								</li>
							</ul>
						</div>
					@endif
					<p class="{{ $role === 'dashboard' ? 'dashboard-slide-title common-text' : 'dashboard-slide-title dashboard-slide-title-no-margin common-text' }}" scope="trends">Trends</p>
					<div id="dashboard-trends-loader" class="dashboard-trends-loader">
						<div id="dashboard-trends-loader-spinner" class="common-spinner" title="Loading trends..."></div>
					</div>
					<div id="dashboard-trends-error" class="dashboard-trends-error">
						<p class="dashboard-trends-error-text common-text" id="dashboard-trends-error-text">Unable to load trends.</p>
						<button class="dashboard-trends-error-button common-text" id="dashboard-trends-error-button" title="Retry" onclick="MEME.loadTrends();">Retry</button>
					</div>
					<ul id="dashboard-trends-list" class="dashboard-trends-list"></ul>
					<button onclick="MEME.showCategories();" class="dashboard-trends-button common-text" id="dashboard-trends-button" title="Show all categories">All categories</button>
					<p class="dashboard-slide-title common-text" scope="newsletter">Stay tuned!</p>
					<form id="dashboard-slide-newsletter" accept-charset="utf-8" action="{{route('newsletter.subscribe')}}" method="post" class="dashboard-newsletter" onsubmit="Newsletter.triggerSubscription(event);">
						<input type="email" placeholder="Your e-mail address" id="dashboard-slide-newsletter-email" name="email" class="dashboard-newsletter-input common-text" autocomplete="off" />
						<input type="submit" class="dashboard-newsletter-button common-text" id="dashboard-slide-newsletter-button" value="Subscribe" title="Subscribe" />
					</form>
				</div>
			</div><div class="slide-right">
				@if ( $role === 'search' )
					<form accept-charset="utf-8" action="{{route('dashboard.search')}}" method="get" id="dashboard-search" class="dashboard-search" onsubmit="MEME.search(event);">
						<div class="dashboard-search-container">
							<p class="dashboard-title common-text">Search for MEMEs</p>
							<input type="search" class="dashboard-input common-text" id="dashboard-search-input" name="q" placeholder="Search for something" onchange="MEME.search();" value="{{$q}}" />
							<p class="dashboard-stats common-text" id="dashboard-search-stats"></p>
						</div>
					</form>
				@endif
				@if ( $role === 'category' )
					<p id="dashboard-category-name" class="dashboard-category-name common-text">{{$categoryName}}</p>
					<p id="dashboard-category-counter" class="dashboard-category-counter common-text">{{$counter}} MEMEs in this category.</p>
				@endif
				@if ( $role === 'author' )
					<a id="dashboard-author-name" class="dashboard-author-name common-text" href="/user/{{$author}}">{{$fullName}}</a>
					<p id="dashboard-author-counter" class="dashboard-author-counter common-text">{{$counter}} MEMEs created by this user.</p>
				@endif
				<div id="dashboard-loader">
					<div class="common-spinner common-spinner-margin" id="dashboard-loader-spinner" title="Loading memes..."></div>
				</div>
				<div id="dashboard-error">
					<p id="dashboard-error-text" class="dashboard-error-text common-text">Unable to load memes.</p>
					<button onclick="MEME.getMEMEs('{{$role}}');" title="Retry" class="dashboard-error-button common-button-error common-text" id="dashboard-error-button">Retry</button>
				</div>
				<ul id="dashboard-list"></ul>
				<form id="dashboard-slide-newsletter-min" accept-charset="utf-8" action="{{route('newsletter.subscribe')}}" method="post" class="dashboard-newsletter-min" onsubmit="Newsletter.triggerSubscription(event);">
					<p class="dashboard-newsletter-title-min common-text" id="dashboard-slide-newsletter-title-min">Stay tuned!</p>
					<input type="email" placeholder="Your e-mail address" id="dashboard-slide-newsletter-email-min" name="email" class="dashboard-newsletter-input-min common-text" autocomplete="off" />
					<input type="submit" class="dashboard-newsletter-button-min common-text" id="dashboard-slide-newsletter-button-min" value="Subscribe" title="Subscribe" />
				</form>
				@if ( $role === 'author' )
					<input type="hidden" id="dashboard-author" value="{{$author}}" />
				@endif
				@if ( $role === 'category' )
					<input type="hidden" id="dashboard-category" value="{{$category}}" />
				@endif
			</div>
		</div>
		@component('components/forms')
		@endcomponent
		@component('components/cookiePolicy')
		@endcomponent
		@component('components/footer')
		@endcomponent
		<script type="text/javascript" src="/js/library.min.js"></script>
	</body>
</html>