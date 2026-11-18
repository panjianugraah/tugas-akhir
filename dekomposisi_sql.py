from pathlib import Path
import re
from docx import Document
from docx.shared import Cm,Pt,RGBColor
from docx.enum.section import WD_ORIENT
from docx.enum.table import WD_TABLE_ALIGNMENT,WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

source=Path(r'C:\Users\ASUS\Downloads\tugas_akhir (1).sql')
sql=source.read_text(encoding='utf-8-sig')
tables=['users','mata_pelajaran','guru','kelas','periode_ppdb','data_ppdb','siswa','pengumuman','galeri','repository','repository_settings']
dd={'id':'Identitas unik data.','name':'Nama pengguna.','email':'Alamat email.','password':'Kata sandi yang disimpan dalam bentuk hash.','email_verified_at':'Waktu verifikasi email.','two_factor_secret':'Rahasia autentikasi dua faktor.','two_factor_recovery_codes':'Kode pemulihan autentikasi dua faktor.','two_factor_confirmed_at':'Waktu konfirmasi autentikasi dua faktor.','remember_token':'Token fitur ingat pengguna.','nama':'Nama mata pelajaran.','nip':'Nomor induk pegawai guru.','nama_lengkap':'Nama lengkap.','jenis_kelamin':'Jenis kelamin.','tempat_lahir':'Tempat lahir.','tanggal_lahir':'Tanggal lahir.','mata_pelajaran':'Keterangan mata pelajaran guru.','no_hp':'Nomor telepon.','alamat':'Alamat.','status':'Status data.','mata_pelajaran_id':'Referensi mata pelajaran.','wali_kelas_id':'Referensi guru wali kelas.','kelas_id':'Referensi kelas siswa.','ppdb_id':'Referensi pendaftaran PPDB siswa.','nama_kelas':'Nama kelas.','tingkat':'Tingkat kelas.','tahun_ajaran':'Tahun ajaran.','kapasitas':'Kapasitas kelas.','nama_periode':'Nama periode PPDB.','tanggal_mulai':'Tanggal dan waktu mulai periode PPDB.','tanggal_selesai':'Tanggal dan waktu selesai periode PPDB.','kuota':'Kuota periode PPDB.','periode_ppdb_id':'Referensi periode PPDB.','nisn':'Nomor induk siswa nasional.','nis':'Nomor induk siswa.','asal_sekolah':'Sekolah asal pendaftar.','nama_orang_tua':'Nama orang tua.','judul':'Judul konten.','slug':'Identitas teks untuk alamat pengumuman.','isi':'Isi pengumuman.','gambar':'Lokasi berkas gambar.','berlaku_sampai':'Tanggal akhir keberlakuan pengumuman.','created_by':'Referensi pengguna pembuat konten.','deskripsi':'Deskripsi konten.','path':'Lokasi berkas repository.','nama_file':'Nama berkas.','ekstensi':'Ekstensi berkas.','ukuran':'Ukuran berkas dalam byte.','kode_akses':'Kode akses repository.','versi':'Versi akses repository.','created_at':'Waktu pembuatan data.','updated_at':'Waktu pembaruan data.'}
cre=re.compile(r'CREATE TABLE\s+`([^`]+)`\s*\((.*?)\) ENGINE=',re.S|re.I)
creates={m.group(1):m.group(2) for m in cre.finditer(sql)}
alters={t:re.findall(r'ALTER TABLE\s+`'+re.escape(t)+r'`\s*(.*?);',sql,re.I|re.S) for t in tables}
def readtable(t):
 assert t in creates,t
 joined='\n'.join(alters[t]); rows=[]
 pks=set(re.findall(r'ADD PRIMARY KEY\s*\(`([^`]+)`\)',joined,re.I))
 uniques={}
 for m in re.finditer(r'ADD UNIQUE KEY\s+`[^`]+`\s*\(([^)]+)\)',joined,re.I):
  cols=re.findall(r'`([^`]+)`',m.group(1))
  for col in cols:uniques[col]='Unique' if len(cols)==1 else 'Unique bersama '+', '.join(x for x in cols if x!=col)
 fks={}
 for m in re.finditer(r'FOREIGN KEY\s*\(`([^`]+)`\)\s*REFERENCES\s*`([^`]+)`\s*\(`([^`]+)`\)([^,;]*)',joined,re.I):
  action=re.search(r'ON DELETE\s+(SET NULL|CASCADE|RESTRICT|NO ACTION)',m.group(4),re.I)
  fks[m.group(1)]='Foreign Key ke '+m.group(2)+'.'+m.group(3)+((' (ON DELETE '+action.group(1).upper()+')') if action else '')
 auto=set(re.findall(r'MODIFY\s+`([^`]+)`[^;\n]*\bAUTO_INCREMENT',joined,re.I))
 for line in creates[t].splitlines():
  m=re.match(r'\s*`([^`]+)`\s+((?:enum\([^)]*\)|[a-z]+(?:\(\d+(?:,\d+)?\))?)(?:\s+UNSIGNED)?)(.*?)(?:,)?\s*$',line,re.I)
  if not m:continue
  field,typ,rest=m.groups(); typ=re.sub(r'\s+UNSIGNED',' unsigned',typ,flags=re.I)
  nullable=not bool(re.search(r'\bNOT NULL\b',rest,re.I)); constraints=[]
  if field in pks:constraints.append('Primary Key')
  if field in fks:constraints.append(fks[field])
  if field in uniques:constraints.append(uniques[field])
  if not nullable:constraints.append('Not Null')
  if field in auto:constraints.append('Auto Increment')
  default=re.search(r'\bDEFAULT\s+(NULL|CURRENT_TIMESTAMP(?:\(\))?|\'[^\']*\'|[^\s,]+)',rest,re.I)
  value=default.group(1) if default else ('NULL (implisit)' if nullable else '-')
  if field in auto:value='Auto Increment'
  if typ.lower().startswith('enum'): rang=typ[5:-1].replace("'",'')
  elif typ.lower().startswith('varchar'):rang='Maks. '+re.search(r'\d+',typ).group()+' karakter'
  elif typ.lower()=='bigint unsigned':rang='0 sampai 18446744073709551615'
  elif typ.lower()=='int unsigned':rang='0 sampai 4294967295'
  elif typ.lower().startswith('tinyint unsigned'):rang='0 sampai 255'
  elif typ.lower().startswith('int'):rang='-2147483648 sampai 2147483647'
  elif typ.lower()=='date':rang='Tanggal (YYYY-MM-DD)'
  elif typ.lower() in ('datetime','timestamp'):rang='Tanggal dan waktu'
  else:rang='Teks bebas' if 'text' in typ.lower() else '-'
  rows.append((field,typ,'Ya' if nullable else 'Tidak',', '.join(constraints) or '-',rang,value,dd.get(field,'Data '+field.replace('_',' ')+'.')))
 return rows
data={t:readtable(t) for t in tables}
assert all(data.values())
doc=Document(); sec=doc.sections[0];sec.orientation=WD_ORIENT.LANDSCAPE;sec.page_width=Cm(29.7);sec.page_height=Cm(21);sec.top_margin=Cm(1.6);sec.bottom_margin=Cm(1.5);sec.left_margin=Cm(1.5);sec.right_margin=Cm(1.5)
style=doc.styles['Normal']; style.font.name='Calibri';style.font.size=Pt(9)
doc.add_heading('Dekomposisi Data SIANGKASA',0)
p=doc.add_paragraph('Struktur tabel utama berdasarkan definisi basis data pada berkas SQL. Tanda “-” berarti tidak ada default atau constraint yang dinyatakan; NULL (implisit) menunjukkan default untuk kolom nullable yang tidak dinyatakan secara eksplisit.')
p.paragraph_format.space_after=Pt(8)
widths=[3.2,3.2,1.0,5.0,4.2,2.2,7.4]
for i,t in enumerate(tables,1):
 p=doc.add_paragraph();p.paragraph_format.keep_with_next=True;p.paragraph_format.space_before=Pt(8);p.paragraph_format.space_after=Pt(4);r=p.add_run(f'Tabel {i}: Struktur Tabel {t.upper()}');r.bold=True;r.font.size=Pt(11)
 table=doc.add_table(rows=1,cols=7);table.alignment=WD_TABLE_ALIGNMENT.CENTER;table.autofit=False;table.style='Table Grid'
 for col,w in zip(table.columns,widths):col.width=Cm(w)
 for c,w,label in zip(table.rows[0].cells,widths,['Nama Field','Tipe Data','Null','Konstrain','Range Nilai','Default','Keterangan']):
  c.width=Cm(w);c.text=label
  sh=OxmlElement('w:shd');sh.set(qn('w:fill'),'DCE6F1');c._tc.get_or_add_tcPr().append(sh)
  for run in c.paragraphs[0].runs:run.bold=True
 table.rows[0]._tr.get_or_add_trPr().append(OxmlElement('w:tblHeader'))
 for row in data[t]:
  cells=table.add_row().cells
  for c,w,val in zip(cells,widths,row):c.width=Cm(w);c.text=val
 for row in table.rows:
  row._tr.get_or_add_trPr().append(OxmlElement('w:cantSplit'))
  for cell in row.cells:
   cell.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
   for p in cell.paragraphs:
    p.paragraph_format.space_before=Pt(0);p.paragraph_format.space_after=Pt(1)
    for run in p.runs:run.font.size=Pt(8)
 doc.add_paragraph().paragraph_format.space_after=Pt(0)
out=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir\Dekomposisi_Data_SIANGKASA.docx')
doc.save(out)
print('Tabel dan jumlah kolom:',[(t,len(v)) for t,v in data.items()]);print(out)
