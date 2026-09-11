# Sauvegarde et reprise — Garage Horizon Auto

Document opérationnel. Référence : `back.md`, section 21 « Sauvegarde et reprise ».

> « Une sauvegarde jamais restaurée n'est pas une sauvegarde. » — back.md, section 21.

## 1. Plan de sauvegarde

| Élément | Fréquence | Rétention | Emplacement |
|---|---|---|---|
| Base MySQL (`mysqldump`) | Quotidienne, 3 h (heure serveur) | 30 jours | Stockage objet OVHcloud, région France |
| Photos de véhicules (répertoire hors racine web, back.md section 16) | Quotidienne | 30 jours | Idem (même rotation que la base, back.md section 16) |
| Fichiers de configuration (Docker, CI, `.env` de production hors secrets, unités systemd) | À chaque modification | Versionné indéfiniment | Dépôt Git GitHub privé |
| Instantané complet du VPS | Hebdomadaire | 4 semaines | OVHcloud |

### Détail — dump MySQL quotidien

- Exécuté à 3 h (creux de trafic, back.md hypothèse « < 3 000 visites/mois ») par une tâche planifiée côté serveur (cron système ou tâche Symfony Scheduler dédiée à l'orchestration, l'exécution réelle du `mysqldump` restant un processus externe au conteneur applicatif).
- Commande de référence (à exécuter contre le conteneur `mysql` de production) :
  ```bash
  docker compose -f docker-compose.prod.yaml exec -T mysql \
    mysqldump --single-transaction --routines --triggers \
    -u app -p"$MYSQL_PASSWORD" app | gzip > "app-$(date +%F).sql.gz"
  ```
- Le dump compressé est poussé vers le stockage objet OVHcloud (compatible S3) immédiatement après génération, puis supprimé du disque local du VPS.
- Purge des objets de plus de 30 jours par règle de cycle de vie du bucket (lifecycle policy), pas par script maison.

### Détail — photos de véhicules

- Répertoire de stockage hors racine web (back.md section 7.4 et 16).
- Synchronisation quotidienne (`rsync` ou synchronisation objet équivalente) vers le même bucket OVHcloud, dans la même rotation de rétention 30 jours que la base — cohérence indispensable : une restauration de base sans les photos correspondantes (ou inversement) casse l'intégrité fonctionnelle du catalogue VO.

### Détail — instantané VPS

- Fonctionnalité native OVHcloud (snapshot hebdomadaire du volume), conservée 4 semaines glissantes.
- Couvre la reprise en cas de corruption ou de défaillance de la machine elle-même (au-delà de la seule base de données), en complément — pas en remplacement — du dump MySQL quotidien.

## 2. Objectifs de reprise

| Indicateur | Valeur cible |
|---|---|
| **RPO** (Recovery Point Objective — perte de données maximale acceptable) | 24 h |
| **RTO** (Recovery Time Objective — délai maximal de remise en service) | 4 h |

Le RPO de 24 h découle directement de la fréquence quotidienne des sauvegardes : en cas de sinistre survenant juste avant la sauvegarde suivante, la perte de données correspond au plus à une journée d'activité (demandes reçues, modifications back-office).

## 3. Procédure de restauration

### 3.1. Restauration de la base MySQL

1. Identifier le dump à restaurer (le plus récent avant le sinistre, ou un point antérieur précis en cas de corruption progressive).
2. Télécharger le dump depuis le stockage objet OVHcloud.
3. Restaurer dans une base MySQL 8.4 (idéalement une instance de recette, jamais directement en écrasant la production sans confirmation explicite) :
   ```bash
   gunzip -c app-YYYY-MM-DD.sql.gz | docker compose -f docker-compose.prod.yaml exec -T mysql \
     mysql -u app -p"$MYSQL_PASSWORD" app
   ```
4. Exécuter `doctrine:schema:validate` pour confirmer la cohérence entre le schéma restauré et les entités Doctrine déployées à cette date (attention à la version du code associée au dump : une restauration doit être accompagnée du déploiement de la version applicative contemporaine du dump, ou suivie des migrations manquantes).
5. Restaurer le répertoire des photos depuis le même horodatage.
6. Relancer les conteneurs applicatifs et le worker Messenger.
7. Effectuer la vérification de santé décrite dans `docs/deploiement.md` (section 8).

### 3.2. Restauration complète du VPS (sinistre machine)

1. Provisionner un nouveau VPS OVHcloud à partir de l'instantané hebdomadaire le plus récent, ou d'une machine neuve suivie du redéploiement applicatif complet (`docs/deploiement.md`).
2. Appliquer la procédure 3.1 pour ramener la base et les photos à l'état le plus récent disponible (l'instantané VPS pouvant être plus ancien que le dernier dump MySQL — toujours préférer le dump le plus récent à la base contenue dans l'instantané).
3. Reconfigurer le DNS si l'adresse IP a changé, et vérifier le renouvellement TLS (Let's Encrypt).

## 4. Test de restauration — obligatoire

Une sauvegarde non testée n'est pas considérée comme fiable. En conséquence :

- **Avant la mise en ligne** : un test de restauration complet (base + photos) est **obligatoire**, exécuté sur un environnement isolé de la production, avec **compte rendu écrit** (date, opérateur, dump utilisé, durée, écarts constatés le cas échéant).
- **En exploitation** : test de restauration **semestriel** (deux fois par an), également avec compte rendu écrit, conservé avec les fichiers de configuration (dépôt Git privé ou espace documentaire de l'équipe).
- Chaque compte rendu doit à minima consigner :
  1. Date et heure du test.
  2. Sauvegarde utilisée (base + photos + éventuellement instantané VPS).
  3. Durée réelle de restauration (comparée au RTO cible de 4 h).
  4. Résultat de `doctrine:schema:validate` après restauration.
  5. Anomalies constatées et actions correctives engagées.

## 5. Liens avec la supervision

Le contrôle quotidien de fraîcheur des sauvegardes (absence de sauvegarde depuis plus de 36 h déclenche une alerte) relève de `docs/supervision.md`, section « Sauvegardes ».
