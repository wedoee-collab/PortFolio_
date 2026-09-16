<?php
function affichetab($tab,$m,$n){
		for($i=0;$i<$m;$i++){
			for($j=0;$j<$n;$j++){
				echo $tab[$i][$j]." ";
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
	$grille[$l][$c]=1;
}


$grilleJ1=initialisegrille();
affichetab($grilleJ1,10,10);
tire(2,2,$grilleJ1);
affichetab($grilleJ1,10,10);

function positionnetorpilleur(&$grille){

    $l=int(readline("Sur quelle ligne voulez vous positionner le torpilleur ? "));
    $c=int(readline("Sur quelle colonne voulez vous positionner le torpilleur ? "));
    $position=readline("voulez-vous postionner le torpilleur en horizontal ? (o/n) "));
    if($position=="o"){
        if($grille[$l][$c]==0){
			$grille[$l][$c]=1;        
        }
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

?>