<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>Meme board | Not found</title>
			<meta property="og:title" content="Meme board | Not found" />
			<meta name="twitter:title" content="Meme board | Not found" />
		@endcomponent
		<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
		<link rel="stylesheet" href="/css/library.css" />
		<link rel="stylesheet" href="/css/theme.css" />
	</head>
	<body>
		@component('components/header')
		@endcomponent
		<div class="slide">
			<p class="error-title common-text" id="error-title">404</p>
			<p class="error-subtitle common-text" id="error-subtitle">The page you were looking for was not found.</p>
			<form accept-charset="utf8" action="{{route('dashboard.search')}}" method="get" class="error-form">
				<p class="error-text common-text" id="error-form-text">Search for something</p>
				<input type="search" class="error-input common-text" id="error-form-input" name="q" placeholder="Search for something" />
				<input type="submit" class="error-button common-text" id="error-form-button" value="Search" title="Search" />
			</form>
		</div>
		@component('components/forms')
		@endcomponent
		@component('components/cookiePolicy')
		@endcomponent
		@component('components/footer')
		@endcomponent
		<script type="text/javascript" src="/js/library.js"></script>
	</body>
</html>