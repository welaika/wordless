<div class="wrap">
  <div id="icon-themes" class="icon32"><br></div>
  <h2><?php echo __('Create a new Wordless theme', 'wl'); ?></h2>
  <form method="POST">
    <table class="form-table">
      <tbody>
        <?php foreach ($theme_options as $name => $properties): ?>
          <tr valign="top">
            <th scope="row">
              <label for="<?php echo $name ?>"><?php echo $properties['label'] ?></label>
            </th>
            <td>
              <input name="<?php echo $name ?>" type="text" id="<?php echo $name ?>" value="<?php echo (!isset($_POST[$name])) ? $properties['default_value'] : $_POST[$name] ?>" class="regular-text">
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
      <input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo __('Create theme and set it as the current one!', 'wl'); ?>">
    </p>
  </form>
</div>

<?php

