<?php $description = isset($description) ? $description : false; ?>
<p>This tool will import any media and documents you are currently hosting on this server to your cloud storage service.</p>
<?php if(!$description): ?>
<?php if($background): ?>
<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.  This process runs in the background until it's finished.  Once you've started the process, please check this page for progress.</p>
<?php else: ?>
<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.</p>
<p><strong>IMPORTANT:</strong> You are running the import process in the web browser.  <strong>Do not navigate away from this page or the import may not finish.</strong></p>
<?php endif; ?>
<p><strong>Note:</strong></p>
<ol>
	<li><strong>Always backup your database before performing any batch migration.</strong></li>
	<li>If you upload any files while this process is running, you'll need to run this tool again after it finishes.</li>
	<li>This process DOES NOT delete your files on your server, you'll have to do that yourself manually.</li>
</ol>
<?php endif; ?>