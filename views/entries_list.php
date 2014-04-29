<div class="wrap">
    <div id="icon-users" class="icon32"><br></div>
    <h2><?php echo $form->post_title; ?>: Entries</h2>

    <form id="acf_form_entries-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
        <?php
            $table = new ACFFormsEntryTable($form);
            $table->prepare_items();
            $table->display();
        ?>
    </form>
</div>
