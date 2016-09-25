<?php
error_reporting(E_ALL);

// Prototypical openssl-based OCSP verifier
class OCSPVerifier {
	var $CERTS_DIR;
	var $TEMP_DIR;
	var $ISSUER_CERTS;
	var $VA_FILE;
	var $URL;

	function OCSPVerifier() {
		// For simplicity we'll hardcode sample configuration here
		$this->TEMP_DIR = "/tmp";
		$this->CERTS_DIR = "/var/www/ocsp_certs";

		$this->ISSUER_CERT = Array();
		$this->ISSUER_CERT["ESTEID-SK 2011"] = "ESTEID-SK_2011.pem";

		$this->VAFILE = "TEST_OCSP_2011.pem";
		$this->URL = "http://demo.sk.ee/ocsp";
	}


	function verify($cert, $issuer_cn) {
		if (!isset($this->ISSUER_CERT[$issuer_cn])) return false;
		
		// Save the certificate to a temporary file
		$filename = tempnam($this->TEMP_DIR, "ocsp");
		$f = fopen($filename, 'w');
		fwrite($f, $cert);
		fclose($f);
	
		// Run openssl
		$result = false;
		$cmd = "openssl ocsp -issuer " . $this->ISSUER_CERT[$issuer_cn] . " -cert " . $filename . " -VAfile " . $this->VAFILE . " -url " . $this->URL;
		$output = shell_exec("cd ".$this->CERTS_DIR." && ".$cmd." 2>&1");
		$result = (strpos($output, $filename.': good') !== FALSE) && (strpos($output, "Response Verify Failure") === FALSE);
		unlink($filename);
		return $result;
	}
};

$v = new OCSPVerifier();
?>

<html>
<body>
	<b>The server reports the following variable values</b>
<pre>
SSL_CLIENT_S_DN=<?=$_SERVER["SSL_CLIENT_S_DN"]?><br>
SSL_CLIENT_VERIFY=<?=$_SERVER["SSL_CLIENT_VERIFY"]?>
</pre>
	<p>
	<b>OCSP validation result: <?=$v->verify($_SERVER["SSL_CLIENT_CERT"], $_SERVER["SSL_CLIENT_I_DN_CN"]) ? "yes" : "no" ?></b>
	</p>

	<p>
	Note that the test server is used for validation here. The test server does not report actual certificate validity,
	but instead whatever value you ask it to report when you upload your certificate <a href="https://demo.sk.ee/upload_cert/">here</a>.
	</p>

	<p><a href="/">Back</a></p>

</body>
</html>
