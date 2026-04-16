<?php
class m_gelombang_pmb extends CI_Model
{
	#variable private (poperpti)
	private $table= 'pmb_tbl_gelombang';
	private $table2= 'pmb_tbl_gelombang_detail';
	private $pk = 'id';
	
	function __construct()
	{
		parent:: __construct();
	}
	
	#Function for get data dan pagination 
	function get_data($limit,$offset,$keyword='')
	{	
		$this->db->select("$this->table.*, count($this->table2.id) as PendaftaranTerbuka");

		if($keyword){
			$this->db->group_start();
			$this->db->or_like("$this->table.nama", $keyword, "both");
			$this->db->or_like("$this->table.kode", $keyword, "both");
			$this->db->group_end();
		}
		
		$this->db->join($this->table2, "$this->table.id=$this->table2.gelombang_id AND current_date() BETWEEN $this->table2.date_start AND $this->table2.date_end","left");
		$this->db->group_by("$this->table.id");
		$this->db->order_by('Nama','ASC');
		return $this->db->get($this->table,$limit,$offset)->result();
	}
	
	#Function for count all total row data
	function count_all($keyword='')
	{	
		if($keyword){
			$this->db->group_start();
			$this->db->or_like("nama", $keyword, "both");
			$this->db->or_like("kode", $keyword, "both");
			$this->db->group_end();
		}
		
		return $this->db->count_all_results($this->table);
	}

	#Function for get data dan pagination 
	function get_data_detail($limit,$offset,$gelombang_id='',$keyword='')
	{	
		$this->db->select('pmb_tbl_gelombang_detail.*');
		$this->db->join('pmb_pilihan_pendaftaran','pmb_pilihan_pendaftaran.id=pmb_tbl_gelombang_detail.pilihan_pendaftaran_id','left');
		$this->db->join('program','program.ID=pmb_tbl_gelombang_detail.program_id','left');
		$this->db->join('programstudi','programstudi.ID=pmb_tbl_gelombang_detail.prodi_id','left');
		if($gelombang_id)
		$this->db->where("gelombang_id",$gelombang_id);

		if($keyword){
			$this->db->group_start();
			$this->db->or_like("program.Nama", $keyword, "both");
			$this->db->or_like("programstudi.Nama", $keyword, "both");
			$this->db->or_like("pmb_pilihan_pendaftaran.nama", $keyword, "both");
			$this->db->group_end();
		}
		
		$this->db->order_by('pmb_tbl_gelombang_detail.id','ASC');
		return $this->db->get($this->table2,$limit,$offset)->result();
	}
	
	#Function for count all total row data
	function count_all_detail($gelombang_id='',$keyword='')
	{	
		$this->db->select('pmb_tbl_gelombang_detail.*');
		$this->db->join('pmb_pilihan_pendaftaran','pmb_pilihan_pendaftaran.id=pmb_tbl_gelombang_detail.pilihan_pendaftaran_id','left');
		$this->db->join('program','program.ID=pmb_tbl_gelombang_detail.program_id','left');
		$this->db->join('programstudi','programstudi.ID=pmb_tbl_gelombang_detail.prodi_id','left');
		if($gelombang_id)
		$this->db->where("gelombang_id",$gelombang_id);

		if($keyword){
			$this->db->group_start();
			$this->db->or_like("program.Nama", $keyword, "both");
			$this->db->or_like("programstudi.Nama", $keyword, "both");
			$this->db->or_like("pmb_pilihan_pendaftaran.nama", $keyword, "both");
			$this->db->group_end();
		}
		
		$this->db->order_by('pmb_tbl_gelombang_detail.id','ASC');
		
		return $this->db->count_all_results($this->table2);
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

	function get_id_detail($id)
	{
		$this->db->where($this->pk,$id);
		return $this->db->get($this->table2)->row();
	}
	
	#Function for add data
	function add_detail($data)
	{
		return $this->db->insert($this->table2,$data);
	}
	
	#Function for edit data
	function edit_detail($id,$data)
	{
		$this->db->where($this->pk,$id);
		return $this->db->update($this->table2,$data);
	}
	
	#Function for delete/remove data
	function delete_detail($id)
	{
		$this->db->where($this->pk,$id);
		return $this->db->delete($this->table2);
	}

	function get_data_generate($gelombang_id='',$program='',$prodi='',$jalur='',$status='')
	{	

		// get data gelombang 
		$dataGelombang = get_id($gelombang_id,'pmb_tbl_gelombang');
		// get data tahun
		$dataTahun = get_id($dataGelombang->tahun_id,'tahun');

		$this->db->select('biaya_semester.JalurPendaftaran AS jalur,
							jenis_pendaftaran.ID AS jenis_pendaftaran,
							biaya_semester.ProgramID AS program_id,
							biaya_semester.ProdiID AS prodi_id,
							biaya_semester.ID AS biaya_semester_satu_id,
							biaya.ID AS biaya_pendaftaran,
							pmb_tbl_gelombang_detail.ID AS gelombang_detail_id');
		$this->db->join('jenis_pendaftaran','jenis_pendaftaran.Kode = biaya_semester.JenisPendaftaran','inner');
		$this->db->join('biaya','biaya.BiayaSemesterID = biaya_semester.ID AND biaya.JenisBiayaID = 32','inner');				
		$this->db->join('pmb_tbl_gelombang_detail','pmb_tbl_gelombang_detail.gelombang_id = '.$dataGelombang->id.' AND pmb_tbl_gelombang_detail.jalur = biaya_semester.JalurPendaftaran
						AND pmb_tbl_gelombang_detail.jenis_pendaftaran = jenis_pendaftaran.ID
						AND pmb_tbl_gelombang_detail.program_id = biaya_semester.ProgramID
						AND pmb_tbl_gelombang_detail.prodi_id = biaya_semester.ProdiID','left');
		$this->db->join("pmb_pilihan_pendaftaran","pmb_pilihan_pendaftaran.jalur = biaya_semester.JalurPendaftaran AND pmb_pilihan_pendaftaran.program_id = biaya_semester.ProgramID 
						AND pmb_pilihan_pendaftaran.jenis_pendaftaran = jenis_pendaftaran.ID AND pmb_pilihan_pendaftaran.tahun_id =  $dataGelombang->tahun_id","inner");				
		$this->db->where('biaya_semester.TahunMasuk',$dataGelombang->tahunmasuk);
		$this->db->where('biaya_semester.SemesterMasuk',$dataTahun->Semester);
		$this->db->where('biaya_semester.Semester','1');
		$this->db->where('biaya_semester.GelombangKe',$dataGelombang->GelombangKe);

		if($program){
			$this->db->where_in("biaya_semester.ProgramID",$program);
		}
		if($prodi){
			$this->db->where_in("biaya_semester.ProdiID",$prodi);
		}
		if($jalur){
			$this->db->where_in("biaya_semester.JalurPendaftaran",$jalur);
		}

		if($status){
			if($status == 1){
				$this->db->where('pmb_tbl_gelombang_detail.ID != ',null);
			}else if($status == 2){
				$this->db->where('pmb_tbl_gelombang_detail.ID',null);
			}
		}
		
		return $this->db->get('biaya_semester')->result();
	}

		function count_data_generate($gelombang_id='',$program='',$prodi='',$jalur='',$status='')
	{	

		// get data gelombang 
		$dataGelombang = get_id($gelombang_id,'pmb_tbl_gelombang');
		// get data tahun
		$dataTahun = get_id($dataGelombang->tahun_id,'tahun');

		$this->db->select('biaya_semester.JalurPendaftaran AS jalur,
							jenis_pendaftaran.ID AS jenis_pendaftaran,
							biaya_semester.ProgramID AS program_id,
							biaya_semester.ProdiID AS prodi_id,
							biaya_semester.ID AS biaya_semester_satu_id');//pmb_pilihan_pendaftaran.id AS pilihan_pendaftaran_id,
		$this->db->join('jenis_pendaftaran','jenis_pendaftaran.Kode = biaya_semester.JenisPendaftaran','inner');
		$this->db->join('biaya','biaya.BiayaSemesterID = biaya_semester.ID AND biaya.JenisBiayaID = 32','inner');			
		$this->db->join("pmb_pilihan_pendaftaran","pmb_pilihan_pendaftaran.jalur = biaya_semester.JalurPendaftaran AND pmb_pilihan_pendaftaran.program_id = biaya_semester.ProgramID 
						AND pmb_pilihan_pendaftaran.jenis_pendaftaran = jenis_pendaftaran.ID AND pmb_pilihan_pendaftaran.tahun_id =  $dataGelombang->tahun_id","inner");			
		$this->db->join('pmb_tbl_gelombang_detail','pmb_tbl_gelombang_detail.gelombang_id = '.$dataGelombang->id.' AND pmb_tbl_gelombang_detail.jalur = biaya_semester.JalurPendaftaran
						AND pmb_tbl_gelombang_detail.jenis_pendaftaran = jenis_pendaftaran.ID
						AND pmb_tbl_gelombang_detail.program_id = biaya_semester.ProgramID
						AND pmb_tbl_gelombang_detail.prodi_id = biaya_semester.ProdiID','left');
		$this->db->where('biaya_semester.TahunMasuk',$dataGelombang->tahunmasuk);
		$this->db->where('biaya_semester.SemesterMasuk',$dataTahun->Semester);
		$this->db->where('biaya_semester.Semester','1');
		$this->db->where('biaya_semester.GelombangKe',$dataGelombang->GelombangKe);

		if($program){
			$this->db->where_in("biaya_semester.ProgramID",$program);
		}
		if($prodi){
			$this->db->where_in("biaya_semester.ProdiID",$prodi);
		}
		if($jalur){
			$this->db->where_in("biaya_semester.JalurPendaftaran",$jalur);
		}

		if($status){
			if($status == 1){
				$this->db->where('pmb_tbl_gelombang_detail.ID != ',null);
			}else if($status == 2){
				$this->db->where('pmb_tbl_gelombang_detail.ID',null);
			}
		}
		
		return $this->db->count_all_results('biaya_semester');
	}
}
?>