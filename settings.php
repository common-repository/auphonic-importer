<div class="wrap">
    <?php screen_icon(); ?>
    <?php
    $options = get_option('ai_options');
    $lastUpdated = get_option('ai_lastUpdated');

    if (!isset($lastUpdated)) {
        $lastUpdated = "N/A";
    }

    if (isset($options['ai_status'])) {
        $option_selected[$options['ai_status']] = " selected";
    }
    if (isset($options['ai_enabled'])) {
        $option_selected[$options['ai_enabled']] = " selected";
    }

    $category_ids = get_all_category_ids();
    $categories = "";
    foreach ($category_ids as $cat_id) {
        $cat_name = get_cat_name($cat_id);
        if ($cat_id == $options['ai_category']) {
            $selected = " selected";
        } else {
            $selected = "";
        }

        $categories .= "<option value='{$cat_id}'{$selected}>{$cat_name}</option>";
    }
    $url = pathinfo("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    $url = str_replace('wp-admin', '', $url['dirname']) . "?ai_update=". $options['ai_update_password'];
    ?>
    <h2>Auphonic Importer</h2>
    <h4>The Auphonic Importer will read the last 10 Productions from the Auphonic API once every hour.<br>
        If you need to update manually, click <a href="<?= $url ?>">here</a></h4>
    Last Updated: <?= $lastUpdated; ?>
    <form method="post" action="options-general.php?page=ai-settings">
        <p><em>All of these options are required.</em></p>
        <b>Username</b><br><input type="test" name="ai_username" value="<?= $options['ai_username'] ?>" size="30"><br><br>
        <b>Password</b><br><input type="password" name="ai_password" value="<?= $options['ai_password'] ?>" size="30"><br><br>
        <b>Cron/Bookmark URL Update Password</b><br><input type="text" name="ai_update_password" value="<?= $options['ai_update_password'] ?>" size="30"><br>
        Your update url is: <?php echo $url; ?><br><br>
        <b>Tag</b><br><input type="text" name="ai_tag" value="<?= $options['ai_tag'] ?>" size="50">
        <p>The tag corresponds to the tag you put in on your Auphonic preset. If you use Auphonic for more
            than one production, this will filter them and only import productions from one preset that has this tag.</p>
        <p><b>Remove control tag from Wordpress post</b>: <input type='checkbox' name="ai_remove_tag" value="1" <?php if (1 == $options['ai_remove_tag']) echo 'checked="checked"'; ?> /></p>
        <b>User to attribute posts to</b><br><select name="ai_user">
            <?php
            $users = get_users_of_blog();
            foreach ($users as $user) {
                if ($user->user_id == $options['ai_user']) {
                    $selected = " selected";
                } else {
                    $selected = "";
                }
                echo "<option value='{$user->user_id}'{$selected}>{$user->display_name}</option>\n";
            }
            ?>
        </select><br><br>
        <b>Category these post be attributed to</b><br>
        <select name="ai_category">
            <?= $categories ?>
        </select>
        <br><br>
        <b>Post Status for new posts</b><br><select name="ai_status">
            <option<?= $option_selected['Draft'] ?>>Draft</option>
            <option<?= $option_selected['Published'] ?>>Published</option>
        </select><br><br>
        <b>Enabled</b><br><select name="ai_enabled">
            <option<?= $option_selected['Yes'] ?>>Yes</option>
            <option<?= $option_selected['No'] ?>>No</option>
        </select><br><em>(if set to No, the plugin will not run)</em><br><br>
        <input type="hidden" name="ai_save_options" value="yes">
        <?php submit_button(); ?>
    </form>

    <h3>Clear Cache</h3>
    <p>Click this link to clear out the cache. Doing so will cause Auphonic Importer to reload the last 10 podcasts the next time it imports</p>
    <p><a href="./?ai_clear_cache=yes" onclick="return confirm('Are you sure you wish to clear the cache');">Clear Cache</a></p>
</div>