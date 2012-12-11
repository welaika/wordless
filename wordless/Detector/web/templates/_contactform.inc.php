		<p>
			If you found a problem with a test or have a question about Detector please submit a comment below:<br /><br />
		</p>
		<form action="contact.php" class="form-stacked" method="post">
			<input type="hidden" id="post" name="post" value="true">
			<input type="hidden" id="uaHash" name="uaHash" value="<?=$ua->uaHash?>">
			<fieldset>
				<legend>Contact</legend>
				<div class="clearfix">
					<label for="email">Your Email</label>
					<input class="xlarge" id="email" name="email" size="30" type="text">
					<span class="help-block"><strong>Note:</strong> providing an email address is only required if you want a response.</span>
				</div>
				<div class="clearfix">
					<label for="message">Your Message</label>
					<?php
						$salutations = array("Dear David","Dearest Dave","Mr. Olsen","Monsieur");
						$randKey = array_rand($salutations);
					?>
					<textarea class="xxlarge" id="message" name="message" rows="8"><?=$salutations[$randKey]?>-
					
I may have found a problem with the browser related to the user agent hash "<?=$_REQUEST['cid']?>." My notes are below:

</textarea>
					<span class="help-block"><strong>Note:</strong> feel free to rip out the default text if you want.</span>
				</div>
			</fieldset>
			<div class="actions span9" <?php if (isset($ua->isMobile) && $ua->isMobile) { print "style='width: 92%'"; }?>>
				<button type="submit" class="btn primary">Send Note</button>
			</div>
		</form>