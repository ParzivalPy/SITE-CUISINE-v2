// Gestion de l'affichage du filtre étendu

const _ftc = document.getElementById('filter-type-checkbox');
const filterTypeExtend = document.getElementById('filter-type-extend');

if (_ftc && filterTypeExtend) {
    _ftc.addEventListener('change', function() {
        if (this.checked) {
            _fptc.checked = false;
            _foc.checked = false;
            filterPrepTimeExtend.style.display = 'none';
            filterOriginExtend.style.display = 'none';
            
            // Remettre les autres flèches à 0
            let arrowPrep = document.getElementById("arrow-prep");
            let arrowOrigin = document.getElementById("arrow-origin");
            if (arrowPrep) arrowPrep.style.transform = "rotate(0deg)";
            if (arrowOrigin) arrowOrigin.style.transform = "rotate(0deg)";
        }
        filterTypeExtend.style.display = _ftc.checked ? 'flex' : 'none';
        let arrow = document.getElementById("arrow-type");
        if (arrow) {
            if (_ftc.checked) {
                arrow.style.transform = "rotate(180deg)";
            } else {
                arrow.style.transform = "rotate(0deg)";
            }
        }
    });
}

const _fptc = document.getElementById('filter-prep-time-checkbox');
const filterPrepTimeExtend = document.getElementById('filter-prep-time-extend');

if (_fptc && filterPrepTimeExtend) {
    _fptc.addEventListener('change', function() {
        if (this.checked) {
            _ftc.checked = false;
            _foc.checked = false;
            filterTypeExtend.style.display = 'none';
            filterOriginExtend.style.display = 'none';
            
            // Remettre les autres flèches à 0
            let arrowType = document.getElementById("arrow-type");
            let arrowOrigin = document.getElementById("arrow-origin");
            if (arrowType) arrowType.style.transform = "rotate(0deg)";
            if (arrowOrigin) arrowOrigin.style.transform = "rotate(0deg)";
        }
        filterPrepTimeExtend.style.display = _fptc.checked ? 'flex' : 'none';
        let arrow = document.getElementById("arrow-prep");
        if (arrow) {
            if (_fptc.checked) {
                arrow.style.transform = "rotate(180deg)";
            } else {
                arrow.style.transform = "rotate(0deg)";
            }
        }
    });
}

const _foc = document.getElementById('filter-origin-checkbox');
const filterOriginExtend = document.getElementById('filter-origin-extend');

if (_foc && filterOriginExtend) {
    _foc.addEventListener('change', function() {
        if (this.checked) {
            _ftc.checked = false;
            _fptc.checked = false;
            filterTypeExtend.style.display = 'none';
            filterPrepTimeExtend.style.display = 'none';
            
            // Remettre les autres flèches à 0
            let arrowType = document.getElementById("arrow-type");
            let arrowPrep = document.getElementById("arrow-prep");
            if (arrowType) arrowType.style.transform = "rotate(0deg)";
            if (arrowPrep) arrowPrep.style.transform = "rotate(0deg)";
        }
        filterOriginExtend.style.display = _foc.checked ? 'flex' : 'none';
        let arrow = document.getElementById("arrow-origin");
        if (arrow) {
            if (_foc.checked) {
                arrow.style.transform = "rotate(180deg)";
            } else {
                arrow.style.transform = "rotate(0deg)";
            }
        }
    });
}

// Récupérer les données PHP injectées dans le HTML
const paysLabels = window.paysLabels || [];
const paysMap = window.paysMap || {};
const originsWanted = window.originsWanted || [];

const input = document.getElementById('origin-input');
const proposed = document.querySelector('.proposed-origins');

function escapeHtml(str){
    return str.replace(/[&<>"']/g, function(m){ 
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; 
    });
}

if (input) {
    input.addEventListener('input', function(){
        if (this.value != "") {
            this.placeholder = "";
        } else {
            this.placeholder = "Chercher une origine";
        }
        
        const val = this.value.trim();
        if (val !== '' && paysLabels.indexOf(val) !== -1) {
            if (proposed) {
                proposed.style.display = 'flex';
            }

            const codeForVal = paysMap[val] || val;

            if (originsWanted.indexOf(val) !== -1 || originsWanted.indexOf(codeForVal) !== -1) {
                console.log('Origine déjà présente dans origins-wanted');
                return;
            } else {
                // Créer le tag de pays
                let div = document.createElement('div');
                div.className = 'proposed-origin-item categorie';
                div.style.cursor = 'pointer';

                let img = document.createElement('img');
                const code = paysMap[val] ? paysMap[val].toLowerCase() : '';
                img.src = 'https://kapowaz.github.io/square-flags/flags/' + code + '.svg';
                
                img.className = 'img';
                img.width = 20;
                img.alt = '?';
                img.style = 'border-radius: 3px; margin: 2px; display: flex; align-items: center; justify-content: center;';
                div.appendChild(img);

                let span = document.createElement('span');
                span.textContent = val;

                // Ajouter le tag au HTML
                div.appendChild(span);
                proposed.appendChild(div);

                originsWanted.push(codeForVal);
                console.log('Updated originsWanted:', originsWanted);

                // Ajouter un événement click pour retirer le tag quand cliqué
                div.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Retirer de l'array originsWanted
                    const index = originsWanted.indexOf(codeForVal);
                    if (index > -1) {
                        originsWanted.splice(index, 1);
                    }
                    // Retirer du DOM
                    if (this.parentNode) {
                        this.parentNode.removeChild(this);
                    }
                    console.log('Updated originsWanted after removal:', originsWanted);
                });

                document.getElementById('origin-filter-form').reset();
            }
        }
    });
}

// Gestion des likes avec vérification d'authentification
document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.like-button');

    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Vérifier si le bouton est désactivé (utilisateur non connecté)
            if (this.hasAttribute('disabled')) {
                e.preventDefault();
                e.stopPropagation();
                
                // Créer et afficher le toast
                const containerId = 'toaster-list';
                let list = document.getElementById(containerId);
                if (!list) {
                    list = document.createElement('div');
                    list.id = containerId;
                    list.className = 'toaster-list';
                    document.body.appendChild(list);
                }

                const box = document.createElement('div');
                box.className = 'toaster-box error show';
                box.innerHTML = '<h4>Authentification requise</h4><p>Vous devez vous connecter pour liker une recette</p>';
                list.appendChild(box);

                // Supprimer le toast après 5 secondes
                setTimeout(() => {
                    box.classList.remove('show');
                    box.classList.add('hide');
                    setTimeout(() => {
                        if (box.parentNode === list) list.removeChild(box);
                    }, 400);
                }, 5000);
            }
        });
    });
});