/**
 * js/proyectos.js
 * Corrección: Enlace de reportes con todos los parámetros necesarios.
 */

const ProyectosPage = {
    allProyectos: [],      
    currentProyectos: [], 

    init: function() {
        this.loadProyectos();
        this.setupSearch();
    },

    loadProyectos: async function() {
        const grid = document.getElementById('lista-proyectos');
        if (!grid) return;

        try {
            const res = await fetch('backend/api/proyectos.php?action=listar');
            const json = await res.json();

            let dataArray = json.data ? json.data : (Array.isArray(json) ? json : []);

            if (dataArray.length > 0) {
                this.allProyectos = dataArray;
                this.currentProyectos = dataArray; 
                this.renderProyectos(this.currentProyectos);
            } else {
                this.renderEmpty();
            }
        } catch (error) {
            console.error("Error:", error);
            grid.innerHTML = '<div class="col-span-full text-center py-20 text-red-500 font-bold">Error al conectar con el servidor.</div>';
        }
    },

    ordenar: function(criterio) {
        const btnRecientes = document.getElementById('btn-recientes');
        const btnPopulares = document.getElementById('btn-populares');
        
        const activeClass = "bg-white dark:bg-gray-700 shadow-sm text-primary font-bold";
        const inactiveClass = "text-gray-500 hover:text-gray-700 font-medium";

        if(criterio === 'recientes') {
            btnRecientes.className = `flex-1 sm:flex-none px-4 py-2 rounded-md text-xs transition-all ${activeClass}`;
            btnPopulares.className = `flex-1 sm:flex-none px-4 py-2 rounded-md text-xs transition-all ${inactiveClass}`;
            this.currentProyectos.sort((a, b) => b.id_proyecto - a.id_proyecto);
        } else {
            btnPopulares.className = `flex-1 sm:flex-none px-4 py-2 rounded-md text-xs transition-all ${activeClass}`;
            btnRecientes.className = `flex-1 sm:flex-none px-4 py-2 rounded-md text-xs transition-all ${inactiveClass}`;
            this.currentProyectos.sort((a, b) => {
                const votosA = parseInt(a.votos_positivos) || 0;
                const votosB = parseInt(b.votos_positivos) || 0;
                return votosB - votosA;
            });
        }
        this.renderProyectos(this.currentProyectos);
    },

    renderProyectos: function(proyectos) {
        const grid = document.getElementById('lista-proyectos');
        if (!proyectos || proyectos.length === 0) {
            this.renderEmpty();
            return;
        }

        grid.innerHTML = '';

        proyectos.forEach(p => {
            const imgUrl = p.imagen ? p.imagen : 'assets/noimage.jpg';
            const autor = p.nombre_proponente ? p.nombre_proponente : 'Ciudadano';
            const categoria = p.categoria ? p.categoria : 'General';
            const avatarAutor = p.foto_perfil ? p.foto_perfil : 'assets/avatars/avatar1.png';
            
            let fechaTexto = 'Reciente';
            if (p.fecha_creacion) {
                const dateObj = new Date(p.fecha_creacion);
                fechaTexto = dateObj.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
            }

            let pos = parseInt(p.votos_positivos) || 0;
            let neg = parseInt(p.votos_negativos) || 0;
            let totalVotos = pos + neg;
            let pct = totalVotos > 0 ? Math.round((pos / totalVotos) * 100) : 0;
            let pctRecursos = p.porcentaje_recursos || 0;
            let voluntarios = p.voluntarios_count || 0; 

            let barraRecursosHTML = '';
            if (p.estado === 'recaudacion') {
                barraRecursosHTML = `
                <div class="mt-4 mb-1">
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Recursos</span>
                        <span class="font-bold text-orange-500 text-xs">${pctRecursos}%</span>
                    </div>
                    <div class="w-full bg-orange-50 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-orange-500 h-full rounded-full transition-all duration-1000" style="width: ${pctRecursos}%"></div>
                    </div>
                </div>`;
            }

            // LIMPIEZA DE COMILLAS PARA EL TÍTULO (Evita errores en el onclick)
            const tituloSafe = p.titulo.replace(/'/g, "\\'"); 

            grid.innerHTML += `
                <div class="bg-white dark:bg-[#1e2329] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-xl transition-all flex flex-col group cursor-pointer h-full" onclick="window.location.href='proyecto.html?id=${p.id_proyecto}'">
                    
                    <div class="relative h-48 overflow-hidden">
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110" style="background-image: url('${imgUrl}');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#13181f]/80 to-transparent"></div>
                        
                        <button onclick="event.stopPropagation(); ProyectosPage.reportar(${p.id_proyecto}, '${tituloSafe}')" class="absolute top-4 left-4 bg-black/30 hover:bg-red-500/90 backdrop-blur text-white p-1.5 rounded-full transition-colors shadow-sm flex items-center justify-center z-10" title="Reportar">
                            <span class="material-symbols-outlined text-[18px]">flag</span>
                        </button>

                        <span class="absolute top-4 right-4 bg-white/90 backdrop-blur text-primary text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                            ${categoria}
                        </span>
                    </div>

                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary transition-colors leading-tight">
                            ${p.titulo}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">
                            ${p.descripcion}
                        </p>
                        
                        <div class="mt-auto">
                            <div class="flex justify-between items-end mb-2">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Aprobación</span>
                                    <span class="font-black text-primary text-xl leading-none">${pct}%</span>
                                </div>
                                <div class="flex gap-2">
                                    <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700" title="Voluntarios Inscritos">
                                        <span class="material-symbols-outlined text-[16px] text-gray-500 dark:text-gray-400">group</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">${voluntarios}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700" title="Votos Totales">
                                        <span class="material-symbols-outlined text-[16px] text-gray-500 dark:text-gray-400">how_to_vote</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">${totalVotos}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mb-2 overflow-hidden shadow-inner">
                                <div class="bg-gradient-to-r from-[#4f7bbf] to-primary h-full rounded-full transition-all duration-1000 ease-out" style="width: ${pct}%"></div>
                            </div>
                            
                            ${barraRecursosHTML}
                            
                            <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-3">
                                    <img src="${avatarAutor}" alt="Autor" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm" onerror="this.src='assets/avatars/avatar1.png'">
                                    <div class="flex flex-col">
                                        <span class="truncate max-w-[100px] text-[11px] font-bold text-gray-700 dark:text-gray-300 leading-tight">${autor}</span>
                                        <span class="text-[9px] text-gray-400 font-medium">${fechaTexto}</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <button onclick="event.stopPropagation(); ProyectosPage.compartir('${tituloSafe}', ${p.id_proyecto})" class="p-1.5 text-gray-400 hover:text-primary transition-colors flex items-center justify-center rounded-md hover:bg-gray-50 dark:hover:bg-gray-800" title="Compartir">
                                        <span class="material-symbols-outlined text-[18px]">share</span>
                                    </button>

                                    <span class="text-primary font-bold text-sm flex items-center gap-1 group-hover:translate-x-1 transition-transform ml-1">
                                        Detalles <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    },

    // --- NUEVA FUNCIÓN REPORTAR CORREGIDA ---
    reportar: function(id, titulo) {
        // Aseguramos que el título viaje seguro en la URL
        const tituloCodificado = encodeURIComponent(titulo || 'Proyecto #' + id);
        // AHORA SÍ: Incluimos 'tipo=proyecto' en la URL
        window.location.href = `reportes.html?tipo=proyecto&id=${id}&titulo=${tituloCodificado}`;
    },

    compartir: function(titulo, id) {
        const url = window.location.origin + `/proyecto.html?id=${id}`;
        if (navigator.share) {
            navigator.share({
                title: titulo,
                text: 'Mira este proyecto en Conecta Maracaibo',
                url: url
            }).catch(console.error);
        } else {
            navigator.clipboard.writeText(url).then(() => alert('Enlace copiado'));
        }
    },

    renderEmpty: function() {
        const grid = document.getElementById('lista-proyectos');
        grid.innerHTML = `
            <div class="col-span-full text-center py-16 bg-white dark:bg-[#1e2329] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">inbox</span>
                <h3 class="text-xl font-bold text-gray-600 dark:text-gray-300 mb-2">No hay proyectos para mostrar</h3>
                <p class="text-gray-500">Intenta cambiar los filtros o realiza una búsqueda diferente.</p>
            </div>
        `;
    },

    filterByCategory: function(categoria) {
        if (categoria === 'all') {
            this.currentProyectos = this.allProyectos;
        } else {
            this.currentProyectos = this.allProyectos.filter(p => 
                p.categoria && p.categoria.toLowerCase() === categoria.toLowerCase()
            );
        }
        this.renderProyectos(this.currentProyectos);
    },

    setupSearch: function() {
        const searchInput = document.getElementById('input-busqueda');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                if (term === '') {
                    this.currentProyectos = this.allProyectos;
                } else {
                    this.currentProyectos = this.allProyectos.filter(p => 
                        (p.titulo && p.titulo.toLowerCase().includes(term)) || 
                        (p.descripcion && p.descripcion.toLowerCase().includes(term))
                    );
                }
                this.renderProyectos(this.currentProyectos);
            });
        }
    }
};