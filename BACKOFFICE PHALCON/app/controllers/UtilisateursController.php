<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Contrôleur d'édition des utilisateurs
 */
use Phalcon\Mvc\Controller,
	Phalcon\Mvc\View;

class UtilisateursController extends ControllerBase
{
	/**
	 * Pré-initialisation du contrôleur
	 * Vérification utilisateur identifié
	 */
	public function beforeExecuteRoute($dispatcher)
    {
		//Vérification utilisateur identifié
		$this->check_admin();
    }
	
	/**
	 * Initialisation du contrôleur
	 * Chargement du fichier de langue correspondant au contrôleur
	 * Récupération du titre dans le fichie de langue du contrôleur
	 * Envoi de l'informatin de menu
	 * Header + Footer
	 * Gestion du fil d'ariane
	 * Appel de l'initialisation du contrôleur parent
	 */
    public function initialize()
    {
		//Chargement du fichier de langue correspondant au contrôleur
        $this->loadCustomTrans('utilisateurs');
		
		//Récupération du titre dans le fichie de langue du contrôleur
		Phalcon\Tag::setTitle($this->t['title']);
		
		//Envoi de l'information de menu
		$this->view->setVar('amutilisateurs', 'active');
		$this->view->setVar('autilisateurs', 'active');
		
		//Header + Footer
		$this->view->setTemplateBefore('header');
		$this->view->setTemplateAfter('footer');
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Utilisateurs');
		$breadcrumbs[2] = array('', 'Utilisateurs');
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Appel de l'initialisation du contrôleur parent
        parent::initialize();
    }

	/**
	 * Action principale
	 * ID utilisateur
	 * Réception du dernier utilisateur édité
	 * Total des utilisateurs en base de données
	 * Informations en session
	 * Envoi des informations CSS et langue vers la vue
	 */
    public function indexAction()
    {
		$this->log('');
		
		//ID utilisateur
		$u_id = $this->session->get('u_id');
		
		//Réception du dernier utilisateur édité
		$scrollto = $this->session->get('scrollto');
		$this->session->remove('scrollto');
		$scrollto = (empty($scrollto))?0:$scrollto;
		$this->view->setVar('scrollto', $scrollto);
		
		//Total des utilisateurs en base de données
		$totals = $this->modelsManager->createBuilder()
			->columns(array('count(u.id) as nb'))
			->addFrom('Utilisateurs', 'u')
			->getQuery()
			->execute();
		$total = $totals[0];
		$total = $total->nb;
		
		$this->view->setVar("total", $total);
		
		//Informations en session
		$sess_start = $this->session->get('sess_start_utilisateurs');
		$sess_order = $this->session->get('sess_order_utilisateurs');
		$sess_search = $this->session->get('sess_search_utilisateurs');
		$sess_length = $this->session->get('sess_length_utilisateurs');
		
		$this->view->setVar("sess_start_utilisateurs", $sess_start);
		$this->view->setVar("sess_order_utilisateurs", $sess_order);
		$this->view->setVar("sess_search_utilisateurs", $sess_search);
		$this->view->setVar("sess_length_utilisateurs", $sess_length);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "utilisateurs.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action de listing des utilisateurs en ajax
	 * Données reçues des dataTables
	 * Gestion de l'ordre d'affichage
	 * Récupération des utilisateurs selon les paramètres
	 * Total des utilisateurs
	 * Total des filtrés
	 * Génération du json
	 * Affichage du json
	 */
    public function listingAction()
    {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);
		$t = $this->t;
		
		//Données reçues des dataTables
		$draw = $this->request->getPost('draw', 'int');
		$start = $this->request->getPost('start', 'int');
		$length = $this->request->getPost('length', 'int');
		$search = $this->request->getPost('search', 'string'); //Array
		$order = $this->request->getPost('order'); //Array
		$columns = $this->request->getPost('columns'); //Array
		
		$search_value = $search['value'];
		$search_regex = $search['regex'];
		
		//Mise en session des données de filtre
		$this->session->set('sess_search_utilisateurs', $search_value);
		$this->session->set('sess_order_utilisateurs', $order);
		$this->session->set('sess_length_utilisateurs', $length);
		$this->session->set('sess_start_utilisateurs', $start);
		
		
		//Gestion de l'ordre d'affichage
		$orderby = 'u.id asc';
		if (count($order) > 0)
		{
			switch ($order[0]['column'])
			{
				case '1': $orderby = 'u.nom'; break; //Nom
				default: $orderby = 'u.id'; break; //ID par défaut
			}
			
			switch ($order[0]['dir'])
			{
				case 'desc': $orderby .= ' desc'; break; //DESC
				case 'asc': $orderby .= ' asc'; break; //ASC
				default: $orderby .= ' desc'; break; //ASC
			}
		}
		
		//Récupération des utilisateurs selon les paramètres
		if (trim($search_value) != '')
		{
			$utilisateurs = $this->modelsManager->createBuilder()
				->columns(array('u.id', 'u.nom', 'u.prenom', 'u.email'))
				->addFrom('Utilisateurs', 'u')
				->where('u.nom like "%'.$search_value.'%" or u.prenom like "%'.$search_value.'%" or u.email like "%'.$search_value.'%"')
				->limit($length, $start)
				->getQuery()
				->execute();
		}
		else
		{
			$utilisateurs = $this->modelsManager->createBuilder()
				->columns(array('u.id', 'u.nom', 'u.prenom', 'u.email'))
				->addFrom('Utilisateurs', 'u')
				->limit($length, $start)
				->getQuery()
				->execute();
		}
		
		//Total des utilisateurs
		$totals = $this->modelsManager->createBuilder()
			->columns(array('count(u.id) as nb'))
			->addFrom('Utilisateurs', 'u')
			->getQuery()
			->execute();
		$total = $totals[0];
		$total = $total->nb;
		
		//Total des filtrés
		if (trim($search_value) != '')
		{
			$filtres = $this->modelsManager->createBuilder()
				->columns(array('u.id'))
				->addFrom('Utilisateurs', 'u')
				->where('u.nom like "%'.$search_value.'%" or u.prenom like "%'.$search_value.'%" or u.email like "%'.$search_value.'%"')
				->orderBy($orderby)
				->getQuery()
				->execute();
			$filtre = count($filtres);
		}
		else
		{
			$filtres = $this->modelsManager->createBuilder()
				->columns(array('u.id'))
				->addFrom('Utilisateurs', 'u')
				->orderBy($orderby)
				->getQuery()
				->execute();
			$filtre = count($filtres);
		}
		
		//Génération du json
		$json = '{
"draw": '.$draw.',
"recordsTotal": '.$total.',
"recordsFiltered": '.$filtre.',
"data": [';
		
		$i=0;
		foreach ($utilisateurs as $utilisateur)
		{
			//Construction finale du json : ID, nom, prénom, modifier, supprimer
			if ($i > 0)
			{
				$json .= ',
			';
			}
			$json .= '["'.$utilisateur->id.'",
			"'.$utilisateur->nom.'",
			"'.$utilisateur->prenom.'",
			"'.$utilisateur->email.'",
			"<a href=\'utilisateurs/detail/'.$utilisateur->id.'\'>'.$t['modifier'].'</a>",
			"<a href=\'javascript: supprimer('.$utilisateur->id.');\'>'.$t['supprimer'].'</a>"]';
			$i++;
		}
		
		$json .= ']}';
		
		//Affichage du json
		echo $json;
		
		return true;
	}
	
	/**
	 * Action de suppression
	 * Récupération de l'utilisateur demandé
	 * Suppression des historiques
	 * Suppression des logs
	 * Suppression de l'utilisateur
	 * Redirection vers l'index
	 */
    public function supprimerAction($id_utilisateur)
    {
		$this->log('');
		
		//Récupération du type de clients demandé
		$utilisateurs = $this->modelsManager->createBuilder()
			->from(array('Utilisateurs'))
			->where('Utilisateurs.id = "'.$id_utilisateur.'"')
			->limit(1)
			->getQuery()
			->execute();
		if (count($utilisateurs) == 0)
		{
			$this->session->set('err', $this->t['err_supprimer']);
			$this->response->redirect('utilisateurs');
			return false;
		}
		else
		{
			$utilisateur = $utilisateurs[0];
		}
		
		//Suppression de l'utilisateur
		$utilisateur->delete();
		
		//Redirection vers l'index
		$this->session->set('succ', $this->t['succ_supprimer']);
		$this->response->redirect('utilisateurs');
		return true;
	}
	
	/**
	 * Action d'affichage du formulaire d'ajout
	 * Gestion du fil d'ariane
	 * Vérification si valeur en session et envoi vers la vue
	 * Envoi des informations CSS et langue vers la vue
	 */
	public function ajouterAction()
	{
		$this->log('');
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Utilisateurs');
		$breadcrumbs[2] = array('utilisateurs', 'Utilisateurs');
		$breadcrumbs[3] = array('', 'Ajouter un utilisateur');
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Vérification si valeur en session et envoi vers la vue
		$nom = $this->session->get('nom');
		$this->view->setVar('nom', $nom);
		$nom = $this->session->get('prenom');
		$this->view->setVar('prenom', $prenom);
		$nom = $this->session->get('email');
		$this->view->setVar('email', $email);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "utilisateurs.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action d'ajout
	 * Réception des valeurs
	 * Mise en session des valeurs
	 * Vérification validité des données
	 * Création de mot de passe
	 * Ajout en base de données
	 * Vidage session et redirection
	 */
	public function nouveauAction()
	{
		$this->log('');
		
		//Réception des valeurs
		$nom = $this->request->getPost('nom', 'string');
		$prenom = $this->request->getPost('prenom', 'string');
		$email = $this->request->getPost('email', 'email');
		$fonction = $this->request->getPost('fonction', 'email');
		$password = $this->request->getPost('password', 'string');
		$confirmation_password = $this->request->getPost('confirmation_password', 'string');
		
		//Mise en session
		$this->session->set('nom', $nom);
		$this->session->set('prenom', $prenom);
		$this->session->set('email', $email);
		
		//Vérification validité des données
		if (empty($nom))
		{
			$this->session->set('err', $this->t['err_nom']);
			$this->response->redirect('utilisateurs/ajouter');
			return false;
		}
		if (empty($email))
		{
			$this->session->set('err', $this->t['err_email']);
			$this->response->redirect('utilisateurs/ajouter');
			return false;
		}
		
		//Création de mot de passe
		if(!empty($password))
		{
			if($password == $confirmation_password)
			{
				$timeTarget = 0.2; 
 
				$cost = 9;
				do {
					$cost++;
					$start = microtime(true);
					password_hash($password, PASSWORD_BCRYPT, ["cost" => $cost]);
					$end = microtime(true);
				} while (($end - $start) < $timeTarget);
				
				$hash = password_hash($password,PASSWORD_BCRYPT,['cost' => $cost]);
			}
			else
			{
				$this->session->set('err', $this->t['err_password']);
				$this->response->redirect('utilisateurs/ajouter');
				return false;
			}
		}
		else
		{
			$this->session->set('err', $this->t['err_champs']);
			$this->response->redirect('utilisateurs/ajouter');
			return false;
		}
		
		//Ajout en base de données
		$utilisateur = new Utilisateurs();
		
		$utilisateur->nom = $nom;
		$utilisateur->prenom = $prenom;
		$utilisateur->email = $email;
		$utilisateur->fonction = $fonction;
		$utilisateur->pass = $hash;
		$saved = $utilisateur->save();
		
		if ($saved == false)
		{
			$this->session->set('err', $this->t['err_save']);
			$this->response->redirect('utilisateurs/ajouter');
			return false;
		}
		
		//Vidage session et redirection
		$this->session->remove('nom');
		$this->session->remove('prenom');
		$this->session->remove('email');
		$this->session->set('succ', $this->t['succ_aj'].$utilisateur->id);
		$this->response->redirect('utilisateurs/detail/'.$utilisateur->id);
		return true;
	}
	
	/**
	 * Action de détail d'un utilisateur
	 * Récupération de l'utilisateur demandé
	 * Gestion du fil d'ariane
	 * Envoi des variables vers la vue
	 * Envoi des informations CSS et langue vers la vue
	 */
    public function detailAction($id_utilisateur)
    {
		$this->log('');
		
		//Récupération de l'utilisateur demandé
		$utilisateurs = $this->modelsManager->createBuilder()
			->from(array('Utilisateurs'))
			->where('Utilisateurs.id = "'.$id_utilisateur.'"')
			->limit(1)
			->getQuery()
			->execute();
		if (count($utilisateurs) == 0)
		{
			$this->session->set('err', $this->t['err_detail']);
			$this->response->redirect('utilisateurs');
			return false;
		}
		else
		{
			$utilisateur = $utilisateurs[0];
		}
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Utilisateurs');
		$breadcrumbs[2] = array('utilisateurs', 'Utilisateurs');
		$breadcrumbs[3] = array('', 'Utilisateur : '.$utilisateur->prenom.' '.$utilisateur->nom.' <small>(ID '.$utilisateur->id.')</small>');
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Envoi des variables vers la vue
		$this->view->setVar('utilisateur', $utilisateur);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "utilisateurs.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action d'enregistrement
	 * Réception des valeurs
	 * Vérification validité des données
	 * Récupération de l'utilisateur
	 * Mise à jour en base de données
	 * Vidage session et redirection
	 */
	public function enregistrerAction($id_utilisateur)
	{
		$this->log('');
		
		//Réception des valeurs
		$retour = $this->request->getPost('retour', 'int');
		
		$nom = $this->request->getPost('nom', 'string');
		$prenom = $this->request->getPost('prenom', 'string');
		$email = $this->request->getPost('email', 'email');
		$fonction = $this->request->getPost('fonction', 'string');
		$password = $this->request->getPost('password', 'string');
		$confirmation_password = $this->request->getPost('confirmation_password', 'string');
		
		//Vérification validité des données
		if (empty($nom))
		{
			$this->session->set('err', $this->t['en_err_nom']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		if (empty($email))
		{
			$this->session->set('err', $this->t['en_err_email']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		
		//Récupération de l'utilisateur
		$utilisateurs = $this->modelsManager->createBuilder()
			->from(array('Utilisateurs'))
			->where('Utilisateurs.id = "'.$id_utilisateur.'"')
			->limit(1)
			->getQuery()
			->execute();
			
		if (count($utilisateurs) == 0)
		{
			$this->session->set('err', $this->t['err_enregistrer']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		else
		{
			$utilisateur = $utilisateurs[0];
		}
		
		//Création mot de passe si champs non vide
		if(!empty($password))
		{
			if($password == $confirmation_password)
			{
				$timeTarget = 0.2; 
 
				$cost = 9;
				do {
					$cost++;
					$start = microtime(true);
					password_hash($password, PASSWORD_BCRYPT, ["cost" => $cost]);
					$end = microtime(true);
				} while (($end - $start) < $timeTarget);
				
				$hash = password_hash($password,PASSWORD_BCRYPT,['cost' => $cost]);
				
				$utilisateur->pass = $hash;
			}
			else
			{
				$this->session->set('err', $this->t['err_password']);
				$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
				return false;
			}
		}
		
		//Mise à jour en base de données
		$utilisateur->nom = $nom;
		$utilisateur->prenom = $prenom;
		$utilisateur->email = $email;
		$utilisateur->fonction = $fonction;
		
		$saved = $utilisateur->save();
		
		if ($saved == false)
		{
			$this->session->set('err', $this->t['en_err_save']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		
		//Vidage session et redirection
		if (! empty($retour))
		{
			//Appui sur le bouton retour
			$this->session->set('succ', $this->t['succ_en'].$utilisateur->id);
			$this->session->set('scrollto', $utilisateur->id);
			$this->response->redirect('utilisateurs');
			return true;
		}
		else
		{
			//Retour par défaut vers la fiche détail
			$this->session->set('succ', $this->t['succ_en'].$utilisateur->id);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return true;
		}
	}
	
	/**
	 * Changement de langue
	 * Rechargement des traductions si nécessaire
	 * Retour à la dernière page
	 */
    public function setLanguageAction($language='')
    {
        //Rechargement des traductions si nécessaire
        if ($language == 'fr') {
            $this->session->set('language', $language);
            $this->loadMainTrans();
            $this->loadCustomTrans('utilisateurs');
        }

        //Retour à la dernière page
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->dispatcher->forward(array('controller' => 'utilisateurs', 'action' => 'index'));
        }
    }
}
