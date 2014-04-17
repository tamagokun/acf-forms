<div class="submitbox" id="submitpost">
    <div id="misc-publishing-actions">
        <div class="misc-pub-section">
            <a class="button" href="edit.php?post_type=form-<?php echo $post->post_name; ?>">View Submissions</a>
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
            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
            <input name="save" type="submit" class="button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
        </div>
        <div class="clear"></div>
    </div>
</div>

