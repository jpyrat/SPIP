<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2008                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


if (!defined("_ECRIRE_INC_VERSION")) return;


// http://doc.spip.org/@aff_statistique_visites_popularite
function aff_statistique_visites_popularite($serveur, $id_article, &$classement, &$liste){
	$out = "";
	// Par popularite
	$result = sql_select("id_article, titre, popularite, visites", "spip_articles", "statut='publie' AND popularite > 0", "", "popularite DESC",'','',$serveur);

	if (sql_count($result,$serveur)) {
		$out .= "<br />\n";
		$out .= "<div class='iconeoff' style='padding: 5px;'>\n";
		$out .= "<div class='verdana1 spip_x-small'>";
		$out .= typo(_T('info_visites_plus_populaires'));
		$out .= "<ol style='padding-left:40px; font-size:x-small;color:#666666;'>";
		while ($row = sql_fetch($result,$serveur)) {
			$titre = typo(supprime_img($row['titre'],''));
			$l_article = $row['id_article'];
			$visites = $row['visites'];
			$popularite = round($row['popularite']);
			$liste++;
			$classement[$l_article] = $liste;
			
			if ($liste <= 30) {
				$articles_vus[] = $l_article;
			
				if ($l_article == $id_article){
					$out .= "\n<li><b>$titre</b></li>";
				} else {
					$out .= "\n<li><a href='" . generer_url_ecrire("statistiques_visites","id_article=$l_article") . "' title='"._T('info_popularite', array('popularite' => $popularite, 'visites' => $visites))."'>$titre</a></li>";
				}
			}
		}
		$recents = array();
		$q = sql_select("id_article", "spip_articles", "statut='publie' AND popularite > 0", "", "date DESC", "10",'',$serveur);

		while ($r = sql_fetch($q,$serveur))
			if (!in_array($r['id_article'], $articles_vus))
				$recents[]= $r['id_article'];

		if ($recents) {
			$result = sql_select("id_article, titre, popularite, visites", "spip_articles", "statut='publie' AND " . sql_in('id_article', $recents), "", "popularite DESC",'','',$serveur);

			$out .= "</ol><div style='text-align: center'>[...]</div>" . "<ol style='padding-left:40px; font-size:x-small;color:#666666;'>";
			while ($row = sql_fetch($result,$serveur)) {
				$titre = typo(supprime_img($row['titre'], ''));
				$l_article = $row['id_article'];
				$visites = $row['visites'];
				$popularite = round($row['popularite']);
				$numero = $classement[$l_article];
				
				if ($l_article == $id_article){
					$out .= "\n<li><b>$titre</b></li>";
				} else {
					$out .= "\n<li><a href='" . generer_url_ecrire("statistiques_visites","id_article=$l_article") . "' title='"._T('info_popularite_3', array('popularite' => $popularite, 'visites' => $visites))."'>$titre</a></li>";
				}
			}
		}
			
		$out .= "</ol>";

		$out .= "<b>"._T('info_comment_lire_tableau')."</b><br />"._T('texte_comment_lire_tableau');

		$out .= "</div>";
		$out .= "</div>";
	}
	return $out;
}

// http://doc.spip.org/@aff_statistique_visites_par_visites
function aff_statistique_visites_par_visites($serveur='', $id_article=0, $classement= array()) {
	$out = '';
	// Par visites depuis le debut
	$result = sql_select("id_article, titre, popularite, visites", "spip_articles", "statut='publie' AND popularite > 0", "", "visites DESC", "30",'',$serveur);

	$n = sql_count($result,$serveur);
	if ($n) {
		$out .= "<br /><div class='iconeoff' style='padding: 5px;'>";
		$out .= "<div style='overflow:hidden;' class='verdana1 spip_x-small'>";
		$out .= typo(_T('info_affichier_visites_articles_plus_visites'));
		$out .= "<ol style='padding-left:40px; font-size:x-small;color:#666666;'>";

		while ($row = sql_fetch($result,$serveur)) {
			$titre = typo(supprime_img($row['titre'],''));
			$l_article = $row['id_article'];
			$visites = $row['visites'];
			$popularite = round($row['popularite']);
			$numero = $classement[$l_article];
				
			if ($l_article == $id_article){
				$out .= "\n<li><b>$titre</b></li>";
			} else {
				$out .= "\n<li><a href='" . generer_url_ecrire("statistiques_visites","id_article=$l_article") . "'\ntitle='"._T('info_popularite_4', array('popularite' => $popularite, 'visites' => $visites))."'>$titre</a></li>";
				}
		}
		$out .= "</ol>";
		$out .= "</div>";
		$out .= "</div>";
	}
	return $out;
}


// http://doc.spip.org/@http_img_rien
function http_img_rien($width, $height, $class='', $title='') {
	return http_img_pack('rien.gif', $title, 
		"width='$width' height='$height'" 
		. (!$class ? '' : (" class='$class'"))
		. (!$title ? '' : (" title=\"$title\"")));
}


// Donne la hauteur du graphe en fonction de la valeur maximale
// Doit etre un entier "rond", pas trop eloigne du max, et dont
// les graduations (divisions par huit) soient jolies :
// on prend donc le plus proche au-dessus de x de la forme 12,16,20,40,60,80,100
// http://doc.spip.org/@maxgraph
function maxgraph($max) {

	switch ($n =strlen($max)) {
	case 0:
		return 1;
	case 1:
		return 16;
	case 2:
		return (floor($max / 8) + 1) * 8;
	case 3:
		return (floor($max / 80) + 1) * 80;
	default:
		$dix = 2 * pow(10, $n-2);
		return (floor($max / $dix) + 1) * $dix;
	}
}

// http://doc.spip.org/@statistiques_jour_et_mois
function statistiques_jour_et_mois($id_article, $select, $table, $where, $duree, $order, $count, $serveur, $interval, $total, $popularite, $liste='', $classement=array(), $script='')
{
	$where2 = $duree ? "$order > DATE_SUB(NOW(),INTERVAL $duree DAY)": '';
	if ($where) $where2 = $where2 ?  "$where2 AND $where" : $where;
	$log = statistiques_collecte_date($select, "(ROUND(UNIX_TIMESTAMP($order) / $interval) *  $interval)", $table, $where2, $serveur);

	if (!$log) return array('','');

	$d = sql_getfetsel("UNIX_TIMESTAMP($order) AS d", $table, $where, '', $order, 1,'',$serveur);
	$last = 0;
	$res = debut_cadre_relief("statistiques-24.gif", true)
	  . statistiques_tous($log,$d, $last, $total, $popularite, $duree, $interval, $classement, $id_article, $liste, $script)
	. fin_cadre_relief(true)
	. statistiques_mode($table);
	
	if (count($log) < 20) return array($res,  '');

	$mois = statistiques_collecte_date($count,
		"FROM_UNIXTIME(UNIX_TIMESTAMP($order),'%Y-%m')", 
		$table,
		"$order > DATE_SUB(NOW(),INTERVAL 2700 DAY)"
		. ($where ? " AND $where" : ''),
		$serveur);
	return array($res, statistiques_par_mois($mois, $last, $script));
}

// http://doc.spip.org/@statistiques_collecte_date
function statistiques_collecte_date($count, $date, $table, $where, $serveur)
{
	$result = sql_select("$count AS n, $date AS d", $table, $where, 'd', 'd', '','', $serveur);
	$log = array();

	while ($r = sql_fetch($result,$serveur)) {
		if ($r['d'] AND  isset($log[$r['d']]))
			$log[$r['d']] += $r['n'];
		else	$log[$r['d']] = $r['n'];
	}
	return $log;
}

// Appelee S'il y a au moins cinq minutes de stats :-)

// http://doc.spip.org/@statistiques_tous
function statistiques_tous($log, $date_premier, $last, $total_absolu, $val_popularite, $aff_jours, $interval, $classement, $id_article=0, $liste=0, $script='')
{
	$r = array_keys($log);
	$date_today = max($r);
	$date_debut = min($r);
	$last = (time()-$date_today>$interval) ? 0 : $log[$date_today];
	$max = max($log);
	$maxgraph = maxgraph($max);
	$rapport = 200 / $maxgraph;
	$agreg = ceil(count($log) / 420);
	$largeur = ($agreg > 1) ? 1 :
	  floor(450 / (1+floor(($date_today-$date_debut)/$interval)));
	if ($largeur > 50) $largeur = 50; elseif ($largeur < 1) $largeur = 1;

	list($moyenne,$val_prec, $res) = stat_log1($log, $agreg, $date_today, $largeur, $rapport, $interval, $script);
	$res = statistiques_hauteur($res, $id_article, $largeur, $maxgraph, $moyenne, $rapport, $val_popularite, $last)
	  . statistiques_nom_des_mois($date_debut, $date_today, ($largeur / ($interval*$agreg)));

	$x = (!$aff_jours) ? 1 : (420/ $aff_jours);
	$res = statistiques_zoom($id_article, $x, $date_premier, $date_debut, $date_today) . $res;

	// cette ligne donne la moyenne depuis le debut
	// (desactive au profit de la moyenne "glissante")
	# $moyenne =  round($total_absolu / ((date("U")-$date_premier)/(3600*24)));
	$res .= "<span class='arial1 spip_x-small'>"
	. _T('texte_statistiques_visites')
	. "</span><br />"
	. "<table cellpadding='0' cellspacing='0' border='0' width='100%'><tr style='width:100%;'>"
	. "\n<td valign='top' style='width: 33%; ' class='verdana1'>"
	. _T('info_maximum')." "
	. $max . "<br />"
	. _T('info_moyenne')." "
	. round($moyenne). "</td>"
	. "\n<td valign='top' style='width: 33%; ' class='verdana1'>"
	. '<a href="'
	. generer_url_ecrire("statistiques_referers","")
	. '" title="'._T('titre_liens_entrants').'">'
	. _T('info_aujourdhui')
	. '</a> '
	. $last;

	if ($val_prec > 0)
		$res .= '<br /><a href="' . generer_url_ecrire("statistiques_referers","jour=veille").'"  title="'._T('titre_liens_entrants').'">'._T('info_hier').'</a> '.$val_prec;
	if ($id_article AND $val_popularite)
		$res .= "<br />"._T('info_popularite_5').' '.$val_popularite;

	$res .= "</td>"
	. "\n<td valign='top' style='width: 33%; ' class='verdana1'>"
	. "<b>"
	. _T('info_total')." "
	. $total_absolu."</b>";
	
	if ($id_article AND $liste) {
		if ($classement[$id_article] > 0) {
			if ($classement[$id_article] == 1)
			      $ch = _T('info_classement_1', array('liste' => $liste));
			else
			      $ch = _T('info_classement_2', array('liste' => $liste));
			$res .= "<br />".$classement[$id_article].$ch;
		}
	} elseif ($liste) {// i.e; pas 'spip_signatures'
		$res .= "<span class='spip_x-small'><br />"
		  ._T('info_popularite_2')." "
		  . ceil($GLOBALS['meta']['popularite_total'])
		  . "</span>";
	}
	$res .= "</td></tr></table>";	

	return $res;
}

// http://doc.spip.org/@statistiques_zoom
function statistiques_zoom($id_article, $largeur_abs, $date_premier, $date_debut, $date_today)
{
	if ($largeur_abs > 1) {
		$inc = ceil($largeur_abs / 5);
		$aff_jours_plus = 420 / ($largeur_abs - $inc);
		$aff_jours_moins = 420 / ($largeur_abs + $inc);
	}
	
	if ($largeur_abs == 1) {
		$aff_jours_plus = 840;
		$aff_jours_moins = 210;
	}
	
	if ($largeur_abs < 1) {
		$aff_jours_plus = 420 * ((1/$largeur_abs) + 1);
		$aff_jours_moins = 420 * ((1/$largeur_abs) - 1);
	}
	
	$pour_article = $id_article ? "&id_article=$id_article" : '';

	$zoom = '';

	if ($date_premier < $date_debut)
		$zoom= http_href(generer_url_ecrire("statistiques_visites","aff_jours=$aff_jours_plus$pour_article"),
			 http_img_pack('loupe-moins.gif',
				       _T('info_zoom'). '-', 
				       "style='border: 0px; vertical-align: middle;'"),
			 "&nbsp;");
	if ( (($date_today - $date_debut) / (24*3600)) > 30)
		$zoom .= http_href(generer_url_ecrire("statistiques_visites","aff_jours=$aff_jours_moins$pour_article"), 
			 http_img_pack('loupe-plus.gif',
				       _T('info_zoom'). '+', 
				       "style='border: 0px; vertical-align: middle;'"),
			 "&nbsp;");

	return $zoom;
}

// Presentation graphique (rq: on n'affiche pas le jour courant)
// http://doc.spip.org/@stat_log1
function stat_log1($log, $agreg, $date_today, $largeur, $rapport, $interval, $script) {
	$res = '';
	$rien = http_img_rien($largeur, 1, 'trait_bas', '');
	$test_agreg = $decal = $jour_prec = $val_prec = $moyenne = 0;
	foreach ($log as $key => $value) {
		if ($key == $date_today) break; 
		$test_agreg ++;
		if ($test_agreg != $agreg) continue;
		$test_agreg = 0;
		if ($decal == 30) $decal = 0;
		$decal ++;
		$evol[$decal] = $value;
		// Inserer des jours vides si pas d'entrees	
		if ($jour_prec > 0) {
			$ecart = (($key-$jour_prec)/$agreg)-$interval;
			for ($i=$interval; $i <= $ecart; $i+=$interval){
				if ($decal == 30) $decal = 0;
				$decal ++;
				$evol[$decal] = $value;
				$m = statistiques_moyenne($evol);
				$m = statistiques_vides($jour_prec+$i, $largeur, $rapport, $m, $script);
				$res .= "\n<td style='width: ${largeur}px'>$m</td>";
			}
		}
		$moyenne = round(statistiques_moyenne($evol),2);
		$hauteur = round($value * $rapport) - 1;
		$m = ($hauteur <= 0) ? '' : statistiques_jour($key, $value, $largeur, $moyenne, $hauteur, $rapport, $script);
		$res .= "\n<td style='width: ${largeur}px'>$m$rien</td>\n";
		$jour_prec = $key;
		$val_prec = $value;
	}
	return array($moyenne, $val_prec, $res);
}

// http://doc.spip.org/@statistiques_href
function statistiques_href($jour, $moyenne, $script, $value='')
{
	$ce_jour=date("Y-m-d", $jour);
	$title = nom_jour($ce_jour).' '.affdate_jourcourt($ce_jour)
	  . ($script ? '' : (" | "
				._T('info_visites')." $value | "
				._T('info_moyenne')." "
			     . round($moyenne,2)));
	return attribut_html(supprimer_tags($title));
}

// http://doc.spip.org/@statistiques_vides
function statistiques_vides($prec, $largeur, $rapport, $moyenne, $script)
{
	$hauteur_moyenne = round($moyenne*$rapport)-1;
	$title = statistiques_href($prec, $moyenne, $script);
	$tagtitle = $script ? '' : $title;
	if ($hauteur_moyenne > 1) {
		$res = http_img_rien($largeur,1, 'trait_moyen', $tagtitle)
		. http_img_rien($largeur, $hauteur_moyenne, '', $tagtitle);
	} else $res = '';
	$res .= http_img_rien($largeur,1,'trait_bas', $tagtitle);
	if (!$script) return $res;
	return "<a href='$script' title='$title'>$res</a>";
}

// http://doc.spip.org/@statistiques_hauteur
function statistiques_hauteur($res, $id_article, $largeur, $maxgraph, $moyenne, $rapport, $val_popularite, $visites_today)
{
	$hauteur = round($visites_today * $rapport) - 1;
	// $total_absolu = $total_absolu + $visites_today;
	// prevision de visites jusqu'a minuit
	// basee sur la moyenne (site) ou popularite (article)
	if (! $id_article) $val_popularite = $moyenne;
	$prevision = (1 - (date("H")*60 + date("i"))/(24*60)) * $val_popularite;
	$hauteurprevision = ceil($prevision * $rapport);
	// preparer le texte de survol (prevision)
	$tagtitle= attribut_html(supprimer_tags(_T('info_aujourdhui')." $visites_today &rarr; ".(round($prevision,0)+$visites_today)));

	$res .= "\n<td style='width: ${largeur}px'>";
	if ($hauteur+$hauteurprevision>0)
	// Afficher la barre tout en haut
		$res .= http_img_rien($largeur, 1, "trait_haut");
	if ($hauteurprevision>0)
	// afficher la barre previsionnelle
		$res .= http_img_rien($largeur, $hauteurprevision,'couleur_prevision', $tagtitle);
	// afficher la barre deja realisee
	if ($hauteur>0)
		$res .= http_img_rien($largeur, $hauteur, 'couleur_realise', $tagtitle);
	// et afficher la ligne de base
	$res .= http_img_rien($largeur, 1, 'trait_bas')
	. "</td>";
	
	return "\n<table cellpadding='0' cellspacing='0' border='0'><tr>" .
	  "\n<td ".http_style_background("fond-stats.gif").">"
	. "\n<table cellpadding='0' cellspacing='0' border='0' class='bottom'><tr>"
	. "\n<td style='background-color: black'>" . http_img_rien(1, 200) . "</td>"
	. $res 

	. "\n<td style='background-color: black'>" .http_img_rien(1, 1) ."</td>"
	. "</tr></table>"
	. "</td>" 
	. "\n<td ".http_style_background("fond-stats.gif")."  valign='bottom'>" . http_img_rien(3, 1, 'trait_bas') ."</td>"
	. "\n<td>" . http_img_rien(5, 1) ."</td>" 
	. "\n<td valign='top'>"
	. statistiques_echelle($maxgraph) 
	. "</td>"  
	. "</tr></table>";
}
// http://doc.spip.org/@statistiques_jour
function statistiques_jour($key, $value, $largeur, $moyenne, $hauteur, $rapport, $script) 
{
	$hauteur_moyenne = round($moyenne * $rapport) - 1;
	$title= statistiques_href($key, $moyenne, $script, $value);
	$tagtitle = $script ? '' : $title;
	if ($hauteur_moyenne > $hauteur) {
		$difference = ($hauteur_moyenne - $hauteur) -1;
		$res = http_img_rien($largeur, 1,'trait_moyen',$tagtitle)
		. http_img_rien($largeur, $difference, '', $tagtitle)
		. http_img_rien($largeur,1, "trait_haut", $tagtitle);
		if (date("w",$key) == "0") // Dimanche en couleur foncee
$res .= http_img_rien($largeur, $hauteur, "couleur_dimanche", $tagtitle);
		else
		  $res .= http_img_rien($largeur,$hauteur, "couleur_jour", $tagtitle);
	} else if ($hauteur_moyenne < $hauteur) {
		$difference = ($hauteur - $hauteur_moyenne) -1;
		$res = http_img_rien($largeur,1,"trait_haut", $tagtitle);
		if (date("w",$key) == "0") // Dimanche en couleur foncee
		  $couleur =  'couleur_dimanche';
		else
		  $couleur = 'couleur_jour';
		$res .= http_img_rien($largeur, $difference, $couleur, $tagtitle)
		. http_img_rien($largeur,1,"trait_moyen", $tagtitle)
		. http_img_rien($largeur, $hauteur_moyenne, $couleur, $tagtitle);
	} else {
		  $res = http_img_rien($largeur, 1, "trait_haut", $tagtitle);
		  if (date("w",$key) == "0") // Dimanche en couleur foncee
		    $res .= http_img_rien($largeur, $hauteur, "couleur_dimanche", $tagtitle);
		  else
		    $res .= http_img_rien($largeur,$hauteur, "couleur_jour", $tagtitle);
	}
	if (!$script) return $res;
	return "<a href='$script&amp;date=$key' title='$title'>$res</a>";
}

// http://doc.spip.org/@statistiques_nom_des_mois
function statistiques_nom_des_mois($date_debut, $date_today, $largeur)
{
	global $spip_lang_left;

	$res = '';
	$gauche_prec = -50;
	$pas =  (24*3600);
	for ($jour = $date_debut; $jour <= $date_today; $jour += $pas) {
		if (date("d", $jour) == "1") {
			$newy = (date("m", $jour) == 1);
			$gauche = floor(($jour - $date_debut) * $largeur);
			if ($gauche - $gauche_prec >= 40 OR $newy) {
				$afficher = $newy ? 
				  ("<b>".annee(date("Y-m-d", $jour))."</b>")
				  : nom_mois(date("Y-m-d", $jour));

				  $res .= "<div class='arial0' style='border-$spip_lang_left: 1px solid black; padding-$spip_lang_left: 2px; padding-top: 3px; position: absolute; $spip_lang_left: ".$gauche."px; top: -1px;'>".$afficher."</div>";
				$gauche_prec = $gauche;
				if ($gauche > 400) break; //400px max
			}
		}
	}
	return "<div style='position: relative; height: 15px'>$res</div>";  
}

// http://doc.spip.org/@statistiques_par_mois
function statistiques_par_mois($entrees, $visites_today, $script){

	// rajouter les visites du jour
	@$entrees[date("Y-m",time())] += $visites_today;
		
	$maxgraph = maxgraph(max($entrees));
	$rapport = 200/$maxgraph;
	$largeur = floor(420 / (count($entrees)));
	if ($largeur < 1) $largeur = 1;
	if ($largeur > 50) $largeur = 50;
	$decal = 0;
	$tab_moyenne = array();

	$table = ''
	. "\n<table cellpadding='0' cellspacing='0' border='0'><tr>" .
		  "\n<td ".http_style_background("fond-stats.gif").">"
	. "\n<table cellpadding='0' cellspacing='0' border='0' class='bottom'><tr>"
	. "\n<td class='trait_bas'>" . http_img_rien(1, 200) ."</td>";
			
	while (list($key, $value) = each($entrees)) {
		$mois = affdate_mois_annee($key);
		if ($decal == 30) $decal = 0;
		$decal ++;
		$tab_moyenne[$decal] = $value;
		$moyenne = statistiques_moyenne($tab_moyenne);
		$hauteur_moyenne = round($moyenne * $rapport) - 1;
		$hauteur = round($value * $rapport) - 1;
		$res = '';
		$title= attribut_html(supprimer_tags("$mois | "
			._T('info_total')." ".$value));
		$tagtitle = $script ? '' : $title;
		if ($hauteur > 0){
			if ($hauteur_moyenne > $hauteur) {
				$difference = ($hauteur_moyenne - $hauteur) -1;
				$res .= http_img_rien($largeur, 1, 'trait_moyen');
				$res .= http_img_rien($largeur, $difference, '', $tagtitle);
				$res .= http_img_rien($largeur,1,"trait_haut");
				if (preg_match(",-01,",$key)){ // janvier en couleur foncee
					$res .= http_img_rien($largeur,$hauteur,"couleur_janvier", $tagtitle);
				} else {
					$res .= http_img_rien($largeur,$hauteur,"couleur_mois", $tagtitle);
				}
			}
			else if ($hauteur_moyenne < $hauteur) {
				$difference = ($hauteur - $hauteur_moyenne) -1;
				$res .= http_img_rien($largeur,1,"trait_haut", $tagtitle);
				if (preg_match(",-01,",$key)){ // janvier en couleur foncee
						$couleur =  'couleur_janvier';
				} else {
						$couleur = 'couleur_mois';
				}
				$res .= http_img_rien($largeur,$difference, $couleur, $tagtitle);
				$res .= http_img_rien($largeur,1,'trait_moyen',$tagtitle);
				$res .= http_img_rien($largeur,$hauteur_moyenne, $couleur, $tagtitle);
			} else {
				$res .= http_img_rien($largeur,1,"trait_haut", $tagtitle);
				if (preg_match(",-01,",$key)){ // janvier en couleur foncee
					$res .= http_img_rien($largeur, $hauteur, "couleur_janvier", $tagtitle);
				} else {
					$res .= http_img_rien($largeur,$hauteur, "couleur_mois", $tagtitle);
				}
			}
		}
		if ($script) 
			$res = "<a href='$script&amp;date=$key' title='$title'>$res</a>";
		$table .= "\n<td style='width: ${largeur}px'>"
		  . $res 
		  . http_img_rien($largeur,1,'trait_bas', $tagtitle)
		  ."</td>\n";
	}
	
	return $table
	. "\n<td style='background-color: black'>" . http_img_rien(1, 1) . "</td>"
	. "</tr></table></td>"
	. "\n<td ".http_style_background("fond-stats.gif")." valign='bottom'>"
	. http_img_rien(3, 1, 'trait_bas') ."</td>"
	. "\n<td>" . http_img_rien(5, 1) ."</td>"
	. "\n<td valign='top'>"
	. statistiques_echelle($maxgraph)
	. "</td></tr></table>";
 }

// http://doc.spip.org/@statistiques_echelle
function statistiques_echelle($maxgraph)
{
  include_spip('public/assembler');
  return recuperer_fond('prive/stats/echelle', array('echelle' => $maxgraph));
}

// http://doc.spip.org/@statistiques_moyenne
function statistiques_moyenne($tab)
{
	if (!$tab) return 0;
	$moyenne = 0;
	foreach($tab as $v) $moyenne += $v;
	return  $moyenne / count($tab);
}


// http://doc.spip.org/@statistiques_signatures
function statistiques_signatures($aff_jours, $id_article, $serveur)
{
	$total = sql_countsel("spip_signatures", "id_article=$id_article");
	if (!$total) return '';
	$script = generer_url_ecrire('controle_petition', "id_article=$id_article");
	list($res, $mois) = statistiques_jour_et_mois($id_article, "COUNT(*)", "spip_signatures", "id_article=$id_article", $aff_jours, "date_time", "COUNT(*)", $serveur, 3600*24, $total, 0, '', array(), $script);

	return "<br />"
	. gros_titre(_T('titre_page_statistiques_signatures_jour'),'', false)
	. $res
	. (!$mois ? '' : (
	  "<br />"
	. gros_titre(_T('titre_page_statistiques_signatures_mois'),'', false)
	. $mois));
}

// http://doc.spip.org/@statistiques_forums
function statistiques_forums($aff_jours, $id_article, $serveur)
{

	$total = sql_countsel("spip_forum", "id_article=$id_article");
	if (!$total) return '';
	$script = generer_url_ecrire('articles_forum', "id_article=$id_article");
	list($res, $mois) = statistiques_jour_et_mois($id_article, "COUNT(*)", "spip_forum", "id_article=$id_article", $aff_jours, "date_heure", "COUNT(*)", $serveur, 3600*24, $total, 0, '', array(), $script);

	return "<br />"
	. gros_titre(_L('Messages de forum par jour'),'', false)
	. $res
	. (!$mois ? '' : (
	  "<br />"
	. gros_titre(_L('Messages de forum par mois'),'', false)
	. $mois));
}

// Le bouton pour CSV et pour passer de svg a htm

// http://doc.spip.org/@statistiques_mode
function statistiques_mode($table)
{

	$lui = self();
	$csv = parametre_url(parametre_url($lui, 'table', $table), 'format', 'csv');

	return "\n<div style='text-align:".$GLOBALS['spip_lang_right'] . ";' class='verdana1 spip_x-small'>"
		. "<a href='"
		. $csv
	  	. "'>CSV</a>"
		. "</div>\n";
}
?>