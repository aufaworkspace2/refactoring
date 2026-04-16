<?php
/* Author : Amir Mufid */

class m_mahasiswa extends CI_Model
{
	#variable private (poperpti)
	private $table= 'mahasiswa';
	private $pk = 'ID';
	
	function __construct()
	{
		parent:: __construct();
	}
	
	function get_data_list($limit, $offset, $ProgramID='', $ProdiID='', $StatusMhswID='', $TahunMasuk='',$SemesterMasuk = '', $keyword='')
	{	
		//$kar = get_id($this->session->userdata('EntityID'),'karyawan');
		$user 	= get_id($this->session->userdata('UserID'),'user');
		
		if($user->ProgramID){
			$arrProgram = explode(",", $user->ProgramID);
		}else{
			$arrProgram = null;
		}
		
		if($user->ProdiID){
			$arrProdi = explode(",", $user->ProdiID);
		}else{
			$arrProdi = null;
		}

		if($ProgramID)
		{
			$ProgramID=explode(',',$ProgramID);
			$this->db->where_in('mahasiswa.ProgramID',$ProgramID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProgramID',$arrProgram);	
			}
		} 
		
		if($ProdiID)
		{
			$ProdiID=explode(',', $ProdiID);
			$this->db->where_in('ProdiID', $ProdiID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProdiID',$arrProdi);	
			}
		}
		
		if($StatusMhswID)
			$this->db->where('StatusMhswID', $StatusMhswID);
		if($TahunMasuk) 
			$this->db->where('TahunMasuk', $TahunMasuk);
		if($SemesterMasuk) 
			$this->db->where('SemesterMasuk', $SemesterMasuk);
		if($keyword) {
			$this->db->where('(NPM LIKE "%'.$keyword.'%" OR Nama LIKE "%'.$keyword.'%")');
		}
		if($ID)
			$this->db->where('ID', $ID);
		
		//$this->db->order_by('Nama','ASC');
		
		$this->db->where('jenis_mhsw','mhsw');
		$this->db->order_by('NPM', 'ASC');
		return $this->db->get($this->table, $limit, $offset)->result();
	}

	function count_data_list($ProgramID='', $ProdiID='', $StatusMhswID='', $TahunMasuk='',$SemesterMasuk = '', $keyword='')
	{
		//$kar = get_id($this->session->userdata('EntityID'),'karyawan');
		$user 	= get_id($this->session->userdata('UserID'),'user');
		
		if($user->ProgramID){
			$arrProgram = explode(",", $user->ProgramID);
		}else{
			$arrProgram = null;
		}
		
		if($user->ProdiID){
			$arrProdi = explode(",", $user->ProdiID);
		}else{
			$arrProdi = null;
		}
			
		if($ProgramID)
		{
			$ProgramID=explode(',',$ProgramID);
			$this->db->where_in('mahasiswa.ProgramID',$ProgramID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProgramID',$arrProgram);	
			}
		} 
		
		if($ProdiID)
		{
			$ProdiID=explode(',', $ProdiID);
			$this->db->where_in('ProdiID', $ProdiID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProdiID',$arrProdi);	
			}
		}
		
		if($StatusMhswID)
			$this->db->where('StatusMhswID', $StatusMhswID);
		if($TahunMasuk) 
			$this->db->where('TahunMasuk', $TahunMasuk);
		if($SemesterMasuk) 
			$this->db->where('SemesterMasuk', $SemesterMasuk);
		if($keyword) {
			$this->db->where('(NPM LIKE "%'.$keyword.'%" OR Nama LIKE "%'.$keyword.'%")');
		}
		if($ID)
			$this->db->where('ID', $ID);
		
		//$this->db->order_by('Nama','ASC');
		
		$this->db->where('jenis_mhsw','mhsw');
		$this->db->order_by('NPM', 'ASC');
		return $this->db->count_all_results($this->table);
	}
	
	#Function for get data dan pagination 
	function get_data($limit,$offset,$ProgramID='',$ProdiID='',$KelasID='',$StatusMhswID='',$TahunMasuk='',$JenjangID='',$keyword='',$ID='', $statusPindahan='',$SecondGeneration='',$SemesterMasuk='', $orderby='', $descasc='')
	{
		//$kar = get_id($this->session->userdata('EntityID'),'karyawan');
		$user 	= get_id($this->session->userdata('UserID'),'user');
		
		if($user->ProgramID){
			$arrProgram = explode(",", $user->ProgramID);
		}else{
			$arrProgram = null;
		}
		
		if($user->ProdiID){
			$arrProdi = explode(",", $user->ProdiID);
		}else{
			$arrProdi = null;
		}

		$this->db->select('mahasiswa.*');

		//$this->db->join('rencanastudi', 'rencanastudi.MhswID=mahasiswa.ID','left');

		if($ProgramID)
		{
			$ProgramID=explode(',',$ProgramID);
			$this->db->where_in('mahasiswa.ProgramID',$ProgramID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProgramID',$arrProgram);	
			}
		} 
		
		if($ProdiID)
		{
			$ProdiID=explode(',',$ProdiID);
			$this->db->where_in('mahasiswa.ProdiID',$ProdiID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProdiID',$arrProdi);	
			}
		} 
		
		if($KelasID)
			$this->db->where('mahasiswa.KelasID',$KelasID);
		if($StatusMhswID)
			$this->db->where('mahasiswa.StatusMhswID',$StatusMhswID);
		if($TahunMasuk) 
			$this->db->where('mahasiswa.TahunMasuk',$TahunMasuk);
		if($SemesterMasuk) 
			$this->db->where('mahasiswa.SemesterMasuk',$SemesterMasuk);
		if($JenjangID)
			$this->db->where('mahasiswa.JenjangID',$JenjangID);
		if($ID)
			$this->db->where('mahasiswa.ID',$ID);
		if($keyword)
			$this->db->where('(mahasiswa.NPM LIKE "%'.$keyword.'%" OR mahasiswa.Nama LIKE "%'.$keyword.'%")');
		if(!empty($statusPindahan))
			$this->db->where('mahasiswa.StatusPindahan', $statusPindahan);

		if($SecondGeneration != ""){
			$this->db->where('mahasiswa.SecondGeneration', $SecondGeneration);
		}
		//$this->db->order_by('Nama','ASC');
		if ($orderby != "" && $descasc != "") {
			$this->db->order_by($orderby,$descasc);
		}
		
		$this->db->where('mahasiswa.jenis_mhsw','mhsw');
		$this->db->group_by('mahasiswa.ID');
		$query = $this->db->get($this->table,$limit,$offset)->result();

		$fetched_data = [];
		foreach($query as $row){
			$row->Nama = removeslashes($row->Nama);
			$fetched_data[] = $row;
		}

		return $fetched_data;
	}
	
	function get_data_konversi($limit,$offset,$ProgramID='',$ProdiID='',$StatusMhswID='',$TahunMasuk='',$JenjangID='',$keyword='',$ID='')
	{	
		if($ProgramID)
		$this->db->where('ProgramID',$ProgramID);
		if($ProdiID)
		$this->db->where('ProdiID',$ProdiID); 
		if($StatusMhswID)
		$this->db->where('StatusMhswID',$StatusMhswID);
		if($TahunMasuk) 
		$this->db->where('TahunMasuk',$TahunMasuk);
		if($JenjangID)
		$this->db->where('JenjangID',$JenjangID);
		if($ID)
		$this->db->where('ID',$ID);
		
		if($keyword) {
			$this->db->like('Nama',$keyword,'both');
			$this->db->or_like('NPM',$keyword,'both');
		}
		
		$this->db->where('StatusPindahan','P');
		$this->db->order_by('Nama','ASC');
		
		$this->db->where('jenis_mhsw','mhsw');
		return $this->db->get($this->table,$limit,$offset)->result();
	}
	
	function get_data_pindahan($limit,$offset,$ProgramID='',$ProdiID='',$StatusMhswID='',$TahunMasuk='',$JenjangID='',$keyword='',$ID='')
	{
		$this->db->select('mahasiswa.*');
		if($ProgramID)
		$this->db->where('mahasiswa.ProgramID',$ProgramID);
		if($ProdiID)
		$this->db->where('mahasiswa.ProdiID',$ProdiID); 
		if($StatusMhswID)
		$this->db->where('mahasiswa.StatusMhswID',$StatusMhswID);
		if($TahunMasuk) 
		$this->db->where('mahasiswa.TahunMasuk',$TahunMasuk);
		if($JenjangID)
		$this->db->where('mahasiswa.JenjangID',$JenjangID);
		if($ID)
		$this->db->where('mahasiswa.ID',$ID);
		
		if($keyword) {
			$this->db->like('Nama',$keyword,'both');
			$this->db->or_like('NPM',$keyword,'both');
		}
		
		$where="KodePT != 0";
		$this->db->where($where);
		$this->db->where('StatusPindahan','P');
		$this->db->join('rencanastudi','rencanastudi.MhswID != mahasiswa.ID');		
		$this->db->group_by('mahasiswa.ID');
		$this->db->order_by('Nama','ASC');
		
		$this->db->where('mahasiswa.jenis_mhsw','mhsw');

		return $this->db->get($this->table,$limit,$offset)->result();
	}
	
	function get_data_lulus($limit, $offset, $ProgramID, $ProdiID, $TahunMasuk, $keyword)
	{
		if($ProgramID)
			$this->db->where('ProgramID', $ProgramID);
		if($ProdiID)
			$this->db->where('ProdiID', $ProdiID);
		if($TahunMasuk)
			$this->db->where('TahunMasuk', $TahunMasuk);
		if($keyword) {
			$this->db->group_start();
			$this->db->like('Nama', $keyword, 'both');
			$this->db->or_like('NPM', $keyword, 'both');
			$this->db->group_end();
		}
		$this->db->where('StatusMhswID', '1');
		
		$this->db->where('jenis_mhsw','mhsw');
		$this->db->order_by('NPM', 'ASC');
		return $this->db->get($this->table, $limit, $offset)->result();
	}
	
	function count_all_pindahan($limit,$offset,$ProgramID='',$ProdiID='',$StatusMhswID='',$TahunMasuk='',$JenjangID='',$keyword='',$ID='')
	{
		if($ProgramID)
		$this->db->where('ProgramID',$ProgramID);
		if($ProdiID)
		$this->db->where('ProdiID',$ProdiID); 
		if($StatusMhswID)
		$this->db->where('StatusMhswID',$StatusMhswID);
		if($TahunMasuk) 
		$this->db->where('TahunMasuk',$TahunMasuk);
		if($JenjangID)
		$this->db->where('JenjangID',$JenjangID);
		if($ID)
		$this->db->where('ID',$ID);
		
		if($keyword) {
			$this->db->like('Nama',$keyword,'both');
			$this->db->or_like('NPM',$keyword,'both');
		}
		
		$where="KodePT != 0 OR KodePT IS NOT NULL OR KodePT != ''";
		$this->db->where($where);
		$this->db->where('StatusPindahan','P');
		
		$this->db->where('jenis_mhsw','mhsw');
		$this->db->order_by('Nama','ASC');
		return $this->db->count_all_results($this->table);
	}
	
	#Function for count all total row data
	function count_all($ProgramID='',$ProdiID='',$KelasID='',$StatusMhswID='',$TahunMasuk='',$JenjangID='',$keyword='', $statusPindahan='',$SecondGeneration='',$SemesterMasuk='',$orderby ='', $descasc = '')
	{
		//$kar = get_id($this->session->userdata('EntityID'),'karyawan');
		$user 	= get_id($this->session->userdata('UserID'),'user');
		
		if($user->ProgramID){
			$arrProgram = explode(",", $user->ProgramID);
		}else{
			$arrProgram = null;
		}
		
		if($user->ProdiID){
			$arrProdi = explode(",", $user->ProdiID);
		}else{
			$arrProdi = null;
		}
		
			
		$this->db->select('mahasiswa.*');

		//$this->db->join('rencanastudi', 'rencanastudi.MhswID=mahasiswa.ID','left');

		if($ProgramID)
		{
			$ProgramID=explode(',',$ProgramID);
			$this->db->where_in('mahasiswa.ProgramID',$ProgramID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProgramID',$arrProgram);	
			}
		} 
		
		if($ProdiID)
		{
			$ProdiID=explode(',',$ProdiID);
			$this->db->where_in('mahasiswa.ProdiID',$ProdiID);
		}else{
			if(!in_array('SPR',explode(',',$this->session->userdata('LevelKode')) )){
				$this->db->where_in('mahasiswa.ProdiID',$arrProdi);	
			}
		} 
		
		if($KelasID)
			$this->db->where('mahasiswa.KelasID',$KelasID);
		if($StatusMhswID)
			$this->db->where('mahasiswa.StatusMhswID',$StatusMhswID);
		if($TahunMasuk) 
			$this->db->where('mahasiswa.TahunMasuk',$TahunMasuk);
		if($SemesterMasuk) 
			$this->db->where('mahasiswa.SemesterMasuk',$SemesterMasuk);
		if($JenjangID)
			$this->db->where('mahasiswa.JenjangID',$JenjangID);
		if($keyword)
			$this->db->where('(mahasiswa.NPM LIKE "%'.$keyword.'%" OR mahasiswa.Nama LIKE "%'.$keyword.'%")');
		if(!empty($statusPindahan))
			$this->db->where('mahasiswa.StatusPindahan', $statusPindahan);

		if($SecondGeneration != ""){
			$this->db->where('mahasiswa.SecondGeneration', $SecondGeneration);
		}
		//$this->db->order_by('Nama','ASC');
		if ($orderby && $descasc) {
			$this->db->order_by($orderby,$descasc);
		}		
		$this->db->where('mahasiswa.jenis_mhsw','mhsw');
		
		
		$this->db->where('jenis_mhsw','mhsw');
		$this->db->group_by('mahasiswa.ID');
		return $this->db->count_all_results($this->table);
	}
	
	function count_all_konversi($ProgramID='',$ProdiID='',$StatusMhswID='',$TahunMasuk='',$JenjangID='',$keyword='')
	{	
		if($ProgramID)
		$this->db->where('ProgramID',$ProgramID);
		if($ProdiID)
		$this->db->where('ProdiID',$ProdiID);
		if($StatusMhswID)
		$this->db->where('StatusMhswID',$StatusMhswID);
		if($TahunMasuk)
		$this->db->where('TahunMasuk',$TahunMasuk);
		if($JenjangID)
		$this->db->where('JenjangID',$JenjangID);
		if($keyword)
		$this->db->like('Nama',$keyword,'both');
		$this->db->or_like('NPM',$keyword,'both');
		
		$this->db->where('StatusPindahan','P');
		
		$this->db->where('jenis_mhsw','mhsw');
		return $this->db->count_all_results($this->table);
	}
	
	function count_all_lulus($ProgramID, $ProdiID, $TahunMasuk, $keyword)
	{
		if($ProgramID)
			$this->db->where('ProgramID', $ProgramID);
		if($ProdiID)
			$this->db->where('ProdiID', $ProdiID);
		if($TahunMasuk)
			$this->db->where('TahunMasuk', $TahunMasuk);
		if($keyword) {
			$this->db->group_start();
			$this->db->like('Nama', $keyword, 'both');
			$this->db->or_like('NPM', $keyword, 'both');
			$this->db->group_end();
		}
		$this->db->where('StatusMhswID', '1');
		
		$this->db->where('jenis_mhsw','mhsw');
		$this->db->order_by('NPM', 'ASC');
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
	
	function get_tahun()
	{
		return $this->db->query("SELECT DISTINCT TahunMasuk FROM mahasiswa where NPM is not null and TahunMasuk != '' ORDER BY TahunMasuk DESC")->result();
	}
	function getAll($MhswID,$Fields) {
		$sql = "SELECT ".$Fields." FROM mahasiswa WHERE ID='".$MhswID."' and NPM is not null ORDER BY NPM DESC";
		return $this->db->query($sql)->row();
	}
	
	#Function untuk generate data mahasiswa ke moodle
	public function generate($angkatan,$prodiid){
		$procedure_name = 'mdl_user';
		$type = 'proc';
		$status = 1;
		$params = array(
			$angkatan,
			$prodiid,
			$status
		);
		$return = 'result';
		
		//$result = call_sp($procedure_name, $params, $type ,$return);
		$result = proc_mdl_user($angkatan,$prodiid,$status);
		
		return $result;
	}
	
	#Function untuk generate data mahasiswa ke library
	public function generate_user_library($angkatan,$prodiid){
		$procedure_name = 'generate_user_perpus';
		$type = 'proc';
		$status = 1;
		$params = array(
			$angkatan,
			$prodiid,
			$status
		);
		$return = 'result';
		
		//$result = call_sp($procedure_name, $params, $type ,$return);
		$result = proc_generate_perpus($angkatan,$prodiid,$status);
		
		return $result;
	}

	function insertSekolah($NamaSekolah=''){		
		$insert['nama'] 	= $NamaSekolah;
		$insert['jenjang'] 	= 'SMA';
		$this->db->insert('sekolahdata',$insert);
		return $this->db->insert_id();
	}
}
?>
