"""Local-only DOCX/PDF reader. Input snapshots are opened read-only; JSON is written to stdout."""
from pathlib import Path
from zipfile import ZipFile
from xml.etree import ElementTree as ET
import hashlib, json, re, sys, unicodedata

PARSER_VERSION = "1.0.0"
W = "{http://schemas.openxmlformats.org/wordprocessingml/2006/main}"
NS = {"w": W[1:-1]}
SECTIONS = {"1": ("identification","Identificación"),"2":("purpose","Propósito del puesto"),
"3":("tasks","Principales tareas y responsabilidades"),"4":("context","Contexto del puesto"),
"5":("profile","Perfil del puesto"),"6":("competencies","Competencias requeridas"),
"7":("commitment","Compromiso"),"8":("contribution","Contribución al Hospital")}
def norm(s):
    return " ".join("".join(c for c in unicodedata.normalize("NFKD",s.lower()) if not unicodedata.combining(c)).split())
def text_of(el):
    return "".join(("\n" if n.tag in (W+"br",W+"cr") else "\t" if n.tag==W+"tab" else n.text or "")
        for n in el.iter() if n.tag in (W+"t",W+"delText",W+"br",W+"cr",W+"tab"))
def issue(code,title,detail,location="document"):
    return dict(code=code,title=title,detail=detail,location=location)
def flat(blocks):
    for b in blocks:
        if b["kind"]=="table":
            for row in b["rows"]:
                for cell in row["cells"]: yield from flat(cell["blocks"])
        else: yield b
def lines_of(blocks):
    for b in flat(blocks):
        if b["kind"]=="paragraph":
            for i,line in enumerate(b["text"].splitlines()):
                if line.strip(): yield dict(b, id=b["id"]+f"/line/{i}", text=line.strip())
def read_docx(p):
    warnings=[]; numbering={}; unsupported=[]
    with ZipFile(p) as z:
        members=z.namelist()
        if sum(i.file_size for i in z.infolist())>120*1024*1024: raise ValueError("DOCX expandido demasiado grande.")
        if "word/numbering.xml" in members:
            nroot=ET.fromstring(z.read("word/numbering.xml"))
            abstract={}
            for a in nroot.findall("w:abstractNum",NS):
                levels={}
                for lv in a.findall("w:lvl",NS):
                    fmt=lv.find("w:numFmt",NS);start=lv.find("w:start",NS)
                    levels[int(lv.get(W+"ilvl","0"))]={"format":fmt.get(W+"val","decimal") if fmt is not None else "decimal","start":int(start.get(W+"val","1")) if start is not None else 1}
                abstract[a.get(W+"abstractNumId")]=levels
            for n in nroot.findall("w:num",NS):
                a=n.find("w:abstractNumId",NS)
                numbering[n.get(W+"numId")]=abstract.get(a.get(W+"val") if a is not None else "",{})
        def parse(el,locator):
            tag=el.tag.split("}")[-1]
            if tag=="p":
                t=text_of(el);style=el.find("w:pPr/w:pStyle",NS)
                b={"id":locator,"kind":"paragraph","text":t,"style":style.get(W+"val","") if style is not None else ""}
                b["heading"]=bool(re.search(r"heading|titulo|title",norm(b["style"])))
                np=el.find("w:pPr/w:numPr",NS)
                if np is not None:
                    nid=np.find("w:numId",NS);il=np.find("w:ilvl",NS)
                    n=nid.get(W+"val","") if nid is not None else "";level=int(il.get(W+"val","0")) if il is not None else 0
                    b["list"]={"id":n,"level":level,**numbering.get(n,{}).get(level,{"format":"decimal","start":1})}
                if not t and el.find(".//w:drawing",NS) is not None:
                    b.update(kind="image",text="Imagen institucional incrustada")
                return [b]
            if tag=="tbl":
                rows=[]
                for ri,tr in enumerate(el.findall("w:tr",NS)):
                    cells=[]
                    for ci,tc in enumerate(tr.findall("w:tc",NS)):
                        cp=tc.find("w:tcPr",NS);span=cp.find("w:gridSpan",NS) if cp is not None else None;merge=cp.find("w:vMerge",NS) if cp is not None else None
                        cb=[]
                        for bi,ch in enumerate(tc):
                            if ch.tag!=W+"tcPr":cb+=parse(ch,f"{locator}/row/{ri}/cell/{ci}/block/{bi}")
                        cell={"blocks":cb,"text":text_of(tc),"colspan":int(span.get(W+"val","1")) if span is not None else 1}
                        if merge is not None:cell["vmerge"]=merge.get(W+"val","continue")
                        cells.append(cell)
                    rows.append({"cells":cells})
                return [{"id":locator,"kind":"table","text":text_of(el),"rows":rows}]
            if tag in ("sectPr","tcPr"):return []
            if tag in ("sdt","sdtContent","customXml"):
                result=[]
                for i,ch in enumerate(el):result+=parse(ch,f"{locator}/{i}")
                return result
            t=text_of(el)
            unsupported.append({"location":locator,"xml":ET.tostring(el,encoding="unicode")})
            return [{"id":locator,"kind":"paragraph","text":t}] if t else []
        main=ET.fromstring(z.read("word/document.xml"));body=main.find("w:body",NS)
        blocks=[]
        if body is None:raise ValueError("El Word no tiene cuerpo de documento.")
        for i,ch in enumerate(body):blocks+=parse(ch,f"word/document.xml/block/{i}")
        headers=[];extras=[];allparts=[main]
        for name in members:
            if re.match(r"word/(header|footer|footnotes|endnotes|comments).*\.xml$",name):
                root=ET.fromstring(z.read(name));allparts.append(root)
                dest=headers if re.match(r"word/(header|footer)",name) else extras
                for i,ch in enumerate(root):
                    if ch.tag in (W+"footnote",W+"endnote",W+"comment"):
                        for j,v in enumerate(ch):dest+=parse(v,f"{name}/{i}/{j}")
                    else:dest+=parse(ch,f"{name}/{i}")
        raw="\n".join(text_of(n) for part in allparts for n in part.iter(W+"p"))
        if main.find(".//w:ins",NS) is not None or main.find(".//w:del",NS) is not None:
            warnings.append(issue("tracked_changes","Control de cambios presente","Se conserva también el texto de cambios; revisar contra el original."))
        if unsupported:warnings.append(issue("unmapped_xml","Componentes adicionales","Hay componentes XML no estándar conservados en la extracción."))
        media=[{"part":n,"sha256":hashlib.sha256(z.read(n)).hexdigest()} for n in members if n.startswith("word/media/")]
    return blocks,headers,extras,raw,warnings,{"media":media,"unmappedXml":unsupported}

def read_pdf(p):
    from pypdf import PdfReader
    reader=PdfReader(p);blocks=[];raw_pages=[];warnings=[]
    for pi,page in enumerate(reader.pages,1):
        raw=page.extract_text() or "";raw_pages.append(raw)
        if not raw.strip():warnings.append(issue(f"pdf_empty_{pi}","Página sin texto extraíble",f"Página {pi}: requiere revisión visual u OCR local.",f"page/{pi}"))
        corrected=unicodedata.normalize("NFKC",raw).replace("Ɵ","ti")
        if corrected!=raw and not any(w["code"]=="pdf_encoding" for w in warnings):
            warnings.append(issue("pdf_encoding","Codificación del PDF","La lectura aplica NFKC a ligaduras y reemplaza Ɵ por ti. El texto crudo y el original se conservan; comprobar contra el PDF."))
        pending=None
        for li,line in enumerate(corrected.splitlines()):
            t=line.strip()
            if not t:continue
            bullet=bool(re.match(r"^[•\uf0b7●]",t))
            heading=t.isupper() or bool(re.match(r"^(CONTROL DE|HONORARIOS M|AN.LISIS DE|VALORES \()",t))
            if pending and not bullet and not heading and not pending.get("heading") and pending.get("list"):
                pending["text"]+=" "+t;continue
            pending={"id":f"page/{pi}/line/{li}","kind":"paragraph","text":re.sub(r"^[•\uf0b7●]\s*","",t),"heading":heading}
            if bullet:pending["list"]={"id":f"page-{pi}","level":0,"format":"bullet","start":1}
            blocks.append(pending)
    return blocks,[],[],"\n\n".join(raw_pages),warnings,{"pages":len(reader.pages),"pdfMetadata":{str(k):str(v) for k,v in (reader.metadata or {}).items()}}

def map_document(p):
    result=read_docx(p) if p.suffix.lower()==".docx" else read_pdf(p)
    blocks,headers,extras,raw,warnings,metadata=result
    lines=list(lines_of(blocks))
    title=lines[0]["text"] if lines else ""
    title_name=re.sub(r"^descriptivo de puesto(?: de trabajo)?\s*[-–—:]?\s*","",title,flags=re.I)
    fields={k:None for k in ["name","area","reportsTo","supervisor","supervises"]};locations={}
    keys={"1.":"name","1.b":"area","1.c":"reportsTo","1.d":"supervisor","1.e":"supervises"}
    patterns=[("name",r"^1\.\s*nombre del puesto\s*:?\s*(.*)$"),
      ("area",r"^1\.b\s*.rea\s*:?\s*(.*)$"),
      ("reportsTo",r"^1\.c\s*reporta a\s*:?\s*(.*)$"),
      ("supervisor",r"^1\.d\s*supervisor directo\s*:?\s*(.*)$"),
      ("supervises",r"^1\.e\s*supervisa el trabajo de\s*:?\s*(.*)$")]
    for i,b in enumerate(lines):
        for key,pat in patterns:
            m=re.match(pat,b["text"],re.I)
            if m:
                v=m.group(1).strip()
                if not v and i+1<len(lines) and not re.match(r"^[1-8]\.",lines[i+1]["text"]):v=lines[i+1]["text"]
                fields[key]=v or None;locations[key]=b["id"]
    header_text="\n".join(b["text"] for b in flat(headers))
    edition_match=re.search(r"Versi.n\s*:?\s*([^|\n]+)",header_text,re.I)
    edition=edition_match.group(1).strip() if edition_match else None
    date_match=re.search(r"\b(\d{2}/\d{2}/\d{4})\b",header_text)
    date=date_match.group(1) if date_match else None
    standard=bool(fields["name"])
    sections=[];current={"key":"additional","title":"Contenido adicional","blocks":[]}
    def finish():
        if current["blocks"]:sections.append(current.copy())
    if standard:
        for b in blocks:
            if b["kind"]=="table":
                if "1. Nombre del puesto" in b["text"]:
                    if current["key"]!="identification":finish();current={"key":"identification","title":"Identificación","blocks":[]}
                elif "Firma y aclaración" in b["text"]:
                    finish();current={"key":"signatures","title":"Espacios de firma","blocks":[]}
                current["blocks"].append(b);continue
            for line in b["text"].splitlines() or [""]:
                lb=dict(b,text=line,id=b["id"]+f"/line/{len(current['blocks'])}")
                m=re.match(r"^([1-8])\.\s+(Nombre del [Pp]uesto|Identificaci.n del [Pp]uesto|Prop.sito del [Pp]uesto|Principales [Tt]areas y [Rr]esponsabilidades|Contexto del [Pp]uesto|Perfil del [Pp]uesto|Competencias [Rr]equeridas|Compromiso|Contribuci.n al Hospital)\s*$",line.strip(),re.I)
                if m:
                    key,label=SECTIONS[m.group(1)]
                    if current["key"]!=key:finish();current={"key":key,"title":label,"blocks":[]}
                    # Preserve identification labels; other headings represented by section title and raw blocks.
                    if key=="identification" and norm(line).startswith("1. nombre"):current["blocks"].append(lb)
                else:current["blocks"].append(lb)
        finish()
    else:
        for b in blocks:
            if b.get("heading") and b.get("list") is None:
                finish();current={"key":"extra-"+str(len(sections)),"title":b["text"],"blocks":[]}
            else:current["blocks"].append(b)
        finish()
        warnings.append(issue("atypical","Documento operativo sin estructura estándar","Se conserva el contenido por bloques. No se infieren nombre formal del puesto, dependencia ni requisitos."))
    if not edition or not date:warnings.append(issue("missing_edition_date","Versión o fecha no declarada","No se completan fechas ni versiones a partir del nombre del archivo o de otros documentos.","headers"))
    if fields["name"] and title_name and norm(fields["name"])!=norm(title_name):
        warnings.append(issue("title_identity","Denominaciones internas diferentes",f"Título documental: «{title_name}». Nombre interno: «{fields['name']}».","identification"))
    if fields["supervises"]=="-":warnings.append(issue("supervision_dash","Personal a cargo sin definir","La fuente consigna un guion; no se interpreta como ausencia de personal.",locations.get("supervises","identification")))
    for k in ["reportsTo","supervisor"]:
        if fields[k] and re.search(r"\s(?:o|/|-)\s",fields[k]):
            warnings.append(issue("ambiguous_"+k,"Dependencia pendiente de interpretación",fields[k],locations.get(k,"identification")))
    if standard:
        found={s["key"] for s in sections}
        for key,label in SECTIONS.values():
            if key not in found:warnings.append(issue("missing_"+key,"Sección ausente",label))
    if not raw.strip():warnings.append(issue("no_text","Sin contenido extraíble","El archivo requiere revisión."))
    return {"parserVersion":PARSER_VERSION,"format":p.suffix[1:].upper(),"structure":"standard" if standard else "operational" if raw.strip() else "unclassified",
      "title":title,"titleName":title_name,"fields":fields,"fieldLocations":locations,"edition":edition,"documentDate":date,
      "rawText":raw,"blocks":blocks,"headers":headers,"extras":extras,"sections":sections,"warnings":warnings,"metadata":metadata}
if __name__=="__main__":
    try:
        p=Path(sys.argv[1])
        if p.suffix.lower() not in (".docx",".pdf"):raise ValueError("Formato no admitido")
        output=map_document(p)
        sys.stdout.buffer.write(json.dumps(output,ensure_ascii=False).encode("utf-8"))
    except Exception as e:
        print(str(e),file=sys.stderr);sys.exit(1)
