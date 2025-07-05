=== WP Post by Email ===
Contributors: kevinbenabdelhak
Tags: email, publication, imap, article, automatique, image à la une, media
Requires at least: 5.0
Tested up to: 6.5.3
Requires PHP: 7.0
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WP Post by Email est un plugin WordPress qui permet de publier automatiquement des articles en envoyant simplement un e-mail à une adresse de votre choix. Pratique pour publier à distance ou alimenter un site avec des contenus facilement : chaque mail non lu devient un article, avec support des images et d’une image à la une par pièce jointe.

== Description ==

**Publiez vos articles par e-mail, simplement !**  
Ce plugin se connecte automatiquement à votre boîte mail IMAP lors de chaque chargement du site, et publie tous les e-mails non lus reçus en tant qu’articles WordPress :

Fonctionnalités principales :

- **Publication automatique** : chaque nouvel e-mail devient un nouvel article.
- **Support des images** : les images intégrées dans le corps du mail (inline) sont ajoutées dans l’article.
- **Image à la une** : la première image envoyée en pièce-jointe (non-inline) devient l’image à la une de l’article.
- **Filtrage par expéditeur** : vous pouvez limiter la création d’articles à certains e-mails uniquement (ou autoriser tous).
- **Paramétrage IMAP** : configuration facile de l’adresse, du mot de passe, du serveur et du port directement dans les réglages WordPress.

== Installation ==

1. **Téléchargez** le plugin (fichier ZIP) : https://kevin-benabdelhak.fr/plugins/wp-post-by-email/
2. Dans WordPress, allez dans « Extensions » > « Ajouter » > « Téléverser une extension » et importez le fichier ZIP.
3. Cliquez sur « Activer ».
4. Allez dans « Réglages » > « WP Post by Email » et renseignez les informations de connexion IMAP (adresse, mot de passe, serveur, port, expéditeurs autorisés).
5. Envoyez un e-mail à l’adresse configurée : il sera publié automatiquement à votre prochaine visite sur le site.

== Utilisation ==

- Écrivez un e-mail (texte ou avec HTML/images si besoin).
- Ajoutez éventuellement une image en pièce jointe pour qu’elle devienne l’image à la une.
- Envoyez le mail à l’adresse configurée dans les réglages du plugin.
- L’article sera automatiquement publié (statut : publié, auteur administrateur par défaut).

== Journal des modifications ==

= 1.0
* Ajouter un e-mail, un mot de passe, un serveur IMAP et un port
* Configurer les emails d'envois pour restriction
* Les images dans le corp du mail sont importées dans les médias et intégrées dans les articles
* Possibilité de mettre une image en avant (en pièce-jointe)
* L'objet correspond au titre de l'article
* L'article est publié automatiquement sur votre site (Attention : Il vous faut créer une tâche cron serveur. Il faut intégrer une tâche cron sur votre hébergeur web ou se rendre sur une page de votre site pour que l'article se publie). Le Hook de publication est "init".

== FAQ ==

= Quelles boîtes mail sont compatibles ? =
Tout compte e-mail supportant l’accès IMAP (Gmail, Outlook, OVH, Gandi, etc.).

= Dois-je laisser le plugin activé en permanence ? =
Oui, le plugin vérifie les nouveaux mails à chaque visite du site pour publier les articles.

= Peut-on limiter la publication à certains expéditeurs ? =
Oui, renseignez dans les réglages les adresses autorisées, ou laissez « * » (tous).

= L’image à la une est-elle forcément la première pièce jointe ? =
Oui, il vous suffit d’ajouter une image en pièce jointe dans votre e-mail : elle sera promue en image à la une de l’article WordPress.

== Support ==

Retrouvez la documentation complète ou posez vos questions sur https://openai.com (ou votre propre site si besoin).
