
// Données fictives à remplacer par les données de l'API du professeur connecté.
const PROF_DEMO={name:"Nom du professeur",classes:[
{id:"gl-l1",cycle:"Licence",program:"Génie Logiciel",level:"L1",year:"2026-2027"},
{id:"gl-l2",cycle:"Licence",program:"Génie Logiciel",level:"L2",year:"2026-2027"},
{id:"rt-l2",cycle:"Licence",program:"Réseaux & Télécommunications",level:"L2",year:"2026-2027"}],
courses:[
{id:1,title:"Introduction à la programmation",subject:"Programmation",description:"Présentation des bases de la programmation et des algorithmes.",classId:"gl-l1",date:"12/11/2026",resources:2},
{id:2,title:"Architecture des réseaux",subject:"Réseaux",description:"Notions fondamentales sur l'architecture et les équipements réseau.",classId:"rt-l2",date:"10/11/2026",resources:1}]};
const $=s=>document.querySelector(s);
const esc=s=>String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll("[data-prof-name]").forEach(e=>e.textContent=PROF_DEMO.name);
    document.querySelectorAll("[data-prof-initials]").forEach(e=>e.textContent="NP");
    $(".menu-toggle")?.addEventListener("click",()=>$(".sidebar")?.classList.toggle("open"));
    renderClasses();
    renderCourses();
    fillSelects();
    $("#course-search")?.addEventListener("input",renderCourses);
    $("#class-filter")?.addEventListener("change",renderCourses);
    $("#publish-form")?.addEventListener("submit",submitCourse);
    $("#cycle")?.addEventListener("change",filterPrograms);
    $("#program")?.addEventListener("change",filterLevels);});
function className(id){let c=PROF_DEMO.classes.find(x=>x.id===id);
    return c?`${c.program} · ${c.level}`:"Classe non définie"}
function renderClasses(){let e=$("#class-grid");
    if(!e)return;e.innerHTML=PROF_DEMO.classes.map(c=>`<article class="class-card"><span class="class-icon"><i class="ri-group-line"></i></span><h3>${esc(c.program)}</h3><p>${esc(c.cycle)} · ${esc(c.level)}</p><div class="class-meta"><span class="pill">${esc(c.year)}</span><span class="pill">Classe attribuée</span></div></article>`).join("");}
function renderCourses(){let e=$("#course-list");
    if(!e)return;
    let q=($("#course-search")?.value||"").toLowerCase(),f=$("#class-filter")?.value||"all";let a=PROF_DEMO.courses.filter(c=>(c.title+" "+c.subject+" "+className(c.classId)).toLowerCase().includes(q)&&(f==="all"||c.classId===f));
    e.innerHTML=a.length?a.map(c=>`<article class="course-card"><div class="course-main"><span class="course-icon"><i class="ri-book-open-line"></i></span><div><h3>${esc(c.title)}</h3><div class="course-sub">${esc(c.subject)} · ${esc(className(c.classId))}</div><p class="course-desc">${esc(c.description)}</p><div class="course-info"><i class="ri-calendar-line"></i> Publié le ${esc(c.date)} · ${c.resources} ressource(s)</div></div></div><div class="course-actions"><button class="btn btn-danger" type="button" onclick="deleteCourse(${c.id})"><i class="ri-delete-bin-line"></i> Supprimer</button></div></article>`).join(""):'<div class="empty"><i class="ri-inbox-line"></i>Aucun cours ne correspond à votre recherche.</div>';}
function deleteCourse(id){let c=PROF_DEMO.courses.find(x=>x.id===id);
    if(!c||!confirm(`Supprimer le cours « ${c.title} » ?`))return;PROF_DEMO.courses=PROF_DEMO.courses.filter(x=>x.id!==id);
    renderCourses();alert("Suppression de démonstration uniquement : connectez cette action à votre API PHP.");}
function fillSelects(){let f=$("#class-filter"),s=$("#class-select");
    if(f)f.innerHTML='<option value="all">Toutes les classes</option>'+PROF_DEMO.classes.map(c=>`<option value="${c.id}">${esc(c.program)} — ${esc(c.level)}</option>`).join("");
    if(s)s.innerHTML='<option value="">Sélectionner une classe attribuée</option>'+PROF_DEMO.classes.map(c=>`<option value="${c.id}">${esc(c.cycle)} · ${esc(c.program)} · ${esc(c.level)}</option>`).join("");}
function filterPrograms(){let cyc=$("#cycle").value,p=$("#program");
    let a=PROF_DEMO.classes.filter(c=>!cyc||c.cycle===cyc);
    p.innerHTML='<option value="">Choisir une filière</option>'+[...new Set(a.map(c=>c.program))].map(x=>`<option>${esc(x)}</option>`).join("");filterLevels();}
function filterLevels(){let cyc=$("#cycle").value,pr=$("#program").value,l=$("#level");let a=PROF_DEMO.classes.filter(c=>(!cyc||c.cycle===cyc)&&(!pr||c.program===pr));
    l.innerHTML='<option value="">Choisir un niveau</option>'+[...new Set(a.map(c=>c.level))].map(x=>`<option>${esc(x)}</option>`).join("");
    let match=a.find(c=>c.level===l.value);if(match)$("#class-select").value=match.id;}
function submitCourse(e){e.preventDefault();let d=new FormData(e.currentTarget),id=d.get("classId");
    if(!PROF_DEMO.classes.some(c=>c.id===id)){alert("Sélectionnez une classe attribuée à votre compte.");
        return;
    }
    let n=($("#course-files")?.files.length||0)+(d.get("resourceLinks")||"").split("\\n").filter(x=>x.trim()).length;PROF_DEMO.courses.unshift({id:Date.now(),title:d.get("title"),subject:d.get("subject"),description:d.get("description"),classId:id,date:new Date().toLocaleDateString("fr-FR"),resources:n});alert("Cours ajouté à la démonstration. Le téléversement et l'enregistrement réels nécessitent l'API du back-end.");
    e.currentTarget.reset();
    renderCourses();}
