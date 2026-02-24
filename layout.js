/**
 * js/layout.js
 * Generador de Header/Footer con Menú Completo y Notificaciones
 */

const Layout = {
    init: function() {
        // Limpiar para evitar duplicados
        const oldHeader = document.querySelector('header');
        if (oldHeader) oldHeader.remove();
        
        const oldFooter = document.querySelector('footer');
        if (oldFooter) oldFooter.remove();

        this.renderHeader();
        this.renderFooter();
        this.checkActiveLink();
        this.setupDropdowns();

        // Sistema de Notificaciones (Solo si hay token)
        if (localStorage.getItem('token')) {
            this.checkNotifications();
            setInterval(() => this.checkNotifications(), 15000); 
        }
    },

    logout: function() {
        if(confirm('¿Seguro que deseas cerrar sesión?')) {
            localStorage.removeItem('token');
            localStorage.removeItem('usuario');
            window.location.href = 'index.html';
        }
    },

    renderHeader: function() {
        const header = document.createElement('header');
        header.className = "fixed top-0 left-0 w-full bg-white/95 backdrop-blur-md border-b border-gray-200 z-[100] shadow-sm transition-all duration-300";
        
        const token = localStorage.getItem('token');
        const usuarioStr = localStorage.getItem('usuario');
        
        let usuario = null;
        let userName = 'Usuario';
        let userAvatar = 'assets/avatars/avatar1.png'; 
        let isAdmin = false;

        if (token && usuarioStr) {
            try {
                usuario = JSON.parse(usuarioStr);
                userName = usuario.nombre ? usuario.nombre.split(' ')[0] : 'Usuario';
                if (usuario.foto_perfil) userAvatar = usuario.foto_perfil;
                if (usuario.rol && ['admin', 'administrador'].includes(usuario.rol.toLowerCase())) {
                    isAdmin = true;
                }
            } catch (e) { console.error(e); }
        }

        header.innerHTML = `
        <div class="max-w-[1440px] mx-auto px-6 md:px-12 h-[72px] flex items-center justify-between">
            
            <a href="index.html" class="flex items-center gap-2 hover:opacity-80 transition-opacity w-48 group">
                <span class="material-symbols-outlined text-[32px] text-[#2c5aa0]">diversity_3</span>
                <div class="flex flex-col leading-none">
                    <span class="font-bold text-base text-[#2c5aa0]">Proyectos</span>
                    <span class="font-bold text-base text-[#2c5aa0]">Maracaibo</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-1 bg-gray-100/80 p-1.5 rounded-full border border-gray-200/50 shadow-inner">
                <a href="index.html" class="nav-link px-5 py-2 text-sm font-bold text-gray-500 rounded-full hover:bg-white hover:text-[#2c5aa0] hover:shadow-sm transition-all duration-200">Inicio</a>
                <a href="proyectos.html" class="nav-link px-5 py-2 text-sm font-bold text-gray-500 rounded-full hover:bg-white hover:text-[#2c5aa0] hover:shadow-sm transition-all duration-200">Proyectos</a>
                
                <div class="relative group h-full flex items-center">
                    <a href="donaciones.html" class="nav-link px-5 py-2 text-sm font-bold text-gray-500 rounded-full group-hover:bg-white group-hover:text-[#2c5aa0] group-hover:shadow-sm transition-all duration-200 flex items-center gap-1">
                        Donaciones
                    </a>
                    <div class="hidden group-hover:block absolute top-full left-1/2 -translate-x-1/2 pt-4 w-64 z-50">
                        <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden p-2 animate-[fadeIn_0.2s_ease-out]">
                            <a href="crear-donacion.html?modo=oferta" class="flex items-start gap-3 p-3 rounded-lg hover:bg-blue-50 transition-colors group/item">
                                <div class="bg-blue-100 text-primary p-2 rounded-lg group-hover/item:bg-primary group-hover/item:text-white transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">volunteer_activism</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-gray-800">Quiero Donar</span>
                                    <span class="block text-xs text-gray-500">Ofrecer recursos</span>
                                </div>
                            </a>
                            <a href="crear-donacion.html?modo=solicitud" class="flex items-start gap-3 p-3 rounded-lg hover:bg-orange-50 transition-colors group/item mt-1">
                                <div class="bg-orange-100 text-orange-600 p-2 rounded-lg group-hover/item:bg-orange-500 group-hover/item:text-white transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">diversity_1</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-gray-800">Solicitar Ayuda</span>
                                    <span class="block text-xs text-gray-500">Necesito apoyo</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="sobre-nosotros.html" class="nav-link px-5 py-2 text-sm font-bold text-gray-500 rounded-full hover:bg-white hover:text-[#2c5aa0] hover:shadow-sm transition-all duration-200">Nosotros</a>
            </nav>

            <div class="flex items-center justify-end w-auto min-w-[12rem] gap-3">
                ${token ? `
                    <div class="relative">
                        <button id="btn-noti" onclick="Layout.toggleNoti()" class="w-10 h-10 rounded-full hover:bg-gray-100 text-gray-500 relative transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-[24px]">notifications</span>
                            <span id="badge-noti" class="hidden absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full animate-pulse"></span>
                        </button>
                        
                        <div id="dropdown-noti" class="hidden absolute top-full right-0 mt-3 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-[60] animate-[fadeIn_0.1s_ease-out]">
                            <div class="p-3 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                                <span class="text-xs font-bold text-gray-500 uppercase">Notificaciones</span>
                                <button onclick="Layout.markAllRead()" class="text-[10px] text-[#2c5aa0] font-bold hover:underline">Marcar leídas</button>
                            </div>
                            <div id="lista-noti" class="max-h-64 overflow-y-auto scrollbar-thin">
                                <p class="text-center text-xs text-gray-400 py-6">Cargando...</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button id="btn-user" class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full border border-transparent hover:border-gray-200 hover:bg-gray-50 transition-all group">
                            <img src="${userAvatar}" class="w-9 h-9 rounded-full bg-gray-200 object-cover border border-white shadow-sm ring-2 ring-transparent group-hover:ring-gray-200 transition-all" onerror="this.src='assets/avatars/avatar1.png'">
                            <div class="flex flex-col items-start hidden sm:flex">
                                <span class="text-xs font-bold text-gray-700 max-w-[80px] truncate leading-none mb-0.5">${userName}</span>
                                <span class="text-[10px] text-gray-400 font-medium leading-none">Conectado</span>
                            </div>
                            <span class="material-symbols-outlined text-gray-400 text-[20px] group-hover:text-[#2c5aa0] transition-colors">expand_more</span>
                        </button>

                        <div id="menu-user" class="hidden absolute top-full right-0 mt-3 w-60 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50 animate-[fadeIn_0.1s_ease-out]">
                            <div class="p-2 space-y-1">
                                <a href="perfil.html?tab=perfil" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2c5aa0] rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">person</span> Mi Perfil
                                </a>
                                
                                <a href="carnet.html" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2c5aa0] rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">badge</span> Mi Carnet
                                </a>

                                <a href="perfil.html?tab=proyectos" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2c5aa0] rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">folder_shared</span> Mis Proyectos
                                </a>

                                <a href="perfil.html?tab=donaciones" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2c5aa0] rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">volunteer_activism</span> Mis Donaciones
                                </a>

                                <a href="perfil.html?tab=solicitudes" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2c5aa0] rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">diversity_1</span> Mis Solicitudes
                                </a>
                                
                                <div class="h-px bg-gray-100 my-1"></div>
                                
                                <a href="ajustes.html" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2c5aa0] rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">settings</span> Ajustes
                                </a>
                                
                                ${isAdmin ? `
                                <a href="admin.html" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-[#2c5aa0] rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span> Panel Admin
                                </a>
                                ` : ''}
                            </div>
                            <div class="p-2 border-t border-gray-100 bg-gray-50">
                                <button onclick="Layout.logout()" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-100/50 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">logout</span> Cerrar Sesión
                                </button>
                            </div>
                        </div>
                    </div>
                ` : `
                    <div class="flex items-center gap-2">
                        <a href="login.html" class="text-sm font-bold text-gray-600 hover:text-[#2c5aa0] transition-colors px-3 py-2">Ingresar</a>
                        <a href="registro.html" class="bg-[#2c5aa0] hover:bg-[#1e4580] text-white text-sm font-bold px-5 py-2.5 rounded-full shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5">
                            Registrarse
                        </a>
                    </div>
                `}
                <button class="md:hidden p-2 text-gray-500 hover:bg-gray-100 rounded-lg"><span class="material-symbols-outlined">menu</span></button>
            </div>
        </div>
        `;
        document.body.prepend(header);
    },

    renderFooter: function() {
        const footer = document.createElement('footer');
        footer.className = "bg-gray-900 border-t border-gray-800 mt-auto pt-16 pb-8 text-gray-400 w-full";
        
        footer.innerHTML = `
        <div class="max-w-[1440px] mx-auto px-6 md:px-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-3xl text-[#2c5aa0]">diversity_3</span>
                        <div class="flex flex-col leading-none">
                            <span class="font-bold text-base text-[#2c5aa0]">Proyectos</span>
                            <span class="font-bold text-base text-[#2c5aa0]">Maracaibo</span>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">Gestión transparente y colaborativa para el desarrollo social de Maracaibo.</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Plataforma</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="proyectos.html" class="hover:text-[#2c5aa0] transition-colors">Proyectos</a></li>
                        <li><a href="donaciones.html" class="hover:text-[#2c5aa0] transition-colors">Donaciones</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Comunidad</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="sobre-nosotros.html" class="hover:text-[#2c5aa0] transition-colors">Nosotros</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Contacto</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2"><span class="material-symbols-outlined text-xs">mail</span> contacto@maracaibo.org</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center"><p class="text-xs text-gray-500">© 2026 Proyectos Maracaibo.</p></div>
        </div>`;
        document.body.appendChild(footer);
    },

    setupDropdowns: function() {
        document.addEventListener('click', (e) => { 
            const userMenu = document.getElementById('menu-user');
            const btnUser = document.getElementById('btn-user');
            const notiMenu = document.getElementById('dropdown-noti');
            const btnNoti = document.getElementById('btn-noti');

            if (userMenu && !userMenu.classList.contains('hidden') && btnUser && !btnUser.contains(e.target) && !userMenu.contains(e.target)) { 
                userMenu.classList.add('hidden'); 
            }
            if (notiMenu && !notiMenu.classList.contains('hidden') && btnNoti && !btnNoti.contains(e.target) && !notiMenu.contains(e.target)) {
                notiMenu.classList.add('hidden');
            }
        });

        const btnUser = document.getElementById('btn-user');
        if (btnUser) {
            btnUser.addEventListener('click', (e) => { 
                e.stopPropagation(); 
                document.getElementById('menu-user').classList.toggle('hidden'); 
                document.getElementById('dropdown-noti')?.classList.add('hidden'); 
            });
        }
    },

    toggleNoti: function() {
        const menu = document.getElementById('dropdown-noti');
        if(menu) {
            menu.classList.toggle('hidden');
            document.getElementById('menu-user')?.classList.add('hidden'); 
        }
    },

    checkNotifications: async function() {
        const token = localStorage.getItem('token');
        if(!token) return;
        try {
            // Nota: Asegúrate de crear el archivo notificaciones.php
            const res = await fetch('backend/api/notificaciones.php?action=listar', {
                headers: {'Authorization': 'Bearer ' + token}
            });
            const json = await res.json();
            
            if(json.ok) {
                const badge = document.getElementById('badge-noti');
                const sinLeer = json.data.filter(n => n.leido == 0).length;
                if(badge) {
                    if(sinLeer > 0) badge.classList.remove('hidden');
                    else badge.classList.add('hidden');
                }
                this.renderNotificationList(json.data);
            }
        } catch(e) { console.error("Error notis", e); }
    },

    renderNotificationList: function(notis) {
        const list = document.getElementById('lista-noti');
        if(!list) return;
        
        if(notis.length === 0) {
            list.innerHTML = '<p class="text-center text-xs text-gray-400 py-6">Sin novedades.</p>';
            return;
        }

        list.innerHTML = '';
        notis.forEach(n => {
            const leidoClass = n.leido == 1 ? 'opacity-60 bg-white' : 'bg-blue-50/60 border-l-4 border-l-[#2c5aa0]';
            const iconColor = n.tipo === 'sistema' ? 'text-gray-400' : (n.tipo === 'proyecto' ? 'text-blue-500' : 'text-green-500');
            const icon = n.tipo === 'sistema' ? 'info' : (n.tipo === 'proyecto' ? 'engineering' : 'volunteer_activism');

            const fechaObj = new Date(n.fecha_creacion);
            const fechaStr = fechaObj.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
            const horaStr = fechaObj.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            
            list.innerHTML += `
            <div onclick="Layout.readNoti(${n.id_notificacion}, '${n.enlace}')" class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition flex gap-3 ${leidoClass}">
                <div class="mt-1 ${iconColor} shrink-0">
                    <span class="material-symbols-outlined text-[20px]">${icon}</span>
                </div>
                <div class="w-full">
                    <p class="text-xs text-gray-800 leading-snug font-medium mb-1">${n.mensaje}</p>
                    <p class="text-[10px] text-gray-400 font-semibold flex justify-between w-full">
                        <span>${fechaStr} • ${horaStr}</span>
                        ${n.leido == 0 ? '<span class="w-2 h-2 bg-[#2c5aa0] rounded-full"></span>' : ''}
                    </p>
                </div>
            </div>`;
        });
    },

    readNoti: async function(id, link) {
        const token = localStorage.getItem('token');
        try {
            await fetch(`backend/api/notificaciones.php?action=leer&id=${id}`, {
                headers: {'Authorization': 'Bearer ' + token}
            });
        } catch(e) {}
        if(link && link !== 'null' && link !== '#' && link !== '') {
            window.location.href = link;
        } else {
            this.checkNotifications();
        }
    },

    markAllRead: async function() {
        const token = localStorage.getItem('token');
        if(confirm("¿Marcar todas como leídas?")) {
            try {
                await fetch(`backend/api/notificaciones.php?action=leer_todas`, {
                    headers: {'Authorization': 'Bearer ' + token}
                });
                this.checkNotifications();
            } catch(e) {}
        }
    },

    checkActiveLink: function() {
        const path = window.location.pathname;
        const links = document.querySelectorAll('.nav-link');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if(href !== 'index.html' && path.includes(href)) {
                link.classList.remove('text-gray-500');
                link.classList.add('bg-white', 'text-[#2c5aa0]', 'shadow-sm');
            } else if ((path.endsWith('/') || path.endsWith('index.html')) && href === 'index.html') {
                link.classList.remove('text-gray-500');
                link.classList.add('bg-white', 'text-[#2c5aa0]', 'shadow-sm');
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => { Layout.init(); });