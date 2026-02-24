// js/auth.js

document.addEventListener('DOMContentLoaded', () => {

  const token = localStorage.getItem('token');
  const userName = localStorage.getItem('userName');
  const userAvatar = localStorage.getItem('userAvatar') || 'avatar1.png';

  const guestEls = document.querySelectorAll('.js-auth-guest');
  const loggedEls = document.querySelectorAll('.js-auth-logged');

  if (token) {
    guestEls.forEach(el => el.classList.add('hidden'));
    loggedEls.forEach(el => el.classList.remove('hidden'));

    document.querySelectorAll('.js-user-name')
      .forEach(el => el.textContent = userName || 'Usuario');

    const avatarImg = document.getElementById('user-avatar');
    if (avatarImg) {
      avatarImg.src = 'assets/avatars/' + userAvatar;
    }

  } else {
    guestEls.forEach(el => el.classList.remove('hidden'));
    loggedEls.forEach(el => el.classList.add('hidden'));
  }

  // Dropdown
  const toggle = document.getElementById('user-menu-toggle');
  const dropdown = document.querySelector('.js-user-dropdown');

  if (toggle && dropdown) {
    toggle.addEventListener('click', () => {
      dropdown.classList.toggle('hidden');
    });
  }

  // Logout
  document.querySelectorAll('.js-logout').forEach(btn => {
    btn.addEventListener('click', () => {
      localStorage.clear();
      window.location.href = 'index.html';
    });
  });

});
