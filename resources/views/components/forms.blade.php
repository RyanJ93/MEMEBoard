<form class="form form-login" method="post" action="{{route('user.login')}}" id="form-login" autocomplete="on" accept-charset="utf-8" onsubmit="User.authenticator.triggerLogin(event);">
	<p class="form-title common-text">Login with your credentials</p>
	<label for="login-email" class="form-label common-text form-label-no-margin">E-Mail</label>
	<input type="email" id="form-login-email" name="email" placeholder="E-Mail" required="true" class="form-input common-text" />
	<label for="login-password" class="form-label common-text">Password</label>
	<input type="password" id="form-login-password" name="password" placeholder="Password" required="true" class="form-input common-text" />
	<a class="form-link common-text" id="form-login-password-restore" href="javascript:void(0);" title="Restore password" onclick="User.authenticator.restorePassword();">Restore password</a>
	<br />
	<label for="login-password" class="form-label common-text form-inline">Remember me</label>
	<input type="checkbox" id="form-login-remember" class="form-inline" />
	<br /><br />
	<input type="reset" value="Cancel" title="Cancel" class="form-button common-text" onclick="UI.form.hideLogin();" />
	<input type="submit" value="Login" title="Login" class="form-button common-text" />
</form>
<form class="form form-register" method="post" action="{{route('user.register')}}" id="form-register" autocomplete="on" accept-charset="utf-8" onsubmit="User.authenticator.triggerRegister(event);">
	<p class="form-title common-text">Create your account</p>
	<label for="login-email" class="form-label common-text form-label-no-margin">Name</label>
	<input type="text" id="form-register-name" name="name" placeholder="Name" required="true" class="form-input common-text" />
	<label for="login-surname" class="form-label common-text">Surname</label>
	<input type="text" id="form-register-surname" name="surname" placeholder="Surname" required="true" class="form-input common-text" />
	<label for="login-email" class="form-label common-text">E-Mail</label>
	<input type="email" id="form-register-email" name="email" placeholder="E-Mail" required="true" class="form-input common-text" onchange="User.authenticator.validateEmail(event);" />
	<p id="form-register-email-validation-ok" class="form-register-validation form-validation form-validation-ok common-text">Your e-mail address is valid and accepted.</p>
	<p id="form-register-email-validation-error-1" class="form-register-validation form-validation form-validation-error common-text">Your e-mail address appears to be invalid or not existing.</p>
	<p id="form-register-email-validation-error-2" class="form-register-validation form-validation form-validation-error common-text">Your e-mail address is disposable or not accepted.</p>
	<label for="login-password" class="form-label common-text">Password</label>
	<input type="password" id="form-register-password" name="password" placeholder="Password" required="true" class="form-input common-text" onchange="User.authenticator.testPassword(event);" />
	<div class="form-progress" id="form-register-password-progress">
		<div class="form-progress-value" id="form-register-password-progress-value"></div>
	</div>
	<br /><br />
	<input type="reset" value="Cancel" title="Cancel" class="form-button common-text" onclick="UI.form.hideRegistration();" />
	<input type="submit" value="Register" title="Register" class="form-button common-text" />
</form>
<div id="form-overlay" class="form-overlay" onclick="UI.form.hideAll();"></div>