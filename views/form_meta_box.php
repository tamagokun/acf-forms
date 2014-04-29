<input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>">
<?php wp_nonce_field('save_' . $metabox['id'], $metabox['id'] . '_nonce'); ?>
<table class="form-table">
    <tr>
        <th>
            <label for="form_field_group">Form Field Group</label>
        </th>
        <td>
            <select name="form_field_group" id="form_field_group">
                <?php $value = get_post_meta($post->ID, 'form_field_group', true); ?>
                <?php foreach($field_groups as $field_group): ?>
                    <?php if ($field_group['id'] == $value): ?>
                        <option value="<?php echo $field_group['id']; ?>" selected>
                            <?php echo $field_group['title']; ?>
                        </option>
                    <?php else: ?>
                        <option value="<?php echo $field_group['id']; ?>">
                            <?php echo $field_group['title']; ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>
<input type="hidden" name="<?php echo $metabox['id']; ?>_fields[]" value="form_field_group" />
