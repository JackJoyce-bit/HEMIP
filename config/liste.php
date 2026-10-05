<h2>Liste des utilisateurs</h2> 
<a href="index.php?action=ajouter">Ajouter</a> 
<?php foreach($utilisateurs as $user){ ?> 
<p> 
<?php echo $user['nom']; echo $user['prenom']; ?> 
<a href="index.php?action=modifier&id=<?php echo $user['id']; ?>">Modifier</a> 
<a href="index.php?action=supprimer&id=<?php echo $user['id']; ?>">Supprimer</a> 
</p> 
<?php } ?>