# back.md — Garage Horizon Auto

**Projet** : site vitrine et back-office — Horizon Auto SARL, Poissy (78)
**Version du document** : 1.0 — 11/09/2026
**Criticité retenue** : faible
**Stack verrouillée** : Symfony 7.4 LTS · PHP 8.4 · MySQL 8.4 LTS · Twig + Bootstrap 5 + Stimulus · EasyAdmin 5.x

---

## 1. Hypothèses retenues

| Hypothèse | Valeur retenue | Impact si fausse |
|---|---|---|
| Trafic | < 3 000 visites/mois la première année | Au-delà de 20 000, ajouter un cache HTTP et revoir le dimensionnement |
| Volume de demandes | 30 à 60 soumissions de formulaire par mois | Au-delà de 300/mois, industrialiser le traitement et la purge |
| Catalogue VO | 10 fiches maximum simultanées, saisie manuelle | Au-delà de 30, un import automatisé devient nécessaire |
| Interconnexion EBP MéCa | Aucune en V1 (export CSV disponible, aucune API identifiée) | Une passerelle réelle relève d'un projet distinct |
| Utilisateurs du back-office | 2 comptes (gérant, secrétaire) | Au-delà de 5, formaliser une gestion de rôles plus fine |
| Photos et logo | Fournis par le client, hors périmètre de développement | Décalage du planning de mise en ligne |
| Hébergement | VPS OVHcloud, France | Un mutualisé impose d'abandonner Docker et la CI/CD complète |
| Conservation des demandes | 12 mois, à confirmer au regard de la finalité déclarée | Durée à réduire si la finalité ne la justifie pas |

---

## 2. Glossaire métier

| Terme | Définition |
|---|---|
| **Prestation** | Service payant proposé par le garage (révision, distribution, pneus, climatisation…). |
| **Demande** | Soumission d'un formulaire par un visiteur : demande de rendez-vous, de devis, question, ou intérêt pour un véhicule d'occasion. |
| **Forfait indicatif** | Prix d'appel affiché pour une prestation, non contractuel, toujours suivi d'un devis. |
| **Véhicule d'occasion (VO)** | Véhicule proposé à la vente par le garage. |
| **Rendez-vous confirmé** | Créneau fixé par téléphone par la secrétaire, saisi a posteriori dans le back-office. Le site ne réserve jamais de créneau. |
| **Rappel J-1** | Courriel automatique envoyé 24 h avant un rendez-vous confirmé. |
| **Page locale** | Page dédiée à une commune de la zone de chalandise, destinée au référencement local. |
| **Bandeau exceptionnel** | Message temporaire affiché en haut du site (fermeture annuelle, information ponctuelle). |
| **Zone de chalandise** | Rayon d'environ 20 km autour de Poissy : Poissy, Achères, Chambourcy, Orgeval, Saint-Germain-en-Laye, Carrières-sous-Poissy. |

---

## 3. Recommandation de stack

| Composant | Choix retenu | Alternatives écartées | Justification |
|---|---|---|---|
| Framework | Symfony 7.4 LTS | Symfony 8.x, WordPress, site statique | Seule branche dont le support (correctifs jusqu'en nov. 2028, sécurité jusqu'en nov. 2029) couvre la durée de vie prévisible du site sans montée de version imposée. |
| Langage | PHP 8.4 | PHP 8.2 (plancher de 7.4) | Ne pas se caler sur le plancher bas dès la première année. |
| Base de données | MySQL 8.4 LTS | MySQL 8.0 (fin de vie avril 2026), PostgreSQL, MariaDB | Famille imposée par le client ; 8.4 est la seule branche MySQL encore couverte par des correctifs de sécurité. |
| ORM | Doctrine ORM | SQL natif | Standard Symfony, migrations versionnées incluses. |
| Back-office | EasyAdmin 5.x | CRUD custom, Sonata | Compatible Symfony 7.x et PHP 8.2+, couvre l'intégralité des besoins CRUD sans développement d'interface. |
| Moteur de gabarits | Twig | — | Imposé. |
| Interactivité | Stimulus via AssetMapper | Webpack Encore, React | AssetMapper évite une chaîne de build Node sur un projet à faible interactivité. |
| Compilation CSS | `symfonycasts/sass-bundle` | CDN Bootstrap, Encore | Nécessaire pour personnaliser les variables Bootstrap sans chaîne Node ; version à confirmer à l'installation. |
| Envoi de courriels | Symfony Mailer + Brevo (SMTP/API) | SMTP OVH direct | Délivrabilité et traçabilité supérieures à un SMTP mutualisé ; palier gratuit suffisant. |
| Tâches différées | Symfony Messenger, transport Doctrine | Redis, RabbitMQ | Aucun besoin de débit ; une table suffit et supprime une dépendance d'infrastructure. |
| Tâches planifiées | Symfony Scheduler | Cron système seul | Planification versionnée dans le code, testable. |
| Anti-abus formulaire | Cloudflare Turnstile + honeypot + limitation de débit | reCAPTCHA | Pas de suivi publicitaire, gratuit, moins intrusif pour l'accessibilité. |
| Mesure d'audience | Matomo auto-hébergé | Google Analytics, Plausible | Pas de transfert hors UE, pas de coût récurrent, sous-traitance évitée. |
| Supervision d'erreurs | Sentry (palier gratuit) | Logs seuls | Détection des erreurs de production sans surveillance manuelle. |

**Sur-ingénierie explicitement écartée** : Redis, Elasticsearch, CQRS, event sourcing, API REST publique, microservices, Kubernetes. Aucun n'est justifié par la volumétrie ni par la criticité.

---

## 4. Vision backend

Le backend a trois responsabilités et pas une de plus :

1. **Servir du contenu éditorial administrable** (prestations, FAQ, pages locales, véhicules d'occasion, horaires) avec un temps de réponse serveur inférieur à 200 ms.
2. **Capter et acheminer les demandes** vers la boîte mail professionnelle, sans perte, sans doublon, et sans exposer de données personnelles.
3. **Tenir les obligations de conservation et de purge** des données personnelles sans intervention humaine.

Tout le reste (agenda, facturation, stock, relances marketing) reste dans EBP MéCa et dans les outils existants du garage.

---

## 5. Architecture

### 5.1. Style architectural

Architecture en couches, monolithe modulaire. Pas d'architecture hexagonale complète : sur un domaine de cette taille, le coût d'abstraction dépasse le bénéfice. Les règles métier réelles (validation d'une demande, calcul de l'état d'ouverture, publication d'un véhicule) sont isolées dans des services testables ; le reste passe directement par Doctrine.

### 5.2. Découpage

```
src/
├── Controller/
│   ├── Public/          # pages visiteurs
│   └── Admin/           # EasyAdmin CRUD + Dashboard
├── Entity/
├── Repository/
├── Form/                # formulaires publics uniquement
├── Service/
│   ├── Contact/         # traitement des demandes
│   ├── Horaires/        # calcul d'ouverture, fermetures
│   ├── Seo/             # métadonnées, données structurées
│   └── Rgpd/            # purge, export, anonymisation
├── Message/             # messages Messenger
├── MessageHandler/
├── Scheduler/           # tâches planifiées
├── EventSubscriber/
├── Security/            # Voters
├── Twig/                # extensions et composants
└── Command/
```

### 5.3. Flux d'une demande

1. Le visiteur soumet le formulaire (POST, jeton CSRF, honeypot, Turnstile).
2. Validation serveur stricte ; en cas d'échec, retour du formulaire avec erreurs associées aux champs.
3. Persistance de la `Demande` en base, statut `nouvelle`.
4. Dépôt d'un message Messenger (notification interne + accusé de réception au visiteur).
5. Redirection vers une page de confirmation (motif POST-Redirect-GET, empêche le double envoi).
6. Traitement asynchrone : courriel à la boîte du garage, accusé de réception au visiteur.
7. La secrétaire traite la demande, saisit éventuellement la date de rendez-vous confirmée.
8. Le Scheduler envoie le rappel J-1 le cas échéant.
9. Au-delà de la durée de conservation, la purge supprime la demande.

---

## 6. Modélisation métier

### 6.1. Entités

| Entité | Rôle | Champs principaux |
|---|---|---|
| `Prestation` | Service proposé | `slug`, `nom`, `famille`, `resume`, `description`, `tarifIndicatif` (nullable), `mentionTarif`, `miseEnAvant`, `ordre`, `actif`, `image`, `seoTitre`, `seoDescription` |
| `Demande` | Soumission d'un formulaire | `type`, `nom`, `telephone`, `email`, `immatriculation` (nullable), `kilometrage` (nullable), `prestation` (nullable), `vehicule` (nullable), `creneauSouhaite`, `message`, `consentementRelance`, `statut`, `creeLe`, `rdvConfirmeLe` (nullable), `rappelEnvoyeLe` (nullable), `purgePrevueLe` |
| `Vehicule` | Véhicule d'occasion | `reference`, `titre`, `marque`, `modele`, `version`, `misEnCirculationLe`, `kilometrage`, `energie`, `boite`, `puissanceFiscale`, `puissanceDin`, `prix`, `description`, `statut`, `urlAnnonceExterne`, `publieLe`, `modifieLe` |
| `PhotoVehicule` | Photo rattachée à un véhicule | `vehicule`, `fichier`, `texteAlternatif`, `ordre`, `principale` |
| `QuestionFrequente` | Entrée de FAQ | `question`, `reponse`, `categorie`, `ordre`, `actif` |
| `PageLocale` | Page commune | `slug`, `commune`, `titre`, `contenu`, `seoTitre`, `seoDescription`, `actif` |
| `PlageHoraire` | Horaire hebdomadaire | `jour`, `matinDebut`, `matinFin`, `apremDebut`, `apremFin`, `ferme` |
| `FermetureExceptionnelle` | Congés, jours fériés | `debutLe`, `finLe`, `libelle` |
| `Bandeau` | Message temporaire | `message`, `niveau`, `debutLe`, `finLe`, `actif` |
| `ParametresSite` | Coordonnées et réglages (enregistrement unique) | `raisonSociale`, `adresse`, `codePostal`, `ville`, `telephone`, `email`, `siret`, `latitude`, `longitude`, `urlFicheGoogle`, `urlLeboncoin`, `numeroWhatsapp`, `delaiReponseJoursOuvres` |
| `Utilisateur` | Compte back-office | `email`, `motDePasse`, `roles`, `nom`, `actif`, `derniereConnexionLe` |
| `JournalAudit` | Traçabilité des accès aux demandes | `utilisateur`, `action`, `entite`, `entiteId`, `creeLe` |

### 6.2. Énumérations (PHP `enum`)

- `TypeDemande` : `RDV`, `DEVIS`, `QUESTION`, `VEHICULE`
- `StatutDemande` : `NOUVELLE`, `EN_COURS`, `TRAITEE`, `ARCHIVEE`
- `StatutVehicule` : `BROUILLON`, `PUBLIE`, `VENDU`, `RETIRE`
- `FamillePrestation` : `ENTRETIEN`, `MECANIQUE`, `PNEUMATIQUE`, `CLIMATISATION`, `DIAGNOSTIC`, `CONTROLE_TECHNIQUE`
- `Energie` : `ESSENCE`, `DIESEL`, `HYBRIDE`, `ELECTRIQUE`, `GPL`
- `BoiteVitesses` : `MANUELLE`, `AUTOMATIQUE`
- `NiveauBandeau` : `INFO`, `ATTENTION`

### 6.3. Règles de gestion

| ID | Règle |
|---|---|
| RG-01 | Une `Demande` exige au minimum un téléphone **ou** un courriel ; les deux peuvent être fournis, aucun ne peut manquer simultanément. |
| RG-02 | L'immatriculation est facultative. Si renseignée, elle est normalisée en majuscules sans espaces et validée par format. |
| RG-03 | Une `Demande` de type `VEHICULE` doit référencer un `Vehicule` au statut `PUBLIE`. |
| RG-04 | Un `Vehicule` ne peut passer au statut `PUBLIE` que s'il possède au moins une photo, un prix et un kilométrage. |
| RG-05 | Un `Vehicule` au statut `VENDU` reste consultable 30 jours avec mention explicite, puis passe automatiquement en `RETIRE`. |
| RG-06 | Un `Vehicule` publié sans modification depuis 60 jours déclenche une alerte interne (fiche potentiellement obsolète). |
| RG-07 | Un `tarifIndicatif` affiché est toujours accompagné de la mention de devis obligatoire ; il ne peut être négatif ni nul. |
| RG-08 | Le rappel J-1 n'est envoyé que si `rdvConfirmeLe` est renseigné, que le courriel existe, et que `rappelEnvoyeLe` est vide. |
| RG-09 | `purgePrevueLe` est calculé à la création de la demande à partir de la durée de conservation paramétrée. |
| RG-10 | Le consentement à la relance commerciale est une case distincte, non pré-cochée, indépendante de l'envoi du formulaire. |
| RG-11 | L'état d'ouverture affiché tient compte des `PlageHoraire`, des `FermetureExceptionnelle` et des jours fériés français. |
| RG-12 | Deux soumissions identiques (même courriel, même type, même message) à moins de 5 minutes d'intervalle sont considérées comme un doublon et ne génèrent qu'une notification. |

---

## 7. Sécurité

### 7.1. Surface d'attaque

Le site n'expose qu'un seul point d'écriture public : le formulaire de demande. C'est la surface à traiter en priorité.

### 7.2. Mesures

| Risque (OWASP) | Mesure |
|---|---|
| A01 Contrôle d'accès défaillant | Pare-feu Symfony sur `/admin`, `ROLE_ADMIN` / `ROLE_SECRETARIAT`, Voters sur les actions sensibles, `access_control` en liste blanche. |
| A02 Défaillances cryptographiques | HTTPS obligatoire, HSTS, `password_hasher: auto`, cookies `secure` + `httponly` + `samesite=lax`. |
| A03 Injection | Doctrine avec requêtes paramétrées exclusivement, échappement Twig par défaut, aucun `|raw` sur du contenu saisi. |
| A04 Conception non sécurisée | Aucun créneau réservable sans validation humaine, aucune donnée de paiement, aucun compte client. |
| A05 Mauvaise configuration | `APP_ENV=prod`, `APP_DEBUG=0`, profiler absent en production, en-têtes CSP, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`. |
| A06 Composants vulnérables | `composer audit` en intégration continue, Dependabot activé, montées de version de sécurité mensuelles. |
| A07 Authentification | Mots de passe d'au moins 12 caractères, limitation des tentatives de connexion, déconnexion après inactivité, 2FA recommandée sur le compte gérant. |
| A08 Intégrité | Dépendances verrouillées par `composer.lock`, images Docker épinglées par version. |
| A09 Journalisation | Monolog + Sentry, journal d'audit sur les accès aux demandes, aucune donnée personnelle dans les logs applicatifs. |
| A10 SSRF | Aucun appel sortant piloté par une saisie utilisateur. |

### 7.3. Protection du formulaire

Quatre couches cumulées : jeton CSRF, champ honeypot invisible mais correctement étiqueté pour les lecteurs d'écran, Cloudflare Turnstile, limitation de débit (`symfony/rate-limiter`, 5 soumissions par heure et par adresse IP, fenêtre glissante). Les téléversements publics sont interdits : aucune photo ne peut être envoyée par un visiteur.

### 7.4. Téléversement en back-office

Photos de véhicules uniquement, extensions autorisées `jpg`/`jpeg`/`png`/`webp`, type MIME vérifié côté serveur, taille maximale 8 Mo, renommage systématique, stockage hors racine web, réencodage à la génération des vignettes.

---

## 8. Back-office

Dashboard EasyAdmin, deux rôles.

| Menu | Type de lien | Accès |
|---|---|---|
| Tableau de bord | `linkToDashboard()` | Les deux |
| Demandes | `linkToCrud(Demande::class)` | Les deux |
| Prestations | `linkToCrud(Prestation::class)` | Gérant |
| Véhicules d'occasion | `linkToCrud(Vehicule::class)` | Les deux |
| FAQ | `linkToCrud(QuestionFrequente::class)` | Gérant |
| Pages locales | `linkToCrud(PageLocale::class)` | Gérant |
| Horaires | `linkToCrud(PlageHoraire::class)` | Gérant |
| Fermetures | `linkToCrud(FermetureExceptionnelle::class)` | Gérant |
| Bandeau | `linkToCrud(Bandeau::class)` | Les deux |
| Paramètres du site | `linkToRoute('admin_parametres')` | Gérant |
| Utilisateurs | `linkToCrud(Utilisateur::class)` | Gérant |
| Export RGPD | `linkToRoute('admin_rgpd_export')` | Gérant |

Le tableau de bord affiche : demandes non traitées, demandes de la semaine, véhicules publiés depuis plus de 60 jours, prochains rendez-vous confirmés. Aucune interface publique n'utilise EasyAdmin.

---

## 9. Services métier

| Service | Responsabilité |
|---|---|
| `DemandeHandler` | Valide, déduplique, persiste, déclenche les notifications. |
| `NotificationDemande` | Compose et envoie le courriel interne et l'accusé de réception. |
| `RappelRendezVous` | Sélectionne les rendez-vous à J-1 et envoie les rappels. |
| `CalculateurOuverture` | Détermine si le garage est ouvert à un instant donné, et le prochain créneau d'ouverture. |
| `PublicationVehicule` | Applique les règles RG-04 à RG-06 lors des changements de statut. |
| `PurgeDonneesPersonnelles` | Supprime les demandes dont `purgePrevueLe` est dépassé. |
| `ExportDonneesPersonnelles` | Produit l'export des données liées à une adresse courriel ou un numéro de téléphone (droit d'accès). |
| `GenerateurDonneesStructurees` | Produit le JSON-LD `AutoRepair`, `Service`, `FAQPage`, `Vehicle`. |

---

## 10. Événements

| Événement | Déclencheur | Conséquence |
|---|---|---|
| `DemandeRecue` | Soumission validée | Notification interne + accusé de réception |
| `RendezVousConfirme` | Saisie de `rdvConfirmeLe` | Planification du rappel J-1 |
| `VehiculePublie` | Statut → `PUBLIE` | Invalidation du cache, mise à jour du sitemap |
| `VehiculeVendu` | Statut → `VENDU` | Programmation du retrait à 30 jours |
| `ConnexionAdministrateur` | Authentification réussie | Écriture au journal d'audit |

---

## 11. Asynchronisme

Symfony Messenger, transport Doctrine (table `messenger_messages`), un worker supervisé par systemd avec redémarrage automatique.

| File | Contenu | Nouvelles tentatives |
|---|---|---|
| `async` | Courriels, notifications | 3, délai exponentiel |
| `failed` | Messages en échec définitif | Inspection manuelle, alerte Sentry |

Tâches planifiées via Symfony Scheduler : rappels J-1 (quotidien, 9 h), purge RGPD (quotidien, 3 h), retrait des véhicules vendus (quotidien), alerte fiches obsolètes (hebdomadaire), régénération du sitemap (quotidien).

---

## 12. Cache

| Niveau | Mise en œuvre |
|---|---|
| Application | Cache Symfony sur système de fichiers, adaptateur APCu pour les métadonnées |
| HTTP | En-têtes `Cache-Control` sur les pages éditoriales, durée 1 h, purge à la publication |
| OPcache | Activé, `validate_timestamps=0` en production, réinitialisé au déploiement |
| Doctrine | Cache de métadonnées et de requêtes en production |
| Assets | Empreinte de version AssetMapper, `Cache-Control: max-age=31536000, immutable` |

Redis est écarté : la volumétrie ne le justifie pas et il ajoute un service à exploiter.

---

## 13. Notifications

| Destinataire | Événement | Canal | Contenu |
|---|---|---|---|
| Garage | Demande reçue | Courriel | Type, nom, coordonnées, véhicule, message, lien vers le back-office |
| Visiteur | Demande reçue | Courriel | Accusé de réception, délai de réponse en jours ouvrés, coordonnées du garage |
| Visiteur | Rendez-vous à J-1 | Courriel | Date, heure, adresse, contact, consignes |
| Gérant | Fiche VO obsolète | Courriel hebdomadaire | Liste des véhicules concernés |
| Gérant | Erreur de production | Sentry | Trace, contexte anonymisé |

Aucun SMS en V1 : coût récurrent et gain non démontré face au courriel sur ce volume.

---

## 14. API

**Non applicable** — aucune API publique n'est prévue. Les seuls points d'entrée sont les routes HTML publiques et le back-office authentifié. Un `sitemap.xml` et un flux JSON-LD sont exposés, mais relèvent du référencement, pas d'une interface programmatique.

---

## 15. Import / export

| Flux | Sens | Format | Fréquence |
|---|---|---|---|
| Demandes | Sortant | CSV (UTF-8, séparateur `;`) | À la demande, depuis le back-office |
| Véhicules | Sortant | CSV | À la demande |
| Données personnelles | Sortant | JSON | À la demande, exercice du droit d'accès |
| EBP MéCa | — | — | Hors périmètre V1 |

---

## 16. Gestion des fichiers

Photos de véhicules stockées hors racine web, servies via un contrôleur dédié ou un lien symbolique contrôlé. Génération de trois variantes (vignette 400 px, carte 800 px, détail 1600 px) en WebP avec repli JPEG, en tâche asynchrone. Suppression physique à la suppression de l'entité. Sauvegarde du répertoire dans la même rotation que la base.

---

## 17. Journalisation et audit

| Canal | Contenu | Rétention |
|---|---|---|
| `app` | Événements métier | 30 jours |
| `security` | Connexions, échecs, changements de rôle | 12 mois |
| `audit` | Accès et modifications sur les demandes | 12 mois |
| `deprecation` | Obsolescences Symfony | 7 jours |

Rotation quotidienne via `logrotate`. Interdiction stricte d'écrire une immatriculation, un courriel ou un numéro de téléphone dans un log applicatif : les entrées référencent l'identifiant de l'entité.

---

## 18. Tests

| Niveau | Portée | Outil |
|---|---|---|
| Unitaire | `CalculateurOuverture`, `PublicationVehicule`, normalisation d'immatriculation, calcul de purge | PHPUnit |
| Fonctionnel | Soumission du formulaire (nominal, erreurs, doublon, quota), accès back-office par rôle | PHPUnit + `WebTestCase` |
| Intégration | Envoi de courriel (transport de test), Messenger, migrations Doctrine | PHPUnit |
| Bout en bout | Parcours « demande de rendez-vous » et « consultation d'un véhicule » | Panther ou Playwright, à confirmer |
| Accessibilité | Contrastes, navigation clavier, hiérarchie des titres | axe-core en intégration continue, vérification manuelle au lecteur d'écran |

Couverture cible : 80 % sur `src/Service`, aucune cible globale imposée. Mutation testing non retenu : hors proportion pour cette criticité.

---

## 19. Qualité de code

PHP-CS-Fixer (règles `@Symfony`), PHPStan niveau 8, Twig CS Fixer, `composer audit`, `lint:twig`, `lint:container`, `doctrine:schema:validate`. Tous exécutés en intégration continue à chaque poussée, bloquants sur la branche principale.

---

## 20. Déploiement

| Environnement | Usage | Hébergement |
|---|---|---|
| Local | Développement | Docker Compose |
| Recette | Validation client | Sous-domaine protégé par authentification HTTP, `noindex` |
| Production | Site public | VPS OVHcloud, France |

Conteneurs : `php-fpm` 8.4, `nginx` ou `caddy`, `mysql` 8.4. TLS par Let's Encrypt avec renouvellement automatique.

Séquence de déploiement : construction de l'image en intégration continue → poussée vers le registre → sur le serveur, `docker compose pull` puis `up -d` → `doctrine:migrations:migrate --no-interaction` → `cache:clear` → `asset-map:compile` → redémarrage du worker → vérification de santé. Bascule sans interruption non retenue : une coupure de quelques secondes est acceptable à cette criticité.

Rollback : remise en service de l'image précédente (étiquetée), restauration de la base si une migration destructive a été appliquée. Toute migration destructive doit être annoncée et sauvegardée avant exécution.

---

## 21. Sauvegarde et reprise

| Élément | Fréquence | Rétention | Emplacement |
|---|---|---|---|
| Base MySQL | Quotidienne, 3 h | 30 jours | Stockage objet OVHcloud, région France |
| Photos de véhicules | Quotidienne | 30 jours | Idem |
| Fichiers de configuration | À chaque modification | Dépôt Git | GitHub privé |
| Instantané du VPS | Hebdomadaire | 4 semaines | OVHcloud |

**RPO** : 24 h. **RTO** : 4 h. Test de restauration obligatoire avant la mise en ligne, puis semestriel, avec compte rendu écrit. Une sauvegarde jamais restaurée n'est pas une sauvegarde.

---

## 22. Supervision et observabilité

| Objet | Outil | Seuil d'alerte |
|---|---|---|
| Disponibilité | UptimeRobot, palier gratuit | 2 échecs consécutifs |
| Erreurs applicatives | Sentry, palier gratuit | Toute erreur de niveau `error` |
| Expiration du certificat | Sonde UptimeRobot | 14 jours avant échéance |
| Espace disque | Script + alerte courriel | 80 % d'occupation |
| File Messenger | Sonde sur la table | Plus de 50 messages en attente |
| Sauvegardes | Vérification quotidienne | Absence de sauvegarde depuis 36 h |
| Core Web Vitals | Google Search Console | Dégradation d'un indicateur |

---

## 23. Intégrations externes

| Intégration | Rôle | Type | Contraintes | Repli |
|---|---|---|---|---|
| Brevo | Courriels transactionnels | SMTP / API | Quota journalier du palier gratuit | SMTP OVHcloud |
| Cloudflare Turnstile | Anti-abus | JavaScript + vérification serveur | Dépend d'un service tiers | Honeypot + limitation de débit seuls |
| Fiche Google Business Profile | Avis et note | Lien sortant | Aucune | Aucun |
| LeBonCoin / La Centrale | Annonces VO | Lien sortant | Aucune | Aucun |
| Matomo auto-hébergé | Mesure d'audience | Instance locale | Charge sur le VPS | Suspension de la mesure |
| Sentry | Erreurs | SDK | Quota du palier gratuit | Logs serveur |
| OpenStreetMap / Leaflet | Plan d'accès | JavaScript | Aucune clé requise | Lien vers l'itinéraire |

Aucune intégration n'est bloquante : le site reste entièrement fonctionnel si chacune tombe.

---

## 24. Éléments à confirmer

1. Arbitrage sur le catalogue VO (fiches administrables, renvoi vers les portails, ou import).
2. Photos et vectorisation du logo : inclus ou facturés en sus.
3. Affichage des avis Google : lien simple retenu par défaut ; l'affichage automatisé impose une vérification des conditions et de la tarification Google Maps Platform, non faite à ce stade.
4. Bouton WhatsApp : conservé ou écarté, et horaires de réponse affichés.
5. Définition opérationnelle d'une « demande qualifiée » pour mesurer l'objectif de 30 par mois.
6. Durée de conservation des demandes : 12 mois annoncés, à justifier par la finalité. Obligations d'information, recueil du consentement pour les relances et conditions de dispense de bandeau de consentement : **à vérifier dans les sources officielles (CNIL) avant rédaction des mentions**, non traité ici.
7. Autorisation d'usage du nom et du logo du centre de contrôle technique partenaire.
8. Conditions d'affichage du label QualiRépar.
9. **Confirmé** : `symfonycasts/sass-bundle` 0.10.0, validé avec Symfony 7.4 et dans l'image de production PHP 8.4.
10. **Confirmé** : Panther 2.4.0, avec ChromeDriver détecté par BDI ; les parcours « demande de rendez-vous » et « consultation d'un véhicule » sont exécutés en CI.

---

## 25. Feuille de route

| Module | Complexité | Version |
|---|---|---|
| Socle technique, Docker, intégration continue | Moyenne | V1 |
| Paramètres du site, horaires, fermetures, bandeau | Faible | V1 |
| Pages éditoriales et pages système | Faible | V1 |
| Prestations et back-office associé | Moyenne | V1 |
| Formulaire de demande et notifications | Élevée | V1 |
| FAQ | Faible | V1 |
| Pages locales | Faible | V1 |
| Véhicules d'occasion et photos | Élevée | V1 |
| Rappels J-1 | Moyenne | V1 |
| Purge et export RGPD | Moyenne | V1 |
| Référencement, données structurées, sitemap | Moyenne | V1 |
| Supervision, sauvegardes, restauration testée | Moyenne | V1 |
| Affichage automatisé des avis Google | Moyenne | V2 |
| Import automatisé du catalogue VO | Élevée | V2 |
| Notifications SMS | Moyenne | V2 |
| Passerelle EBP MéCa | Élevée | V2+ |
| Agenda de réservation en ligne | Très élevée | V2+ |
| Espace client professionnel | Très élevée | V2+ |

---

## 26. Ordre de développement

| Étape | Module | Dépendances |
|---|---|---|
| 1 | Socle : dépôt, Docker, Symfony 7.4, MySQL 8.4, intégration continue, environnements | Aucune |
| 2 | Sécurité et back-office : `Utilisateur`, pare-feu, EasyAdmin, journal d'audit | 1 |
| 3 | Paramètres, horaires, fermetures, bandeau, `CalculateurOuverture` | 2 |
| 4 | Gabarit de base, pages éditoriales, pages système, mentions | 3 |
| 5 | Prestations : entité, CRUD, pages publiques | 4 |
| 6 | Formulaire de demande, anti-abus, Messenger, notifications | 5 |
| 7 | FAQ et pages locales | 4 |
| 8 | Véhicules d'occasion, photos, variantes d'images | 5 |
| 9 | Rappels J-1, purge RGPD, export | 6 |
| 10 | Référencement, données structurées, sitemap, performance | 5, 7, 8 |
| 11 | Supervision, sauvegardes, test de restauration | 1 |
| 12 | Recette, accessibilité, corrections, mise en ligne | Toutes |
