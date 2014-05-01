<?php

class ACFFormsEntryTable extends WP_List_Table
{
    public $form;

    public function __construct($form)
    {
        $this->form = $form;

        parent::__construct(array(
            'singular' => 'acf_form_entry',
            'plural'   => 'acf_form_entires',
            'ajax'     => false
        ));

        error_log(print_r($_SERVER['REQUEST_URI'], true));
    }

    public function extra_tablenav($which)
    {
        if ($which !== 'top') return;

        echo '<input type="hidden" name="post_type" value="' . $_GET['post_type'] . '">';
        echo '<input type="hidden" name="form_id" value="' . $_GET['form_id'] . '">';
    }

    public function set_pagination_args( $args )
    {
        $args['post_type'] = $_GET['post_type'];
        $args['form_id'] = $_GET['form_id'];

        return parent::set_pagination_args($args);
    }

    public function no_items()
    {
		_e('No submissions found.');
	}

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case "post_date":
                return $item->post_date;
            default:
                return print_r($item, true);
        }
    }

    public function column_data($item)
    {
        $url = "?page=%s&action=%s&acf_form_entry=%s&form_id=" . $_GET['form_id'] . "&post_type=" . $_GET['post_type'];
        $actions = array(
            'delete' => sprintf('<a href="' . $url . '">Delete</a>',$_REQUEST['page'],'delete',$item->ID)
        );

        $fields = get_field_objects($item->ID);

        ob_start();
        include(dirname(__DIR__) . '/views/entry_data_row.php');
        return ob_get_clean() . $this->row_actions($actions);
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item->ID
        );
    }

    public function get_columns()
    {
        return $columns = array(
            'cb'   => '<input type="checkbox" />',
            'data' => __('Entry Data'),
            'post_date' => __('Date'),
        );
    }

    public function get_sortable_columns()
    {
        return $sortable = array(
            'post_date' => array('post_date', true)
        );
    }

    public function get_bulk_actions()
    {
        return $actions = array(
            'delete' => __('Delete')
        );
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $entries = $_GET['acf_form_entry'];
            if (!is_array($entries)) $entries = array($entries);
            foreach ($entries as $id) {
                wp_delete_post($id);
            }
        }
    }

    public function prepare_items()
    {
        global $wpdb;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $query = "SELECT * FROM $wpdb->posts WHERE post_parent = %d";

        $orderby = !empty($_GET["orderby"])? $_GET["orderby"] : "ASC";
        $order = !empty($_GET["order"]) ? $_GET["order"] : "";
        if (!empty($orderby) && !empty($order)) $query .= " ORDER BY $orderby $order";

        $total_items = $wpdb->query($wpdb->prepare($query, $this->form->ID));
        $per_page = 10;
        $current_page = $this->get_pagenum();

        $offset = ($current_page - 1) * $per_page;
        $query .= " LIMIT $offset, $per_page";

        $this->set_pagination_args(array(
            "total_items" => $total_items,
            "total_pages" => ceil($total_items / $per_page),
            "per_page" => $per_page
        ));

        $this->items = $wpdb->get_results($wpdb->prepare($query, $this->form->ID));
    }
}
