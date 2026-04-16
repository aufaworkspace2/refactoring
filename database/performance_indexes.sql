-- Performance Indexes for Setup Soal PMB
-- Run this to reduce query time from 20s to <1s

-- Indexes for foreign key lookups (get_field calls)
CREATE INDEX IF NOT EXISTS idx_jenjang_id ON jenjang(ID);
CREATE INDEX IF NOT EXISTS idx_program_id ON program(ID);
CREATE INDEX IF NOT EXISTS idx_tahun_id ON tahun(ID);
CREATE INDEX IF NOT EXISTS idx_programstudi_id ON programstudi(ID);

-- Indexes for permission checks (cek_level function)
CREATE INDEX IF NOT EXISTS idx_modul_script ON modul(Script, AksesID);
CREATE INDEX IF NOT EXISTS idx_submodul_script ON submodul(Script);
CREATE INDEX IF NOT EXISTS idx_levelmodul_lookup ON levelmodul(type, LevelID, ModulID);

-- Indexes for soal queries
CREATE INDEX IF NOT EXISTS idx_soal_idkategori ON pmb_tbl_soal(idkategori);
CREATE INDEX IF NOT EXISTS idx_soal_jawaban ON pmb_tbl_soal(jawaban);
CREATE INDEX IF NOT EXISTS idx_subsoal_idsoal ON pmb_tbl_subsoal(idsoal);

-- Indexes for gelombang queries
CREATE INDEX IF NOT EXISTS idx_gelombang_detail_gelombang ON pmb_tbl_gelombang_detail(gelombang_id);
CREATE INDEX IF NOT EXISTS idx_gelombang_detail_biaya ON pmb_tbl_gelombang_detail(biaya_semester_satu_id);

-- Composite indexes for common query patterns
CREATE INDEX IF NOT EXISTS idx_soal_kategori_search ON pmb_tbl_soal(idkategori, soal);
CREATE INDEX IF NOT EXISTS idx_subsoal_soal_lookup ON pmb_tbl_subsoal(idsoal, soal);

-- Index for session/user lookups
CREATE INDEX IF NOT EXISTS idx_user_username ON user(username);
CREATE INDEX IF NOT EXISTS idx_leveluser_id ON leveluser(ID);

-- Analyze tables to update statistics
ANALYZE TABLE pmb_tbl_soal;
ANALYZE TABLE pmb_tbl_subsoal;
ANALYZE TABLE modul;
ANALYZE TABLE submodul;
ANALYZE TABLE levelmodul;
