<?php 
require "connexion.php"; 
 
$requete = $connexion->query("SELECT * FROM administrateur"); 
 
$utilisateurs = $requete->fetchAll(); 
?> 
 
<h2>Liste des utilisateurs</h2> 
 
<a href="ajouter.php">Ajouter un utilisateur</a> 
 
<table border="1"> 
 
<tr> 
<th>ID</th> 
<th>Nom</th> 
<th>Prénom</th> 
<th>Email</th> 
<th>Actions</th> 
</tr> 
 
<?php foreach($utilisateurs as $user){ ?> 
 
<tr> 
 
<td><?php echo $user['idAdmin']; ?></td> 
<td><?php echo $user['nom']; ?></td> 
<td><?php echo $user['prenom']; ?></td> 
<td><?php echo $user['email']; ?></td> 
 
<td> 

 
</td> 
 
</tr> 
 
<?php } ?> 
 
</table>