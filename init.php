<div class="wrap">
    <?php screen_icon(); ?>
    <h2>Auphonic Importer</h2>
    <h4>Initialize </h4>
    <form method="post" action="options-general.php?page=ai-settings">
        <p>This plugin needs to create a database table. Click the "Save Changes" button below to set things up.</p>
        <input type="hidden" name="ai_init" value="yes">
        <?php submit_button(); ?>
    </form>
</div>