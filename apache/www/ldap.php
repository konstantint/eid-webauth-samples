<?php
error_reporting(E_ALL);

// Prototypical LDAP-based certificate validity checker
class LDAPVerifier {
	var $LDAP_HOST;

	function LDAPVerifier() {
		$this->LDAP_HOST = "ldap.sk.ee";
	}


	function verify($cert, $dn) {
		$conn = ldap_connect($this->LDAP_HOST);
		if (!$conn) return false;
		if (!ldap_bind($conn)) return false;
		$resultset = ldap_search($conn, "c=EE", "(serialNumber=38212200301)");
		if (!$resultset) { echo "No recs"; return false; };
		
		$rec = ldap_first_entry($conn, $resultset);
		while ($rec !== false) {
			$values = ldap_get_values($conn, $rec, 'usercertificate;binary');
			$certificate = "-----BEGIN CERTIFICATE-----\n".chunk_split(base64_encode($values[0]), 64, "\n")."-----END CERTIFICATE-----\n";
			if (strcmp($cert, $certificate) == 0) return "Found";
			$rec = ldap_next_entry($conn, $rec);
		}
		
		// Not found a record with a matching certificate
		return false;
	}
};

$v = new LDAPVerifier();
?>

<html>
<body>
	<b>The server reports the following variable values</b>
<pre>
SSL_CLIENT_S_DN=<?=$_SERVER["SSL_CLIENT_S_DN"]?><br>
SSL_CLIENT_VERIFY=<?=$_SERVER["SSL_CLIENT_VERIFY"]?>
</pre>
	<p>
	<b>LDAP validation result: <?=$v->verify($_SERVER["SSL_CLIENT_CERT"], $_SERVER["SSL_CLIENT_S_DN"]) ? "yes" : "no" ?></b>
	</p>

	<p>
	Note that there is no official guarantee that the validation process used here is correct.
	</p>

	<p><a href="/">Back</a></p>

</body>
</html>
