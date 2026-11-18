import ast
from pathlib import Path
from docx import Document
from docx.shared import Cm, Pt
from docx.enum.text import WD_ALIGN_PARAGRAPH

root=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir')
tree=ast.parse((root/'rapikan_dppl.py').read_text(encoding='utf-8'))
entries=next(ast.literal_eval(n.value) for n in tree.body if isinstance(n,ast.Assign) and any(isinstance(t,ast.Name) and t.id=='entries' for t in n.targets))
# The supplied document contains two dashboard sketches for the same interface.
entries.pop(3)
actions={
'Antar Muka Login Pengguna': [('Masuk',['Baca email dan kata sandi yang dimasukkan pengguna.','Kirim kredensial ke layanan autentikasi untuk diperiksa.','Jika tidak sesuai, tampilkan pesan kesalahan.','Jika valid, buat sesi dan arahkan pengguna ke dashboard admin atau repository sesuai hak akses.']),('Tampil/Sembunyikan Kata Sandi',['Baca tindakan pada tombol visibilitas kata sandi.','Ubah tampilan kata sandi sesuai status yang dipilih.'])],
'2. Antar Muka Registrasi PPDB':[('Muat Periode',['Ambil daftar periode pendaftaran yang sedang aktif.','Tampilkan periode yang tersedia pada pilihan formulir.']),('Kirim Pendaftaran',['Baca periode dan seluruh data calon siswa yang diisi.','Periksa kelengkapan, format, NISN, dan ketersediaan periode.','Jika tidak valid, tampilkan pesan pada formulir agar pengguna dapat memperbaiki isian.','Jika valid, simpan pendaftaran dengan status menunggu dan tampilkan hasil pengiriman.'])],
'Antar Muka Dashboard Admin dan Guru':[('Tampilkan Dashboard',['Periksa hak akses pengguna.','Ambil dan tampilkan ringkasan siswa aktif, guru aktif, kelas, pendaftar, grafik, serta pendaftar terbaru.','Akun yang tidak memiliki akses admin diarahkan ke halaman repository.']),('Pilih Periode',['Baca pilihan periode PPDB.','Muat ulang ringkasan dan grafik pendaftar sesuai periode tersebut.'])],
'Antar Muka Data PPDB':[('Cari dan Filter',['Baca kata kunci atau status yang dipilih.','Minta daftar pendaftar sesuai filter dan tampilkan hasilnya.']),('Terima/Tolak',['Baca pendaftar dan status yang dipilih.','Kirim perubahan status ke server.','Saat diterima, sistem memeriksa data dan membuat siswa bila diperlukan.','Tampilkan hasil perubahan atau pesan kesalahan dan perbarui daftar.']),('Hapus',['Minta konfirmasi penghapusan pendaftar.','Jika disetujui, kirim permintaan hapus dan perbarui daftar.'])],
'Antar Muka Kelola Periode PPDB':[('Tambah/Ubah',['Buka formulir tambah atau formulir berisi data periode terpilih.','Baca nama, tanggal mulai, tanggal selesai, dan kuota.','Validasi isian, termasuk tanggal selesai setelah tanggal mulai.','Jika valid, simpan dan tampilkan hasil; jika tidak, tampilkan kesalahan.']),('Hapus',['Minta konfirmasi.','Jika periode masih memiliki pendaftar, tampilkan pesan bahwa periode tidak dapat dihapus.','Jika penghapusan berhasil, perbarui daftar periode.'])],
'Antar Muka Kelola Data Siswa':[('Cari dan Filter',['Baca pencarian, pilihan kelas, dan status.','Tampilkan daftar siswa yang sesuai.']),('Tambah/Ubah',['Buka formulir dan baca identitas, kontak, kelas, serta status siswa.','Validasi data wajib, NIS, NISN, dan kelas.','Simpan data yang valid atau tampilkan pesan kesalahan.','Perbarui daftar setelah penyimpanan.']),('Ubah Status',['Pilih tindakan luluskan atau aktifkan kembali pada siswa yang sesuai.','Kirim status baru dan perbarui daftar.']),('Hapus',['Minta konfirmasi dan hapus data siswa yang dipilih apabila disetujui.','Muat ulang daftar atau tampilkan pesan kesalahan.'])],
'Antar Muka Kelola Guru':[('Cari dan Filter',['Baca pencarian dan status, lalu tampilkan daftar yang sesuai.']),('Tambah/Ubah',['Buka formulir guru dan baca data identitas, kontak, status, serta mata pelajaran.','Validasi data wajib dan keunikan NIP atau email jika diisi.','Simpan data yang valid dan perbarui daftar, atau tampilkan kesalahan.']),('Hapus',['Minta konfirmasi penghapusan guru terpilih.','Jika disetujui, kirim permintaan dan perbarui daftar.'])],
'Antar Muka Kelola Kelas':[('Cari Kelas',['Baca kata kunci, lalu tampilkan kelas yang sesuai.']),('Tambah/Ubah',['Buka formulir kelas dan baca nama, tingkat, tahun ajaran, kapasitas, serta wali kelas.','Periksa kelengkapan, format tahun ajaran, dan keunikan nama pada tahun ajaran yang sama.','Simpan perubahan atau tampilkan pesan kesalahan.']),('Hapus',['Minta konfirmasi penghapusan.','Kirim permintaan hapus dan muat ulang daftar kelas.'])],
'Antar Muka Kelola Mata Pelajaran':[('Cari',['Baca kata kunci dan tampilkan mata pelajaran yang sesuai.']),('Tambah/Ubah',['Buka formulir dan baca nama mata pelajaran.','Periksa nama wajib diisi dan belum digunakan.','Simpan dan perbarui daftar atau tampilkan kesalahan.']),('Hapus',['Minta konfirmasi, hapus data terpilih, lalu perbarui daftar.'])],
'Antar Muka Kelola Pengumuman':[('Cari dan Filter',['Baca kata kunci dan pilihan status untuk memuat daftar pengumuman.']),('Tambah/Ubah',['Buka formulir, isi judul, isi, tanggal akhir dan gambar bila diperlukan.','Pilih simpan draft atau terbitkan.','Validasi dan simpan perubahan, lalu tampilkan hasil atau kesalahan.']),('Terbitkan/Jadikan Draft',['Baca pengumuman dan status yang dipilih.','Kirim perubahan status dan perbarui daftar.']),('Hapus',['Minta konfirmasi, kirim penghapusan, dan perbarui daftar.'])],
'Antar Muka Pengguna Kelola Galeri':[('Tambah/Ubah',['Buka formulir galeri, pilih gambar dan isi deskripsi bila diperlukan.','Periksa format dan ukuran gambar sesuai validasi sistem.','Simpan dan perbarui daftar gambar atau tampilkan pesan kesalahan.']),('Hapus',['Minta konfirmasi penghapusan, lalu hapus item dan muat ulang galeri.'])],
'Antar Muka Kelola Repository':[('Tambah/Ubah',['Buka formulir dan pilih mata pelajaran, isi judul dan deskripsi, serta pilih berkas.','Validasi isian, jenis dan ukuran berkas.','Simpan dokumen atau perubahan dan perbarui daftar; tampilkan kesalahan jika gagal.']),('Unduh',['Pilih dokumen yang ingin diunduh.','Sistem memeriksa akses dan ketersediaan berkas lalu mengirimkan berkas.']),('Hapus',['Minta konfirmasi, kirim permintaan hapus, dan perbarui daftar.']),('Simpan Kode Akses',['Admin memasukkan kode baru lalu menyetujui konfirmasi.','Sistem memvalidasi dan menyimpan kode serta membatalkan akses pengunjung sebelumnya.']),('Reset Akses Pengunjung',['Admin memilih reset dan menyetujui konfirmasi.','Sistem membatalkan sesi akses pengunjung sebelumnya tanpa mengubah kode.','Tampilkan pesan hasil reset.'])],
'Antar Muka Kelola Laporan':[('Laporan PPDB',['Pilih periode PPDB dan status jika diperlukan.','Jalankan pembuatan laporan.','Sistem memeriksa periode dan menghasilkan laporan PDF.']),('Laporan Siswa',['Pilih kelas atau status jika diperlukan.','Jalankan pembuatan laporan untuk mendapatkan PDF data siswa.']),('Laporan Guru',['Pilih status jika diperlukan.','Jalankan pembuatan laporan untuk mendapatkan PDF data guru.'])]
}
doc=Document();sec=doc.sections[0];sec.top_margin=Cm(2.5);sec.bottom_margin=Cm(2.5);sec.left_margin=Cm(3);sec.right_margin=Cm(2.5)
normal=doc.styles['Normal'];normal.font.name='Times New Roman';normal.font.size=Pt(11);normal.paragraph_format.space_after=Pt(5)
doc.add_heading('2.4 Perancangan Antarmuka',0)
doc.add_paragraph('Bagian ini menjelaskan fungsi antarmuka, masukan, keluaran, dan langkah proses yang dapat dilakukan pengguna pada halaman SIANGKASA.')
for idx,(heading,description,inp,out,_) in enumerate(entries,1):
 h=heading.replace('2. ','').replace('Pengguna Kelola','Kelola').strip()
 doc.add_heading(f'2.4.{idx} {h}',level=1)
 for label,content in [('Deskripsi',description),('Input',inp),('Output',out)]:
  p=doc.add_paragraph();p.paragraph_format.keep_with_next=True; p.add_run(label+' :').bold=True
  doc.add_paragraph(content)
 p=doc.add_paragraph();p.paragraph_format.keep_with_next=True;p.add_run('Method/Algoritma :').bold=True
 for action,steps in actions[heading]:
  p=doc.add_paragraph();p.paragraph_format.keep_with_next=True;p.add_run('On ‘'+action+'’').bold=True
  for j,step in enumerate(steps,1):
   p=doc.add_paragraph(f'{j}. {step}');p.paragraph_format.left_indent=Cm(.6);p.paragraph_format.first_line_indent=Cm(-.4);p.paragraph_format.space_after=Pt(2)
path=root/'Penjelasan_Antarmuka_Bab_2.4_SIANGKASA.docx';doc.save(path);print(path, len(entries))
