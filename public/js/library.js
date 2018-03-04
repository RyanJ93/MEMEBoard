'use strict';

var Utils = {
	/**
	* @var String CSRFToken A string containing the CSRF token that has been get from HTML meta tags.
	*/
	CSRFToken: null,
	
	/**
	* Create a new XHR object.
	*
	* @return object A XHR object used to manage AJAX connections.
	*/
	makeConnection: function(){
		try {
	        return new XMLHttpRequest();
	    }catch(e){}
	    try {
	        return new ActiveXObject('Msxml3.XMLHTTP');
	    }catch(e){}
	    try {
	        return new ActiveXObject('Msxml2.XMLHTTP.6.0');
	    }catch(e){}
	    try {
	        return new ActiveXObject('Msxml2.XMLHTTP.3.0');
	    }catch(e){}
	    try {
	        return new ActiveXObject('Msxml2.XMLHTTP');
	    }catch(e){}
	    try {
	        return new ActiveXObject('Microsoft.XMLHTTP');
	    }catch(e){}
	    return null;
	},
	
	/**
	* Gets the CSRF token.
	*
	* @param Boolean url If set to "true", the token will be encoded as GET parameter, otherwise will be returned as it is.
	*
	* @return String A string containing the CSRF token.
	*/
	getCSRFToken: function(url){
		if ( this.CSRFToken !== null ){
			return this.CSRFToken;
		}
		var token = document.querySelector('meta[name="csrf-token"]');
		token = token === null ? null : token.getAttribute('content');
		this.CSRFToken = token;
		return token;
	},
	
	/**
	* Gets a specified parameter from the query string sent with current URL.
	*
	* @param String name A string containing the parameter name.
	*
	* @return String A string containing the parameter value.
	*/
	getParam: function(name){
		var query = window.location.href.indexOf('?');
		if ( query < 0 ){
			return null;
		}
		query = window.location.href.substr(query + 1).split('&');
		for ( var i = 0 ; i < query.length ; i++ ){
			var buffer = query[i].split('=');
			if ( buffer[0] === name ){
				return decodeURIComponent(buffer[1]);
			}
		}
		return null;
	},
	
	/**
	* Creates a string representation of a number.
	*
	* @param Number counter An integer number greater or equal than zero.
	*
	* @return String A string representation of the counter value.
	*/
	stringifyCounterValue: function(value){
		value = parseInt(value);
		if ( isNaN(value) === true || value <= 0 ){
			return '0';
		}
		value = Math.abs(value);
		if ( Math.floor( value / 1000 ) > 0 ){
			value = value / 1000;
			if ( Math.floor( value / 1000 ) > 0 ){
				value = value / 1000;
				if ( Math.floor( value / 1000 ) > 0 ){
					value = value / 1000;
					if ( Math.floor( value / 1000 ) > 0 ){
						value = value / 1000;
						return Math.floor( value / 1000 ) > 0 ? ( Math.floor( value / 1000 ) + ' P' ) : ( Math.floor(value) + ' T' );
					}
					return Math.floor(value) + ' G';
				}
				return Math.floor(value) + ' M';
			}
			return Math.floor(value) + ' K';
		}
		return value.toString();
	},
	
	/**
	* Handles an error returned from the server through an HTTP status code.
	*
	* @param Number code An integer number greater than zero representing the HTTP status code returned by the server.
	* @param Boolean auth If set to "true" will be thrown an error if no authenticated user is present, otherwise this will be ignored.
	* @param Boolean admin If set to "true" it means that this method shall show an alert if no admin user is authenticated and the server send an error for this reason, otherwise this will be ignored.
	*
	* @return Boolean If the code is 200 will be returned "true", otherwise "false".
	*/
	handleHTTPError: function(code, auth, admin){
		if ( code !== 200 ){
			switch ( code ){
				case 403:{
					if ( auth === true ){
						UI.alert.show('You need to be logged in before using this feature.', 'Login required.', 'error');
					}
				}break;
				case 405:{
					if ( admin === true ){
						UI.alert.show('This feature is reserved to admins only.', 'Action not allowed.', 'error');
					}
				}break;
				case 419:{
					UI.alert.show('Your session has expired, page needs to be reloaded.', 'Session expired.', 'message', function(){
						window.location.reload(true);
					});
				}break;
			}
			if ( ( code !== 403 || auth !== true ) && ( code !== 405 || admin !== true ) && code !== 419 ){
				UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
			}
			return false;
		}
		return true;
	}
};

var UI = {
	dataContainer: function(){
		/**
		* @var Object loader A DOM Object that references to the HTML element that shall be shown during data loading.
		*/
		this.loader = null;
		
		/**
		* @var Object error A DOM Object that references to the HTML element that shall be shown if an error occurred during data loading.
		*/
		this.error = null;
		
		/**
		* @var Object list A DOM Object that references to the HTML element that will contain data.
		*/
		this.list = null;
		
		/**
		* Shows the HTML element that shall be shown during data loading, this method is chainable.
		*/
		this.showLoader = function(){
			this.error.style.opacity = '0';
			this.list.style.opacity = '0';
			window.setTimeout(function(){
				this.error.style.display = 'none';
				this.list.style.display = 'none';
				this.loader.style.display = 'block';
				this.loader.style.opacity = '1';
			}.bind(this), 250);
			return this;
		}
		
		/**
		* Shows the HTML element that shall be shown if an error occurred during data loading, this method is chainable.
		*/
		this.showError = function(){
			this.loader.style.opacity = '0';
			this.list.style.opacity = '0';
			window.setTimeout(function(){
				this.loader.style.display = 'none';
				this.list.style.display = 'none';
				this.error.style.display = 'block';
				this.error.style.opacity = '1';
			}.bind(this), 250);
			return this;
		}
		
		/**
		* Shows the HTML element that contains the data, this method is chainable.
		*
		* @param Boolean table If set to "true" the "display" property will be set to "table", otherwise will be set to "block".
		*/
		this.showList = function(table){
			this.loader.style.opacity = '0';
			this.error.style.opacity = '0';
			window.setTimeout(function(){
				this.loader.style.display = 'none';
				this.error.style.display = 'none';
				this.list.style.display = table === true ? 'table' : 'block';
				this.list.style.opacity = '1';
			}.bind(this), 250);
			return this;
		}
	},
	
	form: {
		/**
		* Shows the login dialog.
		*/
		showLogin: function(){
			UI.menu.close();
			var form = document.getElementById('form-login');
			var overlay = document.getElementById('form-overlay');
			form.style.display = 'block';
			overlay.style.display = 'block';
			window.setTimeout(function(){
				form.style.opacity = '1';
				overlay.style.opacity = '1';
			}, 25);
		},
		
		/**
		* Hides the login dialog.
		*
		* @param Boolean clean If set to "true", the form fields will be emptied, otherwise not.
		*/
		hideLogin: function(clean){
			var form = document.getElementById('form-login');
			var overlay = document.getElementById('form-overlay');
			overlay.style.opacity = '0';
			form.style.opacity = '0';
			window.setTimeout(function(){
				form.style.display = 'none';
				overlay.style.display = 'none';
				if ( clean === true ){
					document.getElementById('form-login-email').value = '';
					document.getElementById('form-login-password').value = '';
				}
			}, 250);
		},
		
		/**
		* Shows the registration dialog.
		*/
		showRegistration: function(){
			UI.menu.close();
			var form = document.getElementById('form-register');
			var overlay = document.getElementById('form-overlay');
			form.style.display = 'block';
			overlay.style.display = 'block';
			window.setTimeout(function(){
				form.style.opacity = '1';
				overlay.style.opacity = '1';
			}, 25);
		},
		
		/**
		* Hides the registration dialog.
		*
		* @param Boolean clean If set to "true", the form fields will be emptied, otherwise not.
		*/
		hideRegistration: function(clean){
			var form = document.getElementById('form-register');
			var overlay = document.getElementById('form-overlay');
			overlay.style.opacity = '0';
			form.style.opacity = '0';
			window.setTimeout(function(){
				form.style.display = 'none';
				overlay.style.display = 'none';
				if ( clean === true ){
					document.getElementById('form-register-name').value = '';
					document.getElementById('form-register-surname').value = '';
					document.getElementById('form-register-email').value = '';
					document.getElementById('form-register-password').value = '';
					var messages = document.querySelectorAll('.form-register-validation');
					for ( var i = 0 ; i < messages.length ; i++ ){
						messages.item(i).style.display = 'none';
					}
					document.getElementById('form-register-password-progress').style.display = 'none';
					document.getElementById('form-register-password-progress-value').style.width = '0%';
					UI.form.resetRegistrationSize();
				}
			}, 250);
		},
		
		/**
		* Sets the proper value for the attribute "messages".
		*/
		resetRegistrationSize: function(){
			var count = 0;
			var messages = document.querySelectorAll('.form-register-validation');
			for ( var i = 0 ; i < messages.length ; i++ ){
				if ( messages.item(i).style.display === 'block' ){
					count++;
				}
			}
			if ( document.getElementById('form-register-password-progress').style.display === 'block' ){
				count++;
			}
			document.getElementById('form-register').setAttribute('messages', count.toString());
		},
		
		/**
		* Hides all dialogs.
		*/
		hideAll: function(){
			this.hideLogin();
			this.hideRegistration();
			UI.menu.close();
		}
	},
	
	menu: {
		/**
		* Toggles the menu for mobile devices.
		*/
		toggle: function(){
			var menu = document.getElementById('header-content-mobile');
			var overlay = document.getElementById('header-overlay');
			if ( menu.style.display === 'block' ){
				this.close();
				return;
			}
			if ( window.innerWidth <= 480 ){
				menu.style.display = 'block';
				overlay.style.display = 'block';
				window.setTimeout(function(){
					menu.style.opacity = '1';
					overlay.style.opacity = '1';
				}, 25);
			}
		},
		
		/**
		* Closes the menu for mobile devices.
		*/
		close: function(){
			var menu = document.getElementById('header-content-mobile');
			if ( menu.style.display !== 'block' ){
				return;
			}
			var overlay = document.getElementById('header-overlay');
			menu.style.opacity = '0';
			overlay.style.opacity = '0';
			window.setTimeout(function(){
				menu.style.display = 'none';
				overlay.style.display = 'none';
			}, 250);
		},
		
		/**
		* Sets the MEMEs' ordering, this method works in the homepage only.
		*
		* @param Object event The event object.
		* @param String ordering A string contianing the ordering name.
		*/
		setOrdering: function(event, ordering){
			var url = window.location.href.substr(window.location.href.lastIndexOf('/') + 1);
			if ( url.lastIndexOf('#') >= 0 ){
				url = url.substr(0, url.lastIndexOf('#'));
			}
			if ( url !== '' ){
				return;
			}
			event.preventDefault();
			event.stopPropagation();
			UI.menu.close();
			MEME.setOrdering(ordering);
		}
	},
	
	cookiePolicy: {
		/**
		* Checks if the cookie agreement has been seen.
		*
		* @return Boolean If the agreement has been seen will be returned "true", otherwise "false".
		*/
		checkAgreement: function(){
			var cookies = decodeURIComponent(document.cookie).split(';');
			for ( var i = 0 ; i < cookies.length ; i++ ){
				var buffer = cookies[i].trim();
				if ( buffer.indexOf('cl') === 0 ){
					return buffer === 'cl=1' ? true : false;
				}
			}
			return false;
		},
		
		/**
		* Shows the banner related to cookie policy.
		*/
		show: function(){
			document.getElementById('cookie-policy').style.display = 'block';
		},
		
		/**
		* Closes the banner related to cookie policy and creates a cookie in order to indicate that the agreement has been seen.
		*/
		agree: function(){
			var date = new Date();
			date.setTime(date.getTime() + 2592000000);
			document.cookie = 'cl=1;expires=' + ( date.toGMTString() ) + ';path=/';
			document.getElementById('cookie-policy').style.display = 'none';
		}
	},
	
	alert: {
		/**
		* Shows an alert to the user.
		*
		* @param String text A string containing the message to be displayed.
		* @param String title A string containing an optional title.
		* @param String scope A string representing the alert's scope, such "error", "success" or "message".
		* @param Function onClose A function provided as callback that will be executed when the alert will be close.
		*/
		show: function(text, title, scope, onClose){
			var element = document.createElement('div');
			element.className = 'alert';
			element.setAttribute('scope', ( typeof(scope) === 'string' ? scope : '' ));
			if ( typeof(title) === 'string' && title !== '' ){
				var innerText = document.createElement('p');
				innerText.className = 'common-text alert-title';
				innerText.textContent = title;
				element.appendChild(innerText);
			}
			if ( typeof(onClose) === 'function' ){
				element.onClose = onClose;
			}
			var innerText = document.createElement('p');
			innerText.className = 'common-text alert-text';
			innerText.textContent = text;
			element.appendChild(innerText);
			var button = document.createElement('button');
			button.className = 'common-text alert-button';
			button.title = 'Close';
			button.textContent = 'Close';
			button.onclick = UI.alert.closeAll;
			element.appendChild(button);
			var closed = UI.alert.closeAll(false);
			var overlay = document.getElementById('alert-overlay');
			if ( overlay === null ){
				overlay = document.createElement('div');
				overlay.id = 'alert-overlay';
				overlay.className = 'alert-overlay';
				overlay.onclick = UI.alert.closeAll;
				document.body.appendChild(overlay);
			}
			document.body.appendChild(element);
			overlay.style.display = 'block';
			window.setTimeout(function(){
				element.style.opacity = '1';
				overlay.style.opacity = '1';
			}, ( closed === true ? 275 : 25 ));
		},
		
		/**
		* Hides all alerts.
		*
		* @param Boolean hideOverlay If set to "false" the overlay will not be hided, otherwise it will be hided as of all alerts.
		*
		* @return Boolean If at least one alert has been closed will be returned "true", otherwise "false".
		*/
		closeAll: function(hideOverlay){
			var executeCallbacks = typeof(hideOverlay) === 'object' ? true : false;
			var elements = document.body.querySelectorAll('div.alert');
			for ( var i = 0 ; i < elements.length ; i++ ){
				if ( executeCallbacks === true && typeof(elements[i].onClose) === 'function' ){
					elements[i].onClose.call(this);
				}
				elements[i].style.opacity = '0';
			}
			if ( elements.length > 0 ){
				window.setTimeout(function(){
					var elements = document.body.querySelectorAll('div.alert');
					for ( var i = 0 ; i < elements.length ; i++ ){
						elements[i].parentNode.removeChild(elements[i]);
					}
				}, 250);
			}
			if ( hideOverlay !== false ){
				var overlay = document.getElementById('alert-overlay');
				overlay.style.opacity = '0';
				window.setTimeout(function(){
					overlay.style.display = 'none';
				}, 250);
			}
			return elements.length === 0 ? false : true;
		}
	}
};

var User = {
	/**
	* @var Number page An integer number greater than zero containing the page number used in users listing.
	*/
	page: 1,
	
	authenticator: {
		/**
		* Calls user login through form submit.
		*
		* @param Object event The event object.
		*/
		triggerLogin: function(event){
			event.preventDefault();
			event.stopPropagation();
			User.authenticator.login();
		},
		
		/**
		* Authenticates the user.
		*/
		login: function(){
			var email = document.getElementById('form-login-email').value;
			if ( email === '' || email.length > 256  ){
				return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
			}
			var password = document.getElementById('form-login-password').value;
			if ( password === '' || password.length > 30 ){
				return UI.alert.show('You must provide a password, max length is 30 chars.', 'Invalid password.', 'error');
			}
			var remember = document.getElementById('form-login-remember').checked === true ? '1' : '0';
			var connection = Utils.makeConnection();
			connection.open('POST', document.getElementById('form-login').action, true);
			connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
			connection.onreadystatechange = function(){
				if ( connection.readyState > 3 ){
					if ( Utils.handleHTTPError(connection.status, false) === false ){
						return false;
					}
					try{
						var data = JSON.parse(connection.responseText);
						if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
							switch ( typeof(data.code) !== 'number' ? null : data.code ){
								case 1:{
									return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
								}break;
								case 2:{
									return UI.alert.show('You must provide a password, max length is 30 chars.', 'Invalid password.', 'error');
								}break;
								case 3:{
									return UI.alert.show('Check your credentials or restore password if forgotten.', 'Wrong credentials.', 'error');
								}break;
								default:{
									return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
								}break;
							}
						}
						UI.alert.show('Welcome back!', 'Logged in!', 'success', function(){
							window.location.reload(true);
						});
					}catch(ex){
						console.log(ex);
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
				}
			};
			connection.send('email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password) + '&remember=' + remember);
		},
		
		/**
		* Calls user registration through form submit.
		*
		* @param Object event The event object.
		*/
		triggerRegister: function(event){
			event.preventDefault();
			event.stopPropagation();
			User.authenticator.register();
		},
		
		/**
		* Creates a new user by using information from the registration form.
		*/
		register: function(){
			var name = document.getElementById('form-register-name').value;
			if ( name === '' || name.length > 30 ){
				return UI.alert.show('You must provide a name, max length is 30 chars.', 'Invalid name.', 'error');
			}
			var surname = document.getElementById('form-register-surname').value;
			if ( surname === '' || surname.length > 30 ){
				return UI.alert.show('You must provide a surname, max length is 30 chars.', 'Invalid surname.', 'error');
			}
			var email = document.getElementById('form-register-email').value;
			if ( email === '' || email.length > 256  ){
				return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
			}
			var password = document.getElementById('form-register-password').value;
			if ( password === '' || password.length > 30 ){
				return UI.alert.show('You must provide a password, max length is 30 chars.', 'Invalid password.', 'error');
			}
			var connection = Utils.makeConnection();
			connection.open('POST', document.getElementById('form-register').action, true);
			connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
			connection.onreadystatechange = function(){
				if ( connection.readyState > 3 ){
					if ( Utils.handleHTTPError(connection.status, false) === false ){
						return false;
					}
					try{
						var data = JSON.parse(connection.responseText);
						if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
							switch ( typeof(data.code) !== 'number' ? null : data.code ){
								case 1:{
									return UI.alert.show('You must provide a surname, max length is 30 chars.', 'Invalid surname.', 'error');
								}break;
								case 2:{
									return UI.alert.show('You must provide a surname, max length is 30 chars.', 'Invalid surname.', 'error');
								}break;
								case 3:{
									return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
								}break;
								case 4:{
									return UI.alert.show('You must provide a password, max length is 30 chars.', 'Invalid password.', 'error');
								}break;
								case 5:{
									return UI.alert.show('An user with the same e-mail address already exists.', 'User already existing.', 'error');
								}break;
								case 7:{
									return UI.alert.show('Your e-mail address appears to be invalid or not existing.', 'Invalid e-mail address.', 'error');
								}break;
								case 8:{
									return UI.alert.show('Your e-mail address is disposable or not accepted.', 'Invalid e-mail address.', 'error');
								}break;
								default:{
									return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
								}break;
							}
						}
						alert('Registration completed!');
						window.setTimeout(function(){
							window.location.reload(true);
						}, 1000);
					}catch(ex){
						console.log(ex);
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
				}
			};
			connection.send('name=' + encodeURIComponent(name) + '&surname=' + encodeURIComponent(surname) + '&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password));
		},
		
		/**
		* Validates the user's e-mail address and check if it is existing and if is disposable.
		*
		* @param Object event The event object.
		*/
		validateEmail: function(event){
			var message = document.getElementById('user-edit-email-validation-ok');
			var identifier = '';
			if ( message === null ){
				message = document.getElementById('form-register-email-validation-ok');
				if ( message === null ){
					return;
				}
				document.getElementById('form-register-email-validation-error-1').style.display = 'none';
				document.getElementById('form-register-email-validation-error-2').style.display = 'none';
				identifier = 'form-register-email-validation-';
			}else{
				document.getElementById('user-edit-email-validation-error-1').style.display = 'none';
				document.getElementById('user-edit-email-validation-error-2').style.display = 'none';
				identifier = 'user-edit-email-validation-';
			}
			message.style.display = 'none';
			if ( identifier === 'form-register-email-validation-' ){
				UI.form.resetRegistrationSize();
			}
			if ( event.target.value === '' || event.target.value.length > 256 ){
				return;
			}
			var connection = Utils.makeConnection();
			connection.open('POST', '/validateEmail', true);
			connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
			connection.onreadystatechange = function(){
				if ( connection.readyState > 3 ){
					if ( Utils.handleHTTPError(connection.status, false) === false ){
						return false;
					}
					try{
						var data = JSON.parse(connection.responseText);
						if ( typeof(data.result) === 'string' && data.result === 'success' ){
							var id = identifier + 'ok';
							if ( typeof(data.data.existing) === 'boolean' && data.data.existing === false ){
								var id = identifier + 'error-1';
							}else if ( typeof(data.data.trusted) === 'boolean' && data.data.trusted === false ){
								var id = identifier + 'error-2';
							}
							document.getElementById(id).style.display = 'block';
							if ( identifier === 'form-register-email-validation-' ){
								UI.form.resetRegistrationSize();
							}
						}
					}catch(ex){
						console.log(ex);
					}
				}
			};
			connection.send('email=' + encodeURIComponent(event.target.value));
		},
		
		/**
		* Analyzes the user's password in order to test its strength.
		*
		* @param Object event The event object.
		*/
		testPassword: function(event){
			var form = false;
			var progress = document.getElementById('user-edit-password-progress');
			if ( progress === null ){
				form = true;
				progress = document.getElementById('form-register-password-progress');
				if ( progress === null ){
					return;
				}
			}
			progress.style.display = 'none';
			progress.setAttribute('display', 'ok');
			progress.title = 'Your password seems to be strong!';
			progress.querySelector('div').style.width = '0%';
			if ( form === true ){
				UI.form.resetRegistrationSize();
			}
			if ( event.target.value === '' || event.target.value.length > 30 ){
				return;
			}
			var connection = Utils.makeConnection();
			connection.open('POST', '/validatePassword', true);
			connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
			connection.onreadystatechange = function(){
				if ( connection.readyState > 3 ){
					if ( Utils.handleHTTPError(connection.status, false) === false ){
						return false;
					}
					try{
						var data = JSON.parse(connection.responseText);
						if ( typeof(data.result) === 'string' && data.result === 'success' && typeof(data.data.score) === 'number' ){
							if ( data.data.score < 40 ){
								progress.setAttribute('display', 'error');
								progress.title = 'Your password is weak, we will accept it anyway, but consider using a better password.';
							}else if ( data.data.score < 75 ){
								progress.setAttribute('display', 'warn');
								progress.title = 'Your passord is strong enough, anyway you could improve it.';
							}
							progress.title = data.data.score + '/100 | ' + progress.title;
							progress.querySelector('div').style.width = data.data.score + '%';
							progress.style.display = 'block';
							if ( form === true ){
								UI.form.resetRegistrationSize();
							}
						}
					}catch(ex){
						console.log(ex);
					}
				}
			};
			connection.send('password=' + encodeURIComponent(event.target.value));
		},
		
		/**
		* Removes the session of the authenticated user.
		*/
		logout: function(){
			var connection = Utils.makeConnection();
			connection.open('POST', '/logout', true);
			connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
			connection.onreadystatechange = function(){
				if ( connection.readyState > 3 ){
					if ( Utils.handleHTTPError(connection.status, false) === false ){
						return false;
					}
					try{
						var data = JSON.parse(connection.responseText);
						if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
							return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
						}
						return UI.alert.show('See you soon!', 'Good bye!', 'success', function(){
							window.location.href = '/';
						});
					}catch(ex){
						console.log(ex);
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
				}
			};
			connection.send();
		},
		
		/**
		* Sends an e-mail message to the user containing the link used for password recovery.
		*/
		restorePassword: function(){
			var email = document.getElementById('form-login-email').value;
			if ( email === '' || email.length > 256  ){
				return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
			}
			var connection = Utils.makeConnection();
			connection.open('POST', '/user/sendPasswordRestoreRequest', true);
			connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
			connection.onreadystatechange = function(){
				if ( connection.readyState > 3 ){
					if ( Utils.handleHTTPError(connection.status, false) === false ){
						return false;
					}
					try{
						var data = JSON.parse(connection.responseText);
						if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
							if ( typeof(data.code) === 'number' && ata.code === 1 ){
								return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
							}
							return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
						}
						return UI.alert.show('An e-mail with restore instructions has been sent to you.', 'Request sent!', 'success');
					}catch(ex){
						console.log(ex);
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
				}
			};
			connection.send('email=' + encodeURIComponent(email));
		}
	},
	
	/**
	* Lists all registered users.
	*/
	loadUsers: function(){
		var dataContainer = new UI.dataContainer();
		dataContainer.loader = document.getElementById('users-loader');
		dataContainer.error = document.getElementById('users-error');
		dataContainer.list = document.getElementById('users-list');
		var append = this.page === 1 ? false : true;
		if ( append === true && dataContainer.list.querySelector('li.users-list-element-loader') !== null ){
			var loader = dataContainer.list.querySelector('li.users-list-element-loader');
			var spinner = document.createElement('div');
			spinner.className = 'users-list-element-loader-spinner users-element-loader-spinner common-spinner';
			spinner.title = 'Loading users...';
			loader.innerHTML = '';
			loader.appendChild(spinner);
		}else{
			dataContainer.showLoader();
		}
		var connection = Utils.makeConnection();
		connection.open('GET', '/users/list?page=' + this.page.toString(), true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return dataContainer.showError();
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return dataContainer.showError();
					}
					var loader = dataContainer.list.querySelector('li.users-list-element-loader');
					if ( loader !== null ){
						loader.parentNode.removeChild(loader);
					}
					User.parseUsers(data.data, append);
					if ( append === true || data.data.length > 0 ){
						var loader = document.createElement('li');
						loader.className = 'users-list-element-loader users-element-loader';
						loader.innerHTML = '';
						if ( data.data.length === 12 ){
							var button = document.createElement('a');
							button.className = 'users-list-element-loader-link users-element-loader-link common-text';
							button.textContent = 'Load more users';
							button.title = 'Load more users';
							button.href = 'javascript:void(0);';
							button.onclick = User.loadMoreUsers;
							loader.appendChild(button);
						}else{
							if ( append === true ){
								var text = document.createElement('span');
								text.className = 'users-list-element-loader-text users-element-loader-text common-text';
								text.textContent = 'No more users';
								loader.appendChild(text);
							}
						}
						dataContainer.list.appendChild(loader);
					}
					dataContainer.showList();
				}catch(ex){
					console.log(ex);
					return dataContainer.showError();
				}
			}
		};
		connection.send();
	},
	
	/**
	* Loads next users page.
	*/
	loadMoreUsers: function(){
		var loader = document.getElementById('users-list').querySelector('li.users-list-element-loader');
		if ( loader !== null ){
			loader.innerHTML = '';
		}
		User.page++;
		User.loadUsers();
	},
	
	/**
	* Generates the HTML objects using the provided users.
	*
	* @param Array data A sequential array of objects where each object represents an user.
	* @param Boolean append If set to "true" new elements will be appended to the list, otherwise the list will be cleaned before adding items.
	*/
	parseUsers: function(data, append){
		var wrapper = document.getElementById('users-list');
		if ( append === false ){
			wrapper.innerHTML = '';
			if ( data.length === 0 ){
				var element = document.createElement('li');
				element.className = 'users-list-element-empty users-element-empty common-text';
				element.textContent = 'No user found.';
				wrapper.appendChild(element);
				return;
			}
		}
		for ( var i = 0 ; i < data.length ; i++ ){
			data[i].name = data[i].name === '' || data[i].surname === '' ? ( data[i].name + data[i].surname ) : ( data[i].name + ' ' + data[i].surname );
			var element = document.createElement('li');
			element.className = 'users-list-element users-element';
			element.setAttribute('uid', data[i].id.toString());
			var name = document.createElement('a');
			name.className = 'users-list-element-name users-element-name common-text';
			name.textContent = data[i].name;
			name.title = data[i].name;
			name.href = '/user/' + data[i].id.toString();
			element.appendChild(name);
			var email = document.createElement('p');
			email.className = 'users-list-element-email users-element-email common-text';
			email.textContent = data[i].email;
			element.appendChild(email);
			if ( data[i].admin === true ){
				var admin = document.createElement('div');
				admin.className = 'users-list-element-admin-badge users-element-admin-badge common-text';
				admin.textContent = 'Admin';
				element.appendChild(admin);
			}
			var date = document.createElement('p');
			date.className = 'users-list-element-date users-element-date common-text';
			date.textContent = 'Member since: ' + ( new Date(data[i].date * 1000).toLocaleDateString('en', {
				year: 'numeric',
				month: 'long',
				day: 'numeric'
			}) );
			element.appendChild(date);
			var button = document.createElement('button');
			button.className = 'users-list-element-button users-element-button common-text';
			button.style.marginLeft = '0px';
			button.title = 'Delete';
			button.textContent = 'Delete';
			button.onclick = User.deleteUser;
			element.appendChild(button);
			var button = document.createElement('button');
			button.className = 'users-list-element-button users-element-button common-text';
			button.title = 'Delete and remove contents';
			button.textContent = 'Delete and remove contents';
			button.onclick = User.deleteUserAndContents;
			element.appendChild(button);
			if ( data[i].me === false ){
				var button = document.createElement('button');
				button.className = 'users-list-element-button users-element-button common-text';
				button.setAttribute('role', 'rights');
				if ( data[i].admin === true ){
					button.title = 'Revert to normal user';
					button.textContent = 'Revert to normal user';
					button.onclick = User.dropAdminRights;
				}else{
					button.title = 'Promote to admin';
					button.textContent = 'Promote to admin';
					button.onclick = User.setAdminRights;
				}
				element.appendChild(button);
			}
			wrapper.appendChild(element);
		}
	},
	
	/**
	* Removes an user and deletes all its contents (MEMEs, comments, votes, views);
	*
	* @param Object event The event object.
	*/
	deleteUserAndContents: function(event){
		if ( event === null ){
			return User.deleteUser(document.getElementById('user-id').value, true);
		}
		User.deleteUser(event, true);
	},
	
	/**
	* Removes an user.
	*
	* @param Object event The event object.
	* @param Boolean dropContents If set to "true" all contents created by this user will be removed, otherwise all contents will be kept.
	*/
	deleteUser: function(event, dropContents){
		if ( event === null ){
			event = document.getElementById('user-id').value;
		}
		var url = '/users/' + ( typeof(event) === 'string' ? encodeURIComponent(event) : encodeURIComponent(event.target.parentNode.getAttribute('uid')) );
		if ( window.confirm('Do you really want to remove the user? Note that this action is irreversible.') === false ){
			return;
		}
		if ( dropContents === true ){
			url += '?dropContents=1';
		}
		var connection = Utils.makeConnection();
		connection.open('DELETE', url, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						if ( typeof(data.code) === 'number' && data.code === 1 ){
							return UI.alert.show('there must be at least one admin user.', 'Cannot remove this user.', 'error');
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					if ( typeof(event) === 'string' ){
						return UI.alert.show('This user is no longer registered to the website.', 'User removed.', 'success', function(){
							window.location.href = '/';
						});
					}
					var element = event.target.parentNode;
					var wrapper = document.getElementById('users-list');
					wrapper.removeChild(element);
					if ( wrapper.querySelector('li.users-list-element') === null ){
						if ( wrapper.querySelector('li.users-list-element-loader-link') !== null ){
							return User.loadMoreUsers();
						}
						wrapper.innerHTML = '';
						var element = document.createElement('li');
						element.className = 'users-list-element-empty dashboard-element-empty common-text';
						element.textContent = 'No user found.';
						wrapper.appendChild(element);
					}
					return UI.alert.show('This user is no longer registered to the website.', 'User removed.', 'success');
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send();
	},
	
	/**
	* Drops admin rights for an user.
	*
	* @param Object event The event object.
	*/
	dropAdminRights: function(event){
		if ( window.confirm('Do you really want to revert this user to a normal user? Note that all memes created by this user will be removed.') === true ){
			User.setAdminRights(event, false);
		}
	},
	
	/**
	* Sets admin rights to an user.
	*
	* @param Object event The event object.
	* @param value If set to "true" the user will be promoted to admin, otherwise admin rights will be dropped for the user.
	*/
	setAdminRights: function(event, value){
		if ( value !== false && window.confirm('Do you really want to promote this user to admin?') === false ){
			return;
		}
		var url = '/users/' + encodeURIComponent(event.target.parentNode.getAttribute('uid')) + '/setAdminRights?value=' + ( value === false ? '0' : '1' );
		var connection = Utils.makeConnection();
		connection.open('GET', url, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					var element = event.target.parentNode;
					var button = element.querySelector('.users-list-element-button[role="rights"]');
					if ( value === false ){
						var badge = element.querySelector('.users-list-element-admin-badge');
						if ( badge !== null ){
							badge.parentNode.removeChild(badge);
						}
						button.title = 'Promote to admin';
						button.textContent = 'Promote to admin';
						button.onclick = User.setAdminRights;
					}else{
						var sibling = element.querySelector('.users-list-element-email');
						var badge = document.createElement('div');
						badge.className = 'users-list-element-admin-badge users-element-admin-badge common-text';
						badge.textContent = 'Admin';
						sibling.parentNode.insertBefore(badge, sibling.nextSibling);
						button.title = 'Revert to normal user';
						button.textContent = 'Revert to normal user';
						button.onclick = User.dropAdminRights;
					}
					return UI.alert.show(( value === false ? 'This user is now a regular user.' : 'This user is now an admin.' ), 'User rights changed successfully!', 'success');
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send();
	},
	
	/**
	* Checks if an admin user is currently authenticated.
	*
	* @return Boolean If an admin user is currently authenticated will be returned "true", otherwise "false".
	*/
	adminLoggedIn: function(){
		var value = document.getElementById('auth-admin');
		return value === null || value.value !== '1' ? false : true;
	},
	
	/**
	* Discards changes for an user in edit mode.
	*
	* @param Object event The event object.
	*/
	revertChanges: function(event){
		event.preventDefault();
		event.stopPropagation();
		document.getElementById('user-edit-input-name').value = document.getElementById('user-edit-input-name-old').value;
		document.getElementById('user-edit-input-surname').value = document.getElementById('user-edit-input-surname-old').value;
		document.getElementById('user-edit-input-email').value = document.getElementById('user-edit-input-email-old').value;
	},
	
	/**
	* Saves changes for an user in edit mode.
	*
	* @param Object event The event object.
	*/
	saveChanges: function(event){
		event.preventDefault();
		event.stopPropagation();
		var name = document.getElementById('user-edit-input-name').value;
		if ( name === '' || name.length > 30 ){
			return UI.alert.show('You must provide a name, max length is 30 chars.', 'Invalid name.', 'error');
		}
		var surname = document.getElementById('user-edit-input-surname').value;
		if ( surname === '' || surname.length > 30 ){
			return UI.alert.show('You must provide a surname, max length is 30 chars.', 'Invalid surname.', 'error');
		}
		var email = document.getElementById('user-edit-input-email').value;
		if ( email === '' || email.length > 256 ){
			return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
		}
		var connection = Utils.makeConnection();
		connection.open('PATCH', document.getElementById('user-password-change').action, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						switch ( ( typeof(data.code) !== 'number' ? null : data.code ) ){
							case 1:
							case 2:{
								return UI.alert.show('You must provide a name, max length is 30 chars.', 'Invalid name.', 'error');
							}break;
							case 3:
							case 4:{
								return UI.alert.show('You must provide a surname, max length is 30 chars.', 'Invalid surname.', 'error');
							}break;
							case 5:{
								return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
							}break;
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					document.getElementById('user-edit-input-name').value = name;
					document.getElementById('user-edit-input-name-old').value = name;
					document.getElementById('user-edit-input-surname').value = surname;
					document.getElementById('user-edit-input-surname-old').value = surname;
					document.getElementById('user-edit-input-email').value = email;
					document.getElementById('user-edit-input-email-old').value = email;
					return UI.alert.show('All changes have been saved.', 'Changes saved.', 'success');
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send('name=' + encodeURIComponent(name) + '&surname=' + encodeURIComponent(surname) + '&email=' + encodeURIComponent(email) + '&mode=1');
	},
	
	/**
	* Changes user password.
	*
	* @param Object event The event object.
	*/
	changePassword: function(event){
		event.preventDefault();
		event.stopPropagation();
		var old = document.getElementById('user-edit-input-password-old').value;
		if ( old === '' || old.length > 30 ){
			return UI.alert.show('You must provide a password, max length is 30 chars.', 'Current password is not valid.', 'error');
		}
		var password = document.getElementById('user-edit-input-password').value;
		if ( password === '' || password.length > 30 ){
			return UI.alert.show('You must provide a password, max length is 30 chars.', 'New password is not valid.', 'error');
		}
		var confirm = document.getElementById('user-edit-input-password-confirm').value;
		if ( password !== confirm ){
			return UI.alert.show('Please double check the inserted passwords.', 'The inserted passwords don\'t match.', 'error');
		}
		var connection = Utils.makeConnection();
		connection.open('PATCH', document.getElementById('user-password-change').action, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						switch ( ( typeof(data.code) !== 'number' ? null : data.code ) ){
							case 8:
							case 9:{
								return UI.alert.show('You must provide a password, max length is 30 chars.', 'Current password is not valid.', 'error');
							}break;
							case 10:{
								return UI.alert.show('You must provide a password, max length is 30 chars.', 'New password is not valid.', 'error');
							}break;
							case 12:{
								return UI.alert.show('If you forgot your password, restore it in the login.', 'Current password is not correct.', 'error');
							}break;
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					document.getElementById('user-edit-input-password-old').value = '';
					document.getElementById('user-edit-input-password').value = '';
					document.getElementById('user-edit-input-password-confirm').value = '';
					alert('The password has been updated.');
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send('old_password=' + encodeURIComponent(old) + '&password=' + encodeURIComponent(password) + '&mode=2');
	},
	
	/**
	* Changes user password using a token sent by e-mail through a password recovery request.
	*
	* @param Object event The event object.
	*/
	restorePassword: function(event){
		event.preventDefault();
		event.stopPropagation();
		var password = document.getElementById('password-restore-password').value;
		if ( password === '' || password.length > 30 ){
			return UI.alert.show('You must provide a password, max length is 30 chars.', 'Invalid password.', 'error');
		}
		if ( document.getElementById('password-restore-confirm').value !== password ){
			return UI.alert.show('Please double check the inserted passwords.', 'The inserted passwords don\'t match.', 'error');
		}
		var token = document.getElementById('password-restore-token').value;
		var email = document.getElementById('password-restore-email').value;
		var connection = Utils.makeConnection();
		connection.open('PATCH', document.getElementById('password-restore').action, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, false) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						switch ( typeof(data.code) === 'number' ? data.code : null ){
							case 4:
							case 5:{
								return UI.alert.show('You must provide a password, max length is 30 chars.', 'Invalid password.', 'error');
							}break;
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					return UI.alert.show('Your password has been updated.', 'Passowrd changed!', 'success', function(){
						window.location.href = '/';
					});
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send('password=' + encodeURIComponent(password) + '&token=' + encodeURIComponent(token) + '&email=' + encodeURIComponent(email));
	}
};

var MEME = {
	/**
	* @var Number page An integer number greater than zero containing the page number used in MEMEs listing.
	*/
	page: 1,
	
	/**
	* @var Object file An object representing the file that will be uploaded in MEME creation.
	*/
	file: null,
	
	/**
	* @var String mode A string representing the mode used in MEME listing.
	*/
	mode: null,
	
	/**
	* @var String ordering A string representing the ordering for MEME listing, note that currently ordering is supported only in default mode (dashboard).
	*/
	ordering: 'popular',
	
	/**
	* Lists MEMEs.
	*
	* @var String ordering A string representing the listing mode.
	*/
	getMEMEs: function(mode){
		if ( typeof(mode) !== 'string' ){
			mode = MEME.mode;
		}
		MEME.mode = mode;
		if ( window.location.hash === '#new' ){
			MEME.ordering = 'new';
		}
		switch ( mode ){
			case 'search':{
				document.getElementById('dashboard-search-stats').style.display = 'none';
				var q = document.getElementById('dashboard-search-input').value;
				if ( q === '' ){
					q = Utils.getParam('q');
					if ( q === null || q === '' ){
						return;
					}
				}
				var url = '/memes?page=' + this.page.toString() + '&q=' + encodeURIComponent(q) + '&mode=search&ordering=' + encodeURIComponent(MEME.ordering);
			}break;
			case 'author':{
				var url = '/memes?page=' + this.page.toString() + '&author=' + encodeURIComponent(document.getElementById('dashboard-author').value) + '&mode=author&ordering=' + encodeURIComponent(MEME.ordering);
			}break;
			case 'category':{
				var url = '/memes?page=' + this.page.toString() + '&category=' + encodeURIComponent(document.getElementById('dashboard-category').value) + '&mode=category&ordering=' + encodeURIComponent(MEME.ordering);
			}break;
			case 'categories':{
				var url = '/categories/list?page=' + this.page.toString();
			}break;
			default:{
				var url = '/memes?page=' + this.page.toString() + '&mode=dashboard&ordering=' + encodeURIComponent(MEME.ordering);
			}break;
		}
		var dataContainer = new UI.dataContainer();
		dataContainer.loader = document.getElementById('dashboard-loader');
		dataContainer.error = document.getElementById('dashboard-error');
		dataContainer.list = document.getElementById('dashboard-list');
		var append = this.page === 1 ? false : true;
		if ( append === true && dataContainer.list.querySelector('li.dashboard-list-element-loader') !== null ){
			var loader = dataContainer.list.querySelector('li.dashboard-list-element-loader');
			var spinner = document.createElement('div');
			spinner.className = 'dashboard-list-element-loader-spinner dashboard-element-loader-spinner common-spinner';
			spinner.title = mode === 'categories' ? 'Loading categories...' : 'Loading memes...';
			loader.innerHTML = '';
			loader.appendChild(spinner);
		}else{
			dataContainer.showLoader();
		}
		var connection = Utils.makeConnection();
		connection.open('GET', url, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, false) === false ){
					return dataContainer.showError();
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return dataContainer.showError();
					}
					var loader = dataContainer.list.querySelector('li.dashboard-list-element-loader');
					if ( loader !== null ){
						loader.parentNode.removeChild(loader);
					}
					if ( mode === 'search' ){
						var stats = document.getElementById('dashboard-search-stats');
						stats.textContent = 'Found ' + Utils.stringifyCounterValue(data.data.count) + ' elements in ' + ( Math.round(data.data.time * 100) / 100 ).toString();
						stats.style.display = 'block';
						MEME.parseMEMEs(data.data.elements, append, false);
					}else{
						MEME.parseMEMEs(data.data, append, ( mode === 'categories' ? true : false ));
					}
					if ( append === true || data.data.length > 0 ){
						var loader = document.createElement('li');
						loader.className = 'dashboard-list-element-loader dashboard-element-loader';
						loader.innerHTML = '';
						if ( data.data.length === 12 ){
							var button = document.createElement('a');
							button.className = 'dashboard-list-element-loader-link dashboard-element-loader-link common-text';
							button.textContent = mode === 'categories' ? 'Load more categories' : 'Load more memes';
							button.title = mode === 'categories' ? 'Load more categories' : 'Load more memes';
							button.href = 'javascript:void(0);';
							button.onclick = MEME.loadMoreMEMEs;
							loader.appendChild(button);
						}else{
							if ( append === true ){
								var text = document.createElement('span');
								text.className = 'dashboard-list-element-loader-text dashboard-element-loader-text common-text';
								text.textContent = mode === 'categories' ? 'No more categories' : 'No more memes';
								loader.appendChild(text);
							}
						}
						dataContainer.list.appendChild(loader);
					}
					if ( mode === 'category' && append === false && data.data.length === 0 ){
						var button = document.createElement('button');
						button.className = 'dashboard-list-element-category-remove dashboard-category-remove common-text';
						button.title = 'Remove this category';
						button.textContent = 'Remove this category';
						button.onclick = MEME.removeCategory;
						dataContainer.list.appendChild(button);
					}
					return dataContainer.showList(true);
				}catch(ex){
					console.log(ex);
					return dataContainer.showError();
				}
			}
		};
		connection.send();
	},
	
	/**
	* Loads next MEMEs page.
	*/
	loadMoreMEMEs: function(){
		var loader = document.getElementById('dashboard-list').querySelector('li.dashboard-list-element-loader');
		if ( loader !== null ){
			loader.innerHTML = '';
		}
		MEME.page++;
		MEME.getMEMEs();
	},
	
	/**
	* Sets the MEMEs' ordering.
	*
	* @param Object event The event object, alternatively, a string containing the ordering name.
	*/
	setOrdering: function(event){
		var ordering = typeof(event) === 'string' ? event : event.target.getAttribute('ord');
		if ( ( ordering !== 'new' && ordering !== 'popular' ) || ordering === MEME.ordering ){
			return;
		}
		if ( ordering === 'new' ){
			document.querySelector('.dashboard-ordering-list-element-link[ord="new"]').setAttribute('selected', 'true');
			document.querySelector('.dashboard-ordering-list-element-link[ord="popular"]').setAttribute('selected', 'false');
			window.location.hash = '#new';
		}else{
			document.querySelector('.dashboard-ordering-list-element-link[ord="popular"]').setAttribute('selected', 'true');
			document.querySelector('.dashboard-ordering-list-element-link[ord="new"]').setAttribute('selected', 'false');
			window.location.hash = '#popular';
		}
		MEME.ordering = ordering;
		MEME.page = 1;
		MEME.getMEMEs();
		document.getElementById('dashboard-list').innerHTML = '';
	},
	
	/**
	* Searches for MEMEs resetting current list.
	*
	* @param Object event The event object.
	*/
	search: function(event){
		if ( typeof(event) === 'object' ){
			event.preventDefault();
			event.stopPropagation();
		}
		MEME.page = 1;
		MEME.getMEMEs('search');
	},
	
	/**
	* Generates the HTML objects using the provided MEMEs.
	*
	* @param Array data A sequential array of objects where each object represents a MEME.
	* @param Boolean append If set to "true" new elements will be appended to the list, otherwise the list will be cleaned before adding items.
	* @param Boolean categories If set to "true" it means that the given array contains categories instead of MEMEs, otherwise it contains MEMEs.
	*/
	parseMEMEs: function(data, append, categories){
		var wrapper = document.getElementById('dashboard-list');
		if ( append === false ){
			wrapper.innerHTML = '';
			if ( data.length === 0 ){
				var element = document.createElement('li');
				element.className = 'dashboard-list-element-empty dashboard-element-empty common-text';
				element.textContent = categories === true ? 'No category found.' : 'No meme found.';
				wrapper.appendChild(element);
				return;
			}
		}
		for ( var i = 0 ; i < data.length ; i++ ){
			var element = document.createElement('li');
			element.className = 'dashboard-list-element dashboard-element';
			element.setAttribute('mid', ( categories === true ? data[i].meme.id.toString() : data[i].id.toString() ));
			var container = document.createElement('div');
			container.className = 'dashboard-list-element-container dashboard-element-container';
			if ( categories !== true && typeof(data[i].mine) !== 'undefined' && data[i].mine === true ){
				var remove = document.createElement('div');
				remove.className = 'dashboard-list-element-container-remove dashboard-element-remove';
				remove.setAttribute('scope', 'remove');
				remove.title = 'Remove this meme.';
				remove.onclick = MEME.remove;
				container.appendChild(remove);
			}
			var memeTitle = categories === true ? data[i].meme.title : data[i].title;
			var link = document.createElement('a');
			link.className = 'dashboard-list-element-container-link-null';
			link.title = memeTitle;
			link.href = categories === true ? ( '/category/' + encodeURIComponent(data[i].name) ) : ( '/memes/' + encodeURIComponent(data[i].id) );
			var path = categories === true ? data[i].meme.path : data[i].path;
			if ( path.indexOf('http') !== 0 ){
				path = '/' + path;
			}
			if ( ( categories === true ? data[i].meme.type : data[i].type ) === 3 ){
				var video = document.createElement('div');
				video.className = 'dashboard-list-element-container-video dashboard-element-video';
				video.setAttribute('ratio', ( categories === true ? data[i].meme.ratio : data[i].ratio ));
				var player = document.createElement('video');
				player.className = 'dashboard-list-element-container-video-player';
				player.src = path;
				player.controls = 'true';
				video.appendChild(player);
				link.appendChild(video);
			}else{
				var image = document.createElement('div');
				image.className = 'dashboard-list-element-container-image dashboard-element-image';
				image.setAttribute('ratio', ( categories === true ? data[i].meme.ratio : data[i].ratio ));
				image.style.backgroundImage = 'url(' + path + ')';
				link.appendChild(image);
			}
			container.appendChild(link);
			if ( categories === true ){
				element.setAttribute('cid', data[i].id.toString());
				var name = document.createElement('a');
				name.className = 'dashboard-list-element-container-link dashboard-element-link common-text';
				name.title = data[i].name;
				name.textContent = data[i].name;
				name.href = '/category/' + encodeURIComponent(data[i].name);
				container.appendChild(name);
				var counter = document.createElement('p');
				counter.className = 'dashboard-list-element-container-counter dashboard-element-counter common-text';
				counter.textContent = Utils.stringifyCounterValue(data[i].count) + ' MEMEs in this category.';
				container.appendChild(counter);
				element.appendChild(container);
				wrapper.appendChild(element);
				continue;
			}
			var info = document.createElement('div');
			info.className = 'dashboard-list-element-container-info dashboard-element-info';
			var date = document.createElement('p');
			date.className = 'dashboard-list-element-container-info-date dashboard-element-date common-text';
			date.textContent = new Date(data[i].date * 1000).toLocaleDateString('en', {
				year: 'numeric',
				month: 'long',
				day: 'numeric'
			});
			info.appendChild(date);
			var title = document.createElement('a');
			title.className = 'dashboard-list-element-container-info-title dashboard-element-title common-text';
			title.textContent = memeTitle;
			title.title = memeTitle;
			title.href = categories === true ? ( '/category/' + encodeURIComponent(data[i].name) ) : ( '/memes/' + encodeURIComponent(data[i].id) );
			info.appendChild(title);
			if ( categories === false && data[i].user !== null ){
				var user = document.createElement('a');
				user.className = 'dashboard-list-element-container-info-user dashboard-element-user common-text';
				user.href = '/author/' + encodeURIComponent(data[i].user.id);
				user.title = 'Check more memes from this user.';
				user.textContent = data[i].user.name;
				info.appendChild(user);
			}
			if ( categories === false ){
				var list = document.createElement('ul');
				list.className = 'dashboard-list-element-container-info-categories dashboard-element-categories';
				for ( var n = 0 ; n < data[i].categories.length ; n++ ){
					var category = document.createElement('li');
					category.className = 'dashboard-list-element-container-info-categories-element dashboard-element-category';
					var link = document.createElement('a');
					link.className = 'dashboard-list-element-container-info-categories-element-link dashboard-element-category-name common-text';
					link.textContent = data[i].categories[n];
					link.href = '/category/' + encodeURIComponent(data[i].categories[n]);
					link.title = 'More meme from this category.';
					category.appendChild(link);
					list.appendChild(category);
				}
				info.appendChild(list);
				var counters = document.createElement('div');
				counters.className = 'dashboard-element-counters';
				var icon = document.createElement('div');
				icon.className = 'dashboard-list-element-container-info-icon dashboard-element-icon';
				icon.setAttribute('scope', 'upVote');
				icon.onclick = Vote.toggleUpVote;
				if ( data[i].vote.positive === true ){
					icon.setAttribute('selected', 'true');
				}
				counters.appendChild(icon);
				var counter = document.createElement('span');
				counter.className = 'dashboard-list-element-container-info-counter dashboard-element-counter common-text';
				counter.textContent = Utils.stringifyCounterValue(data[i].counters.upVotes);
				counter.setAttribute('scope', 'upVote');
				counter.setAttribute('counter', data[i].counters.upVotes.toString());
				counters.appendChild(counter);
				var icon = document.createElement('div');
				icon.className = 'dashboard-list-element-container-info-icon dashboard-element-icon';
				icon.setAttribute('scope', 'downVote');
				icon.onclick = Vote.toggleDownVote;
				if ( data[i].vote.negative === true ){
					icon.setAttribute('selected', 'true');
				}
				counters.appendChild(icon);
				var counter = document.createElement('span');
				counter.className = 'dashboard-list-element-container-info-counter dashboard-element-counter common-text';
				counter.textContent = Utils.stringifyCounterValue(data[i].counters.downVotes);
				counter.setAttribute('scope', 'downVote');
				counter.setAttribute('counter', data[i].counters.downVotes.toString());
				counters.appendChild(counter);
				var icon = document.createElement('div');
				icon.className = 'dashboard-list-element-container-info-icon dashboard-element-icon';
				icon.setAttribute('scope', 'comment');
				counters.appendChild(icon);
				var counter = document.createElement('span');
				counter.className = 'dashboard-list-element-container-info-counter dashboard-element-counter common-text';
				counter.textContent = Utils.stringifyCounterValue(data[i].counters.comments);
				counter.setAttribute('counter', data[i].counters.comments.toString());
				counters.appendChild(counter);
				info.appendChild(counters);
			}
			container.appendChild(info);
			element.appendChild(container);
			wrapper.appendChild(element);
		}
	},
	
	/**
	* Loads trend categories.
	*/
	loadTrends: function(){
		var dataContainer = new UI.dataContainer();
		dataContainer.loader = document.getElementById('dashboard-trends-loader');
		dataContainer.error = document.getElementById('dashboard-trends-error');
		dataContainer.list = document.getElementById('dashboard-trends-list');
		dataContainer.showLoader();
		var connection = Utils.makeConnection();
		connection.open('GET', '/trends', true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, false) === false ){
					return dataContainer.showError();
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return dataContainer.showError();
					}
					if ( data.data.length === 0 ){
						var element = document.createElement('li');
						element.className = 'dashboard-trends-list-element-empty dashboard-trends-element-empty common-text';
						element.textContent = 'No trend found.';
						dataContainer.list.appendChild(element);
						return dataContainer.showList();
					}
					for ( var i = 0 ; i < data.data.length ; i++ ){
						var element = document.createElement('li');
						element.className = 'dashboard-trends-list-element dashboard-trends-element';
						var link = document.createElement('a');
						link.className = 'dashboard-trends-list-element-link dashboard-trends-element-link common-text';
						link.textContent = data.data[i].name + ' (' + Utils.stringifyCounterValue(data.data[i].count) + ')';
						link.title = data.data[i].name;
						link.href = '/category/' + encodeURIComponent(data.data[i].name);
						element.appendChild(link);
						dataContainer.list.appendChild(element);
					}
					return dataContainer.showList();
				}catch(ex){
					console.log(ex);
					return dataContainer.showError();
				}
			}
		};
		connection.send();
	},
	
	/**
	* Redirects the client to the categories' page.
	*/
	showCategories: function(){
		window.location.href = '/categories';
	},
	
	/**
	* Handles drag over event in the file upload in MEME creation.
	*
	* @param Object event The event object.
	*/
	dragOverUploader: function(event){
		event.preventDefault();
		event.stopPropagation();
		if ( MEME.file !== null ){
			return;
		}
		document.getElementById('create-uploader').setAttribute('drag', 'true');
	},
	
	/**
	* Handles drag leave event in the file upload in MEME creation.
	*
	* @param Object event The event object.
	*/
	dragLeaveUploader: function(event){
		event.preventDefault();
		event.stopPropagation();
		if ( MEME.file !== null ){
			return;
		}
		document.getElementById('create-uploader').setAttribute('drag', 'false');
	},
	
	/**
	* Sets the file that will be uploaded in MEME creation.
	*
	* @param Object event The event object.
	*/
	setUploaderFile: function(event){
		event.preventDefault();
		event.stopPropagation();
		if ( MEME.file !== null ){
			return;
		}
		if ( event.type === 'drop' ){
			var dataTransfer = typeof(event.dataTransfer) !== 'undefined' ? event.dataTransfer : ( typeof(event.originalEvent.dataTransfer) === 'undefined' ? null : event.originalEvent.dataTransfer );
			if ( dataTransfer === null || typeof(dataTransfer.files) === 'undefined' || dataTransfer.files.length === 0 ){
				return;
			}
			var file = dataTransfer.files[0];
			var type = file.type.toLowerCase();
		}else{
			var files = document.getElementById('create-uploader-input').files;
			if ( files.length === 0 ){
				return;
			}
			var type = files[0].type.toLowerCase();
			var file = files[0];
		}
		if ( ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'video/mp4'].indexOf(type) < 0 ){
			document.getElementById('create-uploader-input').value = '';
			return;
		}
		MEME.file = file;
		document.getElementById('create-uploader').setAttribute('drag', 'completed');
		document.getElementById('create-uploader-inner-content-wrapper-picker').style.display = 'none';
		document.getElementById('create-uploader-inner-content-wrapper-preview').style.display = 'table-cell';
		if ( type === 'video/mp4' ){
			document.getElementById('create-uploader-inner-content-wrapper-preview-video').style.display = 'none';
			document.getElementById('create-uploader-inner-content-wrapper-preview-loader').style.display = 'block';
			var player = document.getElementById('create-uploader-inner-content-wrapper-preview-video-player');
			player.src = window.URL.createObjectURL(MEME.file);
			player.load();
		}else{
			var preview = document.getElementById('create-uploader-inner-content-wrapper-preview-image');
			preview.style.backgroundImage = 'url(' + window.URL.createObjectURL(file) + ')';
			preview.style.display = 'block';
		}
	},
	
	/**
	* Processes the video meta tags in order to center and fit the video into the view.
	*
	* @param Boolean length If set to "false" the check on video duration will be skipped, otherwise it will be made.
	* @param String ratio A string containing the selected aspect-ratio, if not set, the value will be read from the HTML select.
	*/
	processVideoPreview: function(length, ratio){
		document.getElementById('create-uploader-inner-content-wrapper-preview-loader').style.display = 'none';
		document.getElementById('create-uploader-inner-content-wrapper-preview-image').style.display = 'none';
		document.getElementById('create-uploader-inner-content-wrapper-preview-video').style.display = 'block';
		var player = document.getElementById('create-uploader-inner-content-wrapper-preview-video-player');
		if ( length !== false ){
			var duration = parseInt(document.getElementById('video-max-length').value);
			if ( duration <= 0 || isNaN(duration) === true ){
				return MEME.removeFileFromUploader();
			}
			if ( player.duration > duration ){
				alert('The length of your video is too much.');
				return MEME.removeFileFromUploader();
			}
		}
		if ( typeof(ratio) !== 'string' ){
			ratio = document.getElementById('create-container-section-form-ratio').value;
		}
		ratio = ratio.split(':');
		ratio[0] = parseInt(ratio[0]);
		ratio[1] = parseInt(ratio[1]);
		player.style.height = 'auto';
		player.style.width = 'auto';
		player.style.top = '0px';
		player.style.left = '0px';
		if ( ( player.videoWidth / player.videoHeight ) > ( ratio[0] / ratio[1] ) ){
			player.style.height = '100%';
			player.style.width = 'auto';
			window.setTimeout(function(){
				var width = parseFloat(window.getComputedStyle(document.getElementById('create-uploader-inner-content-wrapper-preview-video'))['width']);
				var size = parseFloat(window.getComputedStyle(player)['width']);
				var margin = ( size - width ) / 2;
				player.style.left = '-' + margin.toString() + 'px';
			}, 25);
		}else{
			player.style.width = '100%';
			player.style.height = 'auto';
			window.setTimeout(function(){
				var height = parseFloat(window.getComputedStyle(document.getElementById('create-uploader-inner-content-wrapper-preview-video'))['height']);
				var size = parseFloat(window.getComputedStyle(player)['height']);
				var margin = ( size - height ) / 2;
				player.style.top = '-' + margin.toString() + 'px';
			}, 25);
		}
	},
	
	/**
	* Plays the video set by the upploader.
	*
	* @param Boolean rewind If set to "true" the video will be rewinded before playing, otherwise it will resume from the time it was paused.
	*/
	playVideoPreview: function(rewind){
		var player = document.getElementById('create-uploader-inner-content-wrapper-preview-video-player');
		if ( rewind === true || player.ended === true ){
			player.currentTime = 0;
			return player.play();
		}
		if ( player.paused === true ){
			return player.play();
		}
		player.pause();
	},
	
	/**
	* Triggers the click on the input in order to make the browser open the file picker.
	*/
	openFilePicker: function(){
		if ( MEME.file !== null ){
			return;
		}
		document.getElementById('create-uploader-input').click();
	},
	
	/**
	* Removes the file that was selected and clear file preview.
	*/
	removeFileFromUploader: function(){
		MEME.file = null;
		document.getElementById('create-uploader-input').value = '';
		document.getElementById('create-uploader-inner-content-wrapper-preview').style.display = 'none';
		document.getElementById('create-uploader-inner-content-wrapper-picker').style.display = 'table-cell';
		var preview = document.getElementById('create-uploader-inner-content-wrapper-preview-image');
		preview.style.backgroundImage = '';
		preview.style.display = 'none';
		document.getElementById('create-uploader-inner-content-wrapper-preview-video').style.display = 'none';
		var player = document.getElementById('create-uploader-inner-content-wrapper-preview-video-player');
		try{
			player.pause();
			player.src = '';
			player.load();
		}catch(ex){}
		player.style.left = '0px';
		player.style.top = '0px';
		document.getElementById('create-uploader-inner-content-wrapper-preview-loader').style.display = 'none';
		document.getElementById('create-uploader').setAttribute('drag', 'false');
	},
	
	/**
	* Applies the selected aspect-ratio on the file preview.
	*/
	setRatio: function(){
		var ratio = document.getElementById('create-container-section-form-ratio').value;
		document.getElementById('create-uploader-inner-content-wrapper-preview-image').setAttribute('ratio', ratio);
		document.getElementById('create-uploader-inner-content-wrapper-preview-video').setAttribute('ratio', ratio);
		if ( MEME.file !== null && MEME.file.type.toLowerCase() === 'video/mp4' ){
			MEME.processVideoPreview(false, ratio);
		}
	},
	
	/**
	* Adds a new category to the category list and select it.
	*
	* @param Object event The event object.
	*/
	addCategory: function(event){
		if ( event.keyCode === 13 ){
			event.preventDefault();
			event.stopPropagation();
			if ( event.target.value !== '' && event.target.value.length <= 16 ){
				var wrapper = document.getElementById('create-container-section-form-category');
				var elements = wrapper.querySelectorAll('option');
				var value = event.target.value.trim();
				for ( var i = 0 ; i < elements.length ; i++ ){
					if ( elements.item(i).textContent === value ){
						elements.item(i).selected = true;
						event.target.value = '';
						return;
					}
				}
				var element = document.createElement('option');
				element.className = 'common-text';
				element.value = '';
				element.textContent = value;
				element.selected = true;
				wrapper.appendChild(element);
				event.target.value = '';
				wrapper.scrollTop = wrapper.scrollHeight;
			}
		}
	},
	
	/**
	* Calls the method used to create a new MEME.
	*
	* @param Object event The event object.
	*/
	triggerCreation: function(event){
		event.preventDefault();
		event.stopPropagation();
		MEME.create();
	},
	
	/**
	* Creates a new MEME using the data set within the creation form.
	*/
	create: function(){
		if ( MEME.file === null ){
			return UI.alert.show('A MEME must contain a file (an image, GIF or a video).', 'You must provide a file.', 'error');
		}
		var form = new FormData();
		var title = document.getElementById('create-container-section-form-title').value;
		if ( title === '' || title.length > 30 ){
			return UI.alert.show('You must provide a title, max length is 30 chars.', 'Invalid title.', 'error');
		}
		form.append('title', title);
		var categories = document.getElementById('create-container-section-form-category').querySelectorAll('option');
		var count = 0;
		for ( var i = 0 ; i < categories.length ; i++ ){
			if ( categories.item(i).selected === true ){
				if ( categories.item(i).value === '' ){
					form.append('new_category[]', categories.item(i).textContent);
				}else{
					form.append('category[]', categories.item(i).value);
				}
				count++;
			}
		}
		if ( count === 0 ){
			return UI.alert.show('You must select at least one category.', 'Invalid categories.', 'error');
		}
		if ( count > 3 ){
			return UI.alert.show('You can select up to 3 categories.', 'Invalid categories.', 'error');
		}
		var text = document.getElementById('create-container-section-form-text').value;
		if ( text.length > 1000 ){
			return UI.alert.show('The given text is too long, max length is 1000 chars.', 'Invalid text.', 'error');
		}
		form.append('text', text);
		var ratio = document.getElementById('create-container-section-form-ratio').value;
		form.append('ratio', ( ratio !== '4:3' && ratio !== '16:9' && ratio !== '16:10' ? '1:1' : ratio ));
		form.append('file', MEME.file);
		document.getElementById('create-loader-action-processing').style.display = 'none';
		document.getElementById('create-loader-action-uploading').style.display = 'block';
		document.getElementById('create-loader-progress').setAttribute('status', 'uploading');
		document.getElementById('create-loader-progress-value').style.width = '0%';
		document.getElementById('create-container').style.display = 'none';
		document.getElementById('create-loader').style.display = 'block';
		var connection = Utils.makeConnection();
		connection.upload.onprogress = function(event){
			if ( event.lengthComputable === true ){
				var percentage = ( event.loaded / event.total ) * 100;
				document.getElementById('create-loader-progress-value').style.width = percentage.toString() + '%';
				if ( event.loaded === event.total ){
					document.getElementById('create-loader-action-uploading').style.display = 'none';
					document.getElementById('create-loader-action-processing').style.display = 'block';
					document.getElementById('create-loader-progress').setAttribute('status', 'processing');
				}
			}
		};
		connection.open('POST', document.getElementById('create-container-section-form').action, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				MEME.hideCreationLoader();
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						switch ( typeof(data.code) !== 'number' ? null : data.code ){
							case 1:{
								return UI.alert.show('A MEME must contain a file (an image, GIF or a video).', 'You must provide a file.', 'error');
							}break;
							case 2:{
								return UI.alert.show('File can be an image (jpg or png), a GIF or a mp4 video.', 'Unsupported file type.', 'error');
							}break;
							case 3:
							case 4:{
								return UI.alert.show('You must provide a title, max length is 30 chars.', 'Invalid title.', 'error');
							}break;
							case 5:{
								return UI.alert.show('You must select at least one category.', 'Invalid categories.', 'error');
							}break;
							case 6:{
								return UI.alert.show('You can select up to 3 categories.', 'Invalid categories.', 'error');
							}break;
							case 8:{
								return UI.alert.show('The given text is too long, max length is 1000 chars.', 'Invalid text.', 'error');
							}break;
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					var url = typeof(data.data.id) === 'number' ? ( '/memes/' + data.data.id.toString() ) : '/';
					return UI.alert.show('Your MEME has successfully been created!', 'MEME created!', 'success', function(){
						window.location.href = url;
					});
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send(form);
	},
	
	/**
	* Hides the HTML element that contains the loader used during MEME creation.
	*/
	hideCreationLoader: function(){
		document.getElementById('create-loader').style.display = 'none';
		document.getElementById('create-container').style.display = 'block';
		document.getElementById('create-loader-progress').setAttribute('status', 'none');
		document.getElementById('create-loader-progress-value').style.width = '0%';
		document.getElementById('create-loader-action-uploading').style.display = 'none';
		document.getElementById('create-loader-action-processing').style.display = 'none';
	},
	
	/**
	* Removes a MEME.
	*
	* @param Object event The event object.
	*/
	remove: function(event){
		if ( event === null ){
			var id = document.getElementById('meme-id').value;
		}else{
			var id = event.target.parentNode.parentNode.getAttribute('mid');
			if ( id === null ){
				return;
			}
		}
		if ( window.confirm('Do you really want to remove this MEME?') === true ){
			var connection = Utils.makeConnection();
			connection.open('DELETE', '/memes/' + encodeURIComponent(id), true);
			connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
			connection.onreadystatechange = function(){
				if ( connection.readyState > 3 ){
					if ( Utils.handleHTTPError(connection.status, true, true) === false ){
						return false;
					}
					try{
						var data = JSON.parse(connection.responseText);
						if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
							return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
						}
						if ( event === null ){
							return UI.alert.show('This MEME has successfully been removed.', 'MEME removed.', 'success', function(){
								window.location.href = '/';
							});
						}
						var element = event.target.parentNode.parentNode;
						var wrapper = element.parentNode;
						wrapper.removeChild(element);
						if ( wrapper.querySelector('li.dashboard-list-element') === null ){
							if ( wrapper.querySelector('li.dashboard-list-element-loader-link') !== null ){
								return MEME.loadMoreMEMEs();
							}
							wrapper.innerHTML = '';
							var element = document.createElement('li');
							element.className = 'dashboard-list-element-empty dashboard-element-empty common-text';
							element.textContent = 'No meme found.';
							wrapper.appendChild(element);
						}
					}catch(ex){
						console.log(ex);
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
				}
			};
			connection.send();
		}
	},
	
	/**
	* Removes an empty category.
	*/
	removeCategory: function(){
		var id = document.getElementById('dashboard-category').value;
		var connection = Utils.makeConnection();
		connection.open('DELETE', '/category/' + encodeURIComponent(id), true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						if ( typeof(data.code) === 'number' && data.code === 3 ){
							return UI.alert.show('You cannot remove a non-empty category.', 'Invalid action.', 'error');
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					return UI.alert.show('This category has successfully been removed.', 'Category removed', 'success', function(){
						window.location.href = '/';
					});
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send();
	}
};

var Comment = {
	/**
	* @var Number page An integer number greater than zero containing the page number used in comments listing.
	*/
	page: 1,
	
	/**
	* Loads all comments for a MEME or created by an user.
	*/
	loadComments: function(){
		var dataContainer = new UI.dataContainer();
		var user = document.getElementById('user-id');
		var profile = user === null ? false : true;
		if ( profile === true ){
			dataContainer.loader = document.getElementById('user-comments-loader');
			dataContainer.error = document.getElementById('user-comments-error');
			dataContainer.list = document.getElementById('user-comments-list');
			var loader = dataContainer.list.querySelector('li.user-comments-list-element-loader');
		}else{
			dataContainer.loader = document.getElementById('slide-column-comments-loader');
			dataContainer.error = document.getElementById('slide-column-comments-error');
			dataContainer.list = document.getElementById('slide-column-comments-list');
			var loader = dataContainer.list.querySelector('li.slide-column-comments-list-element-loader');
		}
		var append = this.page === 1 ? 0 : 1;
		if ( append === 1 && loader !== null ){
			var spinner = document.createElement('div');
			if ( profile === true ){
				spinner.className = 'user-comments-list-element-loader-spinner user-comments-element-loader-spinner';
			}else{
				spinner.className = 'slide-column-comments-list-element-loader-spinner meme-comments-element-loader-spinner';
			}
			spinner.title = 'Loading comments...';
			loader.innerHTML = '';
			loader.appendChild(spinner);
		}else{
			dataContainer.showLoader();
		}
		var url = profile === true ? ( '/user/' + encodeURIComponent(user.value) + '/comments?' ) : ( '/comments?meme=' + encodeURIComponent(document.getElementById('meme-id').value) + '&' ) + 'page=' + this.page.toString();
		var connection = Utils.makeConnection();
		connection.open('GET', url, true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, false) === false ){
					return dataContainer.showError();
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return dataContainer.showError();
					}
					var profile = document.getElementById('user-id') === null ? false : true;
					var loader = profile === true ? dataContainer.list.querySelector('li.user-comments-list-element-loader') : dataContainer.list.querySelector('li.slide-column-comments-list-element-loader');
					if ( loader !== null ){
						loader.parentNode.removeChild(loader);
					}
					Comment.parseComments(data.data, append);
					if ( append === 1 || data.data.length > 0 ){
						var loader = document.createElement('li');
						if ( profile === true ){
							loader.className = 'user-comments-list-element-loader user-comments-element-loader';
						}else{
							loader.className = 'slide-column-comments-list-element-loader meme-comments-element-loader';
						}
						loader.innerHTML = '';
						if ( data.data.length === 20 ){
							var button = document.createElement('a');
							if ( profile === true ){
								button.className = 'user-comments-list-element-loader-link user-comments-element-loader-link common-text';
							}else{
								button.className = 'slide-column-comments-list-element-loader-link meme-comments-element-loader-link common-text';
							}
							button.textContent = 'Load more comments';
							button.title = 'Load more comments';
							button.href = 'javascript:void(0);';
							button.onclick = Comment.loadMoreComments;
							loader.appendChild(button);
						}else{
							if ( append === 1 ){
								var text = document.createElement('span');
								if ( profile === true ){
									text.className = 'user-comments-list-element-loader-text user-comments-element-loader-text common-text';
								}else{
									text.className = 'slide-column-comments-list-element-loader-text meme-comments-element-loader-text common-text';
								}
								text.textContent = 'No more comments';
								loader.appendChild(text);
							}
						}
						dataContainer.list.appendChild(loader);
					}
					return dataContainer.showList();
				}catch(ex){
					console.log(ex);
					return dataContainer.showError();
				}
			}
		};
		connection.send();
	},
	
	/**
	* Loads next commments page.
	*/
	loadMoreComments: function(){
		if ( document.getElementById('user-id') === null ){
			var loader = document.getElementById('slide-column-comments-list').querySelector('li.meme-comments-list-element-loader');
		}else{
			var loader = document.getElementById('user-comments-list').querySelector('li.user-comments-list-element-loader');
		}
		if ( loader !== null ){
			loader.innerHTML = '';
		}
		Comment.page++;
		Comment.loadComments();
	},
	
	/**
	* Generates the HTML objects using the provided comments.
	*
	* @param Array data A sequential array of objects where each object represents a comment.
	* @param Number appendMode An integer number greater or equal than 0 and lower or equal than 2 representing the append mode (0 => the list will be emptied before adding entries, append comments, insert comment as first element).
	*/
	parseComments: function(data, appendMode){
		var profile = document.getElementById('user-id') === null ? false : true;
		var wrapper = profile === true ? document.getElementById('user-comments-list') : document.getElementById('slide-column-comments-list');
		if ( appendMode === 0 ){
			wrapper.innerHTML = '';
			if ( data.length === 0 ){
				var element = document.createElement('li');
				element.className = profile === true ? 'user-comments-list-element-empty user-comments-element-empty common-text' : 'slide-column-comments-list-element-empty meme-comments-element-empty common-text';
				element.textContent = 'No comment found.';
				wrapper.appendChild(element);
				return;
			}
		}else if ( appendMode === 2 ){
			var element = profile === true ? wrapper.querySelector('.user-comments-list-element-empty') : wrapper.querySelector('.slide-column-comments-list-element-empty');
			if ( element !== null ){
				wrapper.removeChild(element);
			}
		}
		var admin = User.adminLoggedIn();
		for ( var i = 0 ; i < data.length ; i++ ){
			var element = document.createElement('li');
			element.className = profile === true ? 'user-comments-list-element user-comments-element' : 'slide-column-comments-list-element meme-comments-element';
			element.setAttribute('cid', data[i].id);
			var header = document.createElement('div');
			header.className = profile === true ? 'user-comments-list-element-header' : 'slide-column-comments-list-element-header';
			if ( typeof(data[i].user) === 'object' && data[i].user !== null ){
				var user = document.createElement('a');
				if ( profile === true ){
					user.className = 'user-comments-list-element-header-user-link user-comments-element-user-link common-text';
				}else{
					user.className = 'slide-column-comments-list-element-header-user-link meme-comments-element-user-link common-text';
				}
				user.title = data[i].user.name;
				user.textContent = data[i].user.name;
				user.href = '/user/' + data[i].user.id;
			}else{
				var user = document.createElement('p');
				user.className = profile === true ? 'user-comments-list-element-header-user user-comments-element-user common-text' : 'slide-column-comments-list-element-header-user meme-comments-element-user common-text';
				user.textContent = 'Anonymous user.';
			}
			header.appendChild(user);
			var date = document.createElement('p');
			date.className = profile === true ? 'user-comments-list-element-header-date user-comments-element-date common-text' : 'slide-column-comments-list-element-header-date meme-comments-element-date common-text';
			date.textContent = new Date(data[i].date * 1000).toLocaleDateString('en', {
				year: 'numeric',
				month: 'long',
				day: 'numeric'
			});
			header.appendChild(date);
			if ( admin === true || data[i].mine === true ){
				var remove = document.createElement('div');
				remove.className = profile === true ? 'user-comments-list-element-header-remove user-comments-element-remove' :  'slide-column-comments-list-element-header-remove meme-comments-element-remove';
				remove.setAttribute('scope', 'remove');
				remove.title = 'Remove this comment';
				remove.onclick = Comment.remove;
				header.appendChild(remove);
			}
			element.appendChild(header);
			var text = document.createElement('p');
			text.className = profile === true ? 'user-comments-list-element-text user-comments-element-text common-text' : 'slide-column-comments-list-element-text meme-comments-element-text common-text';
			text.textContent = data[i].text;
			element.appendChild(text);
			if ( appendMode === 2 && profile === false ){
				wrapper.insertBefore(element, wrapper.firstChild);
			}else{
				wrapper.appendChild(element);
			}
		}
	},
	
	/**
	* Calls the method used to create the comment.
	*
	* @param Object event The event object.
	*/
	triggerCreation: function(event){
		event.preventDefault();
		event.stopPropagation();
		Comment.create();
	},
	
	/**
	* Checks if the "enter" is pressed and then creates the comment.
	*
	* @param Object event The event object.
	*/
	handleKeyPress: function(event){
		if ( event.keyCode !== 13 || event.shiftKey === true ){
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		Comment.create();
	},
	
	/**
	* Creates a new comment for a MEME.
	*/
	create: function(){
		var text = document.getElementById('slide-column-comments-editor-comment').value;
		text = text.trim();
		if ( text.length === 0 ){
			return;
		}
		if ( text.length > 10000 ){
			return UI.alert.show('Your comment is too long, max length is 10000 chars.', 'Comment is too long.', 'error');
		}
		var connection = Utils.makeConnection();
		connection.open('POST', '/comments', true);
		connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, false) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						switch ( ( typeof(data.code) === 'number' ? data.code : null ) ){
							case 1:{
								return UI.alert.show('You cannot create an empty comment.', 'Invalid text.', 'error');
							}break;
							case 2:{
								return UI.alert.show('Your comment is too long, max length is 10000 chars.', 'Comment is too long.', 'error');
							}break;
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					document.getElementById('slide-column-comments-editor-comment').value = '';
					var counter = document.querySelector('.slide-column-counters-list-element-counter[scope="comment"]');
					if ( counter !== null ){
						var value = parseInt(counter.getAttribute('counter')) + 1;
						counter.setAttribute('counter', value.toString());
						counter.textContent = Utils.stringifyCounterValue(value);
					}
					return Comment.parseComments([data.data], 2);
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send('meme=' + encodeURIComponent(document.getElementById('meme-id').value) + '&text=' + encodeURIComponent(text));
	},
	
	/**
	* Removes a comment.
	*/
	remove: function(event){
		var element = event.target.parentNode.parentNode;
		var id = element.getAttribute('cid');
		if ( id === null || id === '' ){
			return;
		}
		var connection = Utils.makeConnection();
		connection.open('DELETE', '/comments/' + encodeURIComponent(id), true);
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, true) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					var profile = document.getElementById('user-id') === null ? false : true;
					var element = event.target.parentNode.parentNode;
					var wrapper = element.parentNode;
					wrapper.removeChild(element);
					var loader = profile === true ? wrapper.querySelector('li.user-comments-list-element-loader') : wrapper.querySelector('li.slide-column-comments-list-element');
					if ( loader === null ){
						var link = profile === true ? wrapper.querySelector('li.user-comments-list-element-loader-link') : wrapper.querySelector('li.meme-comments-list-element-loader-link');
						if ( link !== null ){
							return Comment.loadMoreComments();
						}
						wrapper.innerHTML = '';
						var element = document.createElement('li');
						element.className = profile === true ? 'user-comments-list-element-empty user-comments-element-empty common-text' : 'slide-column-comments-list-element-empty meme-comments-element-empty common-text';
						element.textContent = 'No comment found.';
						wrapper.appendChild(element);
					}
					var counter = document.querySelector('.slide-column-counters-list-element-counter[scope="comment"]');
					if ( counter !== null ){
						var value = parseInt(counter.getAttribute('counter')) - 1;
						if ( value < 0 ){
							value = 0;
						}
						counter.setAttribute('counter', value.toString());
						counter.textContent = Utils.stringifyCounterValue(value);
					}
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send();
	}
};

var Vote = {
	/**
	* Toggles the value of a positive vote.
	*
	* @param Object event The event object.
	*/
	toggleUpVote: function(event){
		if ( event === null ){
			return Vote.toggle(null, true);
		}
		Vote.toggle(event.target.parentNode.parentNode.parentNode.parentNode, true);
	},
	
	/**
	* Toggles the value of a negative vote.
	*
	* @param Object event The event object.
	*/
	toggleDownVote: function(event){
		if ( event === null ){
			return Vote.toggle(null, false);
		}
		Vote.toggle(event.target.parentNode.parentNode.parentNode.parentNode, false);
	},
	
	/**
	* Toggles the value of a vote.
	*
	* @param Object element An object representing the DOM element containing the target MEME.
	* @param Boolean positive If set to "true" a positive vote will be toggled, otherwise a negative one.
	*/
	toggle: function(element, positive){
		var id = element === null ? document.getElementById('meme-id').value : element.getAttribute('mid');
		var value = positive === true ? '1' : '0';
		var connection = Utils.makeConnection();
		connection.open('POST', '/toggleVote/' + id, true);
		connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, true, false) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					if ( element === null ){
						var icon = document.querySelector('.slide-column-counters-list-element-icon[scope="upVote"]');
						var counter = document.querySelector('.slide-column-counters-list-element-counter[scope="upVote"]');
					}else{
						var icon = element.querySelector('.dashboard-list-element-container-info-icon[scope="upVote"]');
						var counter = element.querySelector('.dashboard-list-element-container-info-counter[scope="upVote"]');
					}
					if ( data.data.positive === true && icon.getAttribute('selected') !== 'true' ){
						var counterValue = parseInt(counter.getAttribute('counter')) + 1;
						counter.setAttribute('counter', counterValue);
						counter.textContent = Utils.stringifyCounterValue(counterValue);
						icon.setAttribute('selected', 'true');
					}else if ( data.data.positive === false && icon.getAttribute('selected') === 'true' ){
						var counterValue = parseInt(counter.getAttribute('counter')) - 1;
						if ( counterValue < 0 ){
							counterValue = 0;
						}
						counter.setAttribute('counter', counterValue);
						counter.textContent = Utils.stringifyCounterValue(counterValue);
						icon.setAttribute('selected', 'false');
					}
					if ( element === null ){
						icon = document.querySelector('.slide-column-counters-list-element-icon[scope="downVote"]');
						counter = document.querySelector('.slide-column-counters-list-element-counter[scope="downVote"]');
					}else{
						icon = element.querySelector('.dashboard-list-element-container-info-icon[scope="downVote"]');
						counter = element.querySelector('.dashboard-list-element-container-info-counter[scope="downVote"]');
					}
					if ( data.data.negative === true && icon.getAttribute('selected') !== 'true' ){
						var counterValue = parseInt(counter.getAttribute('counter')) + 1;
						counter.setAttribute('counter', counterValue);
						counter.textContent = Utils.stringifyCounterValue(counterValue);
						icon.setAttribute('selected', 'true');
					}else if ( data.data.negative === false && icon.getAttribute('selected') === 'true' ){
						var counterValue = parseInt(counter.getAttribute('counter')) - 1;
						if ( counterValue < 0 ){
							counterValue = 0;
						}
						counter.setAttribute('counter', counterValue);
						counter.textContent = Utils.stringifyCounterValue(counterValue);
						icon.setAttribute('selected', 'false');
					}
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send('value=' + value);
	}
};

var Contact = {
	/**
	* Calls the method that send the message.
	*
	* @param Object event The event object.
	*/
	triggerSubmit: function(event){
		event.preventDefault();
		event.stopPropagation();
		Contact.submit();
	},
	
	/**
	* Sends a message to the website owner.
	*/
	submit: function(){
		var name = document.getElementById('about-contact-name').value;
		if ( name === '' || name.length > 30 ){
			return UI.alert.show('You must provide a name, max length is 30 chars.', 'Invalid name.', 'error');
		}
		var email = document.getElementById('about-contact-email').value;
		if ( email === '' || email.length > 256 ){
			return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
		}
		var message = document.getElementById('about-contact-message').value;
		if ( message === '' || message.length > 10000 ){
			return UI.alert.show('You must provide a message, max length is 10000 chars.', 'Invalid message.', 'error');
		}
		var url = document.getElementById('about-contact').action;
		var connection = Utils.makeConnection();
		connection.open('POST', url, true);
		connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				if ( Utils.handleHTTPError(connection.status, false) === false ){
					return false;
				}
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						switch ( ( typeof(data.code) === 'number' ? data.code : null ) ){
							case 1:
							case 2:{
								return UI.alert.show('You must provide a name, max length is 30 chars.', 'Invalid name.', 'error');
							}break;
							case 3:{
								return UI.alert.show('You must provide a valid e-mail address.', 'Invalid e-mail address.', 'error');
							}break;
							case 4:
							case 5:{
								return UI.alert.show('You must provide a message, max length is 10000 chars.', 'Invalid message.', 'error');
							}break;
						}
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					document.getElementById('about-contact-name').value = '';
					document.getElementById('about-contact-email').value = '';
					document.getElementById('about-contact-message').value = '';
					alert('Your message has been successfully sent.');
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send('name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&message=' + encodeURIComponent(message));
	}
};

var Newsletter = {
	/**
	* Calls the method that subscribe the user to the mailing list.
	*
	* @param Object event The event object.
	*/
	triggerSubscription: function(event){
		event.preventDefault();
		event.stopPropagation();
		Newsletter.subscribe();
	},
	
	/**
	* Adds the given e-mail address to the newsletter's mailing list.
	*/
	subscribe: function(){
		var email = document.getElementById('dashboard-slide-newsletter-email').value;
		if ( email === '' || email.length > 256 ){
			email = document.getElementById('dashboard-slide-newsletter-email-min').value;
			if ( email === '' || email.length > 256 ){
				return;
			}
		}
		var url = document.getElementById('dashboard-slide-newsletter').action;
		var connection = Utils.makeConnection();
		connection.open('POST', url, true);
		connection.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		connection.setRequestHeader('X-CSRF-Token', Utils.getCSRFToken());
		connection.onreadystatechange = function(){
			if ( connection.readyState > 3 ){
				try{
					var data = JSON.parse(connection.responseText);
					if ( typeof(data.result) !== 'string' || data.result !== 'success' ){
						return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
					}
					document.getElementById('dashboard-slide-newsletter-email').value = '';
					document.getElementById('dashboard-slide-newsletter-email-min').value = '';
					return UI.alert.show('Your e-mail address has been inserted in our mailing list.', 'Subscribed successfully!', 'success');
				}catch(ex){
					console.log(ex);
					return UI.alert.show('An unexpected error has occurred, please retry later.', 'Unable to complete the action.', 'error');
				}
			}
		};
		connection.send('email=' + encodeURIComponent(email));
	}
};

if ( UI.cookiePolicy.checkAgreement() === false ){
	UI.cookiePolicy.show();
}
switch ( window.location.hash ){
	case '#newsletter.removed':{
		UI.alert.show('Your address has been remove from our newsletter!', 'E-mail address removed.', 'success');
	}break;
	case '#newsletter.error':{
		UI.alert.show('An error occurred while removing your address, retry later.', 'Unable to remove your e-mail address.', 'error');
	}break;
}