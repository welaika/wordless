<div class="wrap">
  <div id="icon-themes" class="icon32"><br></div>
  <h2><?php echo __("Setting Wordless preferences", "wl") ?></h2>
  <form method="POST">
    <table class="form-table">
      <tbody>
        <?php foreach ($wordless_preferences as $name => $properties): ?>
          <tr valign="top">
            <th scope="row">
              <label for="<?php echo $name ?>"><?php echo $properties['label'] ?></label>
            </th>
            <td>
              <input name="<?php echo $name ?>" type="text" id="<?php echo $name ?>" value="<?php
              if (!get_option($name)){
                echo $properties['default_value'];
              } else {
                if ($name == "assets_preprocessors" || $name == 'css_require_libs'){
                  echo implode(", ", get_option($name));
                } else {
                  echo get_option($name);
                }
              }
              ?>" class="regular-text">
              <br/>
              <span class="description"><?php echo $properties['description'] ?></span>
              <?php if (isset($properties['error'])): ?>
                <br/>
                <span class="error" style="color:red"><?php echo $properties['error'] ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo __("Save preferences", "wl") ?>">
    </p>
  </form>
</div>

<?php

