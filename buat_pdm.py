from pathlib import Path
import re
from docx import Document
from docx.shared import Inches, Pt
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from PIL import Image,ImageDraw,ImageFont

root=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir')
source=root/'DPPL_SIANGKASA_Bab1-2.2.docx'
dest=root/'DPPL_SIANGKASA_Bab1-2.3.docx'
names={'users':'User','mata_pelajaran':'MataPelajaran','guru':'Guru','kelas':'Kelas','periode_ppdb':'PeriodePpdb','data_ppdb':'DataPpdb','siswa':'Siswa','pengumuman':'Pengumuman','galeri':'Galeri','repository':'Repository','repository_settings':'RepositorySetting'}
fks={'guru.mata_pelajaran_id':'mata_pelajaran.id','kelas.wali_kelas_id':'guru.id','siswa.kelas_id':'kelas.id','data_ppdb.periode_ppdb_id':'periode_ppdb.id','siswa.ppdb_id':'data_ppdb.id','pengumuman.created_by':'users.id','galeri.created_by':'users.id','repository.created_by':'users.id','repository.mata_pelajaran_id':'mata_pelajaran.id'}
fields={}
for table,model in names.items():
 s=(root/'app'/'Models'/(model+'.php')).read_text(encoding='utf-8-sig')
 if model=='User': a=['name','email','email_verified_at','password','two_factor_secret','two_factor_recovery_codes','two_factor_confirmed_at','remember_token']
 else:
  m=re.search(r'protected\s+\$fillable\s*=\s*\[(.*?)\]',s,re.S)
  a=re.findall(r"'([^']+)'",m.group(1))
 fields[table]=['id']+a+['created_at','updated_at']
def typ(f):
 if f=='id' or f.endswith('_id') or f=='created_by': return 'bigint'
 if f in ['tingkat','kapasitas','kuota','versi']: return 'int'
 if f=='ukuran':return 'bigint'
 if f in ['tanggal_lahir','berlaku_sampai']:return 'date'
 if f in ['tanggal_mulai','tanggal_selesai','email_verified_at','two_factor_confirmed_at','created_at','updated_at']:return 'datetime'
 if f in ['alamat','isi','deskripsi','two_factor_secret','two_factor_recovery_codes']:return 'text'
 return 'varchar'
def desc(f):
 d={'id':'Identitas data','created_by':'Identitas pembuat konten','ppdb_id':'Identitas data pendaftar','kelas_id':'Identitas kelas','wali_kelas_id':'Identitas guru wali kelas','mata_pelajaran_id':'Identitas mata pelajaran','periode_ppdb_id':'Identitas periode PPDB','versi':'Versi akses pengunjung','kode_akses':'Kode akses repository','path':'Lokasi berkas repository','ukuran':'Ukuran berkas','created_at':'Waktu pembuatan','updated_at':'Waktu pembaruan'}
 return d.get(f,f.replace('_',' ').capitalize())+'.'

dbml=['// PDM perkiraan dari model dan controller; cocokkan dengan MySQL.']
for t,fs in fields.items():
 dbml.append('Table '+t+' {')
 for f in fs: dbml.append('  '+f+' '+typ(f)+(' [pk]' if f=='id' else ''))
 dbml.append('}\n')
for f,target in fks.items():dbml.append('Ref: '+f+(' - ' if f=='siswa.ppdb_id' else ' > ')+target)
(root/'PDM_SIANGKASA.dbml').write_text('\n'.join(dbml),encoding='utf-8')

font=ImageFont.truetype(r'C:\Windows\Fonts\arial.ttf',21); bold=ImageFont.truetype(r'C:\Windows\Fonts\arialbd.ttf',24)
def makefig(tables,edges,filename):
 W=1670; colw=720; gap=160; pad=35
 heights=[55+len(fields[t])*25 for t in tables]
 rows=max((heights[i:i+2] or [0]) for i in []) if False else [max(heights[i:i+2]) for i in range(0,len(tables),2)]
 y=30; pos={}
 for ri in range(len(rows)):
  for c in range(2):
   i=ri*2+c
   if i<len(tables):pos[tables[i]]=(pad+c*(colw+gap),y)
  y+=rows[ri]+110
 im=Image.new('RGB',(W,y),'white'); d=ImageDraw.Draw(im)
 for a,b,label in edges:
  x1,y1=pos[a]; x2,y2=pos[b]; x1+=colw//2; x2+=colw//2
  if y1<y2: y1+=heights[tables.index(a)]; y2-=4
  elif y1>y2: y1-=4; y2+=heights[tables.index(b)]
  else: x1=pos[a][0]+colw; x2=pos[b][0]; y1+=45; y2+=45
  mid=(y1+y2)//2; d.line([(x1,y1),(x1,mid),(x2,mid),(x2,y2)],fill='#555555',width=3)
 for t,(x,y0) in pos.items():
  h=heights[tables.index(t)]; d.rectangle([x,y0,x+colw,y0+h],fill='white',outline='black',width=2); d.rectangle([x,y0,x+colw,y0+36],fill='#dedede',outline='black',width=2); d.text((x+10,y0+5),t,font=bold,fill='black')
  for i,f in enumerate(fields[t]):
   key='PK ' if f=='id' else ('FK ' if t+'.'+f in fks else '')
   d.text((x+10,y0+43+i*25),f'{key}{f} : {typ(f)}',font=font,fill='black')
 im.save(root/filename)
makefig(['periode_ppdb','mata_pelajaran','data_ppdb','guru','siswa','kelas'],[('periode_ppdb','data_ppdb',''),('data_ppdb','siswa',''),('mata_pelajaran','guru',''),('guru','kelas',''),('kelas','siswa','')],'pdm_akademik.png')
makefig(['users','mata_pelajaran','pengumuman','repository','galeri','repository_settings'],[('users','pengumuman',''),('users','galeri',''),('users','repository',''),('mata_pelajaran','repository','')],'pdm_konten.png')

doc=Document(source)
start=next(p._p for p in doc.paragraphs if p.text.strip()=='Dekomposisi Data')
end=next(p._p for p in doc.paragraphs if p.text.strip()=='2.4 Perancangan Antar Muka')
node=start
while node is not end:
 nxt=node.getnext(); node.getparent().remove(node); node=nxt
def para(s,style='Body Text'):
 p=doc.add_paragraph(s,style=style); end.addprevious(p._p); return p
def tbl(t,fs):
 tab=doc.add_table(rows=1,cols=4); tab.style='Table Grid'
 for c,v in zip(tab.rows[0].cells,['Nama field','Tipe data*','Kunci*','Keterangan']): c.text=v
 for f in fs:
  key='PK' if f=='id' else ('FK ke '+fks[t+'.'+f] if t+'.'+f in fks else '-')
  for c,v in zip(tab.add_row().cells,[f,typ(f),key,desc(f)]):c.text=v
 for row in tab.rows:
  row._tr.get_or_add_trPr().append(OxmlElement('w:cantSplit'))
  for cell in row.cells:
   for p in cell.paragraphs:
    p.paragraph_format.space_after=Pt(0)
    for run in p.runs:run.font.size=Pt(9)
 tab.rows[0]._tr.get_or_add_trPr().append(OxmlElement('w:tblHeader'))
 end.addprevious(tab._tbl)
para('Dekomposisi Data','Heading 3')
para('Dekomposisi data menjelaskan tabel yang digunakan oleh model SIANGKASA. Tabel berikut menyajikan nama field, perkiraan tipe data, kunci, dan fungsi setiap field. Tanda bintang menunjukkan rancangan yang masih perlu diverifikasi terhadap struktur MySQL; panjang tipe, nullable, nilai default, indeks, dan constraint fisik belum dapat dipastikan dari kode aplikasi.')
for i,(t,fs) in enumerate(fields.items(),2):
 p=para(f'Tabel 2.{i} Struktur tabel {t}'); p.paragraph_format.keep_with_next=True
 tbl(t,fs)
para('Physical Data Model','Heading 3')
para('Physical Data Model (PDM) berikut menggambarkan tabel dan hubungan yang digunakan oleh aplikasi. Diagram dibagi menjadi dua kelompok; tabel users dan mata_pelajaran ditampilkan kembali pada kelompok konten untuk menunjukkan hubungan lintas kelompok. PK menunjukkan kunci utama dan FK menunjukkan field yang merujuk ke tabel lain.')
for f,caption in [('pdm_akademik.png','Gambar 2.7 PDM Akademik dan PPDB'),('pdm_konten.png','Gambar 2.8 PDM Konten dan Repository')]:
 p=doc.add_paragraph(); p.alignment=WD_ALIGN_PARAGRAPH.CENTER; p.add_run().add_picture(str(root/f),width=Inches(6.1)); end.addprevious(p._p)
 p=para(caption); p.alignment=WD_ALIGN_PARAGRAPH.CENTER
para('Satu mata pelajaran dikaitkan dengan beberapa guru dan dokumen; satu guru dapat menjadi wali beberapa kelas dan satu kelas dapat memiliki beberapa siswa. Satu periode PPDB memiliki beberapa pendaftar. Model DataPpdb menyatakan relasi hasOne ke Siswa; penerapan constraint unik pada database perlu diverifikasi. Tabel repository_settings berdiri sendiri dan digunakan untuk menyimpan pengaturan kode akses serta versi akses pengunjung.')
doc.save(dest)
print(dest)
