# front.md — Garage Horizon Auto

**Version du document** : 1.0 — 11/09/2026
**Stack verrouillée** : Twig + Bootstrap 5 + Stimulus (AssetMapper), Symfony 7.4 LTS
**Cible d'accessibilité** : WCAG 2.2 AA et RGAA

---

## 1. Glossaire UX

| Terme | Définition |
|---|---|
| **Action principale** | L'action que la page cherche à provoquer. Une seule par page. |
| **Clic-to-call** | Lien `tel:` déclenchant l'appel depuis un mobile. |
| **Bandeau exceptionnel** | Message temporaire en haut de page, administrable. |
| **État d'ouverture** | Indicateur « Ouvert / Fermé » calculé côté serveur. |
| **Bloc de réassurance** | Éléments prouvant la fiabilité : note Google, label, ancienneté, agréments. |
| **Parcours urgence** | Visiteur en panne cherchant à joindre le garage immédiatement. |
| **Parcours devis** | Visiteur préparant une intervention, comparant, demandant un prix. |
| **Zone tactile** | Surface cliquable ; minimum 44 × 44 px. |

---

## 2. Vision UX/UI

Le site sert un visiteur pressé, souvent sur mobile, souvent contrarié (panne, dépense imprévue). Trois principes :

1. **Le téléphone reste l'action principale.** 45 % des échanges passent par téléphone ou SMS. Le numéro est atteignable en un geste depuis n'importe quelle page, sans défilement.
2. **Le formulaire est le second choix, pas le premier.** Il sert quand le garage est fermé ou quand le visiteur ne veut pas appeler.
3. **Rien ne doit promettre plus que ce que le garage peut tenir.** La secrétaire répond du lundi au vendredi : le site l'affiche explicitement.

Registre visuel : rassurant et démonstratif plutôt que minimal — le client final a demandé un accueil plus riche qu'un site institutionnel sobre (bandeau utilitaire, badges de réassurance, grille de pictogrammes, photo d'atelier en en-tête). Ce registre reste cadré par trois garde-fous :
- **Aucune donnée inventée.** Chaque badge, chiffre ou engagement affiché doit correspondre à une règle réellement appliquée par le garage (délai de réponse configuré en back-office, politique de devis, etc.) ou rester volontairement générique. Les labels, certifications ou services non confirmés par le client (ex. agrément qualité, véhicule de courtoisie) ne sont ajoutés qu'après validation explicite.
- **Les photos sont celles validées par le client**, même en l'absence de séance photo dédiée à ce stade ; pas de bandeau vidéo, pas d'animation décorative au défilement.
- **Le téléphone reste l'action principale** : la photo et les pictogrammes appuient le message, ils ne le remplacent pas — le bouton d'appel et « Prendre rendez-vous » restent visibles sans défilement sur la page d'accueil.

---

## 3. Structure de l'application

```
/                                   Accueil
/prestations                        Liste des prestations
/prestations/{slug}                 Détail d'une prestation
/vehicules-occasion                 Catalogue VO
/vehicules-occasion/{reference}     Détail d'un véhicule
/rendez-vous                        Formulaire de demande
/rendez-vous/confirmation           Confirmation de soumission
/questions-frequentes               FAQ
/garage-automobile/{commune}        Page locale
/professionnels                     Offre dédiée aux professionnels (flotte, devis groupés)
/le-garage                          Présentation, équipe, labels (« À propos » dans le menu)
/contact                            Coordonnées, plan, horaires
/mentions-legales
/politique-de-confidentialite
/plan-du-site
/sitemap.xml
```

Profondeur maximale : deux niveaux depuis l'accueil. Aucune page ne doit demander plus de deux clics.

---

## 4. Navigation

**Barre utilitaire (bureau uniquement)** : adresse, téléphone, courriel à gauche ; résumé des horaires et liens Facebook/Instagram à droite — chaque élément affiché seulement si renseigné en back-office. Masquée sur mobile pour ne pas alourdir l'en-tête.
**En-tête (mobile)** : logo, bouton d'appel, bouton de menu. Barre collante compacte, hauteur inférieure à 64 px.
**En-tête (bureau)** : logo, menu horizontal, état d'ouverture, numéro, bouton « Demander un rendez-vous ».
**Menu** : Accueil · Prestations (menu déroulant par famille, `<details>` natif) · Véhicules d'occasion · Professionnels · À propos · FAQ · Contact.
**Barre d'action mobile fixe en bas** : « Appeler » · « Demander un RDV » · « Itinéraire ». Hauteur 56 px, compensée par une marge basse sur le contenu pour ne jamais masquer de texte.
**Pied de page** : coordonnées, horaires, zone d'intervention, liens légaux, lien vers la fiche Google, réseaux sociaux actifs.
**Fil d'Ariane** : présent sur toutes les pages sauf l'accueil, balisé en `BreadcrumbList`.

---

## 5. Gabarits Twig

```
templates/
├── base.html.twig
├── components/          # composants Twig réutilisables
├── partials/            # en-tête, pied de page, bandeau, barre mobile
├── home/
├── prestation/
├── vehicule/
├── demande/
├── faq/
├── local/
├── page/                # pages éditoriales
├── system/              # 404, 500, maintenance
├── mail/                # gabarits de courriels
└── admin/               # surcharges EasyAdmin
```

Héritage à trois niveaux maximum. Les composants Twig sont préférés aux inclusions avec longue liste de variables.

---

## 6. Composants d'interface

| Composant | Rôle | Points d'attention |
|---|---|---|
| `bouton-appel` | Lien `tel:` | Libellé explicite, pas seulement une icône ; `aria-label` complet |
| `etat-ouverture` | Ouvert / Fermé + prochaine ouverture | Information portée par du texte, jamais par la seule couleur |
| `carte-prestation` | Vignette de prestation | Zone cliquable entière, titre en lien, pas de `div` cliquable |
| `carte-vehicule` | Vignette de véhicule | Prix, kilométrage, année, énergie visibles sans survol |
| `galerie-vehicule` | Photos d'un véhicule | Navigation clavier, textes alternatifs, pas de défilement automatique |
| `bloc-reassurance` | Note Google, labels, ancienneté | Aucune donnée inventée |
| `barre-utilitaire` | Adresse, téléphone, courriel en bureau | Masquée sur mobile, chaque élément conditionné à sa présence en back-office |
| `hero-photo` | Photo d'atelier + dégradé + promesse | Contraste du texte garanti par un dégradé, pas par la seule photo ; `alt` descriptif |
| `badge-reassurance` | Puce à coche + engagement court | Contenu réel ou générique uniquement, jamais une certification non confirmée |
| `grille-pictogrammes` | Familles de prestations illustrées | Jeu d'icônes SVG unique (section 7), pas de fonte d'icônes |
| `bandeau-exceptionnel` | Message temporaire | `role="status"`, fermable, état mémorisé en session |
| `formulaire-demande` | Formulaire principal | Voir section 9 |
| `accordeon-faq` | Questions / réponses | `<details>` natif ou motif ARIA complet |
| `plan-acces` | Carte Leaflet | Chargement différé, alternative textuelle avec adresse et lien d'itinéraire |
| `barre-action-mobile` | Actions fixes | Masquée au clavier virtuel, ne masque jamais le contenu |
| `fil-ariane` | Navigation contextuelle | `nav` + `aria-label` |

---

## 7. Assets

AssetMapper, sans chaîne de build Node. Bootstrap 5.3.x importé en SCSS via `symfonycasts/sass-bundle` pour ne compiler que les composants utilisés (grille, navigation, formulaires, boutons, accordéon, alertes) ; les modules non utilisés sont exclus à la compilation.

Polices auto-hébergées, format WOFF2, `font-display: swap`, deux graisses maximum. Aucun appel à Google Fonts : dépendance externe et transfert d'adresse IP évitables.

Images : WebP avec repli JPEG, `srcset` et `sizes`, `width` et `height` renseignés systématiquement pour éviter tout décalage de mise en page, `loading="lazy"` sauf sur l'image principale de l'accueil.

Icônes : jeu SVG en ligne unique et limité (une quinzaine de symboles couvrant la navigation, les coordonnées, les familles de prestations et les badges de réassurance). Pas de fonte d'icônes.

---

## 8. JavaScript et interactivité

Le site fonctionne sans JavaScript, hors plan d'accès et vérification anti-abus. Contrôleurs Stimulus prévus :

| Contrôleur | Rôle |
|---|---|
| `menu-mobile` | Ouverture du menu, piège de focus, fermeture à l'échappement |
| `formulaire-demande` | Affichage conditionnel des champs selon le type de demande |
| `galerie` | Navigation entre photos, clavier et tactile |
| `plan-acces` | Initialisation Leaflet au défilement |
| `bandeau` | Fermeture et mémorisation |
| `accordeon` | Uniquement si `<details>` ne suffit pas |

Budget : moins de 40 Ko de JavaScript compressé, hors Turnstile.

---

## 9. Formulaires

### Formulaire de demande

Champ unique par ligne, étiquettes visibles au-dessus, jamais de placeholder en guise d'étiquette.

| Champ | Type | Obligatoire | Remarques |
|---|---|---|---|
| Type de demande | Boutons radio | Oui | Rendez-vous · Devis · Question · Un véhicule |
| Prestation | Liste | Non | Affichée pour les types Rendez-vous et Devis |
| Nom | Texte | Oui | `autocomplete="name"` |
| Téléphone | Tel | Conditionnel | `autocomplete="tel"`, clavier numérique |
| Courriel | Email | Conditionnel | `autocomplete="email"` |
| Immatriculation | Texte | Non | Aide explicite sur l'usage de la donnée |
| Kilométrage | Nombre | Non | `inputmode="numeric"` |
| Disponibilités | Texte | Non | Champ libre, aucune promesse de créneau |
| Message | Zone de texte | Non | 2 000 caractères, compteur accessible |
| Consentement relance | Case | Non | Non pré-cochée, distincte de l'envoi |

Au moins un moyen de contact est exigé (RG-01) : la règle est annoncée avant la soumission, pas seulement après l'erreur.

**Erreurs** : résumé en tête de formulaire avec liens d'ancrage vers chaque champ fautif, message associé par `aria-describedby`, focus déplacé sur le résumé, `aria-invalid` positionné. Aucun message d'erreur porté par la seule couleur.

**Après soumission** : redirection vers une page de confirmation distincte qui rappelle le délai de réponse en jours ouvrés, le numéro de téléphone et les horaires.

---

## 10. Interfaces clés

**Accueil** — Barre utilitaire, bandeau éventuel, en-tête avec état d'ouverture, hero photo (photo validée par le client + dégradé pour la lisibilité du texte) avec promesse, ville, badges de réassurance (données réelles ou génériques, jamais inventées) et boutons d'appel/de demande, grille de pictogrammes par famille de prestations, prestations mises en avant, section « pourquoi nous choisir » avec encart horaires, aperçu VO si stock, extrait de FAQ, plan et horaires.

**Détail de prestation** — Titre, description, forfait indicatif avec mention de devis, durée indicative, ce qui est inclus, action de demande pré-remplie avec la prestation.

**Catalogue VO** — Grille de cartes, tri par prix et par date d'arrivée, filtres énergie et boîte uniquement si le stock dépasse six véhicules. État vide explicite avec renvoi vers les annonces externes.

**Détail d'un véhicule** — Galerie, caractéristiques en liste de définitions, prix, description, action de demande pré-remplie avec le véhicule, lien vers l'annonce externe.

**Demande** — Formulaire seul, sans distraction, avec rappel du téléphone en haut.

**Contact** — Adresse, plan, horaires détaillés, fermetures à venir, zone d'intervention, moyens de contact par ordre de rapidité réelle.

---

## 11. Performance

| Indicateur | Budget |
|---|---|
| LCP (mobile, 4G simulée) | < 2,0 s |
| INP | < 150 ms |
| CLS | < 0,05 |
| Réponse serveur (TTFB) | < 300 ms |
| Poids total de l'accueil | < 600 Ko |
| JavaScript compressé | < 40 Ko |
| CSS compressé | < 50 Ko |
| Requêtes HTTP sur l'accueil | < 30 |

Moyens : compression Brotli, HTTP/2, cache d'assets d'un an avec empreinte de version, images dimensionnées et différées, Leaflet chargé au défilement, Turnstile chargé au premier contact avec le formulaire, aucune police externe, aucun script publicitaire.

Vérification à la livraison de chaque lot concerné, puis mesure sur site réel via la Search Console.

---

## 12. Responsive et accessibilité

Conception mobile d'abord. Points de rupture Bootstrap standards ; la mise en page bascule en deux colonnes à partir de 768 px, trois à partir de 1200 px.

| Exigence | Mise en œuvre |
|---|---|
| Contraste | 4,5:1 sur le texte courant, 3:1 sur le texte large et les composants d'interface |
| Taille de texte | 16 px minimum en corps de page ; aucune valeur en pixels figée empêchant le zoom |
| Zoom | Contenu utilisable à 200 % sans défilement horizontal |
| Zones tactiles | 44 × 44 px minimum, espacement suffisant entre actions voisines |
| Navigation clavier | Tout parcours réalisable au clavier, ordre de tabulation logique |
| Focus | Indicateur visible, contrasté, jamais supprimé |
| Lien d'évitement | « Aller au contenu » en premier élément focalisable |
| Titres | Un seul `h1` par page, hiérarchie sans saut de niveau |
| Images | Texte alternatif pertinent ; `alt=""` pour le décoratif |
| Formulaires | Étiquettes explicites, erreurs identifiées en texte, aide contextuelle |
| Langue | `lang="fr"` sur la racine |
| Mouvement | Respect de `prefers-reduced-motion` |
| Lecteurs d'écran | Vérification manuelle sur NVDA et VoiceOver avant mise en ligne |

15 % des clients sont peu à l'aise avec internet : cette contrainte porte le choix d'un corps de texte à 17 px, de libellés en français courant sans jargon, et d'un formulaire sans étape multiple.

---

## 13. Design system

| Jeton | Valeur | Usage |
|---|---|---|
| `--couleur-primaire` | À définir à partir de l'enseigne et du véhicule floqué | Actions principales |
| `--couleur-accent` | Dérivée, contraste vérifié | Signalement, urgence |
| `--couleur-texte` | Gris très foncé plutôt que noir pur | Corps de page |
| `--couleur-fond-alt` | Gris très clair | Alternance de sections |
| `--rayon` | 8 px | Cartes, boutons |
| `--espace-1` à `--espace-6` | Échelle 4 / 8 / 16 / 24 / 40 / 64 px | Espacements |
| `--ombre-carte` | Discrète | Cartes uniquement |

Échelle typographique : 17 · 20 · 24 · 30 · 38 px. Deux graisses. Hauteur de ligne 1,6 en corps de page.

Les couleurs définitives sont arrêtées après réception de la charte existante (enseigne, cartes de visite, véhicule floqué) et validées au contraste avant toute intégration.

---

## 14. Hiérarchie visuelle

Chaque page respecte l'ordre : bandeau éventuel → titre → action principale → preuve → détail → action secondaire. Une seule action principale visible par écran. Le prix, quand il est affiché, n'est jamais l'élément le plus visible : la mention de devis l'accompagne systématiquement.

---

## 15. États d'interface

| État | Traitement |
|---|---|
| Chargement | Aucun écran de chargement : les pages sont rendues côté serveur |
| Vide (aucun VO) | Message explicite et renvoi vers les annonces externes |
| Vide (aucune fermeture) | Section masquée, pas de bloc vide |
| Erreur de formulaire | Résumé en tête, focus déplacé, messages par champ |
| Succès | Page de confirmation dédiée |
| Hors ligne | Page de secours minimale si une PWA est retenue |
| Erreur serveur | Page 500 avec numéro de téléphone visible |
| Introuvable | Page 404 avec recherche de prestation et liens principaux |
| Garage fermé | État d'ouverture explicite et délai de réponse annoncé |

---

## 16. Notifications utilisateur

Messages temporaires Bootstrap en `role="status"` ou `role="alert"` selon l'urgence, jamais auto-masqués avant 6 secondes, toujours fermables au clavier. Aucune notification superposée au contenu sur mobile.

---

## 17. Thèmes

Thème clair unique. Le mode sombre est écarté en V1 : coût de vérification des contrastes non justifié par le public visé. `color-scheme: light` déclaré pour éviter une inversion automatique par le navigateur.

---

## 18. Pages système

| Page | Contenu |
|---|---|
| 404 | Message clair, liens vers prestations, contact, accueil |
| 500 | Message court, numéro de téléphone, sans détail technique |
| Maintenance | Page statique, horaires, téléphone |
| Plan du site | Liste complète des pages publiques |
| Mentions légales | Éditeur, hébergeur, SIRET, directeur de publication |
| Politique de confidentialité | Données, finalités, durées, droits, sous-traitants — rédigée après vérification des obligations en vigueur |

---

## 19. Référencement

Cible principale : le référencement local, qui est le levier réel pour un garage de quartier.

| Élément | Mise en œuvre |
|---|---|
| Cohérence NAP | Nom, adresse et téléphone strictement identiques au site, à la fiche Google et aux annuaires |
| Données structurées | `AutoRepair` sur le site, `Service` par prestation, `FAQPage`, `Vehicle` par véhicule, `BreadcrumbList` |
| Titres et descriptions | Uniques, administrables, longueurs contrôlées |
| URL | Explicites, en français, sans identifiant numérique |
| Pages locales | Une page par commune, contenu réellement distinct — jamais un gabarit avec le nom de ville substitué |
| Maillage interne | Prestations liées entre elles, pages locales reliées aux prestations |
| Sitemap | Généré quotidiennement, soumis à la Search Console |
| `robots.txt` | Recette et back-office exclus |
| Canoniques | Systématiques |
| Redirections | Aucune en V1, aucun historique d'URL à conserver |

Le site doit renvoyer vers la fiche Google et non l'inverse : la fiche capte déjà 30 % des nouveaux clients.

---

## 20. PWA

**Non applicable en V1** — aucun usage répété justifiant une installation. Les bénéfices attendus (cache hors ligne, installation sur l'écran d'accueil) ne correspondent pas à un visiteur qui consulte le site une à deux fois par an. Réévaluable si un espace client professionnel est développé.

---

## 21. Éléments à confirmer

**Résolus :**
- Logo : fourni par le client (`assets/images/logo/`), intégré à l'en-tête et au favicon.
- Photos : photo d'atelier et photo de devanture fournies par le client, intégrées en accueil (optimisées WebP/JPEG). Séance photo dédiée non nécessaire pour l'instant.
- Bouton WhatsApp : écarté.
- Avis Google : note et lien seuls (pas d'intégration API) ; en attente de la note réelle du garage pour l'afficher.
- Filtres du catalogue VO : conditionnés au seuil de six véhicules en stock (déjà implémenté).
- Bootstrap : verrouillé en 5.3.8 via `composer.lock`.
- Outil de test bout en bout : Symfony Panther.
- Certifications et services affichés (« Certifié QualiRépar », « Partenariat contrôle technique », « Véhicule de courtoisie », « Garage indépendant ») : confirmés exacts par le client, intégrés tels quels sur l'accueil.

**Encore ouvert :**
1. Charte graphique définitive : couleurs de l'enseigne et du véhicule floqué (palette provisoire conservée pour l'instant, à ajuster si une charte réelle est fournie).
2. Réseaux sociaux : champs Facebook/Instagram ajoutés en back-office (`ParametresSite`) mais non renseignés — la barre utilitaire ne les affiche que si les liens sont saisis.
3. Note Google réelle à renseigner dès qu'elle est connue.

---

## 22. Feuille de route

| Module | Complexité | Version |
|---|---|---|
| Gabarit de base, en-tête, pied de page, barre mobile | Moyenne | V1 |
| Design system et jetons | Moyenne | V1 |
| Accueil | Élevée | V1 |
| Prestations (liste et détail) | Moyenne | V1 |
| Formulaire de demande et confirmation | Élevée | V1 |
| FAQ | Faible | V1 |
| Contact et plan d'accès | Moyenne | V1 |
| Catalogue VO et galerie | Élevée | V1 |
| Pages locales | Faible | V1 |
| Page Professionnels | Faible | V1 |
| Pages système | Faible | V1 |
| Données structurées et référencement | Moyenne | V1 |
| Optimisation des performances | Moyenne | V1 |
| Recette d'accessibilité | Élevée | V1 |
| Filtres avancés du catalogue | Moyenne | V2 |
| Intégration des avis Google | Moyenne | V2 |
| Mode sombre | Faible | V2 |
| PWA | Moyenne | V2+ |

---

## 23. Ordre de développement

| Étape | Module | Dépendances |
|---|---|---|
| 1 | Jetons, échelle typographique, compilation SCSS Bootstrap | Socle backend |
| 2 | Gabarit de base, en-tête, pied de page, lien d'évitement, barre mobile | 1 |
| 3 | Pages système et pages éditoriales | 2 |
| 4 | Prestations : liste et détail | 2, backend étape 5 |
| 5 | Formulaire de demande, états d'erreur, confirmation | 4, backend étape 6 |
| 6 | Contact, horaires, plan d'accès | 2, backend étape 3 |
| 7 | FAQ et pages locales | 2 |
| 8 | Catalogue VO, cartes, galerie | 2, backend étape 8 |
| 9 | Données structurées, métadonnées, sitemap | 4, 7, 8 |
| 10 | Optimisation des performances et vérification des budgets | 3 à 9 |
| 11 | Recette d'accessibilité : clavier, lecteur d'écran, contrastes, zoom | Toutes |