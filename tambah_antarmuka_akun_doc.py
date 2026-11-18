from docx import Document
from docx.shared import Cm, Pt
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT
from pathlib import Path

path=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir\Penjelasan_Antarmuka_Bab_2.4_SIANGKASA_Revisi.docx')
doc=Document(path)
if any(p.text.strip().startswith('2.4.14 Antarmuka Tambah Akun') for p in doc.paragraphs):
    raise SystemExit('Subbab antarmuka tambah akun sudah ada.')
doc.add_heading('2.4.14 Antarmuka Tambah Akun', level=1)
doc.add_paragraph('Antarmuka ini khusus digunakan admin untuk membuat akun pengguna baru tanpa mengakhiri sesi admin. Formulir memuat nama, alamat email, kata sandi, dan konfirmasi kata sandi. Setelah validasi berhasil, akun dibuat dan admin tetap berada pada sesi yang sama.')
rows=[
('Deskripsi','Antarmuka Tambah Akun digunakan admin untuk mendaftarkan akun pengguna baru. Sistem memeriksa kelengkapan nama, format dan keunikan email, serta kecocokan dan aturan kata sandi. Kata sandi disimpan dalam bentuk hash. Setelah akun berhasil dibuat, sesi admin tetap aktif.'),
('Input','Buat Akun: nama (name), alamat email (email), kata sandi (password), dan konfirmasi kata sandi (password_confirmation).'),
('Output','Pesan validasi pada kolom nama, email, atau kata sandi apabila isian tidak lengkap atau tidak valid. Pesan kesalahan apabila email sudah digunakan atau konfirmasi kata sandi tidak cocok. Pesan “Akun berhasil ditambahkan” apabila penyimpanan berhasil. Admin tetap berada dalam sesi akunnya.'),
('Method/Algoritma','On “Buat Akun”\n1. Baca nama, email, password, dan password_confirmation dari formulir.\n2. Kirim data ke endpoint pembuatan akun.\n3. Validasi nama wajib diisi, email wajib diisi dengan format email yang benar dan belum digunakan, serta password wajib diisi, memenuhi aturan kata sandi, dan cocok dengan konfirmasinya.\n4. Jika validasi gagal, tampilkan pesan kesalahan pada kolom terkait dan kosongkan kolom kata sandi serta konfirmasinya.\n5. Jika validasi berhasil, buat akun baru dan simpan kata sandi dalam bentuk hash.\n6. Kirim pemberitahuan pendaftaran jika fitur verifikasi email diaktifkan.\n7. Tampilkan pesan keberhasilan dan kosongkan formulir. Sesi admin tetap aktif.'),
]
t=doc.add_table(rows=0,cols=2)
t.style='Table Grid';t.autofit=False;t.columns[0].width=Cm(3.2);t.columns[1].width=Cm(12.1)
for label,value in rows:
    cells=t.add_row().cells;cells[0].width=Cm(3.2);cells[1].width=Cm(12.1)
    cells[0].text=label;cells[1].text=value
    for c in cells:c.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
    for run in cells[0].paragraphs[0].runs:run.bold=True
    for c in cells:
        for p in c.paragraphs:
            p.paragraph_format.space_after=Pt(3)
            for run in p.runs:run.font.size=Pt(10)
doc.save(path)
print(path)
