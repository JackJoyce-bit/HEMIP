/* Données de démonstration uniquement : le back-end doit alimenter ces éléments depuis la session. */
const HEMIP_DEMO={
 student:{fullName:"Nom de l'étudiant",program:"Filière à connecter",level:"Niveau à connecter",academicYear:"Année académique à connecter"},
 courses:[
  {title:"Titre du cours",subject:"Matière à connecter",teacher:"Nom du professeur",date:"Date de publication",description:"La description du cours sera affichée ici.",resources:[{label:"Télécharger le support PDF",type:"pdf",url:"#",download:true}]},
  {title:"Ressources pédagogiques",subject:"Autre matière",teacher:"Nom du professeur",date:"Date de publication",description:"Les documents, vidéos ou liens publiés par l'enseignant seront accessibles ici.",resources:[{label:"Télécharger le document",type:"doc",url:"#",download:true},{label:"Voir la vidéo",type:"video",url:"#",download:false}]}
 ],
 timetable:{image:"assets/img/emploi-du-temps.jpg",pdf:"assets/documents/emploi-du-temps.pdf",published:false}
};
const esc=s=>String(s??"").replace(/[&<>\"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
const icons={pdf:"ri-file-pdf-2-line",doc:"ri-file-word-2-line",video:"ri-video-line",link:"ri-external-link-line"};
document.addEventListener("DOMContentLoaded",()=>{
 document.querySelectorAll("[data-student]").forEach(el=>{const k=el.dataset.student;
    if(HEMIP_DEMO.student[k]!==undefined)el.textContent=HEMIP_DEMO.student[k]});
 const initials=HEMIP_DEMO.student.fullName.split(" ").filter(Boolean).slice(0,2).map(x=>x[0]).join("").toUpperCase();
 document.querySelectorAll("[data-student-initials]").forEach(el=>el.textContent=initials);
 document.querySelector(".menu-toggle")?.addEventListener("click",()=>document.querySelector(".sidebar")?.classList.toggle("open"));
 const search=document.getElementById("course-search"),filter=document.getElementById("subject-filter");
 if(document.getElementById("course-grid")){const apply=()=>renderCourses(HEMIP_DEMO.courses.filter(c=>(c.title+" "+c.subject+" "+c.teacher).toLowerCase().includes((search?.value||"").toLowerCase())&&(filter?.value==="all"||!filter?.value||c.subject===filter.value)));
    search?.addEventListener("input",apply);
    filter?.addEventListener("change",apply);apply()}
 renderTimetable();
});
function renderCourses(list){const grid=document.getElementById("course-grid");
    if(!grid)return;
    if(!list.length){grid.innerHTML='<div class="empty" style="grid-column:1/-1">Aucun cours ne correspond à votre recherche.</div>';
        return}
 grid.innerHTML=list.map(c=>`<article class="course"><div class="course-top"><span class="course-icon"><i class="ri-book-open-line"></i></span><span class="course-date"><i class="ri-calendar-line"></i> ${esc(c.date)}</span></div><h3>${esc(c.title)}</h3><div class="subject">${esc(c.subject)}</div><p class="description">${esc(c.description)}</p><div class="teacher">Enseignant : <b>${esc(c.teacher)}</b></div><div class="resources">${(c.resources||[]).map(r=>`<a class="resource" href="${esc(r.url)}" ${r.download?'download':''} ${r.download?'':'target="_blank" rel="noopener"'}><i class="${icons[r.type]||"ri-file-line"}"></i>${esc(r.label)}</a>`).join("")}</div></article>`).join("")}
function renderTimetable(){const preview=document.getElementById("timetable-preview"),download=document.getElementById("timetable-download"),empty=document.getElementById("timetable-empty");
    if(!preview)return;
    const t=HEMIP_DEMO.timetable;
    if(!t.published){preview.hidden=true;
        download.hidden=true;
        empty.hidden=false;
        return}
        preview.src=t.image;
        preview.hidden=false;
        download.href=t.pdf;
        download.hidden=false;
        empty.hidden=true}
