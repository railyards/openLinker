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
// Login form
// 18.03.2016, MDV Replaced connector to db from mysql_ to mysqli_
// 01.04.2016, MDV added input validation
//
require_once ("includes/config.php");
require_once ("includes/authcookie.php");
require_once ("includes/connexion.php");
require_once ("includes/toolkit.php");

$logok=0;
$monhost = "http://" . $_SERVER['SERVER_NAME'];
$monuri = $monhost . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
$rediradmin = "Location: " . $monuri . "list.php?folder=in";
$rediruser = "Location: " . $monuri . "list.php?folder=in";
$redirguest = "Location: " . $monuri . "list.php?folder=guest";

$validActionSet = array('logout', 'shibboleth');
$action = addslashes(isValidInput($_GET['action'],10,'s',false,$validActionSet)?$_GET['action']:NULL);
$complement = "&action=$action&monaut=$monaut&cookie=".$_COOKIE['illinkid'];
if ((!empty($_COOKIE['illinkid'])) && (empty($action)) && ($monaut=="sadmin"))
    header("$rediradmin".$complement);
if ((!empty($_COOKIE['illinkid'])) && (empty($action)) && ($monaut=="admin"))
    header("$rediradmin".$complement);
if ((!empty($_COOKIE['illinkid'])) && (empty($action)) && ($monaut=="user"))
    header("$rediruser".$complement);
if ((!empty($_COOKIE['illinkid'])) && (empty($action)) && ($monaut=="guest"))
    header("$redirguest".$complement);
if(!empty($action)){
    if ($action == 'logout'){
        setcookie('illinkid[nom]', '', (time() - 31536000));
        setcookie('illinkid[bib]', '', (time() - 31536000));
        setcookie('illinkid[aut]', '', (time() - 31536000));
        setcookie('illinkid[log]', '', (time() - 31536000));
    }

    // *********************************
    // *********************************
    // shibboleth authentication
    // *********************************
    // *********************************
    if (($shibboleth == 1) && ($action == 'shibboleth')){
        $email = 'nobody@nowhere.ch';
        // $email = strtolower($_SERVER['mail']);
        $email = strtolower($_SERVER['Shib-InetOrgPerson-mail']);
        if (strlen($email)<6){
            $email = 'nobody@nowhere.ch';
            $mes='Votre login Shibboleth ne correspond pas avec un compte sur OpenILLink, veuillez contacter l\'administrateur du site : ' . $configemail;
        }
        else{
            // check if the user id and password combination exist in database
            $req = "SELECT * FROM users WHERE email = '$email'";
            $result = dbquery($req);
            $nb = iimysqli_num_rows($result);
            if ($nb == 1){
                // the user id and password match
                $logok=$logok+1;
                for ($i=0 ; $i<$nb ; $i++){
                    $enreg = iimysqli_result_fetch_array($result);
                    $nom = $enreg['name'];
                    $login = $enreg['login'];
                    $status = $enreg['status'];
                    $library = $enreg['library'];
                    $admin = $enreg['admin'];
                    $admin = md5 ($admin . $secure_string_cookie);
                    setcookie('illinkid[nom]', $nom, (time() + 36000));
                    setcookie('illinkid[bib]', $library, (time() + 36000));
                    setcookie('illinkid[aut]', $admin, (time() + 36000));
                    setcookie('illinkid[log]', $login, (time() + 36000));
                    if ($monaut=="sadmin")
                        header("$rediradmin");
                    if ($monaut=="admin")
                        header("$rediradmin");
                    if ($monaut=="user")
                        header("$rediruser");
                    if ($monaut=="guest")
                        header("$redirguest");
                }
            }
            else{
                 // the user id and password don't match, so guest with login = email
                 $cookie_guest = md5 ("9" . $secure_string_cookie);
                 $logok=$logok+1;
                 setcookie('illinkid[nom]', $email, (time() + 36000));
                 setcookie('illinkid[bib]', 'guest', (time() + 36000));
                 setcookie('illinkid[aut]', $cookie_guest, (time() + 36000));
                 setcookie('illinkid[log]', $email, (time() + 36000));
                 header("$redirguest");
            }
        }
    }
}
// *********************************
// *********************************
// login/password authentication
// *********************************
// *********************************

$log = ((!empty($_POST['log'])) && isValidInput($_POST['log'],255,'s',false))?$_POST['log']:NULL;
$pwd = ((!empty($_POST['pwd'])) && isValidInput($_POST['pwd'],255,'s',false))?$_POST['pwd']:NULL;
if ((!empty($log))&&(!empty($pwd))){
    $logok=0;
    $password=md5($pwd);
    // check if the user id and password combination exist in database
    $req = "SELECT * FROM users WHERE login = '$log' AND password = '$password'";
    $result = dbquery($req);
    $nb = iimysqli_num_rows($result);
    if ($nb == 1){
        // the user id and password match,
        $logok=$logok+1;
        $enreg = iimysqli_result_fetch_array($result);
        $nom = $enreg['name'];
        $login = $enreg['login'];
        $status = $enreg['status'];
        $library = $enreg['library'];
        $admin = $enreg['admin'];
        $admin = md5 ($admin . $secure_string_cookie);
        setcookie('illinkid[nom]', $nom, (time() + 36000));
        setcookie('illinkid[bib]', $library, (time() + 36000));
        setcookie('illinkid[aut]', $admin, (time() + 36000));
        setcookie('illinkid[log]', $login, (time() + 36000));
        if (in_array($enreg['admin'], array($auth_sadmin, $auth_admin)))
           header("$rediradmin");
        if ($enreg['admin'] == $auth_user)
           header("$rediruser");
        if ($enreg['admin'] == $auth_guest)
           header("$redirguest");
    }
    else
        $mes='Le login ou le password ne sont pas corrects';
}
if ((!empty($log))||(!empty($pwd))){
    if ($logok==0){
        // Connexion par login crypté
        $mailg = strtolower($log) . $secure_string_guest_login;
        $passwordg = substr(md5($mailg), 0, 8);
        if ($pwd == $passwordg){
            $cookie_guest = md5 ($auth_guest . $secure_string_cookie);
            $logok=$logok+1;
            setcookie('illinkid[nom]', strtolower($log), (time() + 36000));
            setcookie('illinkid[bib]', 'guest', (time() + 36000));
            setcookie('illinkid[aut]', $cookie_guest, (time() + 36000));
            setcookie('illinkid[log]', strtolower($log), (time() + 36000));
            header("$redirguest");
        }
        else
            $mes='Le login ou le password ne sont pas corrects';
    }
}
require ("includes/header.php");
echo "<ul>\n";
if (!empty($mes))
    echo "<br /><b><font color=\"red\">".$mes."</font></b><br />\n";
if ($shibboleth == 1)
    echo "<a href=\"". $shibbolethurl . "\"><img src=\"img/shibboleth.png\" alt=\"Shibboleth authentication\" style=\"float:right;\"/></a>";
echo "<form name=\"loginform\" id=\"loginform\" action=\"login.php\" method=\"post\">\n";
if (empty($log))
    $log='';

echo "<label>Username:<br /><input type=\"text\" name=\"log\" id=\"log\" value=\"" . $log . "\" size=\"20\" tabindex=\"1\" /></label></p>\n";
echo "<label>Password:<br /> <input type=\"password\" name=\"pwd\" id=\"pwd\" value=\"\" size=\"20\" tabindex=\"2\" /></label></p>\n";
// echo "<p>\n";
// echo "  <label><input name=\"rememberme\" type=\"checkbox\" id=\"rememberme\" value=\"forever\" tabindex=\"3\" /> \n";
// echo "  Garder en mémoire</label></p>\n";
echo "<p>\n";
echo "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"login\" tabindex=\"4\" />\n";
echo "<input type=\"hidden\" name=\"redirect_to\" value=\"/\" />\n";
echo "</p>\n";
echo "<br />\n";
echo "</form>\n";

echo "</ul>\n";
echo "\n";
if ((!empty($action)) && $action == 'logout'){
    $monnom="";
    $monaut="";
    $monlog="";
}
if ($displayResendLink){
    echo '<p><a href="resendcredentials.php" target="_self"> Demander le mot de passe</a> : service seulement disponible pour les utilisateurs avec une commande openillink</p>';
}
require ("includes/footer.php");
?>
