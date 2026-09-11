# Supervision et observabilité — Garage Horizon Auto

Document opérationnel, sous forme de checklist. Référence : `back.md`, section 22 « Supervision et observabilité ».

## 1. Plan de supervision

| Objet | Outil | Seuil d'alerte | Destinataire |
|---|---|---|---|
| Disponibilité du site | UptimeRobot (palier gratuit) | 2 échecs consécutifs | Gérant / équipe technique |
| Erreurs applicatives | Sentry (palier gratuit) | Toute erreur de niveau `error` | Gérant (back.md section 13) |
| Expiration du certificat TLS | Sonde UptimeRobot | 14 jours avant échéance | Équipe technique |
| Espace disque du VPS | Script + alerte courriel | 80 % d'occupation | Équipe technique |
| File d'attente Messenger (`messenger_messages`) | Sonde sur la table | Plus de 50 messages en attente | Équipe technique |
| Fraîcheur des sauvegardes | Vérification quotidienne | Absence de sauvegarde depuis 36 h | Équipe technique |
| Core Web Vitals | Google Search Console | Dégradation d'un indicateur | Équipe technique |

Aucune de ces sondes ne dépend d'une intégration bloquante (back.md section 23) : le site reste fonctionnel si un outil de supervision tombe, seule la visibilité sur un incident est réduite.

## 2. Checklist de mise en place (avant mise en ligne)

- [ ] **UptimeRobot** : moniteur HTTP(S) configuré sur l'URL publique du site, intervalle le plus court disponible au palier gratuit, alerte configurée à 2 échecs consécutifs, notification par courriel (et SMS si le palier le permet) vers le gérant et l'équipe technique.
- [ ] **UptimeRobot — certificat TLS** : sonde dédiée à l'expiration du certificat (fonctionnalité native UptimeRobot sur un moniteur HTTPS), seuil à 14 jours avant échéance.
- [ ] **Sentry** : SDK Symfony installé et configuré (DSN en variable d'environnement, jamais committé), environnement (`prod`/`recette`) correctement distingué, règle d'alerte sur tout événement de niveau `error` et au-dessus, contexte anonymisé (back.md A09 : aucune donnée personnelle — immatriculation, courriel, téléphone — dans les traces envoyées à Sentry, comme dans les logs applicatifs).
- [ ] **Espace disque** : script cron sur le VPS (ex. `df` sur le point de montage applicatif), seuil 80 %, envoi d'un courriel d'alerte à l'équipe technique ; à exécuter au minimum quotidiennement.
- [ ] **File Messenger** : requête planifiée (cron ou Symfony Scheduler) comptant les lignes de la table `messenger_messages` pour la file `async` non encore consommées ; alerte si plus de 50 messages en attente — un volume élevé indique un worker arrêté ou en échec (voir `docker/systemd/messenger-worker.service.example`).
- [ ] **Fraîcheur des sauvegardes** : vérification quotidienne que le dernier objet de sauvegarde (base + photos) dans le stockage OVHcloud date de moins de 36 h ; alerte courriel sinon. Voir `docs/sauvegarde.md`.
- [ ] **Core Web Vitals** : propriété du site enregistrée dans Google Search Console, rapport « Signaux Web essentiels » consulté périodiquement (mensuel recommandé), alerte manuelle en cas de dégradation d'un indicateur (LCP, INP, CLS).

## 3. Checklist opérationnelle récurrente

| Fréquence | Action |
|---|---|
| Quotidienne (automatique) | Vérification de fraîcheur des sauvegardes |
| Quotidienne (automatique) | Vérification de l'espace disque |
| Continue (automatique) | Sonde de disponibilité UptimeRobot |
| Continue (automatique) | Capture des erreurs applicatives par Sentry |
| Hebdomadaire (manuelle) | Lecture du tableau de bord Sentry (tendances, erreurs récurrentes non traitées) |
| Hebdomadaire (automatique, back.md section 11) | Alerte fiches véhicules obsolètes (plus de 60 jours sans modification) |
| Mensuelle (manuelle) | Consultation des Core Web Vitals dans Google Search Console |
| Semestrielle (manuelle) | Test de restauration de sauvegarde (voir `docs/sauvegarde.md`, section 4) |
| Mensuelle (automatique/CI) | Montée de version de sécurité des dépendances (`composer audit`, Dependabot — back.md A06) |

## 4. Réaction aux alertes

| Alerte | Premier réflexe |
|---|---|
| 2 échecs consécutifs UptimeRobot | Vérifier l'état des conteneurs (`docker compose ps`), les logs `php`/`caddy`, la connectivité MySQL |
| Erreur Sentry `error` | Consulter la trace, évaluer l'impact utilisateur, corriger ou créer un ticket selon la gravité |
| Certificat TLS < 14 jours | Vérifier le renouvellement automatique Let's Encrypt, renouveler manuellement si le mécanisme automatique a échoué |
| Disque > 80 % | Identifier la source de croissance (logs non tournés, dumps locaux non purgés, cache), purger, envisager une extension de volume |
| File Messenger > 50 messages | Vérifier que le service `messenger-worker.service` est actif (`systemctl status`), consulter la file `failed` pour des échecs répétés |
| Sauvegarde absente > 36 h | Vérifier le cron/planification du dump, l'accès au stockage objet OVHcloud, relancer manuellement le dump si nécessaire |
| Dégradation Core Web Vitals | Identifier la page/le gabarit concerné, vérifier le poids des images de véhicules (variantes WebP, back.md section 16), le cache HTTP des pages éditoriales |

## 5. Journalisation (rappel, back.md section 17)

| Canal | Contenu | Rétention |
|---|---|---|
| `app` | Événements métier | 30 jours |
| `security` | Connexions, échecs, changements de rôle | 12 mois |
| `audit` | Accès et modifications sur les demandes | 12 mois |
| `deprecation` | Obsolescences Symfony | 7 jours |

Rotation quotidienne via `logrotate`. Rappel strict : aucune immatriculation, courriel ou numéro de téléphone dans un log applicatif — les entrées référencent l'identifiant de l'entité, jamais la donnée personnelle elle-même. Cette règle s'applique également aux événements envoyés à Sentry.
