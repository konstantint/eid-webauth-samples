<?php
ini_set("session.cookie_secure", "1");
session_start();
?>
<html>
<head>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<body>
	<p>
	<?php
		if (isset($_SESSION["clientCN"]) && $_SESSION["clientCN"] != "") {
	?>
		You are currently logged in as <?=$_SESSION["clientCN"]?>. 
		<a id="logout" href="#">Log out</a>
	<?php
		}
		else {
	?>
		You are currently anonymous.
		<a id="login" href="#">Log in</a>
	<?php
		}
	?>
	<p><a href="/">Back</a></p>
	<script>
		$("#logout").click(function() {
			// Works in some Firefox versions
			if (window.crypto && window.crypto.logout) window.crypto.logout();
			
			// In Chrome you can make the browser renegotiate if you fail certificate validation
			$.ajax("/sslverify0")
				.done(function() { location.href = "logout.php"; })
				.fail(function() { location.href = "logout.php"; });
		});

		$("#login").click(function() {
			$.ajax("login.php")
				.fail(function() { alert("Login failed. If this repeats you may need to restart your browser."); })
				.done(function(data) { 
					if (data == "") alert("Login failed. You might need to clear your SSL cache or restart the browser.");
					else location.reload(true); 
				});
		});
	</script>
</body>
</html>
