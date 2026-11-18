import zipfile
import io
import xml.sax.saxutils as saxutils
from pathlib import Path

def esc(text):
    if text is None:
        return "-"
    return saxutils.escape(str(text))

tables_data = [
    {
        "name": "users",
        "desc": "Tabel ini berfungsi untuk menyimpan data akun pengguna (administrator dan staf) yang memiliki hak akses login ke dalam sistem.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik akun pengguna"),
            ("name", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama lengkap pengguna sistem"),
            ("email", "varchar(255)", "Tidak", "UNIQUE, NOT NULL", "Format surel valid, maks. 255 karakter", "-", "Alamat surel unik; digunakan sebagai login identifier"),
            ("email_verified_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu verifikasi alamat surel"),
            ("password", "varchar(255)", "Tidak", "NOT NULL", "String hasil hashing Bcrypt", "-", "Hash kata sandi akun; tidak disimpan sebagai teks biasa"),
            ("two_factor_secret", "text", "Ya", "-", "String terenkripsi", "NULL", "Kunci rahasia autentikasi dua faktor (2FA)"),
            ("two_factor_recovery_codes", "text", "Ya", "-", "String JSON terenkripsi", "NULL", "Kumpulan kode pemulihan autentikasi 2FA"),
            ("two_factor_confirmed_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu konfirmasi aktivasi autentikasi 2FA"),
            ("remember_token", "varchar(100)", "Ya", "-", "String alfanumerik, maks. 100 karakter", "NULL", "Token sesi untuk fitur persistensi login (Remember Me)"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembuatan data akun"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan data akun terakhir")
        ]
    },
    {
        "name": "data_ppdb",
        "desc": "Tabel ini digunakan untuk mencatat seluruh data pendaftaran calon peserta didik baru secara daring.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik data pendaftaran PPDB"),
            ("periode_ppdb_id", "bigint unsigned", "Ya", "FOREIGN KEY (periode_ppdb.id)", "Mengikuti ID pada periode_ppdb", "NULL", "Menghubungkan pendaftar ke gelombang/periode PPDB terkait"),
            ("nama_lengkap", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama lengkap calon peserta didik"),
            ("nisn", "varchar(10)", "Tidak", "UNIQUE, NOT NULL", "Tepat 10 digit angka", "-", "Nomor Induk Siswa Nasional unik calon siswa"),
            ("jenis_kelamin", "enum('L','P')", "Tidak", "NOT NULL", "'L', 'P'", "-", "Jenis kelamin pendaftar (L = Laki-laki, P = Perempuan)"),
            ("tempat_lahir", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Kota/kabupaten tempat lahir pendaftar"),
            ("tanggal_lahir", "date", "Tidak", "NOT NULL", "Format tanggal YYYY-MM-DD", "-", "Tanggal lahir calon siswa"),
            ("asal_sekolah", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama sekolah dasar/madrasah asal pendaftar"),
            ("nama_orang_tua", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama orang tua atau wali pendaftar"),
            ("no_hp", "varchar(20)", "Tidak", "NOT NULL", "1 - 20 karakter", "-", "Nomor telepon atau WhatsApp aktif"),
            ("email", "varchar(255)", "Ya", "-", "Format surel valid, maks. 255 karakter", "NULL", "Alamat surel calon siswa atau orang tua"),
            ("alamat", "text", "Tidak", "NOT NULL", "Teks alamat lengkap", "-", "Alamat domisili lengkap calon siswa"),
            ("status", "enum('menunggu','diterima','ditolak')", "Tidak", "NOT NULL", "'menunggu', 'diterima', 'ditolak'", "'menunggu'", "Status seleksi berkas pendaftaran calon siswa"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu formulir pendaftaran dikirimkan"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan status seleksi")
        ]
    },
    {
        "name": "periode_ppdb",
        "desc": "Tabel ini digunakan untuk mengatur gelombang/jadwal pembukaan serta kuota penerimaan peserta didik baru.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik periode PPDB"),
            ("nama_periode", "varchar(150)", "Tidak", "NOT NULL", "1 - 150 karakter", "-", "Nama gelombang PPDB (misal: '2027/2028 - Gelombang 1')"),
            ("tanggal_mulai", "datetime", "Tidak", "NOT NULL", "Format datetime YYYY-MM-DD HH:MM:SS", "-", "Waktu dimulainya pembukaan pendaftaran"),
            ("tanggal_selesai", "datetime", "Tidak", "NOT NULL", "Format datetime YYYY-MM-DD HH:MM:SS", "-", "Waktu ditutupnya masa pendaftaran"),
            ("kuota", "int unsigned", "Ya", "-", "0 s/d 4.294.967.295", "NULL", "Jumlah kuota daya tampung penerimaan siswa baru"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembuatan data periode"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan informasi periode")
        ]
    },
    {
        "name": "siswa",
        "desc": "Tabel ini mencatat seluruh data induk siswa resmi yang terdaftar aktif maupun alumni di sekolah.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik data siswa"),
            ("ppdb_id", "bigint unsigned", "Ya", "UNIQUE, FOREIGN KEY (data_ppdb.id)", "Mengikuti ID pada data_ppdb", "NULL", "Relasi 1-ke-1 ke pendaftar PPDB asal siswa (nullable jika siswa pindahan)"),
            ("kelas_id", "bigint unsigned", "Ya", "FOREIGN KEY (kelas.id)", "Mengikuti ID pada kelas", "NULL", "Relasi ke kelas/rombongan belajar tempat siswa ditempatkan"),
            ("nis", "varchar(20)", "Tidak", "UNIQUE, NOT NULL", "Maksimal 20 karakter alfanumerik", "-", "Nomor Induk Siswa lokal yang diterbitkan sekolah"),
            ("nisn", "varchar(10)", "Tidak", "UNIQUE, NOT NULL", "Tepat 10 digit angka", "-", "Nomor Induk Siswa Nasional resmi dari Kemendikbud"),
            ("nama_lengkap", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama lengkap siswa"),
            ("jenis_kelamin", "enum('L','P')", "Tidak", "NOT NULL", "'L', 'P'", "-", "Jenis kelamin siswa (L = Laki-laki, P = Perempuan)"),
            ("tempat_lahir", "varchar(100)", "Tidak", "NOT NULL", "1 - 100 karakter", "-", "Kota/tempat kelahiran siswa"),
            ("tanggal_lahir", "date", "Tidak", "NOT NULL", "Format tanggal YYYY-MM-DD", "-", "Tanggal lahir siswa"),
            ("nama_orang_tua", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama orang tua / wali siswa"),
            ("no_hp", "varchar(20)", "Tidak", "NOT NULL", "1 - 20 karakter", "-", "Nomor telepon kontak siswa/orang tua"),
            ("email", "varchar(255)", "Ya", "-", "Format surel valid, maks. 255 karakter", "NULL", "Alamat surel siswa"),
            ("alamat", "text", "Tidak", "NOT NULL", "Teks alamat lengkap", "-", "Alamat tempat tinggal siswa"),
            ("status", "enum('aktif','lulus','pindah','keluar')", "Tidak", "NOT NULL", "'aktif', 'lulus', 'pindah', 'keluar'", "'aktif'", "Status keaktifan siswa dalam kegiatan akademik"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pencatatan data siswa pertama kali"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan data kesiswaan")
        ]
    },
    {
        "name": "guru",
        "desc": "Tabel ini digunakan untuk mengelola data tenaga pendidik/guru beserta penugasan mata pelajaran yang diampunya.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik data guru"),
            ("nip", "varchar(18)", "Ya", "UNIQUE", "18 digit angka", "NULL", "Nomor Induk Pegawai bagi guru ASN/PNS"),
            ("nama_lengkap", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama lengkap guru beserta gelar akademik"),
            ("jenis_kelamin", "enum('L','P')", "Tidak", "NOT NULL", "'L', 'P'", "-", "Jenis kelamin guru (L = Laki-laki, P = Perempuan)"),
            ("tempat_lahir", "varchar(100)", "Tidak", "NOT NULL", "1 - 100 karakter", "-", "Kota kelahiran guru"),
            ("tanggal_lahir", "date", "Tidak", "NOT NULL", "Format tanggal YYYY-MM-DD", "-", "Tanggal lahir guru"),
            ("mata_pelajaran", "varchar(255)", "Ya", "-", "1 - 255 karakter", "NULL", "Keterangan deskriptif bidang mata pelajaran yang diajarkan"),
            ("mata_pelajaran_id", "bigint unsigned", "Ya", "FOREIGN KEY (mata_pelajaran.id)", "Mengikuti ID pada mata_pelajaran", "NULL", "Relasi ke tabel data master mata pelajaran"),
            ("no_hp", "varchar(20)", "Tidak", "NOT NULL", "1 - 20 karakter", "-", "Nomor kontak telepon / WhatsApp guru"),
            ("email", "varchar(255)", "Ya", "UNIQUE", "Format surel valid, maks. 255 karakter", "NULL", "Alamat surel aktif guru"),
            ("alamat", "text", "Tidak", "NOT NULL", "Teks alamat lengkap", "-", "Alamat domisili tempat tinggal guru"),
            ("status", "enum('aktif','nonaktif')", "Tidak", "NOT NULL", "'aktif', 'nonaktif'", "'aktif'", "Status penugasan dinas guru di sekolah"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu penambahan data guru"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan data guru")
        ]
    },
    {
        "name": "kelas",
        "desc": "Tabel ini digunakan untuk mencatat data rombongan belajar (rombel), tingkatan kelas, dan penetapan wali kelas.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik ruang/rombongan belajar"),
            ("nama_kelas", "varchar(50)", "Tidak", "NOT NULL, KOMPOSIT UNIQUE", "1 - 50 karakter", "-", "Nama rombel (misal: 'VII A', 'VIII B')"),
            ("tingkat", "tinyint unsigned", "Tidak", "NOT NULL", "1 s/d 255 (contoh: 7, 8, 9)", "-", "Tingkatan jenjang pendidikan kelas SMP"),
            ("tahun_ajaran", "varchar(9)", "Tidak", "NOT NULL, KOMPOSIT UNIQUE", "Format 9 karakter (misal: '2026/2027')", "-", "Periode tahun ajaran berlakunya rombel"),
            ("kapasitas", "int unsigned", "Tidak", "NOT NULL", "1 s/d 100", "32", "Jumlah maksimal daya tampung siswa dalam kelas"),
            ("wali_kelas_id", "bigint unsigned", "Ya", "FOREIGN KEY (guru.id)", "Mengikuti ID pada guru", "NULL", "Relasi ke guru yang bertindak sebagai wali kelas"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembuatan data kelas"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan data kelas")
        ]
    },
    {
        "name": "mata_pelajaran",
        "desc": "Tabel data master untuk mencatat seluruh mata pelajaran kurikulum yang diajarkan di sekolah.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik mata pelajaran"),
            ("nama", "varchar(100)", "Tidak", "UNIQUE, NOT NULL", "1 - 100 karakter", "-", "Nama mata pelajaran kurikulum (misal: 'Matematika', 'IPA')"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembuatan mata pelajaran"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan mata pelajaran")
        ]
    },
    {
        "name": "pengumuman",
        "desc": "Tabel ini digunakan untuk menyimpan berita, agenda, dan pengumuman sekolah yang disajikan kepada publik.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik artikel pengumuman"),
            ("created_by", "bigint unsigned", "Ya", "FOREIGN KEY (users.id)", "Mengikuti ID pada users", "NULL", "Relasi ke akun admin/pengguna pembuat artikel"),
            ("judul", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Judul utama pengumuman"),
            ("slug", "varchar(255)", "Tidak", "UNIQUE, NOT NULL", "1 - 255 karakter URL-friendly", "-", "Identifier teks pengumuman untuk tautan URL ramah SEO"),
            ("isi", "longtext", "Tidak", "NOT NULL", "Teks panjang / format HTML", "-", "Konten lengkap isi pengumuman"),
            ("gambar", "varchar(255)", "Ya", "-", "Path berkas gambar", "NULL", "Lokasi berkas gambar sampul (thumbnail) pengumuman"),
            ("berlaku_sampai", "date", "Ya", "-", "Format tanggal YYYY-MM-DD", "NULL", "Batas tanggal tayang berlakunya pengumuman"),
            ("status", "enum('draft','terbit')", "Tidak", "NOT NULL", "'draft', 'terbit'", "'draft'", "Status publikasi artikel pengumuman"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pengumuman dibuat"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan terakhir pengumuman")
        ]
    },
    {
        "name": "galeri",
        "desc": "Tabel ini menyimpan dokumentasi kegiatan dan foto-foto kegiatan sekolah yang dipublikasikan pada website.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik foto galeri"),
            ("created_by", "bigint unsigned", "Ya", "FOREIGN KEY (users.id)", "Mengikuti ID pada users", "NULL", "Relasi ke akun pengguna yang mengunggah foto"),
            ("gambar", "varchar(255)", "Tidak", "NOT NULL", "Path direktori penyimpanan berkas", "-", "Lokasi fisik berkas foto kegiatan di storage peladen"),
            ("deskripsi", "varchar(255)", "Ya", "-", "1 - 255 karakter", "NULL", "Keterangan/kepsyen singkat mengenai foto kegiatan"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu foto diunggah"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan data foto")
        ]
    },
    {
        "name": "repository",
        "desc": "Tabel ini berfungsi sebagai penyimpanan materi ajar digital, modul, atau dokumen tugas sekolah yang dapat diunduh oleh siswa.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik berkas materi repository"),
            ("mata_pelajaran_id", "bigint unsigned", "Ya", "FOREIGN KEY (mata_pelajaran.id)", "Mengikuti ID pada mata_pelajaran", "NULL", "Relasi ke mata pelajaran yang bersangkutan"),
            ("created_by", "bigint unsigned", "Ya", "FOREIGN KEY (users.id)", "Mengikuti ID pada users", "NULL", "Relasi ke akun guru/admin yang mengunggah berkas"),
            ("judul", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Judul materi atau dokumen ajar"),
            ("deskripsi", "text", "Ya", "-", "Teks deskripsi dokumen", "NULL", "Uraian ringkas materi pembahasan dokumen"),
            ("path", "varchar(255)", "Ya", "-", "Path direktori berkas", "NULL", "Lokasi fisik penyimpanan dokumen pada server storage"),
            ("nama_file", "varchar(255)", "Tidak", "NOT NULL", "1 - 255 karakter", "-", "Nama asli berkas dokumen saat diunggah"),
            ("ekstensi", "varchar(10)", "Tidak", "NOT NULL", "Maksimal 10 karakter (misal: pdf, docx)", "-", "Ekstensi/format tipe berkas dokumen"),
            ("ukuran", "bigint unsigned", "Tidak", "NOT NULL", "0 s/d 18.446.744.073.709.551.615", "-", "Ukuran fisik berkas dalam satuan byte"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu berkas diunggah"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembaruan informasi berkas")
        ]
    },
    {
        "name": "repository_settings",
        "desc": "Tabel konfigurasi satu baris (singleton configuration) yang mengatur kode akses untuk mengunduh berkas pada repositori sekolah.",
        "fields": [
            ("id", "bigint unsigned", "Tidak", "PRIMARY KEY, AUTO_INCREMENT", "1 s/d 18.446.744.073.709.551.615", "AUTO_INCREMENT", "Identifier unik konfigurasi repositori"),
            ("kode_akses", "varchar(100)", "Tidak", "NOT NULL", "1 - 100 karakter alfanumerik", "-", "Kata sandi/token yang wajib diinput pengunjung sebelum mengunduh berkas materi"),
            ("versi", "int unsigned", "Tidak", "NOT NULL", "1 s/d 4.294.967.295", "1", "Penomoran versi kode akses untuk menginvalidasi sesi unduhan terdahulu secara otomatis"),
            ("created_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pembuatan pengaturan repositori"),
            ("updated_at", "timestamp", "Ya", "-", "Nilai timestamp valid", "NULL", "Waktu pergantian kode akses terakhir")
        ]
    }
]

# Column widths in dxa for Landscape A4 (total ~ 13958 dxa):
# Width: 16838, margins 1440 on each side -> 13958 printable width.
col_widths = [1800, 1600, 800, 2400, 2200, 1500, 3658]
headers = ["Nama Field", "Tipe Data", "Null", "Konstrain", "Range Nilai", "Default", "Keterangan"]

def build_cell(text, width, is_header=False, align="left", bold=False):
    align_xml = f'<w:jc w:val="{align}"/>'
    bold_xml = '<w:b/><w:bCs/>' if bold or is_header else ''
    font_sz = '20' if is_header else '19'  # 10pt / 9.5pt
    shd_xml = '<w:shd w:val="clear" w:color="auto" w:fill="EBF0F5"/>' if is_header else ''
    
    return f'''<w:tc>
        <w:tcPr>
            <w:tcW w:w="{width}" w:type="dxa"/>
            {shd_xml}
            <w:tcMar>
                <w:top w:w="80" w:type="dxa"/>
                <w:bottom w:w="80" w:type="dxa"/>
                <w:left w:w="120" w:type="dxa"/>
                <w:right w:w="120" w:type="dxa"/>
            </w:tcMar>
        </w:tcPr>
        <w:p>
            <w:pPr>
                {align_xml}
                <w:spacing w:before="30" w:after="30" w:line="240" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    {bold_xml}
                    <w:sz w:val="{font_sz}"/>
                    <w:szCs w:val="{font_sz}"/>
                </w:rPr>
                <w:t xml:space="preserve">{esc(text)}</w:t>
            </w:r>
        </w:p>
    </w:tc>'''

def build_table(fields):
    cols_xml = "".join([f'<w:gridCol w:w="{w}"/>' for w in col_widths])
    
    # Header row
    hdr_cells = "".join([build_cell(h, col_widths[i], is_header=True, align="center", bold=True) for i, h in enumerate(headers)])
    hdr_row = f'''<w:tr>
        <w:trPr>
            <w:tblHeader/>
            <w:cantSplit/>
        </w:trPr>
        {hdr_cells}
    </w:tr>'''
    
    # Data rows
    data_rows = []
    for f in fields:
        row_cells = []
        for i, val in enumerate(f):
            align = "center" if i in [2, 5] else "left"
            is_bold = (i == 0)
            row_cells.append(build_cell(val, col_widths[i], is_header=False, align=align, bold=is_bold))
        data_rows.append(f'''<w:tr>
            <w:trPr><w:cantSplit/></w:trPr>
            {"".join(row_cells)}
        </w:tr>''')
    
    tbl_xml = f'''<w:tbl>
        <w:tblPr>
            <w:tblW w:w="13958" w:type="dxa"/>
            <w:jc w:val="center"/>
            <w:tblBorders>
                <w:top w:val="single" w:sz="6" w:space="0" w:color="888888"/>
                <w:left w:val="single" w:sz="6" w:space="0" w:color="888888"/>
                <w:bottom w:val="single" w:sz="6" w:space="0" w:color="888888"/>
                <w:right w:val="single" w:sz="6" w:space="0" w:color="888888"/>
                <w:insideH w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>
                <w:insideV w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>
            </w:tblBorders>
            <w:tblLayout w:type="fixed"/>
        </w:tblPr>
        <w:tblGrid>{cols_xml}</w:tblGrid>
        {hdr_row}
        {"".join(data_rows)}
    </w:tbl>'''
    return tbl_xml

def build_paragraph(text, style='Normal', bold=False, italic=False, sz='24', align='left', space_before='100', space_after='100'):
    align_xml = f'<w:jc w:val="{align}"/>'
    bold_xml = '<w:b/><w:bCs/>' if bold else ''
    italic_xml = '<w:i/><w:iCs/>' if italic else ''
    return f'''<w:p>
        <w:pPr>
            {align_xml}
            <w:spacing w:before="{space_before}" w:after="{space_after}"/>
        </w:pPr>
        <w:r>
            <w:rPr>
                <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                {bold_xml}
                {italic_xml}
                <w:sz w:val="{sz}"/>
                <w:szCs w:val="{sz}"/>
            </w:rPr>
            <w:t xml:space="preserve">{esc(text)}</w:t>
        </w:r>
    </w:p>'''

body_elements = []

# Title & Subtitle
body_elements.append(build_paragraph("DEKOMPOSISI DATA SISTEM INFORMASI AKADEMIK & PPDB (SIANGKASA)", bold=True, sz='28', align='center', space_before='100', space_after='60'))
body_elements.append(build_paragraph("Daftar Struktur Fisik & Kamus Data Tabel Utama (Sumber: tugas_akhir (1).sql)", italic=True, sz='22', align='center', space_before='0', space_after='240'))

body_elements.append(build_paragraph("Dokumen ini memuat spesifikasi dekomposisi data (kamus data fisik) untuk seluruh tabel utama domain aplikasi SIANGKASA. Tabel teknis bawaan framework (seperti cache, jobs, dan sessions) dikecualikan.", italic=False, sz='22', align='both', space_before='0', space_after='200'))

for idx, t in enumerate(tables_data, 1):
    heading = f"{idx}. Tabel {t['name']}"
    body_elements.append(build_paragraph(heading, bold=True, sz='24', align='left', space_before='200', space_after='60'))
    body_elements.append(build_paragraph(t['desc'], italic=False, sz='21', align='left', space_before='0', space_after='100'))
    body_elements.append(build_table(t['fields']))
    body_elements.append(build_paragraph("", sz='16', space_before='0', space_after='150'))

# Landscape Section Properties (A4: 16838 x 11906, 1 inch margins)
sect_pr = '''<w:sectPr>
    <w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/>
    <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
    <w:cols w:space="720"/>
</w:sectPr>'''

full_doc_xml = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
            xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
            xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
            xmlns:v="urn:schemas-microsoft-com:vml"
            xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
            xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"
            xmlns:w15="http://schemas.microsoft.com/office/word/2012/wordml">
    <w:body>
        {"".join(body_elements)}
        {sect_pr}
    </w:body>
</w:document>'''

# Clean document.xml.rels
clean_rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/webSettings" Target="webSettings.xml"/>
    <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/fontTable" Target="fontTable.xml"/>
    <Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>
</Relationships>'''

# Clean [Content_Types].xml
clean_content_types = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
    <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
    <Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>
    <Override PartName="/word/webSettings.xml" ContentType="application/vnd.openxmlformats-officedocument.webSettings+xml"/>
    <Override PartName="/word/fontTable.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml"/>
    <Override PartName="/word/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>'''

# Create output docx by copying template files
src_zip = 'DPPL_SIANGKASA_Bab1-2.4.docx'
out_path = Path(r'c:\Users\ASUS\Documents\Program kuliah\tugas-akhir\Dekomposisi_Data_SIANGKASA.docx')

allowed_files = [
    '_rels/.rels',
    'docProps/app.xml',
    'docProps/core.xml',
    'word/theme/theme1.xml',
    'word/fontTable.xml',
    'word/settings.xml',
    'word/webSettings.xml',
    'word/styles.xml'
]

with zipfile.ZipFile(src_zip, 'r') as zin:
    with zipfile.ZipFile(out_path, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
        zout.writestr('[Content_Types].xml', clean_content_types.encode('utf-8'))
        zout.writestr('word/_rels/document.xml.rels', clean_rels.encode('utf-8'))
        zout.writestr('word/document.xml', full_doc_xml.encode('utf-8'))
        
        for name in allowed_files:
            if name in zin.namelist():
                zout.writestr(name, zin.read(name))

print(f"File successfully created at: {out_path}")
print(f"File size: {out_path.stat().st_size} bytes")

