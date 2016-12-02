<?php
require_once '/Users/fuzzy/dev/wordless/vendor/phamlp/haml/HamlHelpers.php';
?><h2>
  Post Details
</h2>  <?php echo render_partial("posts/post"); ?>

<h2>
  <?php echo "And the answer is... <?php echo $answer; ?>"; ?>

</h2>