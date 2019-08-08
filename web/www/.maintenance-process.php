<?php

header('HTTP/1.1 503 Service Unavailable');
header('Retry-After: 300'); // 5 minutes in seconds

?>
<!DOCTYPE html>
<meta charset="utf-8">
<meta name="robots" content="noindex">
<meta name="generator" content="Nette Framework">

<style>
	body { color: #333; background: white; width: 600px; margin: 100px auto; text-align: center; }
	h1 { font: bold 47px/1.5 sans-serif; margin: .6em 0 }
	p { font: 21px/1.5 Georgia,serif; margin: 1.5em 0 }
</style>

<title>Stránky jsou dočasně nedostupné</title>

<h1>Stránky jsou v procesu aktualizace</h1>

<p>Děkujeme za pochopení.</p>

<?php

exit;
