<?php $count = 0; ?>
<?php foreach ($fields as $field_name => $field): ?>
    <?php if ($count == 4): ?>
        <div class="full-submission">
    <?php endif; ?>
    <p>
        <strong><?php echo $field['label']; ?>: </strong>
        <?php echo ACFFormsUtil::print_field($field, true); ?>
    </p>
    <?php $count++; ?>
<?php endforeach; ?>
<?php if ($count > 4): ?>
    </div>
<?php endif; ?>
