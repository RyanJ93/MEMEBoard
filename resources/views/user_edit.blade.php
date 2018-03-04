<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>{{env('BASE_TITLE', 'MEMEBoard')}} | {{$fullName}} | Edit profile</title>
			<meta property="og:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | {{$fullName}} | Edit profile" />
			<meta name="twitter:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | {{$fullName}} | Edit profile" />
		@endcomponent
	</head>
	<body>
		@component('components/header')
		@endcomponent
		<div class="slide">
			<p class="user-edit-title common-text">Edit your profile</p>
			<form accept-charset="utf-8" action="{{route('user.update', $user->id)}}" method="post" id="user-edit" onsubmit="User.saveChanges(event);" autocomplete="off">
				<label class="user-edit-label user-edit-label-no-margin common-text" for="user-edit-input-name">Name</label>
				<input type="text" class="user-edit-input common-text" id="user-edit-input-name" name="name" placeholder="Name" required="true" maxlength="30" value="{{$user->name}}" />
				<input type="hidden" id="user-edit-input-name-old" value="{{$user->name}}" />
				<label class="user-edit-label common-text" for="user-edit-input-surname">Surname</label>
				<input type="text" class="user-edit-input common-text" id="user-edit-input-surname" name="surname" placeholder="Surname" required="true" maxlength="30" value="{{$user->surname}}" />
				<input type="hidden" id="user-edit-input-surname-old" value="{{$user->surname}}" />
				<label class="user-edit-label common-text" for="user-edit-input-email">E-Mail</label>
				<input type="email" class="user-edit-input common-text" id="user-edit-input-email" name="email" placeholder="E-Mail" required="true" maxlength="30" value="{{$user->email}}" onchange="User.authenticator.validateEmail(event);" />
				<p id="user-edit-email-validation-ok" class="user-edit-validation user-edit-validation-ok common-text">Your e-mail address is valid and accepted.</p>
				<p id="user-edit-email-validation-error-1" class="user-edit-validation user-edit-validation-error common-text">Your e-mail address appears to be invalid or not existing.</p>
				<p id="user-edit-email-validation-error-2" class="user-edit-validation user-edit-validation-error common-text">Your e-mail address is disposable or not accepted.</p>
				<input type="hidden" id="user-edit-input-email-old" value="{{$user->email}}" />
				<input type="submit" class="user-edit-button common-text" value="Save" title="Save" />
				<input type="reset" class="user-edit-button common-text" value="Revert" title="Revert" onclick="User.revertChanges(event);" />
			</form>
			<br /><br />
			<p class="user-edit-title common-text">Change password</p>
			<form accept-charset="utf-8" action="{{route('user.update', $user->id)}}" method="post" id="user-password-change" onsubmit="User.changePassword(event);" autocomplete="off">
				<label class="user-edit-label user-edit-label-no-margin common-text" for="user-edit-input-password-old">Old password</label>
				<input type="password" class="user-edit-input common-text" id="user-edit-input-password-old" name="password-old" placeholder="Old password" required="true" maxlength="30" />
				<label class="user-edit-label user-edit-label common-text" for="user-edit-input-password">New password</label>
				<input type="password" class="user-edit-input common-text" id="user-edit-input-password" name="password" placeholder="New password" required="true" maxlength="30" onchange="User.authenticator.testPassword(event);" />
				<div class="user-edit-progress" id="user-edit-password-progress">
					<div class="user-edit-progress-value" id="user-edit-password-progress-value"></div>
				</div>
				<label class="user-edit-label user-edit-label common-text" for="user-edit-input-password-confirm">Repeat new password</label>
				<input type="password" class="user-edit-input common-text" id="user-edit-input-password-confirm" name="password-confirm" placeholder="Repeat new password" required="true" maxlength="30" />
				<input type="submit" class="user-edit-button common-text" value="Change" title="Change" />
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