<div class="submitbox" id="submitpost">
    <div id="misc-publishing-actions">
        <div class="misc-pub-section">
            <a class="button" href="<?php echo $entries_url; ?>">View Submissions</a>
        </div>
        <div class="misc-pub-section">
            <label>Shortcode:</label>
            <strong>[acf-form form="<?php echo $post->post_name; ?>"]</strong>
        </div>
    </div>
    <div id="major-publishing-actions">
        <div id="delete-action">
            <?php if (current_user_can('delete_post', $post->ID)): ?>
                <a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>">Trash</a>
            <?php endif; ?>
        </div>
        <div id="publishing-action">
            <span class="spinner"></span>
            <?php if (!in_array($post->post_status, array('publish')) || 0 == $post->ID): ?>
                <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish'); ?>" />
            <?php else: ?>
                <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
            <?php endif; ?>
            <?php submit_button(__( 'Save' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' )); ?>
        </div>
        <div class="clear"></div>
    </div>
</div>

