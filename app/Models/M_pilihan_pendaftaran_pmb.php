<?php

class m_pilihan_pendaftaran_pmb extends CI_Model
{
	#variable private (poperpti)
	private $table= 'pmb_pilihan_pendaftaran';
	private $pk = 'id';
	
	function __construct()
	{
		parent:: __construct();
	}
	
	#Function for get data dan pagination 
	function get_data($limit,$offset,$keyword='',$tahun_id='')
	{	
		$this->db->select($this->table.'.*,COUNT(pmb_tbl_gelombang_detail.id) AS jumlah_gelombang_detail');
		if($keyword){
			$this->db->group_start();
			$this->db->or_like($this->table.".nama",$keyword);
			$this->db->group_end();
		}

		if($tahun_id)
		$this->db->where($this->table.".tahun_id",$tahun_id);

		$this->db->join('pmb_tbl_gelombang_detail','pmb_tbl_gelombang_detail.pilihan_pendaftaran_id = '.$this->table.'.id','LEFT');
		
		$this->db->order_by($this->table.'.id','ASC');
		$this->db->group_by($this->table.'.id');
		return $this->db->get($this->table,$limit,$offset)->result();
	}
	
	#Function for count all total row data
	function count_all($keyword='',$tahun_id='')
	{	
		if($keyword){
			$this->db->group_start();
			$this->db->or_like("nama",$keyword);
			$this->db->group_end();
		}

		if($tahun_id)
		$this->db->where("tahun_id",$tahun_id);
		
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