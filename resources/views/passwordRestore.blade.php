<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>{{env('BASE_TITLE', 'MEMEBoard')}} | Create a new meme</title>
			<meta property="og:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | Create a new meme" />
			<meta name="twitter:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | Create a new meme" />
		@endcomponent
	</head>
	<body>
		@component('components/header')
		@endcomponent
		<div class="slide">
			<form accept-charset="utf-8" action="{{route('user.restorePassword')}}" method="post" id="password-restore" autocomplete="on" onsubmit="User.restorePassword(event);">
				<label for="password-restore-password" class="password-restore-label common-text">New password</label>
				<input type="password" class="password-restore-input common-text" id="password-restore-password" name="password" placeholder="New password" />
				<label for="password-restore-confirm" class="password-restore-label common-text">Confirm new password</label>
				<input type="password" class="password-restore-input common-text" id="password-restore-confirm" name="confirm" placeholder="Confirm new password" />
				<input type="hidden" value="{{$token}}" id="password-restore-token" name="token" />
				<input type="hidden" value="{{$email}}" id="password-restore-email" name="email" />
				<input type="submit" class="password-restore-button common-text" id="password-restore-button" title="Change password" value="Change password" />
			</form>
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