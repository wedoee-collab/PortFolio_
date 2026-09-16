<?php

function affichetab($tab,$m,$n){
    for($i=0;$i<$m;$i++){
        for($j=0;$j<$n;$j++){
            if($tab[$i][$j] == 1) echo "O ";
            else if($tab[$i][$j] == 3) echo "X ";
            else echo ". ";
        }
        echo "\n";
    }
    echo "------------\n";
}

function initialisegrille(){
    for($i=0;$i<10;$i++){
        for($j=0;$j<10;$j++){
            $grille[$i][$j]=0;
        }
    }
    return $grille;
}

function tire($l,$c,&$grille){
    if($grille[$l][$c] == 2){
        echo "Touché !\n";
        $grille[$l][$c] = 3;
    }
    else if($grille[$l][$c] == 0){
        echo "Manqué.\n";
        $grille[$l][$c] = 1;
    }
    else {
        echo "Vous avez déjà tiré ici.\n";
    }
}

function positionnetorpilleur(&$grille){
    $l = (int) readline("Sur quelle ligne voulez vous positionner le torpilleur ? ");
    $c = (int) readline("Sur quelle colonne voulez vous positionner le torpilleur ? ");
    $position = readline("voulez-vous positionner le torpilleur en horizontal ? (o/n) ");
    if($position == "o"){
        if($c+1 < 10 && $grille[$l][$c] == 0 && $grille[$l][$c+1] == 0){
            $grille[$l][$c] = 2;
            $grille[$l][$c+1] = 2;
        }
    } else {
        if($l+1 < 10 && $grille[$l][$c] == 0 && $grille[$l+1][$c] == 0){
            $grille[$l][$c] = 2;
            $grille[$l+1][$c] = 2;
        }
    }
}

$grilleJ1=initialisegrille();
$tab[0][0]=1;
$tab[0][1]=2;
$tab[1][0]=3;
$tab[1][1]=4;
affichetab($grilleJ1,10,10);
tire(2,2,$grilleJ1);
affichetab($grilleJ1,10,10);


function peutPlacer($grille,$l,$c,$taille,$orientation){
    if($orientation == "o"){
        if($c + $taille > 10) return false;
        for($i=0;$i<$taille;$i++){
            if($grille[$l][$c+$i] != 0) return false;
        }
    } else {
        if($l + $taille > 10) return false;
        for($i=0;$i<$taille;$i++){
            if($grille[$l+$i][$c] != 0) return false;
        }
    }
    return true;
}

function poserBateau(&$grille,$l,$c,$taille,$orientation){
    if($orientation == "o"){
        for($i=0;$i<$taille;$i++){
            $grille[$l][$c+$i] = 2;
        }
    } else {
        for($i=0;$i<$taille;$i++){
            $grille[$l+$i][$c] = 2;
        }
    }
}

function placeBateau(&$grille,$taille,$nom,$joueur){
    echo "\nJoueur $joueur – Placez le $nom ($taille cases)\n";
    while(true){
        $l = (int) readline("Ligne départ : ");
        $c = (int) readline("Colonne départ : ");
        $orientation = readline("Horizontal ? (o/n) : ");
        if(peutPlacer($grille,$l,$c,$taille,$orientation)){
            poserBateau($grille,$l,$c,$taille,$orientation);
            echo "$nom placé.\n";
            break;
        } else {
            echo "Placement impossible, réessayez.\n";
        }
    }
}

function tousBateauxCoulés($grille){
    for($i=0;$i<10;$i++){
        for($j=0;$j<10;$j++){
            if($grille[$i][$j] == 2) return false;
        }
    }
    return true;
}

$bateaux = [
    ["Porte-avions",5],
    ["Croiseur",4],
    ["Contre-torpilleur",3],
    ["Sous-marin",3],
    ["Torpilleur",2]
];

$grilleJ1 = initialisegrille();
$grilleJ2 = initialisegrille();

echo "\n=== Placement Joueur 1 ===\n";
foreach($bateaux as $b){
    if($b[0] == "Torpilleur"){
        positionnetorpilleur($grilleJ1);
    } else {
        placeBateau($grilleJ1,$b[1],$b[0],1);
    }
}

echo "\n=== Placement Joueur 2 ===\n";
foreach($bateaux as $b){
    if($b[0] == "Torpilleur"){
        positionnetorpilleur($grilleJ2);
    } else {
        placeBateau($grilleJ2,$b[1],$b[0],2);
    }
}

echo "\n=== Début de la partie ===\n";

$joueur = 1;

while(true){
    echo "\n--- Tour du joueur $joueur ---\n";
    if($joueur == 1){
        affichetab($grilleJ2,10,10);
        $l = (int) readline("Ligne tir : ");
        $c = (int) readline("Colonne tir : ");
        tire($l,$c,$grilleJ2);
        if(tousBateauxCoulés($grilleJ2)){
            echo "Joueur 1 gagne !\n";
            break;
        }
        $joueur = 2;
    } else {
        affichetab($grilleJ1,10,10);
        $l = (int) readline("Ligne tir : ");
        $c = (int) readline("Colonne tir : ");
        tire($l,$c,$grilleJ1);
        if(tousBateauxCoulés($grilleJ1)){
            echo "Joueur 2 gagne !\n";
            break;
        }
        $joueur = 1;
    }
}

echo "\n=== FIN DU JEU ===\n";

?>
