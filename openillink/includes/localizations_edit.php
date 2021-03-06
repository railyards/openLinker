﻿<?php
// ***************************************************************************
// ***************************************************************************
// ***************************************************************************
// OpenLinker is a web based library system designed to manage 
// journals, ILL, document delivery and OpenURL links
// 
// Copyright (C) 2012, Pablo Iriarte
// 
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// 
// ***************************************************************************
// ***************************************************************************
// ***************************************************************************
// Localizations table : edit form
// 11.03.2016, MDV Replaced connector to db from mysql_ to mysqli_
// 21.03.2016, MDV Input reading verification; suppressed reference to unknown file menurech.php
// 
require_once ("config.php");
require_once ("authcookie.php");
require_once ("connexion.php");
require_once ("includes/toolkit.php");

$montitle = "Gestion des localisations";
$id= ((!empty($_GET['id'])) && isValidInput($_GET['id'],11,'s',false)) ? $_GET['id'] : "";
if (!empty($_COOKIE[illinkid])){
    if (($monaut == "admin")||($monaut == "sadmin")){
        if ($id!=""){
            $myhtmltitle = $configname[$lang] . " : édition de la localisation " . $id;
            $montitle = "Gestion des localisations : édition de la fiche " . $id;
            require ("headeradmin.php");
            $req = "SELECT * FROM localizations WHERE id = ?";
            $result = dbquery($req, array($id), 'i');
            $nb = iimysqli_num_rows($result);
            if ($nb == 1){
                echo "<h1>" . $montitle . "</h1>\n";
                echo "<br /></b>";
                echo "<ul>\n";
                $enreg = iimysqli_result_fetch_array($result);
                $locid = $enreg['id'];
                $loccode = $enreg['code'];
                $loclibrarary = $enreg['library'];
                $name["fr"] = $enreg['name1'];
                $name["en"] = $enreg['name2'];
                $name["de"] = $enreg['name3'];
                $name["it"] = $enreg['name4'];
                $name["es"] = $enreg['name5'];
                echo "<form action=\"update.php\" method=\"POST\" enctype=\"x-www-form-encoded\" name=\"fiche\" id=\"fiche\">\n";
                echo "<input name=\"table\" type=\"hidden\" value=\"localizations\">\n";
                echo "<input name=\"id\" type=\"hidden\" value=\"".$locid."\">\n";
                echo "<input name=\"action\" type=\"hidden\" value=\"update\">\n";
                echo "<table id=\"hor-zebra\">\n";
                echo "<tr><td></td><td><input type=\"submit\" value=\"Enregistrer les modifications\">\n";
                echo "&nbsp;&nbsp;<input type=\"button\" value=\"Annuler\" onClick=\"self.location='list.php?table=localizations'\">\n";
                echo "&nbsp;&nbsp;<input type=\"button\" value=\"Supprimer\" onClick=\"self.location='update.php?action=delete&table=localizations&id=" . $locid . "'\"></td></tr>\n";
                echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
                echo "<tr><td><b>Code *</b></td><td>\n";
                echo "<input name=\"code\" type=\"text\" size=\"60\" value=\"" . $loccode . "\"></td></tr>\n";
                echo "</td></tr>\n";
                echo "<tr><td class=\"odd\"><b>".$guiLabelName1[$lang]." *</b></td><td class=\"odd\"><input name=\"name1\" type=\"text\" size=\"60\" value=\"" . $name["fr"] . "\"></td></tr>\n";
                echo "<tr><td><b>".$guiLabelName2[$lang]."</b></td><td><input name=\"name2\" type=\"text\" size=\"60\" value=\"" . $name["en"] . "\"></td></tr>\n";
                echo "<tr><td class=\"odd\"><b>".$guiLabelName3[$lang]."</b></td><td class=\"odd\"><input name=\"name3\" type=\"text\" size=\"60\" value=\"" . $name["de"] . "\"></td></tr>\n";
                echo "<tr><td><b>".$guiLabelName4[$lang]."</b></td><td><input name=\"name4\" type=\"text\" size=\"60\" value=\"" . $name["it"] . "\"></td></tr>\n";
                echo "<tr><td class=\"odd\"><b>".$guiLabelName5[$lang]."</b></td><td class=\"odd\"><input name=\"name5\" type=\"text\" size=\"60\" value=\"" . $name["es"] . "\"></td></tr>\n";
                echo "<tr><td><b>Bibliothèque d'attribution</b></td><td>\n";
                echo "<select name=\"library\">\n";
                $reqlibraries="SELECT code, name1, name2, name3, name4, name5 FROM libraries ORDER BY name1 ASC";
                $optionslibraries="";
                $resultlibraries = dbquery($reqlibraries);
                $nblibs = iimysqli_num_rows($resultlibraries);
                if ($nblibs > 0){
                    while ($rowlibraries = iimysqli_result_fetch_array($resultlibraries)){
                        $codelibraries = $rowlibraries["code"];
                        $namelibraries["fr"] = $rowlibraries["name1"];
                        $namelibraries["en"] = $rowlibraries["name2"];
                        $namelibraries["de"] = $rowlibraries["name3"];
                        $namelibraries["it"] = $rowlibraries["name4"];
                        $namelibraries["es"] = $rowlibraries["name5"];
                        $optionslibraries.="<option value=\"" . $codelibraries . "\"";
                        if ($loclibrarary == $codelibraries)
                            $optionslibraries.=" selected";
                        $optionslibraries.=">" . $namelibraries[$lang] . "</option>\n";
                    }
                    echo $optionslibraries;
                }
                echo "</select></td></tr>\n";
                echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
                echo "<tr><td></td><td><input type=\"submit\" value=\"Enregistrer les modifications\">\n";
                echo "&nbsp;&nbsp;<input type=\"button\" value=\"Annuler\" onClick=\"self.location='list.php?table=localizations'\">\n";
                echo "&nbsp;&nbsp;<input type=\"button\" value=\"Supprimer\" onClick=\"self.location='update.php?action=delete&table=localizations&id=" . $locid . "'\"></td></tr>\n";
                echo "</table>\n";
                echo "</form><br /><br />\n";
                require ("footer.php");
            }
            else{
                echo "<center><br/><b><font color=\"red\">\n";
                echo "La fiche " . $id . " n'a pas été trouvée dans la base.</b></font>\n";
                echo "<br /><br /><b>Veuillez relancer de nouveau votre recherche ou contactez l'administrateur de la base : " . $configemail . "</b></center><br /><br /><br /><br />\n";
                require ("footer.php");
            }
        }
        else{
            require ("header.php");
            //require ("menurech.php");
            echo "<center><br/><b><font color=\"red\">\n";
            echo "La fiche n'a pas été trouvée dans la base.</b></font>\n";
            echo "<br /><br /><b>Veuillez relancer de nouveau votre recherche ou contactez l'administrateur de la base : " . $configemail . "</b></center><br /><br /><br /><br />\n";
            echo "<br /><br />\n";
            echo "</ul>\n";
            echo "\n";
            require ("footer.php");
        }
    }
    else{
        require ("header.php");
        echo "<center><br/><b><font color=\"red\">\n";
        echo "Vos droits sont insuffisants pour éditer cette fiche</b></font></center><br /><br /><br /><br />\n";
        require ("footer.php");
    }
}
else{
    require ("header.php");
    require ("loginfail.php");
    require ("footer.php");
}
?>
