<div class="wrap">
<h1>Wordless</h1>
    <div class="card">
        <h2><?php echo __('Create a new Wordless theme', 'wl'); ?></h2>
        <form method="POST" action="admin.php?page=wordless&update-options=1">
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
    <div class="card">
        <h2><?php echo __('Upgrade theme from Wordless 0.5x to Wordless 2', 'wl'); ?></h2>
        <p>
            <?php echo __('This function will be enabled if we\'ll notice that the current
                            theme is a theme built with the old Wordless 0.5x version.
                            Upgrading the theme will copy all files necessary to start
                            working on current theme using WebPack and all other new goodies
                            from newer Wordless versions', 'wl'); ?>
        </p>
        <form method="POST" action="admin.php?page=wordless&upgrade-theme=1">

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo __('Upgrade', 'wl'); ?>" <?php if ($theme_is_upgradable !== true) echo("disabled") ?>>
            </p>
        </form>
    </div>
</div>
<?php

