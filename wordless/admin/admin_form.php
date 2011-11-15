<div class="wrap">
  <div id="icon-themes" class="icon32"><br></div>
  <h2>Create a new Wordless theme</h2>
  <form method="POST">
    <table class="form-table">
      <tbody>
        <?php foreach ($theme_options as $name => $properties): ?>
          <tr valign="top">
            <th scope="row">
              <label for="<?= $name ?>"><?= $properties['label'] ?></label>
            </th>
            <td>
              <input name="<?= $name ?>" type="text" id="<?= $name ?>" value="<?= $_POST[$name] ?>" class="regular-text">
              <br/>
              <span class="description"><?= $properties['description'] ?></span>
              <?php if (isset($properties['error'])): ?>
                <br/>
                <span class="error" style="color:red"><?= $properties['error'] ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button-primary" value="Create theme and set it as the current one!">
    </p>
  </form>
</div>

<?php

