from docx import Document
from docx.shared import Pt, Cm
from docx.oxml import OxmlElement
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT
from pathlib import Path

src=Path(r'C:\Users\ASUS\Downloads\DPPL_SIANGKASA_Bab1-2.3.docx')
out=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir\DPPL_SIANGKASA_Bab1-2.4.docx')
d=Document(src)
ps=d.paragraphs; i=next(i for i,p in enumerate(ps) if p.text.strip()=='2.4 Perancangan Antar Muka')
intro=ps[i+1]; intro.text='Bagian ini menjelaskan rancangan antarmuka SIANGKASA, masukan dan keluaran, serta langkah penggunaan setiap fungsi berdasarkan sketsa dan implementasi sistem.'
# Remove the generic template example while preserving the supplied interface sketches.
start=next(p._p for p in ps[i:] if p.text.strip()=='Contoh:')
end=next(p._p for p in ps[i:] if p.text.strip()=='Antar Muka Login Pengguna')
cur=start
while cur is not end:
 nxt=cur.getnext(); cur.getparent().remove(cur); cur=nxt

entries=[
('Antar Muka Login Pengguna','Memungkinkan admin dan guru masuk ke sistem menggunakan akun masing-masing.','Alamat email dan kata sandi; pilihan ingat saya jika tersedia.','Akses ke halaman sesuai hak pengguna atau pesan kegagalan autentikasi.','Pengguna mengisi email dan kata sandi, lalu memilih tombol masuk. Sistem memeriksa kredensial; apabila valid pengguna diarahkan sesuai hak akses, sedangkan data yang salah menghasilkan pesan kesalahan.'),
('2. Antar Muka Registrasi PPDB','Memungkinkan calon siswa atau orang tua mengirim formulir pendaftaran pada periode yang sedang aktif.','Periode PPDB, nama lengkap, NISN, jenis kelamin, tempat dan tanggal lahir, asal sekolah, nama orang tua, nomor telepon, alamat, dan email jika ada.','Pesan pendaftaran berhasil atau pesan validasi pada kolom yang bermasalah.','Pengguna memilih periode, mengisi formulir, dan mengirimkannya. Sistem memvalidasi data, keunikan NISN, serta periode pendaftaran. Data yang valid disimpan dengan status menunggu.'),
('Antar Muka Dashboard Admin dan Guru','Menampilkan ringkasan informasi bagi admin setelah masuk. Guru diarahkan ke halaman pengelolaan repository sesuai hak aksesnya.','Pilihan periode PPDB pada dashboard admin.','Ringkasan siswa aktif, guru aktif, kelas, pendaftar, grafik, dan data pendaftar terbaru.','Admin membuka dashboard dan dapat memilih periode PPDB untuk memperbarui ringkasan dan grafik pendaftaran.'),
('Antar Muka Dashboard Admin dan Guru','Menampilkan bagian informasi dashboard dan navigasi ke modul yang sesuai dengan hak pengguna.','Pilihan menu navigasi dan periode jika tersedia.','Halaman modul atau ringkasan berdasarkan pilihan pengguna.','Pengguna memilih menu yang tersedia. Sistem mengarahkan pengguna ke halaman yang dipilih sesuai hak akses.'),
('Antar Muka Data PPDB','Memungkinkan admin memeriksa pendaftar dan mengelola status penerimaan.','Pencarian, filter status, pilihan pendaftar, dan status penerimaan.','Daftar atau rincian pendaftar dan pesan hasil perubahan.','Admin mencari atau memilih pendaftar lalu mengubah status. Sistem memvalidasi perubahan; pada penerimaan, sistem membuat data siswa apabila belum tersedia sesuai aturan penerimaan.'),
('Antar Muka Kelola Periode PPDB','Memungkinkan admin mengatur periode pendaftaran.','Nama periode, tanggal dan waktu mulai, tanggal dan waktu selesai, serta kuota.','Daftar periode dan pesan hasil penyimpanan atau validasi.','Admin menambah atau mengubah periode dengan tanggal selesai setelah tanggal mulai. Periode yang masih memiliki pendaftar tidak dapat dihapus.'),
('Antar Muka Kelola Data Siswa','Memungkinkan admin mencari, menambah, mengubah, dan menghapus data siswa.','NIS, NISN, nama lengkap, jenis kelamin, tempat dan tanggal lahir, orang tua, nomor telepon, alamat, email, kelas, serta status.','Daftar siswa dan pesan keberhasilan atau kesalahan validasi.','Admin membuka daftar, menggunakan pencarian atau filter, lalu memilih tindakan tambah, ubah, perubahan status, atau hapus. Sistem memvalidasi data dan memperbarui daftar.'),
('Antar Muka Kelola Guru','Memungkinkan admin mengelola data guru dan mata pelajaran yang diampu.','NIP jika tersedia, nama, jenis kelamin, tempat dan tanggal lahir, nomor telepon, email, alamat, status, dan mata pelajaran.','Daftar guru beserta pesan hasil proses.','Admin memilih tambah, ubah, atau hapus dan mengisi data yang diperlukan. Sistem memeriksa kelengkapan, format, dan keunikan data yang berlaku.'),
('Antar Muka Kelola Kelas','Memungkinkan admin mengelola kelas dan wali kelas.','Nama kelas, tingkat, tahun ajaran, kapasitas, dan wali kelas jika ada.','Daftar kelas dan pesan hasil penyimpanan atau validasi.','Admin menambah atau mengubah kelas lalu menyimpan. Sistem memeriksa format tahun ajaran dan nama kelas pada tahun ajaran yang sama.'),
('Antar Muka Kelola Mata Pelajaran','Memungkinkan admin mengelola daftar mata pelajaran.','Nama mata pelajaran.','Daftar mata pelajaran dan pesan hasil proses.','Admin memilih tambah, ubah, atau hapus. Sistem memeriksa nama sebelum menyimpan perubahan.'),
('Antar Muka Kelola Pengumuman','Memungkinkan admin mengelola pengumuman sekolah.','Judul, isi, status, tanggal berlaku sampai jika ada, dan gambar jika ada.','Daftar pengumuman serta pesan hasil penyimpanan atau validasi.','Admin mengisi formulir dan memilih status draft atau terbit. Sistem menyimpan data dan gambar yang diberikan; admin dapat mengubah atau menghapus pengumuman.'),
('Antar Muka Pengguna Kelola Galeri','Memungkinkan admin mengelola gambar kegiatan sekolah.','Berkas gambar dan deskripsi jika ada.','Daftar galeri dan pesan hasil proses.','Admin memilih gambar dan mengisi deskripsi, lalu menyimpan. Admin juga dapat mengubah atau menghapus item galeri.'),
('Antar Muka Kelola Repository','Memungkinkan admin dan guru mengelola dokumen; admin juga mengatur kode dan mereset akses pengunjung.','Mata pelajaran, judul, deskripsi, berkas, serta kode akses untuk pengaturan admin.','Daftar dokumen, hasil unduhan, dan pesan hasil penyimpanan atau reset.','Pengguna yang berhak menambah, mengubah, menghapus, atau mengunduh dokumen. Admin dapat menyimpan kode atau mereset akses pengunjung; setelah reset pengunjung perlu memasukkan kembali kode.'),
('Antar Muka Kelola Laporan','Memungkinkan admin menghasilkan laporan PPDB, siswa, dan guru.','Periode PPDB untuk laporan pendaftar serta filter kelas atau status sesuai laporan.','Berkas laporan PDF atau pesan apabila masukan tidak valid.','Admin memilih jenis laporan, mengisi filter yang tersedia, kemudian menjalankan pembuatan laporan. Sistem menyusun dan mengirimkan berkas PDF.')]

def add_table_before(anchor,desc,inp,outp,method):
 t=d.add_table(rows=0,cols=2); t.style='Table Grid'; t.autofit=False
 t.columns[0].width=Cm(3.2);t.columns[1].width=Cm(12.0)
 for a,b in [('Deskripsi',desc),('Input',inp),('Output',outp),('Metode/Algoritma',method)]:
  cells=t.add_row().cells; cells[0].width=Cm(3.2);cells[1].width=Cm(12);cells[0].text=a;cells[1].text=b
  for c in cells:c.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
  for r in cells[0].paragraphs[0].runs:r.bold=True
 anchor.addprevious(t._tbl)
 return t

paragraphs=d.paragraphs; idx=next(i for i,p in enumerate(paragraphs) if p.text.strip()=='2.4 Perancangan Antar Muka')
matches=[]; search=idx
for ent in entries:
 for k in range(search+1,len(paragraphs)):
  if paragraphs[k].text.strip()==ent[0]:matches.append(k);search=k;break
 else:raise ValueError('Heading tidak ditemukan: '+ent[0])
for j,entry in enumerate(entries):
 p=paragraphs[matches[j]]
 p.paragraph_format.keep_with_next=True
 for r in p.runs:r.bold=True
 if j+1<len(entries):anchor=paragraphs[matches[j+1]]._p
 else:anchor=d._element.body.sectPr
 add_table_before(anchor,*entry[1:])

# Improve readability of existing tables without touching drawings or text boxes.
for table in d.tables:
 for row in table.rows:
  for cell in row.cells:
   cell.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
   for p in cell.paragraphs:
    p.paragraph_format.space_before=Pt(0); p.paragraph_format.space_after=Pt(2)
    for r in p.runs:
     if r.font.size is None:r.font.size=Pt(9)
settings=d.settings.element
u=settings.find('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}updateFields')
if u is None:u=OxmlElement('w:updateFields');settings.append(u)
u.set('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val','true')
d.save(out)
print(out)
