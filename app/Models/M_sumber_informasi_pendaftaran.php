<?php

class m_sumber_informasi_pendaftaran extends CI_Model
{
	#variable private (poperpti)
	private $table= 'pmb_tbl_referensi_daftar';
	private $pk = 'id_ref_daftar';
	
	function __construct()
	{
		parent:: __construct();
	}
	
	#Function for get data dan pagination 
	function get_data($limit,$offset,$keyword='')
	{	
		$this->db->select('*');
		if($keyword)
		$this->db->where("(".$this->table.".nama_ref like '%".$keyword."%') ");
		
		$this->db->order_by($this->table.'.nama_ref','ASC');
		$this->db->group_by($this->table.'.id_ref_daftar');
		return $this->db->get($this->table,$limit,$offset)->result();
	}
	
	#Function for count all total row data
	function count_all($keyword='')
	{	
		if($keyword)
		$this->db->where("(nama_ref like '%".$keyword."%') ");
		
		return $this->db->count_all_results($this->table);
	}

	
	#Function for get data with id
	function get_id($id)
	{
		$this->db->where($this->pk,$id);
		return $this->db->get($this->table)->row();
	}
	
	#Function for add data
	function add($data)
	{
		return $this->db->insert($this->table,$data);
	}
	
	#Function for edit data
	function edit($id,$data)
	{
		$this->db->where($this->pk,$id);
		return $this->db->update($this->table,$data);
	}
	
	#Function for delete/remove data
	function delete($id)
	{
		$this->db->where($this->pk,$id);
		return $this->db->delete($this->table);
	}
}
?>