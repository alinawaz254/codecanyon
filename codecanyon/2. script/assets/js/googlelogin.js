// Render Google Sign-in button
function renderButton() {
	gapi.signin2.render('gSignIn', {
		'scope': 'profile email',
		'width': 340,
		'height': 48,
		'longtitle': true,
		'theme': 'light',
		'onsuccess': onSuccess,
		'onfailure': onFailure
	});
}

function onSuccess(googleUser) {
    // Get the Google profile data (basic)
    //var profile = googleUser.getBasicProfile();
    
    // Retrieve the Google account data
    gapi.client.load('oauth2', 'v2', function () {
        var request = gapi.client.oauth2.userinfo.get({
            'userId': 'me'
        });
        request.execute(function (resp) {
            // Display the user details
			google_login_prc(resp.name,'',resp.gender,resp.email,resp.picture);
        });
    });
}

function google_login_prc(first_name,last_name,gender,email,profile_image) {
	$.ajax({
	 data: {
	  first_name: first_name,
	  last_name: last_name,
	  gender: gender,
	  email: email,
      profile_image: profile_image
	 },
	 type: 'POST',
	 dataType: 'json',
	 url: 'lib/includes/socialloginprocess.php',
	 success: function(response) {
	   var message = response.message;
			if(message == '1') { 
				window.location.replace("dashboard.php");
			} else { 
				$("#google_return_msg").html("<div class='alert alert-success'>"+message+"</div>");
			}
	   }
	});
}

// Sign-in failure callback
function onFailure(error) {
    alert("Something isn't right with google login. Please refresh page and try again.");
}