<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/* 
 * @author CokesHome
 * @copyright Copyright (c) 2015, CokesHome
 * 
 * This is model class for table "permissions"
 */

class Permissions_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'permissions';
    protected $key        = 'id_permission';

    /**
     * @var string Field name to use for the created time column in the DB table
     * if $set_created is enabled.
     */
    protected $created_field = 'created_on';

    /**
     * @var string Field name to use for the modified time column in the DB
     * table if $set_modified is enabled.
     */
    protected $modified_field = 'modified_on';

    /**
     * @var bool Set the created time automatically on a new record (if true)
     */
    protected $set_created = FALSE;

    /**
     * @var bool Set the modified time automatically on editing a record (if true)
     */
    protected $set_modified = FALSE;

    /**
     * @var bool Enable/Disable soft deletes.
     * If false, the delete() method will perform a delete of that row.
     * If true, the value in $deleted_field will be set to 1.
     */
    protected $soft_deletes = FALSE;

    /**
     * @var string The type of date/time field used for $created_field and $modified_field.
     * Valid values are 'int', 'datetime', 'date'.
     */
    protected $date_format = 'datetime';
    //--------------------------------------------------------------------

    /**
     * @var bool If true, will log user id in $created_by_field, $modified_by_field,
     * and $deleted_by_field.
     */
    protected $log_user = FALSE;

    /**
     * Function construct used to load some library, do some actions, etc.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function update_user_permission()
    {
        $this->db->trans_begin();

        $this->db->where('id_user <>', '7');
        $reset_permissions = $this->db->delete('user_permissions');

        $this->db->select('a.*');
        $this->db->from('users a');
        $this->db->where('a.id_user <>', '7');
        $get_users = $this->db->get()->result();

        $arr_insert_permissions = [];

        foreach ($get_users as $item_users) :
            $this->db->select('a.id_permission');
            $this->db->from('user_permissions a');
            $this->db->where('a.id_user', '7');
            $get_si_itu = $this->db->get()->result();

            foreach ($get_si_itu as $item_itu) :
                $arr_insert_permissions[] = [
                    'id_user' => $item_users->id_user,
                    'id_permission' => $item_itu->id_permission
                ];
            endforeach;
        endforeach;

        if (!empty($arr_insert_permissions)) {
            $this->db->insert_batch('user_permissions', $arr_insert_permissions);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
        } else {
            $this->db->trans_commit();

            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }
}
