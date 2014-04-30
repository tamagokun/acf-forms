<?php foreach ($fields as $field_name => $field): ?>
    <p>
        <strong><?php echo $field['label']; ?>:</strong>
        <?php if (is_array($field['value'])): ?>
            <?php echo implode(', ', $field['value']); ?>
        <?php else: ?>
            <?php echo $field['value']; ?>
        <?php endif; ?>
    </p>
<?php endforeach; ?>
