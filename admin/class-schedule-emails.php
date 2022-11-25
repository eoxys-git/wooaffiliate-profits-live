<?php 

/**
 *  WAI plugin classes
 *  
 *  @since 0.0.1
 *  
 * */
 
class Schedule_email_list_table extends WP_List_Table
{

    public $found_data = array();

    function links_List_data()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'wai_schedule_emails';

        $results = $wpdb->get_results("SELECT * FROM $table_name");
        $ps = array();
        foreach ($results as $value){
            $entries = array(
                'schedule_title' => $value->schedule_title,
                'levels' => $value->levels,
                'days' => $value->days,
                'content' => $value->content,
                'action' => '<a href="'.site_url().'/wp-admin/admin.php?page=add-schedule-email&schedule_id='.$value->id.'" onclick="" data-user-id="">Edit</a>'
            );
            array_push($ps, $entries);

        }
        return $ps;
    }

    function no_items()
    {
        _e('No Schedule found.');
    }

    function column_default($item, $column_name)
    {
        switch ($column_name)
        {
            case 'checkbox':
            case 'schedule_title':
            case 'levels':
            case 'days':
            case 'content':
            case 'action':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'schedule_title' => array(
                'schedule_title',
                false
            ) ,
            'levels' => array(
                'levels',
                false
            ) ,

            'days' => array(
                'days',
                false
            ) 
        );
        return $sortable_columns;
    }

    function get_columns()
    {
        $columns = array(
            'schedule_title' => 'Schedule Title',
            'levels' => 'Levels',
            'days' => 'Days',
            'content' => 'Content',
            'action' => 'Action'
        );

        return $columns;
    }

    function usort_reorder($a, $b)
    {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'product';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    function prepare_items()
    {

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );
        $this->process_bulk_action();
        $data = $this->links_List_data();
        usort($data, array(&$this,
            'usort_reorder'
        ));
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $this->found_data = array_slice($data, (($current_page - 1) * $per_page) , $per_page);
        $this->items = $this->found_data;
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));

    }

}