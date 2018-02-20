<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2015
 * Desc: Contrôleur de base
 */
class ControllerBase extends Phalcon\Mvc\Controller
{
	/**
	 * Récupération des fichiers de messages en fonction de la langue (fr par défaut)
	 */
    protected function _getTransPath()
    {
        $translationPath = '../app/messages/';
        $language = $this->session->get("language");
        if (!$language) {
            $this->session->set("language", "fr");
        }
        if ($language === 'fr') {
            return $translationPath.$language;
        } else {
            return $translationPath.'fr';
        }
    }

    /**
     * Chargement du fichier de langue principal (main)
	 * Retourne un objet de type Translate
	 * Envoi vers la vue de l'objet Translate sous le nom $mt
     */
    public function loadMainTrans()
    {
        $translationPath = $this->_getTransPath();
        require $translationPath."/main.php";

        //Retourne un objet de type Translate
        $mainTranslate = new Phalcon\Translate\Adapter\NativeArray(array(
            "content" => $messages
        ));

        //Envoi vers la vue de l'objet Translate sous le nom $mt
        $this->view->setVar("mt", $mainTranslate);
      }

      /**
       * Chargement du fichier de langue correspondant au contrôleur appelé
	   * Retourne un objet de type Translate
	   * Envoi vers la vue de l'objet Translate sous le nom $mt
	   * Mise en variable de l'objet Translate pour accès dans le contrôleur
       */
    public function loadCustomTrans($transFile)
    {
        $translationPath = $this->_getTransPath();
        require $translationPath.'/'.$transFile.'.php';

        //Retourne un objet de type Translate
        $controllerTranslate = new Phalcon\Translate\Adapter\NativeArray(array(
            "content" => $messages
        ));

        //Envoi vers la vue de l'objet Translate sous le nom $t
        $this->view->setVar("t", $controllerTranslate);
		
		//Mise en variable de l'objet Translate pour accès dans le contrôleur
		$this->t = $controllerTranslate;
    }

	/**
	 * Intialisation du contrôleur
	 * Ajout du préfixe au titre
	 * Chargement du fichier de langue principal
	 * Gestion des variables globales
	 * Réception d'un message d'erreur
	 * Réception d'un message de succès
	 * Réception d'un message de warning
	 * Réception d'un message d'information
	 * Données utilisateur envoyées à la vue
	 */
    public function initialize()
    {
		//Ajout du préfixe au titre
		Phalcon\Tag::prependTitle('THEAKAGI.COM - ');
		
		//Chargement du fichier de langue principal
        $this->loadMainTrans();
		
		//Réception d'un message d'erreur
		$err = $this->session->get('err');
		
		if (!empty($err))
		{
			$this->view->setVar('err', $err);
			$this->session->remove('err');
		}
		
		//Réception d'un message de succès
		$succ = $this->session->get('succ');
		
		if (!empty($succ))
		{
			$this->view->setVar('succ', $succ);
			$this->session->remove('succ');
		}
		
		//Réception d'un message de warning
		$warn = $this->session->get('warn');
		
		if (!empty($warn))
		{
			$this->view->setVar('warn', $warn);
			$this->session->remove('warn');
		}
		
		//Réception d'un message d'information
		$info = $this->session->get('info');
		
		if (!empty($info))
		{
			$this->view->setVar('info', $info);
			$this->session->remove('info');
		}
		
		//Données utilisateur envoyées à la vue
		$this->view->setVar('u_nom', $this->session->get('u_nom'));
		$this->view->setVar('u_prenom', $this->session->get('u_prenom'));
		$this->view->setVar('u_fonction', $this->session->get('u_fonction'));
		$u_vignette = (empty($this->session->get('u_vignette')))?'defaut.png':$this->session->get('u_vignette');
		$this->view->setVar('u_vignette', $u_vignette);
    }
	
	/**
	 * Date en français
	 */
	public function dateFr($format, $timestamp=false, $txt='')
	{
		if ( !$timestamp ) $date_en = $txt;
		else               $date_en = date($format,$timestamp);

		$texte_en = array(
			"Monday", "Tuesday", "Wednesday", "Thursday",
			"Friday", "Saturday", "Sunday", "January",
			"February", "March", "April", "May",
			"June", "July", "August", "September",
			"October", "November", "December"
		);
		$texte_fr = array(
			"Lundi", "Mardi", "Mercredi", "Jeudi",
			"Vendredi", "Samedi", "Dimanche", "Janvier",
			"F&eacute;vrier", "Mars", "Avril", "Mai",
			"Juin", "Juillet", "Ao&ucirc;t", "Septembre",
			"Octobre", "Novembre", "D&eacute;cembre"
		);
		$date_fr = str_replace($texte_en, $texte_fr, $date_en);

		$texte_en = array(
			"Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun",
			"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul",
			"Aug", "Sep", "Oct", "Nov", "Dec"
		);
		$texte_fr = array(
			"Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim",
			"Jan", "F&eacute;v", "Mar", "Avr", "Mai", "Jui",
			"Jui", "Ao&ucirc;", "Sep", "Oct", "Nov", "D&eacute;c"
		);
		
		$date_fr = str_replace($texte_en, $texte_fr, $date_fr);

		return $date_fr;
	}
	
	/**
	 * Vérification si admin loggué
	 */
	public function check_admin()
	{
		//Vérification si admin loggué
		$admin = $this->session->get('u_id');
		
		if (empty($admin))
		{
			$this->session->set('err', $this->view->getVar('mt')['admin_check_failed']);
			$this->response->redirect("login");
			$this->dispatcher->forward
			(
				array
				(
					"controller" => "login",
					"action"     => "index"
				)
			);
			$this->view->disable();
			return false;
		}
	}
	
	/**
	 * Fonction de log
	 * Récupération des informations
	 * Création et enregistrement du log
	 */
	public function log($id)
	{
		//Récupération des informations
		$controller = $this->router->getControllerName();
		$action = $this->router->getActionName();
		$uri = $this->request->getURI();
		
		//Création et enregistrement du log
		$log = new Logs();
		$log->id_utilisateur = $this->session->get('u_id');
		$log->projet = $id;
		$log->date = time();
		$log->controller = $controller;
		$log->action = $action;
		$log->uri = $uri;
		$log->save();
	}
}
