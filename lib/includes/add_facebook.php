<script>
  function statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
    if (response.status === 'connected') {
      testAPI();
    } else if (response.status === 'not_authorized') {
      document.getElementById('status').innerHTML = 'Please log ' +
        'into this app.';
    } else {
      document.getElementById('status').innerHTML = 'Please log ' +
        'into Facebook.';
    }
  }
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }
  window.fbAsyncInit = function() {
  FB.init({
    appId      : '1499598016922826',
    cookie     : true,  // enable cookies to allow the server to access 
    status : true, // check login status
    xfbml      : true,  // parse social plugins on this page
    version    : 'v3.2' // use version 2.0
  });

  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });
};

  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));

  function testAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
      console.log('Successful login for: ' + response.name);
    	if(response.email != 'undefined' || response.email != '') { 
			fb_login_prc(response.first_name, response.last_name, response.gender, response.email);
		}  
    });
  }
  function fb_login_prc(first_name,last_name,gender,email) {
		$.ajax({
		 data: {
		  first_name: first_name,
		  last_name: last_name,
		  gender: gender,
		  email: email
		 },
		 type: 'POST',
		 dataType: 'json',
		 url: 'lib/includes/socialloginprocess.php',
		 success: function(response) {
		   var message = response.message;
				if(message == '1') { 
					window.location.replace("dashboard.php");
				} else { 
					$("#fb_return_msg").html("<div class='alert alert-success'>"+message+"</div>");
				}
		   }
		});
	}
</script>