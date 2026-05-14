

// 1. THEME CLAIR / SOMBRE avec cookie


function appliquerTheme(theme) {
    const link = document.getElementById('theme-css');
    if (!link) return;
    link.href = (theme === 'sombre') ? 'style-sombre.css' : 'style.css';
}

function basculerTheme() {
    const link         = document.getElementById('theme-css');
    const estSombre    = link && link.href.includes('sombre');
    const nouveauTheme = estSombre ? 'clair' : 'sombre';

    appliquerTheme(nouveauTheme);

    // Sauvegarder dans un cookie 30 jours
    const expiration = new Date();
    expiration.setDate(expiration.getDate() + 30);
    document.cookie = `theme=${nouveauTheme}; expires=${expiration.toUTCString()}; path=/`;

    const btn = document.getElementById('btn-theme');
    if (btn) {
        btn.textContent = (nouveauTheme === 'sombre') ? '☀️ Mode clair' : '🌙 Mode sombre';
    }
}

function lireCookie(nom) {
    const cookies = document.cookie.split(';');
    for (const c of cookies) {
        const [cle, val] = c.trim().split('=');
        if (cle === nom) return val;
    }
    return null;
}

// 2. INITIALISATION AU CHARGEMENT DE LA PAGE


document.addEventListener('DOMContentLoaded', () => {

    // Lire le cookie et appliquer le bon thème
    const themeSauvegarde = lireCookie('theme');
    const themesValides   = ['clair', 'sombre'];
    const themeAAppliquer = themesValides.includes(themeSauvegarde) ? themeSauvegarde : 'clair';
    appliquerTheme(themeAAppliquer);

    const btn = document.getElementById('btn-theme');
    if (btn) {
        btn.textContent = (themeAAppliquer === 'sombre') ? '☀️ Mode clair' : '🌙 Mode sombre';
    }

    initValidationConnexion();
    initValidationInscription();
    initCompteurCaracteres();
    initFiltresCarte();
    initTriCarte();
    initProfilEdition();
});


// 3. VALIDATION FORMULAIRE CONNEXION (côté client)
=

function initValidationConnexion() {
    const form = document.getElementById('form-connexion');
    if (!form) return;

    const inputEmail = document.getElementById('email');
    const inputMdp   = document.getElementById('mdp');

    ajouterIconeOeil(inputMdp);

    form.addEventListener('submit', (e) => {
        supprimerErreurs(form);
        let valide = true;

        if (!inputEmail.value.trim()) {
            afficherErreur(inputEmail, "L'adresse email est obligatoire.");
            valide = false;
        } else if (!estEmailValide(inputEmail.value.trim())) {
            afficherErreur(inputEmail, 'Format invalide. Exemple : nom@domaine.fr');
            valide = false;
        }

        if (!inputMdp.value) {
            afficherErreur(inputMdp, 'Le mot de passe est obligatoire.');
            valide = false;
        }

        if (!valide) e.preventDefault();
    });
}

// 4. VALIDATION FORMULAIRE INSCRIPTION (côté client)


function initValidationInscription() {
    const form = document.getElementById('form-inscription');
    if (!form) return;

    const inputNom    = document.getElementById('nom');
    const inputPrenom = document.getElementById('prenom');
    const inputEmail  = document.getElementById('email');
    const inputMdp    = document.getElementById('mdp');
    const inputMdp2   = document.getElementById('mdp_confirm');
    const inputTel    = document.getElementById('tel');

    // Ajouter l'icône œil sur les deux champs mot de passe
    ajouterIconeOeil(inputMdp);
    ajouterIconeOeil(inputMdp2);

    form.addEventListener('submit', (e) => {
        supprimerErreurs(form);
        let valide = true;

        if (!inputNom?.value.trim()) {
            afficherErreur(inputNom, 'Le nom est obligatoire.');
            valide = false;
        }
        if (!inputPrenom?.value.trim()) {
            afficherErreur(inputPrenom, 'Le prénom est obligatoire.');
            valide = false;
        }
        if (!inputEmail?.value.trim()) {
            afficherErreur(inputEmail, "L'email est obligatoire.");
            valide = false;
        } else if (!estEmailValide(inputEmail.value.trim())) {
            afficherErreur(inputEmail, 'Format invalide. Exemple : nom@domaine.fr');
            valide = false;
        }
        if (!inputMdp || inputMdp.value.length < 6) {
            afficherErreur(inputMdp, 'Le mot de passe doit contenir au moins 6 caractères.');
            valide = false;
        }
        if (inputMdp && inputMdp2 && inputMdp.value !== inputMdp2.value) {
            afficherErreur(inputMdp2, 'Les mots de passe ne correspondent pas.');
            valide = false;
        }
        if (inputTel?.value && !estTelValide(inputTel.value)) {
            afficherErreur(inputTel, 'Numéro invalide (10 chiffres, commencer par 0).');
            valide = false;
        }

        if (!valide) e.preventDefault();
    });
}


// 5. COMPTEUR DE CARACTERES EN TEMPS REEL


function initCompteurCaracteres() {
    const configs = [
        { id: 'mdp',         max: 50  },
        { id: 'mdp_confirm', max: 50  },
        { id: 'email',       max: 100 },
    ];

    for (const { id, max } of configs) {
        const input = document.getElementById(id);
        if (!input) continue;

        const compteur     = document.createElement('small');
        compteur.className = 'compteur-chars';
        compteur.textContent = `0 / ${max} caractères`;
        input.parentNode.appendChild(compteur);

        input.addEventListener('input', () => {
            const nb = input.value.length;
            compteur.textContent = `${nb} / ${max} caractères`;
            compteur.style.color = nb > max * 0.9 ? 'var(--terracotta)' : 'var(--brun-mid)';
        });
    }
}


// 6. FILTRES ASYNCHRONES SUR LA CARTE (fetch / requête async)


function initFiltresCarte() {
    const container = document.getElementById('grille-plats');
    if (!container) return;

    document.querySelectorAll('.filter-btn[data-cat], .filter-btn[data-regime]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();

            // Mettre à jour le bouton actif
            const groupe = btn.closest('.filter-buttons');
            if (groupe) {
                groupe.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }

            const catActif    = document.querySelector('.filter-btn[data-cat].active')?.dataset.cat    || 'toutes';
            const regimeActif = document.querySelector('.filter-btn[data-regime].active')?.dataset.regime || '';

            chargerPlatsAsync(catActif, regimeActif);
        });
    });
}

function chargerPlatsAsync(categorie, regime) {
    const container = document.getElementById('grille-plats');
    if (!container) return;

    container.innerHTML = '<p style="text-align:center;padding:40px;">⏳ Chargement...</p>';

    const params = new URLSearchParams();
    params.set('cat', categorie);
    if (regime) params.set('regime', regime);

    fetch('api_plats.php?' + params.toString())
        .then(r => r.json())
        .then(plats => {
            if (plats.length === 0) {
                container.innerHTML = '<p style="text-align:center;padding:40px;">Aucun plat pour ces critères.</p>';
                return;
            }

            container.className = 'grid-pizzas';
            container.innerHTML = plats.map(p => `
                <article class="pizza-card">
                    <img src="${echapper(p.image)}"
                         alt="${echapper(p.nom)}"
                         class="pizza-img"
                         onerror="this.src='images/margherita.jpg'">
                    <div class="pizza-info">
                        <h3>${echapper(p.nom)}</h3>
                        <p>${echapper(p.description)}</p>
                        ${p.allergenes?.length ? `<p class="allergenes">⚠️ ${p.allergenes.join(', ')}</p>` : ''}
                        <span class="price">${parseFloat(p.prix).toFixed(2)} €</span>
                        <a href="panier.php?action=ajouter&type=plat&id=${p.id}" class="btn-add">Ajouter au panier 🛒</a>
                    </div>
                </article>
            `).join('');
        })
        .catch(() => {
            container.innerHTML = '<p style="text-align:center;color:red;">Erreur lors du chargement.</p>';
        });
}


// 7. TRI DES PLATS COTE CLIENT (sans rechargement de page)


function initTriCarte() {
    const selectTri = document.getElementById('select-tri');
    if (!selectTri) return;

    selectTri.addEventListener('change', () => {
        const tri    = selectTri.value;
        const grille = document.getElementById('grille-plats');
        if (!grille) return;

        const cartes = Array.from(grille.querySelectorAll('.pizza-card'));

        cartes.sort((a, b) => {
            const prixA = parseFloat(a.querySelector('.price')?.textContent) || 0;
            const prixB = parseFloat(b.querySelector('.price')?.textContent) || 0;
            const nomA  = a.querySelector('h3')?.textContent || '';
            const nomB  = b.querySelector('h3')?.textContent || '';

            if (tri === 'prix-asc')  return prixA - prixB;
            if (tri === 'prix-desc') return prixB - prixA;
            if (tri === 'nom-asc')   return nomA.localeCompare(nomB, 'fr');
            if (tri === 'nom-desc')  return nomB.localeCompare(nomA, 'fr');
            return 0;
        });

        for (const carte of cartes) {
            grille.appendChild(carte);
        }
    });
}


// 8. MODIFICATION DU PROFIL EN ASYNCHRONE (fetch)


function initProfilEdition() {
    document.querySelectorAll('.edit-icon[data-champ]').forEach(el => {
        const champ = el.dataset.champ;
        el.innerHTML = `<span onclick="activerEditionChamp('${champ}')" title="Modifier" style="cursor:pointer;">✎</span>`;
    });
}

function activerEditionChamp(champ) {
    const row = document.querySelector(`.info-item[data-champ="${champ}"]`);
    if (!row) return;

    const valeurSpan     = row.querySelector('.valeur-champ');
    const valeurActuelle = valeurSpan.textContent.trim();
    const champsVides    = ['Non renseigné', 'Non renseignée', 'Aucun'];

    const input     = document.createElement('input');
    input.type      = 'text';
    input.value     = champsVides.includes(valeurActuelle) ? '' : valeurActuelle;
    input.className = 'input-edition';
    valeurSpan.replaceWith(input);
    input.focus();

    const crayon = row.querySelector('.edit-icon');
    crayon.innerHTML = `
        <button onclick="validerEdition('${champ}', this)" class="btn-ok"
                style="padding:4px 10px;font-size:11px;margin-right:4px;">✓ OK</button>
        <button onclick="annulerEdition('${champ}', '${echapper(valeurActuelle)}')" class="btn-ok"
                style="padding:4px 10px;font-size:11px;background:#888;">✕</button>
    `;
}

function validerEdition(champ, bouton) {
    const row         = document.querySelector(`.info-item[data-champ="${champ}"]`);
    const input       = row.querySelector('.input-edition');
    const nouvelleVal = input.value.trim();

    bouton.disabled    = true;
    bouton.textContent = '⏳';

    fetch('api_profil.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ champ: champ, valeur: nouvelleVal })
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes) {
            const span       = document.createElement('span');
            span.className   = 'valeur-champ';
            span.textContent = nouvelleVal || 'Non renseigné';
            input.replaceWith(span);

            const crayon = row.querySelector('.edit-icon');
            crayon.innerHTML = `<span onclick="activerEditionChamp('${champ}')" title="Modifier" style="cursor:pointer;">✎</span>`;

            afficherNotification('✅ ' + data.message, 'succes');
        } else {
            afficherNotification('❌ ' + (data.message || 'Erreur'), 'erreur');
            bouton.disabled    = false;
            bouton.textContent = '✓ OK';
        }
    })
    .catch(() => {
        afficherNotification('❌ Erreur de connexion', 'erreur');
        bouton.disabled    = false;
        bouton.textContent = '✓ OK';
    });
}

function annulerEdition(champ, valeurOriginale) {
    const row   = document.querySelector(`.info-item[data-champ="${champ}"]`);
    const input = row.querySelector('.input-edition');

    const span       = document.createElement('span');
    span.className   = 'valeur-champ';
    span.textContent = valeurOriginale;
    input.replaceWith(span);

    const crayon = row.querySelector('.edit-icon');
    crayon.innerHTML = `<span onclick="activerEditionChamp('${champ}')" title="Modifier" style="cursor:pointer;">✎</span>`;
}


// 9. FONCTIONS UTILITAIRES


function estEmailValide(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function estTelValide(tel) {
    return /^0[1-9][0-9]{8}$/.test(tel.replace(/\s/g, ''));
}

function afficherErreur(input, message) {
    if (!input) return;
    input.style.borderColor = '#c0392b';
    const msg       = document.createElement('small');
    msg.className   = 'erreur-champ';
    msg.textContent = message;
    input.parentNode.appendChild(msg);
}

function supprimerErreurs(form) {
    form.querySelectorAll('.erreur-champ').forEach(el => el.remove());
    form.querySelectorAll('input').forEach(i => i.style.borderColor = '');
}

function ajouterIconeOeil(input) {
    if (!input) return;

    const wrapper         = document.createElement('div');
    wrapper.style.cssText = 'position:relative;';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const oeil             = document.createElement('span');
    oeil.textContent       = '👁';
    oeil.style.cssText     = 'position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:16px;user-select:none;';

    oeil.addEventListener('click', () => {
        input.type       = input.type === 'password' ? 'text' : 'password';
        oeil.textContent = input.type === 'password' ? '👁' : '🙈';
    });

    wrapper.appendChild(oeil);
}

function afficherNotification(message, type) {
    const ancien = document.getElementById('notif-flash');
    if (ancien) ancien.remove();

    const notif       = document.createElement('div');
    notif.id          = 'notif-flash';
    notif.textContent = message;
    notif.style.cssText = `
        position: fixed;
        top: 90px;
        right: 20px;
        z-index: 9999;
        padding: 14px 20px;
        border-radius: 10px;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        background: ${type === 'succes' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'succes' ? '#155724' : '#721c24'};
        border-left: 5px solid ${type === 'succes' ? '#27ae60' : '#c0392b'};
        animation: fadeIn 0.3s ease;
    `;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 4000);
}

function echapper(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
