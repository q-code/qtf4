<h2>Introduction</h2>

<div style="padding:10px 0 10px 15px">
<p>Les <b>tags</b> sont des marqueurs personnels que les utilisateurs peuvent attacher aux sujets. Ces tags sont utilisés pour grouper les sujets ou générer des statistiques particulières. Les tags que l'utilisateur peut ajouter aux sujets sont libres. Cependant, si une liste de tags est définie, le system va présenter ces <b>tags proposés</b> dans un menu déroulant.</p>
<p>La liste des ces tags proposés peut être créée par forum (et par langue). Ces listes sont de simples fichiers <b>csv</b> sockés dans le répertoire /upload/.</p>
</div>

<h2>Fichier CSV</h2>

<div style="padding:10px 0 10px 15px">
<p>Une liste de tags est un simple fichier texte contenant un tag par ligne.<br>
Vous pouvez aussi ajouter une courte descrition (après un point virgule).</p>
<p>ATTENTION: Un tag ne peut pas comporter de guillemets ["]. Si vous utilisez des accents, assurez-vous que votre fichier est codé en utf-8.</p>
<p>Exemple:</p>
<p style="color:#139613">
International<br>
National<br>
Local;Arrondissement ou commune
</p>
</div>

<h2>Pour créer une liste commune à tous les forums</h2>

<div style="padding:10px 0 10px 15px">
<p>
1 - Créer un fichier .csv nommé <b>tags_fr.csv</b><br>
2 - Uploadez votre fichier en utilisant la page d'administration (ou par ftp)
</p>
<p class="small">Note: _fr signifie français, vous pouvez créer des listes dans d'autres langues: tags_en.csv, tags_nl.csv, ...</p>
</div>

<h2>Pour créer une liste spécifique à un forum</h2>

<div style="padding:10px 0 10px 15px">
<p>
1 - Créer un fichier .csv nommé <b>tags_fr_0.csv</b> où "0" est l'id de la section.<br>
2 - Uploadez votre fichier en utilisant la page d'administration (ou par ftp)
</p>
<p class="small">Note: _fr signifie français, vous pouvez créer des listes dans d'autres langues: tags_en_0.csv, tags_nl_0.csv, ...</p>
</div>
