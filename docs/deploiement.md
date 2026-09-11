# Déploiement — Garage Horizon Auto

Document opérationnel. Référence : `back.md`, section 20 « Déploiement ».

## 1. Environnements

| Environnement | Usage | Hébergement |
|---|---|---|
| Local | Développement | Docker Compose (`docker-compose.yaml`) |
| Recette | Validation client | Sous-domaine protégé par authentification HTTP, `noindex` |
| Production | Site public | VPS OVHcloud, France |

Conteneurs de production : `php-fpm` 8.4 (`docker/php/Dockerfile`, stage `prod`), `caddy` (ou `nginx`), `mysql` 8.4. TLS par Let's Encrypt avec renouvellement automatique.

Bascule sans interruption (blue/green, rolling) explicitement **non retenue** : une coupure de quelques secondes lors du redémarrage des conteneurs est acceptable au regard de la criticité faible retenue pour ce projet.

## 2. Séquence de déploiement

Chaque étape est bloquante : en cas d'échec, on s'arrête et on ne poursuit pas la suite de la séquence.

1. **Construction de l'image en intégration continue**
   - Déclenchée par une fusion sur `main` (voir `.github/workflows/ci.yaml`, qui fait déjà passer `composer audit`, PHPStan, PHP-CS-Fixer, les tests, `doctrine:schema:validate`, etc. avant toute image).
   - `docker build --target prod -f docker/php/Dockerfile -t <registre>/garage-horizon-auto:<sha-ou-tag> .`
   - Étiquetage systématique par le SHA du commit **et** par un tag mobile (`latest` ou `production`) — le SHA sert de point de rollback précis (section 4).

2. **Poussée vers le registre**
   - `docker push <registre>/garage-horizon-auto:<sha-ou-tag>`
   - Registre privé (GitHub Container Registry ou registre OVHcloud), authentification par jeton dédié au déploiement, jamais le compte personnel d'un développeur.

3. **Sur le serveur : mise à jour des conteneurs**
   ```bash
   cd /srv/garage-horizon-auto/current
   docker compose -f docker-compose.prod.yaml pull
   docker compose -f docker-compose.prod.yaml up -d
   ```
   `docker-compose.prod.yaml` référence l'image taguée (pas de `build:` en production) — fichier propre à l'infrastructure serveur, distinct du `docker-compose.yaml` de développement présent dans ce dépôt.

4. **Migrations de base de données**
   ```bash
   docker compose -f docker-compose.prod.yaml exec -T php \
     bin/console doctrine:migrations:migrate --no-interaction
   ```
   - **Toute migration destructive** (suppression de colonne, de table, changement de type non trivial) doit être **annoncée à l'équipe avant exécution**, et précédée d'une **sauvegarde de la base** (voir `docs/sauvegarde.md`), même en dehors de la fenêtre de sauvegarde quotidienne planifiée.

5. **Vidage du cache applicatif**
   ```bash
   docker compose -f docker-compose.prod.yaml exec -T php bin/console cache:clear --env=prod --no-debug
   ```
   OPcache est configuré avec `validate_timestamps=0` en production (`docker/php/conf.d/opcache-prod.ini`, back.md section 12) : le cache **n'est jamais revalidé automatiquement**, cette étape de déploiement est donc la seule façon de prendre en compte le nouveau code. Un redémarrage du conteneur `php` (étape 3) réinitialise également le cache OPcache en mémoire.

6. **Compilation des assets**
   ```bash
   docker compose -f docker-compose.prod.yaml exec -T php bin/console asset-map:compile
   ```
   Les assets AssetMapper sont servis avec empreinte de version et `Cache-Control: max-age=31536000, immutable` (back.md section 12) : toute page déjà en cache navigateur référence donc automatiquement la nouvelle empreinte après ce déploiement.

7. **Redémarrage du worker Messenger**
   ```bash
   sudo systemctl restart messenger-worker.service
   ```
   Voir `docker/systemd/messenger-worker.service.example` pour le modèle d'unité systemd. Le worker consomme les files `async` et `failed` (`bin/console messenger:consume async failed --time-limit=3600`) ; il doit repartir avec le code fraîchement déployé, jamais avec l'ancien binaire encore chargé en mémoire.

8. **Vérification de santé (health check)**
   - Requête HTTP sur une route de santé (ou la page d'accueil) : code 200 attendu.
   - Vérification que `doctrine:schema:validate` ne signale pas de dérive.
   - Vérification dans Sentry qu'aucune erreur de niveau `error` n'apparaît dans les minutes suivant le déploiement.
   - Vérification visuelle rapide du site (accessibilité de `/admin`, formulaire de contact).
   - Un déploiement n'est considéré terminé que lorsque cette étape est validée.

## 3. Pré-requis et secrets

- `APP_ENV=prod`, `APP_DEBUG=0` dans l'environnement du conteneur `php` de production (jamais dans un fichier committé — voir `.env` : « DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE »).
- Secrets (mot de passe MySQL, `APP_SECRET`, clés Brevo, Turnstile, Sentry DSN) injectés via variables d'environnement du serveur ou via `bin/console secrets:set` (vault Symfony), jamais dans le dépôt Git.
- `composer.lock` et les images Docker sont épinglés par version (back.md, A08 « Intégrité ») : aucune reconstruction d'image ne doit modifier silencieusement une version de dépendance en production.

## 4. Rollback

En cas d'anomalie détectée après déploiement (échec de la vérification de santé, erreurs Sentry en hausse, régression fonctionnelle bloquante) :

1. **Remise en service de l'image précédente**, identifiée par son étiquette (SHA du commit précédent) :
   ```bash
   docker compose -f docker-compose.prod.yaml pull   # si l'image précédente n'est plus locale
   IMAGE_TAG=<sha-precedent> docker compose -f docker-compose.prod.yaml up -d
   ```
2. **Restauration de la base de données** — uniquement si une migration destructive a été appliquée lors du déploiement fautif. Restaurer depuis la sauvegarde prise juste avant la migration (voir `docs/sauvegarde.md`), jamais depuis la sauvegarde quotidienne générique si une sauvegarde ad hoc pré-migration existe.
3. **Redémarrage du worker Messenger** avec le code redevenu cohérent avec la base restaurée.
4. **Nouvelle vérification de santé** (étape 8 ci-dessus).
5. **Compte rendu** : cause de l'échec, action corrective, avant nouvelle tentative de déploiement.

Une migration non destructive (ajout de colonne nullable, ajout de table) ne nécessite en général pas de restauration de base lors d'un rollback applicatif : l'ancienne version du code ignore simplement les nouveaux éléments de schéma.
