import re, os, copy
from pathlib import Path
from docx import Document
from docx.shared import Inches, Pt
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from PIL import Image, ImageDraw, ImageFont

root=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir')
src=Path(r'C:\Users\ASUS\Documents\Kuliah\DPPL_SIANGKASA_Bab1-2.1.docx')
out=root/'DPPL_SIANGKASA_Bab1-2.2.docx'
tmp=root/'dppl_assets'; tmp.mkdir(exist_ok=True)
models=['User','Siswa','Guru','Kelas','MataPelajaran','DataPpdb','PeriodePpdb','Pengumuman','Galeri','Repository','RepositorySetting']
controllers=['SiswaController','GuruController','KelasController','MataPelajaranController','PpdbController','PeriodePpdbController','PengumumanController','GaleriController','RepositoryController','PenggunaController','DashboardController','LaporanController','ProfileController','SecurityController']
texts={}
for n in models: texts[n]=(root/'app'/'Models'/(n+'.php')).read_text(encoding='utf-8-sig')
for n in controllers:
 p=root/'app'/'Http'/'Controllers'/(('Settings/' if n in ['ProfileController','SecurityController'] else '')+n+'.php')
 texts[n]=p.read_text(encoding='utf-8-sig')
def methods(n):
 return [(m.group(1),re.sub(r'\s+',' ',m.group(2)).strip()) for m in re.finditer(r'public\s+function\s+(\w+)\s*\((.*?)\)',texts[n],re.S)]
def attrs(n):
 s=texts[n]
 if n=='User': return ['id','name','email','email_verified_at','password','two_factor_secret','two_factor_recovery_codes','two_factor_confirmed_at','remember_token','created_at','updated_at']
 m=re.search(r'protected\s+\$fillable\s*=\s*\[(.*?)\]',s,re.S)
 return ['id']+(re.findall(r"'([^']+)'",m.group(1)) if m else [])+['created_at','updated_at']
attrdesc={'id':'Identitas data.','created_at':'Waktu data dibuat.','updated_at':'Waktu data terakhir diubah.','created_by':'Identitas akun pembuat konten.','ppdb_id':'Identitas pendaftar PPDB terkait.','kelas_id':'Identitas kelas siswa.','wali_kelas_id':'Identitas guru wali kelas.','mata_pelajaran_id':'Identitas mata pelajaran terkait.','periode_ppdb_id':'Identitas periode pendaftaran.','nis':'Nomor induk siswa.','nisn':'Nomor induk siswa nasional.','nip':'Nomor induk pegawai guru.','nama_lengkap':'Nama lengkap.','tanggal_lahir':'Tanggal lahir.','tempat_lahir':'Tempat lahir.','jenis_kelamin':'Jenis kelamin.','nama_orang_tua':'Nama orang tua.','no_hp':'Nomor telepon.','email':'Alamat surel.','alamat':'Alamat.','status':'Status data sesuai konteks kelas.','nama_kelas':'Nama kelas.','tingkat':'Tingkat kelas.','tahun_ajaran':'Tahun ajaran kelas.','kapasitas':'Kapasitas kelas.','nama':'Nama mata pelajaran.','nama_periode':'Nama periode PPDB.','tanggal_mulai':'Waktu mulai pendaftaran.','tanggal_selesai':'Waktu akhir pendaftaran.','kuota':'Informasi kuota pendaftaran.','asal_sekolah':'Sekolah asal pendaftar.','judul':'Judul konten.','slug':'Identitas teks untuk alamat pengumuman.','isi':'Isi pengumuman.','gambar':'Lokasi berkas gambar.','deskripsi':'Uraian konten.','berlaku_sampai':'Tanggal akhir keberlakuan pengumuman.','path':'Lokasi berkas repository.','nama_file':'Nama asli berkas.','ekstensi':'Ekstensi berkas.','ukuran':'Ukuran berkas.','kode_akses':'Kode akses repository.','versi':'Versi kode akses repository.','mata_pelajaran':'Keterangan mata pelajaran pada model guru.','name':'Nama akun.','password':'Kata sandi akun dalam bentuk hash.','email_verified_at':'Waktu verifikasi surel.','two_factor_secret':'Rahasia autentikasi dua faktor.','two_factor_recovery_codes':'Kode pemulihan autentikasi dua faktor.','two_factor_confirmed_at':'Waktu konfirmasi autentikasi dua faktor.','remember_token':'Token sesi ingat saya.'}
def typ(a):
 if a=='id' or a.endswith('_id') or a=='created_by': return 'int'
 if a in ['tingkat','kapasitas','kuota','versi','ukuran']: return 'int'
 if a in ['tanggal_lahir','berlaku_sampai']: return 'date'
 if a in ['tanggal_mulai','tanggal_selesai','created_at','updated_at','email_verified_at','two_factor_confirmed_at']: return 'datetime'
 return 'string'
verbs={'index':'Mengambil daftar data.','list':'Mengambil daftar pendaftar PPDB.','show':'Mengambil rincian data.','store':'Memvalidasi dan menyimpan data baru.','update':'Memvalidasi dan memperbarui data.','destroy':'Menghapus data yang dipilih.','aktif':'Mengambil periode PPDB yang sedang dibuka.','publik':'Mengambil konten yang tersedia bagi publik.','showBySlug':'Mengambil pengumuman terbit berdasarkan slug.','download':'Memeriksa akses dan mengunduh berkas repository.','bukaAkses':'Memeriksa kode dan membuka akses repository pada sesi.','tutupAkses':'Menutup akses repository pada sesi.','simpanKode':'Menyimpan kode akses repository.','create':'Menampilkan halaman pembuatan akun.','edit':'Menampilkan halaman pengaturan.','ppdb':'Menyusun laporan PPDB dalam format PDF.','siswa':'Menyusun laporan siswa dalam format PDF.','guru':'Menyusun laporan guru dalam format PDF.'}
relations={'kelas':'Mengembalikan relasi kelas siswa.','ppdb':'Mengembalikan relasi pendaftaran siswa.','kelasWali':'Mengembalikan relasi kelas yang diwalikan guru.','mapel':'Mengembalikan relasi mata pelajaran guru.','waliKelas':'Mengembalikan relasi guru wali kelas.','siswa':'Mengembalikan relasi data siswa.','materi':'Mengembalikan relasi materi repository.','guru':'Mengembalikan relasi data guru.','periodePpdb':'Mengembalikan relasi periode pendaftaran.','pendaftar':'Mengembalikan relasi pendaftar PPDB.','pembuat':'Mengembalikan relasi akun pembuat konten.','mataPelajaran':'Mengembalikan relasi mata pelajaran.','getGambarUrlAttribute':'Menghasilkan URL gambar apabila tersedia.'}
def descmethod(n,m):
 if n in models:return relations.get(m,'Menjalankan operasi model.')
 if n=='DashboardController':return 'Mengolah ringkasan akademik dan PPDB untuk halaman dashboard.'
 if n=='PpdbController' and m=='update': return 'Memperbarui status pendaftar dan membuat data siswa saat penerimaan melalui transaksi.'
 if n=='ProfileController' and m=='destroy':return 'Menghapus akun pengguna yang sedang masuk dan mengakhiri sesi.'
 if n=='SecurityController' and m=='update':return 'Memperbarui kata sandi akun yang sedang masuk.'
 return verbs.get(m,'Memproses permintaan.')
def output(n,m):
 if n in models:return 'Relasi Eloquent' if m!='getGambarUrlAttribute' else 'URL gambar atau null'
 if n=='LaporanController':return 'Respons berkas PDF'
 if n in ['ProfileController','SecurityController']:return 'Halaman Inertia' if m=='edit' else 'Respons pengalihan'
 if n=='DashboardController' or (n=='PenggunaController' and m=='create'):return 'Halaman Inertia'
 if n=='PenggunaController':return 'Respons pengalihan'
 if n=='PpdbController' and m=='store':return 'Respons JSON atau pengalihan'
 if n=='RepositoryController' and m=='download':return 'Respons unduhan berkas'
 return 'Respons JSON'

# Generate readable grouped class diagrams at print resolution.
fontpath=r'C:\Windows\Fonts\arial.ttf'; boldpath=r'C:\Windows\Fonts\arialbd.ttf'
font=ImageFont.truetype(fontpath,23); bold=ImageFont.truetype(boldpath,25); small=ImageFont.truetype(fontpath,19)
groups=[('Akademik',[('SiswaController','Siswa'),('GuruController','Guru'),('KelasController','Kelas'),('MataPelajaranController','MataPelajaran')]),('PPDB dan konten',[('PpdbController','DataPpdb'),('PeriodePpdbController','PeriodePpdb'),('PengumumanController','Pengumuman'),('GaleriController','Galeri')]),('Repository dan akun',[('RepositoryController','Repository'),('RepositoryController','RepositorySetting'),('PenggunaController','User'),('ProfileController','User'),('SecurityController','User')]),('Dashboard dan laporan',[('DashboardController','DataPpdb, PeriodePpdb, Siswa, Guru, Kelas'),('LaporanController','DataPpdb, PeriodePpdb, Siswa, Guru, Kelas')])]
def drawgroup(idx,group):
 title,pairs=group; h=100+len(pairs)*176; im=Image.new('RGB',(1800,h),'white'); d=ImageDraw.Draw(im)
 d.text((35,20),'Controller',font=bold,fill='black'); d.text((1000,20),'Model',font=bold,fill='black')
 for i,(c,m) in enumerate(pairs):
  y=65+i*176; d.rectangle((25,y,700,y+150),outline='black',width=2); d.line((25,y+36,700,y+36),fill='black',width=2); d.text((37,y+5),c,font=bold,fill='black')
  ms=[x[0]+'()' for x in methods(c)]; chunks=[', '.join(ms[k:k+4]) for k in range(0,len(ms),4)]
  for j,line in enumerate(chunks[:4]):d.text((37,y+42+j*25),line,font=small,fill='black')
  d.rectangle((1000,y,1770,y+150),outline='black',width=2); d.text((1012,y+5),m,font=bold if len(m)<26 else small,fill='black')
  if ',' not in m:
   d.line((1000,y+36,1770,y+36),fill='black',width=2)
   a=attrs(m); s=', '.join(a[:6]); d.text((1012,y+46),s[:65],font=small,fill='black')
   mm=', '.join(v[0]+'()' for v in methods(m)); d.text((1012,y+75),mm[:70],font=small,fill='black')
  d.line((700,y+75,995,y+75),fill='black',width=2); d.polygon([(995,y+75),(980,y+68),(980,y+82)],fill='black'); d.text((770,y+45),'menggunakan',font=small,fill='black')
 p=tmp/('diagram'+str(idx)+'.png'); im.save(p); return p

doc=Document(src)
start=next(p._p for p in doc.paragraphs if p.text.strip()=='Kelas Diagram')
end=next(p._p for p in doc.paragraphs if p.text.strip()=='2.3 Perancangan Data')
node=start
while node is not end:
 nxt=node.getnext(); node.getparent().remove(node); node=nxt
def addp(s,style='Body Text'):
 p=doc.add_paragraph(s,style=style); end.addprevious(p._p); return p
def table(headers,rows):
 t=doc.add_table(rows=1,cols=len(headers)); t.style='Table Grid'
 for i,x in enumerate(headers):t.rows[0].cells[i].text=x
 for row in rows:
  cells=t.add_row().cells
  for i,x in enumerate(row):cells[i].text=str(x)
 for row in t.rows:
  trPr=row._tr.get_or_add_trPr(); trPr.append(OxmlElement('w:cantSplit'))
  for cell in row.cells:
   for p in cell.paragraphs:
    p.paragraph_format.space_after=Pt(1); p.paragraph_format.space_before=Pt(1)
    for r in p.runs:r.font.size=Pt(9)
 for r in t.rows[0].cells:
  for p in r.paragraphs:
   for x in p.runs:x.bold=True
 t.rows[0]._tr.get_or_add_trPr().append(OxmlElement('w:tblHeader'))
 end.addprevious(t._tbl)

addp('Kelas Diagram','Heading 3')
addp('Diagram kelas SIANGKASA menggambarkan kelas model dan controller serta hubungan penggunaannya. Diagram dibagi berdasarkan kelompok fitur untuk mempermudah pembacaan. Tanda panah menunjukkan bahwa controller menggunakan model; operasi yang ditampilkan adalah operasi publik yang didefinisikan pada kelas.')
for i,g in enumerate(groups,3):
 p=doc.add_paragraph(); p.alignment=WD_ALIGN_PARAGRAPH.CENTER; p.add_run().add_picture(str(drawgroup(i,g)),width=Inches(6.15)); end.addprevious(p._p)
 cap=addp(f'Gambar 2.{i} Diagram kelas {g[0]}'); cap.alignment=WD_ALIGN_PARAGRAPH.CENTER
addp('Pada hubungan antarmodel, satu mata pelajaran dapat dikaitkan dengan beberapa guru dan materi repository. Seorang guru dapat menjadi wali untuk beberapa kelas, sedangkan satu kelas dapat memiliki beberapa siswa. Satu periode PPDB memiliki beberapa pendaftar; model DataPpdb mendefinisikan hubungan satu siswa untuk setiap pendaftar. Model Pengumuman, Galeri, dan Repository memiliki hubungan dengan User sebagai pembuat konten.')
addp('Deskripsi Kelas dan Atribut','Heading 3')
addp('Bagian ini menjelaskan atribut dan fungsi kelas yang ditampilkan pada diagram. Tipe atribut dinyatakan sebagai tipe yang digunakan atau disimpulkan dari kode aplikasi; tipe kolom fisik dan batasan basis data perlu disesuaikan dengan struktur MySQL. Atribut id dan waktu mengikuti konvensi Eloquent. Fungsi bawaan framework serta fungsi privat tidak dirinci.')
for n in models+controllers:
 ismodel=n in models
 heading=addp('Kelas '+n,'Body Text')
 for run in heading.runs: run.bold=True
 addp(('Kelas model yang merepresentasikan data '+n+'.' if ismodel else 'Kelas controller yang menangani permintaan pada modul '+n.replace('Controller','')+'.'))
 if ismodel:
  addp('Deskripsi atribut kelas '+n)
  table(['Nama atribut','Tipe data aplikasi','Deskripsi'],[(a,typ(a),attrdesc.get(a,'Data '+a.replace('_',' ')+'.')) for a in attrs(n)])
 ms=methods(n)
 if ms:
  addp('Deskripsi fungsi kelas '+n)
  rows=[]
  for m,param in ms:
   rows.append((m+'('+param+')',param if param else 'Tidak ada',output(n,m),descmethod(n,m)))
  table(['Fungsi dan parameter','Input','Output','Deskripsi'],rows)
 elif ismodel:addp('Kelas ini tidak mendefinisikan fungsi publik khusus; operasi dasar disediakan oleh Eloquent.')
doc.save(out)
print(out)
