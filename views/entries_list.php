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
<script>

            (function($) {

                $(document).ready(function() {
                    $(document).on('click', '.toggle-full-submission', function(e) {
                        e.preventDefault();
                        var container = $(this).parents('td');
                        container.toggleClass('open');
                        if (container.hasClass('open')) {
                            $(this).text('Less');
                        } else {
                            $(this).text('More');
                        }
                    });
                });

            })(jQuery);

</script>
