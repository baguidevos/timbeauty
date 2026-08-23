# 💈 TimBeauty

> Système de gestion complet pour salon de coiffure — **open source**, **100% en français**, pensé pour l'Afrique de l'Ouest.

---

## Table des matières

1. [Aperçu](#-aperçu)
2. [Fonctionnalités](#-fonctionnalités)
3. [Architecture technique](#-architecture-technique)
4. [Installation](#-installation)
5. [Premier démarrage](#-premier-démarrage)
6. [Guide utilisateur](#-guide-utilisateur)
   - [Tableau de bord](#-tableau-de-bord)
   - [Planning & Rendez-vous](#-planning--rendez-vous)
   - [Clients](#-clients)
   - [Coiffeurs](#-coiffeurs)
   - [Prestations](#-prestations)
   - [Ventes (Point de vente)](#-ventes-point-de-vente)
   - [Produits & Inventaire](#-produits--inventaire)
   - [Caisse](#-caisse)
   - [Dépenses](#-dépenses)
   - [Salaires & Paie](#-salaires--paie)
   - [Présences](#-présences)
   - [Promotions](#-promotions)
   - [Fidélité](#-fidélité)
   - [Statistiques & Rapports](#-statistiques--rapports)
   - [Performance](#-performance)
   - [Fournisseurs & Commandes](#-fournisseurs--commandes)
   - [Objectifs de revenus](#-objectifs-de-revenus)
   - [Galerie photos](#-galerie-photos)
   - [Notifications](#-notifications)
   - [Paramètres](#-paramètres)
7. [Rôles & Permissions](#-rôles--permissions)
8. [Raccourcis clavier](#-raccourcis-clavier)
9. [Modèle de données](#-modèle-de-données)
10. [API](#-api)
11. [Export & Impressions](#-export--impressions)
12. [Portail client](#-portail-client)
13. [Configuration avancée](#-configuration-avancée)
14. [Roadmap](#-roadmap)
15. [Licence](#-licence)

---

## 🎯 Aperçu

BarberShop Pro est un système de gestion tout-en-un conçu spécifiquement pour les salons de coiffure en Afrique de l'Ouest. Il gère l'ensemble des opérations quotidiennes : prise de rendez-vous, vente de services et produits, gestion de caisse, paie des coiffeurs, fidélité client, et bien plus.

### Pourquoi BarberShop Pro ?

| Problème courant | Solution BarberShop Pro |
|---|---|
| Rendez-vous gérés sur carnet | Planning numérique avec vues jour/semaine/mois |
| Prix et ventes non tracés | Point de vente complet avec reçus thermiques |
| Pas de suivi de caisse | Caisse avec ouverture/fermeture et transactions |
| Clients oubliés après visite | Programme de fidélité automatique (points + niveaux) |
| Pas de suivi des dépenses | Catégorisation et suivi des dépenses |
| Paie calculée manuellement | Calcul automatique des salaires (fixe + commissions) |
| Stock géré de tête | Alertes de stock bas, mouvements tracés |
| Promotions informelles | Système de promotions avec conditions et limites |

### Caractéristiques clés

- 🇫🇷 **100% en français** — Interface entièrement traduite
- 💰 **FCFA** — Devise CFA d'Afrique de l'Ouest, format `12 500 FCFA`
- 🇹🇬 **Paiements locaux** — Espèces, TMoney, Flooz, Carte, Virement
- 🌙 **Mode sombre** — Thème ambre or + charbon sombre
- ⌨️ **Raccourcis clavier** — Navigation rapide (Alt+1-9, ⌘K)
- 📱 **Responsive** — Fonctionne sur mobile, tablette et desktop
- 🖨️ **Reçus thermiques** — Compatible imprimantes 58mm/80mm

---

## ✨ Fonctionnalités

### Gestion quotidienne
- ✅ Tableau de bord avec KPIs en temps réel
- ✅ Planning des rendez-vous (vue jour, semaine, mois)
- ✅ Point de vente (POS) avec panier et multi-paiement
- ✅ Gestion de caisse (ouverture, fermeture, transactions)
- ✅ Fiche de vente détaillée et reçus imprimables (58mm/80mm / A4)
- ✅ Gestion des retours produits et annulation de prestations avec réintégration des stocks
- ✅ Édition complète des ventes avec synchronisation automatique d'inventaire

### Gestion des personnes
- ✅ Fiches clients complètes avec historique
- ✅ Gestion des coiffeurs (5 types de rémunération)
- ✅ Programme de fidélité (points, niveaux, récompenses)
- ✅ Suivi des présences (pointage, retards, absences)

### Finance & Comptabilité
- ✅ Suivi des dépenses par catégorie
- ✅ Calcul automatique des salaires (fixe, commission, hybride)
- ✅ Paiement des salaires avec historique
- ✅ Objectifs de revenus (global et par coiffeur)

### Inventaire & Fournisseurs
- ✅ Gestion des produits avec alertes stock bas
- ✅ Mouvements de stock tracés (entrées/sorties)
- ✅ Gestion des fournisseurs et commandes d'achat
- ✅ Réception de commandes conforme à 100% ou partielle avec ajustement précis des stocks

### Marketing & Analytique
- ✅ Promotions (pourcentage ou montant fixe)
- ✅ Statistiques détaillées (multi-périodes)
- ✅ Rapports exportables (CSV, PDF)
- ✅ Classement de performance des coiffeurs

### Système
- ✅ 3 rôles avec permissions (Admin, Caissier, Coiffeur)
- ✅ Notifications (rappels, anniversaires, promotions)
- ✅ Palette de commandes (⌘K)
- ✅ Journal d'activité
- ✅ Paramètres configurables
- ✅ Galerie photos avant/après
- ✅ Portail client en ligne

---

## 🏗️ Architecture technique

### Stack technique

| Couche | Technologie | Version |
|--------|------------|---------|
| **Frontend** | Next.js (App Router) | 16 |
| **Langage** | TypeScript | 5 |
| **Styling** | Tailwind CSS + shadcn/ui | 4 |
| **Base de données** | SQLite via Prisma ORM | 6 |
| **État client** | Zustand | 5 |
| **Graphiques** | Recharts | 2 |
| **Animations** | Framer Motion | 12 |
| **Formulaires** | React Hook Form + Zod | 7 |
| **Icônes** | Lucide React | — |
| **Runtime** | Bun | — |

### Architecture de l'application

```
BarberShop Pro
├── 📊 Single-Page Application (SPA)
│   └─ Flament, Livewire, Alpine.js
├── 🔐 RBAC
│   └── 3 rôles : admin, cashier, barber, avec shield de filament
└── 📦 Export
    ├── CSV (séparateur ;, encodage UTF-8 BOM)
    └── PDF (impression navigateur)
```


---

## 📦 Installation

### Prérequis

- **Bun** >= 1.3 (ou Node.js >= 18)
- **Git**

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/votre-org/barbershop-pro.git
cd barbershop-pro

# 2. Installer les dépendances
bun install

# 3. Configurer l'environnement
cp .env.example .env
# Éditer .env si nécessaire (la base SQLite est pré-configurée)

# 4. Initialiser la base de données
bun run db:push

# 5. Insérer les données de démonstration
bun run db:seed

# 6. Démarrer l'application
bun run dev
```

L'application est accessible sur `http://localhost:3000`.

---

## 🚀 Premier démarrage

### Connexion

L'application inclut 3 utilisateurs de démonstration :

| Rôle | Email | Mot de passe | Accès |
|------|-------|-------------|-------|
| **Administrateur** | `admin@barbershop.com` | `password` | Accès complet |
| **Caissier** | `caissier@barbershop.com` | `password` | 17 modules |
| **Coiffeur** | `coiffeur@barbershop.com` | `password` | 5 modules |

### Sélecteur de rôle

En mode développement, un **sélecteur de rôle** est disponible dans l'en-tête. Il permet de basculer entre les 3 rôles sans connexion, pour tester les permissions.

> 💡 En production, ce sélecteur est remplacé par un vrai système d'authentification.

### Navigation

L'interface est organisée en **5 sections** dans la barre latérale :

```
📋 Principal
   ├── Tableau de bord
   ├── Statistiques
   ├── Rapports
   ├── Planning
   └── Galerie

👥 Gestion
   ├── Clients
   ├── Coiffeurs
   └── Prestations

🛒 Ventes
   ├── Ventes
   ├── Produits
   ├── Fournisseurs
   └── Caisse

💰 Finance
   ├── Dépenses
   ├── Salaires
   ├── Objectifs
   ├── Présences
   ├── Promotions
   ├── Performance
   └── Fidélité

⚙️ Système
   ├── Notifications
   └── Paramètres
```

---

## 📖 Guide utilisateur

### 📊 Tableau de bord

Le tableau de bord est la page d'accueil. Il offre une vue d'ensemble en temps réel de l'activité du salon.

#### KPIs affichés

| Indicateur | Description | Exemple |
|-----------|-------------|---------|
| **Revenu du jour** | Total des ventes d'aujourd'hui | `85 000 FCFA` |
| **Revenu mensuel** | Total des ventes du mois | `1 250 000 FCFA` |
| **Rendez-vous du jour** | Nombre de RDV aujourd'hui | `12` |
| **Clients totaux** | Nombre de clients enregistrés | `156` |
| **Dépenses du jour** | Total des dépenses aujourd'hui | `15 000 FCFA` |
| **Solde de caisse** | Solde de la caisse ouverte | `70 000 FCFA` |
| **RDV en attente** | Rendez-vous non confirmés | `3` |
| **Alertes stock** | Produits sous seuil minimum | `2` |

#### Graphiques

- **Revenu 7 jours** — Graphique en aires montrant la tendance sur la semaine
- **Top prestations** — Graphique en barres des 5 prestations les plus demandées
- **Top coiffeurs** — Classement par chiffre d'affaires

#### Sections complémentaires

- **Rendez-vous récents** — Les 5 derniers RDV avec statut
- **Dépenses récentes** — Les 5 dernières dépenses
- **Anniversaires du mois** — Clients dont l'anniversaire tombe ce mois
- **Niveaux de fidélité** — Répartition des clients par niveau
- **Prochain rendez-vous** — Compte à rebours en temps réel vers le prochain RDV
- **Objectif de revenu** — Barre de progression vers l'objectif mensuel

---

### 📅 Planning & Rendez-vous

Le module Planning permet de gérer tous les rendez-vous du salon.

#### Vues disponibles

| Vue | Description |
|-----|-------------|
| **Jour** | Créneaux horaires de 8h à 19h, un coiffeur ou tous |
| **Semaine** | 7 jours avec créneaux, colonnes par coiffeur |
| **Mois** | Calendrier mensuel avec compteurs par jour |

#### Créer un rendez-vous

1. Cliquer sur le bouton **+ Nouveau rendez-vous** (ou sur un créneau vide en vue jour)
2. Sélectionner le **client** (recherche possible)
3. Sélectionner le **coiffeur**
4. Sélectionner la **prestation**
5. L'heure de fin est calculée automatiquement selon la durée de la prestation
6. Ajouter des **notes** si nécessaire
7. Valider

#### Statuts des rendez-vous

| Statut | Couleur | Signification | Transition possible vers |
|--------|---------|---------------|------------------------|
| ⏳ **En attente** | Ambre | RDV créé, en attente de confirmation | Confirmé, Annulé |
| ✅ **Confirmé** | Bleu ciel | RDV confirmé par le client | En cours, Annulé |
| 🔵 **En cours** | Émeraude | Prestation en cours d'exécution | Terminé |
| 🟢 **Terminé** | Émeraude foncé | Prestation terminée | — |
| 🔴 **Annulé** | Rouge | RDV annulé | — |
| ⬛ **Absent** | Gris | Client ne s'est pas présenté | — |

#### Détection de conflits

Le système détecte automatiquement les chevauchements de créneaux pour un même coiffeur et affiche une alerte.

---

### 👥 Clients

Le module Clients gère la base de clients du salon.

#### Fiche client

Chaque client possède :

| Champ | Description | Requis |
|-------|-------------|--------|
| Prénom | Prénom du client | ✅ |
| Nom | Nom de famille | ✅ |
| Téléphone | Numéro principal | ✅ |
| WhatsApp | Numéro WhatsApp | — |
| Email | Adresse email | — |
| Genre | Homme / Femme | — |
| Date de naissance | Pour anniversaires | — |
| Adresse | Adresse physique | — |
| Notes | Notes libres | — |

#### Informations automatiques

- **Première visite** — Date automatiquement enregistrée
- **Dernière visite** — Mise à jour à chaque vente
- **Nombre de visites** — Compteur incrémenté automatiquement
- **Total dépensé** — Somme de toutes les ventes
- **Statut fidèle** — Activé après un nombre de visites configuré
- **Points fidélité** — Gagnés à chaque vente (voir section Fidélité)
- **Niveau fidélité** — Bronze, Argent, Or ou Platine

#### Détail client

En cliquant sur un client, un panneau latéral affiche :
- Informations personnelles
- Historique des rendez-vous
- Historique des achats
- Utilisation des promotions
- Graphique des dépenses

#### Export

Les clients peuvent être exportés en **CSV** (compatible Excel français, séparateur `;`) ou **PDF**.

---

### ✂️ Coiffeurs

Le module Coiffeurs gère le personnel du salon.

#### Types de rémunération

| Type | Description | Formule |
|------|-------------|---------|
| **Fixe** | Salaire mensuel fixe | `fixedSalary` |
| **Commission** | Pourcentage sur les ventes | `totalVentes × commissionRate` |
| **Fixe + Commission** | Salaire de base + commission | `fixedSalary + (totalVentes × commissionRate)` |
| **Par prestation** | Taux fixe par prestation | `nombrePrestations × perServiceRate` |
| **Hybride** | Combinaison personnalisée | Configurable |

#### Profil coiffeur

- Informations personnelles (nom, téléphone, adresse)
- Statut : **Actif** / **Inactif**
- Spécialités (ex: "Coupe, Tresse, Coloration")
- Type et paramètres de rémunération
- Date d'embauche
- Photo de profil
- Emploi du temps (voir section Présences)
- Historique des absences

#### Lien avec un utilisateur

Un coiffeur peut être lié à un **compte utilisateur** pour lui donner accès à l'application avec le rôle "Coiffeur".

---

### ✨ Prestations

Le module Prestations gère le catalogue de services du salon.

#### Organisation

Les prestations sont organisées en **catégories** (ex: Coupe, Coiffure, Soin, Barbier). Chaque catégorie a :
- Un nom
- Une description optionnelle
- Une icône
- Un ordre d'affichage

#### Prestation

| Champ | Description | Exemple |
|-------|-------------|---------|
| Nom | Nom de la prestation | "Coupe homme" |
| Prix | Prix en FCFA | `3 000 FCFA` |
| Durée | Durée en minutes | `30 min` |
| Catégorie | Catégorie parent | "Coupe" |
| Commission | % de commission pour le coiffeur | `30%` |
| Statut | Actif / Inactif | — |

> 💡 La **commission** définie ici peut remplacer celle du coiffeur si le type de rémunération est "par prestation".

---

### 🛒 Ventes (Point de vente)

Le module Ventes est le **point de vente** du salon. C'est le module le plus utilisé au quotidien.

#### Interface POS

L'interface se compose de :
1. **Sélecteur de prestations** — Groupées par catégorie, cliquer pour ajouter au panier
2. **Sélecteur de produits** — Produits disponibles à la vente
3. **Panier** — Liste des articles avec quantité et prix
4. **Sélection client** — Optionnel, pour la fidélité et l'historique
5. **Sélection coiffeur** — Celui qui réalise la prestation
6. **Réduction** — Montant ou pourcentage de réduction
7. **Paiement** — Choix du mode de paiement

#### Ajouter un article

- **Prestation** : Cliquer sur la prestation dans le sélecteur → ajouté au panier
- **Produit** : Cliquer sur le produit → quantité +1, stock décrémenté
- **Modifier** : Les boutons +/- ajustent la quantité
- **Supprimer** : Le bouton 🗑️ retire l'article du panier

#### Modes de paiement

| Mode | Icône | Usage |
|------|-------|-------|
| **Espèces** | 💵 | Paiement en liquide |
| **TMoney** | 📱 | Mobile money Togo Telecom |
| **Flooz** | 💜 | Mobile money Moov Africa |
| **Carte** | 💳 | Carte bancaire |
| **Virement** | ↔️ | Virement bancaire |

#### Finaliser une vente

1. Vérifier le contenu du panier
2. Sélectionner le client (optionnel)
3. Sélectionner le coiffeur
4. Appliquer une réduction si nécessaire
5. Choisir le mode de paiement
6. Cliquer **Encaisser**
7. Un **reçu** est généré automatiquement

#### Reçu de vente

Le reçu inclut :
- Nom et adresse du salon
- Date et heure
- Numéro de vente
- Client et coiffeur
- Liste des articles (prestation/produit, quantité, prix)
- Sous-total, réduction, total
- Mode de paiement

Le reçu peut être :
- **Visualisé** dans une fenêtre de prévisualisation
- **Imprimé** sur imprimante thermique (58mm ou 80mm)
- **Réimprimé** depuis l'historique des ventes

#### Historique des ventes

L'onglet **Historique** affiche toutes les ventes passées avec :
- Filtrage par date, client, coiffeur, mode de paiement
- Détail de chaque vente (articles, paiements)
- Réimpression du reçu
- Export CSV/PDF

---

### 📦 Produits & Inventaire

Le module Produits gère l'inventaire du salon (shampoings, gels, accessoires, etc.).

#### Gestion des produits

| Champ | Description | Exemple |
|-------|-------------|---------|
| Nom | Nom du produit | "Gel coiffant 500ml" |
| Référence | Code unique | "GEL-500" |
| Catégorie | Catégorie de produit | "Soins capillaires" |
| Prix d'achat | Prix d'achat fournisseur | `5 000 FCFA` |
| Prix de vente | Prix de vente client | `8 000 FCFA` |
| Stock actuel | Quantité en stock | `25` |
| Stock minimum | Seuil d'alerte | `5` |
| Fournisseur | Fournisseur principal | "Beauty Supply Lomé" |
| Statut | Actif / Inactif | — |

#### Alertes de stock

Les produits dont le **stock actuel ≤ stock minimum** sont signalés en **rouge** dans la liste. Le tableau de bord affiche aussi un compteur d'alertes.

#### Mouvements de stock

Chaque entrée ou sortie de stock est tracée :
- **Entrée** : Réception commande, retour, ajustement
- **Sortie** : Vente, perte, ajustement
- **Raison** : Texte expliquant le mouvement
- **Référence** : Lien vers la vente ou commande associée

---

### 💰 Caisse

Le module Caisse gère l'ouverture et la fermeture de la caisse enregistreuse.

#### Cycle de vie

```
Ouverture → Transactions → Fermeture
```

1. **Ouvrir la caisse** — Saisir le montant initial (fonds de caisse)
2. **Transactions** — Les ventes et dépenses sont automatiquement liées
3. **Fermer la caisse** — Saisir le montant de clôture
4. **Écart** — Calculé automatiquement entre le solde théorique et réel

#### Types de transactions

| Type | Effet | Description |
|------|-------|-------------|
| Vente | + | Encaissement d'une vente |
| Dépense | - | Paiement d'une dépense |
| Dépôt | + | Ajout d'espèces |
| Retrait | - | Retrait d'espèces |
| Ajustement | ± | Correction manuelle |

#### Indicateurs

- **Montant d'ouverture** — Fonds de caisse initial
- **Solde actuel** — Opening + ventes - dépenses + dépôts - retraits
- **Statut** — Ouverte / Fermée
- **Heure d'ouverture** — Date/heure de la dernière ouverture

> ⚠️ Il ne peut y avoir qu'**une seule caisse ouverte** à la fois.

---

### 🧾 Dépenses

Le module Dépenses permet de suivre toutes les dépenses du salon.

#### Catégories de dépenses

Exemples prédéfinis :
- Loyer
- Électricité / Eau / Internet
- Fournitures
- Produits capillaires
- Marketing
- Transport
- Salaires
- Maintenance

#### Saisie d'une dépense

| Champ | Description | Exemple |
|-------|-------------|---------|
| Montant | Montant en FCFA | `50 000 FCFA` |
| Catégorie | Catégorie de dépense | "Loyer" |
| Date | Date de la dépense | 01/08/2026 |
| Bénéficiaire | Qui reçoit le paiement | "Propriétaire" |
| Mode de paiement | Espèces, TMoney, etc. | "Virement" |
| Description | Notes supplémentaires | "Loyer août 2026" |

#### Filtrage et export

- Filtrage par **catégorie**, **date**, **mode de paiement**
- Export **CSV** ou **PDF**

---

### 💵 Salaires & Paie

Le module Salaires calcule et gère la paie des coiffeurs.

#### Fiche de paie

| Élément | Description |
|---------|-------------|
| **Salaire fixe** | Montant de base selon le type de rémunération |
| **Commissions** | Total des commissions du mois |
| **Bonus** | Bonus manuel ajouté |
| **Avances** | Avances déjà versées |
| **Déductions** | Déductions (retards, etc.) |
| **Salaire net** | Fixe + Commissions + Bonus - Avances - Déductions |

#### Processus de paie

1. **Sélectionner le mois** — Choisir le mois et l'année
2. **Générer** — Le système calcule automatiquement les commissions
3. **Ajouter des ajustements** — Bonus, avances, déductions
4. **Approuver** — Vérifier et approuver la fiche
5. **Payer** — Enregistrer le paiement avec mode et date

#### Statuts de paie

| Statut | Description |
|--------|-------------|
| **Brouillon** | Calcul en cours, modifiable |
| **Approuvé** | Vérifié, prêt à payer |
| **Payé** | Paiement enregistré |
| **Annulé** | Paie annulée |

---

### ⏰ Présences

Le module Présences gère le pointage et le suivi des coiffeurs.

#### Emploi du temps

Définir les horaires de chaque coiffeur pour chaque jour de la semaine :
- Heure de début et de fin
- Jour de repos (toggle)

#### Pointage

| Action | Effet |
|--------|-------|
| **Pointage d'entrée** | Enregistre l'heure d'arrivée |
| **Pointage de sortie** | Enregistre l'heure de départ, calcule les minutes travaillées |

#### Statuts de présence

| Statut | Condition |
|--------|-----------|
| **Présent** | Arrivé à l'heure |
| **En retard** | Arrivé après l'heure prévue |
| **Absent** | Pas de pointage |
| **Demi-journée** | Pointé seulement un créneau |
| **Congé** | Jour de repos ou absence autorisée |

#### Absences planifiées

Les absences peuvent être enregistrées à l'avance :
- **Maladie** — Avec ou sans justificatif
- **Personnel** — Congé personnel
- **Congé** — Congé annuel

---

### 🏷️ Promotions

Le module Promotions gère les offres promotionnelles du salon.

#### Types de promotions

| Type | Description | Exemple |
|------|-------------|---------|
| **Pourcentage** | Réduction en % | "-20% sur les coupes" |
| **Montant fixe** | Réduction en FCFA | "-500 FCFA sur la tresse" |

#### Conditions d'application

| Condition | Description |
|-----------|-------------|
| **Dates** | Période de validité (début/fin) |
| **Visites minimum** | Le client doit avoir X visites |
| **Clients fidèles uniquement** | Réservé aux clients marqués "fidèles" |
| **Usages maximum** | Limite le nombre total d'utilisations |
| **Prestations ciblées** | Applicable seulement à certaines prestations |

#### Cycle de vie

1. **Créer** la promotion avec ses conditions
2. **Activer** la promotion (statut: Actif)
3. Le système **vérifie automatiquement** l'éligibilité lors des ventes
4. Les **utilisations sont comptées** et tracées
5. La promotion **expire** automatiquement si maxUsages atteint ou date dépassée

---

### 🏆 Fidélité

Le module Fidélité gère le programme de fidélisation des clients.

#### Système de points

- Chaque vente génère des **points** pour le client
- Le nombre de points dépend du **niveau** du client (points par FCFA)
- Les points peuvent être **gagnés**, **utilisés**, **ajustés** ou reçus en **bonus**

#### Niveaux de fidélité

| Niveau | Points minimum | Points/1000 FCFA | Réduction |
|--------|---------------|-----------------|-----------|
| 🥉 **Bronze** | 0 | 1 | 0% |
| 🥈 **Argent** | 100 | 1.5 | 5% |
| 🥇 **Or** | 500 | 2 | 10% |
| 💎 **Platine** | 1 000 | 3 | 15% |

> Les clients montent de niveau **automatiquement** quand ils atteignent le seuil de points.

#### Règles de fidélité

Des règles peuvent définir des récompenses automatiques :
- **Visites requises** — Ex: "Après 4 visites, -10% sur la tresse"
- **Service ciblé** — Optionnel, pour une prestation spécifique
- **Période de refroidissement** — Délai entre deux utilisations
- **Durée de validité** — Validité de la récompense en jours

#### Tableau de bord fidélité

- Répartition des clients par niveau
- Historique des transactions de points
- Classement des meilleurs clients
- Statistiques d'utilisation

---

### 📊 Statistiques & Rapports

### Statistiques

Le module Statistiques offre des analyses détaillées sur l'activité du salon.

#### Périodes d'analyse

- Aujourd'hui
- Hier
- 7 derniers jours
- 30 derniers jours
- Ce mois
- Ce trimestre
- Personnalisé (date début/fin)

#### Indicateurs disponibles

- **Chiffre d'affaires** — Total, moyen par vente, par jour
- **Rendez-vous** — Total, par statut, taux de présence
- **Clients** — Nouveaux, revenants, dépense moyenne
- **Prestations** — Top 10, répartition par catégorie
- **Coiffeurs** — CA par coiffeur, commissions, nombre de prestations
- **Paiements** — Répartition par mode (espèces, TMoney, Flooz…)
- **Produits** — Top ventes, stock value, rotation

#### Rapports

Le module Rapports permet de générer des rapports imprimables :

- **Rapport quotidien** — Résumé de la journée
- **Rapport hebdomadaire** — Analyse de la semaine
- **Rapport mensuel** — Bilan du mois
- **Rapport par coiffeur** — Performance individuelle
- **Rapport financier** — Revenus, dépenses, bénéfice

Tous les rapports peuvent être exportés en **CSV** ou **PDF**.

---

### 📈 Performance

Le module Performance analyse la performance individuelle des coiffeurs.

#### Métriques par coiffeur

| Métrique | Description |
|----------|-------------|
| Chiffre d'affaires | Total des ventes du coiffeur |
| Nombre de prestations | Nombre de services réalisés |
| Ticket moyen | CA / nombre de ventes |
| Taux de complétion | % de RDV terminés vs annulés/absents |
| Revenu moyen par heure | CA / heures travaillées |
| Commission gagnée | Total des commissions |

#### Classement

Les coiffeurs sont classés selon différents critères :
- 🥇 Top chiffre d'affaires
- 🥇 Top nombre de prestations
- 🥇 Top satisfaction (basé sur le taux de complétion)

#### Objectifs individuels

Des objectifs de revenus peuvent être fixés pour chaque coiffeur (voir section Objectifs).

---

### 🚚 Fournisseurs & Commandes

### Fournisseurs

Gestion des fournisseurs de produits :

| Champ | Description | Exemple |
|-------|-------------|---------|
| Nom | Nom du fournisseur | "Beauty Supply Lomé" |
| Contact | Personne de contact | "Kofi" |
| Téléphone | Numéro | "+228 90 12 34 56" |
| Email | Adresse email | "contact@beauty.tg" |
| Conditions | Conditions de paiement | "Net 30 jours" |
| Statut | Actif / Inactif | — |

### Commandes d'achat

| Statut | Description |
|--------|-------------|
| **En attente** | Commande créée |
| **Approuvée** | Validée par l'admin |
| **Commandée** | Envoyée au fournisseur |
| **Reçue** | Marchandise réceptionnée |
| **Annulée** | Commande annulée |

Le stock est automatiquement mis à jour quand une commande passe en statut **Reçue**.

---

### 🎯 Objectifs de revenus

Le module Objectifs permet de fixer des cibles de revenus.

#### Types d'objectifs

| Type | Description | Exemple |
|------|-------------|---------|
| **Salon** | Objectif global du salon | "1 000 000 FCFA/mois" |
| **Coiffeur** | Objectif individuel | "350 000 FCFA/mois" |

#### Suivi

- **Barre de progression** — % atteint par rapport à l'objectif
- **Écart** — Montant restant à atteindre
- **Tendance** — Basée sur le rythme actuel, l'objectif sera-t-il atteint ?

---

### 📸 Galerie photos

La galerie permet de documenter les transformations avec des photos **avant/après**.

#### Fonctionnalités

- Ajouter des photos avant et après pour un rendez-vous
- Lier à un client, un coiffeur, et optionnellement un RDV
- Ajouter des **légendes** et **tags**
- Filtrer par client, coiffeur, type (avant/après)
- Visualiser en galerie avec comparaison

> 💡 Idéal pour le marketing sur les réseaux sociaux !

---

### 🔔 Notifications

Le centre de notifications agrège toutes les alertes et rappels.

#### Types de notifications

| Type | Description | Canaux |
|------|-------------|--------|
| Rappel RDV | Rappel avant un rendez-vous | In-app, SMS, WhatsApp |
| Anniversaire | Anniversaire client | In-app, SMS |
| Promotion | Nouvelle promotion disponible | In-app |
| Stock bas | Produit sous seuil minimum | In-app |
| Caisse | Alerte de caisse | In-app |
| Paiement | Confirmation de paiement | In-app |
| Salaire | Fiche de paie disponible | In-app |
| Personnalisée | Notification libre | In-app |

#### Fonctionnement

- Les notifications **in-app** apparaissent en temps réel (polling toutes les 60s)
- Un **badge** avec compteur s'affiche dans l'en-tête
- Les notifications peuvent être marquées **lues** individuellement ou toutes en même temps
- Les canaux SMS et WhatsApp sont des **placeholders** prêts à être connectés à une API

---

### ⚙️ Paramètres

Le module Paramètres configure les options globales du salon.

#### Paramètres disponibles

| Catégorie | Paramètres |
|-----------|-----------|
| **Salon** | Nom, adresse, téléphone, logo |
| **Horaires** | Heure d'ouverture, heure de fermeture |
| **Devise** | Devise et format (FCFA par défaut) |
| **Reçu** | Largeur (58mm/80mm), texte personnalisé, logo |
| **Fidélité** | Points par FCFA, seuils de niveaux |
| **Notifications** | Préférences de canaux |

---

## 🔐 Rôles & Permissions

### Les 3 rôles

#### 👑 Administrateur

**Accès complet** à tous les modules. Peut :
- Gérer les utilisateurs et les rôles
- Configurer les paramètres du salon
- Accéder aux statistiques et rapports
- Gérer la paie et les objectifs
- Toutes les opérations du Caissier et du Coiffeur

#### 💼 Caissier

**Accès opérationnel** pour la gestion quotidienne. Peut :
- Voir le tableau de bord
- Gérer les rendez-vous, clients, coiffeurs, prestations
- Réaliser des ventes (POS)
- Gérer les produits et la caisse
- Saisir des dépenses
- Voir les rapports et statistiques
- Gérer les promotions et la fidélité
- Gérer les présences et la galerie
- Voir les notifications et les performances

**Ne peut PAS** :
- Gérer les salaires et la paie
- Configurer les paramètres système
- Gérer les utilisateurs
- Voir les objectifs de revenus

#### ✂️ Coiffeur

**Accès limité** à ses propres activités. Peut :
- Voir le tableau de bord
- Voir et modifier ses rendez-vous
- Réaliser des ventes
- Voir les notifications
- Voir la galerie

**Ne peut PAS** :
- Gérer les clients, prestations, produits
- Accéder à la caisse, dépenses, salaires
- Voir les statistiques et rapports
- Gérer les promotions et la fidélité

### Tableau récapitulatif

| Module | Admin | Caissier | Coiffeur |
|--------|:-----:|:--------:|:--------:|
| Tableau de bord | ✅ | ✅ | ✅ |
| Statistiques | ✅ | ✅ | ❌ |
| Rapports | ✅ | ✅ | ❌ |
| Planning | ✅ | ✅ | ✅ |
| Galerie | ✅ | ✅ | ✅ |
| Clients | ✅ | ✅ | ❌ |
| Coiffeurs | ✅ | ✅ | ❌ |
| Prestations | ✅ | ✅ | ❌ |
| Ventes | ✅ | ✅ | ✅ |
| Produits | ✅ | ✅ | ❌ |
| Fournisseurs | ✅ | ❌ | ❌ |
| Caisse | ✅ | ✅ | ❌ |
| Dépenses | ✅ | ✅ | ❌ |
| Salaires | ✅ | ❌ | ❌ |
| Objectifs | ✅ | ❌ | ❌ |
| Présences | ✅ | ✅ | ❌ |
| Promotions | ✅ | ✅ | ❌ |
| Performance | ✅ | ✅ | ❌ |
| Fidélité | ✅ | ✅ | ❌ |
| Notifications | ✅ | ✅ | ✅ |
| Paramètres | ✅ | ❌ | ❌ |

---

## ⌨️ Raccourcis clavier

### Navigation rapide

| Raccourci | Module |
|-----------|--------|
| `Alt + 1` | Tableau de bord |
| `Alt + 2` | Planning |
| `Alt + 3` | Clients |
| `Alt + 4` | Ventes |
| `Alt + 5` | Caisse |
| `Alt + 6` | Produits |
| `Alt + 7` | Dépenses |
| `Alt + 8` | Performance |
| `Alt + 0` | Paramètres |

### Actions rapides

| Raccourci | Action |
|-----------|--------|
| `Alt + Shift + N` | Nouveau rendez-vous |
| `Alt + Shift + R` | Nouveau client |
| `Alt + Shift + V` | Nouvelle vente |
| `Alt + Shift + E` | Nouvelle dépense |

### Globaux

| Raccourci | Action |
|-----------|--------|
| `⌘K` / `Ctrl+K` | Palette de commandes |
| `?` | Aide des raccourcis |

### Palette de commandes (⌘K)

La palette de commandes permet de :
- **Rechercher** un module par nom
- **Naviguer** vers n'importe quelle page
- **Créer** un nouveau client, RDV, vente, dépense
- **Rechercher** un client par nom ou téléphone

---

## 🗃️ Modèle de données

### 35 modèles au total

| Catégorie | Modèles |
|-----------|---------|
| **Auth** | User |
| **Clients** | Client |
| **Staff** | Barber, StaffSchedule, StaffAbsence, StaffAttendance |
| **Services** | ServiceCategory, Service |
| **Appointments** | Appointment |
| **Sales** | Sale, SaleItem, Payment |
| **Products** | ProductCategory, Product, StockMovement |
| **Finance** | CashRegister, CashTransaction, ExpenseCategory, Expense |
| **Payroll** | Payroll, SalaryPayment |
| **Promotions** | Promotion, PromotionService, PromotionUsage |
| **Loyalty** | LoyaltyRule, LoyaltyTier, LoyaltyPointTransaction |
| **Suppliers** | Supplier, PurchaseOrder, PurchaseOrderItem |
| **Targets** | RevenueTarget |
| **Gallery** | AppointmentPhoto |
| **System** | Setting, ActivityLog, Notification |

---

## 🔌 API

### Endpoints principaux

#### Dashboard
```
GET /api/dashboard → { todayRevenue, monthRevenue, todayAppointments, ... }
```

#### CRUD standard (exemple avec les clients)
```
GET    /api/clients          → Client[]
POST   /api/clients          → Client
GET    /api/clients/:id      → Client
PUT    /api/clients/:id      → Client
DELETE /api/clients/:id      → { success: true }
```

#### Endpoints spéciaux

| Endpoint | Description |
|----------|-------------|
| `GET /api/products/reorder-suggestions` | Produits à réapprovisionner |
| `GET /api/cash-registers/transactions` | Transactions de la caisse ouverte |
| `GET /api/attendance/summary` | Résumé des présences |
| `GET /api/loyalty/leaderboard` | Classement fidélité |
| `GET /api/birthdays` | Anniversaires du mois |
| `GET /api/booking/availability` | Créneaux disponibles |
| `GET /api/portal/client` | Données du portail client |
| `GET /api/notifications` | Notifications non lues |
| `PUT /api/notifications` | Marquer tout comme lu |

#### Export

```
GET /api/export/clients?format=csv
GET /api/export/sales?format=csv
GET /api/export/expenses?format=pdf
GET /api/export/inventory?format=csv
GET /api/export/payroll?format=pdf
GET /api/export/appointments?format=csv
GET /api/export/statistics?format=pdf
```

---

## 📤 Export & Impressions

### CSV

- **Séparateur** : `;` (compatibilité Excel français)
- **Encodage** : UTF-8 avec BOM
- **Colonnes** : Noms français (Prénom, Nom, Téléphone…)
- **Montants** : Format FCFA sans symbole (`12500`)

### PDF

- **Méthode** : Génération HTML → impression navigateur
- **Format** : A4 avec en-tête du salon
- **Personnalisable** : Logo, adresse, téléphone

### Reçus thermiques

| Largeur | Usage |
|---------|-------|
| **58mm** | Imprimante portable |
| **80mm** | Imprimante comptoir |

---

## 🌐 Portail client

BarberShop Pro inclut un **portail client** accessible publiquement.

### Page de réservation (`/booking`)

Le client peut :
1. Voir les **prestations disponibles** avec prix et durée
2. Choisir un **coiffeur**
3. Sélectionner un **créneau horaire** (les créneaux occupés sont grisés)
4. Saisir ses **coordonnées** (nom, téléphone)
5. Confirmer la **réservation**

### Espace client (`/portal`)

Le client peut :
- Voir ses **prochains rendez-vous**
- Voir son **historique** de visites
- Voir ses **points de fidélité** et niveau
- Voir les **promotions** disponibles

---

## ⚙️ Configuration avancée

### Données de démonstration

Les données semées incluent des données **réalistes togolaises** :

#### Coiffeurs
| Nom | Type | Salaire/Commission |
|-----|------|-------------------|
| Paul Amégan | Commission | 30% |
| David Kossi | Fixe + Commission | 50 000 FCFA + 15% |
| Kévi Adzoe | Par prestation | 500 FCFA/prestation |

#### Prestations (exemples)
| Catégorie | Prestation | Prix | Durée |
|-----------|-----------|------|-------|
| Coupe | Coupe homme | 2 000 FCFA | 30 min |
| Coupe | Coupe enfant | 1 500 FCFA | 20 min |
| Tresse & Locks | Tresse femme | 5 000 FCFA | 60 min |
| Tresse & Locks | Locks entretien | 4 000 FCFA | 45 min |
| Soins & Coloration | Coloration | 5 000 FCFA | 45 min |
| Soins & Coloration | Soin profond | 3 000 FCFA | 30 min |
| Barbe | Taille barbe | 1 000 FCFA | 15 min |
| Barbe | Rasage complet | 1 500 FCFA | 20 min |

### Personnalisation du thème

| Couleur | Usage | Valeur |
|---------|-------|--------|
| **Ambre 500** | Couleur primaire | `#f59e0b` |
| **Slate 900** | Arrière-plan sombre | `#0f172a` |
| **Emerald** | Succès, terminé | `#10b981` |
| **Rose** | Danger, erreur | `#f43f5e` |
| **Sky** | Information | `#0ea5e9` |

---

## 🗺️ Roadmap

- [ ] 🔌 **ESC/POS** — Intégration directe imprimantes thermiques
- [ ] 📱 **WhatsApp** — Notifications via WhatsApp Business API
- [ ] 📱 **SMS** — Notifications SMS
- [ ] 💳 **TMoney/Flooz** — Intégration API mobile money
- [ ] 🏪 **Multi-salons** — Support de plusieurs succursales
- [ ] 🎁 **Programme parrainage** — Bonus pour les recommandations
- [ ] 📸 **Avant/Après IA** — Analyse automatique des transformations
- [ ] 📊 **Dashboard coiffeur** — Vue personnelle pour chaque coiffeur
- [ ] 🎯 **Tâches & Objectifs** — Module de gestion des tâches
- [ ] 🔐 **Authentification** — Vrai système de login
- [ ] 🌍 **i18n** — Support multilingue (FR, EN, ES)
- [ ] 📱 **App mobile** — Application mobile (PWA)

---

## 📄 Licence

BarberShop Pro est un projet **open source** publié sous licence **MIT**.

---

<p align="center">
  <strong>💈 BarberShop Pro</strong><br>
  <em>Gestion de salon de coiffure — Made in Togo 🇹🇬</em><br><br>
  <em>Conçu avec ❤️ pour les coiffeurs d'Afrique de l'Ouest</em>
</p>
