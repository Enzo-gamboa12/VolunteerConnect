"""Verificações ESTÁTICAS; não executa PHP, SQL, imagens ou JavaScript do projeto.
Uso: python tests/static_review.py
Opcional durante revisão sem extrair imagens: --assets-archive caminho.zip
Não substitui php -l, importação MySQL nem testes no navegador.
"""
from pathlib import Path, PurePosixPath
import argparse, re, json, zipfile, subprocess, tempfile, shutil
parser=argparse.ArgumentParser();parser.add_argument('--assets-archive');args=parser.parse_args()
root=Path(__file__).resolve().parents[1]
known={p.relative_to(root).as_posix() for p in root.rglob('*') if p.is_file()}
if args.assets_archive:
 with zipfile.ZipFile(args.assets_archive) as z:known.update('/'.join(PurePosixPath(n).parts[1:]) for n in z.namelist())
report=[]
def check(ok,label):
 if not ok:raise AssertionError(label)
 report.append(label)
def strip_php_strings(code):
 # Lexical balance only; not a PHP parser or lint.
 i=0;out=[]
 while i<len(code):
  if code.startswith('//',i) or code[i]=='#':
   j=code.find('\n',i);i=len(code) if j<0 else j;continue
  if code.startswith('/*',i):
   j=code.find('*/',i+2);assert j>=0,'Comentário PHP aberto';i=j+2;continue
  if code[i] in "\"'":
   quote=code[i];i+=1
   while i<len(code):
    if code[i]=='\\':i+=2;continue
    if code[i]==quote:i+=1;break
    i+=1
   else:raise AssertionError('String PHP aberta')
   out.append(' STRING ');continue
  out.append(code[i]);i+=1
 return ''.join(out)
def mask_php(s):
 blocks=[]
 def f(m):blocks.append(m[0]);return f'PHPCODE{len(blocks)-1}PLACEHOLDER'
 return re.sub(r'<\?(?:php|=).*?(?:\?>|\Z)',f,s,flags=re.S),blocks
files=list((root/'app').rglob('*.php'))+[root/'cadastro.php'];form_count=0;js_count=0
for p in files:
 s=p.read_text();code='\n'.join(re.findall(r'<\?(?:php|=)(.*?)(?:\?>|\Z)',s,re.S));plain=strip_php_strings(code)
 stack=[]
 for char in plain:
  if char in '([{':stack.append(char)
  elif char in ')]}':
   assert stack and '([{'.index(stack.pop())==')]}'.index(char),(p.name,'delimitador',char)
 check(not stack,'Delimitadores PHP equilibrados: '+p.relative_to(root).as_posix())
 for target in re.findall(r"(?:require|include)(?:_once)?\s+__DIR__\s*\.\s*['\"]([^'\"]+)['\"]",s):
  check((p.parent/target.lstrip('/')).exists(),'Include resolvido: '+p.name+' → '+target)
 masked,blocks=mask_php(s)
 for attr,target in re.findall(r'''\b(href|src|action)=["']([^"']*)["']''',masked):
  if 'PHPCODE' in target or not target or target.startswith(('#','data:','http:','https:','mailto:','tel:')):continue
  path=target.split('?')[0].split('#')[0]
  if not path:continue
  check('app/'+path.lstrip('/') in known,'Referência local resolvida: '+p.name+' → '+path)
 for form in re.findall(r'<form\b.*?</form>',masked,re.S|re.I):
  if re.search(r'method=["\']post["\']',form,re.I):
   for idx,block in enumerate(blocks):form=form.replace(f'PHPCODE{idx}PLACEHOLDER',block)
   check('<?= csrfField() ?>' in form,'Token CSRF no formulário POST: '+p.name);form_count+=1
 for attrs,body in re.findall(r'<script([^>]*)>(.*?)</script>',s,re.S|re.I):
  if 'src=' in attrs or not body.strip():continue
  body=re.sub(r'<\?(?:php|=).*?\?>','0',body,flags=re.S)
  if shutil.which('node'):
   with tempfile.NamedTemporaryFile(suffix='.js',mode='w') as f:
    f.write(body);f.flush();result=subprocess.run(['node','--check',f.name],capture_output=True,text=True)
   check(result.returncode==0,'Sintaxe JS inline: '+p.name+' '+result.stderr);js_count+=1
for p in (root/'app/js').glob('*.js'):
 if shutil.which('node'):
  result=subprocess.run(['node','--check',str(p)],capture_output=True,text=True)
  check(result.returncode==0,'Sintaxe JS: '+p.name+' '+result.stderr);js_count+=1
sql=(root/'database/schema.sql').read_text();migration=(root/'database/migrate_existing.sql').read_text()
check('CREATE DATABASE' not in sql and not re.search(r'^USE ',sql,re.M),'Schema usa o banco selecionado pelo ambiente')
check(sql.index('data_inicio DATE')<sql.index('CREATE OR REPLACE VIEW'),'Datas existem antes das views')
check(sql.count('data_termino DATE')==1 and 'ALTER TABLE' not in sql,'Sem ALTER duplicado na instalação nova')
check('status, tipo, vagas, vagasTotais)' in sql,'Seeds de campanhas informam tipo obrigatório')
check("SET senha =" not in sql and "'12345'" not in sql,'Sem substituição global de senhas ou senha demonstrativa em texto puro')
check('i.id AS id_instituicao' in sql and 'c.tipo AS categoria' in sql,'View contém campos exigidos pelos painéis')
check(not re.search(r'\b(?:DELETE FROM|TRUNCATE|DROP TABLE|UPDATE\s+\w+\s+SET\s+senha)\b',migration,re.I),'Migração não apaga registros/tabelas nem troca senhas')
for table in ['instituicoes','voluntarios','campanhas','inscricoes']:
 check(bool(re.search(r'CREATE TABLE '+table+r'\s*\(.*?ENGINE=InnoDB',sql,re.S)),'Tabela transacional: '+table)
php='\n'.join(p.read_text() for p in files)
check(not re.search(r'\bmb_\w+\(',php),'Sem dependência de mbstring não instalada')
check(php.count('function create(')==1,'Uma implementação do CRUD')
for n in ['salvarVoluntario.php','editar-campanha.php']:
 source=(root/'app'/n).read_text()
 check('beginTransaction()' in source and 'FOR UPDATE' in source and 'rollBack()' in source and 'commit()' in source,'Proteção transacional de vagas: '+n)
check('requireAccount' in (root/'app/painel-instituicao.php').read_text(),'Painel de instituição exige conta validada')
check('requireAccount' in (root/'app/verificar-voluntario.php').read_text(),'Listagem de voluntários exige conta validada')
check('id_instituicao=?' in (root/'app/verificar-voluntario.php').read_text(),'Listagem escopada à instituição')
check("$senha === $senhaBanco" not in (root/'app/login.php').read_text(),'Removida comparação que aceitava hash como senha')
check('hash_equals(csrfToken(), $token)' in (root/'app/bootstrap.php').read_text(),'POST exige verificação CSRF no servidor')
check('csrf_token' not in (root/'app/js/navigation.js').read_text(),'JavaScript do menu não manipula credenciais ou tokens')
try:
 import yaml
 compose=yaml.safe_load((root/'compose.yaml').read_text())
 check(compose['services']['app']['environment']['DB_DATABASE']=='${DB_DATABASE:-volunteerconnect}','Nome padrão do banco consistente no Compose')
except ImportError:report.append('SKIP: PyYAML indisponível; Compose não analisado')
check(form_count>=6,'Cobertura de formulários POST: '+str(form_count))
summary={'checks_passed':len(report),'php_files_structurally_checked':len(files),'post_forms_with_csrf':form_count,'javascript_syntax_checks':js_count,'not_run':['php -l (PHP indisponível nesta revisão)','execução PHP','importação/migração MySQL','Docker build','fluxos HTTP','testes de concorrência real','renderização no navegador'],'checks':report}
(root/'tests/static_results.json').write_text(json.dumps(summary,ensure_ascii=False,indent=2))
print(json.dumps({k:v for k,v in summary.items() if k!='checks'},ensure_ascii=False,indent=2))
