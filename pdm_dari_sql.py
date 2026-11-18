from pathlib import Path
import ast,re
root=Path(r'C:\Users\ASUS\Documents\Program kuliah\tugas-akhir')
tree=ast.parse((root/'dekomposisi_sql.py').read_text(encoding='utf-8'))
nodes=[]
for node in tree.body:
 if isinstance(node,ast.Assign) and any(isinstance(t,ast.Name) and t.id=='doc' for t in node.targets):break
 nodes.append(node)
env={};exec(compile(ast.Module(body=nodes,type_ignores=[]),'<schema>','exec'),env)
data=env['data']; alters=env['alters']; lines=['Project SIANGKASA {','  database_type: \'MySQL\'','  Note: \'PDM 11 tabel utama sesuai dump SQL 3 November 2026.\'','}','']
enums={}
for t,rows in data.items():
 for f,typ,*_ in rows:
  if typ.startswith('enum('):
   name=t+'_'+f;enums[(t,f)]=name
   lines+=['Enum '+name+' {']+['  '+x for x in re.findall(r"'([^']*)'",typ)]+['}','']
for t,rows in data.items():
 lines.append('Table '+t+' {')
 for f,typ,null,const,rang,default,desc in rows:
  opts=[]
  if 'Primary Key' in const:opts.append('pk')
  if 'Auto Increment' in const:opts.append('increment')
  if null=='Tidak':opts.append('not null')
  else:opts.append('null')
  if 'Unique' in const and 'Unique bersama' not in const:opts.append('unique')
  if default not in ['-','Auto Increment']:
   value='null' if default.startswith('NULL') else default
   if typ.startswith(('int','bigint','tinyint')):value=value.strip("'")
   opts.append('default: '+value)
  if 'Foreign Key' in const:opts.append("note: 'FK'")
  dt=enums.get((t,f),('"'+typ+'"' if ' ' in typ else typ))
  lines.append('  '+f+' '+dt+' ['+', '.join(opts)+']')
 if t=='kelas':lines+=['  indexes {','    (nama_kelas, tahun_ajaran) [unique]','  }']
 lines+=['}','']
for t,blocks in alters.items():
 for b in blocks:
  for m in re.finditer(r'FOREIGN KEY\s*\(`([^`]+)`\)\s*REFERENCES\s*`([^`]+)`\s*\(`([^`]+)`\)([^,;]*)',b,re.I):
   f,target,col,rest=m.groups();delete=re.search(r'ON DELETE\s+(SET NULL|RESTRICT|CASCADE)',rest,re.I)
   symbol='-' if (t,f)==('siswa','ppdb_id') else '>'
   lines.append(f'Ref: {t}.{f} {symbol} {target}.{col} [delete: '+(delete.group(1).lower() if delete else 'no action')+']')
out=root/'PDM_SIANGKASA_PK_FK.dbml';out.write_text('\n'.join(lines)+'\n',encoding='utf-8')
print(out);print('Tabel:',len(data),'Relasi:',sum(x.startswith('Ref:') for x in lines))
