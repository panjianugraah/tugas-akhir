import re
from pathlib import Path
from docx import Document
from docx.shared import Cm, Pt, RGBColor
from docx.enum.section import WD_ORIENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

src=Path(r'C:\Users\ASUS\Downloads\tugas_akhir (1).sql')
out=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir\Dekomposisi_Data_SIANGKASA.docx')
sql=src.read_text(encoding='utf-8-sig')
tables=['users','mata_pelajaran','guru','kelas','periode_ppdb','data_ppdb','siswa','pengumuman','galeri','repository','repository_settings']
labels={'users':'Menyimpan akun pengguna sistem.','mata_pelajaran':'Menyimpan daftar mata pelajaran.','guru':'Menyimpan data guru.','kelas':'Menyimpan data kelas dan wali kelas.','periode_ppdb':'Menyimpan periode pendaftaran PPDB.','data_ppdb':'Menyimpan data pendaftar PPDB.','siswa':'Menyimpan data siswa.','pengumuman':'Menyimpan pengumuman sekolah.','galeri':'Menyimpan data gambar galeri.','repository':'Menyimpan metadata dokumen repository.','repository_settings':'Menyimpan pengaturan kode akses repository.'}
desc={'id':'Identitas data','name':'Nama akun','email':'Alamat email','email_verified_at':'Waktu verifikasi email','password':'Kata sandi akun','two_factor_secret':'Rahasia autentikasi dua faktor','two_factor_recovery_codes':'Kode pemulihan dua faktor','two_factor_confirmed_at':'Waktu konfirmasi dua faktor','remember_token':'Token ingat saya','created_at':'Waktu pembuatan data','updated_at':'Waktu pembaruan data','nama':'Nama mata pelajaran','nip':'Nomor induk pegawai','nama_lengkap':'Nama lengkap','jenis_kelamin':'Jenis kelamin','tempat_lahir':'Tempat lahir','tanggal_lahir':'Tanggal lahir','mata_pelajaran':'Keterangan mata pelajaran','mata_pelajaran_id':'Identitas mata pelajaran','no_hp':'Nomor telepon','alamat':'Alamat','status':'Status data','nama_kelas':'Nama kelas','tingkat':'Tingkat kelas','tahun_ajaran':'Tahun ajaran','kapasitas':'Kapasitas kelas','wali_kelas_id':'Identitas guru wali kelas','nama_periode':'Nama periode PPDB','tanggal_mulai':'Waktu mulai pendaftaran','tanggal_selesai':'Waktu akhir pendaftaran','kuota':'Kuota pendaftaran','nisn':'Nomor induk siswa nasional','asal_sekolah':'Sekolah asal','nama_orang_tua':'Nama orang tua','periode_ppdb_id':'Identitas periode PPDB','ppdb_id':'Identitas pendaftar PPDB','kelas_id':'Identitas kelas','nis':'Nomor induk siswa','judul':'Judul konten','slug':'Slug pengumuman','isi':'Isi pengumuman','gambar':'Lokasi berkas gambar','berlaku_sampai':'Tanggal akhir berlaku','created_by':'Identitas pembuat konten','deskripsi':'Deskripsi konten','path':'Lokasi berkas repository','nama_file':'Nama berkas','ekstensi':'Ekstensi berkas','ukuran':'Ukuran berkas','kode_akses':'Kode akses repository','versi':'Versi akses repository'}
doc=Document(); sec=doc.sections[0]; sec.orientation=WD_ORIENT.LANDSCAPE; sec.page_width,sec.page_height=sec.page_height,sec.page_width
sec.top_margin=sec.bottom_margin=Cm(1.6); sec.left_margin=sec.right_margin=Cm(1.7)
normal=doc.styles['Normal'];normal.font.name='Arial';normal.font.size=Pt(9)
title=doc.add_paragraph(style='Title'); title.add_run('Dekomposisi Data SIANGKASA');
for r in title.runs:r.font.color.rgb=RGBColor(0,0,0);r.font.size=Pt(20)
doc.add_paragraph('Dokumen ini menjelaskan struktur tabel utama SIANGKASA berdasarkan definisi CREATE TABLE dan ALTER TABLE pada berkas SQL. Kolom Null menunjukkan apakah nilai NULL diperbolehkan. Tanda — pada Default berarti tidak terdapat klausa DEFAULT eksplisit. Nilai enum dicantumkan pada tipe data.')
doc.add_paragraph('PK: primary key; FK: foreign key; UQ: unique; AI: auto increment. Nama kelas dan tahun ajaran pada tabel kelas memiliki constraint unik gabungan.')
def shade(cell,fill):
 tcPr=cell._tc.get_or_add_tcPr(); sh=OxmlElement('w:shd');sh.set(qn('w:fill'),fill);tcPr.append(sh)
def set_width(cell,cm):cell.width=Cm(cm);cell._tc.get_or_add_tcPr().get_or_add_tcW().set(qn('w:w'),str(int(cm/2.54*1440)))
widths=[4.05,5.15,1.15,2.25,6.15,5.9]
count=0
for tn in tables:
 create=re.search(r'CREATE TABLE\s+`'+re.escape(tn)+r'`\s*\((.*?)\)\s*ENGINE=',sql,re.S|re.I)
 assert create,tn
 alter=re.findall(r'ALTER TABLE\s+`'+re.escape(tn)+r'`\s*(.*?);',sql,re.S|re.I)
 alt='\n'.join(alter); keyinfo={}
 def add(col,val):keyinfo.setdefault(col,[]).append(val)
 for m in re.finditer(r'ADD PRIMARY KEY\s*\(([^)]+)\)',alt,re.I):
  for col in re.findall(r'`([^`]+)`',m.group(1)):add(col,'PK')
 for m in re.finditer(r'ADD UNIQUE KEY\s+`[^`]+`\s*\(([^)]+)\)',alt,re.I):
  cols=re.findall(r'`([^`]+)`',m.group(1))
  for col in cols:add(col,'UQ' if len(cols)==1 else 'UQ gabungan: '+', '.join(cols))
 for m in re.finditer(r'FOREIGN KEY\s*\(`([^`]+)`\)\s*REFERENCES\s*`([^`]+)`\s*\(`([^`]+)`\)([^,;\n]*)',alt,re.I):
  s='FK → '+m.group(2)+'.'+m.group(3); actions=m.group(4).strip()
  if actions:s+=' ('+actions+')'
  add(m.group(1),s)
 for m in re.finditer(r'MODIFY\s+`([^`]+)`[^,;\n]*AUTO_INCREMENT',alt,re.I):add(m.group(1),'AI')
 rows=[]
 for line in create.group(1).splitlines():
  m=re.match(r'\s*`([^`]+)`\s+(enum\([^)]*\)|[a-z]+(?:\(\d+\))?(?:\s+UNSIGNED)?)(.*?)(?:,)?\s*$',line,re.I)
  if not m:continue
  name,typ,rest=m.groups(); nullable='Tidak' if re.search(r'\bNOT NULL\b',rest,re.I) else 'Ya'
  dm=re.search(r'\bDEFAULT\s+(NULL|CURRENT_TIMESTAMP(?:\(\))?|\'[^\']*\'|[^\s,]+)',rest,re.I)
  default=dm.group(1) if dm else '—'
  rows.append((name,typ,nullable,default,'; '.join(keyinfo.get(name,[])) or '—',desc.get(name,name.replace('_',' ').capitalize())))
 assert rows,tn
 if count:doc.add_page_break()
 count+=1
 p=doc.add_paragraph();p.paragraph_format.keep_with_next=True;run=p.add_run(f'Tabel {count}. Struktur tabel {tn}');run.bold=True;run.font.size=Pt(13)
 doc.add_paragraph(labels[tn])
 t=doc.add_table(rows=1,cols=6);t.autofit=False;t.style='Table Grid'
 for cell,txt,w in zip(t.rows[0].cells,['Nama field','Tipe data dan nilai','Null','Default','Constraint','Keterangan'],widths):
  set_width(cell,w);cell.text=txt;shade(cell,'D9EAF7')
  for r in cell.paragraphs[0].runs:r.bold=True
 t.rows[0]._tr.get_or_add_trPr().append(OxmlElement('w:tblHeader'))
 for row in rows:
  cells=t.add_row().cells
  for c,v,w in zip(cells,row,widths):set_width(c,w);c.text=v;c.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
  cells[0]._tc.getparent().get_or_add_trPr().append(OxmlElement('w:cantSplit'))
 for row in t.rows:
  for c in row.cells:
   for p in c.paragraphs:
    p.paragraph_format.space_before=Pt(0);p.paragraph_format.space_after=Pt(1)
    for run in p.runs:run.font.name='Arial';run.font.size=Pt(8)
doc.save(out)
print('Output:',out,'tabel:',count)
