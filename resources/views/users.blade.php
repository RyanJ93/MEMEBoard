<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>{{env('BASE_TITLE', 'MEMEBoard')}} | Registered users and admins</title>
			<meta property="og:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | Registered users and admins" />
			<meta name="twitter:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | Registered users and admins" />
		@endcomponent
	</head>
	<body onload="User.loadUsers();">
		@component('components/header')
		@endcomponent
		<div class="slide">
			<p id="users-title" class="users-title common-text">Registered users and admins:</p>
			<div id="users-loader">
				<div id="users-loader-spinner" class="common-spinner" title="Loading users..."></div>
			</div>
			<div id="users-error">
				<p id="users-error-text" class="users-error-text common-text">Unable to load users.</p>
				<button id="users-error-button" class="users-error-button common-text" title="Retry" onclick="User.loadUsers();">Retry</button>
			</div>
			<ul id="users-list"></ul>
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