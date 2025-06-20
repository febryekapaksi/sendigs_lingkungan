<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Pr_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $table_name = 'ms_inventory_category3';
	protected $key        = 'id';

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
	protected $set_created = true;

	/**
	 * @var bool Set the modified time automatically on editing a record (if true)
	 */
	protected $set_modified = true;
	/**
	 * @var string The type of date/time field used for $created_field and $modified_field.
	 * Valid values are 'int', 'datetime', 'date'.
	 */
	/**
	 * @var bool Enable/Disable soft deletes.
	 * If false, the delete() method will perform a delete of that row.
	 * If true, the value in $deleted_field will be set to 1.
	 */
	protected $soft_deletes = true;

	protected $date_format = 'datetime';

	/**
	 * @var bool If true, will log user id in $created_by_field, $modified_by_field,
	 * and $deleted_by_field.
	 */
	protected $log_user = true;

	/**
	 * Function construct used to load some library, do some actions, etc.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	function generate_code($tgl)
	{
		$query = $this->db->query("SELECT MAX(no_po) as max_id FROM tr_purchase_order WHERE no_po LIKE '%P" . date('y', strtotime($tgl)) . "%'");
		$row = $query->row_array();
		$thn = date('y', strtotime($tgl));
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 3, 5);
		$counter = $max_id1 + 1;
		$idcust = "P" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
		return $idcust;
	}
	function BuatNomor($tgl)
	{
		$bulan = date('m', strtotime($tgl));
		$tahun = date('Y', strtotime($tgl));
		$blnthn = date('Y-m');
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_purchase_order WHERE month(tanggal)='$bulan' and Year(tanggal)='$tahun'");
		$row = $query->row_array();
		$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, -13, 3);
		$counter = $max_id1 + 1;
		$idcust = "PO-" . sprintf("%03s", $counter) . "/MP/" . $bulan . "/" . $tahun;
		return $idcust;
	}


	public function CariPenawaran()
	{
		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_penawaran a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->order_by('a.no_penawaran', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function getHeaderPenawaran($id)
	{
		$this->db->select('a.*, b.name_customer as name_customer, b.address_office as address_office, b.telephone as telephone,b.fax as fax');
		$this->db->from('tr_penawaran a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->where('a.no_penawaran', $id);
		$query = $this->db->get();
		return $query->result();
	}
	public function PrintDetail($id)
	{
		$this->db->select('a.*, b.nama as nama3,b.hardness as hardness, c.nama as nama2 , d.nilai_dimensi as nilai');
		$this->db->from('child_penawaran a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3=a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2=b.id_category2');
		$this->db->join('child_inven_dimensi d', 'd.id_category3=a.id_category3');
		$this->db->where('a.no_penawaran', $id);
		$query = $this->db->get();
		return $query->result();
	}
	function level_2($inventory_1)
	{
		$search = "deleted='0' and id_type='$inventory_1'";
		$this->db->where($search);
		$this->db->order_by('id_category1', 'ASC');
		return $this->db->from('ms_inventory_category1')
			->get()
			->result();
	}
	function level_3($id_inventory2)
	{
		$search = "deleted='0' and id_category1='$id_inventory2'";
		$this->db->where($search);
		$this->db->order_by('id_category2', 'ASC');
		return $this->db->from('ms_inventory_category2')
			->get()
			->result();
	}
	function compotition($id_inventory2)
	{
		$search = "deleted='0' and id_category1='$id_inventory2'";
		$this->db->where($search);
		$this->db->order_by('id_compotition', 'ASC');
		return $this->db->from('ms_compotition')
			->get()
			->result();
	}
	function bentuk($id_bentuk)
	{
		$search = "deleted='0' and id_bentuk='$id_bentuk'";
		$this->db->where($search);
		$this->db->order_by('id_dimensi', 'ASC');
		return $this->db->from('ms_dimensi')
			->get()
			->result();
	}
	function level_4($id_inventory3)
	{
		$this->db->where('id_category2', $id_inventory3);
		$this->db->order_by('id_category3', 'ASC');
		return $this->db->from('ms_inventory_category3')
			->get()
			->result();
	}

	public function get_data($table, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	function getById($id)
	{
		return $this->db->get_where('inven_lvl2', array('id_inventory2' => $id))->row_array();
	}

	public function get_data_category()
	{
		$search = "a.deleted='0'";
		$this->db->select('a.*, b.nama as nama_category2, c.nilai_dimensi as nilai_dimensi,d.nm_bentuk as nm_bentuk');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_category2 b', 'b.id_category2 =a.id_category2');
		$this->db->join('child_inven_dimensi c', 'c.id_category3 =a.id_category3');
		$this->db->join('ms_bentuk d', 'd.id_bentuk =a.id_bentuk');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}

	public function getpenawaran($id)
	{
		$search = "a.no_penawaran='$id' ";
		$this->db->select('a.*, b.nama as nama_category3, b.hardness as hardness, c.nama as nama_category2, d.nilai_dimensi as thickness');
		$this->db->from('child_penawaran a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3 =a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2 =b.id_category2');
		$this->db->join('child_inven_dimensi d', 'd.id_category3 =b.id_category3');
		$this->db->where('a.no_penawaran', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function getview($id)
	{
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_type b', 'b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d', 'd.id_category2 =a.id_category2');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_child_compotition($id)
	{
		$this->db->select('a.*, b.name_compotition as name_compotition');
		$this->db->from('dt_material_compotition a');
		$this->db->join('ms_material_compotition b', 'b.id_compotition=a.id_compotition');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}
	public function get_child_dimention($id)
	{
		$this->db->select('a.*, b.dimensi_bentuk as dimensi_bentuk');
		$this->db->from('dt_material_dimensi a');
		$this->db->join('child_dimensi_bentuk b', 'b.id_dimensi_bentuk=a.id_dimensi_bentuk');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}
	public function get_child_suplier($id)
	{
		$this->db->select('a.*, b.name_supplier as name_supplier');
		$this->db->from('dt_material_supplier a');
		$this->db->join('master_supplier b', 'b.id_supplier=a.id_supplier');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function getSpek($id)
	{
		$this->db->select('a.*, b.name_compotition as name_compotition');
		$this->db->from('dt_material_compotition a');
		$this->db->join('ms_material_compotition b', 'b.id_compotition = a.id_compotition');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function cariPurchaserequest($filter_status = null)
	{
		// $this->db->select('a.*, b.nm_lengkap as nama_user');
		// $this->db->from('material_planning_base_on_produksi a');
		// $this->db->join('users b', 'b.id_user = a.booking_by', 'left');
		// $this->db->join('tr_purchase_order c', 'c.no_po = a.po_number', 'left');
		// $this->db->where_in('c.status', array('1', null));
		// $this->db->where('a.app_req', '1');
		// $this->db->where('a.app_po_req', '0');

		// if($filter_status !== null){
		// 	if($filter_status == '1'){
		// 		$query = $this->db->query('
		// 			SELECT 
		// 				a.so_number as so_number,
		// 				a.tgl_so as tgl_so, 
		// 				b.nm_lengkap as nama_user
		// 			FROM
		// 				material_planning_base_on_produksi a
		// 				LEFT JOIN users b ON b.id_user = a.booking_by
		// 			WHERE
		// 				(SELECT COUNT(x.id) as hitung FROM material_planning_base_on_produksi_detail x WHERE x.so_number = a.so_number ) > 0 AND
		// 				(SELECT COUNT(ab.id) FROM dt_trans_po ab WHERE ab.idpr = a.id AND ab.tipe IS NULL) < 1
		// 		');
		// 	} else if($filter_status == '2'){
		// 		$query = $this->db->query('
		// 			SELECT 
		// 				a.so_number as so_number,
		// 				a.tgl_so as tgl_so, 
		// 				b.nm_lengkap as nama_user
		// 			FROM
		// 				material_planning_base_on_produksi a
		// 				LEFT JOIN users b ON b.id_user = a.booking_by
		// 			WHERE
		// 				(SELECT COUNT(x.id) as hitung FROM material_planning_base_on_produksi_detail x WHERE x.so_number = a.so_number) > 0 AND
		// 				(SELECT COUNT(ab.id) FROM dt_trans_po ab WHERE ab.idpr = a.id AND ab.tipe IS NULL) > 0 AND
		// 				(SELECT COUNT(ab.id) FROM dt_trans_po ab WHERE ab.idpr = a.id AND ab.tipe IS NULL) <> (SELECT COUNT(x.id) as hitung FROM material_planning_base_on_produksi_detail x WHERE x.so_number = a.so_number)
		// 		');
		// 	} else {
		// 		$query = $this->db->query('
		// 			SELECT 
		// 				a.so_number as so_number,
		// 				a.tgl_so as tgl_so, 
		// 				b.nm_lengkap as nama_user
		// 			FROM
		// 				material_planning_base_on_produksi a
		// 				LEFT JOIN users b ON b.id_user = a.booking_by
		// 			WHERE
		// 				(SELECT COUNT(x.id) as hitung FROM material_planning_base_on_produksi_detail x WHERE x.so_number = a.so_number ) > 0
		// 		');
		// 	}
		// }else{
		// 	$query = $this->db->query('
		// 		SELECT 
		// 			a.so_number as so_number,
		// 			a.tgl_so as tgl_so, 
		// 			b.nm_lengkap as nama_user
		// 		FROM
		// 			material_planning_base_on_produksi a
		// 			LEFT JOIN users b ON b.id_user = a.booking_by
		// 		WHERE
		// 			(SELECT COUNT(x.id) as hitung FROM material_planning_base_on_produksi_detail x WHERE x.so_number = a.so_number ) > 0
		// 	');
		// }

		$query = $this->db->query('
			SELECT 
				a.so_number as so_number,
				a.no_pr as no_pr,
				a.tgl_so as tgl_so, 
				b.nm_lengkap as nama_user,
				"pr material" as tipe_pr
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN users b ON b.id_user = a.booking_by
			WHERE
				(SELECT COUNT(x.id) as hitung FROM material_planning_base_on_produksi_detail x WHERE x.so_number = a.so_number ) > 0 AND 
				a.metode_pembelian = "1" AND 
				a.close_pr IS NULL

			UNION ALL
			
			SELECT 
				a.no_pengajuan as so_number,
				a.no_pr as no_pr,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl_so,
				b.nm_lengkap as nama_user,
				"pr depart" as tipe_pr
			FROM
				rutin_non_planning_header a
				LEFT JOIN users b ON b.id_user = a.created_by
			WHERE
				a.sts_app = "Y" AND
				a.metode_pembelian = "1" AND
				a.close_pr IS NULL

			UNION ALL

			SELECT 
				a.id as so_number,
				a.no_pr as no_pr,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl_so,
				b.nm_lengkap as nama_user,
				"pr asset" as tipe_pr
			FROM
				tran_pr_header a
				LEFT JOIN users b ON b.id_user = a.created_by
			WHERE
				a.app_status_3 = "Y" AND
				a.metode_pembelian = "1" AND
				a.close_pr IS NULL

			UNION ALL

			SELECT
				a.id as so_number,
				a.id as no_pr,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl_so,
				b.nm_lengkap as nama_user,
				"project consultant" as tipe_pe
			FROM
				' . DBCNL . '.kons_tr_kasbon_project_header a
				LEFT JOIN ' . DBCNL . '.users b ON b.id_user = a.created_by
			WHERE
				a.metode_pembayaran = "3" AND
				a.sts = "1"
		')->result();

		// $query = $this->db->get();
		// print_r($query);
		// exit;
		return $query;
	}


	function get_where_in($field, $kunci, $tabel)
	{
		$this->db->where_in($field, $kunci);
		$query = $this->db->get($tabel);
		return $query->result();
	}

	function get_where_in_and($field, $kunci, $and, $tabel)
	{
		$this->db->where_in($field, $kunci);
		$this->db->where($and);
		$query = $this->db->get($tabel);
		return $query->result();
	}
}
