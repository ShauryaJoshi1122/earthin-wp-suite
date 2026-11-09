/**
 * Accessible Mobile Navigation Script for Earthin Theme
 * Zero jQuery, pure vanilla JavaScript
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const nav = document.getElementById('site-navigation');
        if (!nav) return;

        const button = nav.querySelector('.menu-toggle');
        if (!button) return;

        const menu = nav.querySelector('ul');
        if (!menu) return;

        button.setAttribute('aria-expanded', 'false');

        button.addEventListener('click', function() {
            const expanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', !expanded);
            nav.classList.toggle('toggled');
        });
    });
})();
