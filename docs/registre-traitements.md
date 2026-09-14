# Registre des activités de traitement — Garage Horizon Auto

Document de conformité RGPD (article 30 du RGPD). Référence : `back.md` section 24, point 6 ;
`docs/sauvegarde.md` ; politique de confidentialité (`templates/public/page_systeme/politique_confidentialite.html.twig`).

> Ce registre n'est pas soumis à la CNIL pour validation — il n'existe plus de système
> d'autorisation préalable depuis le RGPD (2018). Il constitue la preuve d'auto-conformité
> (« accountability ») à présenter en cas de contrôle ou de plainte. Modèle inspiré du
> registre simplifié proposé par la CNIL pour les TPE/PME (cnil.fr).

## 1. Responsable de traitement

| Champ | Valeur |
|---|---|
| Raison sociale | Renseignée en back-office (`ParametresSite.raisonSociale`) — Horizon Auto SARL |
| Adresse | Renseignée en back-office (`ParametresSite.adresse`, `codePostal`, `ville`) |
| SIRET | Renseigné en back-office (`ParametresSite.siret`) |
| Contact pour l'exercice des droits | Adresse courriel du garage (`ParametresSite.email`) |
| Délégué à la protection des données (DPO) | Aucun désigné — non obligatoire à cette taille d'organisation (back.md hypothèse : 2 comptes back-office) |

## 2. Sous-traitants (article 28 du RGPD)

| Sous-traitant | Rôle | Données transmises | Localisation |
|---|---|---|---|
| Brevo | Envoi des courriels transactionnels (accusé de réception, notification interne, rappel J-1) | Nom, courriel, contenu du message | UE (back.md section 3, 23) |
| Cloudflare (Turnstile) | Vérification anti-robot du formulaire de contact | Signaux techniques de navigation (pas de donnée du formulaire) | Hors UE — clauses contractuelles types Cloudflare |
| Sentry | Suivi des erreurs applicatives | Aucune donnée personnelle ni corps de requête (désactivés explicitement, voir `docs/supervision.md` §2) | UE ou clauses contractuelles types selon plan |
| OVHcloud | Hébergement (serveur, sauvegardes) | Ensemble de la base de données et des fichiers, chiffrés au repos | France |

Aucune intégration n'est bloquante pour le fonctionnement du site (back.md section 23) : ce tableau doit être mis à jour si un nouvel outil externe est ajouté (ex. Matomo, prévu au cahier des charges mais non encore intégré à ce jour).

## 3. Traitements

### 3.1. Traitement des demandes visiteurs (formulaire de contact)

| Élément | Détail |
|---|---|
| **Finalité** | Traiter une demande de rendez-vous, de devis, une question, ou un intérêt pour un véhicule d'occasion (RG-01 à RG-03, back.md section 6.3) |
| **Base légale** | Exécution de mesures précontractuelles à la demande de la personne concernée (article 6.1.b du RGPD) — la personne initie elle-même la démarche |
| **Personnes concernées** | Visiteurs du site soumettant le formulaire |
| **Données collectées** | Nom, téléphone et/ou courriel (RG-01 : au moins un des deux obligatoire), immatriculation (facultative, normalisée), kilométrage (facultatif), prestation ou véhicule concerné, créneau souhaité, message libre, consentement à la relance commerciale (case distincte, RG-10) |
| **Destinataires internes** | Gérant et secrétariat (les deux rôles back-office, back.md section 8) |
| **Destinataires externes** | Brevo (acheminement des courriels uniquement, voir §2) |
| **Durée de conservation** | 12 mois à compter de la réception (`purgePrevueLe`, RG-09), purge automatique quotidienne (`PurgeDonneesPersonnelles`, back.md section 11). **À confirmer/justifier** : cette durée est actuellement un défaut technique, pas encore validée au regard de la finalité réelle (back.md section 24, point 6) — voir recommandation ci-dessous. |
| **Mesures de sécurité** | HTTPS obligatoire, jeton CSRF, honeypot, Cloudflare Turnstile, limitation de débit (5 soumissions/heure/IP, `config/packages/rate_limiter.yaml`), accès back-office restreint par rôle (`GerantVoter`), requêtes paramétrées (Doctrine), aucune donnée personnelle dans les journaux applicatifs (back.md section 17) |
| **Droits exercés via** | `ExportDonneesPersonnelles` (droit d'accès, export par courriel/téléphone) et `PurgeDonneesPersonnelles` (suppression) ; export global RGPD accessible en back-office (menu « Export RGPD ») |

**Recommandation** : 12 mois est une durée par défaut, pas encore justifiée. Une demande de RDV/devis qui n'aboutit pas à une relation commerciale n'a pas la même justification qu'un contact prospect au sens marketing (pour lequel la CNIL évoque usuellement 3 ans). Une durée plus courte (6 à 12 mois après traitement de la demande, sauf consentement explicite à la relance) est plus aisément défendable — à trancher avec le garage puis à documenter ici et dans la politique de confidentialité.

### 3.2. Comptes utilisateurs du back-office

| Élément | Détail |
|---|---|
| **Finalité** | Authentifier et autoriser l'accès au back-office (gérant, secrétariat) |
| **Base légale** | Intérêt légitime (gestion du personnel habilité à accéder à l'outil, article 6.1.f) |
| **Personnes concernées** | Salariés/gérant du garage disposant d'un accès (`Utilisateur`, back.md hypothèse : 2 comptes) |
| **Données collectées** | Courriel, mot de passe (haché), rôle, nom, date de dernière connexion |
| **Destinataires** | Aucun destinataire externe |
| **Durée de conservation** | Durée d'activité du compte + suppression à la fin de la relation (pas de purge automatique — action manuelle en back-office) |
| **Mesures de sécurité** | Mots de passe hachés (`password_hasher: auto`), limitation des tentatives de connexion, pare-feu Symfony sur `/admin`, déconnexion après inactivité (back.md section 7.2, A07) |

### 3.3. Journal d'audit des accès aux demandes

| Élément | Détail |
|---|---|
| **Finalité** | Tracer les connexions et les accès/modifications sur les demandes, à des fins de sécurité et de preuve (`JournalAudit`) |
| **Base légale** | Obligation de sécurité du responsable de traitement (article 32 du RGPD) et intérêt légitime |
| **Personnes concernées** | Utilisateurs du back-office (pas les visiteurs) |
| **Données collectées** | Identifiant utilisateur, action réalisée, entité et identifiant concernés, horodatage — jamais la donnée personnelle du visiteur elle-même (back.md section 17) |
| **Destinataires** | Aucun destinataire externe |
| **Durée de conservation** | 12 mois (canal `audit`, back.md section 17) |
| **Mesures de sécurité** | Écriture automatique et non modifiable depuis l'interface, conservation séparée des données métier |

### 3.4. Supervision des erreurs applicatives

| Élément | Détail |
|---|---|
| **Finalité** | Détecter et diagnostiquer les erreurs de production |
| **Base légale** | Intérêt légitime (maintien en condition opérationnelle du site) |
| **Personnes concernées** | Potentiellement tout visiteur dont l'action déclenche une erreur, mais aucune donnée personnelle n'est en pratique collectée |
| **Données collectées** | Trace technique de l'erreur uniquement — données personnelles et corps de requête explicitement désactivés côté Sentry (`docs/supervision.md` §2) |
| **Destinataires** | Sentry (sous-traitant, voir §2) |
| **Durée de conservation** | Politique de rétention du palier Sentry utilisé (par défaut, de l'ordre de 90 jours) |
| **Mesures de sécurité** | DSN tenu secret, filtrage des erreurs 403/404, désactivation des données personnelles à la source |

## 4. Mise à jour de ce registre

Ce registre doit être mis à jour à chaque ajout ou modification d'un traitement de données personnelles (nouveau champ collecté, nouvel outil externe, changement de durée de conservation). Il est versionné dans le dépôt Git au même titre que les autres documents opérationnels (`docs/deploiement.md`, `docs/sauvegarde.md`, `docs/supervision.md`), pour en conserver l'historique.
