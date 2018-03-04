<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>{{env('BASE_TITLE', 'MEMEBoard')}} | {{$fullName}}</title>
			<meta property="og:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | {{$fullName}}" />
			<meta name="twitter:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | {{$fullName}}" />
		@endcomponent
	</head>
	<body onload="Comment.loadComments();">
		@component('components/header')
		@endcomponent
		<div class="slide">
			<p class="user-title common-text" scope="main">User details:</p>
			<div class="user-section user-section-picture" id="user-section-picture">
				<div class="user-profile-picture" id="user-section-profile-picture"></div>
			</div><div class="user-section user-section-info" id="user-section-info">
				<p class="user-name common-text" id="user-section-name">{{$fullName}}</p>
				<p class="user-date common-text" id="user-section-date">Join date: {{$user->created_at->formatLocalized('%B %d, %Y')}}</p>
				<p class="user-counter user-section-counter common-text">Comments count: {{$counters['comments']}}</p>
				@if ( $user->admin === 1 )
					<p class="user-counter user-section-counter common-text">MEME count: {{$counters['memes']}}</p>
					<br />
					<div id="user-section-admin-badge" class="user-admin-badge common-text">Admin</div>
				@endif
				@if ( $mine === true || $admin === true )
					@if ( $mine === true )
						<button class="user-button common-text" id="user-section-button" title="Edit your profile" href="{{route('user.edit', $user->id)}}" onclick="window.location.href=this.getAttribute('href');">Edit your profile</button>
					@else
						<button class="user-button common-text" id="user-section-button" title="Edit user" href="{{route('user.edit', $user->id)}}" onclick="window.location.href=this.getAttribute('href');">Edit user</button>
					@endif
				@endif
				@if ( $user->admin === 1 )
					<br />
					<a href="" class="user-link common-text user-section-link" title="See all memes">See all memes</a>
				@endif
				@if ( $mine === true || $admin === true )
					<br />
					@if ( $mine === true )
						<a href="javascript:void(0);" class="user-link common-text user-section-link" title="Remove your account" onclick="User.deleteUserAndContents(null);">Remove your account</a>
					@else
						<a href="javascript:void(0);" class="user-link common-text user-section-link" title="Remove account" onclick="User.deleteUser(null);">Remove account</a>
						<br />
						<a href="javascript:void(0);" class="user-link common-text user-section-link" title="Remove account and contents" onclick="User.deleteUserAndContents(null);">Remove account and contents</a>
					@endif
				@endif
			</div>
			<p class="user-title common-text" scope="comments">User comments:</p>
			<div id="user-comments-loader">
				<div id="user-comments-loader-spinner" class="common-spinner" title="Loading comments..."></div>
			</div>
			<div id="user-comments-error">
				<p class="user-comments-error-text common-text" id="user-comments-error-text">Unable to load comments.</p>
				<button class="user-comments-error-button common-text" id="user-comments-error-button" title="Retry" onclick="Comment.loadComments();">Retry</button>
			</div>
			<ul id="user-comments-list"></ul>
			<input type="hidden" id="user-id" value="{{$user->id}}" />
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